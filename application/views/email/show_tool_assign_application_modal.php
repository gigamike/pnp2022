<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="modal inmodal" id="instantToolsModalAssignApplication" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <form id="itoolAssignApplicationForm">
        <input type="hidden" name="log_id" id="log_id" value="<?php echo $log->id; ?>">

        <div class="modal-dialog modal-lg">
            <div class="modal-content animated fadeIn">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12 m-t-n-sm">
                            <button type="button" class="close m-r-n-sm loading-disabler" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <h2>Assign Application</h2>
                        </div>
                    </div><!-- header -->

                    <!-- notifications -->
                    <div id="iToolAssignApplicatioNotification" class="alert alert-danger" style="display:none"><span></span></div>
                    <!-- notifications -->


                    <div class="row form-group">
                        <div class="col-sm-8">
                            <label>Application Status</label>
                            <select name="filterStatus" id="filterStatus" class="select2-input form-control input-sm" style="width:100%;">
                                <option value="" selected>All</option>
                                <?php
                                foreach ($application_status_list as $key => $value) {
                                    $selected = isset($stored_filters['filterStatus']) && $stored_filters['filterStatus'] == $key ? ' selected' : '';
                                    echo '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
                                }
                                ?>
                            </select>
                        </div><!-- column -->
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <div id="appliction-wrapper">
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </form>
</div>
