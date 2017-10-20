<?php
/**
 * @package Shippo
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2017, Iurii Makukh <gplcart.software@gmail.com>
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0+
 */
?>
<form method="post" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <fieldset>
    <div class="form-group">
      <div class="col-md-6 col-md-offset-2">
        <div class="checkbox">
          <label>
            <input name="settings[test]" type="checkbox"<?php echo empty($settings['test']) ? '' : ' checked'; ?>> <?php echo $this->text('Test mode'); ?>
            <div class="help-block"><?php echo $this->text('If selected all requests to Shippo will be made with the test token and will not be charged'); ?></div>
          </label>
        </div>
      </div>
    </div>
  </fieldset>
  <fieldset>
    <legend><?php echo $this->text('Credentials'); ?></legend>
    <div class="form-group">
      <label class="col-md-2 control-label"><?php echo $this->text('Test token'); ?></label>
      <div class="col-md-4">
        <input name="settings[key][test]" class="form-control" value="<?php echo $this->e($settings['key']['test']); ?>">
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-2 control-label"><?php echo $this->text('Live token'); ?></label>
      <div class="col-md-4">
        <input name="settings[key][live]" class="form-control" value="<?php echo $this->e($settings['key']['live']); ?>">
      </div>
    </div>
    <div class="form-group">
      <div class="col-md-10 col-md-offset-2">
        <div class="help-block"><?php echo $this->text('In order to use Shippo\'s API you have to register an account on https://goshippo.com and obtain the API tokens'); ?></div>
      </div>
    </div>
  </fieldset>
  <fieldset>
    <legend><?php echo $this->text('Sender'); ?></legend>
    <div class="form-group">
      <label class="col-md-2 control-label"><?php echo $this->text('Name'); ?></label>
      <div class="col-md-4">
        <input name="settings[sender][name]" class="form-control" value="<?php echo $this->e($settings['sender']['name']); ?>">
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-2 control-label"><?php echo $this->text('Company'); ?></label>
      <div class="col-md-4">
        <input name="settings[sender][company]" class="form-control" value="<?php echo $this->e($settings['sender']['company']); ?>">
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-2 control-label"><?php echo $this->text('Street'); ?></label>
      <div class="col-md-4">
        <input name="settings[sender][street1]" class="form-control" value="<?php echo $this->e($settings['sender']['street1']); ?>">
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-2 control-label"><?php echo $this->text('City'); ?></label>
      <div class="col-md-4">
        <input name="settings[sender][city]" class="form-control" value="<?php echo $this->e($settings['sender']['city']); ?>">
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-2 control-label"><?php echo $this->text('State'); ?></label>
      <div class="col-md-4">
        <input name="settings[sender][state]" class="form-control" value="<?php echo $this->e($settings['sender']['state']); ?>">
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-2 control-label"><?php echo $this->text('ZIP'); ?></label>
      <div class="col-md-4">
        <input name="settings[sender][zip]" class="form-control" value="<?php echo $this->e($settings['sender']['zip']); ?>">
      </div>
    </div>
    <div class="form-group<?php echo $this->error('sender.country', ' has-error'); ?>">
      <label class="col-md-2 control-label"><?php echo $this->text('Country'); ?></label>
      <div class="col-md-4">
        <select name="settings[sender][country]" class="form-control">
          <?php foreach ($countries as $code => $country) { ?>
          <option value="<?php echo $this->e($code); ?>"<?php echo $settings['sender']['country'] == $code ? ' selected' : ''; ?>><?php echo $this->e($country['name']); ?></option>
          <?php } ?>
        </select>
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-2 control-label"><?php echo $this->text('Phone'); ?></label>
      <div class="col-md-4">
        <input name="settings[sender][phone]" class="form-control" value="<?php echo $this->e($settings['sender']['phone']); ?>">
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-2 control-label"><?php echo $this->text('E-mail'); ?></label>
      <div class="col-md-4">
        <input name="settings[sender][email]" class="form-control" value="<?php echo $this->e($settings['sender']['email']); ?>">
      </div>
    </div>
    <div class="form-group">
      <div class="col-md-10 col-md-offset-2">
        <div class="help-block"><?php echo $this->text('The address will be used to determine sender\'s location and calculate shipping rates'); ?></div>
      </div>
    </div>
  </fieldset>
  <fieldset>
    <legend><?php echo $this->text('Dimensions'); ?></legend>
    <div class="form-group">
      <label class="col-md-2 control-label"><?php echo $this->text('Width'); ?></label>
      <div class="col-md-4">
        <input name="settings[default][width]" class="form-control" value="<?php echo $this->e($settings['default']['width']); ?>">
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-2 control-label"><?php echo $this->text('Height'); ?></label>
      <div class="col-md-4">
        <input name="settings[default][height]" class="form-control" value="<?php echo $this->e($settings['default']['height']); ?>">
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-2 control-label"><?php echo $this->text('Length'); ?></label>
      <div class="col-md-4">
        <input name="settings[default][length]" class="form-control" value="<?php echo $this->e($settings['default']['length']); ?>">
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-2 control-label"><?php echo $this->text('Weight'); ?></label>
      <div class="col-md-4">
        <input name="settings[default][weight]" class="form-control" value="<?php echo $this->e($settings['default']['weight']); ?>">
      </div>
    </div>
    <div class="form-group">
      <div class="col-md-10 col-md-offset-2">
        <div class="help-block">
          <?php echo $this->text('If order dimensions are unavailable or look incorrect then send to Shippo these default dimensions to calculate shipping rates'); ?>
        </div>
      </div>
    </div>
  </fieldset>
  <fieldset>
    <legend><?php echo $this->text('Services'); ?></legend>
    <div class="form-group">
      <label class="col-md-2 control-label"><?php echo $this->text('Default price'); ?></label>
      <div class="col-md-4">
        <div class="input-group">
          <span class="input-group-addon" id="basic-addon1">USD</span>
          <input name="settings[default][price]" class="form-control" value="<?php echo $this->e($settings['default']['price']); ?>">
        </div>
        <div class="help-block"><?php echo $this->text('If Shippo failed to calculate shipping rates and default method is enabled and shown, use this amount as a shipping cost'); ?></div>
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-2 control-label"><?php echo $this->text('Available'); ?></label>
      <div class="col-md-10">
        <table class="table table-condensed table-bordered">
          <thead>
            <tr>
              <td><?php echo $this->text('Method'); ?></td>
              <td><input type="checkbox" id="check-all"> <?php echo $this->text('Available'); ?></td>
              <td><input name="settings[default][method]" value="" type="radio"> <?php echo $this->text('Default'); ?></td>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($methods as $id => $method) { ?>
            <tr>
              <td>
                <?php echo $this->e($method['title']); ?>
              </td>
              <td>
                <input name="settings[enabled][]" value="<?php echo $this->e($id); ?>" type="checkbox"<?php echo in_array($id, $settings['enabled']) ? 'checked' : ''; ?>>
              </td>
              <td>
                <input name="settings[default][method]" value="<?php echo $this->e($id); ?>" type="radio"<?php echo $settings['default']['method'] === $id ? 'checked' : ''; ?>>
              </td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
        <div class="help-block"><?php echo $this->text('Select which shipping services will be potentially available to customers during checkout. If Shippo failed to calculate shipping rates a default method will be shown instead (if selected)'); ?></div>
      </div>
    </div>
    <div class="form-group">
      <div class="col-md-4 col-md-offset-2">
        <div class="btn-toolbar">
          <a href="<?php echo $this->url("admin/module/list"); ?>" class="btn btn-default"><?php echo $this->text('Cancel'); ?></a>
          <button class="btn btn-default save" name="save" value="1"><?php echo $this->text('Save'); ?></button>
        </div>
      </div>
    </div>
  </fieldset>
</form>
<script>
    $(function () {
        $("#check-all").click(function () {
            $('[name="settings[enabled][]"]').prop('checked', this.checked);
        });

        $('[name="settings[default][method]"]').click(function(){
            if($(this).is(':checked')){
                $(this).closest('tr').find('[name="settings[enabled][]"]').prop('checked', true);
            }
        });
    });
</script>