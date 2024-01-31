$(document).ready(function (e) {

    $("body").on("change", "input[name='calendar_visibility']", async function () {
        if ($(this).prop("checked") == true) {
            $(".collect_appointment_feedback_div").removeClass("d-none");
        } else {
            $(".collect_appointment_feedback_div").addClass("d-none");
            $(".feedback_type_div").addClass("d-none");
            $(".sync_feedback_type_div").addClass("d-none");
            $(".collection_div").addClass("d-none");
        }
    });

    $("body").on("change", "input[name='collect_appointment_feedback']", async function () {
        if ($(this).prop("checked") == true) {
            $(".feedback_type_div").removeClass("d-none");
        } else {
            $(".feedback_type_div").addClass("d-none");
            $(".sync_feedback_type_div").addClass("d-none");
            $(".collection_div").addClass("d-none");
        }
    });

    $("body").on("change", "select[name='feedback_type']", async function () {
        if ($(this).val() == 2) {
            $(".sync_feedback_type_div").removeClass("d-none");
            $(".collection_div").removeClass("d-none");
            $(".select2").css({ "width": "100% !important" });
        } else {
            $(".sync_feedback_type_div").addClass("d-none");
            $(".collection_div").addClass("d-none");
        }
    });


    $("body").on("change", "select[name='sync_feedback_type']", async function () {
        if ($(this).val() == 2) {
            //$(".display_as_div").removeClass("d-none");
            $(".section_display_div").removeClass("d-none");
            //$(".choose-field-label").text("Choose Field");
        } else {
            //$(".display_as_div").addClass("d-none");
            $(".section_display_div").addClass("d-none");
            //$(".choose-field-label").text("Choose a Text Field for Feedback and a Date Field for Data Received");
        }
    });

    $("body").on("change", "input[name='display_as']", async function () {
        if ($(this).prop("checked") == true) {
            // Allow multiple
        } else {
            // Do not allow multiple
        }
    });


    $("body").on("change", "select[name='project_type_id']", async function () {
        $(".loading").show();
        $.ajax({
            url: "/admin/calendar/get_project_type_section_list",
            type: "GET",
            data: { project_type_id: $(this).val() },
            success: function (response) {
                $("select[name='collection_section_id']").html(response.html);
            },
            error: function () {
                $(".loading").hide();
                alert("Error to Process Your Request! Please try Again!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    });

    $("body").on("change", "select[name='collection_section_id']", async function () {
        $(".loading").show();
        $.ajax({
            url: "/admin/calendar/get_project_type_section_field_list",
            type: "GET",
            data: {
                project_type_id: $("select[name='project_type_id']").val(),
                collection_section_id: $(this).val(),
                sync_feedback_type: $("select[name='sync_feedback_type']").val()
            },
            success: function (response) {
                $(".field_id").html(response.html);
                $("select[name='display_item_collection_section_id']").html(response.display_field_options);
            },
            error: function () {
                $(".loading").hide();
                alert("Error to Process Your Request! Please try Again!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    });

});
