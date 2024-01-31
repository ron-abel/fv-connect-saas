

$(document).ready(function () {
    primary_trigger = '';
    trigger_event = '';
    CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    defineVariable();
    callTinyMce();
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


function defineVariable() {
    filter_selection = 0;
    project_type_id = 0;
    project_type_name = 0;
    phase_name_id = 0;
    phase_name = '';
    filter_contact_by = '';
    person_type_selection_id = 0;
    person_type_selection_name = "";
    filter_task_by = "";
    org_user_id = 0;
    org_user_name = "";
    project_section_selector = "";
    project_section_selector_name = "";
    project_section_field_selector = 0;
    project_section_field_name = "";
    filter_appointment_by = "";
    filter_appointment_by_name = "";
    project_hashtag = "";
    trigger_name = "";
    tenant_form_id = 0;
    tenant_form_name = "";
    client_file_upload_configuration_id = 0;
    client_file_upload_configuration_name = "";
    sms_line = "";
}

$("body").on("change", "select[name='primary_trigger']", async function () {
    primary_trigger = $(this).val();
    $(".trigger_event_div").removeClass("d-none");
    let trigger_event_options = ""
    trigger_event = "Created";
    if (primary_trigger == 'Project') {
        trigger_event_options = '<option value="Created">Created</option>' +
            '<option value="PhaseChanged">Phase Changed</option>' +
            '<option value="AddedHashtag">Project Hashtag Added</option>';
        $(".filter_selection_div").removeClass("d-none");

    } else if (primary_trigger == 'Contact') {
        trigger_event_options = '<option value="Created">Created</option>' +
            '<option value="Updated">Updated</option>';
        $(".filter_selection_div").removeClass("d-none");

    } else if (primary_trigger == 'Note') {
        trigger_event_options = '<option value="Created">Created</option>' +
            '<option value="Completed">Completed</option>' +
            '<option value="TaskflowButtonTrigger">Taskflow Button Trigger</option>';
        $(".filter_selection_div").removeClass("d-none");

    } else if (primary_trigger == 'CollectionItem') {
        trigger_event_options = '<option value="Created">Created</option>' +
            '<option value="Deleted">Deleted</option>';
        $(".filter_selection_div").removeClass("d-none");

    } else if (primary_trigger == 'Appointment') {
        trigger_event_options = '<option value="Created">Created</option>' +
            '<option value="Updated">Updated</option>' +
            '<option value="Deleted">Deleted</option>';
        $(".filter_selection_div").removeClass("d-none");

    } else if (primary_trigger == 'Section') {
        trigger_event_options = '<option value="Visible">Visible</option>' +
            '<option value="Hidden">Hidden</option>';
        $(".filter_selection_div").removeClass("d-none");
        trigger_event = "Visible";

    } else if (primary_trigger == 'ProjectRelation') {
        trigger_event_options = '<option value="Related">Related</option>' +
            '<option value="Unrelated">Unrelated</option>';
        trigger_event = "Related";
        $(".filter_selection_div").addClass("d-none");

    } else if (primary_trigger == 'TeamMessageReply' || primary_trigger == 'DocumentShared') {
        $(".filter_selection_div").addClass("d-none");
        $(".trigger_event_div").addClass("d-none");
        trigger_event = "";

    } else if (primary_trigger == 'DocumentUploaded' || primary_trigger == 'FormSubmitted' || primary_trigger == 'SMSReceived') {
        $(".filter_selection_div").removeClass("d-none");
        $(".trigger_event_div").addClass("d-none");
        trigger_event = "";

    } else if (primary_trigger == 'CalendarFeedback') {
        trigger_event_options = '<option value="Received">Received</option>';
        trigger_event = "Received";
        $(".filter_selection_div").addClass("d-none");

    } else {
        trigger_event_options = '<option value="">Trigger Event</option>';
        trigger_event = "";
    }
    $("select[name='trigger_event']").html(trigger_event_options);
    resetFormItem($(this).attr('name'));
});


$("body").on("change", "select[name='trigger_event']", async function () {
    trigger_event = $(this).val();
    if (primary_trigger == 'Project' && trigger_event == 'Created') {
        $(".filter_selection_div").addClass("d-none");
    } else if (primary_trigger == 'ProjectRelation') {
        $(".filter_selection_div").addClass("d-none");
    } else {
        $(".filter_selection_div").removeClass("d-none");
    }
    resetFormItem($(this).attr('name'));
});

$("body").on("change", "input[name='filter_selection']", async function () {

    if ($(this).prop("checked") == true) {
        filter_selection = 1;
    } else {
        filter_selection = 0;
    }

    if (!filter_selection) {
        resetFormItem($(this).attr('name'));
    }

    if (primary_trigger == 'Project' && filter_selection && (trigger_event == 'PhaseChanged' || trigger_event == 'Created')) {
        $(".project_type_div").removeClass("d-none");

        $(".loading").show();
        $.ajax({
            url: "automated_workflow/get_project_type_list",
            type: "GET",
            success: function (response) {
                $("select[name='project_type_id']").html(response.html);
            },
            error: function () {
                $(".loading").hide();
                alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    }

    if (primary_trigger == 'Project' && trigger_event == 'AddedHashtag' && filter_selection) {
        $(".project_hashtag_div").removeClass("d-none");
    }

    if (primary_trigger == 'Contact' && filter_selection) {
        let filter_options = '<option value="">Select Contact By</option>' +
            '<option value="Person Types">Person Types</option>';
        $(".filter_contact_by_div").removeClass("d-none");
        $("select[name='filter_contact_by']").html(filter_options);
    }

    if (primary_trigger == 'Note' && trigger_event == 'Created' && filter_selection) {
        let filter_options = '<option value="">Select Task By</option>' +
            '<option value="Task Hashtags">Task Hashtags</option>' +
            '<option value="Assigned To">Assigned To</option>' +
            '<option value="Created By">Created By</option>' +
            '<option value="Auto-Generated Task">Auto-Generated Task</option>';
        $(".filter_task_by_div").removeClass("d-none");
        $("select[name='filter_task_by']").html(filter_options);
    }

    if (primary_trigger == 'Note' && trigger_event == 'Completed' && filter_selection) {
        let filter_options = '<option value="">Select Task By</option>' +
            '<option value="Task Hashtags">Task Hashtags</option>' +
            '<option value="Completed By">Completed By</option>' +
            '<option value="Auto-Generated Task">Auto-Generated Task</option>';
        $(".filter_task_by_div").removeClass("d-none");
        $("select[name='filter_task_by']").html(filter_options);
    }

    if ((primary_trigger == 'Note' && trigger_event == 'TaskflowButtonTrigger' && filter_selection)) {

        $(".project_type_div").removeClass("d-none");
        $(".project_section_div").removeClass("d-none");
        $(".project_section_field_div").removeClass("d-none");

        $(".project_section_label").text("Choose Section");

        $(".loading").show();
        $.ajax({
            url: "automated_workflow/get_project_type_list",
            type: "GET",
            success: function (response) {
                $("select[name='project_type_id']").html(response.html);
            },
            error: function () {
                $(".loading").hide();
                alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    }


    if (primary_trigger == 'CollectionItem' && filter_selection) {

        $(".project_type_div").removeClass("d-none");
        $(".project_section_div").removeClass("d-none");

        $(".project_section_label").text("Choose Collection");

        $(".loading").show();
        $.ajax({
            url: "automated_workflow/get_project_type_list",
            type: "GET",
            success: function (response) {
                $("select[name='project_type_id']").html(response.html);
            },
            error: function () {
                $(".loading").hide();
                alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    }

    if (primary_trigger == 'Appointment' && filter_selection) {
        let filter_options = '<option value="">Select Appointment By</option>' +
            '<option value="Note Hashtag">Note Hashtag</option>' +
            '<option value="All Day Appointment">All Day Appointment</option>' +
            '<option value="Attendee">Attendee</option>';
        $(".filter_appointment_by_div").removeClass("d-none");
        $("select[name='filter_appointment_by']").html(filter_options);
    }

    if (primary_trigger == 'Section' && filter_selection) {
        $(".project_type_div").removeClass("d-none");
        $(".project_section_div").removeClass("d-none");
        $(".loading").show();
        $.ajax({
            url: "automated_workflow/get_project_type_list",
            type: "GET",
            success: function (response) {
                $("select[name='project_type_id']").html(response.html);
            },
            error: function () {
                $(".loading").hide();
                alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    }

    if (primary_trigger == 'FormSubmitted' && filter_selection) {
        $(".tenant_form_div").removeClass("d-none");
    }
    if (primary_trigger == 'DocumentUploaded' && filter_selection) {
        $(".client_file_upload_configuration_div").removeClass("d-none");
    }
    if (primary_trigger == 'SMSReceived' && filter_selection) {
        $(".sms_line_div").removeClass("d-none");
    }

});


$("body").on("change", "select[name='project_type_id']", async function () {
    project_type_id = $(this).val();
    project_type_name = $(this).find("option:selected").text();
    if (primary_trigger == 'Project' && trigger_event == 'PhaseChanged' && filter_selection) {
        $(".loading").show();
        $.ajax({
            url: "automated_workflow/get_phase_list",
            type: "GET",
            data: { project_type_id: project_type_id },
            success: function (response) {
                $(".phase_name_div").removeClass("d-none");
                $("select[name='phase_name_id']").html(response.html);
            },
            error: function () {
                $(".loading").hide();
                alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    }

    if (primary_trigger == 'Note' && trigger_event == 'TaskflowButtonTrigger' && filter_selection) {

        $(".loading").show();
        $.ajax({
            url: "automated_workflow/get_project_type_section_list",
            type: "GET",
            data: { project_type_id: project_type_id },
            success: function (response) {
                $(".project_section_div").removeClass("d-none");
                $("select[name='project_section_selector']").html(response.html);
            },
            error: function () {
                $(".loading").hide();
                alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    }

    if (primary_trigger == 'CollectionItem' && filter_selection) {

        $(".loading").show();
        $.ajax({
            url: "automated_workflow/get_project_type_collection_list",
            type: "GET",
            data: { project_type_id: project_type_id },
            success: function (response) {
                $(".project_section_div").removeClass("d-none");
                $("select[name='project_section_selector']").html(response.html);
            },
            error: function () {
                $(".loading").hide();
                alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    }

    if (primary_trigger == 'Section' && filter_selection) {

        $(".loading").show();
        $.ajax({
            url: "automated_workflow/get_project_type_section_list",
            type: "GET",
            data: { project_type_id: project_type_id },
            success: function (response) {
                $(".project_section_div").removeClass("d-none");
                $("select[name='project_section_selector']").html(response.html);
            },
            error: function () {
                $(".loading").hide();
                alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    }


});

$("body").on("change", "select[name='project_section_selector']", async function () {
    project_section_selector = $(this).val();
    project_section_selector_name = $(this).find("option:selected").text();
    if (primary_trigger == 'Note' && trigger_event == 'TaskflowButtonTrigger' && filter_selection) {

        $(".loading").show();
        $.ajax({
            url: "automated_workflow/get_project_section_field",
            type: "GET",
            data: {
                project_type_id: project_type_id,
                project_section_selector: project_section_selector
            },
            success: function (response) {
                $(".project_section_field_div").removeClass("d-none");
                $("select[name='project_section_field_selector']").html(response.html);
            },
            error: function () {
                $(".loading").hide();
                alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    }
});

$("body").on("change", "select[name='project_section_field_selector']", async function () {
    project_section_field_selector = $(this).val();
    project_section_field_name = $(this).find("option:selected").text();
});

$("body").on("change", "select[name='phase_name_id']", async function () {
    phase_name_id = $(this).val();
    phase_name = $(this).find("option:selected").text();
});

$("body").on("change", "select[name='filter_contact_by']", async function () {
    filter_contact_by = $(this).val();
    if (primary_trigger == 'Contact' && filter_contact_by == 'Person Types' && filter_selection) {
        $(".loading").show();
        $.ajax({
            url: "automated_workflow/get_contact_metadata",
            type: "GET",
            success: function (response) {
                $(".person_type_selection_div").removeClass("d-none");
                $("select[name='person_type_selection_id']").html(response.html);
            },
            error: function () {
                $(".loading").hide();
                alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    }
});

$("body").on("change", "select[name='person_type_selection_id']", async function () {
    person_type_selection_id = $(this).val();
    person_type_selection_name = $(this).find("option:selected").text();
});

$("body").on("change", "select[name='filter_task_by']", async function () {
    filter_task_by = $(this).val();
    resetFormItem($(this).attr('name'));
    if (filter_task_by == 'Task Hashtags' || filter_task_by == 'Auto-Generated Task') {
        $(".project_hashtag_div").removeClass("d-none");
        $(".hastag_label").text("Contains Hashtag");
    }
    if (filter_task_by == 'Assigned To' || filter_task_by == 'Created By' || filter_task_by == 'Completed By') {
        $(".loading").show();
        $.ajax({
            url: "automated_workflow/get_user_list",
            type: "GET",
            success: function (response) {
                $(".org_user_div").removeClass("d-none");
                $("select[name='org_user_id']").html(response.html);
            },
            error: function () {
                $(".loading").hide();
                alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    }

});

$("body").on("change", "select[name='org_user_id']", async function () {
    org_user_id = $(this).val();
    org_user_name = $(this).find("option:selected").text();
});

$("body").on("change", "select[name='tenant_form_id']", async function () {
    tenant_form_id = $(this).val();
    tenant_form_name = $(this).find("option:selected").text();
});
$("body").on("change", "select[name='client_file_upload_configuration_id']", async function () {
    client_file_upload_configuration_id = $(this).val();
    client_file_upload_configuration_name = $(this).find("option:selected").text();
});
$("body").on("change", "select[name='sms_line']", async function () {
    sms_line = $(this).val();
});

$("body").on("change", "select[name='filter_appointment_by']", async function () {
    resetFormItem($(this).attr('name'));
    filter_appointment_by = $(this).val();
    filter_appointment_by_name = $(this).find("option:selected").text();
    if (filter_appointment_by == 'Note Hashtag') {
        $(".project_hashtag_div").removeClass("d-none");
        $(".hastag_label").text("Contains Hashtag");
    }

    if (filter_appointment_by == 'Attendee') {
        $(".loading").show();
        $.ajax({
            url: "automated_workflow/get_user_list",
            type: "GET",
            success: function (response) {
                $(".org_user_div").removeClass("d-none");
                $("select[name='org_user_id']").html(response.html);
            },
            error: function () {
                $(".loading").hide();
                alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    }

});


function resetFormItem(item_name) {
    if (item_name == 'primary_trigger' || item_name == 'trigger_event' || item_name == 'filter_selection') {
        $(".project_type_div").addClass("d-none");
        $(".phase_name_div").addClass("d-none");
        $(".filter_contact_by_div").addClass("d-none");
        $(".person_type_selection_div").addClass("d-none");
        $(".project_section_div").addClass("d-none");
        $(".project_section_field_div").addClass("d-none");
        $(".filter_task_by_div").addClass("d-none");
        $(".filter_appointment_by_div").addClass("d-none");
        $(".org_user_div").addClass("d-none");
        $(".project_hashtag_div").addClass("d-none");
        $("input[name='filter_selection']:checkbox").prop('checked', false);
        $(".tenant_form_div").addClass("d-none");
        $(".client_file_upload_configuration_div").addClass("d-none");
        $(".sms_line_div").addClass("d-none");
        defineVariable();
    }

    if (item_name == 'filter_task_by' || item_name == 'filter_appointment_by') {
        $(".project_hashtag_div").addClass("d-none");
        $(".org_user_div").addClass("d-none");
        project_hashtag = "";
        org_user_id = 0;
        org_user_name = "";
        filter_appointment_by = "";
        filter_appointment_by_name = "";
    }
}



$(".save-trigger").click(function () {

    let trigger_name = $("input[name='trigger_name']").val();
    let project_hashtag = $("input[name='project_hashtag']").val();

    let trigger_data = {
        _token: CSRF_TOKEN,
        primary_trigger: primary_trigger,
        trigger_event: trigger_event,
        trigger_name: trigger_name,
        filter_selection: filter_selection,
        project_type_id: project_type_id,
        project_type_name: project_type_name,
        phase_name_id: phase_name_id,
        phase_name: phase_name,
        filter_contact_by: filter_contact_by,
        person_type_selection_id: person_type_selection_id,
        person_type_selection_name: person_type_selection_name,
        filter_task_by: filter_task_by,
        org_user_id: org_user_id,
        org_user_name: org_user_name,
        project_section_selector: project_section_selector,
        project_section_selector_name: project_section_selector_name,
        project_section_field_selector: project_section_field_selector,
        project_section_field_name: project_section_field_name,
        filter_appointment_by: filter_appointment_by,
        filter_appointment_by_name: filter_appointment_by_name,
        project_hashtag: project_hashtag,
        tenant_form_id: tenant_form_id,
        tenant_form_name: tenant_form_name,
        client_file_upload_configuration_id: client_file_upload_configuration_id,
        client_file_upload_configuration_name: client_file_upload_configuration_name,
        sms_line: sms_line
    };

    $(".loading").show();
    $.ajax({
        url: "automated_workflow/save",
        type: "POST",
        data: trigger_data,
        dataType: 'JSON',
        success: function (response) {
            if (response.status) {
                Swal.fire({
                    text: response.message,
                    icon: "success",
                }).then(function () {
                    location.reload();
                });
            } else {
                Swal.fire({
                    text: response.message,
                    icon: "error",
                });
            }
        },
        error: function () {
            $(".loading").hide();
            alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
        },
    }).done(function () {
        $(".loading").hide();
    });

});


$("body").on("click", "button.remove", async function () {
    let trigger_id = $(this).attr('data-id');
    let is_used = $(this).attr('data-used');
    let alert_message = (is_used == '1') ? 'This trigger already is in used. Are you sure want to delete?' : 'Are you sure want to delete?';
    Swal.fire({
        title: alert_message,
        showDenyButton: true,
        confirmButtonText: 'Yes',
        denyButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: "post",
                url: "automated_workflow/delete",
                data: {
                    _token: CSRF_TOKEN,
                    trigger_id: trigger_id
                },
                success: function (response) {
                    Swal.fire({
                        text: response.message,
                        icon: "success",
                    }).then(function () {
                        location.reload();
                    });
                },
            });
        }
    });
});


$("body").on("click", "button.test", async function () {
    let trigger_id = $(this).attr('data-id');
    Swal.fire({
        title: 'Are you sure want to change test mode status?',
        showDenyButton: true,
        confirmButtonText: 'Yes',
        denyButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: "post",
                url: "automated_workflow/update_status",
                data: {
                    _token: CSRF_TOKEN,
                    trigger_id: trigger_id,
                    is_test_click: true
                },
                success: function (response) {
                    Swal.fire({
                        text: response.message,
                        icon: "success",
                    }).then(function () {
                        location.reload();
                    });
                },
            });
        }
    });
});


$("body").on("click", "button.live", async function () {
    let trigger_id = $(this).attr('data-id');
    Swal.fire({
        title: 'Are you sure want to change live mode status?',
        showDenyButton: true,
        confirmButtonText: 'Yes',
        denyButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: "post",
                url: "automated_workflow/update_status",
                data: {
                    _token: CSRF_TOKEN,
                    trigger_id: trigger_id,
                    is_live_click: true
                },
                success: function (response) {
                    Swal.fire({
                        text: response.message,
                        icon: "success",
                    }).then(function () {
                        location.reload();
                    });
                },
            });
        }
    });
});


function loadLogTable() {
    var log_start_date = moment();
    var log_end_date = moment();

    function cb(start, end) {
        $('#logreportrange span').html(start.format('MM/D/YYYY') + ' - ' + end.format('MM/D/YYYY'));

        // let export_href = $(".export-custom-log").attr("href");
        // export_href = export_href.split("?")[0] + '?log_start_date=' + start.format('YYYY-MM-DD') + '&log_end_date=' + end.format('YYYY-MM-DD');
        // $(".export-custom-log").attr("href", export_href);

        $('#kt_datatable_custom_logs').DataTable({
            responsive: true,
            bDestroy: true,
            order: [[4, 'desc']],
            ajax: {
                url: 'automated_workflow/logs',
                method: 'get',
                data: {
                    'log_start_date': start.format('YYYY-MM-DD'),
                    'log_end_date': end.format('YYYY-MM-DD'),
                },
            },
            columns: [
                {
                    data: 'trigger_id',
                },
                {
                    data: 'map_ids',
                },
                {
                    data: 'action_ids',
                },
                {
                    data: 'ProjectId',
                },
                {
                    data: 'map_workflow_description',
                },
                {
                    data: 'is_handled',
                },
                {
                    data: 'updated_at',
                },
                {
                    data: 'webhook_request_json',
                }
            ],
            columnDefs: [
                {
                    targets: -3,
                    render: function (data, type, full, meta) {
                        return data ? '<i class="fa fa-check" style="color:#1bc5bd;"></i>' : '';
                    },
                },
                {
                    targets: -1,
                    render: function (data, type, full, meta) {
                        return '<button type="button" data-toggle="modal" data-json="' + encodeURIComponent(data) + '" data-target="#logDetails" class="btn btn-hover-bg-primary show-log-details">Trigger Details</button>' +
                            '<button type="button" data-toggle="modal" data-json="' + encodeURIComponent(full.log_details) + '" data-target="#actionLogDetails" class="btn btn-hover-bg-primary action-log-details">Actions Details</button>';
                    },
                },
            ]
        });
    }

    $('#logreportrange').daterangepicker({
        startDate: log_start_date,
        endDate: log_end_date,
        ranges: {
            'Today': [moment(), moment()],
            'This Week': [moment().startOf('isoWeek'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
        }
    }, cb);
    cb(log_start_date, log_end_date);
}

$("body").on("click", ".show-log-details", async function () {
    let json_details = JSON.stringify(JSON.parse(decodeURIComponent($(this).data('json'))), null, 2);
    $('.json-details').text(json_details);
});

$("body").on("click", ".action-log-details", async function () {
    let json_details = JSON.parse(decodeURIComponent($(this).data('json')));
    let table_body = "";
    json_details.forEach(element => {
        table_body += "<tr>";
        table_body += "<td>" + element.action_id + "</td>";
        table_body += "<td>" + element.fv_project_id + "</td>";
        table_body += "<td>" + element.fv_client_id + "</td>";
        table_body += "<td>" + element.emails + "</td>";
        table_body += "<td>" + element.sms_phones + "</td>";
        table_body += "<td>" + element.note_body + "</td>";
        table_body += "</tr>";
    });
    $(".action_log_table_body").html(table_body);
});

$("body").on("change", "input.action-status", async function () {
    let action_id = $(this).val();
    $(".loading").show();
    $.ajax({
        type: "post",
        url: "automated_workflow/action/update_status",
        data: {
            _token: CSRF_TOKEN,
            action_id: action_id
        },
        success: function (response) {
            //location.reload();
        },
    }).done(function () {
        $(".loading").hide();
    });
});

$("body").on("click", "button.action-all-status", async function () {
    Swal.fire({
        title: 'Are you sure want to change all action status into active?',
        showDenyButton: true,
        confirmButtonText: 'Yes',
        denyButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: "post",
                url: "automated_workflow/action/update_status",
                data: {
                    _token: CSRF_TOKEN,
                    action_change: 'all'
                },
                success: function (response) {
                    Swal.fire({
                        text: response.message,
                        icon: "success",
                    }).then(function () {
                        location.reload();
                    });
                },
            });
        }
    });
});


/* $("body").on("click", "button.action-edit", async function () {
    let json_details = ($(this).data('json'));
    $('input[name="action_id"]').val(json_details.id);
    $('input[name="action_name"]').val(json_details.action_name);
    $('input[name="action_description"]').val(json_details.action_description);
    $('input[name="is_active"]').prop("checked", json_details.is_active);
}); */

$("body").on("change", "select[name='initial_action_id']", async function () {
    let action_id_str = $(this).val();
    let action_short_code = action_id_str.split("-")[1];

    $("textarea[name='client_sms_body']").prop('required', false);
    $("select[name='fv_project_task_assign_user_id']").prop('required', false);
    $("input[name='fv_project_hashtag']").prop('required', false);

    $(".client_sms_body_div").addClass("d-none");
    $(".client_tiny_sms_body_div").addClass("d-none");
    //$(".fv_project_note_with_pin_div").addClass("d-none");
    $(".fv_project_hashtag_div").addClass("d-none");
    $(".fv_project_task_assign_type_div").addClass("d-none");
    $(".fv_project_task_assign_user_div").addClass("d-none");
    $(".fv_project_task_assign_user_role_div").addClass("d-none");

    $("select[name='section_visibility_project_type_id']").prop('required', false);
    $("select[name='section_visibility_section_selector']").prop('required', false);
    $("select[name='section_visibility']").prop('required', false);
    $(".section_visibility_project_type_div").addClass("d-none");
    $(".section_visibility_section_selector_div").addClass("d-none");
    $(".section_visibility_div").addClass("d-none");

    $("select[name='phase_assignment']").prop('required', false);
    $("select[name='phase_assignment_project_type_id']").prop('required', false);
    $("select[name='project_phase_id_native']").prop('required', false);
    $(".phase_assignment_div").addClass("d-none");
    $(".phase_assignment_project_type_div").addClass("d-none");
    $(".project_phase_id_native_div").addClass("d-none");

    $(".delivery_hook_url_div").addClass("d-none");
    $("input[name='delivery_hook_url']").prop('required', false);

    $(".send_sms_choice_div").addClass("d-none");
    $(".person_field_project_type_div").addClass("d-none");
    $(".person_field_project_type_section_selector_div").addClass("d-none");
    $(".person_field_project_type_section_field_selector_div").addClass("d-none");
    $("select[name='send_sms_choice']").prop('required', false);
    $("select[name='person_field_project_type_id']").prop('required', false);
    $("select[name='person_field_project_type_section_selector']").prop('required', false);
    $("select[name='person_field_project_type_section_field_selector']").prop('required', false);

    $(".mirror_div").addClass("d-none");
    $(".mirror-select-item").prop('required', false);

    $(".project_team_choice_div").addClass("d-none");
    $("select[name='project_team_choice']").prop('required', false);
    $(".team_member_user_div").addClass("d-none");
    $("select[name='team_member_user_id']").prop('required', false);
    $(".add_team_member_choice_div").addClass("d-none");
    $("select[name='add_team_member_choice']").prop('required', false);
    $(".add_team_member_choice_level_div").addClass("d-none");
    $("select[name='add_team_member_choice_level']").prop('required', false);


    if (action_short_code == '1') {
        $(".client_sms_body_div").removeClass("d-none");
        $("textarea[name='client_sms_body']").prop('required', true);
        $(".send_sms_choice_div").removeClass("d-none");
        $("select[name='send_sms_choice']").prop('required', true);
    } else if (action_short_code == '5') {
        $(".client_sms_body_div").removeClass("d-none");
        $("textarea[name='client_sms_body']").prop('required', true);
    } else if (action_short_code == '3') {
        $(".client_sms_body_div").removeClass("d-none");
        // $(".fv_project_note_with_pin_div").removeClass("d-none");
        $("textarea[name='client_sms_body']").prop('required', true);
    } else if (action_short_code == '4') {
        $(".client_sms_body_div").removeClass("d-none");
        $("textarea[name='client_sms_body']").prop('required', true);
        $(".fv_project_task_assign_type_div").removeClass("d-none");
    } else if (action_short_code == '6' || action_short_code == '7') {
        $(".fv_project_hashtag_div").removeClass("d-none");
        $("input[name='fv_project_hashtag']").prop('required', true);
    } else if (action_short_code == '8') {
        $(".section_visibility_project_type_div").removeClass("d-none");
        $(".section_visibility_section_selector_div").removeClass("d-none");
        $(".section_visibility_div").removeClass("d-none");
        $("select[name='section_visibility_project_type_id']").prop('required', true);
        $("select[name='section_visibility_section_selector']").prop('required', true);
        $("select[name='section_visibility']").prop('required', true);

        $(".loading").show();
        $.ajax({
            url: "automated_workflow/get_project_type_list",
            type: "GET",
            success: function (response) {
                $("select[name='section_visibility_project_type_id']").html(response.html);
            },
            error: function () {
                $(".loading").hide();
                alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    } else if (action_short_code == '10') {
        $(".phase_assignment_div").removeClass("d-none");
        $("select[name='phase_assignment']").prop('required', true);
    } else if (action_short_code == '11') {
        $(".delivery_hook_url_div").removeClass("d-none");
        $("input[name='delivery_hook_url']").prop('required', true);
    } else if (action_short_code == '12') {
        $(".client_tiny_sms_body_div").removeClass("d-none");
        $("textarea[name='client_tiny_sms_body']").prop('required', true);
    } else if (action_short_code == '13') {
        $(".mirror_div").removeClass("d-none");
        $(".mirror-select-item").prop('required', true);

        $(".loading").show();
        $.ajax({
            url: "automated_workflow/get_project_type_list",
            type: "GET",
            success: function (response) {
                $("select[name='mirror_from_field_project_type_id']").html(response.html);
                //$("select[name='mirror_to_field_project_type_id']").html(response.html);
            },
            error: function () {
                $(".loading").hide();
                alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    } else if (action_short_code == '14') {
        $(".project_team_choice_div").removeClass("d-none");
        $("select[name='project_team_choice']").prop('required', true);
        $(".team_member_user_div").removeClass("d-none");
        $("select[name='team_member_user_id']").prop('required', true);
        $(".loading").show();
        $.ajax({
            url: "automated_workflow/get_user_list",
            type: "GET",
            success: function (response) {
                $("select[name='team_member_user_id']").html(response.html);
            },
            error: function () {
                $(".loading").hide();
                alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    }
});

$("body").on("change", "select[name='project_team_choice']", async function () {
    let project_team_choice = $(this).val();
    if (project_team_choice == 'Add a Team Member') {
        $(".add_team_member_choice_div").removeClass("d-none");
    } else {
        $(".add_team_member_choice_div").addClass("d-none");
        $(".add_team_member_choice_level_div").addClass("d-none");
    }
});

$("body").on("change", "select[name='add_team_member_choice']", async function () {
    let add_team_member_choice = $(this).val();
    if (add_team_member_choice == 'Level') {
        $(".add_team_member_choice_level_div").removeClass("d-none");
    } else {
        $(".add_team_member_choice_level_div").addClass("d-none");
    }
});

$("body").on("change", "select[name='team_member_user_id']", async function () {
    $('input[name="team_member_user_name"]').val($(this).find("option:selected").text());
});


$("body").on("change", "select[name='send_sms_choice']", async function () {
    let send_sms_choice = $(this).val();

    $(".person_field_project_type_div").addClass("d-none");
    $(".person_field_project_type_section_selector_div").addClass("d-none");
    $(".person_field_project_type_section_field_selector_div").addClass("d-none");
    $("select[name='person_field_project_type_id']").prop('required', false);
    $("select[name='person_field_project_type_section_selector']").prop('required', false);
    $("select[name='person_field_project_type_section_field_selector']").prop('required', false);

    if (send_sms_choice == 'To Person Field') {
        $(".person_field_project_type_div").removeClass("d-none");
        $(".person_field_project_type_section_selector_div").removeClass("d-none");
        $(".person_field_project_type_section_field_selector_div").removeClass("d-none");
        $("select[name='person_field_project_type_id']").prop('required', true);
        $("select[name='person_field_project_type_section_selector']").prop('required', true);
        $("select[name='person_field_project_type_section_field_selector']").prop('required', true);
        $(".loading").show();
        $.ajax({
            url: "automated_workflow/get_project_type_list",
            type: "GET",
            success: function (response) {
                $("select[name='person_field_project_type_id']").html(response.html);
            },
            error: function () {
                $(".loading").hide();
                alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    }
});

$("body").on("change", "select[name='person_field_project_type_id']", async function () {
    let person_field_project_type_id = $(this).val();
    $('input[name="person_field_project_type_name"]').val($(this).find("option:selected").text());

    $(".loading").show();
    $.ajax({
        url: "automated_workflow/get_project_type_section_list",
        type: "GET",
        data: { project_type_id: person_field_project_type_id, is_collection: 'static' },
        success: function (response) {
            $("select[name='person_field_project_type_section_selector']").html(response.html);
        },
        error: function () {
            $(".loading").hide();
            alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
        },
    }).done(function () {
        $(".loading").hide();
    });
});

$("body").on("change", "select[name='person_field_project_type_section_selector']", async function () {
    let person_field_project_type_section_selector = $(this).val();
    $('input[name="person_field_project_type_section_selector_name"]').val($(this).find("option:selected").text());

    $(".loading").show();
    $.ajax({
        url: "automated_workflow/get_project_section_field",
        type: "GET",
        data: {
            project_type_id: $("select[name='person_field_project_type_id']").val(),
            project_section_selector: person_field_project_type_section_selector,
            custom_field_types: 'PersonLink'
        },
        success: function (response) {
            $("select[name='person_field_project_type_section_field_selector']").html(response.html);
        },
        error: function () {
            $(".loading").hide();
            alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
        },
    }).done(function () {
        $(".loading").hide();
    });
});

$("body").on("change", "select[name='person_field_project_type_section_field_selector']", async function () {
    $('input[name="person_field_project_type_section_field_selector_name"]').val($(this).find("option:selected").text());
});


/* Mirror from/to Field JS */
$("body").on("change", "select[name='mirror_from_field_project_type_id']", async function () {
    let mirror_from_field_project_type_id = $(this).val();
    let mirror_from_field_project_type_text = $(this).find("option:selected").text();
    $('input[name="mirror_from_field_project_type_name"]').val(mirror_from_field_project_type_text);

    $("select[name='mirror_to_field_project_type_id']").find('option').not(':first').remove();
    $("select[name='mirror_to_field_project_type_id']").append($('<option>', {
        value: mirror_from_field_project_type_id,
        text: mirror_from_field_project_type_text
    }));

    $(".loading").show();
    $.ajax({
        url: "automated_workflow/get_project_type_section_list",
        type: "GET",
        data: { project_type_id: mirror_from_field_project_type_id, is_collection: 'static' },
        success: function (response) {
            $("select[name='mirror_from_field_project_type_section_selector']").html(response.html);
        },
        error: function () {
            $(".loading").hide();
            alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
        },
    }).done(function () {
        $(".loading").hide();
    });
});

$("body").on("change", "select[name='mirror_from_field_project_type_section_selector']", async function () {
    let mirror_from_field_project_type_section_selector = $(this).val();
    $('input[name="mirror_from_field_project_type_section_selector_name"]').val($(this).find("option:selected").text());

    $(".loading").show();
    $.ajax({
        url: "automated_workflow/get_project_section_field",
        type: "GET",
        data: {
            project_type_id: $("select[name='mirror_from_field_project_type_id']").val(),
            project_section_selector: mirror_from_field_project_type_section_selector,
            custom_field_types: 'mirror'
        },
        success: function (response) {
            $("select[name='mirror_from_field_project_type_section_field_selector']").html(response.html);
        },
        error: function () {
            $(".loading").hide();
            alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
        },
    }).done(function () {
        $(".loading").hide();
    });
});

$("body").on("change", "select[name='mirror_from_field_project_type_section_field_selector']", async function () {
    $('input[name="mirror_from_field_project_type_section_field_selector_name"]').val($(this).find("option:selected").text());
});

$("body").on("change", "select[name='mirror_to_field_project_type_id']", async function () {
    let mirror_to_field_project_type_id = $(this).val();
    $('input[name="mirror_to_field_project_type_name"]').val($(this).find("option:selected").text());

    $(".loading").show();
    $.ajax({
        url: "automated_workflow/get_project_type_section_list",
        type: "GET",
        data: { project_type_id: mirror_to_field_project_type_id, is_collection: 'static' },
        success: function (response) {
            $("select[name='mirror_to_field_project_type_section_selector']").html(response.html);
        },
        error: function () {
            $(".loading").hide();
            alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
        },
    }).done(function () {
        $(".loading").hide();
    });
});

$("body").on("change", "select[name='mirror_to_field_project_type_section_selector']", async function () {
    let mirror_to_field_project_type_section_selector = $(this).val();
    $('input[name="mirror_to_field_project_type_section_selector_name"]').val($(this).find("option:selected").text());

    $(".loading").show();
    $.ajax({
        url: "automated_workflow/get_project_section_field",
        type: "GET",
        data: {
            project_type_id: $("select[name='mirror_to_field_project_type_id']").val(),
            project_section_selector: mirror_to_field_project_type_section_selector,
            custom_field_types: 'mirror'
        },
        success: function (response) {
            $("select[name='mirror_to_field_project_type_section_field_selector']").html(response.html);
        },
        error: function () {
            $(".loading").hide();
            alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
        },
    }).done(function () {
        $(".loading").hide();
    });
});

$("body").on("change", "select[name='mirror_to_field_project_type_section_field_selector']", async function () {
    $('input[name="mirror_to_field_project_type_section_field_selector_name"]').val($(this).find("option:selected").text());
});





$("body").on("change", "select[name='phase_assignment']", async function () {
    let phase_assignment = $(this).val();

    $("select[name='phase_assignment_project_type_id']").prop('required', false);
    $("select[name='project_phase_id_native']").prop('required', false);
    $(".phase_assignment_project_type_div").addClass("d-none");
    $(".project_phase_id_native_div").addClass("d-none");

    if (phase_assignment == 'Specific_Phase') {
        $(".phase_assignment_project_type_div").removeClass("d-none");
        $(".project_phase_id_native_div").removeClass("d-none");
        $("select[name='phase_assignment_project_type_id']").prop('required', true);
        $("select[name='project_phase_id_native']").prop('required', true);
        $(".loading").show();
        $.ajax({
            url: "automated_workflow/get_project_type_list",
            type: "GET",
            success: function (response) {
                $("select[name='phase_assignment_project_type_id']").html(response.html);
            },
            error: function () {
                $(".loading").hide();
                alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    }
});

$("body").on("change", "select[name='phase_assignment_project_type_id']", async function () {
    let phase_assignment_project_type_id = $(this).val();
    $('input[name="phase_assignment_project_type_name"]').val($(this).find("option:selected").text());
    $(".loading").show();
    $.ajax({
        url: "automated_workflow/get_phase_list",
        data: { project_type_id: phase_assignment_project_type_id },
        type: "GET",
        success: function (response) {
            $("select[name='project_phase_id_native']").html(response.html);
        },
        error: function () {
            $(".loading").hide();
            alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
        },
    }).done(function () {
        $(".loading").hide();
    });
});



$("body").on("change", "select[name='section_visibility_project_type_id']", async function () {
    let section_visibility_project_type_id = $(this).val();
    $(".loading").show();
    $.ajax({
        url: "automated_workflow/get_project_type_section_list",
        type: "GET",
        data: { project_type_id: section_visibility_project_type_id },
        success: function (response) {
            $("select[name='section_visibility_section_selector']").html(response.html);
        },
        error: function () {
            $(".loading").hide();
            alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
        },
    }).done(function () {
        $(".loading").hide();
    });
});


$("body").on("change", "select[name='fv_project_task_assign_user_id']", async function () {
    $('input[name="fv_project_task_assign_user_name"]').val($(this).find("option:selected").text());
});

$("body").on("change", "select[name='fv_project_task_assign_type']", async function () {
    let fv_project_task_assign_type = $(this).val();

    $(".fv_project_task_assign_user_div").addClass("d-none");
    $("select[name='fv_project_task_assign_user_id']").prop('required', false);
    $(".fv_project_task_assign_user_role_div").addClass("d-none");
    $("select[name='fv_project_task_assign_user_role']").prop('required', false);

    if (fv_project_task_assign_type == 'role') {
        $(".fv_project_task_assign_user_role_div").removeClass("d-none");
        $("select[name='fv_project_task_assign_user_role']").prop('required', true);
        $(".loading").show();
        $.ajax({
            url: "automated_workflow/get_role_list",
            type: "GET",
            success: function (response) {
                $("select[name='fv_project_task_assign_user_role']").html(response.html);
            },
            error: function () {
                $(".loading").hide();
                alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    } else {
        $(".fv_project_task_assign_user_div").removeClass("d-none");
        $("select[name='fv_project_task_assign_user_id']").prop('required', true);
        $(".loading").show();
        $.ajax({
            url: "automated_workflow/get_user_list",
            type: "GET",
            success: function (response) {
                $("select[name='fv_project_task_assign_user_id']").html(response.html);
            },
            error: function () {
                $(".loading").hide();
                alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    }
});

$("body").on("change", "select[name='fv_project_task_assign_user_role']", async function () {
    $('input[name="fv_project_task_assign_user_role_name"]').val($(this).find("option:selected").text());
});

$("body").on("click", "button.remove-action", async function () {
    let action_id = $(this).attr('data-id');
    let is_used = $(this).attr('data-used');
    let alert_message = is_used ? 'This action already is in used. Are you sure want to delete?' : 'Are you sure want to delete?';
    Swal.fire({
        title: alert_message,
        showDenyButton: true,
        confirmButtonText: 'Yes',
        denyButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: "post",
                url: "automated_workflow/map/delete",
                data: {
                    _token: CSRF_TOKEN,
                    action_id: action_id
                },
                success: function (response) {
                    Swal.fire({
                        text: response.message,
                        icon: "success",
                    }).then(function () {
                        location.reload();
                    });
                },
            });
        }
    });
});

$("body").on("click", "button.remove-map", async function () {
    let map_id = $(this).attr('data-id');
    Swal.fire({
        title: 'Are you sure want to delete?',
        showDenyButton: true,
        confirmButtonText: 'Yes',
        denyButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: "post",
                url: "automated_workflow/map/delete",
                data: {
                    _token: CSRF_TOKEN,
                    map_id: map_id,
                    only_map: true
                },
                success: function (response) {
                    Swal.fire({
                        text: response.message,
                        icon: "success",
                    }).then(function () {
                        location.reload();
                    });
                },
            });
        }
    });
});


$("body").on("click", "button.action-edit", async function () {
    let json_details = $(this).data('json');
    $('.action_edit_id').text('(ID# ' + json_details.id + ')');
    $('input[name="update_map_action_id"]').val(json_details.id);
    $('input[name="update_map_action_name"]').val(json_details.action_name);
    $('input[name="update_map_action_description"]').val(json_details.action_description);
    $("textarea[name='update_map_action_note']").val(json_details.note);
    $('select[name="update_map_status"]').val(json_details.status);

    $(".update_client_sms_body_div").addClass("d-none");
    //$(".update_fv_project_note_with_pin_div").addClass("d-none");
    $(".update_fv_project_task_assign_user_div").addClass("d-none");
    $(".update_fv_project_task_assign_type_div").addClass("d-none");
    $(".update_fv_project_task_assign_user_role_div").addClass("d-none");
    $(".update_fv_project_hashtag_div").addClass("d-none");
    $("textarea[name='update_client_sms_body']").prop('required', false);
    $("select[name='update_fv_project_task_assign_user_id']").prop('required', false);
    $("select[name='update_fv_project_task_assign_type']").prop('required', false);
    $("select[name='update_fv_project_task_assign_user_role']").prop('required', false);
    $("input[name='update_fv_project_hashtag']").prop('required', false);

    $("select[name='update_section_visibility_project_type_id']").prop('required', false);
    $("select[name='update_section_visibility_section_selector']").prop('required', false);
    $("select[name='update_section_visibility']").prop('required', false);
    $(".update_section_visibility_project_type_div").addClass("d-none");
    $(".update_section_visibility_section_selector_div").addClass("d-none");
    $(".update_section_visibility_div").addClass("d-none");

    $("select[name='update_phase_assignment']").prop('required', false);
    $("select[name='update_phase_assignment_project_type_id']").prop('required', false);
    $("select[name='update_project_phase_id_native']").prop('required', false);
    $(".update_phase_assignment_div").addClass("d-none");
    $(".update_phase_assignment_project_type_div").addClass("d-none");
    $(".update_project_phase_id_native_div").addClass("d-none");

    $(".update_delivery_hook_url_div").addClass("d-none");
    $("input[name='update_delivery_hook_url']").prop('required', false);

    let action_short_code = json_details.action_short_code;
    $('input[name="update_map_action_short_code"]').val(action_short_code);

    $(".update_send_sms_choice_div").addClass("d-none");
    $(".update_person_field_project_type_div").addClass("d-none");
    $(".update_person_field_project_type_section_selector_div").addClass("d-none");
    $(".update_person_field_project_type_section_field_selector_div").addClass("d-none");
    $("select[name='update_send_sms_choice']").prop('required', false);
    $("select[name='update_person_field_project_type_id']").prop('required', false);
    $("select[name='update_person_field_project_type_section_selector']").prop('required', false);
    $("select[name='update_person_field_project_type_section_field_selector']").prop('required', false);

    $(".update_mirror_div").addClass("d-none");
    $(".update-mirror-select-item").prop('required', false);

    $(".update_project_team_choice_div").addClass("d-none");
    $("select[name='update_project_team_choice']").prop('required', false);
    $(".update_team_member_user_div").addClass("d-none");
    $("select[name='update_team_member_user_id']").prop('required', false);
    $(".update_add_team_member_choice_div").addClass("d-none");
    $("select[name='update_add_team_member_choice']").prop('required', false);
    $(".update_add_team_member_choice_level_div").addClass("d-none");
    $("select[name='update_add_team_member_choice_level']").prop('required', false);


    if (action_short_code == '1') {
        $(".update_client_sms_body_div").removeClass("d-none");
        $("textarea[name='update_client_sms_body']").prop('required', true);
        $("textarea[name='update_client_sms_body']").val(json_details.client_sms_body);
        $(".update_send_sms_choice_div").removeClass("d-none");
        $("select[name='update_send_sms_choice']").prop('required', true);
        $("select[name='update_send_sms_choice']").val(json_details.send_sms_choice);
        if (json_details.send_sms_choice == 'To Person Field') {
            $(".update_person_field_project_type_div").removeClass("d-none");
            $(".update_person_field_project_type_section_selector_div").removeClass("d-none");
            $(".update_person_field_project_type_section_field_selector_div").removeClass("d-none");
            $("select[name='update_person_field_project_type_id']").prop('required', true);
            $("select[name='update_person_field_project_type_section_selector']").prop('required', true);
            $("select[name='update_person_field_project_type_section_field_selector']").prop('required', true);
            $(".loading").show();
            $.ajax({
                url: "automated_workflow/get_project_type_list",
                type: "GET",
                success: function (response) {
                    $("select[name='update_person_field_project_type_id']").html(response.html);
                    $("select[name='update_person_field_project_type_id']").val(json_details.person_field_project_type_id);
                    $("input[name='update_person_field_project_type_name']").val(json_details.person_field_project_type_name);
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
                url: "automated_workflow/get_project_type_section_list",
                type: "GET",
                data: { project_type_id: json_details.person_field_project_type_id, is_collection: 'static' },
                success: function (response) {
                    $("select[name='update_person_field_project_type_section_selector']").html(response.html);
                    $("select[name='update_person_field_project_type_section_selector']").val(json_details.person_field_project_type_section_selector);
                    $("select[name='update_person_field_project_type_section_selector_name']").val(json_details.person_field_project_type_section_selector_name);
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
                url: "automated_workflow/get_project_section_field",
                type: "GET",
                data: {
                    project_type_id: json_details.person_field_project_type_id,
                    project_section_selector: json_details.person_field_project_type_section_selector,
                    custom_field_types: 'PersonLink'
                },
                success: function (response) {
                    $("select[name='update_person_field_project_type_section_field_selector']").html(response.html);
                    $("select[name='update_person_field_project_type_section_field_selector']").val(json_details.person_field_project_type_section_field_selector);
                    $("select[name='update_person_field_project_type_section_field_selector_name']").html(json_details.person_field_project_type_section_field_selector_name);
                },
                error: function () {
                    $(".loading").hide();
                    alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
                },
            }).done(function () {
                $(".loading").hide();
            });

        }

    } else if (action_short_code == '3') {
        $(".update_client_sms_body_div").removeClass("d-none");
        $("textarea[name='update_client_sms_body']").prop('required', true);
        $("textarea[name='update_client_sms_body']").val(json_details.fv_project_note_body);
        //$(".update_fv_project_note_with_pin_div").removeClass("d-none");
        // if (json_details.fv_project_note_with_pin) {
        //     $("input[name='update_fv_project_note_with_pin']").prop('checked', true);
        // } else {
        //     $("input[name='update_fv_project_note_with_pin']").prop('checked', false);
        // }
    } else if (action_short_code == '4') {
        $(".update_client_sms_body_div").removeClass("d-none");
        $("textarea[name='update_client_sms_body']").prop('required', true);
        $("textarea[name='update_client_sms_body']").val(json_details.fv_project_task_body);
        $(".update_fv_project_task_assign_type_div").removeClass("d-none");
        $("select[name='update_fv_project_task_assign_type']").val(json_details.fv_project_task_assign_type);
        $("select[name='update_fv_project_task_assign_type']").prop('required', true);

        let update_fv_project_task_assign_type = json_details.fv_project_task_assign_type;
        if (update_fv_project_task_assign_type == 'role') {
            $(".update_fv_project_task_assign_user_role_div").removeClass("d-none");
            $("select[name='update_fv_project_task_assign_user_role']").prop('required', true);
            $(".loading").show();
            $.ajax({
                url: "automated_workflow/get_role_list",
                type: "GET",
                success: function (response) {
                    $("select[name='update_fv_project_task_assign_user_role']").html(response.html);
                    $("select[name='update_fv_project_task_assign_user_role']").val(json_details.fv_project_task_assign_user_role);
                    $("input[name='update_fv_project_task_assign_user_name']").html(json_details.fv_project_task_assign_user_role_name);
                },
                error: function () {
                    $(".loading").hide();
                    alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
                },
            }).done(function () {
                $(".loading").hide();
            });
        } else {
            $(".update_fv_project_task_assign_user_div").removeClass("d-none");
            $("select[name='update_fv_project_task_assign_user_id']").prop('required', true);
            $(".loading").show();
            $.ajax({
                url: "automated_workflow/get_user_list",
                type: "GET",
                success: function (response) {
                    $("select[name='update_fv_project_task_assign_user_id']").html(response.html);
                    $("select[name='update_fv_project_task_assign_user_id']").val(json_details.fv_project_task_assign_user_id);
                    $("input[name='update_fv_project_task_assign_user_name']").html(json_details.fv_project_task_assign_user_name);
                },
                error: function () {
                    $(".loading").hide();
                    alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
                },
            }).done(function () {
                $(".loading").hide();
            });
        }

    } else if (action_short_code == '5') {
        $(".update_client_sms_body_div").removeClass("d-none");
        $("textarea[name='update_client_sms_body']").prop('required', true);
        $("textarea[name='update_client_sms_body']").val(json_details.email_note_body);
    } else if (action_short_code == '6') {
        $(".update_fv_project_hashtag_div").removeClass("d-none");
        $("input[name='update_fv_project_hashtag']").prop('required', true);
        $("input[name='update_fv_project_hashtag']").val(json_details.fv_project_hashtag);
    } else if (action_short_code == '7') {
        $(".update_client_sms_body_div").removeClass("d-none");
        $("textarea[name='update_client_sms_body']").prop('required', true);
        $("textarea[name='update_client_sms_body']").val(json_details.fv_client_hashtag);
    } else if (action_short_code == '8') {
        $(".update_section_visibility_project_type_div").removeClass("d-none");
        $(".update_section_visibility_section_selector_div").removeClass("d-none");
        $(".update_section_visibility_div").removeClass("d-none");
        $("select[name='update_section_visibility_project_type_id']").prop('required', true);
        $("select[name='update_section_visibility_section_selector']").prop('required', true);
        $("select[name='update_section_visibility']").prop('required', true);

        $(".loading").show();
        $.ajax({
            url: "automated_workflow/get_project_type_list",
            type: "GET",
            success: function (response) {
                $("select[name='update_section_visibility_project_type_id']").html(response.html);
                $("select[name='update_section_visibility_project_type_id']").val(json_details.section_visibility_project_type_id);
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
            url: "automated_workflow/get_project_type_section_list",
            type: "GET",
            data: { project_type_id: json_details.section_visibility_project_type_id },
            success: function (response) {
                $("select[name='update_section_visibility_section_selector']").html(response.html);
                $("select[name='update_section_visibility_section_selector']").val(json_details.section_visibility_section_selector);
            },
            error: function () {
                $(".loading").hide();
                alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
            },
        }).done(function () {
            $(".loading").hide();
        });

        $("select[name='update_section_visibility']").val(json_details.section_visibility);
    } else if (action_short_code == '10') {
        $(".update_phase_assignment_div").removeClass("d-none");
        $("select[name='update_phase_assignment']").prop('required', true);
        $("select[name='update_phase_assignment']").val(json_details.phase_assignment);

        if (json_details.phase_assignment == 'Specific_Phase') {
            $(".update_phase_assignment_project_type_div").removeClass("d-none");
            $(".update_project_phase_id_native_div").removeClass("d-none");
            $("select[name='update_phase_assignment_project_type_id']").prop('required', true);
            $("select[name='update_project_phase_id_native']").prop('required', true);
            $(".loading").show();
            $.ajax({
                url: "automated_workflow/get_project_type_list",
                type: "GET",
                success: function (response) {
                    $("select[name='update_phase_assignment_project_type_id']").html(response.html);
                    $("select[name='update_phase_assignment_project_type_id']").val(json_details.phase_assignment_project_type_id);
                    $("input[name='update_phase_assignment_project_type_name']").val(json_details.phase_assignment_project_type_name);
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
                url: "automated_workflow/get_phase_list",
                data: { project_type_id: json_details.phase_assignment_project_type_id },
                type: "GET",
                success: function (response) {
                    $("select[name='update_project_phase_id_native']").html(response.html);
                    $("select[name='update_project_phase_id_native']").val(json_details.project_phase_id_native);
                    $("select[name='update_project_phase_id_native_name']").val(json_details.project_phase_id_native_name);
                },
                error: function () {
                    $(".loading").hide();
                    alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
                },
            }).done(function () {
                $(".loading").hide();
            });
        }

    } else if (action_short_code == '11') {
        $(".update_delivery_hook_url_div").removeClass("d-none");
        $("input[name='update_delivery_hook_url']").prop('required', true);
        $("input[name='update_delivery_hook_url']").val(json_details.delivery_hook_url);
    } else if (action_short_code == '12') {
        $(".update_client_sms_body_div").removeClass("d-none");
        $("textarea[name='update_client_sms_body']").prop('required', true);
        $("textarea[name='update_client_sms_body']").val(json_details.email_note_body);
    } else if (action_short_code == '13') {
        $(".update_mirror_div").removeClass("d-none");
        $(".update-mirror-select-item").prop('required', true);
        $(".loading").show();
        $.ajax({
            url: "automated_workflow/get_project_type_list",
            type: "GET",
            success: function (response) {
                $("select[name='update_mirror_from_field_project_type_id']").html(response.html);
                //$("select[name='update_mirror_to_field_project_type_id']").html(response.html);
                $("select[name='update_mirror_from_field_project_type_id']").val(json_details.mirror_from_field_project_type_id);
                $("select[name='update_mirror_to_field_project_type_id']").append($('<option>', {
                    value: json_details.mirror_to_field_project_type_id,
                    text: json_details.mirror_to_field_project_type_name
                }));
                $("select[name='update_mirror_to_field_project_type_id']").val(json_details.mirror_to_field_project_type_id);
                $("input[name='update_mirror_from_field_project_type_name']").val(json_details.mirror_from_field_project_type_name);
                $("input[name='update_mirror_to_field_project_type_name']").val(json_details.mirror_to_field_project_type_name);
                $('select[name="update_mirror_from_field_project_type_section_selector"]').html($('<option>', {
                    value: json_details.mirror_from_field_project_type_section_selector,
                    text: json_details.mirror_from_field_project_type_section_selector_name,
                }));
                $("input[name='update_mirror_from_field_project_type_section_selector_name']").val(json_details.mirror_from_field_project_type_section_selector_name);
                $('select[name="update_mirror_to_field_project_type_section_selector"]').html($('<option>', {
                    value: json_details.mirror_to_field_project_type_section_selector,
                    text: json_details.mirror_to_field_project_type_section_selector_name,
                }));
                $("input[name='update_mirror_to_field_project_type_section_selector_name']").val(json_details.mirror_to_field_project_type_section_selector_name);
                $('select[name="update_mirror_from_field_project_type_section_field_selector"]').html($('<option>', {
                    value: json_details.mirror_from_field_project_type_section_field_selector,
                    text: json_details.mirror_from_field_project_type_section_field_selector_name,
                }));
                $("input[name='update_mirror_from_field_project_type_section_field_selector_name']").val(json_details.mirror_from_field_project_type_section_field_selector_name);
                $('select[name="update_mirror_to_field_project_type_section_field_selector"]').html($('<option>', {
                    value: json_details.mirror_to_field_project_type_section_field_selector,
                    text: json_details.mirror_to_field_project_type_section_field_selector_name,
                }));
                $("input[name='update_mirror_to_field_project_type_section_field_selector_name']").val(json_details.mirror_to_field_project_type_section_field_selector_name);
            },
            error: function () {
                $(".loading").hide();
                alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    } else if (action_short_code == '14') {
        $(".update_project_team_choice_div").removeClass("d-none");
        $("select[name='update_project_team_choice']").prop('required', true);
        $(".update_team_member_user_div").removeClass("d-none");
        $("select[name='update_team_member_user_id']").prop('required', true);
        $("select[name='update_project_team_choice']").val(json_details.project_team_choice);
        if (json_details.project_team_choice == 'Add a Team Member') {
            $(".update_add_team_member_choice_div").removeClass("d-none");
        }
        if (json_details.add_team_member_choice == 'Level') {
            $(".update_add_team_member_choice_level_div").removeClass("d-none");
        }
        $(".loading").show();
        $.ajax({
            url: "automated_workflow/get_user_list",
            type: "GET",
            success: function (response) {
                $("select[name='update_team_member_user_id']").html(response.html);
                $("select[name='update_team_member_user_id']").val(json_details.team_member_user_id);
                $("input[name='update_team_member_user_name']").val(json_details.team_member_user_name);
                $("select[name='update_add_team_member_choice']").val(json_details.add_team_member_choice);
                $("select[name='update_add_team_member_choice_level']").val(json_details.add_team_member_choice_level);
            },
            error: function () {
                $(".loading").hide();
                alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    }
});


$("body").on("change", "select[name='update_phase_assignment']", async function () {
    let update_phase_assignment = $(this).val();

    $("select[name='update_phase_assignment_project_type_id']").prop('required', false);
    $("select[name='update_project_phase_id_native']").prop('required', false);
    $(".update_phase_assignment_project_type_div").addClass("d-none");
    $(".update_project_phase_id_native_div").addClass("d-none");

    if (update_phase_assignment == 'Specific_Phase') {
        $(".update_phase_assignment_project_type_div").removeClass("d-none");
        $(".update_project_phase_id_native_div").removeClass("d-none");
        $("select[name='update_phase_assignment_project_type_id']").prop('required', true);
        $("select[name='update_project_phase_id_native']").prop('required', true);
        $(".loading").show();
        $.ajax({
            url: "automated_workflow/get_project_type_list",
            type: "GET",
            success: function (response) {
                $("select[name='update_phase_assignment_project_type_id']").html(response.html);
            },
            error: function () {
                $(".loading").hide();
                alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    }
});

$("body").on("change", "select[name='update_phase_assignment_project_type_id']", async function () {
    let update_phase_assignment_project_type_id = $(this).val();
    $(".loading").show();
    $.ajax({
        url: "automated_workflow/get_phase_list",
        data: { project_type_id: update_phase_assignment_project_type_id },
        type: "GET",
        success: function (response) {
            $("select[name='update_project_phase_id_native']").html(response.html);
        },
        error: function () {
            $(".loading").hide();
            alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
        },
    }).done(function () {
        $(".loading").hide();
    });
});


$("body").on("change", "select[name='update_section_visibility_project_type_id']", async function () {
    let update_section_visibility_project_type_id = $(this).val();
    $(".loading").show();
    $.ajax({
        url: "automated_workflow/get_project_type_section_list",
        type: "GET",
        data: { project_type_id: update_section_visibility_project_type_id },
        success: function (response) {
            $("select[name='update_section_visibility_section_selector']").html(response.html);
        },
        error: function () {
            $(".loading").hide();
            alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
        },
    }).done(function () {
        $(".loading").hide();
    });
});


$("body").on("change", "select[name='update_fv_project_task_assign_type']", async function () {
    let update_fv_project_task_assign_type = $(this).val();

    $(".update_fv_project_task_assign_user_div").addClass("d-none");
    $("select[name='update_fv_project_task_assign_user_id']").prop('required', false);
    $(".update_fv_project_task_assign_user_role_div").addClass("d-none");
    $("select[name='update_fv_project_task_assign_user_role']").prop('required', false);

    if (update_fv_project_task_assign_type == 'role') {
        $(".update_fv_project_task_assign_user_role_div").removeClass("d-none");
        $("select[name='update_fv_project_task_assign_user_role']").prop('required', true);
        $(".loading").show();
        $.ajax({
            url: "automated_workflow/get_role_list",
            type: "GET",
            success: function (response) {
                $("select[name='update_fv_project_task_assign_user_role']").html(response.html);
            },
            error: function () {
                $(".loading").hide();
                alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    } else {
        $(".update_fv_project_task_assign_user_div").removeClass("d-none");
        $("select[name='update_fv_project_task_assign_user_id']").prop('required', true);
        $(".loading").show();
        $.ajax({
            url: "automated_workflow/get_user_list",
            type: "GET",
            success: function (response) {
                $("select[name='update_fv_project_task_assign_user_id']").html(response.html);
            },
            error: function () {
                $(".loading").hide();
                alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    }
});


$("body").on("click", "button.action-map-edit", async function () {
    let json_details = $(this).data('json');
    $('.action_map_edit_id').text('(ID# ' + json_details.map_id + ')');
    $('input[name="update_map_id"]').val(json_details.map_id);
    $('input[name="update_trigger_name"]').val(json_details.trigger_name);
    $('input[name="update_action_name"]').val(json_details.action_name);
    $('select[name="update_map_status"]').val(json_details.status);
    $('textarea[name="update_workflow_description"]').val(json_details.workflow_description);
});

$("body").on("change", "select[name='update_fv_project_task_assign_user_id']", async function () {
    $('input[name="update_fv_project_task_assign_user_name"]').val($(this).find("option:selected").text());
});

$(document).ready(function () {

    $('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
        localStorage.setItem('activeTab', $(e.target).attr('href'));
    });
    var activeTab = localStorage.getItem('activeTab');
    if (activeTab) {
        $('#myTab1 a[href="' + activeTab + '"]').tab('show');
    }

    var loadLogsTable = false;
    if (activeTab == "#logs") {
        loadLogTable();
        loadLogsTable = true;
    }
    $("#logs-tab").click(function () {
        if (!loadLogsTable) {
            loadLogTable();
            loadLogsTable = true;
        }
    });
});


$("body").on("change", "input.trigger-eligible", async function () {
    let trigger_id = $(this).val();
    $(".loading").show();
    $.ajax({
        type: "post",
        url: "automated_workflow/trigger/updateactive",
        data: {
            _token: CSRF_TOKEN,
            trigger_id: trigger_id
        },
        success: function (response) {
            //location.reload();
        },
    }).done(function () {
        $(".loading").hide();
    });
});


$("body").on("click", "button.trigger-all-status", async function () {
    Swal.fire({
        title: 'Are you sure want to change all trigger eligible status into active?',
        showDenyButton: true,
        confirmButtonText: 'Yes',
        denyButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: "post",
                url: "automated_workflow/trigger/updateactive",
                data: {
                    _token: CSRF_TOKEN,
                    trigger_active: 'all'
                },
                success: function (response) {
                    Swal.fire({
                        text: response.message,
                        icon: "success",
                    }).then(function () {
                        location.reload();
                    });
                },
            });
        }
    });
});




var BasicDatatablesDataSourceHtml = function () {
    var triggerDatatable = function () {
        var table1 = $('#trigger_datatable');
        table1.DataTable({
            responsive: true,
            order: [[0, 'desc']],
            bDestroy: true
        });
    };
    var actionDatatable = function () {
        var table2 = $('#action_datatable');
        table2.DataTable({
            responsive: true,
            order: [[0, 'desc']],
            bDestroy: true
        });
    };
    var actionMapDatatable = function () {
        var table3 = $('#action_map_datatable');
        table3.DataTable({
            responsive: true,
            order: [[0, 'desc']],
            bDestroy: true
        });
    };
    return {
        init: function () {
            triggerDatatable();
            actionDatatable();
            actionMapDatatable();
        },
    };
}();

jQuery(document).ready(function () {
    BasicDatatablesDataSourceHtml.init();
});


$("body").on("click", "#map-tab", async function () {
    $(".loading").show();
    $.ajax({
        type: "GET",
        url: "automated_workflow/get_trigger_action",
        data: {
            _token: CSRF_TOKEN
        },
        success: function (response) {
            let initial_triggers = response.initial_triggers;
            let eligible_actions = response.eligible_actions;

            $('select[name="map_trigger_id"] option:not(:first)').remove();
            initial_triggers.forEach(element => {
                $('select[name="map_trigger_id"] option:first').after(
                    $('<option/>')
                        .text(element.trigger_name)
                        .val(element.id)
                );
            });

            $('select[name="map_action_id"] option:not(:first)').remove();
            eligible_actions.forEach(element => {
                $('select[name="map_action_id"] option:first').after(
                    $('<option/>')
                        .text(element.action_name)
                        .val(element.id)
                );
            });
        },
    }).done(function () {
        $(".loading").hide();
    });
});


$("body").on("change", "select[name='project_phase_id_native']", async function () {
    $('input[name="project_phase_id_native_name"]').val($(this).find("option:selected").text());
});
$("body").on("change", "select[name='update_project_phase_id_native']", async function () {
    $('input[name="update_project_phase_id_native_name"]').val($(this).find("option:selected").text());
});



/* Update on Change Function of Send SMS, Email to Client, Mirror Field, Update Project Team */

$("body").on("change", "select[name='update_project_team_choice']", async function () {
    let project_team_choice = $(this).val();
    if (project_team_choice == 'Add a Team Member') {
        $(".update_add_team_member_choice_div").removeClass("d-none");
    } else {
        $(".update_add_team_member_choice_div").addClass("d-none");
        $(".update_add_team_member_choice_level_div").addClass("d-none");
    }
});

$("body").on("change", "select[name='update_add_team_member_choice']", async function () {
    let add_team_member_choice = $(this).val();
    if (add_team_member_choice == 'Level') {
        $(".update_add_team_member_choice_level_div").removeClass("d-none");
    } else {
        $(".update_add_team_member_choice_level_div").addClass("d-none");
    }
});

$("body").on("change", "select[name='update_team_member_user_id']", async function () {
    $('input[name="update_team_member_user_name"]').val($(this).find("option:selected").text());
});


$("body").on("change", "select[name='update_send_sms_choice']", async function () {
    let send_sms_choice = $(this).val();

    $(".update_person_field_project_type_div").addClass("d-none");
    $(".update_person_field_project_type_section_selector_div").addClass("d-none");
    $(".update_person_field_project_type_section_field_selector_div").addClass("d-none");
    $("select[name='update_person_field_project_type_id']").prop('required', false);
    $("select[name='update_person_field_project_type_section_selector']").prop('required', false);
    $("select[name='update_person_field_project_type_section_field_selector']").prop('required', false);

    if (send_sms_choice == 'To Person Field') {
        $(".update_person_field_project_type_div").removeClass("d-none");
        $(".update_person_field_project_type_section_selector_div").removeClass("d-none");
        $(".update_person_field_project_type_section_field_selector_div").removeClass("d-none");
        $("select[name='update_person_field_project_type_id']").prop('required', true);
        $("select[name='update_person_field_project_type_section_selector']").prop('required', true);
        $("select[name='update_person_field_project_type_section_field_selector']").prop('required', true);
        $(".loading").show();
        $.ajax({
            url: "automated_workflow/get_project_type_list",
            type: "GET",
            success: function (response) {
                $("select[name='update_person_field_project_type_id']").html(response.html);
            },
            error: function () {
                $(".loading").hide();
                alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    }
});

$("body").on("change", "select[name='update_person_field_project_type_id']", async function () {
    let person_field_project_type_id = $(this).val();
    $('input[name="update_person_field_project_type_name"]').val($(this).find("option:selected").text());

    $(".loading").show();
    $.ajax({
        url: "automated_workflow/get_project_type_section_list",
        type: "GET",
        data: { project_type_id: person_field_project_type_id, is_collection: 'static' },
        success: function (response) {
            $("select[name='update_person_field_project_type_section_selector']").html(response.html);
        },
        error: function () {
            $(".loading").hide();
            alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
        },
    }).done(function () {
        $(".loading").hide();
    });
});

$("body").on("change", "select[name='update_person_field_project_type_section_selector']", async function () {
    let person_field_project_type_section_selector = $(this).val();
    $('input[name="update_person_field_project_type_section_selector_name"]').val($(this).find("option:selected").text());

    $(".loading").show();
    $.ajax({
        url: "automated_workflow/get_project_section_field",
        type: "GET",
        data: {
            project_type_id: $("select[name='update_person_field_project_type_id']").val(),
            project_section_selector: person_field_project_type_section_selector,
            custom_field_types: 'PersonLink'
        },
        success: function (response) {
            $("select[name='update_person_field_project_type_section_field_selector']").html(response.html);
        },
        error: function () {
            $(".loading").hide();
            alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
        },
    }).done(function () {
        $(".loading").hide();
    });
});

$("body").on("change", "select[name='update_person_field_project_type_section_field_selector']", async function () {
    $('input[name="update_person_field_project_type_section_field_selector_name"]').val($(this).find("option:selected").text());
});


/* Mirror from/to Field JS */
$("body").on("change", "select[name='update_mirror_from_field_project_type_id']", async function () {
    let mirror_from_field_project_type_id = $(this).val();
    let mirror_from_field_project_type_text = $(this).find("option:selected").text();
    $('input[name="update_mirror_from_field_project_type_name"]').val(mirror_from_field_project_type_text);

    $("select[name='update_mirror_to_field_project_type_id']").find('option').not(':first').remove();
    $("select[name='update_mirror_to_field_project_type_id']").append($('<option>', {
        value: mirror_from_field_project_type_id,
        text: mirror_from_field_project_type_text
    }));

    $(".loading").show();
    $.ajax({
        url: "automated_workflow/get_project_type_section_list",
        type: "GET",
        data: { project_type_id: mirror_from_field_project_type_id, is_collection: 'static' },
        success: function (response) {
            $("select[name='update_mirror_from_field_project_type_section_selector']").html(response.html);
        },
        error: function () {
            $(".loading").hide();
            alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
        },
    }).done(function () {
        $(".loading").hide();
    });
});

$("body").on("change", "select[name='update_mirror_from_field_project_type_section_selector']", async function () {
    let mirror_from_field_project_type_section_selector = $(this).val();
    $('input[name="update_mirror_from_field_project_type_section_selector_name"]').val($(this).find("option:selected").text());

    $(".loading").show();
    $.ajax({
        url: "automated_workflow/get_project_section_field",
        type: "GET",
        data: {
            project_type_id: $("select[name='update_mirror_from_field_project_type_id']").val(),
            project_section_selector: mirror_from_field_project_type_section_selector,
            custom_field_types: 'mirror'
        },
        success: function (response) {
            $("select[name='update_mirror_from_field_project_type_section_field_selector']").html(response.html);
        },
        error: function () {
            $(".loading").hide();
            alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
        },
    }).done(function () {
        $(".loading").hide();
    });
});

$("body").on("change", "select[name='update_mirror_from_field_project_type_section_field_selector']", async function () {
    $('input[name="update_mirror_from_field_project_type_section_field_selector_name"]').val($(this).find("option:selected").text());
});

$("body").on("change", "select[name='update_mirror_to_field_project_type_id']", async function () {
    let mirror_to_field_project_type_id = $(this).val();
    $('input[name="update_mirror_to_field_project_type_name"]').val($(this).find("option:selected").text());

    $(".loading").show();
    $.ajax({
        url: "automated_workflow/get_project_type_section_list",
        type: "GET",
        data: { project_type_id: mirror_to_field_project_type_id, is_collection: 'static' },
        success: function (response) {
            $("select[name='update_mirror_to_field_project_type_section_selector']").html(response.html);
        },
        error: function () {
            $(".loading").hide();
            alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
        },
    }).done(function () {
        $(".loading").hide();
    });
});

$("body").on("change", "select[name='update_mirror_to_field_project_type_section_selector']", async function () {
    let mirror_to_field_project_type_section_selector = $(this).val();
    $('input[name="update_mirror_to_field_project_type_section_selector_name"]').val($(this).find("option:selected").text());

    $(".loading").show();
    $.ajax({
        url: "automated_workflow/get_project_section_field",
        type: "GET",
        data: {
            project_type_id: $("select[name='update_mirror_to_field_project_type_id']").val(),
            project_section_selector: mirror_to_field_project_type_section_selector,
            custom_field_types: 'mirror'
        },
        success: function (response) {
            $("select[name='update_mirror_to_field_project_type_section_field_selector']").html(response.html);
        },
        error: function () {
            $(".loading").hide();
            alert("Failed to handle your request! Filevine has a problem now, Please try again 5 mins later!");
        },
    }).done(function () {
        $(".loading").hide();
    });
});

$("body").on("change", "select[name='update_mirror_to_field_project_type_section_field_selector']", async function () {
    $('input[name="update_mirror_to_field_project_type_section_field_selector_name"]').val($(this).find("option:selected").text());
});
