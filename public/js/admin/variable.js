function copyToClipboard(elem) {
    var temp = $("<input>");
    $("body").append(temp);
    temp.val($('#copyText' + elem).text()).select();
    document.execCommand("copy");
    temp.remove();
}

var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

$(document).ready(function () {
    $('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
        localStorage.setItem('variableActiveTab', $(e.target).attr('href'));
    });
    var activeTab = localStorage.getItem('variableActiveTab');
    if (activeTab) {
        $('#myTab1 a[href="' + activeTab + '"]').tab('show');
    }
});


var BasicDatatablesDataSourceHtml = function () {
    var customVariableDatatable = function () {
        var table1 = $('#custom-variable-datatable');
        table1.DataTable({
            responsive: true,
            order: [[0, 'desc']],
            bDestroy: true
        });
    };
    return {
        init: function () {
            customVariableDatatable();
        },
    };
}();

jQuery(document).ready(function () {
    BasicDatatablesDataSourceHtml.init();
});


$("body").on("click", "a.add-new", async function () {

    $('#variable_add_form')[0].reset();

    $(".loading").show();
    $.ajax({
        url: "variable/get_project_type",
        type: "GET",
        success: function (response) {
            $("select[name='fv_project_type']").html(response.html);
        },
        error: function () {
            $(".loading").hide();
            alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
        },
    }).done(function () {
        $(".loading").hide();
    });
});

$("body").on("change", "select[name='fv_project_type']", async function () {
    let fv_project_type_id = $(this).val();
    let fv_project_type_text = $(this).find("option:selected").text();
    $('input[name="fv_project_type_name"]').val(fv_project_type_text);
    $(".loading").show();
    $.ajax({
        url: "variable/get_section",
        type: "GET",
        data: { project_type_id: fv_project_type_id },
        success: function (response) {
            $("select[name='fv_section_selector']").html(response.html);
        },
        error: function () {
            $(".loading").hide();
            alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
        },
    }).done(function () {
        $(".loading").hide();
    });
});


$("body").on("change", "select[name='fv_section_selector']", async function () {
    let fv_section_selector_id = $(this).val();
    let fv_section_selector_text = $(this).find("option:selected").text();
    $('input[name="fv_section_selector_name"]').val(fv_section_selector_text);
    let fv_project_type_id = $('select[name="fv_project_type"]').val();
    $(".loading").show();
    $.ajax({
        url: "variable/get_field",
        type: "GET",
        data: { project_type_id: fv_project_type_id, project_section_selector: fv_section_selector_id },
        success: function (response) {
            $("select[name='fv_field_selector']").html(response.html);
        },
        error: function () {
            $(".loading").hide();
            alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
        },
    }).done(function () {
        $(".loading").hide();
    });
});


$("body").on("change", "select[name='fv_field_selector']", async function () {
    let fv_field_selector_text = $(this).find("option:selected").text();
    $('input[name="fv_field_selector_name"]').val(fv_field_selector_text);
});


$("body").on("change", "input.variable-active", async function () {
    let variable_id = $(this).val();
    $(".loading").show();
    $.ajax({
        type: "post",
        url: "variable/update_active",
        data: {
            _token: CSRF_TOKEN,
            variable_id: variable_id
        },
        success: function (response) { },
    }).done(function () {
        $(".loading").hide();
    });
});

$('body').on('click', '.delete_variable', function () {
    Swal.fire({
        title: 'Are you sure to delete selected variable?',
        icon: 'warning',
        showDenyButton: true,
        showCancelButton: false,
        confirmButtonText: 'Delete',
        denyButtonText: `Cancel`,
    }).then((result) => {
        if (result.isConfirmed) {
            var route_url = $(this).attr('data-url');
            $.ajax({
                url: route_url,
                type: 'POST',
                data: {
                    '_token': CSRF_TOKEN
                },
                dataType: 'JSON',
                success: function (data) {
                    if (data.success) {
                        Swal.fire({
                            title: data.message,
                            icon: 'success',
                        }).then((result) => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: data.message,
                            icon: 'error',
                        });
                    }
                }
            });
        }
    });
});

$('body').on('click', '.save-permission', function () {
    let this_save = $(this);
    let route_url = this_save.attr('data-url');
    let id = this_save.attr('data-id');
    let variable_row = $(".variable-row" + id);
    $.ajax({
        url: route_url,
        type: 'POST',
        data: {
            '_token': CSRF_TOKEN,
            'variable_id': id,
            'is_project_timeline': variable_row.find('input[name="is_project_timeline"]').is(
                ':checked') ? 1 : 0,
            'is_timeline_mapping': variable_row.find('input[name="is_timeline_mapping"]').is(
                ':checked') ? 1 : 0,
            'is_phase_change_sms': variable_row.find('input[name="is_phase_change_sms"]').is(
                ':checked') ? 1 : 0,
            'is_review_request_sms': variable_row.find('input[name="is_review_request_sms"]').is(
                ':checked') ? 1 : 0,
            'is_client_banner_message': variable_row.find('input[name="is_client_banner_message"]')
                .is(':checked') ? 1 : 0,
            'is_automated_workflow_action': variable_row.find(
                'input[name="is_automated_workflow_action"]').is(':checked') ? 1 : 0,
            'is_mass_text': variable_row.find('input[name="is_mass_text"]').is(':checked') ? 1 : 0,
            'is_email_template': variable_row.find('input[name="is_email_template"]').is(
                ':checked') ? 1 : 0,
        },
        dataType: 'JSON',
        success: function (data) {
            if (data.success) {
                Swal.fire({
                    title: data.message,
                    icon: 'success',
                });
            } else {
                Swal.fire({
                    title: data.message,
                    icon: 'error',
                });
            }
        }
    });
});

$('body').on('click', '.edit_variable', function () {
    let data_row = JSON.parse($(this).attr('data-row'));
    $("input[name='variable_id']").val(data_row.master_id);
    $("input[name='variable_name']").val(data_row.variable_name);
    $("input[name='variable_key']").val(data_row.variable_key);
    $("input[name='placeholder']").val(data_row.placeholder);
    $("input[name='variable_description']").val(data_row.variable_description);
    $("input[name='select_all']").prop('checked', data_row.select_all);
    $("#variable_add_form input[name='is_project_timeline']").prop('checked', data_row.is_project_timeline);
    $("#variable_add_form input[name='is_timeline_mapping']").prop('checked', data_row.is_timeline_mapping);
    $("#variable_add_form input[name='is_phase_change_sms']").prop('checked', data_row.is_phase_change_sms);
    $("#variable_add_form input[name='is_review_request_sms']").prop('checked', data_row.is_review_request_sms);
    $("#variable_add_form input[name='is_client_banner_message']").prop('checked', data_row.is_client_banner_message);
    $("#variable_add_form input[name='is_automated_workflow_action']").prop('checked', data_row
        .is_automated_workflow_action);
    $("#variable_add_form input[name='is_mass_text']").prop('checked', data_row.is_mass_text);
    $("#variable_add_form input[name='is_email_template']").prop('checked', data_row.is_email_template);

    $(".loading").show();
    $.ajax({
        url: "variable/get_project_type",
        type: "GET",
        success: function (response) {
            $("select[name='fv_project_type']").html(response.html);
            $("select[name='fv_project_type']").val(data_row.fv_project_type);
            $('input[name="fv_project_type_name"]').val(data_row.fv_project_type_name);
        },
        error: function () {
            $(".loading").hide();
            alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
        },
    }).done(function () {
        $(".loading").hide();
    });

    $(".loading").show();
    $.ajax({
        url: "variable/get_section",
        type: "GET",
        data: { project_type_id: data_row.fv_project_type },
        success: function (response) {
            $("select[name='fv_section_selector']").html(response.html);
            $("select[name='fv_section_selector']").val(data_row.fv_section_selector);
            $('input[name="fv_section_selector_name"]').val(data_row.fv_section_selector_name);
        },
        error: function () {
            $(".loading").hide();
            alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
        },
    }).done(function () {
        $(".loading").hide();
    });

    $(".loading").show();
    $.ajax({
        url: "variable/get_field",
        type: "GET",
        data: { project_type_id: data_row.fv_project_type, project_section_selector: data_row.fv_section_selector },
        success: function (response) {
            $("select[name='fv_field_selector']").html(response.html);
            $("select[name='fv_field_selector']").val(data_row.fv_field_selector);
            $('input[name="fv_field_selector_name"]').val(data_row.fv_field_selector_name);
        },
        error: function () {
            $(".loading").hide();
            alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
        },
    }).done(function () {
        $(".loading").hide();
    });

});


$("body").on("change", "input[name='select_all']", async function () {
    if ($(this).prop("checked") == true) {
        $('.permission-feature').prop('checked', true);
    } else {
        $('.permission-feature').prop('checked', false);
    }
});
