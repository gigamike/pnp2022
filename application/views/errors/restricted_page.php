<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html>
    <head>
        <?php if (ENVIRONMENT == "production" && !empty($this->config->item('mm8_system_gtmcode'))) { ?>
            <!-- Google Tag Manager -->
            <script>
                (function (w, d, s, l, i) {
                    w[l] = w[l] || [];
                    w[l].push({'gtm.start': new Date().getTime(), event: 'gtm.js'});
                    var f = d.getElementsByTagName(s)[0], j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : '';
                    j.async = true;
                    j.src = 'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
                    f.parentNode.insertBefore(j, f);
                })(window, document, 'script', 'dataLayer', '<?php echo $this->config->item('mm8_system_gtmcode'); ?>');
            </script>
            <!-- End Google Tag Manager -->
        <?php } ?>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $this->config->item('mm8_product_html_title'); ?> | Restricted Access</title>
        <link href="<?php echo asset_url(); ?>css/bootstrap.min.css" rel="stylesheet">
        <link href="<?php echo asset_url(); ?>font-awesome/css/font-awesome.css" rel="stylesheet">
        <link href="<?php echo asset_url(); ?>css/animate.css" rel="stylesheet">
        <link href="<?php echo asset_url(); ?>css/utilihub-hub.min.css" rel="stylesheet">
        <link rel="shortcut icon" href="<?php echo asset_url(); ?>favicon.ico"/>
    </head>
    <body class="gray-bg">
        <?php if (ENVIRONMENT == "production" && !empty($this->config->item('mm8_system_gtmcode'))) { ?>
            <!-- Google Tag Manager (noscript) -->
            <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo $this->config->item('mm8_system_gtmcode'); ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
            <!-- End Google Tag Manager (noscript) -->
        <?php } ?>
        <div class="middle-box text-center loginscreen animated fadeInDown">
            <div>
                <div class="m-t-lg"></div>
                <i class="fa fa-lock big-icon"></i>
                <h2>Restricted Access</h2>
                <p>You do not have permission to view this page.<br>Please contact your site administrator.</p>
                <a href="<?php echo base_url(); ?>" class="btn btn-primary">Home</a>
            </div>
        </div>
        <!-- Mainly scripts -->
        <script src="<?php echo asset_url(); ?>js/jquery-3.5.1.min.js"></script>
        <script src="<?php echo asset_url(); ?>js/bootstrap.min.js"></script>

        <?php if (ENVIRONMENT == "production" && !empty($this->config->item('mm8_system_gacode'))) { ?>
            <script>
                (function (i, s, o, g, r, a, m) {
                    i['GoogleAnalyticsObject'] = r;
                    i[r] = i[r] || function () {
                        (i[r].q = i[r].q || []).push(arguments)
                    }, i[r].l = 1 * new Date();
                    a = s.createElement(o),
                            m = s.getElementsByTagName(o)[0];
                    a.async = 1;
                    a.src = g;
                    m.parentNode.insertBefore(a, m)
                })(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');

                ga('create', '<?php echo $this->config->item('mm8_system_gacode'); ?>', 'auto');
                ga('send', 'pageview');
            </script>
        <?php } ?>
    </body>
</html>
