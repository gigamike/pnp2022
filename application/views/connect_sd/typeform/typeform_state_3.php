<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="row">
    <div class="col-md-12 m-b-sm left">
        <span class="chat-img pull-left">
            <img src="<?php echo asset_url() . $chatbotAttributes['photo']; ?>" alt="<?php echo $chatbotAttributes['first_name']; ?> <?php echo $chatbotAttributes['last_name']; ?>" class="profile-photo img-circle" />
        </span>
        <div class="chat-body clearfix">
            <div class="callout">
                <div class="header">
                    <strong class="primary-font"><?php echo $chatbotAttributes['first_name']; ?> <?php echo $chatbotAttributes['last_name']; ?></strong> 
                </div>
                <p class="text-16">Thanks for contacting us, have a great day!</p>
            </div>
        </div>
    </div>
</div>
