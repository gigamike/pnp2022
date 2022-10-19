var switchery_toggle_data;

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
    load_chart_top_partner_referrals();
    load_chart_top_agent_referrals();
    load_chart_top_services_referrals();
}


var chart_applications_obj = null;
var chart_applications_data = null;

function load_chart_applications() {

    $.ajax({
        type: "POST",
        url: baseUrl + "csagent/ajax-main-chart-applications",
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
            $("#chart-applications-div-wrapper").html('<canvas id="chart-applications-div" height="100"></canvas>');
            var canvas = document.getElementById("chart-applications-div");
            var ctx = canvas.getContext("2d");

            chart_applications_obj = new Chart(ctx, {
                type: 'line',
                data: chart_applications_data,
                options: chart_line_opts
            });

            //metrics
            $("#metricTotalCustomers").html(parseInt(jObj.customer_count).toLocaleString('en', {minimumFractionDigits: 0, maximumFractionDigits: 0}));
            $("#metricTotalApplications").html(parseInt(jObj.applications_count).toLocaleString('en', {minimumFractionDigits: 0, maximumFractionDigits: 0}));

        }
    });

}


var chart_connections_obj = null;
var chart_connections_data = null;

function load_chart_connections() {

    $.ajax({
        type: "POST",
        url: baseUrl + "csagent/ajax-main-chart-connections",
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
            $("#chart-connections-div-wrapper").html('<canvas id="chart-connections-div" height="100"></canvas>');
            var canvas = document.getElementById("chart-connections-div");
            var ctx = canvas.getContext("2d");

            chart_connections_obj = new Chart(ctx, {
                type: 'line',
                data: chart_connections_data,
                options: chart_line_opts
            });

            //metrics
            $("#metricTotalConnections").html(parseInt(jObj.connections_count).toLocaleString('en', {minimumFractionDigits: 0, maximumFractionDigits: 0}));

        }
    });

}


var chart_top_partners_obj = null;
var chart_top_partners_data = null;

function load_chart_top_partner_referrals() {

    $.ajax({
        type: "POST",
        url: baseUrl + "csagent/ajax-main-chart-top-partner-referrals",
        cache: false,
        data: {
            'test': $("#use_sample_data").val(),
            'start': $("#referenceDateStart").val(),
            'end': $("#referenceDateEnd").val()
        },
        success: function (jObj) {
            chart_top_partners_data = {
                labels: jObj.xticks,
                datasets: jObj.dataset
            };

            if (chart_top_partners_obj !== null) {
                chart_top_partners_obj.destroy();
            }


            //create canvas dynamically
            //if containers is hidden, chartjs sets the height to 0
            $("#chart-top-partners-div-wrapper").html('<canvas id="chart-top-partners-div" height="150"></canvas>');
            var canvas = document.getElementById("chart-top-partners-div");
            var ctx = canvas.getContext("2d");

            chart_top_partners_obj = new Chart(ctx, {
                type: 'horizontalBar',
                data: chart_top_partners_data,
                options: (jObj.dataset[0].data.length > 2 ? chart_bar_opts_nolabel : chart_bar_opts_nolabel_smalldataset)
            });

            //metrics
            $("#metricTotalPartners").html(parseInt(jObj.total_partners).toLocaleString('en', {minimumFractionDigits: 0, maximumFractionDigits: 0}));

        }
    });

}


var chart_top_agents_obj = null;
var chart_top_agents_data = null;

function load_chart_top_agent_referrals() {

    $.ajax({
        type: "POST",
        url: baseUrl + "csagent/ajax-main-chart-top-agent-referrals",
        cache: false,
        data: {
            'test': $("#use_sample_data").val(),
            'start': $("#referenceDateStart").val(),
            'end': $("#referenceDateEnd").val()
        },
        success: function (jObj) {
            chart_top_agents_data = {
                labels: jObj.xticks,
                datasets: jObj.dataset
            };

            if (chart_top_agents_obj !== null) {
                chart_top_agents_obj.destroy();
            }


            //create canvas dynamically
            //if containers is hidden, chartjs sets the height to 0
            $("#chart-top-agents-div-wrapper").html('<canvas id="chart-top-agents-div" height="150"></canvas>');
            var canvas = document.getElementById("chart-top-agents-div");
            var ctx = canvas.getContext("2d");

            chart_top_agents_obj = new Chart(ctx, {
                type: 'horizontalBar',
                data: chart_top_agents_data,
                options: (jObj.dataset[0].data.length > 2 ? chart_bar_opts_nolabel : chart_bar_opts_nolabel_smalldataset)
            });

            //metrics
            $("#metricTotalAgents").html(parseInt(jObj.total_agents).toLocaleString('en', {minimumFractionDigits: 0, maximumFractionDigits: 0}));

        }
    });

}


var chart_top_services_obj = null;
var chart_top_services_data = null;

function load_chart_top_services_referrals() {

    $.ajax({
        type: "POST",
        url: baseUrl + "csagent/ajax-main-chart-top-service-referrals",
        cache: false,
        data: {
            'test': $("#use_sample_data").val(),
            'start': $("#referenceDateStart").val(),
            'end': $("#referenceDateEnd").val()
        },
        success: function (jObj) {
            chart_top_services_data = {
                labels: jObj.xticks,
                datasets: jObj.dataset
            };

            if (chart_top_services_obj !== null) {
                chart_top_services_obj.destroy();
            }


            //create canvas dynamically
            //if containers is hidden, chartjs sets the height to 0
            $("#chart-top-services-div-wrapper").html('<canvas id="chart-top-services-div" height="150"></canvas>');
            var canvas = document.getElementById("chart-top-services-div");
            var ctx = canvas.getContext("2d");

            chart_top_services_obj = new Chart(ctx, {
                type: 'horizontalBar',
                data: chart_top_services_data,
                options: (jObj.dataset[0].data.length > 2 ? chart_bar_opts_nolabel : chart_bar_opts_nolabel_smalldataset)
            });
        }
    });

}