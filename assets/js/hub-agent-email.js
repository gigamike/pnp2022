var dtObj;

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

    $('.input-group.date.single').datepicker({
        format: baseDateFormat,
        autoclose: true,
        keyboardNavigation: false
    });

    $('.input-group.date.date-from').datepicker({
        format: baseDateFormat,
        autoclose: true,
        keyboardNavigation: false
    }).on('changeDate', function (e) {
        if ($('#' + $(':input', this).attr('id')).val() == "") {
            return;
        }

        var from_date = new Date(e.date);
        var dateParts = $('#' + $(':input', this).attr('attr-target-date')).val().split("/");
        var to_date = new Date();

        /**
         * CODE BRANCHING HERE - DATE FORMAT
         */
        if (baseDateFormat === "mm/dd/yyyy") {
            to_date.setFullYear(dateParts[2], parseInt(dateParts[0]) - 1, dateParts[1]);
        } else {
            //default format is dd/mm/yyyy
            to_date.setFullYear(dateParts[2], parseInt(dateParts[1]) - 1, dateParts[0]);
        }

        if (from_date > to_date) {
            $(this).datepicker('update', to_date);
        }
    });

    $('.input-group.date.date-to').datepicker({
        format: baseDateFormat,
        autoclose: true,
        keyboardNavigation: false
    }).on('changeDate', function (e) {
        if ($('#' + $(':input', this).attr('id')).val() == "") {
            return;
        }

        var to_date = new Date(e.date);
        var dateParts = $('#' + $(':input', this).attr('attr-target-date')).val().split("/");
        var from_date = new Date();

        /**
         * CODE BRANCHING HERE - DATE FORMAT
         */
        if (baseDateFormat === "mm/dd/yyyy") {
            from_date.setFullYear(dateParts[2], parseInt(dateParts[0]) - 1, dateParts[1]);
        } else {
            //default format is dd/mm/yyyy
            from_date.setFullYear(dateParts[2], parseInt(dateParts[1]) - 1, dateParts[0]);
        }

        if (to_date < from_date) {
            $(this).datepicker('update', from_date);
        }

    });

    $(".date-filter-operator").on('change', function () {
        var target_div = $(this).attr('attr-target-div');

        //QUERY_FILTER_IS_BETWEEN = 13
        if (parseInt($(this).val()) === 13) {
            $("#" + target_div + " .date-filter-single-input-div").css('display', 'none');
            $("#" + target_div + " .date-filter-multi-input-div").css('display', 'block');

            $("#" + target_div + " .input-group.date > :input").val("");

            $("#" + target_div + ' .input-group.date.date-single').datepicker('update', new Date()).datepicker('update', '');
            $("#" + target_div + ' .input-group.date.date-from').datepicker('update', new Date()).datepicker('update', '');
            $("#" + target_div + ' .input-group.date.date-to').datepicker('update', new Date()).datepicker('update', '');
        } else if ($("#" + target_div + " .date-filter-single-input-div").css('display') == "none") {
            $("#" + target_div + " .date-filter-single-input-div").css('display', 'block');
            $("#" + target_div + " .date-filter-multi-input-div").css('display', 'none');

            $("#" + target_div + " .input-group.date > :input").val("");
            $("#" + target_div + ' .input-group.date.date-single').datepicker('update', new Date()).datepicker('update', '');
            $("#" + target_div + ' .input-group.date.date-from').datepicker('update', new Date()).datepicker('update', '');
            $("#" + target_div + ' .input-group.date.date-to').datepicker('update', new Date()).datepicker('update', '');
        }
    });

    dtObj = $('#dtApplicationEmail').dataTable({
        dom: '<<t><"row m-t-sm"<"col-sm-6"i><"col-sm-6"p>>>',
        processing: true,
        serverSide: true,
        responsive: false,
        autoWidth: false,
        scrollX: true,
        scrollY: true,
        pageLength: 25,
        ajax: {
            "url": baseUrl + "agent/ajax-dt-get-email",
            "data": function (d) {
                d.searchText = $('#dtSearchText').val();

                d.emailLabel = $('#emailLabel').val();

                //filters
                d.filterTag = $('#filterTag').val() !== null ? $('#filterTag').val() : "";
                d.filterReferenceCodeOperator = $('#filterReferenceCodeOperator').val() !== null ? $('#filterReferenceCodeOperator').val() : "";
                d.filterReferenceCode = $('#filterReferenceCode').val();
                d.filterCustomerIdOperator = $('#filterCustomerIdOperator').val() !== null ? $('#filterCustomerIdOperator').val() : "";
                d.filterCustomerId = $('#filterCustomerId').val();
                d.filterSubjectOperator = $('#filterSubjectOperator').val() !== null ? $('#filterSubjectOperator').val() : "";
                d.filterSubject = $('#filterSubject').val();
                d.filterFromOperator = $('#filterFromOperator').val() !== null ? $('#filterFromOperator').val() : "";
                d.filterFrom = $('#filterFrom').val();
                d.filterToOperator = $('#filterToOperator').val() !== null ? $('#filterToOperator').val() : "";
                d.filterTo = $('#filterTo').val();
                d.filterDateSentOperator = $('#filterDateSentOperator').val() !== null ? $('#filterDateSentOperator').val() : "";
                d.filterDateSent = $('#filterDateSent').val();
                d.filterDateSentFrom = $('#filterDateSentFrom').val();
                d.filterDateSentTo = $('#filterDateSentTo').val();

                d.columnHeader = $("#columnEmailForm").serialize();
            }
        },
        columns: [
            {"data": "is_read", "orderable": false, "searchable": false},
            {"data": "from_name"},
            {"data": "subject"},
            {"data": "attachment", "orderable": false, "searchable": false},
            {"data": "date_processed", "searchable": false}
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
            "paginate": {"next": "<i class=\"fa fa-angle-right\"></i>", "previous": "<i class=\"fa fa-angle-left\"></i>"} //pagination
        },
        fnDrawCallback: function () {
            $("thead").remove();
        }
    });

    $(window).resize(function () {
        dtObj.fnAdjustColumnSizing();
    });

    $(".navbar-minimalize").click(function () {
        // add delay since inspinia.js adds delay in SmoothlyMenu()
        setTimeout(function () {
            dtObj.fnAdjustColumnSizing();
        }, 310);
    });

    //toggle visible columns
    $('.dt-toggle-col-visibility').on('click', function (e) {
        //e.preventDefault();
        var column = dtObj.api().column($(this).val());
        column.visible(!column.visible());

        //update sess
        $.ajax({
            type: "POST",
            url: baseUrl + "agent/ajax-set-email-visible-cols",
            data: $("#columnEmailForm").serialize()
        });
    });

    //event after dt reload
    $('#dtApplicationEmail').on('draw.dt', function (e, settings) {
        $('#filtersCountDiv').html((parseInt(settings.json.filtersCount) <= 0 ? "" : " " + settings.json.filtersCount));
    });

    //search
    $('#dtSearchText').bind('keyup', function (e) {
        if (e.keyCode === 13) {
            dtObj.api().ajax.reload();
        }
    });

    $('#dtSearchBtn').click(function () {
        $(this).blur();
        dtObj.api().ajax.reload();
    });

    //reset button
    $('#filterResetBtn').click(function () {
        $(this).blur();
        $("#dtSearchText").val('');
        $('#filterEmailDiv .filter-set').val('');
        $("#filterEmailDiv .select2-input").val("").trigger("change");
        $("#filterEmailDiv .select2-input.filter-operator").each(function () {
            $(this).val([$("option:first", this).val()]).trigger("change");
        });

        $('.input-group.date.date-single').datepicker('update', new Date()).datepicker('update', '');
        $('.input-group.date.date-from').datepicker('update', new Date()).datepicker('update', '');
        $('.input-group.date.date-to').datepicker('update', new Date()).datepicker('update', '');
        dtObj.api().ajax.reload();
    });


    //apply button
    $('#filterApplyBtn').click(function () {
        $(this).blur();
        $("#dtSearchText").val('');
        dtObj.api().ajax.reload();
    });

    $('.filter-value').bind('keyup', function (e) {
        if (e.keyCode === 13) {
            $('#filterApplyBtn').focus().click();
        }
    });

    $("#toggleColumnEmailBtn").click(function (e) {
        $("#filterEmailDiv").css('display', 'none');
        $("#columnEmailDiv").css('display', 'block');
    });

    $("#toggleFilterEmailBtn").click(function (e) {
        $("#filterEmailDiv").css('display', 'block');
        $("#columnEmailDiv").css('display', 'none');
    });

    $('#dtApplicationEmail').on("click", ".app-summary-row", function (event) {
        var the_class = event.target.className;
        var the_node = event.target.nodeName;

        if (the_node !== "INPUT" && the_class !== "checkbox") {
            window.location.href = baseUrl + 'agent/email-thread/' + $(this).attr('attr-email');
        }
    });

    $('#btnRefresh').click(function () {
        $(this).blur();
        dtObj.api().ajax.reload();
    });

    $("#emailLabel").on("change", function (e) {
        dtObj.api().ajax.reload();
    });

    $('#btnMarkAsRead').click(function () {
        $(this).blur();

        if ($("#dtApplicationEmail input:checkbox:checked").length <= 0) {
            swal('', "Please select at least one email message to mark as read", "error");
            return false;
        }

        var ids = [];
        $("input[type='checkbox'][name='is_read[]']:checked").each(function (i, e) {
            ids.push($(this).val());
        });

        $.ajax({
            type: "POST",
            data: {
                'is_read': ids
            },
            beforeSend: function () {
                $("#spinnerModal").modal('show');
                $('.loading-disabler').prop('disabled', false);
            },
            url: baseUrl + "agent/ajax-email-mark-as-read",
            success: function (jObj) {
                $("#spinnerModal").modal("hide");

                if (parseInt(jObj.status) === 1) {
                    swal({
                        title: "",
                        text: 'Selected email message(s) have been marked as read!',
                        type: "success",
                        confirmButtonColor: "#2C83FF",
                        confirmButtonText: "Close"
                    });
                    dtObj.api().ajax.reload();
                }
            }
        });
    });


    $('#toggleSearchBtn').click(function () {

        var state = $('#toggleSearchBtn .action-label').html();

        if (state == 'Search')
            dt_show_search();
        else
            dt_hide_search();

        dt_hide_columns();
        dt_hide_filters();

    });

    $('#toggleColumnEmailBtn').click(function () {

        var state = $('#toggleColumnEmailBtn .action-label').html();

        if (state == 'Columns')
            dt_show_columns();
        else
            dt_hide_columns();

        dt_hide_search();
        dt_hide_filters();

    });

    $('#toggleFilterEmailBtn').click(function () {
        var state = $('#toggleFilterEmailBtn .action-label').html();

        if (state == 'Filter')
            dt_show_filters();
        else
            dt_hide_filters();

        dt_hide_columns();
        dt_hide_search();

    });
});

function init_email() {
    setTimeout(function () {
        if (dtObj !== null) {
            $('input[type=checkbox][name=columnHeader\\[\\]]').each(function () {
                if (!this.checked) {
                    dtObj.api().column($(this).val()).visible(false);
                }
            });
        }
    }, 310);
}

function dt_hide_search() {

    $('#dtSearchContainer').hide();
    $('#toggleSearchBtn .action-label').html('Search');
}

function dt_hide_columns() {

    $('#dtColumnContainer').hide();
    $('#toggleColumnEmailBtn .action-label').html('Columns');
}

function dt_hide_filters() {

    $('#dtFilterContainer').hide();
    $('#toggleFilterEmailBtn .action-label').html('Filter');
}

function dt_show_search() {

    $('#dtSearchContainer').show();
    $('#toggleSearchBtn .action-label').html('Close');
}

function dt_show_columns() {

    $('#dtColumnContainer').show();
    $('#toggleColumnEmailBtn .action-label').html('Close');
}

function dt_show_filters() {

    $('#dtFilterContainer').show();
    $('#toggleFilterEmailBtn .action-label').html('Close');
}