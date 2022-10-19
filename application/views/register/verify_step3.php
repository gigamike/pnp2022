<?php
defined('BASEPATH') or exit('No direct script access allowed');
$this->load->view('register/verify_header');
?>
<div class="limiter">
    <div class="container-login100">
        <div class="wrap-login100">
            <form role="form" class="login100-form" method="POST" action="<?php echo base_url(); ?>register/verify-account/3/<?php echo $token1 . "/" . $token2; ?>">
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
                                <h1 class="font-bold"><?php echo $this->config->item('mm8_system_name'); ?> Rewards</h1>
                                <p class="text-muted">Receive rewards when your customers use a service. In order to receive payments please enter your business number for tax purposes.</p>
                                <p class="text-muted">If you don't have this information handy, you can skip for now, but remember - we won't be able to pay you until we have your payment information.</p>
                            </div>
                        </div>
                        <?php
                        /**
                         * CODE BRANCHING HERE - COUNTRY
                         *      AU
                         *      NZ
                         *      US
                         *      UK
                         */
                        $this->load->view('register/section_payments_' . $this->config->item('mm8_country_code'));
                        ?>
                        <div class="row m-t-md">
                            <div class="col-sm-12">
                                <button type="submit" class="btn btn-primary btn-radius-sm btn-lg block full-width m-b">Next</button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <a href="<?php echo base_url() . 'register/verify-account/2/' . $token1 . '/' . $token2 . '?parent=3'; ?>"><i class="fa fa-chevron-left"></i> Back</a>
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
