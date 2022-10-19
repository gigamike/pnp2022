var connectSDTimerInterval = 5000;
var connectSDTimerID = null;

$(document).ready(function() {
    $(window).keydown(function(event) {
        if (event.keyCode === 13) {
            event.preventDefault();
            return false;
        }
    });

    // CONNECT Room Chat feature
    $('.connect-sd-right-sidebar-toggle').on('click', function() {
        $('#connect-sd-sidebar').toggleClass('sidebar-open');

        if ($("#connect-sd-sidebar").hasClass("sidebar-open")) {

            // hide task guide
            if ($('#task-guide-container').hasClass('opened'))
                $('#task-guide-menu').trigger('click');

            connectScroll();
        }
    });

    if (findGetParameter('open-chat')) {
        $('#connect-sd-sidebar').toggleClass('sidebar-open');
    }

    $(window).scroll(function() {
        if ($(window).scrollTop() > 0 && !$('body').hasClass('fixed-nav')) {
            $('#connect-sd-sidebar').addClass('sidebar-top');
        } else {
            $('#connect-sd-sidebar').removeClass('sidebar-top');
        }
    });

    $("#connectSDChatMessage").keyup(function(event) {
        if (event.keyCode === 13) {
            $("#connectSDChatSendBtn").click();
        }
    });

    $('#connectSDChatSendBtn').on('click', function() {
        $(this).blur();

        //validate form
        if (!$("#connectSDChatForm").valid()) {
            return;
        }

        // required if saving from ajax
        tinyMCE.triggerSave(true, true);

        // Create a formdata object and add the files
        var data = new FormData(document.getElementById('connectSDChatForm'));
        $.each($(':file'), function(i, file) {
            data.append('file-' + i, file);
        });

        //save
        $.ajax({
            type: 'POST',
            url: baseUrl + 'connect-sd/chat/ajax-message-submit',
            data: data,
            cache: false,
            processData: false, // Don't process the files
            contentType: false, // Set content type to false as jQuery will tell the server its a query string request
            beforeSend: function() {
                $("#spinnerModal").modal('show');
            },
            success: function(jObj) {
                $("#spinnerModal").modal("hide");
                if (jObj.successful) {
                    $("#connectSDChatMessage").val('');
                    connectSDChatMessages();
                } else {
                    swal('Ooops!', jObj.error, "error");

                    if (typeof jObj.refresh !== 'undefined') {
                        location.reload();
                    }
                }
            }
        });
    });

    getConnectSDChatTinymce();

    connectSDChatMessages();

    init();
});

function init() {
    typeform();
}

function typeform() {
    //initial load
    if ($("#connectSDTypeFormCurrentState").val() != "") {
        //validate forms first

        if (!$("#connectSDTypeformForm").valid()) {
            return;
        }
    }

    // Create a formdata object and add the files
    var data = new FormData(document.getElementById('connectSDTypeformForm'));
    $.each($(':file'), function(i, file) {
        data.append('file-' + i, file);
    });

    $.ajax({
        url: baseUrl + "connect-sd/typeform/ajax-typeform",
        type: 'POST',
        data: data,
        cache: false,
        processData: false, // Don't process the files
        contentType: false, // Set content type to false as jQuery will tell the server its a query string request
        beforeSend: function() {
            $(".btn").prop('disabled', true);
        },
        success: function(jObj) {
            $(".btn").prop('disabled', false);

            if (jObj.successful) {
                $("#connectSDTypeformContainerDiv :input, #connectSDTypeformContainerDiv .btn-group-lg").prop("disabled", true);
                $("#connectSDTypeformContainerDiv .btn-group-lg").attr("disabled", "disabled");
                $(".navigator-div").remove();

                $("#connectSDTypeformContainerDiv .widget").addClass("disabled-widget");
                $("#typeformForm").off("click", ".disabled-widget"); // unbind before redefining
                $("#typeformForm").on("click", ".disabled-widget", function() {
                    $("#resetState").val($(this).attr('attr-state'));
                    $(this).nextAll().remove();
                    $(this).remove();
                    typeform();
                });

                $("#connectSDTypeformContainerDiv").append(chatBotIsTypingTemplate());

                typeformScrollToElement();

                chatBotIsTyping()
                    .then(
                        function(result) {
                            $('#connectSDTypeformContainerDiv .chatbotTypingWrapper').remove();
                            $("#connectSDTypeformContainerDiv").append(jObj.html);
                            $("#connectSDTypeFormCurrentState").val(jObj.currentState);
                            $("#connectSDTypeFormRowCount").val(jObj.rowCount);

                            //add validator rules to system fields
                            //add js listener to system fields
                            $(":input.system-field:enabled").not(':button,:hidden').each(function() {

                            });

                            typeformScrollToElement();

                            //focus element
                            $("#state" + jObj.rowCount + "Div :input:first").not(':button,:hidden,:radio,:checkbox').focus();

                            if (typeof jObj.connect_to_agent !== 'undefined') {
                                connectSDTimerID = setInterval(connect, connectSDTimerInterval);
                            }
                        }
                    );
            } else {
                show_notification(jObj.error, false);
            }
        }
    });
}

function chatBotIsTyping() {
    return new Promise(function(resolve, reject) {
        setTimeout(function() {
            resolve("anything");
        }, 1000);
    });
}

function chatBotIsTypingTemplate() {
    var html = "";
    html += "<div class=\"chatbotTypingWrapper\">";
    html += "<div class=\"typingIndicator\">";
    html += "<span></span>";
    html += "<span></span>";
    html += "<span></span>";
    html += "</div>";
    html += "</div>";

    return html;
}

function show_notification(message, successful) {
    var divclass = successful ? "alert-success" : "alert-danger";
    var divicon = successful ? "<i class='fa fa-2x fa-check-circle-o'></i>" : "<i class='fa fa-2x fa-times-circle-o'></i>";

    $('#notificationDiv .notif-icon').html(divicon);
    $('#notificationDiv .notif-text').html(message);
    $('#notificationDiv').removeClass().addClass('widget ' + divclass + ' p-md text-center animated fadeIn').css('display', 'block');
}

function typeformScrollToElement() {
    jQuery('.full-height-scroll').slimscroll({ scrollBy: '400px' });
}

function connect() {
    $.ajax({
        type: 'POST',
        url: baseUrl + "connect-sd/typeform/ajax-connect",
        data: $('#connectSDTypeformForm').serialize(),
        success: function(jObj) {
            if (jObj.successful) {
                if (typeof jObj.is_chat !== 'undefined') {
                    clearInterval(connectSDTimerID);
                    connectSDTimerID = null;

                    var url = window.location.href;
                    if (url.indexOf('?') > -1) {
                        url += '&open-chat=1'
                    } else {
                        url += '?open-chat=1'
                    }
                    window.location.href = url;

                    return false;
                }

                if (typeof jObj.is_ticket !== 'undefined') {
                    // continue with typeform as ticket
                    clearInterval(connectSDTimerID);
                    connectSDTimerID = null;

                    $("#connectSDTypeFormCurrentState").val(6);
                    typeform();
                }
            } else {
                show_notification(jObj.error, false);
            }
        }
    });
}

function connectScroll() {
    var height = 0;
    $('#connectSDChatMessages .sidebar-message').each(function(i, value) {
        height += parseInt($(this).height());
    });
    height += '';
    $('#connectSDChatMessages').animate({ scrollTop: height });
}

function connectSDChatMessages() {
    $.ajax({
        type: 'POST',
        url: baseUrl + 'connect-sd/chat/ajax-get-messages',
        success: function(jObj) {
            if (jObj.successful) {
                $('#connectSDChatMessages').html(jObj.html);
                connectScroll();
            }
        }
    });
}

function getConnectSDChatTinymce() {
    $('.connectSDChatTinymce').tinymce({
        contextmenu: false,
        browser_spellcheck: true,
        height: 130,
        menubar: false,
        statusbar: false,
        plugins: [
            'autolink lists link'
        ],
        toolbar: [
            'bold italic strikethrough | link | numlist bullist | blockquote'
        ],
        // relative_urls: false, // do not use this, when special tag i.e. [URL] it adds domain i.e. https://local-utilihub.io/[URL]
        // remove_script_host: true,
        // document_base_url: baseUrl,
        urlconverter_callback: function(url, node, on_save, name) {
            return url;
        }
    });
}

function download_file(file_uri) {
    $.ajax({
        type: "POST",
        url: baseUrl + "connect-sd/chat/ajax-download-file-exists",
        data: { download_uri: file_uri },
        success: function(jObj) {
            if (jObj.exists) {
                var form = $("#downloadForm");
                if (form.length == 0) {
                    form = $("<form>").attr({ "id": "downloadForm", "method": "POST", "action": baseUrl + "connect-sd/chat/force-download" }).hide();
                    $("body").append(form);
                }
                form.find("input").remove();
                var args = { download_uri: file_uri, csrfmhub: $('#csrfheaderid').val() };
                for (var field in args) {
                    form.append($("<input>").attr({ "value": args[field], "name": field }));
                }
                form.submit();
            }
        }
    });
}

function findGetParameter(parameterName) {
    var result = null,
        tmp = [];
    location.search
        .substr(1)
        .split("&")
        .forEach(function(item) {
            tmp = item.split("=");
            if (tmp[0] === parameterName) result = decodeURIComponent(tmp[1]);
        });
    return result;
}