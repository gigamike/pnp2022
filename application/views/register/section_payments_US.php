<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="row">
    <div class="col-sm-12 m-t-md">
        <label>Payment Method</label>
    </div>
</div>

<!-- Skip -->
<?php
if (!isset($form_data['payment_method']) || (isset($form_data['payment_method']) && (int) $form_data['payment_method'] === (int) PAY_BY_SKIP)) {
    $is_checked = 'checked';
} else {
    $is_checked = '';
}
?>
<div class="row">
    <div class="form-group">
        <div class="col-sm-12">
            <div class="radio">
                <label><input type="radio" name="payment_method" value="<?php echo PAY_BY_SKIP; ?>" <?php echo $is_checked; ?>> <?php echo $this->config->item('mm8_payment_method')[PAY_BY_SKIP]; ?></label>
            </div>
        </div>
    </div>
</div>
<!-- Skip -->
<!-- Bank Transfer -->
<?php
if (isset($form_data['payment_method']) && (int) $form_data['payment_method'] === (int) PAY_BY_BANK_TRANSFER) {
    $is_checked = 'checked';
    $display_css = 'block';
    $is_disabled = '';
} else {
    $is_checked = '';
    $display_css = 'none';
    $is_disabled = 'disabled';
}
?>
<div class="row">
    <div class="form-group">
        <div class="col-sm-12">
            <div class="radio">
                <label><input type="radio" name="payment_method" value="<?php echo PAY_BY_BANK_TRANSFER; ?>" <?php echo $is_checked; ?>> <?php echo $this->config->item('mm8_payment_method')[PAY_BY_BANK_TRANSFER]; ?></label>
            </div>
        </div>
    </div>
</div>
<div id="userPaymentMethodDiv-<?php echo PAY_BY_BANK_TRANSFER; ?>" class="row payment-method-options" style="display:<?php echo $display_css; ?>">
    <div class="col-sm-10 col-sm-offset-1">
        <label>Bank Name</label>
        <div class="form-group<?php if (form_error('bank_name') != "") echo " has-error"; ?>">
            <input type="text" name="bank_name" placeholder="Bank Name" class="form-control input-lg" value="<?php echo isset($form_data['bank_name']) ? $form_data['bank_name'] : ''; ?>" <?php echo $is_disabled; ?>>
            <?php echo form_error('bank_name'); ?>
        </div>
    </div>
    <div class="col-sm-10 col-sm-offset-1">
        <label>Account Name</label>
        <div class="form-group<?php if (form_error('bank_acc_name') != "") echo " has-error"; ?>">
            <input type="text" name="bank_acc_name" placeholder="Account Holder's Full Name" class="form-control input-lg" value="<?php echo isset($form_data['bank_acc_name']) ? $form_data['bank_acc_name'] : ''; ?>" <?php echo $is_disabled; ?>>
            <?php echo form_error('bank_acc_name'); ?>
        </div>
    </div>
    <div class="col-sm-10 col-sm-offset-1">
        <label>Account Number</label>
        <div class="form-group<?php if (form_error('bank_acc_no') != "") echo " has-error"; ?>">
            <input type="text" name="bank_acc_no" placeholder="Account Number" class="form-control input-lg" value="<?php echo isset($form_data['bank_acc_no']) ? $form_data['bank_acc_no'] : ''; ?>" <?php echo $is_disabled; ?>>
            <?php echo form_error('bank_acc_no'); ?>
        </div>
    </div>
    <div class="col-sm-10 col-sm-offset-1">
        <label>Routing Number</label>
        <div class="form-group<?php if (form_error('bank_routing_number') != "") echo " has-error"; ?>">
            <input type="text" name="bank_routing_number" placeholder="Routing Number" class="form-control input-lg" value="<?php echo isset($form_data['bank_routing_number']) ? $form_data['bank_routing_number'] : ''; ?>" <?php echo $is_disabled; ?>>
            <?php echo form_error('bank_routing_number'); ?>
        </div>
    </div>
</div>
<!-- Bank Transfer -->
