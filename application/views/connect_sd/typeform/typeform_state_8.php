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
                <p class="text-16">Would you like to attach a file?</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-10 col-sm-offset-1">
        <div class="form-group">
            <input class="form-control input-lg" name="ticketAttachments[]" type="file" multiple />
            <span class="help-block m-b-none">Allowed file types: .jpg, .jpeg, .gif, .png</span>
        </div>
    </div>
</div>

<div class="row navigator-div">
    <div class="col-sm-10 col-sm-offset-1 text-right">
        <p><a href="javascript:void(0);" class="btn btn-md btn-primary btn-w-md" onclick="typeform()">OK <i class="fa fa-check"></i> </a></p>
    </div>
</div>