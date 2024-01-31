/*
Template Name: Admin Pro Admin
Author: Wrappixel
Email: niravjoshi87@gmail.com
File: js
*/

function get_ajax_data(data) {
    const url = new URL(window.location.href);
    var return_response = "";
    return $.ajax({
        type: "get",
        url: "/admin/get_graph_data"+url.search,
        contentType: "application/json",
        dataType: "json",
        data: data,
    });
}

var data = { day: "today" };
get_ajax_data(data).then((res) => {
    $("#today_client").text(res);
});

var data = { day: "week" };
get_ajax_data(data).then((res) => {
    $("#week_client").text(res);
});

$(function () {
    "use strict";
    // ==============================================================
    // Newsletter
    // ==============================================================

    var offset = 0;
    var data = { total: "data" };
    var min, max, data, max_value, month;
    get_ajax_data(data).then((res) => {
        min = parseInt(res["min-day"]);
        max = parseInt(res["max-day"]);
        max_value = parseInt(res["max-value"]);
        month = res["month"];
        data = res["data"];
        data = Object.values(data);
        chart_graph();
    });

    function chart_graph() {

        var ctx = document.getElementById("myChart").getContext("2d");
        var chart = new Chart(ctx, {
            // The type of chart we want to create
            type: "line",

            // The data for our dataset
            data: {
                labels: last7days,
                datasets: [
                    {
                        label: "Lookup Count",
                        backgroundColor: "rgb(0,158,251)",
                        borderColor: "rgb(0,158,251)",
                        data: data
                    }
                ]
            },

            // Configuration options go here
            options: {
                legend: {
                    display: false,
                    position: "right",
                    align: "top"
                },
                responsive: false,
                maintainAspectRatio: false,
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                        }
                    }]
                },
            }

        });

        if (data.every((currentValue) => currentValue < 6)) {
            chart.options.scales.yAxes[0].ticks.stepSize = 1
            chart.update();
        }
    }

    $("<div id='tooltip'></div>").css({
        position: "absolute",
        display: "none",
        border: "1px solid rgb(0, 158, 251)",
        padding: "5px",
        color: "#fff",
        "border-radius": "5px",
        "background-color": "rgb(0, 158, 251)",
        opacity: 1
    }).appendTo("body");

    $("#flot-line-chart").on("plothover", function (event, pos, item) {
        if (!pos.x || !pos.y) {
            return;
        }

        if (item) {
            var y = item.datapoint[1].toFixed(0);
            $("#tooltip").html(item.series.label + " is " + y)
                .css({ top: item.pageY + 5, left: item.pageX + 5 })
                .fadeIn(200);
        } else {
            $("#tooltip").hide();
        }

    });

    // select the duration  for the projects. 
    $('#id_select_duration').on("change", function () {
        var duration = $(this).val();
        $.ajax({
            type: "get",
            url: "/admin/get_table_data/" + duration,
            contentType: "application/json",
            dataType: "json",
            success: function (response) {
                console.log(response);
                $("#lookupData").html("");
                for (var i = 0; i < response.length; i++) {
                    $("#lookupData").append(`
                        <tr>
                            <td class="border-top-0">
                                ${response[i]['Lookup_Name'] ? response[i]['Lookup_Name'] : response[i]['Result_Project_Id']}
                            </td>
                            <td class="border-top-0" >${response[i]['Result_Client_Name'] ? response[i]['Result_Client_Name'] : ''}</td>
                            <td class="border-top-0" >${response[i]['Result_Project_Id'] ? response[i]['Result_Project_Id'] : ''}</td>
                            <td class="border-top-0" ><i class="fa ${response[i]['Result'] == 1 ? 'fa-check text-success' : 'fa-times text-danger'}"></td>
                            <td class="border-top-0" >${response[i]['created_at'] ? response[i]['created_at'] : ''}</td>
                        </tr>
                    `);
                }
            },
        });
    });
});
$(".RdStatus").click(function () {
    if ($(this).is(':checked')) {
        var current_id = $(this).attr('data-id');
        var status_obj = "Status_" + current_id;
        var current_status = $(this).val();

        $('#' + status_obj).val(current_status);

        var strName = "Full_name_" + current_id;
        var strEmail = "Email_" + current_id;
        var strPhone = "Phonenumber_" + current_id;
        var fvData = "FV_data_" + current_id;
        var strSelection = "Selection_selector_" + current_id;
        var strField = "Field_selector_" + current_id;

        var objId = $(this).attr('id');
        if (current_status != "2") {
            $("#" + strName).css('display', 'none');
            $("#" + strName).prop('required', false);
            $("#" + strEmail).css('display', 'none');
            $("#" + strEmail).prop('required', false);
            $("#" + strPhone).css('display', 'none');
            $("#" + strPhone).prop('required', false);
        }

        if (current_status == "2") {
            $("#" + strName).css('display', '');
            $("#" + strName).prop('required', true);
            $("#" + strEmail).css('display', '');
            $("#" + strEmail).prop('required', true);
            $("#" + strPhone).css('display', '');
            $("#" + strPhone).prop('required', true);
        }

        if (current_status != "1") {
            $("#" + fvData).css('display', 'none');
            $("#" + fvData).prop('required', false);
            $("#" + strSelection).css('display', 'none');
            $("#" + strSelection).prop('required', false);
            $("#" + strField).css('display', 'none');
            $("#" + strField).prop('required', false);
        }

        if (current_status == "1") {
            $("#" + fvData).css('display', 'none');
            // $("#" + strName).prop('required',true);
            $("#" + strSelection).css('display', 'none');
            $("#" + strSelection).prop('required', false);
            $("#" + strField).css('display', 'none');
            $("#" + strField).prop('required', false);
        }

    }
});

$(".clientDisable").click(function () {
    if ($(this).is(':checked')) {
        var current_id = $(this).attr('data-id');
        var strName = "Full_name_" + current_id;
        var strEmail = "Email_" + current_id;
        var strPhone = "Phonenumber_" + current_id;

        $("#" + strName).css('display', 'none')
        $("#" + strName).prop('required', false);
        $("#" + strEmail).css('display', 'none');
        $("#" + strEmail).prop('required', false);
        $("#" + strPhone).css('display', 'none');
        $("#" + strPhone).prop('required', false);

        var status_obj = "Status_" + current_id;
        $('#' + status_obj).val("0");
    }
});

$(".clientEnable").click(function () {
    if ($(this).is(':checked')) {
        var current_id = $(this).attr('data-id');
        var strName = "Full_name_" + current_id;
        var strEmail = "Email_" + current_id;
        var strPhone = "Phonenumber_" + current_id;

        $("#" + strName).css('display', '');
        $("#" + strName).prop('required', true);
        $("#" + strEmail).css('display', '');
        $("#" + strEmail).prop('required', true);
        $("#" + strPhone).css('display', '');
        $("#" + strPhone).prop('required', true);

        var status_obj = "Status_" + current_id;
        $('#' + status_obj).val("1");
    }
});