$(document).ready(function (e) {

});


$(".fetch-vital").on('click', (function (e) {
    let projectTypeId = $("#projectType").val();
    if (projectTypeId == '') {
        Swal.fire({
            text: 'Please Select Project Type!',
            icon: "error",
        });
        return false;
    }

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(".loading").show();
    $("#ProjectsVital").html("<option value=''>Select Vital to Display</option>");
    $.ajax({
        url: "get_project_vitals",
        type: "POST",
        data: { projectTypeId: projectTypeId },
        success: function (data) {
            $("#ProjectsVital").html(data.html_vitals);
        }
    }).done(function () {
        $(".loading").hide();
    });
}));


$("#projectType").on('change', (function (e) {
    $("#ProjectsVital").html("<option value=''>Select Vital to Display</option>");
    let projectTypeId = $("#projectType").val();
    if (projectTypeId == '') {
        $(".more-feilds").html("");
        return false;
    }

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(".loading").show();
    $(".more-feilds").html("");
    $.ajax({
        url: "get_current_project_vitals",
        type: "POST",
        data: { projectTypeId: projectTypeId },
        success: function (data) {
            $(".more-feilds").html(data.html_vitals_slot);
            VitalSlotOrder();
        }
    }).done(function () {
        $(".loading").hide();
    });
}));


$(".add-more").on('click', (function (e) {
    if ($('#projectType').val() > 0 && $("#ProjectsVital").val() != "" && $("#ProjectsVital").val() != null) {
        var friendlyName = $("#ProjectsVital option:selected").text();
        var field_name = $('#ProjectsVital').val();
        let field_check = false;
        $(".project-vital").each(function () {
            let added_field = $(this).find('.field_name').val();
            if (field_name == added_field) {
                field_check = true;
            }
        });

        if (field_check) {
            Swal.fire({
                text: "You Can't Select One Vital More Than Once!",
                icon: "error",
            });
            return false;
        }

        var html = '<div class="row project-vital"> <div class="col-md-2 pt-6 vitalSlotOrder"> </div><div class="col-md-3 pt-3">';
        html += '<input class="form-control friendly_name" readonly  name="friendly_name" value="' + friendlyName + '">';
        html += '<input type="hidden" class="form-control field_name"  name="field_name" value="' + field_name + '">';
        html += '</div><div class="col-md-3 pt-3">';
        html += '<input class="form-control override_title" name="override_title" value="">';
        html += '</div><div class="col-md-4 pt-3">';
        html += '<button type="button" class="btn btn-sm btn-danger remove"><i class="fa fa-trash"></i></button>';
        html += '<button type="button" class="btn btn-sm btn-grey moveup"><i class="fa fa-arrow-up"></i></i></button>';
        html += '<button type="button" class="btn btn-sm btn-grey movedown" style="padding:0px"><i class="fa fa-arrow-down"></i></button>';
        html += '</div></div>';
        $(".more-feilds").append(html);
        VitalSlotOrder();
    } else {
        Swal.fire({
            text: 'Select Project Type & Project Vital!',
            icon: "error",
        });
    }
}));


function VitalSlotOrder() {
    let row = 1;
    $(".project-vital").each(function () {
        $(this).find('.vitalSlotOrder').text('Vital Slots # ' + row++);
    });
}

$('.save-Project-Vitals').click(function () {
    var projectTypeId = $('#projectType').val();
    var projectType = $("#projectType option:selected").text();
    if ($('#is_show_project_sms_number').prop('checked')) {
        var projectSMSNumber = 1;
    } else {
        var projectSMSNumber = 0;
    }
    if ($('#is_show_project_email').prop('checked')) {
        var projectEmail = 1;
    } else {
        var projectEmail = 0;
    }
    if ($('#is_show_project_clientname').prop('checked')) {
        var projectClient = 1;
    } else {
        var projectClient = 0;
    }
    if ($('#is_show_project_name').prop('checked')) {
        var projectName = 1;
    } else {
        var projectName = 0;
    }
    if ($('#is_show_project_id').prop('checked')) {
        var projectId = 1;
    } else {
        var projectId = 0;
    }
    let vital_value = [];
    $(".project-vital").each(function () {
        vital_value.push({ 'friendly_name': $(this).find('.friendly_name').val(), 'field_name': $(this).find('.field_name').val(), 'override_title': $(this).find('.override_title').val() });
    });

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var formData = {
        'projectSMSNumber': projectSMSNumber,
        'projectEmail': projectEmail,
        'projectClient': projectClient,
        'projectName': projectName,
        'projectId': projectId,
        'vital_value': vital_value,
        'projectType': projectType,
        'projectTypeId': projectTypeId,
        'project_vital_override_title': $("input[name='project_vital_override_title']").val()
    };

    $(".loading").show();
    $.ajax({
        type: "post",
        url: 'project_vitals_save',
        data: formData,
        success: function (response) {
            if (response.status === true) {
                Swal.fire({
                    text: response.message,
                    icon: "success",
                });
            }
        },
    }).done(function () {
        $(".loading").hide();
    });
});

$("body").on("click", ".remove", async function () {
    $(this).parents().parents(".project-vital").remove();
    VitalSlotOrder();
});


$("body").on("click", ".moveup", async function () {
    let itemlist = $('#sortable-more-feilds');
    let selected = $(this).parents().parents().index();
    if (selected > 0) {
        jQuery($(itemlist).children().eq(selected - 1)).before(jQuery($(itemlist).children().eq(selected)));
    }
    VitalSlotOrder();
});


$("body").on("click", ".movedown", async function () {
    let itemlist = $('#sortable-more-feilds');
    let len = $(itemlist).children().length;
    let selected = $(this).parents().parents().index();
    if (selected < len) {
        jQuery($(itemlist).children().eq(selected + 1)).after(jQuery($(itemlist).children().eq(selected)));
    }
    VitalSlotOrder();
});


$("input[name='test_tfa_number']").on("blur", (function (e) {
    e.preventDefault();
    let test_tfa_number = $(this).val();
    var form_data = { 'test_tfa_number': test_tfa_number };

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        url: "save_tenant_test_number_settings",
        type: "POST",
        data: form_data,
        success: function (data) {

        }
    });

}));
