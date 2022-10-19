var dtObj;

$(document).ready(function() {

    //prevent form submission by ENTER
    $(window).keydown(function(event) {
        if (event.keyCode === 13) {
            event.preventDefault();
            return false;
        }
    });

    $.fn.select2.defaults.set("theme", "bootstrap");
    $(".select2-input").select2();

    dtObj = $('#dtCustomersSummaryTbl').dataTable({
        dom: '<<t><"row m-t-sm"<"col-sm-6"i><"col-sm-6"p>>>',
        processing: true,
        serverSide: true,
        responsive: false,
        autoWidth: false,
        scrollX: true,
        scrollY: true,
        pageLength: 25,
        ajax: {
            "url": baseUrl + "agent/ajax-dt-get-customers",
            "data": function(d) {
                d.searchText = $('#dtSearchText').val();
                //filters
                d.filterCustomerIdOperator = $('#filterCustomerIdOperator').val() !== null ? $('#filterCustomerIdOperator').val() : "";
                d.filterCustomerId = $('#filterCustomerId').val();
                d.filterNameOperator = $('#filterNameOperator').val() !== null ? $('#filterNameOperator').val() : "";
                d.filterName = $('#filterName').val();
                d.filterEmailOperator = $('#filterEmailOperator').val() !== null ? $('#filterEmailOperator').val() : "";
                d.filterEmail = $('#filterEmail').val();
                d.filterPhoneNumberOperator = $('#filterPhoneNumberOperator').val() !== null ? $('#filterPhoneNumberOperator').val() : "";
                d.filterPhoneNumber = $('#filterPhoneNumber').val();

                d.columnHeader = $("#columnCustomersForm").serialize();
            }
        },
        columns: [
            { "data": "customer_id", "className": "text-center" },
            { "data": "full_name" },
            { "data": "email", "className": "td-wrapping-line", "sortable": false, "searchable": false },
            { "data": "primary_phone", "sortable": false, "searchable": false },
            { "data": "application_count", "className": "text-center", "searchable": false },
            { "data": "last_added", "searchable": false },
            { "data": "last_actioned", "searchable": false },
            { "data": "last_updated", "searchable": false },
            { "data": "verified", "className": "text-center", "sortable": false, "searchable": false }
        ],
        order: [
            [$('#order_col').val(), $('#order_dir').val()]
        ],
        language: {
            "search": "_INPUT_", //search
            "searchPlaceholder": "Search Records",
            "lengthMenu": "Show _MENU_", //label
            "emptyTable": "None Found.",
            "info": "Showing _START_ - _END_ of _TOTAL_", //label
            "paginate": { "next": "<i class=\"fa fa-angle-right\"></i>", "previous": "<i class=\"fa fa-angle-left\"></i>" } //pagination
        }

    });

    $(window).resize(function() {
        dtObj.fnAdjustColumnSizing();
    });

    $(".navbar-minimalize").click(function() {
        // add delay since inspinia.js adds delay in SmoothlyMenu()
        setTimeout(function() {
            dtObj.fnAdjustColumnSizing();
        }, 310);
    });

    //event after dt reload
    $('#dtCustomersSummaryTbl').on('draw.dt', function(e, settings) {
        $('#filtersCountDiv').html((parseInt(settings.json.filtersCount) <= 0 ? "" : " " + settings.json.filtersCount));
    });

    $('#dtCustomersSummaryTbl').on("click", ".dt-row-cursor", function() {
        var customer = $(this).attr('attr-customer');
        $.ajax({
            type: "POST",
            data: { 'customer': customer },
            beforeSend: function() {
                $("#spinnerModal").modal('show');
            },
            url: baseUrl + "agent/ajax-dt-get-customers-expand-row",
            success: function(jObj) {
                $("#spinnerModal").modal("hide");
                if (jObj.successful) {
                    $("#customerRowModalContainer").html(jObj.html);
                    $("#customerRowModal").modal("show");
                }
            }
        });
    });

    //search
    $('#dtSearchText').bind('keyup', function(e) {
        if (e.keyCode === 13) {
            dtObj.api().ajax.reload();
        }
    });

    $('#dtSearchBtn').click(function() {
        $(this).blur();
        dtObj.api().ajax.reload();
    });

    $("#toggleColumnCustomersBtn").click(function(e) {
        $("#filterCustomersDiv").css('display', 'none');
        $("#columnCustomersDiv").css('display', 'block');
    });

    $("#toggleFilterCustomersBtn").click(function(e) {
        $("#filterCustomersDiv").css('display', 'block');
        $("#columnCustomersDiv").css('display', 'none');
    });

    //reset button
    $('#filterResetBtn').click(function() {
        $(this).blur();
        $("#dtSearchText").val('');
        $('#filterCustomersDiv .filter-set').val('');
        $("#filterCustomersDiv .select2-input").val("").trigger("change");
        $("#filterCustomersDiv .select2-input.filter-operator").each(function() {
            $(this).val([$("option:first", this).val()]).trigger("change");
        });

        dtObj.api().ajax.reload();
    });

    //apply button
    $('#filterApplyBtn').click(function() {
        $(this).blur();
        $("#dtSearchText").val('');
        dtObj.api().ajax.reload();
    });

    $('.filter-value').bind('keyup', function(e) {
        if (e.keyCode === 13) {
            $('#filterApplyBtn').focus().click();
        }
    });

    //toggle visible columns
    $('.dt-toggle-col-visibility').on('click', function(e) {
        //e.preventDefault();
        var column = dtObj.api().column($(this).val());
        column.visible(!column.visible());

        //update sess
        $.ajax({
            type: "POST",
            url: baseUrl + "agent/ajax-set-customers-visible-cols",
            data: $("#columnCustomersForm").serialize()
        });
    });

});

function init_customers() {
    setTimeout(function() {
        if (dtObj !== null) {
            $('input[type=checkbox][name=columnHeader\\[\\]]').each(function() {
                if (!this.checked) {
                    dtObj.api().column($(this).val()).visible(false);
                }
            });
        }
    }, 310);
}