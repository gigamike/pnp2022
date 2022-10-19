<?php
defined('BASEPATH') or exit('No direct script access allowed');
$this->load->view('register/verify_header');
?>
<div class="limiter">
    <div class="container-login100">
        <div class="wrap-login100">
            <form role="form" class="login100-form" method="POST" action="<?php echo base_url(); ?>register/verify-account/4/<?php echo $token1 . "/" . $token2; ?>">
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
                                <h1 class="font-bold">Set Password</h1>
                                <p class="text-muted">Please enter a new password. Your password needs to:</p>
                                <ol type="i" class="text-muted">
                                    <li>include both lower and upper case characters;</li>
                                    <li>include at least one number or symbol;</li>
                                    <li>be at least <?php echo $this->config->item('mm8_system_password_length'); ?> characters long</li>
                                </ol>
                            </div>
                        </div>
                        <div class="row m-t-md">
                            <div class="col-sm-12">
                                <label>Password</label>
                                <div class="form-group<?php
                                if (form_error('user_password') != "") {
                                    echo " has-error";
                                }
                                ?>">
                                    <input type="password" class="form-control input-lg" name="user_password" id="user_password" placeholder="Enter Password" required autocomplete="off">
                                    <?php echo form_error('user_password'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <label>Repeat Password</label>
                                <div class="form-group<?php
                                if (form_error('user_password_confirm') != "") {
                                    echo " has-error";
                                }
                                ?>">
                                    <input type="password" class="form-control input-lg" name="user_password_confirm" placeholder="Repeat Password" required autocomplete="off">
                                    <?php echo form_error('user_password_confirm'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <label>Password Strength Meter</label>
                                <div id="password_meter" class="progress progress-mini m-b-xs">
                                    <div style="width:0%;" class="progress-bar progress-bar-info"></div>
                                </div>
                            </div>
                        </div>


                        <div class="row m-t-md">
                            <div class="col-sm-12">
                                <button type="submit" class="btn btn-primary btn-radius-sm btn-lg block full-width m-b">Complete</button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <a href="<?php echo base_url() . 'register/verify-account/3/' . $token1 . '/' . $token2; ?>"><i class="fa fa-chevron-left"></i> Back</a>
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
