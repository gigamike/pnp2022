<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div id="connect-sd-sidebar">
    <div class="sidebar-container">
        <div class="sidebar-title">
            <div class="row">
                <div class="col-sm-12 no-margins">
                    <span class="pull-right m-t-n-sm m-r-n-sm">
                        <h3 class="no-margins">
                            <a class="connect-sd-right-sidebar-toggle">
                                <i class="fa fa-close text-danger"></i>
                            </a>
                        </h3>
                    </span>
                </div>
            </div>
            <h3>Connect Service Desk</h3>
        </div>
        <ul class="nav nav-tabs navs-2">
            <li class="active"><a data-toggle="tab" id="connect-a-tab-1" href="#connect-tab-1">Chat</a></li>
            <li><a data-toggle="tab" id="connect-a-tab-2" href="#connect-tab-2">My Tickets</a></li>
        </ul>
        <div class="tab-content">
            <div id="connect-tab-1" class="tab-pane active">
                <div class="sidebar-title">
                    <h3> <i class="fa fa-comments-o"></i> Chat</h3>
                    <small>I'm here to help find you the right answer.</small>
                </div>

                <?php if ($this->session->utilihub_hub_user_connect_sd_chat_channel): ?>
                <div class="m-t-sm connectChannelWrapper">
                    <div style="height:300px">
                        <div id="connectSDChatMessages" class="full-height-scroll border-size-lg">
                        </div>
                    </div>
                    <div>
                        <div class="sidebar-message">
                            <form id="connectSDChatForm">
                                <div class="pull-left text-center m-r-sm">
                                    <img alt="image" class="img-circle message-avatar" src="<?php echo $this->session->utilihub_hub_user_profile_photo; ?>">
                                </div>
                                <div class="media-body">
                                    <strong><?php echo $this->session->utilihub_hub_user_profile_first_name; ?> <?php echo $this->session->utilihub_hub_user_profile_last_name; ?></strong><br>
                                    <div class="form-group m-t-sm">
                                        <textarea class="form-control m-t-sm connectSDChatTinymce" id="connectSDChatMessage" name="connectSDChatMessage" placeholder="Write message..." required></textarea>
                                    </div>

                                   <div class="pull-right">
                                        <button class="btn btn-xs btn-primary" type="button" id="connectSDChatSendBtn">Send</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>    
                <?php else: ?>
                <form id="connectSDTypeformForm">
                    <input type="hidden" name="connectSDTypeFormCurrentState" id="connectSDTypeFormCurrentState" value="0">
                    <input type="hidden" name="connectSDTypeFormRowCount" id="connectSDTypeFormRowCount" value="0">

                    <section class="m-t-lg m-b-sm">
                        <div class="row">
                            <div class="col-sm-12">
                                <div style="height:400px">
                                    <div id="connectSDTypeformContainerDiv" class="full-height-scroll"></div>
                                </div>
                            </div>
                        </div>
                    </section>
                </form>
                <?php endif; ?>
            </div>

            <div id="connect-tab-2" class="tab-pane">
                <div class="sidebar-title">
                    <h3> <i class="fa fa-ticket"></i> My Tickets</h3>
                    <small>Your tickets.</small>
                </div>
                <div>
                    <?php
                        $user_type = null;
                        $tickets = [];
                        switch ($this->session->utilihub_hub_target_role) {
                            case USER_MANAGER:
                                $user_type = CONNECT_SD_USER_TYPE_USER_MANAGER;
                                $tickets = connect_sd_get_tickets($user_type, $this->session->utilihub_hub_user_id, 10);

                                break;
                            case USER_SUPER_AGENT:
                                $user_type = CONNECT_SD_USER_TYPE_USER_SUPER_AGENT;
                                $tickets = connect_sd_get_tickets($user_type, $this->session->utilihub_hub_user_id, 10);

                                break;
                            case USER_AGENT:
                                $user_type = CONNECT_SD_USER_TYPE_USER_AGENT;
                                $tickets = connect_sd_get_tickets($user_type, $this->session->utilihub_hub_user_id, 10);

                                break;
                            case USER_CUSTOMER_SERVICE_AGENT:
                                $user_type = CONNECT_SD_USER_TYPE_USER_CUSTOMER_SERVICE_AGENT;
                                $tickets = connect_sd_get_tickets($user_type, $this->session->utilihub_hub_user_id, 10);

                                break;
                            default:
                        }
                    ?>
                    <?php if (count($tickets) > 0): ?>
                        <?php foreach ($tickets as $ticket): ?>
                    <div class="sidebar-message">
                        <a href="<?php echo base_url(); ?>connect-sd/tickets/view/<?php echo $ticket->reference_code; ?>">
                            <div class="media-body">
                                <?php echo $ticket->reference_code; ?> - <?php echo $ticket->subject; ?>
                                <p class="m-b-none">
                                    <span class="label pull-right label-<?php echo $this->config->item('mm8_connect_sd_ticket_status_label')[$ticket->status]; ?>"><?php echo  $this->config->item('mm8_connect_sd_ticket_status')[$ticket->status]; ?></span>
                                    
                                    Priority: <span class="label label-<?php echo $this->config->item('mm8_connect_sd_ticket_priority_label')[$ticket->priority]; ?>"><?php echo  $this->config->item('mm8_connect_sd_ticket_priority')[$ticket->priority]; ?></span>
                                </p>
                                <br>
                                <small class="text-muted"><?php echo $ticket->date_added_formatted; ?></small>
                            </div>
                        </a>
                    </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                    <div class="sidebar-message">
                        No Tickets
                    </div>
                    <?php endif; ?>
                    
                </div>  
            </div>

        </div>
    </div>
</div>