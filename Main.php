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
     * Implements hook "module.install.before"
     * @param mixed $result
     */
    public function hookModuleInstallBefore(&$result)
    {
        if (!extension_loaded('curl')) {
            $result = gplcart_text('CURL library is not enabled');
        }
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
        $this->getModel()->calculate($data);
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
        $this->setShippingMethods($methods);
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
     * Sets module shipping methods
     * @param array $methods
     */
    protected function setShippingMethods(array &$methods)
    {
        $settings = $this->module->getSettings('shippo');

        foreach ($this->getModel()->getServiceNames() as $id => $info) {
            list($carrier, $service) = $info;
            $methods["shippo_$id"] = array(
                'dynamic' => true,
                'module' => 'shippo',
                'status' => in_array("shippo_$id", $settings['enabled']),
                'title' => gplcart_text('@carrier - @service', array('@carrier' => $carrier, '@service' => $service))
            );
        }
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
