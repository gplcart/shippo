<?php

/**
 * @package Shippo
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\modules\shippo\models;

use gplcart\core\Model;
use gplcart\core\models\Language as LanguageModel,
    gplcart\core\models\User as UserModel,
    gplcart\core\models\Price as PriceModel,
    gplcart\core\models\State as StateModel,
    gplcart\core\models\Currency as CurrencyModel,
    gplcart\core\models\Address as AddressModel,
    gplcart\core\models\Store as StoreModel,
    gplcart\core\models\Shipping as ShippingModel;
use gplcart\core\helpers\Session as SessionHelper,
    gplcart\core\helpers\Convertor as ConvertorHelper;
use gplcart\modules\shippo\models\Api as ShippoApiModel;

/**
 * Manages basic behaviors and data related to Shippo module
 */
class Shippo extends Model
{

    /**
     * An array of Shippo module settings
     * @var array
     */
    protected $settings = array();

    /**
     * Api model instance
     * @var \gplcart\modules\shippo\models\Api $api
     */
    protected $api;

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * User model class instance
     * @var \gplcart\core\models\User $user
     */
    protected $user;

    /**
     * Price model class instance
     * @var \gplcart\core\models\Price $price
     */
    protected $price;

    /**
     * Shipping model class instance
     * @var \gplcart\core\models\Shipping $shipping
     */
    protected $shipping;

    /**
     * State model class instance
     * @var \gplcart\core\models\State $state
     */
    protected $state;

    /**
     * Currency model class instance
     * @var \gplcart\core\models\Currency $currency
     */
    protected $currency;

    /**
     * Address model class instance
     * @var \gplcart\core\models\Address $address
     */
    protected $address;

    /**
     * Store model instance
     * @var \gplcart\core\models\Store $store
     */
    protected $store;

    /**
     * Convertor class instance
     * @var \gplcart\core\helpers\Convertor $convertor
     */
    protected $convertor;

    /**
     * Session class instance
     * @var \gplcart\core\helpers\Session $session
     */
    protected $session;

    /**
     * @param ShippoApiModel $api
     * @param LanguageModel $language
     * @param UserModel $user
     * @param PriceModel $price
     * @param CurrencyModel $currency
     * @param AddressModel $address
     * @param StoreModel $store
     * @param StateModel $state
     * @param ShippingModel $shipping
     * @param SessionHelper $session
     * @param ConvertorHelper $convertor
     */
    public function __construct(ShippoApiModel $api, LanguageModel $language,
            UserModel $user, PriceModel $price, CurrencyModel $currency,
            AddressModel $address, StoreModel $store, StateModel $state,
            ShippingModel $shipping, SessionHelper $session,
            ConvertorHelper $convertor)
    {
        parent::__construct();

        $this->api = $api;
        $this->user = $user;
        $this->price = $price;
        $this->state = $state;
        $this->store = $store;
        $this->session = $session;
        $this->address = $address;
        $this->currency = $currency;
        $this->language = $language;
        $this->shipping = $shipping;
        $this->convertor = $convertor;

        $this->settings = $this->config->module('shippo');
    }

    /**
     * Returns an array of carrier names keyed by id
     * @return array
     */
    public function getCarrierNames()
    {
        return require __DIR__ . '/../config/carriers.php';
    }

    /**
     * Returns an array of service names keyed by id
     * @return array
     */
    public function getServiceNames()
    {
        return require __DIR__ . '/../config/services.php';
    }

    /**
     * Calculates shipping rates and sets available shipping methods on checkout page
     * @param array $data
     */
    public function calculate(array &$data)
    {
        if ($data['request_shipping_methods']) {
            $address = $this->getSourceAddress($data);
            $rates = $this->getRates($address, $data['cart'], $data['order']);
            $this->setShippingMethodsCheckout($data, $rates);
        }
    }

    /**
     * Validates order shipping rates before an order is created
     * @param array $order
     * @param array $result
     */
    public function validate(array &$order, array &$result)
    {
        $method = $this->shipping->get($order['shipping']);

        if (empty($method['module']) || $method['module'] !== 'shippo') {
            return null;
        }

        // Forbid further processing if shipping component has not been set
        if (!isset($order['data']['components']['shipping'])) {
            $result = array(
                'severity' => 'danger',
                'redirect' => 'checkout',
                'message' => $this->language->text('Please recalculate shipping rates')
            );
            return null;
        }

        $rates = $this->getRates($order['shipping_address'], $order['cart'], $order);

        $this->setShippingMethod($method, $rates, $order['currency']);

        // Forbid further processing and redirect back if shipping rates don't match
        if (isset($method['price']) && $method['price'] != $order['data']['components']['shipping']) {
            $result = array(
                'severity' => 'danger',
                'redirect' => 'checkout',
                'message' => $this->language->text('Please recalculate shipping rates')
            );
            return null;
        }

        // Save Shippo request in the order data to get later labels etc.
        $order['data']['shippo'] = $method['data'];
    }

    /**
     * Returns an array of cached rates
     * @param array|integer $address
     * @param array $cart
     * @param array $order
     * @return array
     */
    public function getRates($address, array $cart, array $order)
    {
        if (!is_array($address)) {
            $address = $this->address->get($address);
        }

        $to_address = $this->getShippoAddress($address);

        if (empty($to_address)) {
            return array();
        }

        $session_limit = 10;
        $session_rates = $this->session->get('shippo', array());

        if (count($session_rates) > $session_limit) {
            $this->session->delete('shippo');
        }

        $cache_id = $this->getCacheKey($this->settings['sender'], $to_address);

        if (!empty($session_rates[$cache_id])) {
            return $session_rates[$cache_id];
        }

        if ($this->api->isValidAddress($to_address) !== true) {
            return $this->getDefaultRates();
        }

        $parcel = $this->getParcel($cart, $order);
        $response = $this->api->getRates($this->settings['sender'], $to_address, $parcel);

        if (empty($response)) {
            return $this->getDefaultRates();
        }

        $session_rates[$cache_id] = $response;
        $this->session->set('shippo', $session_rates);

        return $response;
    }

    /**
     * Returns a unique cache key for a combination of recipient and sender addresses
     * @param array $from
     * @param array $to
     * @return string
     */
    protected function getCacheKey(array $from, $to)
    {
        ksort($to);
        ksort($from);

        return md5(json_encode(array($from, $to)));
    }

    /**
     * Sets shipping methods available for the order shipping address
     * @param array $data
     * @param array $rates
     */
    protected function setShippingMethodsCheckout(array &$data, array $rates)
    {
        foreach ($data['shipping_methods'] as &$method) {

            if (empty($method['module']) || $method['module'] !== 'shippo') {
                continue;
            }

            if (!$this->setShippingMethod($method, $rates, $data['order']['currency'])) {
                unset($data['shipping_methods'][$method['id']]);
            }
        }

        // Show cheapest items first
        gplcart_array_sort($data['shipping_methods'], 'price');
    }

    /**
     * Adjust price and label for the given shipping method
     * @param array $method
     * @param array $rates
     * @param string $currency
     * @return boolean
     */
    protected function setShippingMethod(array &$method, array $rates, $currency)
    {
        $service_id = $this->getShippoServiceId($method['id']);

        if (empty($rates[$service_id])) {
            return false;
        }

        $converted = $this->currency->convert($rates[$service_id]['amount_local'], $rates[$service_id]['currency_local'], $currency);
        $price = $this->price->format($converted, $currency, false, true);

        $method['title'] .= " - $price";

        if (isset($rates[$service_id]['days'])) {
            $method['description'] = $this->language->text('Estimated delivery time: @num day(s)', array('@num' => $rates[$service_id]['days']));
        }

        $method['data'] = $rates[$service_id];
        $method['price'] = $this->price->amount($converted, $currency);

        return true;
    }

    /**
     * Returns the default rates if unabled to calculate via Shippo API
     * @return array
     */
    protected function getDefaultRates()
    {
        if (empty($this->settings['default']['method'])) {
            return array();
        }

        $service_id = $this->getShippoServiceId($this->settings['default']['method']);

        return array(
            $service_id => array(
                'currency' => 'USD',
                'amount' => $this->settings['default']['price']
            )
        );
    }

    /**
     * Converts the system method ID into Shippo's service ID
     * @param string $system_method_id
     * @return string
     */
    protected function getShippoServiceId($system_method_id)
    {
        return str_replace('shippo_', '', $system_method_id);
    }

    /**
     * Converts an address into Shippo's format
     * @param array $data
     * @return array
     */
    protected function getShippoAddress(array $data)
    {
        if (empty($data)) {
            return array();
        }

        $name = array();
        if (isset($data['first_name'])) {
            $name[] = $data['first_name'];
        }
        if (isset($data['middle_name'])) {
            $name[] = $data['middle_name'];
        }
        if (isset($data['last_name'])) {
            $name[] = $data['last_name'];
        }

        $city = '';
        if (isset($data['city_name'])) {
            $city = $data['city_name'];
        } else if (isset($data['city_id']) && !is_numeric($data['city_id'])) {
            $city = $data['city_id'];
        }

        $state = '';
        if (isset($data['state_name'])) {
            $state = $data['state_name'];
        } else if (isset($data['state_id'])) {
            $state_data = $this->state->get($data['state_id']);
            $state = isset($state_data['name']) ? $state_data['name'] : '';
        }

        if (!empty($data['country'])) {
            $country = $data['country'];
        } else {
            $store = $this->store->getDefault(true);
            $country = $store['data']['country'];
        }

        return array(
            'city' => $city,
            'state' => $state,
            'country' => $country,
            'name' => implode(' ', $name),
            'email' => $this->user->getSession('email'),
            'phone' => isset($data['phone']) ? $data['phone'] : '',
            'zip' => isset($data['postcode']) ? $data['postcode'] : '',
            'company' => isset($data['company']) ? $data['company'] : '',
            'street1' => isset($data['address_1']) ? $data['address_1'] : '',
            'street2' => isset($data['address_2']) ? $data['address_2'] : '',
        );
    }

    /**
     * Returns an array of parcel data in Shippo's format
     * @param array $cart
     * @param array $order
     * @return array
     */
    protected function getParcel(array $cart, array $order)
    {
        $dimensions = $this->getDimensions($cart, $order);
        $dimensions += $this->settings['default'];

        return array(
            'width' => $dimensions['width'],
            'height' => $dimensions['height'],
            'length' => $dimensions['length'],
            'weight' => $dimensions['weight'],
            'mass_unit' => $order['weight_unit'],
            'distance_unit' => $order['size_unit']
        );
    }

    /**
     * Returns total dimensions of all products in the order
     * @param array $cart
     * @param array $order
     * @return array
     */
    protected function getDimensions(array $cart, array $order)
    {
        $width = $height = $length = $weight = array();

        foreach ($cart['items'] as $item) {
            $product = $item['product'];
            $width[] = (float) $this->convertor->convert($product['width'], $product['size_unit'], $order['size_unit']);
            $height[] = (float) $this->convertor->convert($product['height'], $product['size_unit'], $order['size_unit']);
            $length[] = (float) $this->convertor->convert($product['length'], $product['size_unit'], $order['size_unit']);
            $weight[] = (float) $this->convertor->convert($product['weight'], $product['weight_unit'], $order['weight_unit']);
        }

        $result = array(
            'height' => max($height),
            'length' => max($length),
            'width' => array_sum($width),
            'weight' => array_sum($weight),
        );

        if (count(array_filter($result)) != count($result)) {
            return array();
        }

        return $result;
    }

    /**
     * Sets a source shipping address
     * @param array $data
     * @return array
     */
    protected function getSourceAddress(array $data)
    {
        if (!empty($data['order']['shipping_address'])) {
            $address_id = $data['order']['shipping_address'];
            return $this->address->get($address_id);
        }

        if (!empty($data['show_shipping_address_form']) && !empty($data['address']['shipping'])) {
            return $data['address']['shipping'];
        }

        return array();
    }

}
