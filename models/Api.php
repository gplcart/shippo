<?php

/**
 * @package Shippo
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\modules\shippo\models;

use gplcart\core\Config,
    gplcart\core\Library;

/**
 * Manages basic behaviors and data related to Shippo API
 */
class Api
{

    /**
     * Config class instance
     * @var \gplcart\core\Config $config
     */
    protected $config;

    /**
     * Library class instance
     * @var \gplcart\core\Library $library
     */
    protected $library;

    /**
     * An array of Shippo module settings
     * @var array
     */
    protected $settings;

    /**
     * @param Config $config
     * @param Library $library
     */
    public function __construct(Config $config, Library $library)
    {
        $this->config = $config;
        $this->library = $library;
        $this->settings = $this->config->getFromModule('shippo');
    }

    /**
     * Returns either live or test token
     * @return string
     */
    protected function getToken()
    {
        if (empty($this->settings['test'])) {
            return $this->settings['key']['live'];
        }

        return $this->settings['key']['test'];
    }

    /**
     * Performs request to Shippo's API to get shipping rates
     * @param array $from
     * @param array $to
     * @param array $parcel
     * @return array
     */
    public function getRates(array $from, array $to, array $parcel)
    {
        $this->library->load('shippo');

        try {

            \Shippo::setApiKey($this->getToken());

            $shipment = array(
                'async' => false,
                'address_to' => $to,
                'address_from' => $from,
                'parcels' => array($parcel)
            );

            $result = \Shippo_Shipment::create($shipment);
        } catch (\Exception $ex) {
            $result = array();
        }

        if (empty($result['rates'])) {
            return array();
        }

        $rates = array();
        foreach ($result['rates'] as $rate) {
            $rates[$rate['servicelevel']['token']] = json_decode($rate, true);
        }

        return $rates;
    }

    /**
     * Creates a Shippo address object
     * @param array $address
     * @param array $options
     * @return array
     */
    public function createAddress(array $address, array $options = array())
    {
        $this->library->load('shippo');

        try {
            \Shippo::setApiKey($this->getToken());
            $response = \Shippo_Address::create(array_merge($address, $options));
        } catch (\Exception $ex) {
            $response = array();
        }

        if (!is_array($response)) {
            $response = json_decode($response, true);
        }

        return $response;
    }

    /**
     * Validates an address
     * @param array $address
     * @return boolean|array
     */
    public function isValidAddress(array $address)
    {
        $result = $this->createAddress($address, array('validate' => true));

        if (empty($result['object_id'])) {
            return false;
        }

        if (!isset($result['validation_results']['is_valid']) || !empty($result['validation_results']['is_valid'])) {
            return true;
        }

        if (empty($result['validation_results']['messages'])) {
            return false;
        }

        $messages = array();
        foreach ($result['validation_results']['messages'] as $message) {
            $messages[] = $message['text'];
        }

        return $messages;
    }

    /**
     * Request shipping label and tracking number
     * @param string $object_id
     * @param array $options
     * @return array
     */
    public function getLabel($object_id, array $options = array())
    {
        $this->library->load('shippo');

        try {
            \Shippo::setApiKey($this->getToken());
            $default = array('rate' => $object_id, 'async' => false);
            $response = \Shippo_Transaction::create(array_merge($default, $options));
        } catch (\Exception $ex) {
            $response = array();
        }

        if (!is_array($response)) {
            $response = json_decode($response, true);
        }

        if (isset($response['status']) && $response['status'] === 'SUCCESS') {
            return $response;
        }

        return array();
    }

}
