$(document).ready(function (e) {
    $(document).ready(function () {
        if (current_project_typeid != "") {
            getPhases(
                current_project_typeid,
                current_project_type_name,
                current_template_name,
                true
            );
            getCategories(current_project_typeid, curPhase, true);
        }
    });
   
    $("#lstPhases").click(function () {
        var current_phase = $(this).val();
        var current_template = $("#id_project_types").val();
        getCategories(current_template, current_phase);
    });

    $(document).on("click", "#btnAddRow2", function () {
        var currentRowCount = $("#RowCount").val();
        var newRowCount = parseInt(currentRowCount) + 1;
        var type = $("#currentProjectTypeId").val();
        var template_name = $("#currentTemplateName").val();

        $(".loading").show();
        $.ajax(
            "get_phase_categories_info?row=" +
            currentRowCount +
            "&typeId=" +
            type +
            "&template_name=" +
            template_name,
            {
                success: function (data) {
                    $(".loading").hide();
                    $("#tblCatInfo").append(data);
                    $("#RowCount").val(newRowCount);

                    var totRows = newRowCount;
                    var totCats = $("#CategoryCount").val();
                    callTinyMce();
                    if (parseInt(totRows) < parseInt(totCats)) {
                        $("#btnAddRow2").css("display", "");
                    } else {
                        $("#btnAddRow2").css("display", "none");
                    }
                },
                error: function () {
                    $(".loading").hide();
                    Swal.fire({
                        text: "There was some error performing the AJAX call!",
                        icon: "error",
                    });
                },
            }
        );
    });

    $(document).on("click", ".save_phase_mapping", function () {
        var _self = $(this);
        tinyMCE.triggerSave(true, true);
        let data = {};
        data.id = "";
        if (_self.parent().parent().find(".catid").length) {
            data.id = _self.parent().parent().find(".catid").val();
        }
        data.type_phase_id = _self
            .parent()
            .parent()
            .find(".TemplateCats")
            .val();
        data.type_phase_name = _self
            .parent()
            .parent()
            .find(".TemplateCatNames")
            .val();
        data.phase_category_id = _self.parent().parent().find(".cats").val();
        data.overrite_phase_name = _self
            .parent()
            .parent()
            .find(".OverwritePhaseName")
            .val();
        data.phase_description = _self
            .parent()
            .parent()
            .find(".phaseMappingtxtDescription")
            .val();
        data.project_type_id = $(document).find("#currentProjectTypeId").val();
        data.project_type_name = $(document)
            .find("#currentProjectTypeName")
            .val();
        data.is_default = _self
            .parent()
            .parent()
            .find(".isDefault")
            .is(":checked")
            ? 1
            : 0;
        data._token = csrf_token;
        $(".loading").show();
        $.ajax({
            url: "phase_mapping_single_save",
            method: "POST",
            data: data,
            success: function (response) {
                $(".loading").hide();
                Swal.fire({
                    text: "Phase mapping data successfully updated!",
                    icon: "success",
                });
            },
            error: function () {
                $(".loading").hide();
                Swal.fire({
                    text: "There was some error performing the AJAX call!",
                    icon: "error",
                });
            },
        });
    });

    $(document).on("click", ".delete_phase_mapping", function () {
        Swal.fire({
            title: "Are you sure want to delete?",
            showDenyButton: true,
            confirmButtonText: "Yes",
            denyButtonText: "No",
        }).then((result) => {
            if (result.isConfirmed) {
                var pm_id = $(this).attr("data-target");
                var _self = $(this);
                $(".loading").show();
                $.ajax(
                    "get_phase_categories_info?function=delete&pm_id=" + pm_id,
                    {
                        success: function (data) {
                            $(".loading").hide();
                            if (data.success) {
                                _self.parent().parent().remove();
                            } else {
                                Swal.fire({
                                    text: data.message,
                                    icon: "error",
                                });
                            }
                        },
                        error: function () {
                            $(".loading").hide();
                            Swal.fire({
                                text: "There was some error performing the AJAX call!",
                                icon: "error",
                            });
                        },
                    }
                );
            }
        });
    });

    $(document).on("click", ".edit_phase_mapping", function () {
        var pm_id = $(this).attr("data-target");
        $(".label_" + pm_id).text("Override Project Phase");
        $("#OverwritePhase_" + pm_id).attr("type", "text");
        $(this).hide();
    });

    $(document)
        .on("focus", ".lstcats", function () {
            previous = this.value;
        })
        .on("change", ".lstcats", function () {
            var allCats = $("#AllSelectedCatIds").val();
            var allCatsArray = allCats.split(",");
            var val = $(this).val();
            var textVal = $("#" + $(this)[0].id + " option:selected").text();
            var flag = $.inArray(val, allCatsArray);
            var type = $("#currentProjectTypeId").val();

            if (flag == "-1") {
                // $(this).parent().parent().find('textarea').val('');
                var objId = $(this)
                    .parent()
                    .parent()
                    .find("textarea")
                    .attr("id");
                $(this).parent().find(".TemplateCats").val(val);
                $(this).parent().find(".TemplateCatNames").val(textVal);

                if (val != "0") {
                    allCatsArray.push(val);
                    $("#AllSelectedCatIds").val(allCatsArray);
                }
            } else {
                Swal.fire({
                    text: "selcted category already exists!",
                    icon: "error",
                });
                $(this).val(previous);
            }
        });

    $(document).on("change", ".cats", function () {
        let val = $(this).val();
        let rowId = $(this).data("row-id");
        if (val) {
            $(".loading").show();
            $.ajax("get_phase_category_description_by_id?id=" + val, {
                success: function (data) {
                    $(".loading").hide();
                    if (data.phase_category) {
                        tinyMCE
                            .get("txtDescription_" + rowId)
                            .setContent(
                                data.phase_category.phase_category_description
                            );
                    }
                },
                error: function () {
                    $(".loading").hide();
                },
            });
        }
    });

    // check for fetch type
    $(document).on("click", ".fetch_types", function () {
        var type = $("#currentProjectTypeId").val();
        $(".loading").show();
        $.ajax("get_phase_categories_info?fetch=1&fetchId=" + type, {
            success: function (data) {
                $(".loading").hide();
                $values = JSON.parse(data);
                $(".fetch_message").css("display", "block");
                $(".fetch_message").html($values.message);
            },
            error: function () {
                $(".loading").hide();
                Swal.fire({
                    text: "There was some error performing the AJAX call!",
                    icon: "error",
                });
            },
        });
    });
    // check for fetch type
    $(document).on("click", "#delete_mapped_timeline", function () {
        Swal.fire({
            text: "Are you sure to delete the selected timeline mapping?",
            confirmButtonText: "Yes!",
            showCancelButton: true,
            confirmButtonColor: "#2778c4",
            cancelButtonColor: "#d14529",
            cancelButtonText: "No.",
        }).then((result) => {
            if (result.isConfirmed) {
                var projectTypeId = $("#currentProjectTypeId").val();
                $(".loading").show();
                $.ajax({
                    url: "delete_mapped_timeline/" + projectTypeId,
                    method: "DELETE",
                    headers: {
                        "X-CSRF-Token": $('meta[name="csrf-token"]').attr("content")
                    },
                    success: function (data) {
                        $(".loading").hide();
                        if (data.success) {
                            window.location.replace("phase_mapping");
                        } else {
                            Swal.fire({
                                text: "Something was wrong!",
                                icon: "error",
                            });
                        }
                    },
                    error: function () {
                        $(".loading").hide();
                        Swal.fire({
                            text: "There was some error performing the AJAX call!",
                            icon: "error",
                        });
                    },
                });
            }
        });
    });

    var $form = $("form");

    $form.submit(function (e) {
        tinyMCE.triggerSave(true, true);
        var tinyedotrvalid = true;
        $(".phaseMappingtxtDescription").each(function () {
            var tinyedotor_val = $(this).val();
            var tinyedotor = $.trim($(tinyedotor_val).text());
            if (tinyedotor == "") {
                tinyedotrvalid = false;
            }
        });
        if (tinyedotrvalid == false) {
            Swal.fire({
                text: "Please fill the Project Phase Description!",
                icon: "error",
            });
            return false;
        }
        var initialState = $("#initial_state").val();
        var max_words = $("#max_desc_words").val();
        if (initialState === $form.serialize()) {
            Swal.fire({
                text: "You didn't change anything!",
                icon: "error",
            });
            return false;
        }

        // check for validation of description max words
        var errors = 0;
        $("#tblCatInfo")
            .find("textarea")
            .each(function () {
                var words_count = $(this).val().split(" ");
                if (words_count.length > max_words) {
                    if ($(this).parent().find(".text-danger").length <= 0) {
                        $(this).after(
                            "<span class='text-danger'>Please enter maximum " +
                            max_words +
                            " words</span>"
                        );
                    }
                    errors += 1;
                } else {
                    $(this).next().remove();
                }
            });

        if (errors > 0) {
            return false;
        }
    });
});

function addNewMapping() {
    var valToAppend_project = $("#id_project_types").val();
    var valToAppend_template = $("#id_phase_templates").val();

    var nameToAppend_project = $("#id_project_types option:selected").text();
    var nameToAppend_template = $("#id_phase_templates option:selected").text();

    forLoopArr = document.getElementsByClassName("span_name");
    var tocheck = true;
    for (var i = 0; i < forLoopArr.length; i++) {
        if (forLoopArr[i].dataset.id == valToAppend_project) {
            tocheck = false;
        }
    }
    if (tocheck) {
        if (
            valToAppend_project != ""
        ) {
            escapeValToAppend = escape(valToAppend_project);
            $("#divToAppendTAb").append(
                "<span class='btn span_name span_tabs' data-id=\"" +
                valToAppend_project +
                '"' +
                ' data-template="' +
                valToAppend_template +
                '"' +
                " onclick='getPhases(\"" +
                escapeValToAppend +
                '","' +
                nameToAppend_project +
                '","' +
                nameToAppend_template +
                "\")'>" +
                nameToAppend_project +
                "</span>"
            );
            current_template = getPhases(
                escapeValToAppend,
                nameToAppend_project,
                nameToAppend_template
            );
        } else {
            Swal.fire({
                text: "Invalid Mapping Info!",
                icon: "error",
            });
        }
    }
}

function getPhases(projectid, projectname, template_name, update = false) {
    // init the fetch info part.
    $(".fetch_message").css("display", "none");
    $(".fetch_message").html("");

    if (projectid != "") {
        // set values of project types
        $("#currentProjectTypeId").val(projectid);
        $("#currentProjectTypeName").val(projectname);
        $("#currentProjectType").text(projectname);
        $("#currentProjectTypeContent").css("display", "block");
        $("#currentTemplateName").val(template_name);
        $("#currentTimelineTemplateName").text(template_name);

        $("#btnAddRow2").css("display", "none");
        $(".span_tabs").removeClass("active_tab");

        $(".loading").show();
        $.ajax(
            "get_phase_categories_info?type=" +
            projectid +
            "&template_name=" +
            template_name,
            {
                success: function (data) {
                    $(".loading").hide();
                    $.ajax(
                        "get_phase_mapping_override_title_by_id?project_id=" +
                        projectid,
                        {
                            success: function (data) {
                                $(".loading").hide();
                                if (data.tenant_phase_mapping_override_titles) {
                                    $("#title").val(
                                        data
                                            .tenant_phase_mapping_override_titles
                                            .title
                                    );
                                } else {
                                    $("#title").val("[phaseName]");
                                }
                            },
                            error: function () {
                                $(".loading").hide();
                            },
                        }
                    );
                    $('span[data-id="' + projectid + '"]').addClass(
                        "active_tab"
                    );
                    $("#categoryInfo").html(data);
                    var totRows = $("#RowCount").val();
                    var totCats = $("#CategoryCount").val();
                    if (parseInt(totRows) < parseInt(totCats)) {
                        $("#btnAddRow2").css("display", "");
                        if (parseInt(totRows) <= 0) {
                            // add new record : empty row.
                            $("#btnAddRow2").trigger("click");
                        }
                    } else {
                        $("#btnAddRow2").css("display", "none");
                    }

                    if (parseInt(totRows) > 0) {
                        $(".fetch_types").css("display", "block");
                        $("#delete_mapped_timeline").css("display", "block");
                    } else {
                        $(".fetch_types").css("display", "none");
                        $("#delete_mapped_timeline").css("display", "none");
                    }
                    if (update) {
                        $("#lstPhases").val(curPhase);
                    }

                    var initialState = $("form").serialize();
                    $("#initial_state").val(initialState);
                    callTinyMce();
                },
                error: function () {
                    $(".loading").hide();
                    Swal.fire({
                        text: "There was some error performing the AJAX call!",
                        icon: "error",
                    });
                },
            }
        );
    } else {
        $("#lstPhases").html('<option value="">--Select Phase--</option>');
        $("#lstCategories").html(
            '<option value="">--Select Category--</option>'
        );
        $("#categoryInfo").html("");
        $("#btnAddRow2").css("display", "none");
    }
}

function getCategories(template, phase, update = false) {
    if (phase != "") {
        // set values of project types
        var phaseName = $("#lstPhases option:selected").text();
        $("#phaseName").val(phaseName);

        $("#btnAddRow2").css("display", "none");
        $(".loading").show();
        $.ajax(
            "get_phase_categories_info?type=" + template + "&phase=" + phase,
            {
                success: function (data) {
                    $(".loading").hide();
                    // $("#lstCategories").html(data);
                    $("#categoryInfo").html(data);
                    var totRows = $("#RowCount").val();
                    var totCats = $("#CategoryCount").val();
                    if (parseInt(totRows) < parseInt(totCats)) {
                        $("#btnAddRow2").css("display", "");
                    } else {
                        $("#btnAddRow2").css("display", "none");
                    }
                    if (update) {
                        $("#lstCategories").val(curCat);
                    }
                },
                error: function () {
                    $(".loading").hide();
                    Swal.fire({
                        text: "There was some error performing the AJAX call!",
                        icon: "error",
                    });
                },
            }
        );
    } else {
        $("#lstCategories").html(
            '<option value="">--Select Category--</option>'
        );
        $("#categoryInfo").html("");
        $("#btnAddRow2").css("display", "");
    }
}
function callTinyMce() {
    tinymce.remove(".phaseMappingtxtDescription");
    tinymce.init({
        selector: ".phaseMappingtxtDescription",
        min_height: 500,
        plugins: [
            "advlist autolink lists link image charmap print preview anchor tinymcespellchecker",
            "searchreplace visualblocks code fullscreen",
            "insertdatetime media table paste",
            "media code",
            "lists",
            "image code"
        ],
        menubar: "file edit insert view format table tools",
        toolbar:"bullist numlist | undo redo | styleselect | bold italic | lists | alignleft aligncenter alignright alignjustify | outdent indent | spellchecker language spellcheckdialog",
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

$(document).on("click", ".isDefault", function () {
    if ($(this).is(":checked")) {
        $(".isDefault").prop("checked", false);
        $(this).prop("checked", true);
    }
});


$("input.default_phase_mapping").change(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $(".loading").show();
    $.ajax({
        url: "update_default_phase_mapping",
        type: "POST",
        success: function (response) {

        },
    }).done(function () {
        $(".loading").hide();
    });
});

function addAllPhase() {
    $(".loading").show();
    $.ajax({
        url: "add_all_phase_mapping",
        type: "POST",
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            current_template_name: $(document).find("#currentTimelineTemplateName").val(),
            current_project_typeid: $(document).find("#currentProjectTypeId").val(),
            current_project_typename: $(document).find("#currentProjectTypeName").val(),
        },
        dataType: 'JSON',
        success: function (response) {
            if (response.status) {
                Swal.fire({
                    text: response.message,
                    icon: "success",
                }).then(function () {
                    location.reload();
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
}
