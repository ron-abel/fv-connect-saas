$(document).ready(function (e) {
    $(document).on('click', '#settings_save', function (e) {
        e.preventDefault();
        var appFilevine = $('#fv_tenant_base_url').val();
        if (appFilevine == '') {
            Swal.fire({
                text: 'The fv tenant base url field is required.',
                icon: "error",
            });
            return false;
        }
        if (appFilevine.substr(-1) === '/') {
            Swal.fire({
                text: 'Please, Do not include a tailing slash at the end of Filevine Login URL!',
                icon: "error",
            });
            $('#fv_tenant_base_url').focus();
            window.scrollTo(0, 0);
            return false;
        }

        $('#settings_save_form').submit();

        /* var domain = appFilevine.replace('http://', '').replace('https://', '').split(/[/?#]/)[0];
         var myKey = 'filevine.com';
         var myKey1 = 'filevineapp.com';
         var myMatch = domain.search(myKey);
         var myMatch1 = domain.search(myKey1);
         if (myMatch != -1 || myMatch1 != -1) {
             $('#settings_save_form').submit();
         } else {
             Swal.fire({
                 text: 'The format of the Filevine Tenant base url is invalid.',
                 icon: "error",
             });
             return false;
         } */
    });


    $("input[name=image]").on('change', (function (e) {
        e.preventDefault();

        var formData = new FormData();
        formData.append('image', $("input[name=image]")[0].files[0]);

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            url: "upload_firms_logo",
            type: "POST",
            data: formData,
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function () {
                //$("#preview").fadeOut();
            },
            success: function (data) {
                $(".js-firm-logo-name").html($("input[name=image]")[0].files.item(0).name);
            },
            error: function (e) {
            }
        });
    }));

    $("input[name=background]").on('change', (function (e) {
        e.preventDefault();

        var formData = new FormData();
        formData.append('image', $("input[name=background]")[0].files[0]);

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            url: "upload_background",
            type: "POST",
            data: formData,
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function () {
                //$("#preview").fadeOut();
            },
            success: function (data) {
                $(".js-background-name").html($("input[name=background]")[0].files.item(0).name);
            },
            error: function (e) {
            }
        });
    }));

    $("input.display-setting-color").on('change', (function (e) {
        e.preventDefault();

        var color = $(this).val();
        var form_data = { [$(this).attr('name')]: color };

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            url: "update_display_color_settings",
            type: "POST",
            data: form_data,
            beforeSend: function () {
            },
            success: function (data) {
            },
            error: function (e) {
            }
        });
    }));

    $("input[name=lf_display_name]").on('blur', (function (e) {
        e.preventDefault();

        var display_name = $(this).val();
        var form_data = { 'lf_display_name': display_name };

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            url: "update_law_firm_display_name",
            type: "POST",
            data: form_data,
            beforeSend: function () {
            },
            success: function (data) {
            },
            error: function (e) {
            }
        });
    }));


    $("input[name='display_phone_number']").on("blur", (function (e) {
        e.preventDefault();
        let display_phone_number = $(this).val();
        var form_data = { 'display_phone_number': display_phone_number };

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            url: "save_tenant_portal_display_settings",
            type: "POST",
            data: form_data,
            success: function (data) {

            }
        });

    }));
    // get custom project settings
    getCustomProjectSettings();
});


$("input.notification-config").change(function () {
    let config_id = $(this).closest('.config-row').attr('data-id');
    let notification_type = $(this).attr('data-name');
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $(".loading").show();
    $.ajax({
        url: "setting_update_notification_config",
        type: "POST",
        data: {
            config_id: config_id,
            notification_type: notification_type
        },
        success: function (response) {
        },
    }).done(function () {
        $(".loading").hide();
    });
});

function noticeChangeStatus(e, id) {
    $.ajax({
        type: "POST",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: "update_client_notification_status",
        data: {
            id
        },
        success: function (response) {
        },
        error: function () {

        }
    });
}

$("input[name='show_archieved_phase']").click(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.ajax({
        url: "update_show_archieved_phase",
        type: "POST",
        data: {
            show_archieved_phase: $('input[name=show_archieved_phase]').is(":checked") ? 1 : 0,
        },
        success: function (response) {
        },
    }).done(function () {

    });
});

$("input[name='client_custom_project_name']").click(function () {
    var value = $(this).prop('checked');
    if (value) {
        $('.client-custom-project-settings').removeClass('d-none');
    }
    else {
        $('.client-custom-project-settings').addClass('d-none');
    }
});
$("input[name='client_custom_project_name_append_another_field']").click(function () {
    var value = $(this).prop('checked');
    if (value) {
        $('.client-custom-project-optional').removeClass('d-none');
        // $('#display_project_as option[value="client_full_name-field_value"]').removeClass('d-none');
        // $('#display_project_as option[value="field_value-field_value"]').removeClass('d-none');
    }
    else {
        $('.client-custom-project-optional').addClass('d-none');
        // $('#display_project_as option[value="client_full_name-field_value"]').addClass('d-none');
        // $('#display_project_as option[value="field_value-field_value"]').addClass('d-none');
    }
});

$("#display_project_as").change(function () {
    var value = $(this).val();
    if (value == "field_value") {
        $('.client-custom-project-main').removeClass('d-none');
        // $('.client-custom-project-optional').addClass('d-none');
    }
    else {
        $('.client-custom-project-main').addClass('d-none');
        // $('.client-custom-project-optional').addClass('d-none');
    }
    // else if(value == "field_value-field_value") {
    //     $('.client-custom-project-main').removeClass('d-none');
    //     $('.client-custom-project-optional').removeClass('d-none');
    // }
    // else if(value == "client_full_name-field_value") {
    //     $('.client-custom-project-main').addClass('d-none');
    //     $('.client-custom-project-optional').removeClass('d-none');
    // }
});

// get section from project type selected
$(document).on("change", "#client_custom_project_selector, #client_custom_project_selector_optional", function () {
    let value = $(this).val();
    let type = $(this).attr('data-type');
    let name = $(this).find('option:selected').text();
    if (value !== "") {
        $(".loading").show();
        $.ajax({
            url: "/admin/get_project_sections_cutom_project/" + value,
            type: "GET",
            success: function (json) {
                $(".loading").hide();
                if (type == "main") {
                    $('#client_custom_section_selector').html('');
                    $('#client_custom_project_type_name').val(name);
                    $('#client_custom_section_selector').html(json.html);
                }
                else {
                    $('#client_custom_section_selector_' + type).html('');
                    $('#client_custom_project_type_name_' + type).val(name);
                    $('#client_custom_section_selector_' + type).html(json.html);
                }
            }
        });
    }
    else {
        if (type == "main") {
            $('#client_custom_project_type_name').val('');
            $('#client_custom_section_selector').html('');
            $('#client_custom_section_name').val('');
            $('#client_custom_field_selector').html('');
            $('#client_custom_field_name').val('');
        }
        else {
            $('#client_custom_project_type_name_' + type).val('');
            $('#client_custom_section_selector_' + type).html('');
            $('#client_custom_section_name_' + type).val('');
            $('#client_custom_field_selector_' + type).html('');
            $('#client_custom_field_name_' + type).val('');
        }
    }

});
// get section fileds from section selected
$(document).on("change", "#client_custom_section_selector, #client_custom_section_selector_optional", function () {
    let value = $(this).val();
    let type = $(this).attr('data-type');
    let name = $(this).find('option:selected').text();
    let project_type_id = "";
    if (type == "main") {
        project_type_id = $('#client_custom_project_selector').val();
    }
    else {
        project_type_id = $('#client_custom_project_selector_' + type).val();
    }

    if (value !== "") {
        $(".loading").show();
        $.ajax({
            url: "/admin/get_project_section_fields_cutom_project/" + project_type_id + "/" + value,
            type: "GET",
            success: function (json) {
                $(".loading").hide();
                if (type == "main") {
                    $('#client_custom_field_selector').html('');
                    $('#client_custom_section_name').val(name);
                    $('#client_custom_field_selector').html(json.html);
                }
                else {
                    $('#client_custom_field_selector_' + type).html('');
                    $('#client_custom_section_name_' + type).val(name);
                    $('#client_custom_field_selector_' + type).html(json.html);
                }
            }
        });
    }
    else {
        if (type == "main") {
            $('#client_custom_section_name').val('');
            $('#client_custom_field_selector').html('');
            $('#client_custom_field_name').val('');
        }
        else {
            $('#client_custom_section_name_' + type).val('');
            $('#client_custom_field_selector_' + type).html('');
            $('#client_custom_field_name_' + type).val('');
        }
    }

});
// set field values for feild selected
$(document).on("change", "#client_custom_field_selector, #client_custom_field_selector_optional", function () {
    let value = $(this).val();
    let type = $(this).attr('data-type');
    let name = $(this).find('option:selected').text();

    if (value !== "") {
        if (type == "main") {
            $('#client_custom_field_name').val(name);
        }
        else {
            $('#client_custom_field_name_' + type).val(name);
        }
    }
    else {
        if (type == "main") {
            $('#client_custom_field_name').val('');
        }
        else {
            $('#client_custom_field_name_' + type).val('');
        }
    }
});

$(document).on('click', '#save-client-custom-project-settings', function (e) {
    e.preventDefault();
    let validations = {
        'field_value': ['client_custom_project_selector', 'client_custom_section_selector', 'client_custom_field_selector'],
        'client_full_name-field_value': ['client_custom_project_selector_optional', 'client_custom_section_selector_optional', 'client_custom_field_selector_optional'],
        'field_value-field_value': ['client_custom_project_selector', 'client_custom_section_selector', 'client_custom_field_selector', 'client_custom_project_selector_optional', 'client_custom_section_selector_optional', 'client_custom_field_selector_optional'],
    };
    let data = {
        'is_custom': $("input[name='client_custom_project_name']").prop('checked'),
        'is_custom_append': $("input[name='client_custom_project_name_append_another_field']").prop('checked'),
        'display_project_as': $("#display_project_as").val(),
        'client_custom_project_selector': $("#client_custom_project_selector").val(),
        'client_custom_project_type_name': $("#client_custom_project_type_name").val(),
        'client_custom_section_selector': $("#client_custom_section_selector").val(),
        'client_custom_section_name': $("#client_custom_section_name").val(),
        'client_custom_field_selector': $("#client_custom_field_selector").val(),
        'client_custom_field_name': $("#client_custom_field_name").val(),
        'client_custom_project_selector_optional': $("#client_custom_project_selector_optional").val(),
        'client_custom_project_type_name_optional': $("#client_custom_project_type_name_optional").val(),
        'client_custom_section_selector_optional': $("#client_custom_section_selector_optional").val(),
        'client_custom_section_name_optional': $("#client_custom_section_name_optional").val(),
        'client_custom_field_selector_optional': $("#client_custom_field_selector_optional").val(),
        'client_custom_field_name_optional': $("#client_custom_field_name_optional").val(),
    };

    var errors = 0;
    if (data.is_custom) {
        if (data.display_project_as == "field_value") {
            let props = validations[data.display_project_as];
            for (let i = 0; i < props.length; i++) {
                if (data[props[i]] == "" || data[props[i]] == null) {
                    $('#' + props[i]).addClass('invalid-custom-project');
                    errors += 1;
                }
            }
        }
        if (data.is_custom_append) {
            let props = validations['client_full_name-field_value'];
            for (let i = 0; i < props.length; i++) {
                if (data[props[i]] == "" || data[props[i]] == null) {
                    $('#' + props[i]).addClass('invalid-custom-project');
                    errors += 1;
                }
            }
        }
    }
    // check if validated
    if (errors > 0) {
        Swal.fire({
            text: 'Required fields missing, please check highlighed fields.',
            icon: "error",
        });
        setTimeout(function () {
            $("body select").removeClass('invalid-custom-project');
        }, 5000);
        return;
    }
    else {
        // format data
        let formData = new FormData();
        let props = Object.keys(data);
        for (let i = 0; i < props.length; i++) {
            formData.append(props[i], data[props[i]]);
        }

        $(".loading").show();
        // send ajax request here
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            url: "/admin/save_settings_cutom_project",
            type: "POST",
            data: formData,
            contentType: false,
            cache: false,
            processData: false,
            success: function (data) {
                $(".loading").hide();
                if (data.status) {
                    Swal.fire({
                        text: data.message,
                        icon: "success",
                    });
                    getCustomProjectSettings();
                }
                else {
                    Swal.fire({
                        text: data.message,
                        icon: "error",
                    });
                }
            },
            error: function (e) {
                $(".loading").hide();
            }
        });
    }
});

function getCustomProjectSettings() {
    $('.loading').show();
    $.ajax({
        type: "POST",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: "/admin/get_settings_cutom_project",
        timeout: 25000,
        success: function (response) {
            $('.loading').hide();
            if (response.status) {
                $("input[name='client_custom_project_name']").prop('checked', response.data.is_custom);
                $("input[name='client_custom_project_name_append_another_field']").prop('checked', response.data.is_custom_append);
                if (response.data.hasOwnProperty('mappings') && response.data.is_custom == 1) {
                    // if(response.data.is_custom_append == 1) {
                    //     $('.client-custom-project-main').removeClass('d-none');
                    // }
                    // else {
                    //     $('.client-custom-project-main').addClass('d-none');
                    // }
                    $('.client-custom-project-settings').removeClass('d-none');

                    // populate options
                    if (response.data.hasOwnProperty('mappings')) {
                        if (response.data.mappings.hasOwnProperty('main')) {
                            $("#client_custom_section_selector").html(response.data.mappings.main.sections);
                            $("#client_custom_field_selector").html(response.data.mappings.main.fields);
                        }
                        if (response.data.mappings.hasOwnProperty('append')) {
                            $("#client_custom_section_selector_optional").html(response.data.mappings.append.sections);
                            $("#client_custom_field_selector_optional").html(response.data.mappings.append.fields);
                        }
                    }

                    if (response.data.mappings.hasOwnProperty('object')) {
                        $('#display_project_as').val(response.data.mappings.object.selected_option);
                        // check for main part
                        if (response.data.mappings.object.selected_option == 'field_value') {
                            $('.client-custom-project-main').removeClass('d-none');
                            // set field values
                            $("#client_custom_project_selector").val(response.data.mappings.object.fv_project_type_id);
                            $("#client_custom_project_type_name").val(response.data.mappings.object.fv_project_type_name);
                            $("#client_custom_section_selector").val(response.data.mappings.object.fv_section_id);
                            $("#client_custom_section_name").val(response.data.mappings.object.fv_section_name);
                            $("#client_custom_field_selector").val(response.data.mappings.object.fv_field_id);
                            $("#client_custom_field_name").val(response.data.mappings.object.fv_field_name);
                        }
                        else {
                            $('.client-custom-project-main').addClass('d-none');
                        }

                        // check for append part
                        if (response.data.is_custom_append == 1) {
                            $('.client-custom-project-optional').removeClass('d-none');
                            // set field values
                            $("#client_custom_project_selector_optional").val(response.data.mappings.object.sec_fv_project_type_id);
                            $("#client_custom_project_type_name_optional").val(response.data.mappings.object.sec_fv_project_type_name);
                            $("#client_custom_section_selector_optional").val(response.data.mappings.object.sec_fv_section_id);
                            $("#client_custom_section_name_optional").val(response.data.mappings.object.sec_fv_section_name);
                            $("#client_custom_field_selector_optional").val(response.data.mappings.object.sec_fv_field_id);
                            $("#client_custom_field_name_optional").val(response.data.mappings.object.sec_fv_field_name);
                        }
                        else {
                            $('.client-custom-project-optional').addClass('d-none');
                        }
                    }
                }
            }
        },
        error: function () {
            $('.loading').hide();
        }
    });
}



$("body").on("change", "select[name='default_sms_way']", async function () {
    let default_sms_way = $(this).val();
    if (default_sms_way == 'broadcast_number') {
        $(".loading").show();
        $.ajax({
            url: "default_contact/get_contact_metadata",
            type: "GET",
            success: function (response) {
                $(".default_sms_custom_contact_div").removeClass("d-none");
                $("select.default_sms_custom_contact_label").html(response.html);
                $("select.default_sms_custom_contact_label").prop("required", true);
            },
            error: function () {
                $(".loading").hide();
                alert("Error to Process Your Request! Please try Again!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    } else {
        $("select.default_sms_custom_contact_label").prop("required", false);
        $("select.default_sms_custom_contact_label").html("");
        $(".default_sms_custom_contact_div").addClass("d-none");
    }
});


$("body").on("click", "input[name='default_sms_way_status']", async function () {
    if ($(this).is(':checked')) {
        $(".default_sms_way_div").removeClass("d-none");
        $(".number_submitted_by_user_div").removeClass("d-none");
    } else {
        $("select[name='default_sms_way']").val($("select[name='default_sms_way'] option:first").val());
        $(".default_sms_way_div").addClass("d-none");
        $("input[name='number_submitted_by_user']:checkbox").prop('checked', false);
        $(".number_submitted_by_user_div").addClass("d-none");
        $("select.default_sms_custom_contact_label").prop("required", false);
        $("select.default_sms_custom_contact_label").html("");
        $(".default_sms_custom_contact_div").addClass("d-none");
    }
});

$("button.add_reply_to_org_email").click(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $(".loading").show();
    $.ajax({
        url: "add_reply_to_org_email",
        type: "POST",
        data: {
            reply_to_org_email: $('input[name=reply_to_org_email]').val(),
        },
        success: function (response) {
            Swal.fire({
                text: response.message,
                icon: "success",
            })
        },
    }).done(function () {
        $(".loading").hide();
    });
});


$("input.sms-line-toggle").change(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $(".loading").show();
    $.ajax({
        url: "setting_update_sms_line_toggle",
        type: "POST",
        data: {
            field_name: $(this).attr("name"),
        },
        success: function (response) {
        },
    }).done(function () {
        $(".loading").hide();
    });
});

$("input.phase_change_response").change(function () {
    if ($(this).is(":checked")) {
        $(".phase_change_response_text_div").removeClass("d-none");
        $(".phase_change_response_text_default").addClass("d-none");
    } else {
        $(".phase_change_response_text_div").addClass("d-none");
        $(".phase_change_response_text_default").removeClass("d-none");
    }
});

$("input.review_request_response").change(function () {
    if ($(this).is(":checked")) {
        $(".review_request_response_text_div").removeClass("d-none");
        $(".review_request_response_text_default").addClass("d-none");
    } else {
        $(".review_request_response_text_div").addClass("d-none");
        $(".review_request_response_text_default").removeClass("d-none");
    }
});

$("input.mass_text_response").change(function () {
    if ($(this).is(":checked")) {
        $(".mass_text_response_text_div").removeClass("d-none");
        $(".mass_text_response_text_default").addClass("d-none");
    } else {
        $(".mass_text_response_text_div").addClass("d-none");
        $(".mass_text_response_text_default").removeClass("d-none");
    }
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

function VitalSlotOrder() {
    let row = 1;
    $(".project-vital").each(function () {
        $(this).find('.vitalSlotOrder').text('Order # ' + row);
        $(this).find('.postOrder').val(row);
        row++;
    });
}
