$(document).ready(function() {
    $(window).keydown(function(event) {
        if (event.keyCode === 13) {
            event.preventDefault();
            return false;
        }
    });

    $.fn.select2.defaults.set("theme", "bootstrap");
    $(".select2-input").select2();

    getTinymce();

    $('#replyBtn').click(function(e) {
        e.preventDefault();
        $('.tinymce').focus();
        $("html, body").animate({ scrollTop: $(document).height() }, 1000);
    });

    $('#reply_send').click(function(e) {
        e.preventDefault();
        $(this).blur();
        reply();
    });

    $('#ticketResolveBtn').click(function() {
        $(this).blur();

        $('#resolveReplyModal').modal('show');
    });

    $('#resolveReplySendBtn').click(function() {
        $(this).blur();

        swal({
            title: "",
            text: "Are you sure to resolve ticket? Please confirm if you want to resolve this ticket",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#2C83FF",
            confirmButtonText: "Yes, ticket resolve",
            closeOnConfirm: true
        }, function(isConfirm) {
            if (!isConfirm) {
                return;
            }

            // required if saving from ajax
            tinyMCE.triggerSave(true, true);

            // Create a formdata object and add the files
            var data = new FormData(document.getElementById('resolveReplyForm'));
            $.each($(':file'), function(i, file) {
                data.append('file-' + i, file);
            });

            $.ajax({
                type: 'POST',
                url: baseUrl + 'connect-sd/tickets/ajax-ticket-resolve-reply',
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
                        swal({
                            title: "",
                            text: jObj.message,
                            type: "success",
                            confirmButtonColor: "#2C83FF",
                            confirmButtonText: "Close"
                        }, function() {
                            window.location.href = baseUrl + "connect-sd/tickets/view/" + jObj.ticket_reference_code;
                        });
                    } else {
                        swal('Ooops!', jObj.error, "error");
                    }
                }
            });
        });
    });

    $('#ticketReOpenBtn').click(function() {
        $(this).blur();

        $('#reopenReplyModal').modal('show');
    });

    $('#reopenReplySendBtn').click(function() {
        $(this).blur();

        // required if saving from ajax
        tinyMCE.triggerSave(true, true);

        // Create a formdata object and add the files
        var data = new FormData(document.getElementById('reopenReplyForm'));
        $.each($(':file'), function(i, file) {
            data.append('file-' + i, file);
        });

        $.ajax({
            type: 'POST',
            url: baseUrl + 'connect-sd/tickets/ajax-ticket-reopen-reply',
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
                    swal({
                        title: "",
                        text: jObj.message,
                        type: "success",
                        confirmButtonColor: "#2C83FF",
                        confirmButtonText: "Close"
                    }, function() {
                        window.location.href = baseUrl + "connect-sd/tickets/view/" + jObj.ticket_reference_code;
                    });
                } else {
                    swal('Ooops!', jObj.error, "error");
                }
            }
        });
    });
});

function reply() {
    // required if saving from ajax
    tinyMCE.triggerSave(true, true);

    // Create a formdata object and add the files
    var data = new FormData(document.getElementById('replyForm'));
    $.each($(':file'), function(i, file) {
        data.append('file-' + i, file);
    });

    $.ajax({
        type: "POST",
        url: baseUrl + "connect-sd/tickets/ajax-ticket-reply-submit",
        data: data,
        cache: false,
        processData: false, // Don't process the files
        contentType: false, // Set content type to false as jQuery will tell the server its a query string request
        beforeSend: function() {
            $("#spinnerModal").modal('show');
        },
        success: function(jObj) {
            $("#spinnerModal").modal('hide');

            if (jObj.successful) {
                swal({ title: "", text: "Reply Added!", type: "success" }, function() {
                    location.reload();
                });
            } else {
                var err_msg = typeof jObj.error === 'undefined' || jObj.error === null ? 'Reply Failed!' : jObj.error;
                swal('', err_msg, "error");
            }
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
        convert_urls: true
    });
}