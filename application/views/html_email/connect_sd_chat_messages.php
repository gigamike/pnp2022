<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if (count($chatMessages) > 0): ?>
<ul style="list-style: none;margin: 0;padding: 0;">
     <?php foreach ($chatMessages as $chatMessage): ?>
        <?php if (!is_null($chatMessage->connect_sd_user_id)): ?>
    <li style="margin-bottom: 10px;padding-bottom: 5px;border-bottom: 1px dotted #B3A9A9;">
        <span style="float: left !important;">
            <img style="width: 38px;height: 38px;border-radius: 50%;vertical-align: middle;" src="<?php echo $chatMessage->profile_photo; ?>" alt="?php echo $chatMessage->first_name; ?> <?php echo $chatMessage->last_name; ?>" />
        </span>
        <div style="margin-left: 60px;">
            <div>
                <strong style="font-weight: 700;"><?php echo $chatMessage->first_name; ?> <?php echo $chatMessage->last_name; ?></strong>
                <small style="float: right !important;color: #888;font-size: 85%;">
                    <span></span><?php echo $chatMessage->date_added_formatted; ?> </small>
            </div>
            <p style="margin: 0;color: #777777;"><?php echo $chatMessage->message; ?></p>
        </div>
    </li>
        <?php else: ?>
    <li style="margin-bottom: 10px;padding-bottom: 5px;border-bottom: 1px dotted #B3A9A9;">
        <span style="float: right !important;">
            <img style="width: 38px;height: 38px;border-radius: 50%;vertical-align: middle;" src="<?php echo $chatMessage->profile_photo; ?>" alt="?php echo $chatMessage->first_name; ?> <?php echo $chatMessage->last_name; ?>" class="img-circle">
        </span>
        <div style="margin-right: 60px;">
            <div class="header">
                <small style="color: #888;font-size: 85%;">
                    <span style="margin-right: 5px;"></span><?php echo $chatMessage->date_added_formatted; ?> </small>
                <strong style="float: right !important;font-weight: 700;"><?php echo $chatMessage->first_name; ?> <?php echo $chatMessage->last_name; ?></strong>
            </div>
            <p style="margin: 0;color: #777777;"><?php echo $chatMessage->message; ?></p>
        </div>
    </li>
        <?php endif; ?>
    <?php endforeach; ?>
</ul>
<?php endif; ?>