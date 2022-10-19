<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<form id="piDeviceForm">

    <div class="row wrapper">
        <div class="col-sm-12">
            <a href="<?php echo base_url() . "pi-devices"; ?>" class="m-l-n-sm text-navy">
                <i class="fa fa-angle-left m-r-xs"></i> PI Devices/Inventory
            </a>
        </div>
        <div class="col-sm-12">
            <h3>Add PI Device</h3>
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
                            <label class="control-label">Tracking Type<span class="text-danger">*</span></label>
                            <select class="form-control input-lg" id="tracking_type" name="tracking_type" required>
                                <option value="" disabled selected>Select</option>
                                <option value="hotlist">Hotlist</option>
                                <option value="whitelist">Whitelist</option>
                            </select>
                        </div><!-- column -->
                        <div class="col-sm-6">
                            <?php echo get_kb_field_explainer('tracking_type'); ?>
                        </div><!-- column -->
                    </div>

                    <div class="row form-group">
                        <div class="col-sm-6">
                            <label class="control-label">Location<span class="text-danger">*</span></label>
                            <textarea name="location" id="location" class="form-control input-lg" required></textarea>
                        </div><!-- column -->
                        <div class="col-sm-6">
                            <?php echo get_kb_field_explainer('location'); ?>
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


