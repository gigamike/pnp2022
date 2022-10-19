<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="row wrapper white-bg">
    <div class="col-sm-8 m-b-sm">
        <h3>Email Thread</h3>
    </div>
    <div class="col-sm-4 text-right">
        <div class="form-group">
            <?php echo isset($kb_toggler) ? $kb_toggler : ""; ?>
            <?php if ($app_id) { ?>
                <button id="showComposeEmailBtn" type="button" class="btn btn-md btn-primary  m-t-xs m-l-xs">Compose Email</button>
            <?php } else { ?>
                <button type="button" class="btn btn-md btn-primary  m-t-xs m-l-xs btnAssignApplication" attr-id="<?php echo $current_log_email->id; ?>">Assign Application</button>
            <?php } ?>
        </div>
    </div>
</div>

<?php if ($app_id): ?>
    <div class="row border-bottom white-bg dashboard-header">
        <div class="col-md-12">
            <div class="row">
                <div class="col-sm-6">
                    <div class="row">
                        <div class="col-sm-12 text-left">
                            <small class="text-muted">Reference Code</small><br>
                            <p class="font-bold"><a attr-application="<?php echo $reference_code; ?>" href="javascript:void(0);" class="applicationReferrals"><?php echo $reference_code; ?></a></p>
                        </div>
                    </div>
                    <div class="hr-line-dashed m-t-none m-b-none"></div>
                    <div class="row">
                        <div class="col-sm-12 text-left">
                            <small class="text-muted">Customer ID</small><br>
                            <p class="font-bold"><a attr-customer="<?php echo $customer_code; ?>" href="javascript:void(0);" class="customer"><?php echo $customer_code; ?></a></p>
                        </div>
                    </div>
                    <div class="hr-line-dashed m-t-none m-b-none"></div>
                </div>
                <div class="col-sm-6">
                    <div class="row">
                        <div class="col-sm-12 text-left">
                            <small class="text-muted">Customer</small><br>
                            <p class="font-bold"><?php echo $full_name; ?></p>
                        </div>
                    </div>
                    <div class="hr-line-dashed m-t-none m-b-none"></div>
                    <div class="row">
                        <div class="col-sm-12 text-left">
                            <small class="text-muted">Customer Email</small><br>
                            <p class="font-bold dt-"><?php echo $customer_email; ?></p>
                        </div>
                    </div>
                    <div class="hr-line-dashed m-t-none m-b-none"></div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- content wrapper start -->
<div class="row wrapper wrapper-content">
    <div class="col-sm-12">

        <?php echo isset($kb_explainer) ? $kb_explainer : ""; ?>

        <?php if ($app_id): ?>
            <div class="ibox float-e-margins" id="composeEmailDiv" style="display:none;">
                <div class="ibox-content bordered-box">
                    <?php $this->load->view('section_compose_email'); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php foreach ($emails as $email): ?>
            <div class="ibox float-e-margins">
                <div class="ibox-content bordered-box">
                    <div class="feed-element">
                        <div class="media-body ">
                            <small class="pull-right"><?php echo time_elapsed($email->date_processed); ?></small>
                            <strong><?php echo $email->from_name; ?> &lt;<?php echo $email->from; ?>&gt;</strong> emailed to <strong>&lt;<?php echo $email->to; ?>&gt;</strong>.
                            <br>
                            <p class="no-margins">Subject: <?php echo $email->subject; ?></p>
                            <?php if (isset($email->cc) && !empty($email->cc)) { ?>
                                <p class="no-margins">Cc: <?php echo $email->cc; ?></p>
                            <?php } ?>
                            <?php if (isset($email->bcc) && !empty($email->bcc)) { ?>
                                <p class="no-margins">Bcc: <?php echo $email->bcc; ?></p>
                            <?php } ?>
                            <p class="no-margins">Sent: <?php echo reformat_str_date($email->date_processed, 'Y-m-d H:i:s', $this->config->item('mm8_php_default_date_format') . ' H:i:s'); ?></p>
                            <div class="well">
                                <div class="panel blank-panel">
                                    <iframe src ="<?php echo base_url() ?>email/email-html/<?php echo $email->id; ?>" width="100%" height="400" frameborder="0" style="border:none;">
                                        <p>Your browser does not support iframes.</p>
                                    </iframe>
                                </div>

                                <!-- attachments -->
                                <?php if (isset($email->attachments) && count($email->attachments) > 0): ?>
                                    <div class="mail-attachment">
                                        <p>
                                            <span><i class="fa fa-paperclip"></i> <?php
                                                $countAttachments = count($email->attachments);
                                    echo ($countAttachments > 1) ? $countAttachments . " attachments" : $countAttachments . " attachment";
                                    ?></span>
                                        </p>
                                        <div class="attachment">
                                            <?php foreach ($email->attachments as $attachment): ?>
                                                <div class="file-box">
                                                    <div class="file">
                                                        <?php
                                            if (ENVIRONMENT == 'production') {
                                                $file_url_parts = (parse_url($attachment['fileUrl']));
                                                $a_attributes = 'href="javascript:void(0)" onclick="download_file(\'' . ltrim($file_url_parts['path'], '/') . '\')"';
                                            } else {
                                                $a_attributes = 'href="' . $attachment['fileUrl'] . '" target="_blank"';
                                            }
                                                ?>
                                                        <a <?php echo $a_attributes; ?>>
                                                            <span class="corner"></span>

                                                            <div class="icon">
                                                                <i class="fa fa-file"></i>
                                                            </div>
                                                            <div class="file-name">
                                                                <?php echo $attachment['filename']; ?>
                                                            </div>
                                                        </a>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                            <div class="clearfix"></div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <!-- attachments -->

                            </div>

                        </div>

                    </div>
                </div>
            </div>
        <?php endforeach; ?>

    </div>
</div>

<div id="instantToolsModalContainer"></div>

<div id="customerRowModalContainer"></div>

<div id="applicationSummaryModalContainer"></div>
