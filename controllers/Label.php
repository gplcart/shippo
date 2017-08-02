<?php

/**
 * @package Shippo
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2017, Iurii Makukh <gplcart.software@gmail.com>
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0+
 */

namespace gplcart\modules\shippo\controllers;

use gplcart\core\models\Order as OrderModel;
use gplcart\modules\shippo\models\Api as ModuleShippoModel;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to Shippo module
 */
class Label extends BackendController
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
     * @param OrderModel $order
     * @param ModuleShippoModel $shippo
     */
    public function __construct(OrderModel $order, ModuleShippoModel $api)
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

        $limit = $this->setPager($this->getTotalListLabel());

        $this->setData('orders', $this->getOrdersListLabel($limit));
        $this->setData('statuses', $this->order->getStatuses());

        $this->outputListLabel();
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
     * @param array $limit
     * @return array
     */
    protected function getOrdersListLabel(array $limit)
    {
        $options = array(
            'limit' => $limit,
            'shipping_prefix' => 'shippo_') + $this->query_filter;

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

        $order_id = $this->getQuery('order_id', '', 'string');
        $object_id = $this->getQuery('get_label', '', 'string');

        if (empty($object_id) || empty($order_id)) {
            return null;
        }

        $order = $this->order->get($order_id);

        if (empty($order['order_id'])) {
            return null;
        }

        $response = $this->api->getLabel($object_id);

        if (empty($response['tracking_number']) || empty($response['label_url'])) {
            $this->redirect('admin/tool/shippo', $this->text('An error occurred'), 'warning');
        }

        $order['data']['shipping_label'] = $response['label_url'];

        $data = array(
            'data' => $order['data'],
            'tracking_number' => $response['tracking_number']
        );

        $this->order->update($order_id, $data);
        $this->redirect('admin/tool/shippo', $this->text('Shipping label and tracking number have been saved'), 'success');
    }

    /**
     * Render and output the shipping label overview page
     */
    protected function outputListLabel()
    {
        $this->output('shippo|labels');
    }

}
