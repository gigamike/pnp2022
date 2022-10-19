$(document).ready(function() {
    $('#registerBtn').click(function() {
        $(this).blur();
        grecaptcha.execute();
    });
});

function onSubmit(token) {
    $("#registrationForm").submit();
}