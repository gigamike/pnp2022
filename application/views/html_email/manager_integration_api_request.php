<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta name="viewport" content="width=device-width" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title><?php echo $this->config->item('mm8_product_name'); ?> by <?php echo $this->config->item('mm8_system_name'); ?></title>
    </head>
    <body>
        <table width="100%">
            <tr>
                <td style="display: block !important; margin: 0 auto !important; clear: both !important;">
                    <p>
                        API Key & Documentation Request made by:<br/><br/>
                        Name: <?php echo $user_profile['full_name']; ?><br/>
                        Email: <?php echo $user_profile['email']; ?><br/>
                        Phone: <?php echo $user_profile['mobile_phone']; ?><br/>
                        Role: <?php echo $this->config->item('mm8_agent_roles')[$user_profile['role']]; ?><br/>

                        <br/><br/>Partner Details:<br/><br/>
                        Partner Name: <?php echo $manager_data['name']; ?><br/>
                        Partner Code: <?php echo $manager_data['manager_code']; ?><br/>
                    </p>

                    <p>
                        Message:<br>
                            <?php echo $request_reason; ?>
                    </p>
                </td>
            </tr>
        </table>
    </body>
</html>
