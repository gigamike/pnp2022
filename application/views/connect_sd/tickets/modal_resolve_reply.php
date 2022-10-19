<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade" id="resolveReplyModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12 m-t-n-sm text-right">
                        <button type="button" id="closeBtn" class="close m-r-n-sm loading-disabler" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    </div>
                </div><!-- header -->
                <div class="row m-b">
                    <div class="col-sm-12 ">
                        <h2>Resolve and reply<span class="text-danger">*</span></h2>
                    </div>
                </div><!-- header -->
                <div class="ibox float-e-margins white-bg">
                    <div class="ibox-content">
                        <form id="resolveReplyForm">
                            <input type="hidden" name="ticket_id" value="<?php echo $ticket->id; ?>">
                            
                            <div class="form-group">
                                <textarea name="resolve_reply_body" id="resolve_reply_body" class="tinymce"></textarea>
                                <div class="clearfix"></div><br>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <input class="form-control input-lg" name="attachments[]" type="file" multiple />
                                            <span class="help-block m-b-none">Allowed file types: .jpg, .jpeg, .gif, .png</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 m-b-n-xs">
                                    <div class="pull-right">
                                        <div class="btn-group dropup" id="reply_wrapper">
                                            <button type="button" class="btn btn-primary" id="resolveReplySendBtn">Resolve and reply</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div><!-- modal body -->
        </div><!-- modal content -->
    </div><!-- modal dialog -->
</div><!-- modal -->