$(document).ready(function() {
    $('#registerBtn').click(function() {
        $(this).blur();
        $("#registrationForm").submit();
    });
});