

$(document).ready(function () {
    edit_trigger_id = 0;
    edit_primary_trigger = '';
    edit_trigger_event = '';
    CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    editDefineVariable();
});
function callTinyMce() {
    tinymce.remove(".clientEmailBody");
    tinymce.init({
        selector: ".clientEmailBody",
        min_height: 500,
        plugins: [
            "advlist autolink lists link image charmap print preview anchor tinymcespellchecker",
            "searchreplace visualblocks code fullscreen",
            "insertdatetime media table paste",
            "media code",
            "lists"
        ],
        menubar: "file edit insert view format table tools",
        toolbar:
            // "undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | outdent indent | bullist numlist | spellchecker language spellcheckdialog | custom_action_button",

            "bullist numlist | undo redo | styleselect | bold italic | lists | alignleft aligncenter alignright alignjustify | outdent indent | spellchecker language spellcheckdialog",
        a11y_advanced_options: true,
        image_list: "get-image-list",
        spellchecker_dialog: true,
        smart_paste: true,
        branding: false,
        image_dimensions: false,
        file_picker_callback: function (callback, value, meta) {
            var input = document.createElement("input");
            input.setAttribute("type", "file");
            if (meta.filetype == "image") {
                input.setAttribute("accept", "image/*");
            }
            if (meta.filetype == "media") {
                input.setAttribute("accept", "video/*");
            }
            input.onchange = function () {
                var file = this.files[0];
                var fileUrl = window.URL.createObjectURL(file);
                var xhr, formData;
                xhr = new XMLHttpRequest();
                xhr.withCredentials = false;
                xhr.open("POST", "/admin/phase_categories/upload");
                xhr.setRequestHeader(
                    "X-CSRF-Token",
                    $('meta[name="csrf-token"]').attr("content")
                );
                xhr.onload = function () {
                    var json;
                    if (xhr.status != 200) {
                        console.log("HTTP Error: " + xhr.status);
                        return;
                    }
                    json = JSON.parse(xhr.responseText);
                    if (typeof json.error_message != "undefined") {
                        document
                            .querySelectorAll(".tox-dialog-wrap")
                            .forEach(function (el) {
                                el.style.display = "none";
                            });
                            if(json.error_message.code == "The code has already been taken."){
                                Swal.fire({
                                    text: "The media locker code exists already. Please use another one!",
                                    icon: "warning"
                                });
                            }else{
                                Swal.fire({
                                    text: json.error_message.code ?? json.error_message,
                                    icon: "error"
                                });
                            }
                            location.reload()
                        return;
                    }
                    if (!json || typeof json.location != "string") {
                        console.log("Invalid JSON: " + xhr.responseText);
                        return;
                    }
                    var mediasource = json.location;
                    callback(json.location, { source2: json.location });
                };
                formData = new FormData();
                var mediaCode = document.querySelector('.media-code').value;
                formData.append('file', file);
                formData.append('code', mediaCode);
                xhr.send(formData);
            };
            input.click();
        },
        setup: function (editor) {
            editor.on('init', function () {
                editor.on('OpenWindow', function (e) {
                    const title = document.querySelector(".tox-dialog__title").textContent;
                    
                     // custom media code text box
                     if(title !== "Insert/Edit Media"){
                         const para = document.createElement("div");
                         para.classList ? para.classList.add('custom-div') : para.className += ' custom-div';
                         
                         const cstLabel = document.createElement("label");
                         para.appendChild(cstLabel)
                         
                         input = document.createElement("input");
                         input.type = "text";
                         input.classList ? input.classList.add('tox-textfield') : input.className += 'tox-textfield';
                         input.classList ? input.classList.add('media-code') : input.className += 'media-code';
                         para.appendChild(input);
     
                         const node = document.createTextNode("Media Code");
                         cstLabel.appendChild(node);
     
                         const element = document.querySelector(".tox-form");
                         const child = document.querySelector(".tox-form__group");
                         element.insertBefore(para,child);
                         document.querySelector('.media-code').focus();
                         document.querySelector('.media-code').select();
                     }
                     // custom media codee
                    var dropdown = document.querySelector('.tox-listboxfield .tox-listbox--select');
                    var upload = document.querySelector('.tox-form__controls-h-stack .tox-browse-url');
                    var txtfield = document.querySelector('.tox-textfield');
                    var dropdownDisabled = dropdown.disabled = true;
                    document.querySelector('.tox-checkbox__label').innerHTML = "choose file from media";

                    document.querySelector('.tox-checkbox__label').addEventListener('click', function() {
                        toggleFunctionality();
                    });
                    document.querySelector('.tox-checkbox__icons').addEventListener('click', function() {
                        toggleFunctionality();
                    });
                    
                    document.querySelector('.tox-checkbox-icon__checked').addEventListener('click', function() {
                        toggleFunctionality();
                    });
                    function toggleFunctionality() {
                        if (dropdown.disabled) {
                            dropdown.disabled = false;
                            upload.disabled = true;
                            txtfield.disabled = true;
                            const labels = document.querySelectorAll('label');
                            // for (const label of labels) {
                            //     if (label.innerText === 'Alternative description') {
                            //         label.innerText = 'Meida Code';
                            //     }
                            // }
                        } else {
                            dropdown.disabled = true;
                            txtfield.disabled = false;
                            upload.disabled = false;
                            const labels = document.querySelectorAll('label');
                            // for (const label of labels) {
                            //     if (label.innerText === 'Meida Code') {
                            //         label.innerText = 'Alternative description'; 
                            //     }
                            // }
                        }
                       
                    }
                  
                });
            });
            editor.ui.registry.addButton('custom_action_button', {
                text: 'Link Button',
                onAction: function () {
                    editor.windowManager.open({
                        title: 'Add custom button',
                        body: {
                            type: 'panel',
                            items: [{
                                type: 'input',
                                name: 'button_label',
                                label: 'Button Label',
                                flex: true
                            }, {
                                type: 'input',
                                name: 'button_href',
                                label: 'Button Link',
                                flex: true
                            }, {
                                type: 'selectbox',
                                name: 'button_target',
                                label: 'Target',
                                items: [
                                    { text: 'None', value: '' },
                                    { text: 'New window', value: '_blank' },
                                    { text: 'Self', value: '_self' },
                                    { text: 'Parent', value: '_parent' }
                                ],
                                flex: true
                            }, {
                                type: 'selectbox',
                                name: 'button_style',
                                label: 'Style',
                                items: [
                                    { text: 'Success', value: 'success' },
                                    { text: 'Info', value: 'info' },
                                    { text: 'Warning', value: 'warning' },
                                    { text: 'Error', value: 'error' }
                                ],
                                flex: true
                            }]
                        },
                        onSubmit: function (api) {
                            var html = '<a href="' + api.getData().button_href + '" class="btn btn-' + api.getData().button_style + '" target="' + api.getData().button_target + '">' + api.getData().button_label + '</a>';
                            editor.insertContent(html);
                            api.close();
                        },
                        buttons: [
                            {
                                text: 'Close',
                                type: 'cancel',
                                onclick: 'close'
                            },
                            {
                                text: 'Insert',
                                type: 'submit',
                                primary: true,
                                enabled: false
                            }
                        ]
                    });
                }
            });
        },
    });
}
function editDefineVariable() {
    edit_filter_selection = 0;
    edit_project_type_id = 0;
    edit_project_type_name = 0;
    edit_phase_name_id = 0;
    edit_phase_name = '';
    edit_filter_contact_by = '';
    edit_person_type_selection_id = 0;
    edit_person_type_selection_name = "";
    edit_filter_task_by = "";
    edit_org_user_id = 0;
    edit_org_user_name = "";
    edit_project_section_selector = "";
    edit_project_section_selector_name = "";
    edit_project_section_field_selector = 0;
    edit_project_section_field_name = "";
    edit_filter_appointment_by = "";
    edit_filter_appointment_by_name = "";
    edit_project_hashtag = "";
    edit_tenant_form_id = 0;
    edit_tenant_form_name = "";
    edit_client_file_upload_configuration_id = 0;
    edit_client_file_upload_configuration_name = "";
    edit_sms_line = "";
    edit_trigger_name = "";
}


$("body").on("change", "input[name='edit_filter_selection']", async function () {

    resetFormValue();

    if ($(this).prop("checked") == true) {
        edit_filter_selection = 1;
    } else {
        edit_filter_selection = 0;
    }

    if (!edit_filter_selection) {
        editResetFormItem($(this).attr('name'));
    }

    if (edit_primary_trigger == 'Project' && edit_trigger_event == 'PhaseChanged' && edit_filter_selection) {
        $(".edit_project_type_div").removeClass("d-none");

        $(".loading").show();
        $.ajax({
            url: "automated_workflow/get_project_type_list",
            type: "GET",
            success: function (response) {
                $("select[name='edit_project_type_id']").html(response.html);
            },
            error: function () {
                $(".loading").hide();
                alert("Error to Process Your Request! Please try Again!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    }

    if (edit_primary_trigger == 'Project' && edit_trigger_event == 'AddedHashtag' && edit_filter_selection) {
        $(".edit_project_hashtag_div").removeClass("d-none");
    }

    if (edit_primary_trigger == 'Contact' && edit_filter_selection) {
        let filter_options = '<option value="">Select Contact By</option>' +
            '<option value="Person Types">Person Types</option>';
        $(".edit_filter_contact_by_div").removeClass("d-none");
        $("select[name='edit_filter_contact_by']").html(filter_options);
    }

    if (edit_primary_trigger == 'Note' && edit_trigger_event == 'Created' && edit_filter_selection) {
        let filter_options = '<option value="">Select Task By</option>' +
            '<option value="Task Hashtags">Task Hashtags</option>' +
            '<option value="Assigned To">Assigned To</option>' +
            '<option value="Created By">Created By</option>' +
            '<option value="Auto-Generated Task">Auto-Generated Task</option>';
        $(".edit_filter_task_by_div").removeClass("d-none");
        $("select[name='edit_filter_task_by']").html(filter_options);
    }

    if (edit_primary_trigger == 'Note' && edit_trigger_event == 'Completed' && edit_filter_selection) {
        let filter_options = '<option value="">Select Task By</option>' +
            '<option value="Task Hashtags">Task Hashtags</option>' +
            '<option value="Completed By">Completed By</option>' +
            '<option value="Auto-Generated Task">Auto-Generated Task</option>';
        $(".edit_filter_task_by_div").removeClass("d-none");
        $("select[name='edit_filter_task_by']").html(filter_options);
    }

    if ((edit_primary_trigger == 'Note' && edit_trigger_event == 'TaskflowButtonTrigger' && edit_filter_selection)) {

        $(".edit_project_type_div").removeClass("d-none");
        $(".edit_project_section_div").removeClass("d-none");
        $(".edit_project_section_field_div").removeClass("d-none");

        $(".project_section_label").text("Choose Section");

        $(".loading").show();
        $.ajax({
            url: "automated_workflow/get_project_type_list",
            type: "GET",
            success: function (response) {
                $("select[name='edit_project_type_id']").html(response.html);
            },
            error: function () {
                $(".loading").hide();
                alert("Error to Process Your Request! Please try Again!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    }


    if (edit_primary_trigger == 'CollectionItem' && edit_filter_selection) {

        $(".edit_project_type_div").removeClass("d-none");
        $(".edit_project_section_div").removeClass("d-none");

        $(".project_section_label").text("Choose Collection");

        $(".loading").show();
        $.ajax({
            url: "automated_workflow/get_project_type_list",
            type: "GET",
            success: function (response) {
                $("select[name='edit_project_type_id']").html(response.html);
            },
            error: function () {
                $(".loading").hide();
                alert("Error to Process Your Request! Please try Again!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    }

    if (edit_primary_trigger == 'Appointment' && edit_filter_selection) {
        let filter_options = '<option value="">Select Appointment By</option>' +
            '<option value="Note Hashtag">Note Hashtag</option>' +
            '<option value="All Day Appointment">All Day Appointment</option>' +
            '<option value="Attendee">Attendee</option>';
        $(".edit_filter_appointment_by_div").removeClass("d-none");
        $("select[name='edit_filter_appointment_by']").html(filter_options);
    }

    if (edit_primary_trigger == 'Section' && edit_filter_selection) {
        $(".edit_project_type_div").removeClass("d-none");
        $(".edit_project_section_div").removeClass("d-none");
        $(".loading").show();
        $.ajax({
            url: "automated_workflow/get_project_type_list",
            type: "GET",
            success: function (response) {
                $("select[name='edit_project_type_id']").html(response.html);
            },
            error: function () {
                $(".loading").hide();
                alert("Error to Process Your Request! Please try Again!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    }

    if (edit_primary_trigger == 'FormSubmitted' && edit_filter_selection) {
        $(".edit_tenant_form_div").removeClass("d-none");
    }
    if (edit_primary_trigger == 'DocumentUploaded' && edit_filter_selection) {
        $(".edit_client_file_upload_configuration_div").removeClass("d-none");
    }
    if (edit_primary_trigger == 'SMSReceived' && edit_filter_selection) {
        $(".edit_sms_line_div").removeClass("d-none");
    }

});


$("body").on("change", "select[name='edit_project_type_id']", async function () {
    edit_project_type_id = $(this).val();
    edit_project_type_name = $(this).find("option:selected").text();
    if (edit_primary_trigger == 'Project' && edit_trigger_event == 'PhaseChanged' && edit_filter_selection) {
        $(".loading").show();
        $.ajax({
            url: "automated_workflow/get_phase_list",
            type: "GET",
            data: { project_type_id: edit_project_type_id },
            success: function (response) {
                $(".edit_phase_name_div").removeClass("d-none");
                $("select[name='edit_phase_name_id']").html(response.html);
            },
            error: function () {
                $(".loading").hide();
                alert("Error to Process Your Request! Please try Again!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    }

    if (edit_primary_trigger == 'Note' && edit_trigger_event == 'TaskflowButtonTrigger' && edit_filter_selection) {

        $(".loading").show();
        $.ajax({
            url: "automated_workflow/get_project_type_section_list",
            type: "GET",
            data: { project_type_id: edit_project_type_id },
            success: function (response) {
                $(".edit_project_section_div").removeClass("d-none");
                $("select[name='edit_project_section_selector']").html(response.html);
            },
            error: function () {
                $(".loading").hide();
                alert("Error to Process Your Request! Please try Again!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    }

    if (edit_primary_trigger == 'CollectionItem' && edit_filter_selection) {

        $(".loading").show();
        $.ajax({
            url: "automated_workflow/get_project_type_collection_list",
            type: "GET",
            data: { project_type_id: edit_project_type_id },
            success: function (response) {
                $(".edit_project_section_div").removeClass("d-none");
                $("select[name='edit_project_section_selector']").html(response.html);
            },
            error: function () {
                $(".loading").hide();
                alert("Error to Process Your Request! Please try Again!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    }

    if (edit_primary_trigger == 'Section' && edit_filter_selection) {

        $(".loading").show();
        $.ajax({
            url: "automated_workflow/get_project_type_section_list",
            type: "GET",
            data: { project_type_id: edit_project_type_id },
            success: function (response) {
                $(".edit_project_section_div").removeClass("d-none");
                $("select[name='edit_project_section_selector']").html(response.html);
            },
            error: function () {
                $(".loading").hide();
                alert("Error to Process Your Request! Please try Again!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    }

});

$("body").on("change", "select[name='edit_project_section_selector']", async function () {
    edit_project_section_selector = $(this).val();
    edit_project_section_selector_name = $(this).find("option:selected").text();
    if (edit_primary_trigger == 'Note' && edit_trigger_event == 'TaskflowButtonTrigger' && edit_filter_selection) {
        $(".loading").show();
        $.ajax({
            url: "automated_workflow/get_project_section_field",
            type: "GET",
            data: {
                project_type_id: edit_project_type_id,
                project_section_selector: edit_project_section_selector
            },
            success: function (response) {
                $(".edit_project_section_field_div").removeClass("d-none");
                $("select[name='edit_project_section_field_selector']").html(response.html);
            },
            error: function () {
                $(".loading").hide();
                alert("Error to Process Your Request! Please try Again!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    }
});

$("body").on("change", "select[name='edit_project_section_field_selector']", async function () {
    edit_project_section_field_selector = $(this).val();
    edit_project_section_field_name = $(this).find("option:selected").text();
});

$("body").on("change", "select[name='edit_phase_name_id']", async function () {
    edit_phase_name_id = $(this).val();
    edit_phase_name = $(this).find("option:selected").text();
});

$("body").on("change", "select[name='edit_filter_contact_by']", async function () {
    edit_filter_contact_by = $(this).val();
    if (edit_primary_trigger == 'Contact' && edit_filter_contact_by == 'Person Types' && edit_filter_selection) {
        $(".loading").show();
        $.ajax({
            url: "automated_workflow/get_contact_metadata",
            type: "GET",
            success: function (response) {
                $(".edit_person_type_selection_div").removeClass("d-none");
                $("select[name='edit_person_type_selection_id']").html(response.html);
            },
            error: function () {
                $(".loading").hide();
                alert("Error to Process Your Request! Please try Again!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    }
});

$("body").on("change", "select[name='edit_person_type_selection_id']", async function () {
    edit_person_type_selection_id = $(this).val();
    edit_person_type_selection_name = $(this).find("option:selected").text();
});

$("body").on("change", "select[name='edit_filter_task_by']", async function () {
    edit_filter_task_by = $(this).val();
    editResetFormItem($(this).attr('name'));
    if (edit_filter_task_by == 'Task Hashtags' || edit_filter_task_by == 'Auto-Generated Task') {
        $(".edit_project_hashtag_div").removeClass("d-none");
        $(".hastag_label").text("Contains Hashtag");
    }
    if (edit_filter_task_by == 'Assigned To' || edit_filter_task_by == 'Created By' || edit_filter_task_by == 'Completed By') {
        $(".loading").show();
        $.ajax({
            url: "automated_workflow/get_user_list",
            type: "GET",
            success: function (response) {
                $(".edit_org_user_div").removeClass("d-none");
                $("select[name='edit_org_user_id']").html(response.html);
            },
            error: function () {
                $(".loading").hide();
                alert("Error to Process Your Request! Please try Again!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    }

});

$("body").on("change", "select[name='edit_org_user_id']", async function () {
    edit_org_user_id = $(this).val();
    edit_org_user_name = $(this).find("option:selected").text();
});

$("body").on("change", "select[name='edit_tenant_form_id']", async function () {
    edit_tenant_form_id = $(this).val();
    edit_tenant_form_name = $(this).find("option:selected").text();
});
$("body").on("change", "select[name='edit_client_file_upload_configuration_id']", async function () {
    edit_client_file_upload_configuration_id = $(this).val();
    edit_client_file_upload_configuration_name = $(this).find("option:selected").text();
});
$("body").on("change", "select[name='edit_sms_line']", async function () {
    edit_sms_line = $(this).val();
});

$("body").on("change", "select[name='edit_filter_appointment_by']", async function () {
    editResetFormItem($(this).attr('name'));
    edit_filter_appointment_by = $(this).val();
    edit_filter_appointment_by_name = $(this).find("option:selected").text();
    if (edit_filter_appointment_by == 'Note Hashtag') {
        $(".edit_project_hashtag_div").removeClass("d-none");
        $(".hastag_label").text("Contains Hashtag");
    }

    if (edit_filter_appointment_by == 'Attendee') {
        $(".loading").show();
        $.ajax({
            url: "automated_workflow/get_user_list",
            type: "GET",
            success: function (response) {
                $(".edit_org_user_div").removeClass("d-none");
                $("select[name='edit_org_user_id']").html(response.html);
            },
            error: function () {
                $(".loading").hide();
                alert("Error to Process Your Request! Please try Again!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    }

});

function hideAll() {
    $(".edit_project_type_div").addClass("d-none");
    $(".edit_phase_name_div").addClass("d-none");
    $(".edit_filter_contact_by_div").addClass("d-none");
    $(".edit_person_type_selection_div").addClass("d-none");
    $(".edit_project_section_div").addClass("d-none");
    $(".edit_project_section_field_div").addClass("d-none");
    $(".edit_filter_task_by_div").addClass("d-none");
    $(".edit_filter_appointment_by_div").addClass("d-none");
    $(".edit_org_user_div").addClass("d-none");
    $(".edit_project_hashtag_div").addClass("d-none");
    $(".edit_tenant_form_div").addClass("d-none");
    $(".edit_client_file_upload_configuration_div").addClass("d-none");
    $(".edit_sms_line_div").addClass("d-none");
    $("input[name='edit_filter_selection']:checkbox").prop('checked', false);
}

function resetFormValue() {
    $("select[name='edit_project_type_id']").html("<option value=''>Project Type...</option>");
    $("select[name='edit_phase_name_id']").html("<option value=''>Phase...</option>");
    $("select[name='edit_filter_contact_by']").html("<option value=''>Select Contact By</option>");
    $("select[name='edit_person_type_selection_id']").html("<option value=''>Person Type</option>");
    $("select[name='edit_project_section_selector']").html("<option value=''>Choose Section</option>");
    $("select[name='edit_project_section_field_selector']").html("<option value=''>Choose Field</option>");
    $("select[name='edit_filter_task_by']").html("<option value=''>Select Task By</option>");
    $("select[name='edit_filter_appointment_by']").html("<option value=''>Select Appointment</option>");
    $("select[name='edit_org_user_id']").html("<option value=''>User...</option>");
    $("input[name='edit_project_hashtag']").val("");
}

function editResetFormItem(item_name) {
    if (item_name == 'edit_filter_selection') {
        hideAll();
        editDefineVariable();
    }

    if (item_name == 'edit_filter_task_by' || item_name == 'edit_filter_appointment_by') {
        $(".edit_project_hashtag_div").addClass("d-none");
        $(".edit_org_user_div").addClass("d-none");
        edit_project_hashtag = "";
        edit_org_user_id = 0;
        edit_org_user_name = "";
        edit_filter_appointment_by = "";
        edit_filter_appointment_by_name = "";
    }
}


$(".edit-trigger-save").click(function () {

    let edit_trigger_name = $("input[name='edit_trigger_name']").val();
    let edit_project_hashtag = $("input[name='edit_project_hashtag']").val();

    let trigger_data = {
        _token: CSRF_TOKEN,
        trigger_id: edit_trigger_id,
        primary_trigger: edit_primary_trigger,
        trigger_event: edit_trigger_event,
        trigger_name: edit_trigger_name,
        filter_selection: edit_filter_selection,
        project_type_id: edit_project_type_id,
        project_type_name: edit_project_type_name,
        phase_name_id: edit_phase_name_id,
        phase_name: edit_phase_name,
        filter_contact_by: edit_filter_contact_by,
        person_type_selection_id: edit_person_type_selection_id,
        person_type_selection_name: edit_person_type_selection_name,
        filter_task_by: edit_filter_task_by,
        org_user_id: edit_org_user_id,
        org_user_name: edit_org_user_name,
        project_section_selector: edit_project_section_selector,
        project_section_selector_name: edit_project_section_selector_name,
        project_section_field_selector: edit_project_section_field_selector,
        project_section_field_name: edit_project_section_field_name,
        filter_appointment_by: edit_filter_appointment_by,
        filter_appointment_by_name: edit_filter_appointment_by_name,
        project_hashtag: edit_project_hashtag,
        tenant_form_id: edit_tenant_form_id,
        tenant_form_name: edit_tenant_form_name,
        client_file_upload_configuration_id: edit_client_file_upload_configuration_id,
        client_file_upload_configuration_name: edit_client_file_upload_configuration_name,
        sms_line: edit_sms_line
    };

    $(".loading").show();
    $.ajax({
        url: "automated_workflow/save",
        type: "POST",
        data: trigger_data,
        dataType: 'JSON',
        success: function (response) {
            let icon_class = "success";
            if (!response.status) {
                icon_class = "error";
            }
            Swal.fire({
                text: response.message,
                icon: icon_class,
            }).then(function () {
                location.reload();
            });
        },
        error: function () {
            $(".loading").hide();
            alert("Error to Process Your Request! Please try Again!");
        },
    }).done(function () {
        $(".loading").hide();
    });
});



$("body").on("click", "button.trigger-edit", async function () {
    editDefineVariable();
    resetFormValue();
    hideAll();
    $(".edit_trigger_event_div").removeClass("d-none");

    let json_details = ($(this).data('json'));

    edit_trigger_id = json_details.trigger_table_id;
    edit_primary_trigger = json_details.primary_trigger;
    edit_trigger_event = json_details.trigger_event;
    edit_filter_selection = json_details.is_filter;

    $('.trigger_edit_id').text('(ID# ' + edit_trigger_id + ')');
    $('input[name="edit_primary_trigger"]').val(json_details.primary_trigger_display);
    $('input[name="edit_trigger_event"]').val(edit_trigger_event);
    $('input[name="edit_trigger_name"]').val(json_details.trigger_name);

    if (edit_primary_trigger == 'TeamMessageReply' || edit_primary_trigger == 'DocumentShared') {
        $(".edit_filter_selection_div").addClass("d-none");
        $(".edit_trigger_event_div").addClass("d-none");
    } else if (edit_primary_trigger == 'DocumentUploaded' || edit_primary_trigger == 'FormSubmitted' || edit_primary_trigger == 'SMSReceived') {
        $(".edit_filter_selection_div").removeClass("d-none");
        $(".edit_trigger_event_div").addClass("d-none");
    } else if (edit_primary_trigger == 'ProjectRelation') {
        $(".edit_filter_selection_div").addClass("d-none");
    } else {
        $(".edit_filter_selection_div").removeClass("d-none");
    }

    if (edit_filter_selection) {
        $("input[name='edit_filter_selection']:checkbox").prop('checked', true);

        if (edit_primary_trigger == 'Project' && edit_trigger_event == 'Created') {
            edit_project_type_id = json_details.fv_project_type_id;
            edit_project_type_name = json_details.fv_project_type_name;
        } else if (edit_primary_trigger == 'Project' && edit_trigger_event == 'PhaseChanged') {
            edit_project_type_id = json_details.fv_project_type_id;
            edit_project_type_name = json_details.fv_project_type_name;
            edit_phase_name_id = json_details.fv_project_phase_id;
            edit_phase_name = json_details.fv_project_phase_name;
        } else if (edit_primary_trigger == 'Project' && edit_trigger_event == 'AddedHashtag') {
            edit_project_hashtag = json_details.fv_project_hashtag;
        } else if (edit_primary_trigger == 'Contact') {
            edit_filter_contact_by = json_details.fv_contact_filter_type_name;
            edit_person_type_selection_id = json_details.fv_contact_person_type_id;
            edit_person_type_selection_name = json_details.fv_contact_person_type_name;
        } else if (edit_primary_trigger == 'Note') {
            edit_filter_task_by = json_details.fv_task_filter_type_name;
            if (edit_filter_task_by == 'Task Hashtags' || edit_filter_task_by == 'Auto-Generated Task') {
                edit_project_hashtag = json_details.fv_task_hashtag;
            } else if (edit_filter_task_by == 'Assigned To') {
                edit_org_user_id = json_details.fv_task_assigned_user_id;
                edit_org_user_name = json_details.fv_task_assigned_user_name;
            } else if (edit_filter_task_by == 'Created By') {
                edit_org_user_id = json_details.fv_task_created_user_id;
                edit_org_user_name = json_details.fv_task_created_user_name;
            } else if (edit_filter_task_by == 'Completed By') {
                edit_org_user_id = json_details.fv_task_completed_user_id;
                edit_org_user_name = json_details.fv_task_completed_user_name;
            } else if (edit_trigger_event == 'TaskflowButtonTrigger') {
                edit_project_type_id = json_details.fv_taskflow_project_type_id;
                edit_project_type_name = json_details.fv_taskflow_project_type_name;
                edit_project_section_selector = json_details.fv_taskflow_section_id;
                edit_project_section_selector_name = json_details.fv_taskflow_section_name;
                edit_project_section_field_selector = json_details.fv_taskflow_field_id;
                edit_project_section_field_name = json_details.fv_taskflow_field_name;
            }
        } else if (edit_primary_trigger == 'CollectionItem') {
            edit_project_type_id = json_details.fv_collection_item_project_type_id;
            edit_project_type_name = json_details.fv_collection_item_project_type_name;
            edit_project_section_selector = json_details.fv_collection_item_section_id;
            edit_project_section_selector_name = json_details.fv_collection_item_section_name;
            edit_project_section_field_selector = json_details.fv_collection_item_field_id;
            edit_project_section_field_name = json_details.fv_collection_item_field_name;
        } else if (edit_primary_trigger == 'Appointment') {
            edit_filter_appointment_by = json_details.fv_calendar_filter_type_name;
            if (edit_filter_appointment_by == 'Note Hashtag') {
                edit_project_hashtag = json_details.fv_calendar_hashtag;
            } else if (edit_filter_appointment_by == 'Attendee') {
                edit_org_user_id = json_details.fv_calendar_attendee_user_id;
                edit_org_user_name = json_details.fv_calendar_attendee_user_name;
            }
        } else if (edit_primary_trigger == 'Section') {
            edit_project_type_id = json_details.fv_section_toggled_project_type_id;
            edit_project_type_name = json_details.fv_section_toggled_project_type_name;
            edit_project_section_selector = json_details.fv_section_toggled_section_id;
            edit_project_section_selector_name = json_details.fv_section_toggled_section_name;
        } else if (edit_primary_trigger == 'DocumentUploaded') {
            edit_client_file_upload_configuration_id = json_details.client_file_upload_configuration_id;
            edit_client_file_upload_configuration_name = json_details.client_file_upload_configuration_name;
        } else if (edit_primary_trigger == 'FormSubmitted') {
            edit_tenant_form_id = json_details.tenant_form_id;
            edit_tenant_form_name = json_details.tenant_form_name;
        } else if (edit_primary_trigger == 'SMSReceived') {
            edit_sms_line = json_details.sms_line;
        }
    }


    if (edit_primary_trigger == 'Project' && edit_filter_selection && (edit_trigger_event == 'PhaseChanged' || edit_trigger_event == 'Created')) {
        $(".edit_project_type_div").removeClass("d-none");

        $(".loading").show();
        $.ajax({
            url: "automated_workflow/get_project_type_list",
            type: "GET",
            success: function (response) {
                $("select[name='edit_project_type_id']").html(response.html);
                $("select[name='edit_project_type_id']").val(edit_project_type_id);
            },
            error: function () {
                $(".loading").hide();
                alert("Error to Process Your Request! Please try Again!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    }

    if (edit_primary_trigger == 'Project' && edit_trigger_event == 'AddedHashtag' && edit_filter_selection) {
        $(".edit_project_hashtag_div").removeClass("d-none");
        $("input[name='edit_project_hashtag']").val(edit_project_hashtag);
    }

    if (edit_primary_trigger == 'Contact' && edit_filter_selection) {
        let filter_options = '<option value="">Select Contact By</option>' +
            '<option value="Person Types">Person Types</option>';
        $(".edit_filter_contact_by_div").removeClass("d-none");
        $("select[name='edit_filter_contact_by']").html(filter_options);
        $("select[name='edit_filter_contact_by']").val(edit_filter_contact_by);
    }

    if (edit_primary_trigger == 'Note' && edit_trigger_event == 'Created' && edit_filter_selection) {
        let filter_options = '<option value="">Select Task By</option>' +
            '<option value="Task Hashtags">Task Hashtags</option>' +
            '<option value="Assigned To">Assigned To</option>' +
            '<option value="Created By">Created By</option>' +
            '<option value="Auto-Generated Task">Auto-Generated Task</option>';
        $(".edit_filter_task_by_div").removeClass("d-none");
        $("select[name='edit_filter_task_by']").html(filter_options);
        $("select[name='edit_filter_task_by']").val(edit_filter_task_by);
    }

    if (edit_primary_trigger == 'Note' && edit_trigger_event == 'Completed' && edit_filter_selection) {
        let filter_options = '<option value="">Select Task By</option>' +
            '<option value="Task Hashtags">Task Hashtags</option>' +
            '<option value="Completed By">Completed By</option>' +
            '<option value="Auto-Generated Task">Auto-Generated Task</option>';
        $(".edit_filter_task_by_div").removeClass("d-none");
        $("select[name='edit_filter_task_by']").html(filter_options);
        $("select[name='edit_filter_task_by']").val(edit_filter_task_by);
    }

    if ((edit_primary_trigger == 'Note' && edit_trigger_event == 'TaskflowButtonTrigger' && edit_filter_selection)) {

        $(".edit_project_type_div").removeClass("d-none");
        $(".edit_project_section_div").removeClass("d-none");
        $(".edit_project_section_field_div").removeClass("d-none");
        $(".edit_project_section_label").text("Choose Section");

        $(".loading").show();
        $.ajax({
            url: "automated_workflow/get_project_type_list",
            type: "GET",
            success: function (response) {
                $("select[name='edit_project_type_id']").html(response.html);
                $("select[name='edit_project_type_id']").val(edit_project_type_id);
            },
            error: function () {
                $(".loading").hide();
                alert("Error to Process Your Request! Please try Again!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    }


    if (edit_primary_trigger == 'CollectionItem' && edit_filter_selection) {

        $(".edit_project_type_div").removeClass("d-none");
        $(".edit_project_section_div").removeClass("d-none");
        $(".edit_project_section_label").text("Choose Collection");

        $(".loading").show();
        $.ajax({
            url: "automated_workflow/get_project_type_list",
            type: "GET",
            success: function (response) {
                $("select[name='edit_project_type_id']").html(response.html);
                $("select[name='edit_project_type_id']").val(edit_project_type_id);
            },
            error: function () {
                $(".loading").hide();
                alert("Error to Process Your Request! Please try Again!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    }

    if (edit_primary_trigger == 'Appointment' && edit_filter_selection) {
        let filter_options = '<option value="">Select Appointment By</option>' +
            '<option value="Note Hashtag">Note Hashtag</option>' +
            '<option value="All Day Appointment">All Day Appointment</option>' +
            '<option value="Attendee">Attendee</option>';
        $(".edit_filter_appointment_by_div").removeClass("d-none");
        $("select[name='edit_filter_appointment_by']").html(filter_options);
        $("select[name='edit_filter_appointment_by']").val(edit_filter_appointment_by);
    }

    if (edit_primary_trigger == 'Section' && edit_filter_selection) {
        $(".edit_project_type_div").removeClass("d-none");
        $(".edit_project_section_div").removeClass("d-none");
        $(".loading").show();
        $.ajax({
            url: "automated_workflow/get_project_type_list",
            type: "GET",
            success: function (response) {
                $("select[name='edit_project_type_id']").html(response.html);
                $("select[name='edit_project_type_id']").val(edit_project_type_id);
            },
            error: function () {
                $(".loading").hide();
                alert("Error to Process Your Request! Please try Again!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    }

    if (edit_primary_trigger == 'Project' && edit_trigger_event == 'PhaseChanged' && edit_filter_selection) {
        $(".loading").show();
        $.ajax({
            url: "automated_workflow/get_phase_list",
            type: "GET",
            data: { project_type_id: edit_project_type_id },
            success: function (response) {
                $(".edit_phase_name_div").removeClass("d-none");
                $("select[name='edit_phase_name_id']").html(response.html);
                $("select[name='edit_phase_name_id']").val(edit_phase_name_id);
            },
            error: function () {
                $(".loading").hide();
                alert("Error to Process Your Request! Please try Again!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    }

    if (edit_primary_trigger == 'Note' && edit_trigger_event == 'TaskflowButtonTrigger' && edit_filter_selection) {

        $(".loading").show();
        $.ajax({
            url: "automated_workflow/get_project_type_section_list",
            type: "GET",
            data: { project_type_id: edit_project_type_id },
            success: function (response) {
                $(".edit_project_section_div").removeClass("d-none");
                $("select[name='edit_project_section_selector']").html(response.html);
                $("select[name='edit_project_section_selector']").val(edit_project_section_selector);
            },
            error: function () {
                $(".loading").hide();
                alert("Error to Process Your Request! Please try Again!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    }

    if (edit_primary_trigger == 'CollectionItem' && edit_filter_selection) {

        $(".loading").show();
        $.ajax({
            url: "automated_workflow/get_project_type_collection_list",
            type: "GET",
            data: { project_type_id: edit_project_type_id },
            success: function (response) {
                $(".edit_project_section_div").removeClass("d-none");
                $("select[name='edit_project_section_selector']").html(response.html);
                $("select[name='edit_project_section_selector']").val(edit_project_section_selector);
            },
            error: function () {
                $(".loading").hide();
                alert("Error to Process Your Request! Please try Again!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    }

    if (edit_primary_trigger == 'Section' && edit_filter_selection) {
        $(".loading").show();
        $.ajax({
            url: "automated_workflow/get_project_type_section_list",
            type: "GET",
            data: { project_type_id: edit_project_type_id },
            success: function (response) {
                $(".edit_project_section_div").removeClass("d-none");
                $("select[name='edit_project_section_selector']").html(response.html);
                $("select[name='edit_project_section_selector']").val(edit_project_section_selector);
            },
            error: function () {
                $(".loading").hide();
                alert("Error to Process Your Request! Please try Again!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    }

    if (edit_primary_trigger == 'Note' && edit_trigger_event == 'TaskflowButtonTrigger' && edit_filter_selection) {
        $(".loading").show();
        $.ajax({
            url: "automated_workflow/get_project_section_field",
            type: "GET",
            data: {
                project_type_id: edit_project_type_id,
                project_section_selector: edit_project_section_selector
            },
            success: function (response) {
                $(".edit_project_section_field_div").removeClass("d-none");
                $("select[name='edit_project_section_field_selector']").html(response.html);
                $("select[name='edit_project_section_field_selector']").val(edit_project_section_field_selector);
            },
            error: function () {
                $(".loading").hide();
                alert("Error to Process Your Request! Please try Again!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    }

    if (edit_primary_trigger == 'Contact' && edit_filter_contact_by == 'Person Types' && edit_filter_selection) {
        $(".loading").show();
        $.ajax({
            url: "automated_workflow/get_contact_metadata",
            type: "GET",
            success: function (response) {
                $(".edit_person_type_selection_div").removeClass("d-none");
                $("select[name='edit_person_type_selection_id']").html(response.html);
                $("select[name='edit_person_type_selection_id']").val(edit_person_type_selection_id);
            },
            error: function () {
                $(".loading").hide();
                alert("Error to Process Your Request! Please try Again!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    }

    if (edit_filter_task_by == 'Task Hashtags' || edit_filter_task_by == 'Auto-Generated Task') {
        $(".edit_project_hashtag_div").removeClass("d-none");
        $(".hastag_label").text("Contains Hashtag");
        $("input[name='edit_project_hashtag']").val(edit_project_hashtag);
    }
    if (edit_filter_task_by == 'Assigned To' || edit_filter_task_by == 'Created By' || edit_filter_task_by == 'Completed By') {
        $(".loading").show();
        $.ajax({
            url: "automated_workflow/get_user_list",
            type: "GET",
            success: function (response) {
                $(".edit_org_user_div").removeClass("d-none");
                $("select[name='edit_org_user_id']").html(response.html);
                $("select[name='edit_org_user_id']").val(edit_org_user_id);
            },
            error: function () {
                $(".loading").hide();
                alert("Error to Process Your Request! Please try Again!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    }

    if (edit_filter_appointment_by == 'Note Hashtag') {
        $(".edit_project_hashtag_div").removeClass("d-none");
        $(".hastag_label").text("Contains Hashtag");
        $("input[name='edit_project_hashtag']").val(edit_project_hashtag);
    }

    if (edit_filter_appointment_by == 'Attendee') {
        $(".loading").show();
        $.ajax({
            url: "automated_workflow/get_user_list",
            type: "GET",
            success: function (response) {
                $(".edit_org_user_div").removeClass("d-none");
                $("select[name='edit_org_user_id']").html(response.html);
                $("select[name='edit_org_user_id']").val(edit_org_user_id);
            },
            error: function () {
                $(".loading").hide();
                alert("Error to Process Your Request! Please try Again!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    }


    if (edit_primary_trigger == 'FormSubmitted' && edit_filter_selection) {
        $(".edit_tenant_form_div").removeClass("d-none");
        $("select[name='edit_tenant_form_id']").val(edit_tenant_form_id);
    }
    if (edit_primary_trigger == 'DocumentUploaded' && edit_filter_selection) {
        $(".edit_client_file_upload_configuration_div").removeClass("d-none");
        $("select[name='edit_client_file_upload_configuration_id']").val(edit_client_file_upload_configuration_id);
    }
    if (edit_primary_trigger == 'SMSReceived' && edit_filter_selection) {
        $(".edit_sms_line_div").removeClass("d-none");
        $("select[name='edit_sms_line']").val(edit_sms_line);
    }

});
