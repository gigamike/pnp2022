<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<form id="ticketForm" role="form" method="POST">
    <div class="row wrapper">
        <div class="col-sm-12">
            <a href="<?php echo base_url() . 'connect-sd/tickets/view'; ?>" class="m-l-n-sm text-navy">
                <i class="fa fa-angle-left m-r-xs"></i> My Tickects
            </a>
        </div>
        <div class="col-sm-6">
            <h3>Add Ticket</h3>
        </div>
        <div class="col-sm-6 text-right">
            <div class="form-group">
                <?php echo isset($kb_toggler) ? $kb_toggler : ""; ?>
            </div>
        </div>
    </div>
    <!-- content wrapper start -->
    <div class="row wrapper wrapper-content">
        <div class="col-sm-12">

            <?php echo isset($kb_explainer) ? $kb_explainer : ""; ?>

            <div class="ibox float-e-margins white-bg">
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-sm-12">

                            <div class="row form-group">
                                <div class="col-sm-6">
                                    <label class="control-label">Category<span class="text-danger">*</span></label>
                                    <select name="ticket_category_id" id="ticket_category_id" class="form-control select2-input input-lg filter-set filter-value" style="width:100%;" required>
                                        <?php foreach ($ticket_categories as $ticket_category): ?>
                                        <option value="<?php echo $ticket_category->id; ?>">
                                            <?php echo $ticket_category->name; ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div><!-- column -->
                                <div class="col-sm-6">
                                    <?php echo get_kb_field_explainer('ticket_category'); ?>
                                </div><!-- column -->
                            </div>

                            <div id="ticketCategoryWrapper"></div>

                            <div class="row form-group">
                                <div class="col-sm-6">
                                    <label class="control-label">Problem Overview<span class="text-danger">*</span></label>
                                    <input type="text" id="subject" name="subject" placeholder="Subject" class="form-control input-lg" value="" required>
                                </div><!-- column -->
                                <div class="col-sm-6">
                                    <?php echo get_kb_field_explainer('subject'); ?>
                                </div><!-- column -->
                            </div>

                            <div class="row form-group">
                                <div class="col-sm-6">
                                    <label class="control-label">Description<span class="text-danger">*</span></label>
                                    <textarea class="tinymce" name="body" id="body"></textarea>
                                </div><!-- column -->
                                <div class="col-sm-6">
                                    <?php echo get_kb_field_explainer('subject'); ?>
                                </div><!-- column -->
                            </div>

                            <div class="row form-group">
                                <div class="col-sm-6">
                                    <label class="control-label">Urgency<span class="text-danger">*</span></label>
                                    <select name="urgency" id="urgency" class="form-control select2-input input-lg filter-set filter-value" style="width:100%;" required>
                                        <?php foreach ($this->config->item('mm8_connect_sd_ticket_urgency') as $k => $v): ?>
                                        <option value="<?php echo $k; ?>">
                                            <?php echo $this->config->item('mm8_connect_ticket_urgency')[$k]; ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div><!-- column -->
                                <div class="col-sm-6">
                                    <?php echo get_kb_field_explainer('urgency'); ?>
                                </div><!-- column -->
                            </div>

                            <div class="row form-group">
                                <div class="col-sm-6">
                                    <label class="control-label">Impact<span class="text-danger">*</span></label>
                                    <select name="impact" id="impact" class="form-control select2-input input-lg filter-set filter-value" style="width:100%;" required>
                                        <?php foreach ($this->config->item('mm8_connect_sd_ticket_impact') as $k => $v): ?>
                                        <option value="<?php echo $k; ?>">
                                            <?php echo $this->config->item('mm8_connect_sd_ticket_impact')[$k]; ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div><!-- column -->
                                <div class="col-sm-6">
                                    <?php echo get_kb_field_explainer('impact'); ?>
                                </div><!-- column -->
                            </div>

                            <div class="row form-group">
                                <div class="col-sm-6">
                                    <label class="control-label">Tags</label>
                                    <input type="text" name="tags" id="tags" placeholder="Tags" class="tagsinput form-control input-lg" value="">
                                </div><!-- column -->
                                <div class="col-sm-6">
                                    <?php echo get_kb_field_explainer('tags'); ?>
                                </div><!-- column -->
                            </div>

                            <div class="row form-group">
                                <div class="col-sm-6">
                                    <label class="control-label">Attach Files</label>
                                    <input class="form-control input-lg" name="attachments[]" type="file" multiple />
                                    <span class="help-block m-b-none">Allowed file types: .jpg, .jpeg, .gif, .png</span>
                                </div><!-- column -->
                                <div class="col-sm-6">
                                    <?php echo get_kb_field_explainer('attachments'); ?>
                                </div><!-- column -->
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-8">
                    <button type="button" id="saveBtn" class="btn btn-md btn-primary btn-w-md m-b-xs">Save</button>
                    <button type="button" id="resetBtn" class="btn btn-md btn-white btn-w-md m-b-xs">Reset</button>
                </div>
            </div>
        </div>
    </div>
</form>
