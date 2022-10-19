<?php
defined('BASEPATH') or exit('No direct script access allowed');
$this->load->view('login/template_header');
?>
<div class="limiter">
    <div class="container-login100">
        <div class="wrap-login100">
            <form class="login100-form" id="registrationForm" role="form" method="POST" action="<?php echo base_url(); ?>register">
                <input type="hidden" class="txt_csrfname" id="csrfid" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                <div class="row">
                    <div class="col-sm-10 col-sm-offset-1 col-xs-10 col-xs-offset-1 col-lg-8 col-lg-offset-2">
                        <div class="row m-t-n-xl text-center mobile-only">
                            <div class="col-sm-12">
                                <center><img class="img-responsive" src="<?php echo $this->config->item('hub_login_page_logo_url'); ?>"></center>
                            </div>
                        </div>
                        <div class="widget white-bg p-md">

                            <div class="row">
                                <div class="col-sm-12 m-b-md">
                                    <h1 class="font-bold">Sign up today</h1>
                                </div>
                            </div>

                            <?php
                            if ($this->session->flashdata('error_message')) {
                                echo "<div class='alert alert-danger alert-dismissable m-t-sm m-b-md text-left'>";
                                echo "<button aria-hidden='true' data-dismiss='alert' class='close' type='button'><i class='fa fa-times'></i></button>";
                                echo $this->session->flashdata('error_message');
                                echo "</div>";
                            }
                            ?>


                            <?php if ((int) $this->config->item('mm8_hub_account_google_sso') == STATUS_OK) { ?>
                                <div class="row">
                                    <div class="col-sm-12 m-b-xs">
                                        <a href="<?php echo base_url(); ?>register/google-auth" class="btn btn-primary btn-help btn-radius-sm btn-lg block full-width m-b font-bold">Sign up with Google<i class="fa fa-google text-navy pull-right m-t-xs"></i></a>
                                    </div>
                                </div>

                                <div class="row text-center font-bold text-muted">
                                    or
                                </div>
                            <?php } ?>


                            <div class="row">
                                <div class="col-xs-6">
                                    <label>Name</label>
                                    <div class="form-group<?php
                                    if (form_error('registerFirstName') != "") {
                                        echo " has-error";
                                    }
                                    ?>">
                                        <input type="text" name="registerFirstName" placeholder="Firstname" class="form-control input-lg" value="<?php echo set_value('registerFirstName', ''); ?>" required>
                                        <?php echo form_error('registerFirstName'); ?>
                                    </div>
                                </div>
                                <div class="col-xs-6 col-no-l-gutter">
                                    <label>&nbsp;</label>
                                    <div class="form-group<?php
                                    if (form_error('registerLastName') != "") {
                                        echo " has-error";
                                    }
                                    ?>">
                                        <input type="text" name="registerLastName" placeholder="Lastname" class="form-control input-lg" value="<?php echo set_value('registerLastName', ''); ?>" required>
                                        <?php echo form_error('registerLastName'); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <label>Email</label>
                                    <div class="form-group<?php
                                    if (form_error('registerEmail') != "") {
                                        echo " has-error";
                                    }
                                    ?>">
                                        <input type="email" name="registerEmail" id="agentEmail" placeholder="Email" class="form-control input-lg" value="<?php echo set_value('registerEmail', ''); ?>" required>
                                        <?php echo form_error('registerEmail'); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <label>Mobile Phone</label>
                                    <div class="form-group<?php
                                    if (form_error('registerMobilePhone') != "") {
                                        echo " has-error";
                                    }
                                    ?>">
                                        <input type="text" name="registerMobilePhone" placeholder="Mobile Phone" class="form-control input-lg" value="<?php echo set_value('registerMobilePhone', ''); ?>">
                                        <?php echo form_error('registerMobilePhone'); ?>
                                    </div>
                                </div>
                            </div>

                            <p class="text-muted">By signing up, you agree to the <?php echo $this->config->item('mm8_system_name'); ?> <a href="<?php echo $this->config->item('mm8_movinghub_tc_terms_url'); ?>" target="_blank">Terms of Use</a> and <a href="<?php echo $this->config->item('mm8_movinghub_tc_terms_url'); ?>" target="_blank">Privacy Policy</a>.</p>

                            <div class="row m-t-md">
                                <div class="col-sm-12">
                                    <button type="button" class="btn btn-primary btn-radius-sm btn-lg block full-width m-b" id="registerBtn">Sign up</button>
                                </div>
                            </div>

                            <br>
                            <center><a href="<?php echo base_url(); ?>login">Already have an account? Login</a></center>
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

                <?php if (ENVIRONMENT == "production"): ?>
                    <div id='recaptcha' class="g-recaptcha"
                         data-sitekey="<?php echo $this->config->item('mm8_g_recaptcha_site_key'); ?>"
                         data-callback="onSubmit"
                         data-size="invisible"></div>
                     <?php endif; ?>

            </form>
            <div class="login100-more">
                <div class="login100-more-mask"></div>
            </div>
        </div>
    </div>
</div>
<?php $this->load->view('login/template_footer'); ?>
