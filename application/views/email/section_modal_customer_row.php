<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal inmodal" id="customerRowModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
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
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="row">
                                    <div class="col-sm-12 text-left">
                                        <small class="text-muted">Customer ID</small><br>
                                        <p class="font-bold"><?php echo $u_code; ?></p>
                                    </div>
                                </div>
                                <div class="hr-line-dashed m-t-none m-b-none"></div>
                                <div class="row">
                                    <div class="col-sm-12 text-left">
                                        <small class="text-muted">Customer Name</small><br>
                                        <p class="font-bold"><?php echo trim(preg_replace('/\s+/', ' ', $title . ' ' . $full_name)) == "" ? "-" : trim(preg_replace('/\s+/', ' ', $title . ' ' . $full_name)); ?></p>
                                    </div>
                                </div>
                                <div class="hr-line-dashed m-t-none m-b-none"></div>
                                <div class="row">
                                    <div class="col-sm-12 text-left">
                                        <small class="text-muted">Email</small><br>
                                        <p class="font-bold"><?php echo!empty($email) ? $email : '-'; ?></p>
                                    </div>
                                </div>
                                <div class="hr-line-dashed m-t-none m-b-none"></div>
                            </div>
                            <div class="col-sm-6">
                                <div class="row">
                                    <div class="col-sm-12 text-left">
                                        <small class="text-muted">Primary Phone</small><br>
                                        <p class="font-bold"><?php echo!empty($primary_phone) ? $primary_phone : '-'; ?></p>
                                    </div>
                                </div>
                                <div class="hr-line-dashed m-t-none m-b-none"></div>

                                <div class="row">
                                    <div class="col-sm-12 text-left">
                                        <small class="text-muted">Secondary Phone</small><br>
                                        <p class="font-bold"><?php echo!empty($secondary_phone) ? $secondary_phone : '-'; ?></p>
                                    </div>
                                </div>
                                <div class="hr-line-dashed m-t-none m-b-none"></div>
                            </div>
                        </div>


                        <hr class="m-l-n-lg m-r-n-lg border-size-sm"/>
                        <div class="row">
                            <div class="col-sm-12 text-left">
                                <h3>Applications</h3>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 text-left">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th>Reference Code</th>
                                            <th>Property Type</th>
                                            <th>Type</th>
                                            <th class="text-center">Status</th>
                                            <th>Status Tag</th>
                                            <th>Date Added</th>
                                            <th>Date Actioned</th>
                                        </tr>
                                        <tbody>
                                            <?php
                                            if (count($applications) > 0) {
                                                foreach ($applications as $application) {
                                                    echo '<tr>';
                                                    echo '<td>' . $application['reference_code'] . '</td>';
                                                    echo '<td>' . (!empty($application['application_type']) && isset($this->config->item('mm8_application_type_names')[$application['application_type']]) ? $this->config->item('mm8_application_type_names')[$application['application_type']] : '-') . '</td>';
                                                    echo '<td>' . (!empty($application['application_offer_type']) && isset($this->config->item('mm8_offer_type_names')[$application['application_offer_type']]) ? $this->config->item('mm8_offer_type_names')[$application['application_offer_type']] : '-') . '</td>';
                                                    echo '<td class="text-center">' . (!empty($application['application_status']) ? mm8_status_badge($application['application_status']) : '-') . '</td>';
                                                    echo '<td>' . (!empty($application['status_tag']) && isset($this->config->item('mm8_status_tags')[$application['status_tag']]) ? $this->config->item('mm8_status_tags')[$application['status_tag']] : '-') . '</td>';
                                                    echo '<td>' . $application['date_added'] . '</td>';
                                                    echo '<td>' . $application['date_actioned'] . '</td>';
                                                    echo '</tr>';
                                                }
                                            } else {
                                                echo '<tr><td colspan="7">None</td></tr>';
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
