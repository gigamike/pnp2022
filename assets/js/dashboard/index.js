var dtObj;

$(document).ready(function() {

    //prevent form submission by ENTER
    $(window).keydown(function(event) {
        if (event.keyCode === 13) {
            event.preventDefault();
            return false;
        }
    });

    $('#hubDaterangePicker .input-daterange').datepicker({
        keyboardNavigation: false,
        forceParse: false,
        autoclose: true,
        format: baseDateFormat,
        todayBtn: "linked"
    }).on('changeDate', function(e) {
        $('#hubDaterangePicker .actions-addon').css('display', 'inline');
    });

    $('#hubDaterangeApplyBtn').click(function() {
        $(this).blur();
        if ($('#hubDaterangePicker input[name=start]').val() !== "" && $('#hubDaterangePicker input[name=end]').val() !== "") {
            load_dashboard_metrics();
            $('#hubDaterangePicker .actions-addon').css('display', 'none');
        } else {
            date_range_alltime();
        }
    });

    $('#hubDaterangeCancelBtn').click(function() {
        $(this).blur();
        date_range_alltime();
    });

    $('.filterUserType').click(function() {
        $('.filterUserType').closest("li").removeClass("active");
        $(this).closest("li").addClass("active");

        $('#filterUserType').val($(this).attr("attr-user-type"));

        load_dashboard_metrics();
    });

    $('.filterApp').click(function() {
        $('.filterApp').closest("li").removeClass("active");
        $(this).closest("li").addClass("active");

        $('#filterApp').val($(this).attr("attr-app"));

        load_dashboard_metrics();
    });

    load_dashboard_metrics();
});

function date_range_alltime() {
    clear_date_range();
    $('#hubDaterangePicker input[name=start]').datepicker('update', $("#referenceDateStart").val());
    $('#hubDaterangePicker input[name=end]').datepicker('update', moment().format(baseDateFormat.toUpperCase()));

    load_dashboard_metrics();
}

function clear_date_range() {
    $('#hubDaterangePicker input').each(function() {
        $(this).datepicker('clearDates');
    });

    $('#hubDaterangePicker .actions-addon').css('display', 'none');
}

function date_range_alltime() {
    clear_date_range();
    $('#hubDaterangePicker input[name=start]').datepicker('update', $("#referenceDateStart").val());
    $('#hubDaterangePicker input[name=end]').datepicker('update', moment().format(baseDateFormat.toUpperCase()));

    load_dashboard_metrics();
}

function date_range_today() {
    clear_date_range();

    var today = moment().format(baseDateFormat.toUpperCase());
    $('#hubDaterangePicker input[name=start]').datepicker('update', today);
    $('#hubDaterangePicker input[name=end]').datepicker('update', today);

    load_dashboard_metrics();
}

function date_range_last7days() {
    clear_date_range();
    $('#hubDaterangePicker input[name=start]').datepicker('update', moment().subtract(6, 'days').format(baseDateFormat.toUpperCase()));
    $('#hubDaterangePicker input[name=end]').datepicker('update', moment().format(baseDateFormat.toUpperCase()));

    load_dashboard_metrics();
}

function date_range_last30days() {
    clear_date_range();
    $('#hubDaterangePicker input[name=start]').datepicker('update', moment().subtract(29, 'days').format(baseDateFormat.toUpperCase()));
    $('#hubDaterangePicker input[name=end]').datepicker('update', moment().format(baseDateFormat.toUpperCase()));

    load_dashboard_metrics();
}

function date_range_thismonth() {
    clear_date_range();
    $('#hubDaterangePicker input[name=start]').datepicker('update', moment().startOf('month').format(baseDateFormat.toUpperCase()));
    $('#hubDaterangePicker input[name=end]').datepicker('update', moment().endOf('month').format(baseDateFormat.toUpperCase()));

    load_dashboard_metrics();
}

function date_range_lastmonth() {
    clear_date_range();
    $('#hubDaterangePicker input[name=start]').datepicker('update', moment().subtract(1, 'month').startOf('month').format(baseDateFormat.toUpperCase()));
    $('#hubDaterangePicker input[name=end]').datepicker('update', moment().subtract(1, 'month').endOf('month').format(baseDateFormat.toUpperCase()));

    load_dashboard_metrics();
}

function date_range_thisyear() {
    clear_date_range();
    $('#hubDaterangePicker input[name=start]').datepicker('update', moment().startOf('year').format(baseDateFormat.toUpperCase()));
    $('#hubDaterangePicker input[name=end]').datepicker('update', moment().endOf('year').format(baseDateFormat.toUpperCase()));

    load_dashboard_metrics();
}

function date_range_lastyear() {
    clear_date_range();
    $('#hubDaterangePicker input[name=start]').datepicker('update', moment().subtract(1, 'year').startOf('year').format(baseDateFormat.toUpperCase()));
    $('#hubDaterangePicker input[name=end]').datepicker('update', moment().subtract(1, 'year').endOf('year').format(baseDateFormat.toUpperCase()));

    load_dashboard_metrics();
}

function load_dashboard_metrics() {
    metrics();
    pieChart();
    barChart();
    table();
}

function table() {
    dtObj = $('#dtPlateNumbersTbl').dataTable({
        dom: '<<t><"row m-t-sm"<"col-sm-6"i><"col-sm-6"p>>>',
        processing: true,
        serverSide: true,
        responsive: false,
        autoWidth: false,
        scrollX: true,
        scrollY: true,
        ajax: {
            "url": baseUrl + "dashboard/ajax-dt-get-plate-numbers",
            "data": function(d) {
                d.searchText = $('#dtSearchText').val();

                d.filterDateAddedOperator = 13; // QUERY_FILTER_IS_BETWEEN
                d.filterDateAddedFrom = $('#hubDaterangePicker input[name=start]').val();
                d.filterDateAddedTo = $('#hubDaterangePicker input[name=end]').val();
            }
        },
        columns: [
            { "data": "date_added", "searchable": false },
            { "data": "img_url", "searchable": false, "orderable": false },
            { "data": "plate_number" },
            { "data": "tracking_type" },
            { "data": "pi_device_u_code" },
            { "data": "location" },
            { "data": "comments" },
            { "data": "sms_notified", "searchable": false },
            { "data": "actions", "orderable": false, "searchable": false }
        ],
        order: [
            [0, 'desc']
        ],
        language: {
            "search": "_INPUT_", //search
            "searchPlaceholder": "Search Records",
            "lengthMenu": "Show _MENU_", //label
            "emptyTable": "None Found.",
            "info": "_START_ to _END_ of _TOTAL_", //label
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
    $('#dtPlateNumbersTbl').on('draw.dt', function(e, settings) {
        //redefine tooltips
        $('#dtPlateNumbersTbl [data-toggle="tooltip"]').tooltip('destroy');
        $('#dtPlateNumbersTbl [data-toggle="tooltip"]').tooltip();
    });

    $('#dtSearchText').bind('keyup', function(e) {
        if (e.keyCode === 13) {
            dtObj.api().ajax.reload();
        }
    });

    $('#dtSearchBtn').click(function() {
        dtObj.api().ajax.reload();
    });

    $('#toggleSearchBtn').click(function() {

        var state = $('#toggleSearchBtn .action-label').html();

        if (state == 'Search')
            dt_show_search();
        else
            dt_hide_search();
    });
}

function dt_hide_search() {
    $('#dtSearchContainer').hide();
    $('#toggleSearchBtn .action-label').html('Search');
}

function dt_show_search() {
    $('#dtSearchContainer').show();
    $('#toggleSearchBtn .action-label').html('Close');
}

function metrics() {
    $.ajax({
        type: 'POST',
        url: baseUrl + 'dashboard/ajax-metrics',
        data: {
            'filterStart': $('#hubDaterangePicker input[name=start]').val(),
            'filterEnd': $('#hubDaterangePicker input[name=end]').val(),
            'filterUserType': $('#filterUserType').val(),
            'filterApp': $('#filterApp').val()
        },
        success: function(jObj) {
            if (jObj.successful) {
                $('.countPlateNumberHotlistDetected').html(jObj.countPlateNumberHotlistDetected);
                $('.countPlateNumberWhitelistDetected').html(jObj.countPlateNumberWhitelistDetected);
                $('.countPlateNumbers').html(jObj.countPlateNumbers);
                $('.countPIDevices').html(jObj.countPIDevices);
            } else {
                $('.countPlateNumberHotlistDetected').html(0);
                $('.countPlateNumberWhitelistDetected').html(0);
                $('.countPlateNumbers').html(0);
                $('.countPIDevices').html(0);
            }

        }
    });
}

function pieChart() {
    $.ajax({
        type: 'POST',
        url: baseUrl + 'dashboard/ajax-pie-chart',
        data: {
            'filterStart': $('#hubDaterangePicker input[name=start]').val(),
            'filterEnd': $('#hubDaterangePicker input[name=end]').val(),
            'filterUserType': $('#filterUserType').val(),
            'filterApp': $('#filterApp').val()
        },
        success: function(jObj) {
            if (jObj.successful) {
                var data = {
                    labels: [
                        'Checkpoint 10 Manila',
                        'Checkpoint 12 Paranaque',
                        'Checkpoint 1 Quezon City',
                        'Checkpoint 10 Pasay'
                    ],
                    datasets: [{
                        label: 'Ticket Status',
                        data: jObj.data,
                        backgroundColor: [
                            'rgb(54, 162, 235)',
                            'rgb(0, 128, 0)',
                            'rgb(255, 205, 86)',
                            'rgb(128,128,128)'
                        ],
                        hoverOffset: 4
                    }]
                };

                var config = {
                    type: 'pie',
                    data: data,
                    options: {
                        responsive: true
                    }
                };

                $("#pieChartWrapper").html('<canvas id="pieChart"></canvas>');
                var myChart = new Chart(
                    document.getElementById('pieChart'),
                    config
                );
            }

        }
    });
}

function barChart() {
    $.ajax({
        type: 'POST',
        url: baseUrl + 'dashboard/ajax-bar-chart',
        data: {
            'filterStart': $('#hubDaterangePicker input[name=start]').val(),
            'filterEnd': $('#hubDaterangePicker input[name=end]').val(),
            'filterUserType': $('#filterUserType').val(),
            'filterApp': $('#filterApp').val()
        },
        success: function(jObj) {
            if (jObj.successful) {
                var labels = jObj.labels;

                var data = {
                    labels: labels,
                    datasets: [{
                            label: 'Carnapped',
                            data: jObj.dataOpenTickets,
                            borderColor: 'rgb(54, 162, 235)',
                            backgroundColor: 'rgb(54, 162, 235)'
                        },
                        {
                            label: 'Not registered',
                            data: jObj.dataResolvedTickets,
                            borderColor: 'rgb(0, 128, 0)',
                            backgroundColor: 'rgb(0, 128, 0)'
                        },
                        {
                            label: 'Under probation',
                            data: jObj.dataReopenTickets,
                            borderColor: 'rgb(255, 205, 86)',
                            backgroundColor: 'rgb(255, 205, 86)'
                        },
                        {
                            label: 'Special Case',
                            data: jObj.dataClosedTickets,
                            borderColor: 'rgb(128,128,128)',
                            backgroundColor: 'rgb(128,128,128)'
                        }
                    ]
                };

                var config = {
                    type: 'bar',
                    data: data,
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            title: {
                                display: true,
                                text: 'Incidents'
                            }
                        }
                    }
                };

                $("#barChartWrapper").html('<canvas id="barChart"></canvas>');
                var myChart = new Chart(
                    document.getElementById('barChart'),
                    config
                );
            }

        }
    });
}

function delete_to(id) {
    swal({
        title: "Are you sure you want to delete this Plate Number Log?",
        text: "This action cannot be undone. All records related to this Plate Number Log will be deleted too.",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#0072bc",
        confirmButtonText: "Yes!",
        closeOnConfirm: true
    }, function() {
        $.ajax({
            type: 'POST',
            url: baseUrl + 'dashboard/ajax-delete',
            data: {
                'log_id': id
            },
            beforeSend: function() {
                $("#spinnerModal").modal('show');
            },
            success: function(jObj) {
                if (jObj.successful) {
                    swal({
                        title: "",
                        text: jObj.message,
                        type: "success",
                        confirmButtonColor: "#2C83FF",
                        confirmButtonText: "Close"
                    }, function() {

                    });

                    window.location.href = baseUrl + "dashboard";
                } else {
                    $("#spinnerModal").modal("hide");
                    swal('Ooops!', jObj.error, "error");
                }
            }
        });
    });
}