<?php
defined('BASEPATH') or exit('No direct script access allowed');
$this->load->view('register/verify_header');
?>
<div class="limiter">
    <div class="container-login100">
        <div class="wrap-login100">
            <form role="form" class="login100-form" method="POST" action="<?php echo base_url(); ?>register/verify-account/2/<?php echo $token1 . "/" . $token2; ?>">
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
                                <h1 class="font-bold">Partner Admin Details</h1>
                                <p class="text-muted">As an Administrator you have the ability to configure multiple workspaces, setup users and activate modules.</p>
                                <p class="text-muted">Please confirm or complete your contact information.</p>
                            </div>
                        </div>
                        <div class="row m-t-md">
                            <div class="col-sm-12">
                                <label>First Name <span class="text-danger">*</span></label>
                                <div class="form-group<?php
                                if (form_error('first_name') != "") {
                                    echo " has-error";
                                }
                                ?>">
                                    <input type="text" name="first_name" placeholder="Firstname" class="form-control input-lg" value="<?php echo isset($form_data['first_name']) ? $form_data['first_name'] : ""; ?>" required>
                                    <?php echo form_error('first_name'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <label>Last Name <span class="text-danger">*</span></label>
                                <div class="form-group<?php
                                if (form_error('last_name') != "") {
                                    echo " has-error";
                                }
                                ?>">
                                    <input type="text" name="last_name" placeholder="Lastname" class="form-control input-lg" value="<?php echo isset($form_data['last_name']) ? $form_data['last_name'] : ""; ?>" required>
                                    <?php echo form_error('last_name'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <label>Email <span class="text-danger">*</span></label>
                                <div class="form-group<?php
                                if (form_error('user_email') != "") {
                                    echo " has-error";
                                }
                                ?>">
                                    <input type="email" name="user_email" placeholder="Email" class="form-control input-lg" value="<?php echo isset($form_data['user_email']) ? $form_data['user_email'] : ""; ?>" required>
                                    <?php echo form_error('user_email'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <label>Mobile Phone <span class="text-danger">*</span></label>
                                <div class="form-group<?php
                                if (form_error('mobile_phone') != "") {
                                    echo " has-error";
                                }
                                ?>">
                                    <input type="text" name="mobile_phone" placeholder="Mobile Phone" class="form-control input-lg" value="<?php echo isset($form_data['mobile_phone']) ? $form_data['mobile_phone'] : ""; ?>" required>
                                    <?php echo form_error('mobile_phone'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <label>Position</label>
                                <div class="form-group<?php
                                if (form_error('position') != "") {
                                    echo " has-error";
                                }
                                ?>">
                                    <input type="text" name="position" placeholder="E.g Manager" class="form-control input-lg" value="<?php echo isset($form_data['position']) ? $form_data['position'] : ""; ?>">
                                    <?php echo form_error('position'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="row m-t-md">
                            <div class="col-sm-12">
                                <button type="submit" class="btn btn-primary btn-radius-sm btn-lg block full-width m-b">Next</button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <a href="<?php echo base_url() . 'register/verify-account/1/' . $token1 . '/' . $token2; ?>"><i class="fa fa-chevron-left"></i> Back</a>
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
