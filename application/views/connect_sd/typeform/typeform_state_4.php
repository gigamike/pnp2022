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
                <p class="text-16">Before we continue, have you check our knowledge base for the answer?</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-10 col-sm-offset-1">
        <?php if (count($kbs) > 0): ?>
            <?php foreach ($kbs as $kb): ?>
            <div class="radiobtn">
                <input type="radio" name="kbId" id="kbId<?php echo $rowCount; ?>-<?php echo $kb->id; ?>" value="<?php echo $kb->id; ?>" />
                <label for="kbId<?php echo $rowCount; ?>-<?php echo $kb->id; ?>">
                    <?php echo $kb->title; ?></label>
            </div>
            <?php endforeach; ?>
            <div class="radiobtn">
                <input type="radio" name="kbId" id="kbId<?php echo $rowCount; ?>-none" value="none" />
                <label for="kbId<?php echo $rowCount; ?>-none">None</label>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="row navigator-div">
    <div class="col-sm-10 col-sm-offset-1 text-right">
        <p><a href="javascript:void(0);" class="btn btn-md btn-primary btn-w-md" onclick="typeform()">OK <i class="fa fa-check"></i> </a></p>
    </div>
</div>