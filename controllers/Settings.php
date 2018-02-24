<?php

/**
 * @package Shippo
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2017, Iurii Makukh <gplcart.software@gmail.com>
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0+
 */

namespace gplcart\modules\shippo\controllers;

use Exception;
use gplcart\core\controllers\backend\Controller;
use gplcart\core\models\Country;
use gplcart\core\models\Shipping;
use gplcart\modules\shippo\models\Api;

/**
 * Handles incoming requests and outputs data related to Shippo module
 */
class Settings extends Controller
{

    /**
     * Shippo API model instance
     * @var \gplcart\modules\shippo\models\Api $api
     */
    protected $api;

    /**
     * Country model instance
     * @var \gplcart\core\models\Country $country
     */
    protected $country;

    /**
     * Shipping model instance
     * @var \gplcart\core\models\Shipping $shipping
     */
    protected $shipping;

    /**
     * Settings constructor.
     * @param Shipping $shipping
     * @param Country $country
     * @param Api $api
     */
    public function __construct(Shipping $shipping, Country $country, Api $api)
    {
        parent::__construct();

        $this->api = $api;
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
        $this->setData('settings', $this->module->getSettings('shippo'));
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
        $title = $this->text('Edit %name settings', array('%name' => $this->text('Shippo')));
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
        try {

            $this->setSubmitted('settings');

            if ($this->api->isValidAddress($this->getSubmitted('sender')) !== true) {
                $this->setMessage($this->text("Shippo was unable to validate sender's address"), 'warning', true);
            }

        } catch (Exception $ex) {
            $this->setMessage($ex->getMessage(), 'warning', true);
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
