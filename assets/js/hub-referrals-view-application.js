var shareModalFormValidator = null;
var statusTagFormValidator = null;
var setCallbackFormValidator = null;

$(document).ready(function() {

    //prevent form submission by ENTER
    $(window).keydown(function(event) {
        if (event.keyCode === 13) {
            event.preventDefault();
            return false;
        }
    });

    //tabs clikced
    $('#tabHeaders a').click(function(e) {
        e.preventDefault();
        var action = $(this).attr('attr-action');

        if (action !== "" && $("#tab-" + action + " .panel-body").html() === "") {
            load_tab_body(this, action);
        } else {
            $(this).tab('show');
        }
    });

    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
        switch ($(this).attr('attr-action')) {
            case "attachments":
                if (attachments_grid !== null) {
                    setTimeout(function() {
                        attachments_grid.masonry('layout');
                    }, 200);
                }
            default:
                break;
        }
    });

    $(".toggleMoreOrLess").click(function() {
        toggle_more_or_less();
    });

    //share
    $("#shareApplicationBtn").click(function(event) {
        $(this).blur();
        $.ajax({
            type: "POST",
            url: baseUrl + "referrals/ajax-load-share-options-modal",
            beforeSend: function() {
                $("#spinnerModal").modal('show');
            },
            data: {
                application_id: $("#application_id").val()
            },
            success: function(jObj) {
                $("#spinnerModal").modal('hide');

                if (jObj.successful) {
                    $("#shareUrlModalContainer").html(jObj.html_str);


                    //DEFINE LISTENERS
                    //ichecks
                    $('#shareUrlModal .i-checks').iCheck({
                        checkboxClass: 'icheckbox_square-blue',
                        radioClass: 'iradio_square-blue'
                    });

                    $('#shareUrlModal .i-checks').on('ifClicked', function(event) {
                        if ($('#share_sms').length) {
                            $("#share_sms").rules("remove", "selectShareMode");
                            $("#share_smsDiv .form-group").removeClass('has-error');
                            $("#share_smsDiv .help-block").remove();
                        }
                    });

                    shareModalFormValidator = $("#shareUrlModalForm").validate({
                        ignore: [":disabled", ":hidden"],
                        highlight: function(element) {
                            $(element).closest('.form-group').addClass('has-error');
                        },
                        unhighlight: function(element) {
                            $(element).closest('.form-group').removeClass('has-error');
                        },
                        errorClass: 'help-block',
                        errorPlacement: function(error, element) {
                            if (element.parent('.input-group').length) {
                                error.insertAfter(element.parent());
                            } else if (element.is(':checkbox')) {
                                error.insertAfter(element.parent().parent());
                            } else if (element.is(':radio')) {
                                error.insertAfter(element.parent().parent());
                            } else {
                                error.insertAfter(element);
                            }
                        }

                    });

                    $.validator.addMethod("selectShareMode", function(value, element) {
                        return false;
                    }, "Select at least one mode of communication.");


                    $('#shareUrlModalShareBtn').click(function() {
                        $(this).blur();

                        //validate form
                        if (!$("#shareUrlModalForm").valid()) {
                            return;
                        }

                        if ($('#share_email').length && $('#share_sms').length && !$("#share_email").prop('checked') && !$("#share_sms").prop('checked')) {
                            //note: error is added to share_sms
                            $("#share_sms").rules("add", "selectShareMode");
                            shareModalFormValidator.element($("#share_sms"));
                            return;
                        }

                        $.ajax({
                            type: "POST",
                            url: baseUrl + "referrals/ajax-share-application",
                            beforeSend: function() {
                                $("#shareUrlModal").modal('hide');
                                $("#spinnerModal").modal('show');
                            },
                            data: $("#shareUrlModalForm").serialize(),
                            success: function(jObj) {
                                $("#spinnerModal").modal('hide');

                                if (jObj.successful) {
                                    swal({ title: "", text: "Shared!", type: "success" }, function() {
                                        location.reload(true);
                                    });
                                } else {
                                    swal({ title: "", text: jObj.error, type: "error" });
                                }
                            }
                        });
                    });


                    //finally. show the modal
                    $("#shareUrlModal").modal('show');
                } else {
                    swal({ title: "", text: jObj.error, type: "error" });
                }
            }
        });
    });

    //callback
    $("#setCallbackApplicationBtn").click(function(event) {
        event.preventDefault();
        $(this).blur();

        $.ajax({
            type: "POST",
            url: baseUrl + "referrals/ajax-load-set-application-callback-modal",
            beforeSend: function() {
                $("#spinnerModal").modal('show');
            },
            data: {
                application_id: $("#application_id").val()
            },
            success: function(jObj) {
                $("#spinnerModal").modal('hide');

                if (jObj.successful) {
                    $("#setCallbackModalContainer").html(jObj.html_str);


                    //DEFINE LISTENERS
                    //form validator
                    setCallbackFormValidator = $("#setCallbackModalForm").validate({
                        ignore: [":disabled", ":hidden"],
                        highlight: function(element) {
                            $(element).closest('.form-group').addClass('has-error');
                        },
                        unhighlight: function(element) {
                            $(element).closest('.form-group').removeClass('has-error');
                        },
                        errorClass: 'help-block',
                        errorPlacement: function(error, element) {
                            if (element.parent('.input-group').length) {
                                error.insertAfter(element.parent());
                            } else if (element.is(':checkbox')) {
                                error.insertAfter(element.parent().parent());
                            } else if (element.is(':radio')) {
                                error.insertAfter(element.parent().parent());
                            } else {
                                error.insertAfter(element);
                            }
                        }
                    });

                    $.validator.addMethod("selectCallbackDate", function(value, element) {
                        return false;
                    }, "Select a date for callback.");


                    //datepicker
                    $('#callbackDatePicker').datepicker();

                    $('#callbackDatePicker').on("changeDate", function() {
                        //set value
                        $('#callbackDate').val($('#callbackDatePicker').datepicker('getFormattedDate'));


                        update_callback_timeslot();

                        //note: error is added to callbackTimeSlot
                        $("#callbackTimeSlot").rules("remove", "selectCallbackDate");
                        setCallbackFormValidator.element($("#callbackTimeSlot"));
                        $("#callbackTimeSlotDiv .form-group").removeClass('has-error');
                        $("#callbackTimeSlotDiv .help-block").remove();
                    });


                    $('#callbackSetButton').click(function() {
                        $(this).blur();

                        //validate form
                        if (!$("#setCallbackModalForm").valid()) {
                            return;
                        }

                        //same?
                        if ($("#callbackReference").val() === ($('#callbackDate').val() + " " + $('#callbackTimeSlot').val()) && $("#callbackReferenceStatusTag").val() === $("#callbackStatusTag").val()) {
                            return;
                        }

                        //dont allow empty date
                        if ($('#callbackDate').val() === "" || $('#callbackDate').val() === null) {
                            //note: error is added to callbackTimeSlot
                            $("#callbackTimeSlot").rules("add", "selectCallbackDate");
                            setCallbackFormValidator.element($("#callbackTimeSlot"));
                            return;
                        }


                        $.ajax({
                            type: "POST",
                            url: baseUrl + "referrals/ajax-set-application-callback",
                            beforeSend: function() {
                                $("#setCallbackModal").modal('hide');
                                $("#spinnerModal").modal('show');
                            },
                            data: $("#setCallbackModalForm").serialize(),
                            success: function(jObj) {
                                $("#spinnerModal").modal('hide');

                                if (jObj.successful) {
                                    swal({ title: "", text: "Callback Set!", type: "success" }, function() {
                                        location.reload(true);
                                    });
                                } else {
                                    swal({ title: "", text: jObj.error, type: "error" });
                                }
                            }
                        });
                    });


                    $('#callbackRemoveButton').click(function() {
                        $(this).blur();

                        $.ajax({
                            type: "POST",
                            url: baseUrl + "referrals/ajax-remove-application-callback",
                            beforeSend: function() {
                                $("#setCallbackModal").modal('hide');
                                $("#spinnerModal").modal('show');
                            },
                            data: $("#setCallbackModalForm").serialize(),
                            success: function(data) {
                                $("#spinnerModal").modal('hide');
                                if (jObj.successful) {
                                    swal({ title: "", text: "Callback Removed!", type: "success" }, function() {
                                        location.reload(true);
                                    });
                                } else {
                                    swal({ title: "", text: jObj.error, type: "error" });
                                }
                            }
                        });
                    });


                    //finally. show the modal
                    $("#setCallbackModal").modal('show');
                } else {
                    swal({ title: "", text: jObj.error, type: "error" });
                }
            }
        });
    });

    //tag
    $(".set-application-status-tag").click(function(event) {
        event.preventDefault();
        $(this).blur();

        var target_status = $(this).attr('attr-target-status');


        $.ajax({
            type: "POST",
            url: baseUrl + "referrals/ajax-load-application-tag-modal",
            beforeSend: function() {
                $("#spinnerModal").modal('show');
            },
            data: {
                application_id: $("#application_id").val(),
                status: target_status
            },
            success: function(jObj) {
                $("#spinnerModal").modal('hide');

                if (jObj.successful) {
                    $("#statusTagModalContainer").html(jObj.html_str);


                    //DEFINE LISTENERS
                    statusTagFormValidator = $("#statusTagModalForm").validate({
                        ignore: [":disabled", ":hidden"],
                        highlight: function(element) {
                            $(element).closest('.form-group').addClass('has-error');
                        },
                        unhighlight: function(element) {
                            $(element).closest('.form-group').removeClass('has-error');
                        },
                        errorClass: 'help-block',
                        errorPlacement: function(error, element) {
                            if (element.parent('.input-group').length) {
                                error.insertAfter(element.parent());
                            } else if (element.is(':checkbox')) {
                                error.insertAfter(element.parent().parent());
                            } else if (element.is(':radio')) {
                                error.insertAfter(element.parent().parent());
                            } else {
                                error.insertAfter(element);
                            }
                        }

                    });


                    $('#statusTagModalTagBtn').click(function() {
                        $(this).blur();

                        //validate form
                        if (!$("#statusTagModalForm").valid()) {
                            return;
                        }

                        $.ajax({
                            type: "POST",
                            url: baseUrl + "referrals/ajax-tag-application",
                            beforeSend: function() {
                                $("#statusTagModal").modal('hide');
                                $("#spinnerModal").modal('show');
                            },
                            data: $("#statusTagModalForm").serialize(),
                            success: function(jObj) {
                                $("#spinnerModal").modal('hide');

                                if (jObj.successful) {
                                    swal({ title: "", text: "Tagged!", type: "success" }, function() {
                                        location.reload(true);
                                    });
                                } else {
                                    swal({ title: "", text: jObj.error, type: "error" });
                                }
                            }
                        });
                    });


                    //finally. show the modal
                    $("#statusTagModal").modal('show');
                } else {
                    swal({ title: "", text: jObj.error, type: "error" });
                }
            }
        });
    });

});


function toggle_more_or_less() {

    if ($("#moreContentsDiv").css('display') == "none") {
        $("#moreContentsDiv").css('display', 'block');
        $("#toggleMoreContents").css('display', 'none');
        $("#toggleLessContents").css('display', 'block');

    } else {
        $("#moreContentsDiv").css('display', 'none');
        $("#toggleMoreContents").css('display', 'block');
        $("#toggleLessContents").css('display', 'none');
    }
}


function load_tab_body(element, action) {
    $.ajax({
        type: "POST",
        data: $("#applicationReferralsForm").serialize(),
        beforeSend: function() {
            $("#tab-" + action + " .panel-body").html($("#loadingSpinnerReference").html());
        },
        url: baseUrl + "referrals/ajax-load-" + action,
        success: function(jObj) {
            if (jObj.successful) {
                //add contents
                $("#tab-" + action + " .panel-body").html(jObj.html_str);

                //call listeners defined specifically for this action
                var obj = callbacks[action];
                obj();

                $(element).tab('show');

                //wait.... do we scroll somewhere?
                if ($("#scroll_target").val() != "") {
                    var scroll_element = $("#" + $("#scroll_target").val());
                    $("#scroll_target").val("");
                    scroll_to_element(scroll_element);
                }
            } else {
                //show error panel
                $("#tab-" + action + " .panel-body").html(jObj.error_panel);
            }
        }
    });
}

function reload_tab_body(action) {
    var element = $("#header-" + action);
    load_tab_body(element, action);
}


/**
 *
 * TAB FUNCTION LISTENERS ARE DEFINED BELOW
 *
 */

//Dropzone
//Disabling autoDiscover, otherwise Dropzone will try to attach twice.
Dropzone.autoDiscover = false;
var attachments_dropzone = null;
var attachments_grid = null;


var callbacks = {
    notes: function() {
        var action = "notes";
        var application_id = $("#application_id").val();

        $("#loadMoreNotes").click(function(event) {
            $(this).blur();

            //update counter
            var new_count = parseInt($("#notes_count").val()) + parseInt($("#data_limit").val());
            $("#notes_count").val(new_count);

            //set anchor so it scrolls to that part after reload
            $("#scroll_target").val("anchorDivNotes");

            reload_tab_body(action);
        });

        $("#addApplicationNoteBtn").click(function(event) {
            $(this).blur();
            var message = $("#addApplicationNoteMessage").val();
            if (message.trim() == "") {
                return;
            }

            $.ajax({
                type: "POST",
                url: baseUrl + "referrals/ajax-application-add-note",
                data: {
                    message: message.trim(),
                    application_id: application_id
                },
                success: function(jObj) {
                    if (jObj.successful) {
                        //update counter
                        var new_count = parseInt($("#notes_count").val()) < 0 ? parseInt($("#data_limit").val()) : parseInt($("#notes_count").val()) + 1;
                        $("#notes_count").val(new_count);

                        swal({ title: "", text: "Note added!", type: "success" }, function() {
                            reload_tab_body(action);
                        });
                    }
                }
            });
        });

        $(".delete-note").click(function(event) {
            $(this).blur();
            $.ajax({
                type: "POST",
                url: baseUrl + "referrals/ajax-application-delete-note",
                data: {
                    note: $(this).attr('attr-note-id'),
                    application_id: application_id
                },
                success: function(jObj) {
                    if (jObj.successful) {
                        //update counter
                        var new_count = (parseInt($("#notes_count").val()) - 1) < 0 ? parseInt($("#data_limit").val()) : parseInt($("#notes_count").val()) - 1;
                        $("#notes_count").val(new_count);

                        swal({ title: "", text: "Note deleted!", type: "success" }, function() {
                            reload_tab_body(action);
                        });
                    }
                }
            });
        });

        //init
        $("#addApplicationNoteMessage").focus();
    },
    activity: function() {
        var action = "activity";

        $("#loadMoreActivity").click(function(event) {
            $(this).blur();

            //update counter
            var new_count = parseInt($("#activity_count").val()) + parseInt($("#data_limit").val());
            $("#activity_count").val(new_count);

            //set anchor so it scrolls to that part after reload
            $("#scroll_target").val("anchorDivActivity");

            reload_tab_body(action);
        });

    },
    attachments: function() {
        var action = "attachments";
        var application_id = $("#application_id").val();

        $(".delete-attachment").click(function(event) {
            event.preventDefault();
            $(this).blur();

            $.ajax({
                type: "POST",
                url: baseUrl + "referrals/ajax-application-delete-attachment",
                data: {
                    attachment: $(this).attr('attr-attachment-id'),
                    application_id: application_id
                },
                success: function(jObj) {
                    if (jObj.successful) {
                        swal({ title: "", text: "File deleted!", type: "success" }, function() {
                            reload_tab_body(action);
                        });
                    }
                }
            });
        });

        //dropzone
        attachments_dropzone = new Dropzone("div#application_attachment_div", {
            hiddenInputContainer: "#application_attachment_div",
            autoProcessQueue: true,
            addRemoveLinks: false,
            url: baseUrl + "referrals/ajax-application-add-attachment",
            maxFilesize: 4, //MB
            maxFiles: 1,
            uploadMultiple: true, //always set to multiple (as the post action expects multuple files)
            acceptedFiles: "image/jpg,image/jpeg,image/png,image/gif,application/pdf",
            paramName: "application_attachment_file",
            dictDefaultMessage: "Drop attachment here or click to upload.",
            init: function() {
                attachments_dropzone = this;
                this.on("error", function(file, message) {
                    $("#application_attachment_error_div").html('<label class="help-block">' + message + '</label>');
                    this.removeFile(file);
                });
                this.on("reset", function(file, message) {
                    $("#application_attachment_error_div").html('');
                });
                this.on('sending', function(file, xhr, formData) {
                    //append form data
                    formData.append('application_id', application_id);
                    formData.append('csrfmhub', $('#csrfheaderid').val());
                });
                this.on('success', function(file, response) {
                    if (response.successful) {
                        swal({ title: "", text: "File upload successful!", type: "success" }, function() {
                            reload_tab_body(action);
                        });
                    } else {
                        this.removeFile(file);
                        swal({ title: "", text: response.error, type: "error" });
                    }
                });
            }
        });

        //masonry
        attachments_grid = $('#attachmentsGrid').masonry({
            itemSelector: '.grid-item',
            //columnWidth: '.grid-sizer',
            percentPosition: true,
            horizontalOrder: true
        });

        setTimeout(function() {
            attachments_grid.masonry('layout');
        }, 200);


    },
    email: function() {
        var action = "email";
        var application_id = $("#application_id").val();

        //form elements
        $.fn.select2.defaults.set("theme", "bootstrap");
        $(".select2-input").select2();

        getTinymce();

        $('#showComposeEmailBtn').on("click", function() {
            if ($("#composeEmailDiv").length && $("#composeEmailDiv").css('display') == "none") {
                $('#composeEmailDiv').slideToggle();
            }

            scroll_to_element($("#composeEmailDiv"));
        });


        detect_cc_bcc_events();


        $('#itoolEmailTemplate').on('change', function() {
            // reload quick email content binding
            reload_quick_email_template_content();
        });

        $("#itoolEmailForm").on("click", "#iToolEmailSendBtn", function() {
            $(this).blur();

            var fromName = $('#itoolEmailFrom option:selected').attr('attr-from-name');
            $('#itoolEmailFromName').val(fromName);

            // required if saving from ajax
            tinyMCE.triggerSave(true, true);

            // Create a formdata object
            var dataset = new FormData(document.getElementById('itoolEmailForm'));

            //add the files if available
            if ($("#itoolEmailAttachment").val() !== "") {
                $.each($(':file'), function(i, file) {
                    dataset.append('file-' + i, file);
                });
            }


            $.ajax({
                type: "POST",
                url: baseUrl + "referrals/ajax-submit-instant-tool-email",
                data: dataset,
                cache: false,
                processData: false, // Don't process the files
                contentType: false, // Set content type to false as jQuery will tell the server its a query string request
                beforeSend: function() {
                    $("#spinnerModal").modal('show');
                },
                success: function(jObj) {
                    $("#spinnerModal").modal('hide');

                    if (jObj.successful) {
                        //update counter
                        var new_count = parseInt($("#email_count").val()) < 0 ? parseInt($("#data_limit").val()) : parseInt($("#email_count").val()) + 1;
                        $("#email_count").val(new_count);

                        swal({ title: "", text: "Email Sent!", type: "success" }, function() {
                            reload_tab_body(action);
                        });
                    } else {
                        var err_msg = typeof jObj.error === 'undefined' || jObj.error === null ? 'Email Sending Failed!' : jObj.error;
                        swal('', err_msg, "error");
                    }
                }
            });
        });

        $("#itoolEmailForm").on("click", "#itoolEmailFields", function() {
            if ($(this).html() == 'Show all fields') {
                $(this).html('Hide some fields');
            } else {
                $(this).html('Show all fields');
            }

            // hide some email fields
            $('#itoolEmailFromWrapper').slideToggle();
            $('#itoolEmailReplyToWrapper').slideToggle();
            $('#itoolEmailToWrapper').slideToggle();
            $('#itoolEmailAttachmentWrapper').slideToggle();
        });

        $("#loadMoreEmails").click(function(event) {
            $(this).blur();
            emailLoad(true);
        });

        $('#btnRefreshEmail').on('click', function() {
            $(this).blur();
            emailLoad(false);
        });

        function emailLoad(update_count) {
            //update counter
            if (update_count) {
                var new_count = parseInt($("#emails_count").val()) + parseInt($("#data_limit").val());
                $("#emails_count").val(new_count);
            }

            //set anchor so it scrolls to that part after reload
            $("#scroll_target").val("anchorDivEmails");

            reload_tab_body(action);
        }
    },
    sms: function() {
        var action = "sms";
        var application_id = $("#application_id").val();

        $.fn.select2.defaults.set("theme", "bootstrap");
        $(".select2-input").select2();

        $('#showComposeSMSBtn').on("click", function() {
            if ($("#composeSMSDiv").length && $("#composeSMSDiv").css('display') == "none") {
                $('#composeSMSDiv').slideToggle();
            }

            scroll_to_element($("#composeSMSDiv"));
        });

        $('#itoolSmsTemplate').on('change', function() {
            reload_quick_sms_template_content();
        });

        $('#itoolSmsMessage').keyup(function() {
            var text_length = $('#itoolSmsMessage').val().length;
            var text_str = parseInt(text_length) > 0 ? text_length + ' characters' : '';
            $('#itoolSmsMessageCounter').html(text_str);
        });


        $('#token').change(function() {
            var token = $(this).val();
            $('#itoolSmsMessage').val($('#itoolSmsMessage').val() + ' ' + token);

            displaySMSMessageCounter();

            $(this).val('');
        });
        displaySMSMessageCounter();

        $("#itoolSmsForm").on("click", "#iToolSmsSendBtn", function() {
            $(this).blur();

            $.ajax({
                type: "POST",
                url: baseUrl + "referrals/ajax-submit-instant-tool-sms",
                data: { 'application': $('#itoolSmsApplication').val(), 'partner': $('#itoolSmsPartner').val(), 'user': $('#itoolSmsUser').val(), 'to': $('#itoolSmsTo').val(), 'message': $('#itoolSmsMessage').val() },
                beforeSend: function() {
                    $("#spinnerModal").modal('show');
                },
                success: function(jObj) {
                    $("#spinnerModal").modal('hide');

                    if (jObj.successful) {
                        //update counter
                        var new_count = parseInt($("#sms_count").val()) < 0 ? parseInt($("#data_limit").val()) : parseInt($("#sms_count").val()) + 1;
                        $("#sms_count").val(new_count);

                        swal({ title: "", text: "SMS Sent!", type: "success" }, function() {
                            reload_tab_body(action);
                        });
                    } else {
                        var err_msg = typeof jObj.error === 'undefined' || jObj.error === null ? 'SMS Sending Failed!' : jObj.error;
                        swal('', err_msg, "error");
                    }
                }
            });
        });

        $("#loadMoreSMS").click(function(event) {
            $(this).blur();
            smsLoad(true);
        });

        $('#btnRefreshSMS').on('click', function() {
            $(this).blur();
            smsLoad(false);
        });

        function smsLoad(update_count) {
            //update counter
            if (update_count) {
                var new_count = parseInt($("#sms_count").val()) + parseInt($("#data_limit").val());
                $("#sms_count").val(new_count);
            }

            //set anchor so it scrolls to that part after reload
            $("#scroll_target").val("anchorDivSMS");

            reload_tab_body(action);
        }
    },
    chat: function() {
        var action = "chat";
    }
};


function download_file($file_uri) {
    $.ajax({
        type: "POST",
        url: baseUrl + "referrals/ajax-download-file-exists",
        data: { download_uri: $file_uri },
        success: function(jObj) {
            if (jObj.exists) {
                var $form = $("#downloadForm");
                if ($form.length == 0) {
                    $form = $("<form>").attr({ "id": "downloadForm", "method": "POST", "action": baseUrl + "referrals/force-download" }).hide();
                    $("body").append($form);
                }
                $form.find("input").remove();
                var args = { download_uri: $file_uri, csrfmhub: $('#csrfheaderid').val() };
                for (var field in args) {
                    $form.append($("<input>").attr({ "value": args[field], "name": field }));
                }
                $form.submit();
            }
        }
    });

}



function show_email_correspondence(id) {
    $.ajax({
        type: "POST",
        url: baseUrl + "referrals/ajax-load-email-correspondence",
        data: { 'reference': id },
        success: function(jObj) {
            $("#emailCorresModalContainer").html(jObj.html);
            $("#emailCorresModal").modal("show");
        }
    });
}

function resend_customer_email(refid, appid) {
    $.ajax({
        type: "POST",
        url: baseUrl + "referrals/ajax-resend-customer-email",
        data: { 'reference': refid },
        beforeSend: function() {
            $("#emailCorresModal").modal('hide');
            $("#spinnerModal").modal('show');
        },
        success: function(jObj) {
            $("#spinnerModal").modal('hide');
            if (jObj.successful) {
                swal({ title: "", text: "Email Sent!", type: "success" }, function() {
                    reload_tab_body('email');
                });
            } else {
                swal({ title: "", text: "Email sending Failed!", type: "error" });
            }
        }
    });
}



var global_spinner = "fa fa-spinner fa-spin";
var global_pre_spinner = "";

function show_spinner(item) {
    global_pre_spinner = $("i", item).attr("class");
    $("i", item).attr("class", global_spinner);
}

function hide_spinner(item) {
    $("i", item).attr("class", global_pre_spinner);
}


function toggle_cc_bcc_fields(input_id, current_status) {
    var set_cc_bbc_display;
    if (current_status == true) {
        set_cc_bbc_display = 'block';
        $("#" + input_id).removeAttr('disabled');

    } else {
        set_cc_bbc_display = 'none';
        $("#" + input_id).attr('disabled', 'disabled');
    }
    $("#" + input_id + "Div").css('display', set_cc_bbc_display);
}


function detect_cc_bcc_events() {
    var input_id_cc = 'ccUserAssignedFilter';
    var current_status_cc = false;
    var input_id_bcc = 'bccUserAssignedFilter';
    var current_status_bcc = false;
    toggle_cc_bcc_fields(input_id_cc, current_status_cc);
    toggle_cc_bcc_fields(input_id_bcc, current_status_bcc);
    $('#ccLink').click(function() {
        current_status_cc = true;
        toggle_cc_bcc_fields(input_id_cc, current_status_cc);
        $(this).prop("disabled", true);
    });
    $('#bccLink').click(function() {
        current_status_bcc = true;
        toggle_cc_bcc_fields(input_id_bcc, current_status_bcc);
        $(this).prop("disabled", true);
    });
}


function reload_quick_email_template_content() {
    $.ajax({
        type: "POST",
        url: baseUrl + "referrals/ajax-load-instant-tool-email-template",
        data: {
            'application': $('#itoolEmailApplication').val(),
            'partner': $('#itoolEmailPartner').val(),
            'user': $('#itoolEmailUser').val(),
            'from': $('#itoolEmailFrom').val(),
            'to': $('#itoolEmailTo').val(),
            'app_first_name': $('#itoolAppFirstName').val(),
            'app_partner_hotline': $('#itoolAppPartnerHotline').val(),
            'app_ref_code': $('#itoolAppRefCode').val(),
            'app_user_name': $('#itoolAppUserName').val(),
            'app_portal_name': $('#itoolAppPortalName').val(),
            'app_move_in_date': $('#itoolAppMoveInDate').val(),
            'app_email_template': $('#itoolEmailTemplate').val(),
            'app_full_name': $('#itoolAppFullName').val(),
            'app_new_address': $('#itoolAppNewAddress').val(),
            'app_partner_name': $('#itoolAppPartnerName').val()
        },
        beforeSend: function() {
            $("#spinnerModal").modal('show');
        },
        success: function(jObj) {
            $('#itoolEmailSubject').val(jObj.itoolEmailSubject);
            $('#itoolEmailMessage').val(jObj.itoolEmailBody);
            $("#spinnerModal").modal('hide');
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            $('#itoolEmailSubject').val("");
            $('#itoolEmailMessage').val('');
            $("#spinnerModal").modal('hide');
        }
    });
}

function isValidEmailAddress(emailAddress) {
    var pattern = /^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i;
    return pattern.test(emailAddress);
}

// check if its a valid email address
function user_assigned_filter_selection(get_selected_text) {
    var isValidEmail;
    var get_selected_email_text;
    if (get_selected_text.indexOf("(") >= 0 && get_selected_text.indexOf(")") >= 0) {
        //check the brackets and parse the email address
        var email_parse_result = get_selected_text.match(/\((.*)\)/);
        get_selected_email_text = email_parse_result[1];
    } else {
        get_selected_email_text = get_selected_text;
    }

    if (get_selected_email_text.length > 0) {
        isValidEmail = isValidEmailAddress(get_selected_email_text);
        if (isValidEmail === true) {
            return true;
        }
    }
    return false;
}

function reload_quick_sms_template_content() {
    $.ajax({
        type: "POST",
        url: baseUrl + "referrals/ajax-load-instant-tool-sms-template",
        data: {
            'application': $('#itoolSmsApplication').val(),
            'partner': $('#itoolSmsPartner').val(),
            'user': $('#itoolSmsUser').val(),
            'to': $('#itoolSmsTo').val(),
            'app_first_name': $('#itoolAppFirstName').val(),
            'app_partner': $('#activePartnerID').val(),
            'app_partner_hotline': $('#itoolAppPartnerHotline').val(),
            'app_ref_code': $('#itoolAppRefCode').val(),
            'app_user_name': $('#itoolAppUserName').val(),
            'app_portal_name': $('#itoolAppPortalName').val(),
            'app_move_in_date': $('#itoolAppMoveInDate').val(),
            'app_sms_template': $('#itoolSmsTemplate').val(),
            'app_full_name': $('#itoolAppFullName').val(),
            'app_new_address': $('#itoolAppNewAddress').val()
        },
        beforeSend: function() {
            $("#spinnerModal").modal('show');
        },
        success: function(jObj) {
            $('#itoolSmsMessage').val(jObj.itoolSmsTemplate);
            $("#spinnerModal").modal('hide');
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            $('#itoolSmsMessage').val("");
            $("#spinnerModal").modal('hide');
        }
    });
}

function displaySMSMessageCounter() {
    var text_length = $('#itoolSmsMessage').val().length;
    var text_str = parseInt(text_length) > 0 ? text_length + ' characters' : '';
    $('#itoolSmsMessageCounter').html(text_str);
}



function update_callback_timeslot() {

    if ($('#callbackDate').val() === "" || $('#callbackDate').val() === null) {
        return;
    }

    $("#callbackTimeSlot").empty();



    $.ajax({
        type: "POST",
        url: baseUrl + "referrals/ajax-options-callback-timeslots",
        data: {
            'callbackdate': $('#callbackDate').val()
        },
        success: function(jObj) {
            $("#callbackTimeSlot").empty().append($("<option></option>").val('').html("Select time slot").prop('disabled', true).prop('selected', true));
            $.each(jObj, function(key, value) {
                $('#callbackTimeSlot').append($("<option></option>").val(value['k']).html(value['v']));
            });


        }
    });
}

function getTinymce() {
    $('.tinymce').tinymce({
        contextmenu: false,
        browser_spellcheck: true,
        height: 500,
        menubar: 'edit view insert format table tools',
        plugins: [
            'advlist autolink lists link image charmap print preview anchor',
            'searchreplace visualblocks code fullscreen',
            'insertdatetime media table paste code help wordcount',
            'autoresize codesample directionality emoticons hr legacyoutput nonbreaking pagebreak tabfocus textpattern visualchars imagetools'
        ],
        toolbar: [
            'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent',
            'removeformat | image | code | inserttoken | help'
        ],
        // relative_urls: false, // do not use this, when special tag i.e. [URL] it adds domain i.e. https://local-utilihub.io/[URL]
        // remove_script_host: true,
        // document_base_url: baseUrl,
        urlconverter_callback: function(url, node, on_save, name) {
            return url;
        },
        automatic_uploads: true,
        file_picker_types: 'image',
        //images_upload_url: baseUrl + 'common/ajax-tinymce-file-save',
        images_upload_handler: function(blobInfo, success, failure) {
            var xhr, formData;

            xhr = new XMLHttpRequest();
            xhr.withCredentials = false;
            xhr.open('POST', baseUrl + 'common/ajax-tinymce-file-save');

            xhr.onload = function() {
                var json;

                if (xhr.status != 200) {
                    failure('HTTP Error: ' + xhr.status);
                    return;
                }

                json = JSON.parse(xhr.responseText);

                if (!json || typeof json.location != 'string') {
                    failure('Invalid JSON: ' + xhr.responseText);
                    return;
                }

                success(json.location);
            };

            formData = new FormData();
            formData.append('file', blobInfo.blob(), blobInfo.filename());
            // append CSRF token in the form data
            formData.append('csrfmhub', $('#csrfheaderid').val());

            xhr.send(formData);
        },
        convert_urls: true,

        // https://www.tiny.cloud/docs/demo/custom-toolbar-menu-button/
        setup: function(editor) {
            editor.ui.registry.addMenuButton('inserttoken', {
                text: 'Insert Token',
                fetch: function(callback) {
                    var items = [{
                        type: 'menuitem',
                        text: 'Workspace Code',
                        onAction: function() {
                            editor.insertContent('[PARTNERCODE]');
                        }
                    }, {
                        type: 'menuitem',
                        text: 'Workspace Name',
                        onAction: function() {
                            editor.insertContent('[PARTNERNAME]');
                        }
                    }, {
                        type: 'menuitem',
                        text: 'Workspace URL',
                        onAction: function() {
                            editor.insertContent('[PARTNERURL]');
                        }
                    }, {
                        type: 'menuitem',
                        text: 'Workspace Hotline',
                        onAction: function() {
                            editor.insertContent('[PARTNERHOTLINE]');
                        }
                    }, {
                        type: 'menuitem',
                        text: 'Workspace Support',
                        onAction: function() {
                            editor.insertContent('[PARTNERSUPPORT]');
                        }
                    }];

                    callback(items);
                }
            });
        }
    });
}