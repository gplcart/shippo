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
use gplcart\core\models\Order;
use gplcart\modules\shippo\models\Api;

/**
 * Handles incoming requests and outputs data related to Shippo module
 */
class Label extends Controller
{

    /**
     * Shippo Api model instance
     * @var \gplcart\modules\shippo\models\Api $api
     */
    protected $api;

    /**
     * Order model instance
     * @var \gplcart\core\models\Order $order
     */
    protected $order;

    /**
     * @var array
     */
    protected $data_limit;

    /**
     * Label constructor.
     * @param Order $order
     * @param Api $api
     */
    public function __construct(Order $order, Api $api)
    {
        parent::__construct();

        $this->api = $api;
        $this->order = $order;
    }

    /**
     * Route page callback to display the shipping label overview page
     */
    public function listLabel()
    {
        $this->actionLabel();
        $this->setTitleListLabel();
        $this->setBreadcrumbListLabel();
        $this->setFilterListLabel();
        $this->setPagerLabel();

        $this->setData('orders', $this->getOrderListLabel());
        $this->setData('statuses', $this->order->getStatuses());
        $this->outputListLabel();
    }

    /**
     * Sets pager
     */
    protected function setPagerLabel()
    {
        $this->data_limit = $this->setPager(array('total' => $this->getTotalListLabel()));
    }

    /**
     * Set filter on the label overview page
     */
    protected function setFilterListLabel()
    {
        $allowed = array('store_id', 'order_id', 'status', 'created', 'tracking_number');
        $this->setFilter($allowed);
    }

    /**
     * Returns an array of Shippo shipping methods
     * @return array
     */
    protected function getOrderListLabel()
    {
        $options = $this->query_filter;
        $options['limit'] = $this->data_limit;
        $options['shipping_prefix'] = 'shippo_';

        return (array) $this->order->getList($options);
    }

    /**
     * Returns total number of orders
     * @return integer
     */
    protected function getTotalListLabel()
    {
        $options = array(
            'count' => true,
            'shipping_prefix' => 'shippo_'
        );

        return (int) $this->order->getList($options);
    }

    /**
     * Set title on the shipping label overview page
     */
    protected function setTitleListLabel()
    {
        $this->setTitle('Shipping labels');
    }

    /**
     * Set breadcrumbs on the shipping label overview page
     */
    protected function setBreadcrumbListLabel()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')
        );

        $breadcrumbs[] = array(
            'text' => $this->text('Orders'),
            'url' => $this->url('admin/sale/order')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Handles different actions
     */
    protected function actionLabel()
    {
        $this->controlAccess('order_edit');

        $order_id = $this->getQuery('order_id');
        $object_id = $this->getQuery('get_label');

        if (empty($object_id) || empty($order_id)) {
            return null;
        }

        $order = $this->order->get($order_id);

        if (empty($order['order_id'])) {
            return null;
        }

        try {
            $response = $this->api->getLabel($object_id);
        } catch (Exception $ex) {
            $this->redirect('admin/tool/shippo', $ex->getMessage(), 'warning');
        }

        if (empty($response['tracking_number']) || empty($response['label_url'])) {
            $this->redirect('admin/tool/shippo', $this->text('An error occurred'), 'warning');
        }

        $order['data']['shipping_label'] = $response['label_url'];

        $data = array(
            'data' => $order['data'],
            'tracking_number' => $response['tracking_number']
        );

        $this->order->update($order_id, $data);

        $vars = array(
            '@url' => $response['label_url'],
            '%num' => $response['tracking_number']
        );

        $message = $this->text('Label has been <a target="_blank" href="@url">created</a>. Tracking number: %num', $vars);
        $this->redirect('admin/tool/shippo', $message, 'success');
    }

    /**
     * Render and output the shipping label overview page
     */
    protected function outputListLabel()
    {
        $this->output('shippo|labels');
    }

}
