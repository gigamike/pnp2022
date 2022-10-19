<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<form id="userForm">
    <input type="hidden" name="user_id" id="user_id" value="<?php echo $user->id; ?>">

    <div class="row wrapper">
        <div class="col-sm-12">
            <a href="<?php echo base_url() . "users"; ?>" class="m-l-n-sm text-navy">
                <i class="fa fa-angle-left m-r-xs"></i> Users
            </a>
        </div>
        <div class="col-sm-12">
            <h3>Update User</h3>
        </div>
    </div>

    <!-- content wrapper start -->
    <div class="row wrapper wrapper-content">
        <div class="col-sm-12">

            <?php echo isset($kb_explainer) ? $kb_explainer : ""; ?>

            <div class="ibox float-e-margins white-bg">
                <div class="ibox-content">

                    <div class="row form-group">
                        <div class="col-sm-6">
                            <label class="control-label">First name<span class="text-danger">*</span></label>
                            <input type="text" name="first_name" id="first_name" class="form-control input-lg" value="<?php echo $user->first_name; ?>" required>
                        </div><!-- column -->
                        <div class="col-sm-6">
                            <?php echo get_kb_field_explainer('first_name'); ?>
                        </div><!-- column -->
                    </div>

                    <div class="row form-group">
                        <div class="col-sm-6">
                            <label class="control-label">Last name<span class="text-danger">*</span></label>
                            <input type="text" name="last_name" id="last_name" class="form-control input-lg" value="<?php echo $user->last_name; ?>" required>
                        </div><!-- column -->
                        <div class="col-sm-6">
                            <?php echo get_kb_field_explainer('last_name'); ?>
                        </div><!-- column -->
                    </div>

                     <div class="row form-group">
                        <div class="col-sm-6">
                            <label class="control-label">Email<span class="text-danger">*</span></label>
                            <input type="email" name="email" id="email" class="form-control input-lg" value="<?php echo $user->email; ?>" required>
                        </div><!-- column -->
                        <div class="col-sm-6">
                            <?php echo get_kb_field_explainer('email'); ?>
                        </div><!-- column -->
                    </div>

                     <div class="row form-group">
                        <div class="col-sm-6">
                            <label class="control-label">Mobile Phone<span class="text-danger">*</span></label>
                            <input type="text" name="mobile_phone" id="mobile_phone" class="form-control input-lg" value="<?php echo $user->mobile_phone; ?>" required>
                        </div><!-- column -->
                        <div class="col-sm-6">
                            <?php echo get_kb_field_explainer('mobile_phone'); ?>
                        </div><!-- column -->
                    </div>

                    <div class="row form-group">
                        <div class="col-sm-6">
                            <label class="control-label">Position<span class="text-danger">*</span></label>
                            <input type="text" name="position" id="position" class="form-control input-lg" value="<?php echo $user->position; ?>" required>
                        </div><!-- column -->
                        <div class="col-sm-6">
                            <?php echo get_kb_field_explainer('position'); ?>
                        </div><!-- column -->
                    </div>

                    <div class="row form-group">
                        <div class="col-sm-6">
                            <label class="control-label">Assign PI Devices</label>
                            <select name="devices[]" id="devices" class="select2-input form-control input-sm filter-set filter-value" style="width:100%;" multiple>
                                <?php if (count($devices) > 0): ?>
                                    <?php foreach ($devices as $device): ?>
                                <option value="<?php echo $device->id; ?>" <?php if (in_array($device->id, $assign_devices)): ?>selected<?php endif; ?>><?php echo $device->location; ?> (<?php echo $device->u_code; ?>)</option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div><!-- column -->
                        <div class="col-sm-6">
                            <?php echo get_kb_field_explainer('devices'); ?>
                        </div><!-- column -->
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <button id="saveBtn" class="btn btn-md btn-primary btn-w-s m-b-xs" type="button">Save</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</form>


