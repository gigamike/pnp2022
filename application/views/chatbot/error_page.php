<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate, max-stale=0, post-check=0, pre-check=0" />
        <meta http-equiv="pragma" content="no-cache">
        <meta http-equiv="expires" content="-1">
        <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7; IE=EmulateIE9">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="description" content="">
        <meta name="author" content="">
        <meta name="apple-mobile-web-app-capable" content="yes" />
        <meta name="mobile-web-app-capable" content="yes" />
        <title><?php echo $this->config->item('mm8_system_name'); ?> Widget</title>
        <link href="<?php echo asset_url(); ?>css/bootstrap.min.css" rel="stylesheet">
        <link href="<?php echo asset_url(); ?>font-awesome/css/font-awesome.css" rel="stylesheet">
        <!--<link href="<?php echo asset_url(); ?>css/animate.css" rel="stylesheet">-->
        <link href="<?php echo asset_url(); ?>form-builder-themes/blue/css/style.min.css" rel="stylesheet">
        <link rel="shortcut icon" href="<?php echo asset_url(); ?>favicon.ico"/>
        <script type="text/javascript">var baseUrl = '<?php echo base_url(); ?>'; var baseCountry = '<?php echo $this->config->item('mm8_country_code'); ?>'; var baseDateFormat = '<?php echo $this->config->item('mm8_global_date_format'); ?>';  var baseCurrency = '<?php echo $this->config->item('mm8_currency'); ?>'; var systemName = '<?php echo $this->config->item('mm8_system_name'); ?>'; var systemPrefix = '<?php echo $this->config->item('mm8_system_prefix'); ?>';</script>
    </head>
    <body>
        <div class="row">
            <div class="col-sm-8 col-sm-offset-2 m-b-md m-t-xl">
                <div class="jumbotron">
                    <h1 class="font-bold text-danger">Sorry about that!</h1>
                    <p><?php echo $error; ?></p>
                    <!--<p><a role="button" class="btn btn-primary">Learn more</a></p>-->
                </div>
            </div>
        </div>

        <div data-iframe-height></div>
        <script type="text/javascript" src="<?php echo asset_url(); ?>js/jquery-3.5.1.min.js"></script>
        <script type="text/javascript" src="<?php echo asset_url(); ?>js/bootstrap.min.js"></script>
        <script type="text/javascript" src="<?php echo cache_buster(asset_url() . 'js/plugins/metisMenu/jquery.metisMenu.js'); ?>"></script>
        <script type="text/javascript" src="<?php echo cache_buster(asset_url() . 'js/plugins/slimscroll/jquery.slimscroll.min.js'); ?>"></script>
        <script type="text/javascript" src="<?php echo cache_buster(asset_url() . 'js/form-builder/widgets/iframe-resizer/iframeResizer.contentWindow.min.js'); ?>"></script>
        <script type="text/javascript" src="<?php echo cache_buster(asset_url() . 'js/inspinia.js'); ?>"></script>
        <script type="text/javascript" src="<?php echo cache_buster(asset_url() . 'js/plugins/pace/pace.min.js'); ?>"></script>
        <script>
            var g_iFrameResizerInterval;

            $(window).on("load", function (e) {
                g_iFrameResizerInterval = setInterval(interval_callback_scroll, 100);
            });

            function interval_callback_scroll() {
                if ('parentIFrame' in window) {
                    clearInterval(g_iFrameResizerInterval);
                    parentIFrame.scrollTo(0, 0);
                }
            }
        </script>
    </body>
</html>
