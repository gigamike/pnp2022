$(document).ready(function() {
    //prevents caching
    $.ajaxSetup({ cache: false });

    //prevent form submission by ENTER
    $(window).keydown(function(event) {
        if (event.keyCode === 13) {
            event.preventDefault();
            return false;
        }
    });

    //icheck
    $('.i-checks').iCheck({
        checkboxClass: 'icheckbox_square-blue',
        radioClass: 'iradio_square-blue'
    });

    $('#saveBtn').click(function() {
        $(this).blur();
        $.ajax({
            type: 'POST',
            url: baseUrl + 'manager/settings/account-manager/permissions/ajax-permissions-save',
            data: $("#accountManagerPermissionsForm").serialize(),
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
                        location.reload();
                    });
                } else {
                    swal('', jObj.error, "error");
                }
            }
        });
    });
});

function init() {

}

function init_profile() {
    confirm_agent_addons_subscription('premium_support', '', 'manager/settings');
}