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
        <link href="<?php echo asset_url(); ?>css/plugins/toastr/toastr.min.css" rel="stylesheet">
        <link href="<?php echo asset_url(); ?>css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
        <?php
        foreach ($styles as $css) {
            echo "<link href=\"" . $css . "\" rel=\"stylesheet\">\n";
        }
        ?>
        <link href="<?php echo asset_url(); ?>css/animate.css" rel="stylesheet">
        <link href="<?php echo asset_url(); ?>connect-sd-themes/<?php echo $theme; ?>/css/style.min.css" rel="stylesheet">
        <link rel="shortcut icon" href="<?php echo asset_url(); ?>favicon.ico"/>
        <script type="text/javascript">var baseUrl = '<?php echo base_url(); ?>'; var baseCountry = '<?php echo $this->config->item('mm8_country_code'); ?>'; var baseDateFormat = '<?php echo $this->config->item('mm8_global_date_format'); ?>';  var baseCurrency = '<?php echo $this->config->item('mm8_currency'); ?>'; var systemName = '<?php echo $this->config->item('mm8_system_name'); ?>' ;var systemPrefix = '<?php echo $this->config->item('mm8_system_prefix'); ?>';</script>
        <?php echo isset($internal_css) && !empty($internal_css) ? $internal_css : ""; ?>
    </head>
    <body class="<?php if ($this->router->fetch_method() == 'index'): ?>typeform<?php endif; ?>" <?php echo isset($onload_call) ? " onload=\"" . $onload_call . "\"" : ""; ?>>
        <input type="hidden" class="txt_csrfname" id="csrfheaderid" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
  
        <!-- wrapper start -->
        <div id="wrapper">
