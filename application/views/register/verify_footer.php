<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<script type="text/javascript" src="<?php echo asset_url(); ?>js/jquery-3.5.1.min.js" <?php if (SENTRY_ENABLED) { ?> onerror="onScriptLoadError(this.src);"<?php } ?>></script>
<script type="text/javascript" src="<?php echo asset_url(); ?>js/bootstrap.min.js" <?php if (SENTRY_ENABLED) { ?> onerror="onScriptLoadError(this.src);"<?php } ?>></script>
<script type="text/javascript" src="<?php echo cache_buster(asset_url() . 'js/plugins/toastr/toastr.min.js'); ?>" <?php if (SENTRY_ENABLED) { ?> onerror="onScriptLoadError(this.src);"<?php } ?>></script>
<script type="text/javascript" src="<?php echo cache_buster(asset_url() . 'js/plugins/sweetalert/sweetalert.min.js'); ?>" <?php if (SENTRY_ENABLED) { ?> onerror="onScriptLoadError(this.src);"<?php } ?>></script>
<script type="text/javascript" src="<?php echo cache_buster(asset_url() . 'js/plugins/jasny/jasny-bootstrap.min.js'); ?>" <?php if (SENTRY_ENABLED) { ?> onerror="onScriptLoadError(this.src);"<?php } ?>></script>
<script type="text/javascript" src="<?php echo cache_buster(asset_url() . 'js/plugins/metisMenu/jquery.metisMenu.js'); ?>" <?php if (SENTRY_ENABLED) { ?> onerror="onScriptLoadError(this.src);"<?php } ?>></script>
<script type="text/javascript" src="<?php echo cache_buster(asset_url() . 'js/plugins/slimscroll/jquery.slimscroll.min.js'); ?>" <?php if (SENTRY_ENABLED) { ?> onerror="onScriptLoadError(this.src);"<?php } ?>></script>
<script type="text/javascript" src="<?php echo cache_buster(asset_url() . 'js/plugins/bootstrap3-typeahead/bootstrap3-typeahead.js'); ?>" <?php if (SENTRY_ENABLED) { ?> onerror="onScriptLoadError(this.src);"<?php } ?>></script>
<script type="text/javascript" src="<?php echo cache_buster(asset_url() . 'js/inspinia.js'); ?>" <?php if (SENTRY_ENABLED) { ?> onerror="onScriptLoadError(this.src);"<?php } ?>></script>
<script type="text/javascript" src="<?php echo cache_buster(asset_url() . 'js/plugins/pace/pace.min.js'); ?>" <?php if (SENTRY_ENABLED) { ?> onerror="onScriptLoadError(this.src);"<?php } ?>></script>
<script type="text/javascript" src="<?php echo cache_buster(asset_url() . 'js/plugins/clipboard/clipboard.min.js'); ?>" <?php if (SENTRY_ENABLED) { ?> onerror="onScriptLoadError(this.src);"<?php } ?>></script>
<script type="text/javascript" src="<?php echo cache_buster(asset_url() . 'js/hub-register-verify-account.js'); ?>" <?php if (SENTRY_ENABLED) { ?> onerror="onScriptLoadError(this.src);"<?php } ?>></script>


<?php if (isset($show_password_meter) && $show_password_meter) { ?>
    <script>
        $(document).ready(function () {
            $('#user_password').keyup(function () {
                $('#password_meter').html(checkStrength($('#user_password').val()));
            });

            function checkStrength(password) {
                var strength = 0;
                if (password.length >= <?php echo $this->config->item('mm8_system_password_length'); ?>) {
                    strength += 25;
                }
                if (password.match(/[a-z]/)) {
                    strength += 25;
                }
                if (password.match(/[A-Z]/)) {
                    strength += 25;
                }

                if (password.match(/\d/) || password.match(/[^a-zA-Z\d]/)) {
                    strength += 25;
                }
                return '<div style="width: ' + strength + '%;" class="progress-bar progress-bar-info"></div>';
            }
        });
    </script>
<?php } ?>

</body>
</html>