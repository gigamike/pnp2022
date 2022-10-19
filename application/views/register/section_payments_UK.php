<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="row">
    <div class="col-sm-12 m-t-md">
        <label>Payment Method</label>
    </div>
</div>

<!-- Skip -->
<?php
$crn_div_display_css = 'none';
$crn_div_is_disabled = ' disabled';

if (!isset($form_data['payment_method']) || (isset($form_data['payment_method']) && (int) $form_data['payment_method'] === (int) PAY_BY_SKIP)) {
    $is_checked = 'checked';
} else {
    $is_checked = '';
    $crn_div_display_css = 'block';
    $crn_div_is_disabled = '';
}
?>
<div class="row">
    <div class="form-group">
        <div class="col-sm-12">
            <div class="radio">
                <label><input type="radio" name="payment_method" attr-show-crn="0" value="<?php echo PAY_BY_SKIP; ?>" <?php echo $is_checked; ?>> <?php echo $this->config->item('mm8_payment_method')[PAY_BY_SKIP]; ?></label>
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

    $crn_div_display_css = 'block';
    $crn_div_is_disabled = '';
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
                <label><input type="radio" name="payment_method" attr-show-crn="1" value="<?php echo PAY_BY_BANK_TRANSFER; ?>" <?php echo $is_checked; ?>> <?php echo $this->config->item('mm8_payment_method')[PAY_BY_BANK_TRANSFER]; ?></label>
            </div>
        </div>
    </div>
</div>
<div id="userPaymentMethodDiv-<?php echo PAY_BY_BANK_TRANSFER; ?>" class="row payment-method-options" style="display:<?php echo $display_css; ?>">
    <div class="col-sm-10 col-sm-offset-1">
        <label>Account Name</label>
        <div class="form-group<?php if (form_error('bank_acc_name') != "") {
    echo " has-error";
} ?>">
            <input type="text" name="bank_acc_name" placeholder="Account Holder's Full Name" class="form-control input-lg" value="<?php echo isset($form_data['bank_acc_name']) ? $form_data['bank_acc_name'] : ''; ?>" <?php echo $is_disabled; ?>>
            <?php echo form_error('bank_acc_name'); ?>
        </div>
    </div>
    <div class="col-sm-10 col-sm-offset-1">
        <label>Account Number</label>
        <div class="form-group<?php if (form_error('bank_acc_no') != "") {
    echo " has-error";
} ?>">
            <input type="text" name="bank_acc_no" placeholder="Account Number" class="form-control input-lg" value="<?php echo isset($form_data['bank_acc_no']) ? $form_data['bank_acc_no'] : ''; ?>" <?php echo $is_disabled; ?>>
            <?php echo form_error('bank_acc_no'); ?>
        </div>
    </div>
    <div class="col-sm-10 col-sm-offset-1">
        <label>Sort Code</label>
        <div class="form-group<?php if (form_error('bank_sort_code') != "") {
    echo " has-error";
} ?>">
            <input type="text" name="bank_sort_code" placeholder="Sort Code" class="form-control input-lg" value="<?php echo isset($form_data['bank_sort_code']) ? $form_data['bank_sort_code'] : ''; ?>" <?php echo $is_disabled; ?>>
            <?php echo form_error('bank_sort_code'); ?>
        </div>
    </div>
</div>
<!-- Bank Transfer -->
<?php
//carry over the CRN from step 1 if CRN here is not available yet
if (isset($form_data['business_crn']) && !empty($form_data['business_crn']) && (!isset($form_data['user_crn']) || empty($form_data['user_crn']))) {
    $form_data['user_crn'] = $form_data['business_crn'];
}
?>
<div class="row" id="userCRNDiv" style="display:<?php echo $crn_div_display_css; ?>">
    <div class="col-sm-12 m-t-md">
        <label>Administrator's CRN <a href="javascript:void(0);" data-toggle="tooltip" data-placement="top" data-container="body" title="Please provide the CRN of the person or entity being paid <?php echo $this->config->item('mm8_system_name'); ?> Rewards."><i class="fa fa-info-circle"></i></a></label>
        <div class="form-group<?php if (form_error('user_crn') != "") {
    echo " has-error";
} ?>">
            <input type="text" name="user_crn" placeholder="Company Registration Number (CRN)" class="form-control input-lg" value="<?php echo isset($form_data['user_crn']) ? $form_data['user_crn'] : ""; ?>" <?php echo $crn_div_is_disabled; ?> required>
            <?php echo form_error('user_crn'); ?>
        </div>
    </div>
</div>