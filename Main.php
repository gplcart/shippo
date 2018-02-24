<?php

/**
 * @package Shippo
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2017, Iurii Makukh <gplcart.software@gmail.com>
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0+
 */

namespace gplcart\modules\shippo;

use gplcart\core\Container;
use gplcart\core\Library;
use gplcart\core\Module;

/**
 * Main class for Shippo module
 */
class Main
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
     * @param Module $module
     * @param Library $library
     */
    public function __construct(Module $module, Library $library)
    {
        $this->module = $module;
        $this->library = $library;
    }

    /**
     * Implements hook "library.list"
     * @param array $libraries
     */
    public function hookLibraryList(array &$libraries)
    {
        $libraries['shippo'] = array(
            'name' => 'Shippo', // @text
            'description' => 'Shipping API PHP library (USPS, FedEx, UPS and more)', // @text
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
            'menu' => array(
                'admin' => 'Shipping labels' // @text
            ),
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
        $permissions['shippo_label'] = 'Shippo: view and buy labels'; // @text
    }

    /**
     * Implements hook "order.calculate.before"
     * @param mixed $data
     */
    public function hookOrderCalculateBefore(array &$data)
    {
        if (!empty($data['request_shipping_methods'])) {
            $this->calculate($data);
        }
    }

    /**
     * Implements hook "order.submit.before"
     * @param array $order
     * @param array $options
     * @param array $result
     */
    public function hookOrderSubmitBefore(&$order, $options, &$result)
    {
        $this->getModel()->validate($order, $options, $result);
    }

    /**
     * Implements hook "shipping.methods"
     * @param mixed $methods
     */
    public function hookShippingMethods(array &$methods)
    {
        $methods = array_merge($methods, $this->getShippingMethods());
    }

    /**
     * Implements hook "module.enable.after"
     */
    public function hookModuleEnableAfter()
    {
        $this->library->clearCache();
    }

    /**
     * Implements hook "module.disable.after"
     */
    public function hookModuleDisableAfter()
    {
        $this->library->clearCache();
    }

    /**
     * Implements hook "module.install.after"
     */
    public function hookModuleInstallAfter()
    {
        $this->library->clearCache();
    }

    /**
     * Implements hook "module.uninstall.after"
     */
    public function hookModuleUninstallAfter()
    {
        $this->library->clearCache();
    }

    /**
     * Returns an array of Shippo's shipping methods
     * @param bool $only_enabled
     * @return array
     */
    public function getShippingMethods($only_enabled = true)
    {
        $settings = $this->module->getSettings('shippo');

        $methods = array();

        foreach ($this->getModel()->getServiceNames() as $id => $info) {
            list($carrier, $service) = $info;
            $methods["shippo_$id"] = array(
                'dynamic' => true,
                'module' => 'shippo',
                'status' => $only_enabled ? in_array("shippo_$id", $settings['enabled']) : null,
                'title' => gplcart_text('@carrier - @service', array('@carrier' => $carrier, '@service' => $service))
            );
        }

        return $methods;
    }

    /**
     * Calculate shipping
     * @param $data
     */
    public function calculate(&$data)
    {
        $this->getModel()->calculate($data);
    }

    /**
     * Returns Shippo's model instance
     * @return \gplcart\modules\shippo\models\Shippo
     */
    public function getModel()
    {
        /** @var \gplcart\modules\shippo\models\Shippo $instance */
        $instance = Container::get('gplcart\\modules\\shippo\\models\\Shippo');
        return $instance;
    }

}
