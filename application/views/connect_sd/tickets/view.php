<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="row wrapper">
    <div class="col-sm-6">
        <h3>My Tickets</h3>
    </div>
    <div class="col-sm-6 text-right">
        <?php echo isset($kb_toggler) ? $kb_toggler : ""; ?>
        <a class="btn btn-md btn-primary  btn-w-s m-b-xs" href="<?php echo base_url(); ?>connect-sd/tickets/add">New Ticket</a>
        <?php if ($ticket): ?>

            <?php if ($ticket->status==CONNECT_SD_TICKET_STATUS_OPEN): ?>
                <button id="replyBtn" class="btn btn-md btn-default btn-w-s m-b-xs" type="button">Reply</button>
                <button id="ticketResolveBtn" class="btn btn-md btn-warning btn-w-s m-b-xs" type="button">Resolve</button>
            <?php endif; ?>

            <?php if ($ticket->status==CONNECT_SD_TICKET_STATUS_PENDING): ?>
                <button id="replyBtn" class="btn btn-md btn-default btn-w-s m-b-xs" type="button">Reply</button>
                <button id="ticketResolveBtn" class="btn btn-md btn-warning btn-w-s m-b-xs" type="button">Resolve</button>
            <?php endif; ?>

            <?php if ($ticket->status==CONNECT_SD_TICKET_STATUS_RESOLVED): ?>
                <?php
                    // they can only reopen ticket if within 30 days after date resolved
                    
                    if (!empty($ticket->date_resolved)):
                ?>
                    <?php
                        $now = time(); // or your date as well
                        $date = strtotime($ticket->date_resolved);
                        $datediff = $now - $date;
                        $days = round($datediff / (60 * 60 * 24));

                        if ($days <= 30):
                    ?>
                <button id="ticketReOpenBtn" class="btn btn-md btn-info btn-w-s m-b-xs" type="button">Re-open</button>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($ticket->status==CONNECT_SD_TICKET_STATUS_CLOSED): ?>
            <?php endif; ?>     

            <?php if ($ticket->status==CONNECT_SD_TICKET_STATUS_REOPEN): ?>
                <button id="replyBtn" class="btn btn-md btn-default btn-w-s m-b-xs" type="button">Reply</button>
                <button id="ticketResolveBtn" class="btn btn-md btn-warning btn-w-s m-b-xs" type="button">Resolve</button>
            <?php endif; ?>    

        <?php endif; ?>
    </div>
</div>

<?php if (count($tickets) > 0): ?>
<div class="row">
    <div class="col-sm-3">
        
        <ul class="list-group elements-list">
                <?php
                    $hour_of_day = (int) date('G');
                    foreach ($tickets as $row):
                        $datearr = explode(' ', $row->date_added);
                        $datestr = (int) $row->date_added_hours_ago <= $hour_of_day ? "Today, " . $datearr[1] : $row->date_added;
                ?>
            <li class="list-group-item <?php if ($row->reference_code == $ticket_reference_code): ?>active<?php endif; ?>">
                <a href="<?php echo base_url(); ?>connect-sd/tickets/view/<?php echo $row->reference_code; ?>">
                    <small class="pull-right text-muted"> <?php echo $datestr; ?></small>
                    <strong><?php echo $row->reference_code; ?></strong>
                    <div class="small m-t-xs">
                        <p><?php echo $row->subject; ?></p>
                        <p class="m-b-none">
                            <span class="label pull-right label-<?php echo $this->config->item('mm8_connect_sd_ticket_status_label')[$row->status]; ?>"><?php echo  $this->config->item('mm8_connect_sd_ticket_status')[$row->status]; ?></span>
                            
                            Priority: <span class="label label-<?php echo $this->config->item('mm8_connect_sd_ticket_priority_label')[$row->priority]; ?>"><?php echo  $this->config->item('mm8_connect_sd_ticket_priority')[$row->priority]; ?></span>
                        </p>
                    </div>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
        
        <br>
        <br>
        <br>
    </div>
    <div class="col-sm-9">
        <div class="row m-t-sm">
            <div class="col-sm-8">
                <?php if (count($ticket_replies) > 0): ?>
                    <?php
                        
                        foreach ($ticket_replies as $ticket_reply):
                            $datearr = explode(' ', $ticket_reply->date_added);
                            $datestr = (int) $ticket_reply->date_added_hours_ago <= $hour_of_day ? "Today, " . $datearr[1] : $ticket_reply->date_added;
                    ?>
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="feed-element">
                            <div id="reply<?php echo $ticket_reply->id; ?>"></div>
                            <div class="pull-left">
                                <img alt="image" class="img-circle" src="<?php echo !empty($ticket_reply->profile_photo) ? $ticket_reply->profile_photo : asset_url() . "img/connect-sd/guest1.jpg"; ?>">
                            </div>
                            <div class="media-body">
                                <small class="text-muted"><?php echo $datestr; ?></small>
                                <br><?php echo $ticket_reply->first_name; ?> <?php echo $ticket_reply->last_name; ?>
                            </div>
                        </div>
                        <p><?php echo  $ticket_reply->body; ?></p>

                        <?php
                            $countReplyAttachments = count($replyAttachments[$ticket_reply->id]);
                            if ($countReplyAttachments > 0):
                        ?>
                        <div class="m-t-lg">
                            <p>
                                <span><i class="fa fa-paperclip"></i> <?php echo $countReplyAttachments; ?> <?php echo ($countReplyAttachments == 1) ? 'attachment' : 'attachments' ?></span>
                            </p>
                            <div class="attachment">
                                <?php foreach ($replyAttachments[$ticket_reply->id] as $replyAttachment): ?>
                                        <?php
                                                //filename
                                                $filename = "File";
                                                $file_extension = "";
                                                if (preg_match("/([^\/]+)$/", $replyAttachment->url, $matches)) {
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
                                                    $file_url_parts = (parse_url($replyAttachment->url));
                                                    $a_attributes = 'href="javascript:void(0)" onclick="download_file(\'' . ltrim($file_url_parts['path'], '/') . '\')"';
                                                } else {
                                                    $a_attributes = 'href="' . $replyAttachment->url . '" target="_blank"';
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
                                                <?php echo $replyAttachment->file_name; ?>
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
                <?php endif; ?>
                
                <?php if ($ticket->status!=CONNECT_SD_TICKET_STATUS_RESOLVED): ?>
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="feed-element">
                            <div class="pull-left">
                                <img alt="image" class="img-circle" src="<?php echo $this->session->utilihub_hub_user_profile_photo; ?>">
                            </div>
                            <div class="media-body">
                                <small class="text-muted">Reply</small>
                                <br><?php echo $this->session->utilihub_hub_user_profile_first_name; ?> <?php echo $this->session->utilihub_hub_user_profile_last_name; ?>
                            </div>
                        </div>

                        <form id="replyForm">
                            <input type="hidden" id="ticket_id" name="ticket_id" value="<?php echo $ticket->id; ?>">

                            <textarea name="reply_body" id="reply_body" class="tinymce"></textarea>

                            <div class="clearfix"></div><br>
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <input class="form-control input-lg" name="attachments[]" type="file" multiple />
                                        <span class="help-block m-b-none">Allowed file types: .jpg, .jpeg, .gif, .png</span>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 m-b-n-xs">
                                    <div class="pull-right">
                                        <div class="btn-group dropup" id="reply_wrapper">
                                            <button class="btn btn-primary" id="reply_send">Add Reply</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                </div>
                <?php endif; ?>
            </div>
            <div class="col-sm-4">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Details
                    </div>
                    <div class="panel-body">
                        <div class="form-group">
                            <small class="text-muted">Ticket Reference Code</small><br>
                            <?php echo $ticket->reference_code; ?>
                        </div>
                        <div class="form-group">
                            <small class="text-muted">Requestor Info</small><br>
                            <?php echo $ticket->first_name; ?> <?php echo $ticket->last_name; ?>
                        </div>
                        <div class="form-group">
                            <small class="text-muted">Email</small><br>
                            <?php echo $ticket->email; ?>
                        </div>
                        <div class="form-group">
                            <small class="text-muted">Status</small><br>
                            <span class="label label-<?php echo $this->config->item('mm8_connect_sd_ticket_status_label')[$ticket->status]; ?>"><?php echo  $this->config->item('mm8_connect_sd_ticket_status')[$ticket->status]; ?></span>
                        </div>
                        <div class="form-group">
                            <small class="text-muted">Urgency</small><br>
                            <span class="label label-<?php echo $this->config->item('mm8_connect_sd_ticket_urgency_label')[$ticket->impact]; ?>"><?php echo  $this->config->item('mm8_connect_sd_ticket_urgency')[$ticket->urgency]; ?></span>
                        </div>
                        <div class="form-group">
                            <small class="text-muted">Impact</small><br>
                            <span class="label label-<?php echo $this->config->item('mm8_connect_sd_ticket_impact_label')[$ticket->impact]; ?>"><?php echo  $this->config->item('mm8_connect_sd_ticket_impact')[$ticket->impact]; ?></span>
                        </div>
                        <div class="form-group">
                            <small class="text-muted">Priority</small><br>
                            <span class="label label-<?php echo $this->config->item('mm8_connect_sd_ticket_priority_label')[$ticket->priority]; ?>"><?php echo  $this->config->item('mm8_connect_sd_ticket_priority')[$ticket->priority]; ?></span>
                        </div>
                        <div class="form-group">
                            <small class="text-muted">Created</small><br>
                            <?php echo reformat_str_date($ticket->date_added, 'Y-m-d H:i:s', $this->config->item('mm8_php_default_date_format') . ' h:i:s A'); ?>
                        </div>
                        <div class="form-group">
                            <small class="text-muted">Last updated</small><br>
                            <?php  if (!empty($ticket->date_last_updated)): ?>  
                            <?php echo reformat_str_date($ticket->date_last_updated, 'Y-m-d H:i:s', $this->config->item('mm8_php_default_date_format') . ' h:i:s A'); ?>
                            <?php endif; ?>
                        </div>

                        <?php  if (!empty($ticket->date_reopen)): ?>
                        <div class="form-group">
                            <small class="text-muted">Date Re-open</small><br>
                            <?php echo reformat_str_date($ticket->date_reopen, 'Y-m-d H:i:s', $this->config->item('mm8_php_default_date_format') . ' h:i:s A'); ?>
                        </div>
                        <?php endif; ?>

                        <?php  if (!empty($ticket->date_resolved)): ?>
                        <div class="form-group">
                            <small class="text-muted">Date Resolved</small><br>
                            <?php echo reformat_str_date($ticket->date_resolved, 'Y-m-d H:i:s', $this->config->item('mm8_php_default_date_format') . ' h:i:s A'); ?>
                        </div>
                        <?php endif; ?>

                        <?php  if (!empty($ticket->date_closed)): ?>
                        <div class="form-group">
                            <small class="text-muted">Date Closed</small><br>
                            <?php echo reformat_str_date($ticket->date_closed, 'Y-m-d H:i:s', $this->config->item('mm8_php_default_date_format') . ' h:i:s A'); ?>
                        </div>
                        <?php endif; ?>


                        <div class="form-group">
                            <small class="text-muted">Assignee</small><br>
                            <?php echo $assignee; ?>
                        </div>
                    </div>
                </div>
               
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Activities
                    </div>
                    <div class="panel-body">
                        <div id="vertical-timeline" class="vertical-container light-timeline no-margins">
                            <?php if (count($ticketActivities) > 0): ?>
                                <?php foreach ($ticketActivities as $ticketActivity): ?>
                            <div class="vertical-timeline-block">
                                <div class="vertical-timeline-icon navy-bg"></div>
                                <div title="<?php echo $ticketActivity->first_name; ?> <?php echo $ticketActivity->last_name; ?>" class="vertical-timeline-icon gray-bg" style="background-image: url(<?php echo $ticketActivity->profile_photo; ?>); background-size:cover; background-repeat:no-repeat; background-position: center center;">
                                </div>
                                <div class="vertical-timeline-content">
                                    <?php echo $ticketActivity->first_name; ?> <?php echo $ticketActivity->last_name; ?><br>
                                    <p><?php if ($ticketActivity->ticket_reply_id): ?><a href="#reply<?php echo $ticketActivity->ticket_reply_id; ?>"><?php echo $ticketActivity->activity; ?></a><?php else: ?><?php echo $ticketActivity->activity; ?><?php endif; ?></p>
                                    <span class="vertical-date">
                                        <small><?php echo $datestr; ?></small>
                                    </span>
                                </div>
                            </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            
                        </div>
                    </div>
                </div>
                <br><br><br>
            </div>
        </div>
    </div>
</div>
<br><br><br>

<?php $this->load->view('connect_sd/tickets/modal_resolve_reply');?>

<?php $this->load->view('connect_sd/tickets/modal_reopen_reply');?>

<?php else: ?>
    <div class="row m-t-lg">
        <div class="col-sm-12 text-center">
            No Tickets
        </div>
    </div>
<?php endif; ?>