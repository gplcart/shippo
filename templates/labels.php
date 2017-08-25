<?php
/**
 * @package Shippo
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if(empty($orders)) { ?>
<?php echo $this->text('There are no items yet'); ?>
<?php } else { ?>
<div class="table-condensed">
  <table class="table shipping-labels">
    <thead>
      <tr>
        <th>
          <a href="<?php echo $sort_order_id; ?>">
            <?php echo $this->text('ID'); ?> <i class="fa fa-sort"></i>
          </a>
        </th>
        <th>
          <a href="<?php echo $sort_store_id; ?>">
            <?php echo $this->text('Store'); ?> <i class="fa fa-sort"></i>
          </a>
        </th>
        <th>
          <a href="<?php echo $sort_status; ?>">
            <?php echo $this->text('Status'); ?> <i class="fa fa-sort"></i>
          </a>
        </th>
        <th>
          <a href="<?php echo $sort_created; ?>">
            <?php echo $this->text('Created'); ?> <i class="fa fa-sort"></i>
          </a>
        </th>
        <th>
          <a href="<?php echo $sort_tracking_number; ?>">
            <?php echo $this->text('Tracking number'); ?> <i class="fa fa-sort"></i>
          </a>
        </th>
        <th>
          <?php echo $this->text('Label'); ?>
        </th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($orders as $id => $order) { ?>
      <tr>
        <td class="middle">
          <?php if ($this->access('order')) { ?>
          <a href="<?php echo $this->url("admin/sale/order/$id"); ?>"><?php echo $id; ?></a>
          <?php } else { ?>
          <?php echo $id; ?>
          <?php } ?>
        </td>
        <td class="middle">
          <?php if (isset($_stores[$order['store_id']])) { ?>
          <?php echo $this->e($_stores[$order['store_id']]['name']); ?>
          <?php } else { ?>
          <span class="text-danger"><?php echo $this->text('Unknown'); ?></span>
          <?php } ?>
        </td>
        <td class="middle">
          <?php if (isset($statuses[$order['status']])) { ?>
          <?php echo $this->e($statuses[$order['status']]); ?>
          <?php } else { ?>
          <span class="text-danger"><?php echo $this->text('Unknown'); ?></span>
          <?php } ?>
        </td>
        <td class="middle">
          <?php echo $this->date($order['created']); ?>
        </td>
        <td class="middle">
          <?php echo $this->e($order['tracking_number']); ?>
        </td>
        <td class="middle">
          <?php if (!empty($order['data']['shipping_label'])) { ?>
          <a target="_blank" href="<?php echo $this->e($order['data']['shipping_label']); ?>"><?php echo $this->text('Print'); ?></a>
          <?php } ?>
        </td>
        <td>
          <?php if (isset($order['data']['shippo']['object_id']) && $this->access('order_edit')) { ?>
          <ul class="list-inline">
            <li>
              <a onclick="return confirm(GplCart.text('Creating labels is a paid feature unless you operate in test mode. Please confirm'));" href="<?php echo $this->url('', array('get_label' => $order['data']['shippo']['object_id'], 'order_id' => $id)); ?>">
                <?php echo $this->text('get shipping label and tracking number'); ?>
              </a>
            </li>
          </ul>
          <?php } ?>
        </td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
</div>
<?php if (!empty($_pager)) { ?>
<?php echo $_pager; ?>
<?php } ?>
<?php } ?>