$(document).ready(function () {

    //prevent form submission by ENTER
    $(window).keydown(function (event) {
        if (event.keyCode === 13) {
            event.preventDefault();
            return false;
        }
    });

    $.fn.select2.defaults.set("theme", "bootstrap");
    $(".select2-input").select2();

    $('.customer').on("click", function () {
        var customer = $(this).attr('attr-customer');
        $.ajax({
            type: "POST",
            data: {'customer': customer},
            beforeSend: function () {
                $("#spinnerModal").modal('show');
            },
            url: baseUrl + "sms/ajax-dt-get-customers-expand-row",
            success: function (jObj) {
                $("#spinnerModal").modal("hide");
                if (jObj.successful) {
                    $("#customerRowModalContainer").html(jObj.html);
                    $("#customerRowModal").modal("show");
                }
            }
        });
    });


    $('.applicationReferrals').on("click", function () {
        $.ajax({
            type: "POST",
            data: {'application': $(this).attr('attr-application')},
            beforeSend: function () {
                $("#spinnerModal").modal('show');
            },
            url: baseUrl + "referrals/ajax-load-application-summary-modal",
            success: function (jObj) {
                $("#spinnerModal").modal("hide");
                if (jObj.successful) {
                    $("#applicationSummaryModalContainer").html(jObj.html);
                    $("#applicationSummaryModal").modal("show");
                }
            }
        });
    });


    $('#showComposeSMSBtn').on("click", function () {
        if ($("#composeSMSDiv").length && $("#composeSMSDiv").css('display') == "none") {
            $('#composeSMSDiv').slideToggle();
        }

        scroll_to_element($("#composeSMSDiv"));
    });


    $('#itoolSmsTemplate').on('change', function () {
        reload_quick_sms_template_content();
    });


    $("#itoolSmsForm").on("click", "#iToolSmsSendBtn", function () {
        $(this).blur();
        $.ajax({
            type: "POST",
            url: baseUrl + "sms/ajax-submit-instant-tool-sms",
            data: {'application': $('#itoolSmsApplication').val(), 'partner': $('#itoolSmsPartner').val(), 'user': $('#itoolSmsUser').val(), 'to': $('#itoolSmsTo').val(), 'message': $('#itoolSmsMessage').val()},
            beforeSend: function () {
                $("#spinnerModal").modal('show');
            },
            success: function (jObj) {
                $("#spinnerModal").modal('hide');

                if (parseInt(jObj.status) === 1) {
                    swal({title: "", text: "SMS Sent!", type: "success"}, function () {
                        location.reload(true);
                    });
                } else {
                    var err_msg = typeof jObj.error === 'undefined' || jObj.error === null ? 'SMS Sending Failed!' : jObj.error;
                    swal('', err_msg, "error");
                }
            }
        });
    });


    $('.btnAssignApplication').on("click", function () {
        $(this).blur();
        $.ajax({
            type: "POST",
            url: baseUrl + "sms/ajax-load-assign-application",
            data: {
                'id': $(this).attr('attr-id'),
                'type': 'sms'
            },
            success: function (jObj) {
                $("#instantToolsModalContainer").html(jObj.html);
                $("#instantToolsModalAssignApplication").modal("show");

                //form elements
                $.fn.select2.defaults.set("theme", "bootstrap");
                $(".select2-input").select2({
                    dropdownParent: $('#instantToolsModalAssignApplication')
                });

                showApplications(0);

                $("#itoolAssignApplicationForm").on("change", "#filterStatus", function () {
                    showApplications(0);
                });

                $("#itoolAssignApplicationForm").on("click", ".btnAssignPartner", function () {
                    $.ajax({
                        type: "POST",
                        data: {
                            'reference_code': $(this).attr('attr-reference-code'),
                            'log_id': $("#log_id").val(),
                            'type': 'sms'
                        },
                        beforeSend: function () {
                            $("#spinnerModal").modal('show');
                        },
                        url: baseUrl + "sms/ajax-assign-application",
                        success: function (jObj) {
                            $("#spinnerModal").modal("hide");
                            if (jObj.successful) {
                                swal({title: "", text: "SMS assigned to application!", type: "success"}, function () {
                                    location.reload(true);
                                });
                            } else {
                                var err_msg = typeof jObj.error === 'undefined' || jObj.error === null ? 'Assigning application Failed!' : jObj.error;
                                swal('', err_msg, "error");
                            }
                        }
                    });
                });
            }
        });
    });

    $('#itoolSmsMessage').keyup(function () {
        var text_length = $('#itoolSmsMessage').val().length;
        var text_str = parseInt(text_length) > 0 ? text_length + ' characters' : '';
        $('#itoolSmsMessageCounter').html(text_str);
    });


    $('#token').change(function () {
        var token = $(this).val();
        $('#itoolSmsMessage').val($('#itoolSmsMessage').val() + ' ' + token);

        displaySMSMessageCounter();

        $(this).val('');
    });
    displaySMSMessageCounter();
});


function init() {}


function reload_quick_sms_template_content() {
    $.ajax({
        type: "POST",
        url: baseUrl + "sms/ajax-load-instant-tool-sms-template",
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
        beforeSend: function () {
            $("#spinnerModal").modal('show');
        },
        success: function (jObj) {
            $('#itoolSmsMessage').html(jObj.itoolSmsTemplate);
            $("#spinnerModal").modal('hide');
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            $('#itoolSmsMessage').val("");
            $("#spinnerModal").modal('hide');
        }
    });
}


function showApplications(pageNo) {
    var filterStatus = $('#filterStatus').val();
    $.ajax({
        type: "GET",
        url: baseUrl + "sms/ajax-reload-application/" + pageNo + "?filterStatus=" + filterStatus,
        success: function (jObj) {
            $('#appliction-wrapper').html(jObj.html);
            $('#pagination_application').html(jObj.pagination);

            $('#pagination_application').on('click', 'a', function (e) {
                e.preventDefault();
                var pageNo = $(this).attr('data-ci-pagination-page');
                showApplications(pageNo);
            });
        }
    });
}

function displaySMSMessageCounter() {
    var text_length = $('#itoolSmsMessage').val().length;
    var text_str = parseInt(text_length) > 0 ? text_length + ' characters' : '';
    $('#itoolSmsMessageCounter').html(text_str);
}