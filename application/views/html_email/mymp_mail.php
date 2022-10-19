<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta name="viewport" content="width=device-width" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>myMarketplace by <?php echo $this->config->item('mm8_system_name'); ?></title>
    </head>
    <body style="width: 100% !important; height: 100%; line-height: 1.6; background-color: #f6f6f6; margin: 0; padding: 0; font-family: Arial; box-sizing: border-box; font-size: 14px;">
        <table style="background-color: #f6f6f6; width: 100%;">
            <tr>
                <td></td>
                <td style="display: block !important; max-width: 600px !important; margin: 0 auto !important; clear: both !important;" width="600">
                    <div style="max-width: 600px; margin: 0 auto; display: block; padding: 20px;">
                        <table style="background: #fff; border: 1px solid #e9e9e9; border-radius: 3px;" width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="padding: 20px;">
                                    <table cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td>
                                                <img style="max-width: 100%;" alt="<?php echo $this->config->item('mm8_product_name'); ?>" title="<?php echo $this->config->item('mm8_product_name'); ?>" src="<?php echo isset($banner_img) ? $banner_img : asset_url() . "html-email/basic/img/email-banner.jpg"; ?>"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 20px 0px;">
                                                <?php echo $contents; ?>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                        <div style="color: #999;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="font-size: 11px; text-align: left; padding: 20px 20px 10px 20px; line-height: 11px;"><?php echo $this->config->item('mm8_system_email_disclaimer'); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </td>
                <td></td>
            </tr>
        </table>
    </body>
</html>
