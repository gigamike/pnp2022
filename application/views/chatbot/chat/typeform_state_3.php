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
                <p class="text-16"><?php if ($isKBHelp == 'yes'): ?>Great! You can check our other knowledge base or register now online.<?php else: ?>Sorry about that, please check our other knowledge base or contact our hotline at 1-DTI (1-384)<?php endif; ?></p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-10 col-sm-offset-1">
        <div class="radiobtn">
            <input type="radio" name="kbId" id="kbId<?php echo $rowCount; ?>-1" value="1" />
            <label for="kbId<?php echo $rowCount; ?>-1">What products are prohibited to sell online?</label>
        </div>
        <div class="radiobtn">
            <input type="radio" name="kbId" id="kbId<?php echo $rowCount; ?>-2" value="2" />
            <label for="kbId<?php echo $rowCount; ?>-2">Try our website reputation checker</label>
        </div>
        <div class="radiobtn">
            <input type="radio" name="kbId" id="kbId<?php echo $rowCount; ?>-3" value="3" />
            <label for="kbId<?php echo $rowCount; ?>-3">Complaint report now!</label>
        </div>
    </div>
</div>

<div class="row navigator-div">
    <div class="col-sm-10 col-sm-offset-1 text-right">
        <p><a href="javascript:void(0);" class="btn btn-md btn-success btn-w-md" onclick="typeform()">OK <i class="fa fa-check"></i> </a><span class="font-italic text-muted"> or press ENTER</span></p>
    </div>
</div>