<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<section class="container m-t-lg m-b-sm">
    <div class="row">
        <div class="col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1">
            <div class="widget white-bg">
                <div class="row">
                    <div class="col-md-10 col-md-offset-1 col-sm-12 m-t-sm m-b-sm typeform">

                        <div class="panel-body" style="height:500px">
                            <ul id="chatMessagesWrapper" class="chat full-height-scroll"></ul>
                        </div>

                        <form id="messageForm">
                            <input type="hidden" name="chatChannel" id="chatChannel" value="<?php echo $chatChannel; ?>">

                            <input type="hidden" name="chatIdleThreshold" id="chatIdleThreshold" value="<?php echo $this->config->item('mm8_connect_sd_chat_idle_threshold_in_seconds'); ?>">

                            <input type="hidden" name="chatIdleWaitingThreshold" id="chatIdleWaitingThreshold" value="<?php echo $this->config->item('mm8_connect_sd_chat_idle_waiting_threshold_in_seconds'); ?>">

                            <div class="messageEntryWrapper">
                                <div class="row">
                                    <div class="col-sm-10 col-sm-offset-1">
                                        <div class="form-group">
                                            <input type="text" name="message" id="message" class="form-control input-lg" placeholder="message" value="" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row navigator-div">
                                    <div class="col-sm-10 col-sm-offset-1 text-right">
                                        <p><a href="javascript:void(0);" class="btn btn-md btn-success btn-w-md" onclick="message()">OK <i class="fa fa-check"></i> </a><span class="font-italic text-muted"> or press ENTER</span></p>
                                    </div>
                                </div>
                            </div>  

                        </form>     

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

