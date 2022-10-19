<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php foreach ($chatMessages as $chatMessage): ?>
<div class="sidebar-message" style="padding:5px 20px;">
    <div class="feed-element pull-left text-center m-r-sm">
        <img alt="image" class="img-circle" src="<?php echo $chatMessage->profile_photo; ?>" onerror="this.onerror=null;this.src='<?php echo asset_url() . "img/connect-sd/guest1.jpg"; ?>';">
    </div>
    <div class="media-body">
        <strong><?php echo $chatMessage->first_name; ?> <?php echo $chatMessage->last_name; ?></strong>
        <small class="text-muted"><?php echo time_elapsed($chatMessage->date_added); ?></small>
        <div class="" style="margin-bottom: 10px;"><?php echo $chatMessage->message; ?></div>
        <?php
            $countChatMessageAttachments = count($chatMessageAttachments[$chatMessage->id]);
            if ($countChatMessageAttachments > 0):
        ?>
        <div class="mail-attachment">
            <p>
                <span><i class="fa fa-paperclip"></i>
                    <?php echo $countChatMessageAttachments; ?>
                    <?php if ($countChatMessageAttachments == 1): ?> attachment
                    <?php else: ?> attachments
                    <?php endif; ?></span>
            </p>
            <div class="attachment">
                <?php foreach ($chatMessageAttachments[$chatMessage->id] as $chatMessageAttachment): ?>
                <?php
                        //filename
                        $filename = "File";
                        $file_extension = "";
                        if (preg_match("/([^\/]+)$/", $chatMessageAttachment->url, $matches)) {
                            $filename = rawurldecode($matches[1]);
                            $file_extension = strtolower(pathinfo(trim($filename), PATHINFO_EXTENSION));

                            $ext_image = FCPATH . "assets/img/file-extensions/" . $file_extension . ".png";
                            if (file_exists($ext_image)) {
                                $hero_img = '<img alt="file" class="img-responsive" style="height:50px;" src="' . asset_url() . "img/file-extensions/" . $file_extension . ".png" . '">';
                            } else {
                                $hero_img = '<span style="height:50px;"><i class="fa fa-paperclip fa-5x" style="height:50px;"></i></span>';
                            }
                        }

                        if (ENVIRONMENT == 'production') {
                            $file_url_parts = (parse_url($chatMessageAttachment->url));
                            $a_attributes = 'href="javascript:void(0)" onclick="download_file(\'' . ltrim($file_url_parts['path'], '/') . '\')"';
                        } else {
                            $a_attributes = 'href="' . $chatMessageAttachment->url . '" target="_blank"';
                        }
                ?>
                <div class="file-box">
                    <div class="file">
                        <a <?php echo $a_attributes; ?>>
                            <span class="corner"></span>
                            <div class="icon">
                                <center>
                                    <?php echo $hero_img; ?>
                                </center>
                            </div>
                            <div class="file-name">
                                <?php echo $chatMessageAttachment->file_name; ?>
                            </div>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
                <div class="clearfix"></div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>