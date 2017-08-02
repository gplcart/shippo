<?php

/**
 * @package Shippo
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2017, Iurii Makukh <gplcart.software@gmail.com>
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0+
 */

namespace gplcart\modules\shippo\controllers;

use gplcart\core\models\Module as ModuleModel;
use gplcart\core\models\Country as CountryModel;
use gplcart\core\models\Shipping as ShippingModel;
use gplcart\modules\shippo\models\Api as ShippoApiModel;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to Shippo module
 */
class Settings extends BackendController
{

    /**
     * Module model instance
     * @var \gplcart\core\models\Module $module
     */
    protected $module;

    /**
     * Shipping model instance
     * @var \gplcart\core\models\Shipping $shipping
     */
    protected $shipping;

    /**
     * Country model instance
     * @var \gplcart\core\models\Country $country
     */
    protected $country;

    /**
     * Shippo API model instance
     * @var \gplcart\modules\shippo\models\Api $api
     */
    protected $api;

    /**
     * @param ModuleModel $module
     * @param ShippingModel $shipping
     * @param CountryModel $country
     * @param ShippoApiModel $api
     */
    public function __construct(ModuleModel $module, ShippingModel $shipping,
            CountryModel $country, ShippoApiModel $api)
    {
        parent::__construct();

        $this->api = $api;
        $this->module = $module;
        $this->country = $country;
        $this->shipping = $shipping;
    }

    /**
     * Route page callback to display the module settings page
     */
    public function editSettings()
    {
        $this->setTitleEditSettings();
        $this->setBreadcrumbEditSettings();

        $this->setData('countries', $this->country->getIso());
        $this->setData('settings', $this->config->module('shippo'));
        $this->setData('methods', $this->getShippingMethodsSettings());

        $this->submitSettings();
        $this->outputEditSettings();
    }

    /**
     * Returns an array of Shippo shipping methods
     * @return array
     */
    protected function getShippingMethodsSettings()
    {
        return $this->shipping->getList(array('module' => 'shippo'));
    }

    /**
     * Set title on the module settings page
     */
    protected function setTitleEditSettings()
    {
        $vars = array('%name' => $this->text('Shippo'));
        $title = $this->text('Edit %name settings', $vars);
        $this->setTitle($title);
    }

    /**
     * Set breadcrumbs on the module settings page
     */
    protected function setBreadcrumbEditSettings()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')
        );

        $breadcrumbs[] = array(
            'text' => $this->text('Modules'),
            'url' => $this->url('admin/module/list')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Saves the submitted settings
     */
    protected function submitSettings()
    {
        if ($this->isPosted('save') && $this->validateSettings()) {
            $this->updateSettings();
        }
    }

    /**
     * Validate the submitted module settings
     */
    protected function validateSettings()
    {
        $this->setSubmitted('settings');

        if ($this->api->isValidAddress($this->getSubmitted('sender')) !== true) {
            $this->setMessage($this->text('Shippo was unable to validate sender\'s address. Please check it once again!'), 'warning', true);
        }

        return !$this->hasErrors();
    }

    /**
     * Update module settings
     */
    protected function updateSettings()
    {
        $this->controlAccess('module_edit');
        $this->module->setSettings('shippo', $this->getSubmitted());
        $this->redirect('', $this->text('Settings have been updated'), 'success');
    }

    /**
     * Render and output the module settings page
     */
    protected function outputEditSettings()
    {
        $this->output('shippo|settings');
    }

}
