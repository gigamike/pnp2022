<?php
defined('BASEPATH') or exit('No direct script access allowed');
$this->load->view('register/verify_header');
?>
<div class="limiter">
    <div class="container-login100">
        <div class="wrap-login100">
            <form role="form" class="login100-form" method="POST" action="<?php echo base_url(); ?>register/verify-account/1/<?php echo $token1 . "/" . $token2; ?>">
                <input type="hidden" class="txt_csrfname" id="csrfid" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                <div class="row">
                    <div class="col-sm-10 col-sm-offset-1 col-xs-10 col-xs-offset-1 col-lg-8 col-lg-offset-2">

                        <div class="row">
                            <div class="col-sm-12">
                                <?php
                                if ($this->session->flashdata('error_message')) {
                                    echo "<div class='alert alert-danger alert-dismissable m-t-sm m-b-sm text-left'>";
                                    echo "<button aria-hidden='true' data-dismiss='alert' class='close' type='button'><i class='fa fa-times'></i></button>";
                                    echo $this->session->flashdata('error_message');
                                    echo "</div>";
                                }
                                ?>
                                <h1 class="font-bold">Tell us about you</h1>
                                <p class="text-muted">Please tell us a little more about your business to complete your registration.</p>
                            </div>
                        </div>
                        <div class="row m-t-md">
                            <div class="col-sm-12">
                                <label>Business Name <span class="text-danger">*</span></label>
                                <div class="form-group<?php
                                if (form_error('business_name') != "") {
                                    echo " has-error";
                                }
                                ?>">
                                    <input type="text" name="business_name" placeholder="Business Name" class="form-control input-lg" value="<?php echo isset($form_data['business_name']) ? $form_data['business_name'] : ""; ?>" required>
                                    <?php echo form_error('business_name'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <label>Business Address <span class="text-danger">*</span></label>
                                <div class="form-group<?php
                                if (form_error('business_address') != "") {
                                    echo " has-error";
                                }
                                ?>">
                                    <input type="text" name="business_address" id="business_address" placeholder="Business Address" class="form-control input-lg" value="<?php echo isset($form_data['business_address']) ? $form_data['business_address'] : ""; ?>" required>
                                    <?php echo form_error('business_address'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <label>Business Phone <span class="text-danger">*</span></label>
                                <div class="form-group<?php
                                if (form_error('business_phone') != "") {
                                    echo " has-error";
                                }
                                ?>">
                                    <!------------------------------------------------->
                                    <!-- CODE BRANCHING HERE - COUNTRY - START -->
                                    <?php if ($this->config->item('mm8_country_code') == "AU") { ?>
                                        <!--------------------------------------------->
                                        <!-- AU START -->
                                        <input type="text" name="business_phone" placeholder="E.g 0431000000" class="form-control input-lg" value="<?php echo isset($form_data['business_phone']) ? $form_data['business_phone'] : ""; ?>" required>
                                        <!-- AU END -->
                                        <!--------------------------------------------->
                                    <?php } elseif ($this->config->item('mm8_country_code') == "NZ") { ?>
                                        <!--------------------------------------------->
                                        <!-- NZ START -->
                                        <input type="text" name="business_phone" placeholder="E.g 097000000" class="form-control input-lg" value="<?php echo isset($form_data['business_phone']) ? $form_data['business_phone'] : ""; ?>" required>
                                        <!-- NZ END -->
                                        <!--------------------------------------------->
                                    <?php } elseif ($this->config->item('mm8_country_code') == "US") { ?>
                                        <!--------------------------------------------->
                                        <!-- US START -->
                                        <input type="text" name="business_phone" placeholder="E.g 5555550000" class="form-control input-lg" value="<?php echo isset($form_data['business_phone']) ? $form_data['business_phone'] : ""; ?>" required>
                                        <!-- US END -->
                                        <!--------------------------------------------->
                                    <?php } elseif ($this->config->item('mm8_country_code') == "UK") { ?>
                                        <!--------------------------------------------->
                                        <!-- UK START -->
                                        <input type="text" name="business_phone" placeholder="E.g 07712345678" class="form-control input-lg" value="<?php echo isset($form_data['business_phone']) ? $form_data['business_phone'] : ""; ?>" required>
                                        <!-- UK END -->
                                        <!--------------------------------------------->
                                    <?php } ?>
                                    <!-- CODE BRANCHING HERE - COUNTRY - END -->
                                    <!------------------------------------------------->
                                    <?php echo form_error('business_phone'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <!------------------------------------------------->
                                <!-- CODE BRANCHING HERE - COUNTRY - START -->
                                <?php if ($this->config->item('mm8_country_code') == "AU") { ?>
                                    <!--------------------------------------------->
                                    <!-- AU START -->
                                    <label>Business ABN</label>
                                    <div class="form-group<?php
                                    if (form_error('business_abn') != "") {
                                        echo " has-error";
                                    }
                                    ?>">
                                        <input type="text" name="business_abn" placeholder="Australian Business Number" class="form-control input-lg" value="<?php echo isset($form_data['business_abn']) ? $form_data['business_abn'] : ""; ?>">
                                        <?php echo form_error('business_abn'); ?>
                                    </div>
                                    <!-- AU END -->
                                    <!--------------------------------------------->
                                <?php } elseif ($this->config->item('mm8_country_code') == "NZ") { ?>
                                    <!--------------------------------------------->
                                    <!-- NZ START -->
                                    <label>Business IRDN</label>
                                    <div class="form-group<?php
                                    if (form_error('business_irdn') != "") {
                                        echo " has-error";
                                    }
                                    ?>">
                                        <input type="text" name="business_irdn" placeholder="Inland Revenue Department Number (IRDN)" class="form-control input-lg" value="<?php echo isset($form_data['business_irdn']) ? $form_data['business_irdn'] : ""; ?>">
                                        <?php echo form_error('business_irdn'); ?>
                                    </div>
                                    <!-- NZ END -->
                                    <!--------------------------------------------->
                                <?php } elseif ($this->config->item('mm8_country_code') == "US") { ?>
                                    <!--------------------------------------------->
                                    <!-- US START -->

                                    <!-- US END -->
                                    <!--------------------------------------------->
                                <?php } elseif ($this->config->item('mm8_country_code') == "UK") { ?>
                                    <!--------------------------------------------->
                                    <!-- UK START -->
                                    <label>Business CRN</label>
                                    <div class="form-group<?php
                                    if (form_error('business_crn') != "") {
                                        echo " has-error";
                                    }
                                    ?>">
                                        <input type="text" name="business_crn" placeholder="Company Registration Number (CRN)" class="form-control input-lg" value="<?php echo isset($form_data['business_crn']) ? $form_data['business_crn'] : ""; ?>">
                                        <?php echo form_error('business_crn'); ?>
                                    </div>
                                    <!-- UK END -->
                                    <!--------------------------------------------->
                                <?php } ?>
                                <!-- CODE BRANCHING HERE - COUNTRY - END -->
                                <!------------------------------------------------->
                            </div>
                        </div>
                        <!--
                        <div class="row">
                            <div class="col-sm-12">
                                <label>Website</label>
                                <div class="form-group<?php
                        if (form_error('business_website') != "") {
                            echo " has-error";
                        }
                        ?>">
                                    <input type="text" name="business_website" placeholder="Business Website" class="form-control input-lg" value="<?php echo isset($form_data['business_website']) ? $form_data['business_website'] : ""; ?>">
                        <?php echo form_error('business_website'); ?>
                                </div>
                            </div>
                        </div>
                        -->
                        <div class="row">
                            <div class="col-sm-12">
                                <label>Business Type</label>
                                <div class="form-group<?php
                                if (form_error('business_type') != "") {
                                    echo " has-error";
                                }
                                ?>">
                                    <select class="form-control input-lg" name="business_type">
                                        <option value="" disabled selected>Select</option>
                                        <?php
                                        foreach ($lookup_business_type as $value) {
                                            $selected = isset($form_data['business_type']) && $form_data['business_type'] == $value ? " selected" : "";
                                            echo '<option value="' . $value . '"' . $selected . '>' . $value . '</option>';
                                        }
                                        ?>
                                    </select>
                                    <?php echo form_error('business_type'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <label>Industry</label>
                                <div class="form-group<?php
                                if (form_error('business_industry') != "") {
                                    echo " has-error";
                                }
                                ?>">
                                    <select class="form-control input-lg" name="business_industry">
                                        <option value="" disabled selected>Select</option>
                                        <?php
                                        foreach ($lookup_industry as $value) {
                                            $selected = isset($form_data['business_industry']) && $form_data['business_industry'] == $value ? " selected" : "";
                                            echo '<option value="' . $value . '"' . $selected . '>' . $value . '</option>';
                                        }
                                        ?>
                                    </select>
                                    <?php echo form_error('business_industry'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <label>Let us know how many workspaces you will add? <a href="javascript:void(0);" data-toggle="tooltip" data-placement="top" data-container="body" title="Workspace. A platform that stores referrals that are imported or manually uploaded in order to utilise the <?php echo $this->config->item('mm8_system_name'); ?> service. The <?php echo $this->config->item('mm8_product_name'); ?> user is able to have multiple workspaces running simultaneously, eg. an estate agency may have multiple branches that all require separate workspaces or may want separate workspaces for every agent."><i class="fa fa-info-circle"></i></a></label>
                                <div class="form-group<?php
                                if (form_error('partners_estimate') != "") {
                                    echo " has-error";
                                }
                                ?>">
                                    <select class="form-control input-lg" name="partners_estimate">
                                        <option value="" disabled selected>Select</option>
                                        <?php
                                        foreach ($lookup_partners_estimate as $value) {
                                            $selected = isset($form_data['partners_estimate']) && $form_data['partners_estimate'] == $value ? " selected" : "";
                                            echo '<option value="' . $value . '"' . $selected . '>' . $value . '</option>';
                                        }
                                        ?>
                                    </select>
                                    <?php echo form_error('partners_estimate'); ?>
                                </div>
                            </div>
                        </div>



                        <div class="row m-t-md">
                            <div class="col-sm-12">
                                <button type="submit" class="btn btn-primary btn-radius-sm btn-lg block full-width m-b">Next</button>
                            </div>
                        </div>


                        <div class="row m-t-md text-center">
                            <div class="col-sm-12">
                                <center>
                                    <strong>Powered By</strong> <br>
                                    <img src="<?php echo asset_url(); ?>img/system-poweredby.png">
                                </center>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <div class="login100-more">
                <div class="login100-more-mask"></div>
            </div><!-- login more -->
        </div><!-- wrap login -->
    </div><!-- container login -->
</div><!-- limiter -->

<?php $this->load->view('register/verify_footer'); ?>
