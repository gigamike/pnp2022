$(document).ready(function() {
    $(window).keydown(function(event) {
        if (event.keyCode === 13) {
            event.preventDefault();
            return false;
        }
    });

    $.fn.select2.defaults.set("theme", "bootstrap");
    $(".select2-input").select2();

    $('.tagsinput').tagsinput({
        tagClass: 'label label-primary'
    });

    getTinymce();

    $('#ticket_category_id').change(function() {
        getTicketCategory();
    });

    var rules_options = {
        subject: { required: true, maxlength: 255 }
    };

    formValidator = $("#ticketForm").validate({
        rules: rules_options,
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
            } else {
                error.insertAfter(element);
            }
        }
    });

    $('#resetBtn').click(function() {
        $(this).blur();
        $("#ticketForm")[0].reset();
    });

    $('#saveBtn').click(function() {
        $(this).blur();

        //validate form
        if (!$("#ticketForm").valid()) {
            return;
        }

        // required if saving from ajax
        tinyMCE.triggerSave(true, true);

        // Create a formdata object and add the files
        var data = new FormData(document.getElementById('ticketForm'));
        $.each($(':file'), function(i, file) {
            data.append('file-' + i, file);
        });

        //save
        $.ajax({
            type: 'POST',
            url: baseUrl + 'connect-sd/tickets/ajax-ticket-save',
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
                        window.location.href = baseUrl + "connect-sd/tickets/view/" + jObj.ticket_id;
                    });
                } else {
                    swal('Ooops!', jObj.error, "error");
                }
            }
        });
    });

    getTicketCategory();
});

function getTicketCategory() {
    $.ajax({
        type: 'POST',
        url: baseUrl + 'connect-sd/tickets/ajax-get-ticket-category',
        data: {
            ticket_category_id: $("#ticket_category_id").val()
        },
        success: function(jObj) {
            $('#ticketCategoryWrapper').html(jObj.html);

            $.fn.select2.defaults.set("theme", "bootstrap");
            $(".select2-input").select2();

            $('#ticket_category_template_id').change(function() {
                $.ajax({
                    type: 'POST',
                    url: baseUrl + 'connect-sd/tickets/ajax-get-ticket-category-template',
                    data: {
                        ticket_category_template_id: $(this).val()
                    },
                    success: function(jObj) {
                        $("#subject").val(jObj.subject);
                        $('#body').val(jObj.description);
                        $("#urgency").select2("val", jObj.urgency);
                        $("#impact").select2("val", jObj.impact);
                        $("#tags").tagsinput('removeAll');
                        $('#tags').tagsinput('add', jObj.tags);
                        $('#tag').tagsinput('refresh');
                    }
                });
            });

            $('#subject').val('');
            $('#body').val("");
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