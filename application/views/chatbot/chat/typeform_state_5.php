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
                <p class="text-16">Your email address...</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-10 col-sm-offset-1">
        <div class="form-group">
            <input type="email" name="email" attr-target-field="email" class="form-control input-lg" placeholder="e.g. email@email.com" value="" required>
        </div>
    </div>
</div>

<div class="row navigator-div">
    <div class="col-sm-10 col-sm-offset-1 text-right">
        <p><a href="javascript:void(0);" class="btn btn-md btn-success btn-w-md" onclick="typeform()">OK <i class="fa fa-check"></i> </a><span class="font-italic text-muted"> or press ENTER</span></p>
    </div>
</div>