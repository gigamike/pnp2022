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
                <p class="text-16">Have we found what you were looking for?</p>
                <strong><?php echo $title; ?></strong>
                <small><?php echo $non_html_content; ?></small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-10 col-sm-offset-1">
            <div class="radiobtn">
                <input type="radio" name="isKBHelp" id="isKBHelp<?php echo $rowCount; ?>-Yes" value="yes" />
                <label for="isKBHelp<?php echo $rowCount; ?>-Yes">Yes</label>
            </div>
            <div class="radiobtn">
                <input type="radio" name="isKBHelp" id="isKBHelp<?php echo $rowCount; ?>-No" value="no" />
                <label for="isKBHelp<?php echo $rowCount; ?>-No">No</label>
            </div>
    </div>
</div>

<div class="row navigator-div">
    <div class="col-sm-10 col-sm-offset-1 text-right">
        <p><a href="javascript:void(0);" class="btn btn-md btn-primary btn-w-md" onclick="typeform()">OK <i class="fa fa-check"></i> </a></p>
    </div>
</div>