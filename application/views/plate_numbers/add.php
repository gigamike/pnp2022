<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<form id="plateNumberForm">

    <div class="row wrapper">
        <div class="col-sm-12">
            <a href="<?php echo base_url() . "plate-numbers"; ?>" class="m-l-n-sm text-navy">
                <i class="fa fa-angle-left m-r-xs"></i> Plate Numbers
            </a>
        </div>
        <div class="col-sm-12">
            <h3>Add Plate Number</h3>
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
                            <label class="control-label">Plate Number<span class="text-danger">*</span></label>
                            <input type="text" name="plate_number" id="plate_number" class="form-control input-lg" value="" required>
                        </div><!-- column -->
                        <div class="col-sm-6">
                            <?php echo get_kb_field_explainer('plate_number'); ?>
                        </div><!-- column -->
                    </div>

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
                            <label class="control-label">Class<span class="text-danger">*</span></label>
                            <select class="form-control input-lg" id="class" name="class" required>
                                <option value="" disabled selected>Select</option>
                                <option value="private">private</option>
                                <option value="private">public</option>
                                <option value="government">government</option>
                                <option value="diplomat">diplomat</option>
                                <option value="other">other</option>
                            </select>
                        </div><!-- column -->
                        <div class="col-sm-6">
                            <?php echo get_kb_field_explainer('class'); ?>
                        </div><!-- column -->
                    </div>

                    <div class="row form-group">
                        <div class="col-sm-6">
                            <label class="control-label">Region<span class="text-danger">*</span></label>
                            <select class="form-control input-lg" id="region_id" name="region_id" required>
                                <option value="" disabled selected>Select</option>
                                <?php if (count($regions) > 0): ?>
                                    <?php foreach ($regions as $region): ?>
                                <option value="<?php echo $region->id; ?>"><?php echo $region->region; ?> (<?php echo $region->region_name; ?>)</option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div><!-- column -->
                        <div class="col-sm-6">
                            <?php echo get_kb_field_explainer('class'); ?>
                        </div><!-- column -->
                    </div>

                    <div class="row form-group">
                        <div class="col-sm-6">
                            <label class="control-label">First Name</label>
                            <input type="text" name="first_name" id="first_name" class="form-control input-lg" value="">
                        </div><!-- column -->
                        <div class="col-sm-6">
                            <?php echo get_kb_field_explainer('first_name'); ?>
                        </div><!-- column -->
                    </div>

                    <div class="row form-group">
                        <div class="col-sm-6">
                            <label class="control-label">Last Name</label>
                            <input type="text" name="last_name" id="last_name" class="form-control input-lg" value="">
                        </div><!-- column -->
                        <div class="col-sm-6">
                            <?php echo get_kb_field_explainer('last_name'); ?>
                        </div><!-- column -->
                    </div>

                    <div class="row form-group">
                        <div class="col-sm-6">
                            <label class="control-label">Address</label>
                            <textarea name="address" id="address" class="form-control input-lg"></textarea>
                        </div><!-- column -->
                        <div class="col-sm-6">
                            <?php echo get_kb_field_explainer('address'); ?>
                        </div><!-- column -->
                    </div>

                    <div class="row form-group">
                        <div class="col-sm-6">
                            <label class="control-label">Last Registration Date</label>
                            <input type="text" name="last_registration_date" id="last_registration_date" class="form-control input-lg datepicker" value="">
                        </div><!-- column -->
                        <div class="col-sm-6">
                            <?php echo get_kb_field_explainer('cr_no'); ?>
                        </div><!-- column -->
                    </div>

                    <div class="row form-group">
                        <div class="col-sm-6">
                            <label class="control-label">CR Number</label>
                            <input type="text" name="cr_no" id="cr_no" class="form-control input-lg" value="">
                        </div><!-- column -->
                        <div class="col-sm-6">
                            <?php echo get_kb_field_explainer('cr_no'); ?>
                        </div><!-- column -->
                    </div>

                    <div class="row form-group">
                        <div class="col-sm-6">
                            <label class="control-label">Comments<span class="text-danger">*</span></label>
                            <textarea name="comments" id="comments" class="form-control input-lg" required></textarea>
                        </div><!-- column -->
                        <div class="col-sm-6">
                            <?php echo get_kb_field_explainer('comments'); ?>
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


