

$(document).ready(function () {

    //tooltip
    $('[data-toggle="tooltip"]').tooltip();

    //typeahead
    var timeout;
    $("#business_address").typeahead({
        minLength: 4,
        autoSelect: false,
        source: function (query, process) {
            if (timeout) {
                clearTimeout(timeout);
            }
            timeout = setTimeout(function () {
                return ajax_complete_address(query, process);
            }, 400);
        },
        matcher: function (item) {
            return true;
        },
        highlighter: Object,
        displayText: function (item) {
            return item.id === "google" ? '<img src="' + baseUrl + 'assets/img/powered-by-google/desktop/powered_by_google_on_white.png">' : item.name;
        },
        afterSelect: function (item) {
            $("#business_address").val(item.name).change();
        }
    });



    $("#prepaid_mastercard_debit_address").typeahead({
        minLength: 4,
        autoSelect: false,
        source: function (query, process) {
            if (timeout) {
                clearTimeout(timeout);
            }
            timeout = setTimeout(function () {
                return ajax_complete_address(query, process);
            }, 400);
        },
        matcher: function (item) {
            return true;
        },
        highlighter: Object,
        displayText: function (item) {
            return item.id === "google" ? '<img src="' + baseUrl + 'assets/img/powered-by-google/desktop/powered_by_google_on_white.png">' : item.name;
        },
        afterSelect: function (item) {
            $("#prepaid_mastercard_debit_address").val(item.name).change();
        }
    });




    // Payment method
    $('input[name=payment_method]').change(function () {
        //hide all sub options
        $(".payment-method-options").css('display', 'none');
        $(".payment-method-options :input").prop('disabled', true).prop('required', false);


        //show selected method options
        $("#userPaymentMethodDiv-" + $(this).val()).css('display', 'block');
        $("#userPaymentMethodDiv-" + $(this).val() + " :input").prop('disabled', false).prop('required', true);

        /**
         * CODE BRANCHING HERE - COUNTRY
         *      AU
         *      NZ
         *      NZ
         *      UK
         */
        if (baseCountry === "AU") {
            if (parseInt($(this).attr('attr-show-abn')) === 1) {
                $("#userAbnDiv").css('display', 'block');
                $("#userAbnDiv :input").prop('disabled', false);
            } else {
                $("#userAbnDiv").css('display', 'none');
                $("#userAbnDiv :input").prop('disabled', true);
            }
        } else if (baseCountry === "NZ") {
            if (parseInt($(this).attr('attr-show-irdn')) === 1) {
                $("#userIRDNDiv").css('display', 'block');
                $("#userIRDNDiv :input").prop('disabled', false);
            } else {
                $("#userIRDNDiv").css('display', 'none');
                $("#userIRDNDiv :input").prop('disabled', true);
            }
        } else if (baseCountry === "US") {

        } else if (baseCountry === "UK") {
            if (parseInt($(this).attr('attr-show-crn')) === 1) {
                $("#userCRNDiv").css('display', 'block');
                $("#userCRNDiv :input").prop('disabled', false);
            } else {
                $("#userCRNDiv").css('display', 'none');
                $("#userCRNDiv :input").prop('disabled', true);
            }
        }
    });



});



function ajax_complete_address(query, process) {
    $.ajax({
        type: "POST",
        url: baseUrl + "register/ajax-complete-address",
        dataType: 'json',
        data: {term: query},
        success: function (data) {
            return process(data);
        }
    });
}

function ajax_parse_address(item) {
    if (item.id === "manualAddress") {
        return;
    }

    $.ajax({
        type: "POST",
        url: baseUrl + "register/ajax-parse-address",
        dataType: 'json',
        data: {id: item.id, value: item.name},
        success: function (jsonObj) {}
    });
}

