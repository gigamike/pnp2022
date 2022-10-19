var dtObj;

$(document).ready(function () {

    //prevent form submission by ENTER
    $(window).keydown(function (event) {
        if (event.keyCode === 13) {
            event.preventDefault();
            return false;
        }
    });

    /*
     //switchery
     switchery_toggle_data = document.querySelector('.js-switch-togle-data');
     new Switchery(switchery_toggle_data, { color: '#2C83FF', 'size': 'small' });

     switchery_toggle_data.onchange = function() {
     var is_checked = switchery_toggle_data.checked ? "1" : "0";
     $.ajax({
     type: "POST",
     url: baseUrl + "login/ajax - set - hub - user - settings",
     data: {
     'key': 'sample_data_in_graphs',
     'val': is_checked
     },
     success: function() {
     $("#use_sample_data").val(is_checked);

     //load charts
     load_chart_commissions();
     }
     });
     };
     */

    $(".toggle-sample-data").click(function () {
        var is_checked = parseInt($("#use_sample_data").val()) === 1 ? 0 : 1;
        toggle_sample_data(is_checked);
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


    var connection_columns = new Array();
    //manager
    connection_columns[5] = [
        {"data": "reference_code"},
        {"data": "customer_id"},
        {"data": "full_name"},
        {"data": "service_name", "searchable": false},
        {"data": "agent_full_name", "searchable": false},
        {"data": "application_status", "orderable": false, "searchable": false},
        {"data": "connection_status", "orderable": false, "searchable": false},
        {"data": "payment_status", "orderable": false, "searchable": false},
        {"data": "metatag", "orderable": false, "searchable": false},
        {"data": "manager_potential_comms", "searchable": false},
        {"data": "partner_potential_comms", "searchable": false},
        {"data": "agent_potential_comms", "searchable": false},
        {"data": "total_potential_comms", "searchable": false},
        {"data": "manager_actual_comms", "searchable": false},
        {"data": "partner_actual_comms", "searchable": false},
        {"data": "agent_actual_comms", "searchable": false},
        {"data": "total_actual_comms", "searchable": false},
        {"data": "receipt_code"},
        {"data": "date_added", "searchable": false},
        {"data": "date_actioned", "searchable": false},
        {"data": "date_paid", "searchable": false}
    ];
    //partner
    connection_columns[1] = [
        {"data": "reference_code"},
        {"data": "customer_id"},
        {"data": "full_name"},
        {"data": "service_name", "searchable": false},
        {"data": "agent_full_name", "searchable": false},
        {"data": "application_status", "orderable": false, "searchable": false},
        {"data": "connection_status", "orderable": false, "searchable": false},
        {"data": "payment_status", "orderable": false, "searchable": false},
        {"data": "metatag", "orderable": false, "searchable": false},
        {"data": "partner_potential_comms", "searchable": false},
        {"data": "agent_potential_comms", "searchable": false},
        {"data": "total_potential_comms", "searchable": false},
        {"data": "partner_actual_comms", "searchable": false},
        {"data": "agent_actual_comms", "searchable": false},
        {"data": "total_actual_comms", "searchable": false},
        {"data": "receipt_code"},
        {"data": "date_added", "searchable": false},
        {"data": "date_actioned", "searchable": false},
        {"data": "date_paid", "searchable": false}
    ];
    //agent
    connection_columns[3] = [
        {"data": "reference_code"},
        {"data": "customer_id"},
        {"data": "full_name"},
        {"data": "service_name", "searchable": false},
        {"data": "agent_full_name", "searchable": false},
        {"data": "application_status", "orderable": false, "searchable": false},
        {"data": "connection_status", "orderable": false, "searchable": false},
        {"data": "payment_status", "orderable": false, "searchable": false},
        {"data": "metatag", "orderable": false, "searchable": false},
        {"data": "agent_potential_comms", "searchable": false},
        {"data": "agent_actual_comms", "searchable": false},
        {"data": "receipt_code"},
        {"data": "date_added", "searchable": false},
        {"data": "date_actioned", "searchable": false},
        {"data": "date_paid", "searchable": false}
    ];

    dtObj = $('#dtApplicationCommissions').dataTable({
        dom: '<<t><"row m-t-sm"<"col-sm-6"i><"col-sm-6"p>>>',
        processing: true,
        serverSide: true,
        responsive: false,
        autoWidth: false,
        scrollX: true,
        scrollY: true,
        pageLength: 25,
        ajax: {
            "url": baseUrl + "agent/ajax-dt-get-commissions",
            "data": function (d) {
                d.searchText = $('#dtSearchText').val();
                //filters
                d.filterReferenceCodeOperator = $('#filterReferenceCodeOperator').val() !== null ? $('#filterReferenceCodeOperator').val() : "";
                d.filterReferenceCode = $('#filterReferenceCode').val();
                d.filterCustomerIdOperator = $('#filterCustomerIdOperator').val() !== null ? $('#filterCustomerIdOperator').val() : "";
                d.filterCustomerId = $('#filterCustomerId').val();
                d.filterNameOperator = $('#filterNameOperator').val() !== null ? $('#filterNameOperator').val() : "";
                d.filterName = $('#filterName').val();
                d.filterApplicationTypeOperator = $('#filterApplicationTypeOperator').val() !== null ? $('#filterApplicationTypeOperator').val() : "";
                d.filterApplicationType = $('#filterApplicationType').val() !== null ? $('#filterApplicationType').val().join() : "";
                d.filterOfferTypeOperator = $('#filterOfferTypeOperator').val() !== null ? $('#filterOfferTypeOperator').val() : "";
                d.filterOfferType = $('#filterOfferType').val() !== null ? $('#filterOfferType').val().join() : "";
                d.filterApplicationStatusOperator = $('#filterApplicationStatusOperator').val() !== null ? $('#filterApplicationStatusOperator').val() : "";
                d.filterApplicationStatus = $('#filterApplicationStatus').val() !== null ? $('#filterApplicationStatus').val().join() : "";
                d.filterConnectionStatusOperator = $('#filterConnectionStatusOperator').val() !== null ? $('#filterConnectionStatusOperator').val() : "";
                d.filterConnectionStatus = $('#filterConnectionStatus').val() !== null ? $('#filterConnectionStatus').val().join() : "";
                d.filterPaymentStatusOperator = $('#filterPaymentStatusOperator').val() !== null ? $('#filterPaymentStatusOperator').val() : "";
                d.filterPaymentStatus = $('#filterPaymentStatus').val() !== null ? $('#filterPaymentStatus').val().join() : "";
                d.filterDateAddedOperator = $('#filterDateAddedOperator').val() !== null ? $('#filterDateAddedOperator').val() : "";
                d.filterDateAdded = $('#filterDateAdded').val();
                d.filterDateAddedFrom = $('#filterDateAddedFrom').val();
                d.filterDateAddedTo = $('#filterDateAddedTo').val();
                d.filterDateActionedOperator = $('#filterDateActionedOperator').val() !== null ? $('#filterDateActionedOperator').val() : "";
                d.filterDateActioned = $('#filterDateActioned').val();
                d.filterDateActionedFrom = $('#filterDateActionedFrom').val();
                d.filterDateActionedTo = $('#filterDateActionedTo').val();
                d.filterDatePaidOperator = $('#filterDatePaidOperator').val() !== null ? $('#filterDatePaidOperator').val() : "";
                d.filterDatePaid = $('#filterDatePaid').val();
                d.filterDatePaidFrom = $('#filterDatePaidFrom').val();
                d.filterDatePaidTo = $('#filterDatePaidTo').val();
                d.filterMetatagOperator = $('#filterMetatagOperator').val() !== null ? $('#filterMetatagOperator').val() : "";
                d.filterMetatag = $('#filterMetatag').val();

                d.columnHeader = $("#columnCommissionsForm").serialize();
            }
        },
        columns: connection_columns[parseInt($("#mainRole").val())],
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
            url: baseUrl + "agent/ajax-set-commissions-visible-cols",
            data: $("#columnCommissionsForm").serialize()
        });
    });

    //event after dt reload
    $('#dtApplicationCommissions').on('draw.dt', function (e, settings) {
        $('#filtersCountDiv').html((parseInt(settings.json.filtersCount) <= 0 ? "" : " " + settings.json.filtersCount));
    });


    $('#dtApplicationCommissions').on("click", ".app-summary-row", function () {

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
        $('#filterCommissionsDiv .filter-set').val('');
        $("#filterCommissionsDiv .select2-input").val("").trigger("change");
        $("#filterCommissionsDiv .select2-input.filter-operator").each(function () {
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


    $('#toggleSearchBtn').click(function () {

        var state = $('#toggleSearchBtn .action-label').html();

        if (state == 'Search')
            dt_show_search();
        else
            dt_hide_search();

        dt_hide_columns();
        dt_hide_filters();

    });

    $('#toggleColumnCommissionsBtn').click(function () {

        var state = $('#toggleColumnCommissionsBtn .action-label').html();

        if (state == 'Columns')
            dt_show_columns();
        else
            dt_hide_columns();

        dt_hide_search();
        dt_hide_filters();

    });

    $('#toggleFilterCommissionsBtn').click(function () {
        var state = $('#toggleFilterCommissionsBtn .action-label').html();

        if (state == 'Filter')
            dt_show_filters();
        else
            dt_hide_filters();

        dt_hide_columns();
        dt_hide_search();

    });


});


function init_commissions() {
    setTimeout(function () {
        if (dtObj !== null) {
            $('input[type=checkbox][name=columnHeader\\[\\]]').each(function () {
                if (!this.checked) {
                    dtObj.api().column($(this).val()).visible(false);
                }
            });
        }
    }, 310);

    //load charts
    load_chart_commissions();
}

function toggle_sample_data(is_checked) {
    if (parseInt(is_checked) === 1) {
        $("#sampleDataToggleDiv").css('display', 'none');
        $("#sampleDataAlertDiv").css('display', 'block');
    } else {
        $("#sampleDataToggleDiv").css('display', 'block');
        $("#sampleDataAlertDiv").css('display', 'none');
    }

    $.ajax({
        type: "POST",
        url: baseUrl + "login/ajax-set-hub-user-settings",
        data: {
            'key': 'sample_data_in_graphs',
            'val': is_checked
        },
        success: function () {
            $("#use_sample_data").val(is_checked);
            load_all();
        }
    });
}

var chart_commissions_obj = null;
var chart_commissions_data = null;

function load_chart_commissions() {

    $.ajax({
        type: "POST",
        url: baseUrl + "agent/ajax-main-chart-commissions",
        cache: false,
        data: {
            'test': $("#use_sample_data").val(),
            'start': $("#referenceDateStart").val(),
            'end': $("#referenceDateEnd").val()
        },
        success: function (jObj) {
            chart_commissions_data = {
                labels: jObj.xticks,
                datasets: jObj.dataset
            };

            if (chart_commissions_obj !== null) {
                chart_commissions_obj.destroy();
            }

            //create canvas dynamically
            //if containers is hidden, chartjs sets the height to 0
            $("#chart-commissions-div-wrapper").html('<canvas id="chart-commissions-div" height="80"></canvas>');

            var canvas = document.getElementById("chart-commissions-div");
            var ctx = canvas.getContext("2d");


            chart_commissions_obj = new Chart(ctx, {
                //type: 'horizontalBar',
                type: 'bar',
                data: chart_commissions_data,
                options: chart_bar_stacked_nogrid_opts
            });


            //load commissions data
            $.ajax({
                type: "POST",
                url: baseUrl + "agent/ajax-main-data-commissions",
                cache: false,
                data: {
                    'test': $("#use_sample_data").val(),
                    'start': $("#referenceDateStart").val(),
                    'end': $("#referenceDateEnd").val()
                },
                success: function (jObj) {
                    //reset all to 0 first
                    $("#commissionsStatsDiv .comms").html(baseCurrency + '0.00');
                    var sub_total = 0.00;
                    var potential_total = 0.00;

                    //pending move
                    if (typeof jObj.pending_move != "undefined") {
                        sub_total = 0.00;
                        $.each(jObj.pending_move, function (k, v) {
                            sub_total += parseFloat(v);
                            $("#commsPendingMoveDiv .role-" + k + " .comms").html(baseCurrency + parseFloat(v).toLocaleString('en', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                        });

                        $("#commsPendingMoveDiv .comms-total .comms").html(baseCurrency + sub_total.toLocaleString('en', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                        potential_total += sub_total;
                    }

                    //redeemable
                    if (typeof jObj.redeemable != "undefined") {
                        sub_total = 0.00;
                        $.each(jObj.redeemable, function (k, v) {
                            sub_total += parseFloat(v);
                            $("#commsRedeemableDiv .role-" + k + " .comms").html(baseCurrency + parseFloat(v).toLocaleString('en', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                        });

                        $("#commsRedeemableDiv .comms-total .comms").html(baseCurrency + sub_total.toLocaleString('en', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                        potential_total += sub_total;
                    }

                    //paid
                    if (typeof jObj.paid != "undefined") {
                        sub_total = 0.00;
                        $.each(jObj.paid, function (k, v) {
                            sub_total += parseFloat(v);
                            $("#commsPaidDiv .role-" + k + " .comms").html(baseCurrency + parseFloat(v).toLocaleString('en', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                        });

                        $("#commsPaidDiv .comms-total .comms").html(baseCurrency + sub_total.toLocaleString('en', {minimumFractionDigits: 2, maximumFractionDigits: 2}));


                        //set total comms: same as paid
                        //$("#metricTotalCommissions").html(sub_total.toLocaleString('en', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    }


                    //set potential comms
                    //$("#metricPotentialCommissions").html(potential_total.toLocaleString('en', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                }
            });
        }
    });

}

function dt_hide_search() {

    $('#dtSearchContainer').hide();
    $('#toggleSearchBtn .action-label').html('Search');
}

function dt_hide_columns() {

    $('#dtColumnContainer').hide();
    $('#toggleColumnCommissionsBtn .action-label').html('Columns');
}

function dt_hide_filters() {

    $('#dtFilterContainer').hide();
    $('#toggleFilterCommissionsBtn .action-label').html('Filter');
}

function dt_show_search() {

    $('#dtSearchContainer').show();
    $('#toggleSearchBtn .action-label').html('Close');
}

function dt_show_columns() {

    $('#dtColumnContainer').show();
    $('#toggleColumnCommissionsBtn .action-label').html('Close');
}

function dt_show_filters() {

    $('#dtFilterContainer').show();
    $('#toggleFilterCommissionsBtn .action-label').html('Close');
}