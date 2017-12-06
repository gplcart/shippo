<?php

/**
 * @package Shippo
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\modules\shippo\models;

use gplcart\core\Library,
    gplcart\core\Module;

/**
 * Manages basic behaviors and data related to Shippo API
 */
class Api
{

    /**
     * Module class instance
     * @var \gplcart\core\Module $module
     */
    protected $module;

    /**
     * Library class instance
     * @var \gplcart\core\Library $library
     */
    protected $library;

    /**
     * @param Library $library
     * @param Module $module
     */
    public function __construct(Library $library, Module $module)
    {
        $this->module = $module;
        $this->library = $library;
    }

    /**
     * Returns either live or test token
     * @return string
     */
    protected function getToken()
    {
        $settings = $this->module->getSettings('shippo');

        if (empty($settings['test'])) {
            return $settings['key']['live'];
        }

        return $settings['key']['test'];
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
