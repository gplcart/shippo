<?php

/**
 * @package Shippo
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2017, Iurii Makukh <gplcart.software@gmail.com>
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0+
 */

namespace gplcart\modules\shippo;

use gplcart\core\Module;

/**
 * Main class for Shippo module
 */
class Shippo extends Module
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Implements hook "library.list"
     * @param array $libraries
     */
    public function hookLibraryList(array &$libraries)
    {
        $libraries['shippo'] = array(
            'name' => 'Shippo',
            'description' => 'Shipping API PHP library (USPS, FedEx, UPS and more)',
            'url' => 'https://github.com/goshippo/shippo-php-client',
            'download' => 'https://github.com/goshippo/shippo-php-client/archive/v1.3.2.zip',
            'type' => 'php',
            'version_source' => array(
                'lines' => 2,
                'pattern' => '/.*(\\d+\\.+\\d+\\.+\\d+)/',
                'file' => 'vendor/shippo/shippo-php/VERSION'
            ),
            'module' => 'shippo',
            'files' => array(
                'vendor/autoload.php'
            )
        );
    }

    /**
     * Implements hook "route.list"
     * @param array $routes
     */
    public function hookRouteList(array &$routes)
    {
        $routes['admin/module/settings/shippo'] = array(
            'access' => 'module_edit',
            'handlers' => array(
                'controller' => array('gplcart\\modules\\shippo\\controllers\\Settings', 'editSettings')
            )
        );

        $routes['admin/tool/shippo'] = array(
            'access' => 'shippo_label',
            'menu' => array('admin' => 'Shipping labels'),
            'handlers' => array(
                'controller' => array('gplcart\\modules\\shippo\\controllers\\Label', 'listLabel')
            )
        );
    }

    /**
     * Implements hook "user.role.permissions"
     * @param array $permissions
     */
    public function hookUserRolePermissions(array &$permissions)
    {
        $permissions['shippo_label'] = 'Shippo: view and buy labels';
    }

    /**
     * Implements hook "order.calculate.before"
     * @param mixed $data
     */
    public function hookOrderCalculateBefore(array &$data)
    {
        $this->getShippoModel()->calculate($data);
    }

    /**
     * Implements hook "order.submit.before"
     * @param array $order
     * @param array $o
     * @param array $result
     */
    public function hookOrderSubmitBefore(array &$order, $o, array &$result)
    {
        $this->getShippoModel()->validate($order, $result);
    }

    /**
     * Implements hook "shipping.methods"
     * @param mixed $methods
     */
    public function hookShippingMethods(array &$methods)
    {
        $settings = $this->config->module('shippo');

        foreach ($this->getShippoModel()->getServiceNames() as $id => $service) {
            $methods["shippo_$id"] = array(
                'dynamic' => true,
                'module' => 'shippo',
                'title' => implode(' - ', $service),
                'status' => in_array("shippo_$id", $settings['enabled'])
            );
        }
    }

    /**
     * Implements hook "module.enable.after"
     */
    public function hookModuleEnableAfter()
    {
        $this->getLibrary()->clearCache();
    }

    /**
     * Implements hook "module.disable.after"
     */
    public function hookModuleDisableAfter()
    {
        $this->getLibrary()->clearCache();
    }

    /**
     * Implements hook "module.install.after"
     */
    public function hookModuleInstallAfter()
    {
        $this->getLibrary()->clearCache();
    }

    /**
     * Implements hook "module.uninstall.after"
     */
    public function hookModuleUninstallAfter()
    {
        $this->getLibrary()->clearCache();
    }

    /**
     * Implements hook "module.install.before"
     * @param mixed $result
     */
    public function hookModuleInstallBefore(&$result)
    {
        if (!function_exists('curl_init')) {
            $result = 'CURL library is not enabled';
        }
    }

    /**
     * Returns Shippo model instance
     * @return \gplcart\modules\shippo\models\Shippo
     */
    public function getShippoModel()
    {
        return $this->getModel('Shippo', 'shippo');
    }

}
