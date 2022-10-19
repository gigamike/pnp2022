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
     new Switchery(switchery_toggle_data, {color: '#2C83FF', 'size': 'small'});

     switchery_toggle_data.onchange = function () {
     var is_checked = switchery_toggle_data.checked ? "1" : "0";
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
     };
     */

    $(".toggle-sample-data").click(function () {
        var is_checked = parseInt($("#use_sample_data").val()) === 1 ? 0 : 1;
        toggle_sample_data(is_checked);
    });

    //initial load
    load_all();
});

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

function load_all() {
    //reset charts
    $(".chart-div-wrapper canvas").attr('height', '0');
    load_chart_applications();
    load_chart_connections();
    load_chart_partial_tags();
    load_chart_archived_tags();
}


var chart_applications_obj = null;
var chart_applications_data = null;

function load_chart_applications() {

    $.ajax({
        type: "POST",
        url: baseUrl + "agent/ajax-main-chart-applications",
        cache: false,
        data: {
            'test': $("#use_sample_data").val(),
            'start': $("#referenceDateStart").val(),
            'end': $("#referenceDateEnd").val()
        },
        success: function (jObj) {
            chart_applications_data = {
                labels: jObj.xticks,
                datasets: jObj.dataset
            };

            if (chart_applications_obj !== null) {
                chart_applications_obj.destroy();
            }

            //create canvas dynamically
            //if containers is hidden, chartjs sets the height to 0
            $("#chart-applications-div-wrapper").html('<canvas id="chart-applications-div" height="150"></canvas>');
            var canvas = document.getElementById("chart-applications-div");
            var ctx = canvas.getContext("2d");

            chart_applications_obj = new Chart(ctx, {
                type: 'line',
                data: chart_applications_data,
                options: chart_line_opts
            });

            //metrics

            $("#metricApplicationsActioned").html(parseInt(jObj.total_actioned).toLocaleString('en', {minimumFractionDigits: 0, maximumFractionDigits: 0}));
            $("#metricApplicationsAdded").html(parseInt(jObj.total_added).toLocaleString('en', {minimumFractionDigits: 0, maximumFractionDigits: 0}));
            $("#metricDaysSince").html(parseInt(jObj.days_since_last_referral).toLocaleString('en', {minimumFractionDigits: 0, maximumFractionDigits: 0}));
            $("#metricNewCount").html(parseInt(jObj.new_count).toLocaleString('en', {minimumFractionDigits: 0, maximumFractionDigits: 0}));
            $("#metricFollowUpCount").html(parseInt(jObj.follow_up_count).toLocaleString('en', {minimumFractionDigits: 0, maximumFractionDigits: 0}));
            $("#metricCompletedCount").html(parseInt(jObj.completed_count).toLocaleString('en', {minimumFractionDigits: 0, maximumFractionDigits: 0}));
            $("#metricCancelledCount").html(parseInt(jObj.cancelled_count).toLocaleString('en', {minimumFractionDigits: 0, maximumFractionDigits: 0}));
        }
    });

}


var chart_connections_obj = null;
var chart_connections_data = null;

function load_chart_connections() {

    $.ajax({
        type: "POST",
        url: baseUrl + "agent/ajax-main-chart-connections",
        cache: false,
        data: {
            'test': $("#use_sample_data").val(),
            'start': $("#referenceDateStart").val(),
            'end': $("#referenceDateEnd").val()
        },
        success: function (jObj) {
            chart_connections_data = {
                labels: jObj.xticks,
                datasets: jObj.dataset
            };

            if (chart_connections_obj !== null) {
                chart_connections_obj.destroy();
            }

            //create canvas dynamically
            //if containers is hidden, chartjs sets the height to 0
            $("#chart-connections-div-wrapper").html('<canvas id="chart-connections-div" height="150"></canvas>');
            var canvas = document.getElementById("chart-connections-div");
            var ctx = canvas.getContext("2d");

            chart_connections_obj = new Chart(ctx, {
                type: 'line',
                data: chart_connections_data,
                options: chart_line_opts
            });

            //metrics
            $("#metricConnectionsActioned").html(parseInt(jObj.total_actioned).toLocaleString('en', {minimumFractionDigits: 0, maximumFractionDigits: 0}));
            $("#metricConnectionsAdded").html(parseInt(jObj.total_added).toLocaleString('en', {minimumFractionDigits: 0, maximumFractionDigits: 0}));
        }
    });

}


var chart_partial_tags_obj = null;
var chart_partial_tags_data = null;

function load_chart_partial_tags(start, end) {

    $.ajax({
        type: "POST",
        url: baseUrl + "agent/ajax-main-chart-partial-tags",
        cache: false,
        data: {
            'test': $("#use_sample_data").val(),
            'start': start,
            'end': end
        },
        success: function (jObj) {
            chart_partial_tags_data = {
                labels: jObj.xticks,
                datasets: jObj.dataset
            };

            if (chart_partial_tags_obj !== null) {
                chart_partial_tags_obj.destroy();
            }


            //create canvas dynamically
            //if containers is hidden, chartjs sets the height to 0
            $("#chart-partial-tags-div-wrapper").html('<canvas id="chart-partial-tags-div" height="150"></canvas>');
            var canvas = document.getElementById("chart-partial-tags-div");
            var ctx = canvas.getContext("2d");

            chart_partial_tags_obj = new Chart(ctx, {
                type: 'horizontalBar',
                data: chart_partial_tags_data,
                options: (jObj.dataset[0].data.length > 2 ? chart_bar_opts_nolabel : chart_bar_opts_nolabel_smalldataset)
            });

        }
    });

}


var chart_archived_tags_obj = null;
var chart_archived_tags_data = null;

function load_chart_archived_tags(start, end) {

    $.ajax({
        type: "POST",
        url: baseUrl + "agent/ajax-main-chart-archived-tags",
        cache: false,
        data: {
            'test': $("#use_sample_data").val(),
            'start': start,
            'end': end
        },
        success: function (jObj) {
            chart_archived_tags_data = {
                labels: jObj.xticks,
                datasets: jObj.dataset
            };

            if (chart_archived_tags_obj !== null) {
                chart_archived_tags_obj.destroy();
            }


            //create canvas dynamically
            //if containers is hidden, chartjs sets the height to 0
            $("#chart-archived-tags-div-wrapper").html('<canvas id="chart-archived-tags-div" height="150"></canvas>');
            var canvas = document.getElementById("chart-archived-tags-div");
            var ctx = canvas.getContext("2d");

            chart_archived_tags_obj = new Chart(ctx, {
                type: 'horizontalBar',
                data: chart_archived_tags_data,
                options: (jObj.dataset[0].data.length > 2 ? chart_bar_opts_nolabel : chart_bar_opts_nolabel_smalldataset)
            });

        }
    });

}