$(document).ready(function (e) {
    if (curTemplate != "") {
        $("#currentTemplateX").val(curTemplate);
        setSelectedTemplateInfo(curTemplate);
    }
    else if (curTenantTemplate != "") {
        $("#currentTemplateX").val(curTenantTemplate);
        setSelectedTemplateInfo(curTenantTemplate);
    }

    $(document)
        .on("focus", ".lstcats", function () {
            previous = this.value;
        })
        .on("change", ".lstcats", function () {
            var allCats = $("#AllSelectedCatIds").val();
            var allCatsArray = allCats.split(",");
            var val = $(this).val();
            let rowId = $(this).data("row-id");
            var textVal = $("#" + $(this)[0].id + " option:selected").text();
            var flag = $.inArray(val, allCatsArray);
            var template = $("#currentTemplateX").val();

            if (flag == "-1") {

                if (val) {
                    $(".loading").show();
                    $.ajax("get_template_category_description_by_id?id=" + val, {
                        success: function (data) {
                            $(".loading").hide();
                            if (data.template_category) {
                                tinyMCE
                                    .get("txtDescription_" + rowId)
                                    .setContent(
                                        data.template_category.template_category_description
                                    );
                            }
                        },
                        error: function () {
                            $(".loading").hide();
                        },
                    });
                }


                $(this).parent().parent().find("textarea").val("");
                var objId = $(this).parent().parent().find("textarea").attr("id");
                var objTextId = $(this).parent().find(".TemplateCatNames").attr("id");
                $(this).parent().find(".TemplateCats").val(val);
                // $(this).parent().find('.TemplateCatNames').val(textVal);

                if (val != "0") {
                    allCatsArray.push(val);
                    $("#AllSelectedCatIds").val(allCatsArray);

                    $.ajax(
                        "phase_categories_info?cat_id=" +
                        val +
                        "&type=description&template_name=" +
                        template,
                        {
                            success: function (data) {
                                $("#" + objId).val(data);
                            },
                            error: function () {
                                Swal.fire({
                                    text: 'There was some error performing the AJAX call!',
                                    icon: "error",
                                });
                            },
                        }
                    );

                    $.ajax("phase_categories_info?cat_id=" + val + "&type=name", {
                        success: function (data) {
                            $("#" + objTextId).val(data);
                        },
                        error: function () {
                            Swal.fire({
                                text: 'There was some error performing the AJAX call!',
                                icon: "error",
                            });
                        },
                    });
                }
            } else {
                Swal.fire({
                    text: 'selected category already exists!',
                    icon: "error",
                });
                $(this).val(previous);
            }
        });


    $(document).on("click", "#btnAddRow", function () {
        var currentRowCount = $("#RowCount").val();
        var newRowCount = parseInt(currentRowCount) + 1;
        var template = $("#currentTemplateX").val();
        var template_type = $('.span_name[data-id="' + template + '"]').attr('data-type');
        var currentCustomTabsCount = $('.TemplateCatNames').length;

        if (template_type == 'custom' && currentCustomTabsCount >= 6) {
            Swal.fire({
                text: "You've reached the maximum amount of Rows.",
                icon: "error",
            });
        }
        else {
            $.ajax(
                "phase_categories_info?row=" +
                currentRowCount +
                "&template_name=" +
                template + "&template_type=" + template_type,
                {
                    success: function (data) {
                        $("#tblCatInfo").append(data);
                        $("#RowCount").val(newRowCount);

                        var totRows = newRowCount;
                        var totCats = $("#CategoryCount").val();
                        $("#btnAddRow").css("display", "");
                        callCategoryTinyMce()
                    },
                    error: function () {
                        Swal.fire({
                            text: 'There was some error performing the AJAX call!',
                            icon: "error",
                        });
                    },
                }
            );
        }
    });

    $(document).on("click", ".delete_phase_category", function () {
        Swal.fire({
            title: 'Are you sure want to delete?',
            showDenyButton: true,
            confirmButtonText: 'Yes',
            denyButtonText: 'No'
        }).then((result) => {
            if (result.isConfirmed) {
                var pc_id = $(this).attr('data-target');
                var _self = $(this);
                $.ajax(
                    "phase_categories_info?function=delete&pc_id=" + pc_id,
                    {
                        success: function (data) {
                            if (data.success) {
                                _self.parent().parent().remove();
                            }
                            else {
                                Swal.fire({
                                    text: data.message,
                                    icon: "error",
                                });
                            }
                        },
                        error: function () {
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


    $(document).on("click", "#deleteTemplate", function () {
        Swal.fire({
            title: 'Are you sure want to delete this template?',
            showDenyButton: true,
            showCancelButton: false,
            confirmButtonText: 'Yes',
            denyButtonText: 'No',
        }).then((result) => {
            if (result.isConfirmed) {
                var currentTemplateX = $("#currentTemplateX").val();
                var template_type = $('.span_name[data-id="' + currentTemplateX + '"]').attr('data-type');
                $.ajax({
                    url: 'delete_template',
                    method: 'POST',
                    data: {
                        'data': encodeURI(currentTemplateX),
                        'template_type': template_type,
                    },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                text: response.message,
                                icon: "success",
                            }).then(function () {
                                location.reload();
                            });
                        }
                        else {
                            Swal.fire({
                                text: response.message,
                                icon: "error",
                            });
                        }
                    }
                });
            }
        });
    });

    $(document).on("click", ".edit_phase_cat", function () {
        var pm_id = $(this).attr('data-target');
        $('.label_' + pm_id).text('Override Phase Category');
        $('#OverwritePhase_' + pm_id).attr('type', 'text');
        $(this).hide();
    });

    var $form = $('form');

    $form.submit(function (e) {
        tinyMCE.triggerSave(true, true);
        var tinyedotrvalid = true;
        $(".phaseCategorytxtDescription").each(function () {
            var tinyedotor_val = $(this).val();
            var tinyedotor = $.trim($(tinyedotor_val).text());
            if (tinyedotor == "") {
                tinyedotrvalid = false;
            }
        });
        if (tinyedotrvalid == false) {
            Swal.fire({
                text: "Please fill the Category Description",
                icon: "error",
            });
            return false;
        }
        var initialState = $('#initial_state').val();
        var max_words = $('#max_desc_words').val();
        if (initialState === $form.serialize()) {
            Swal.fire({
                text: "You didn't change anything",
                icon: "error",
            });
            return false;
        }
        // check for validation of description max words
        var errors = 0;
        $('#tblCatInfo').find('textarea').each(function () {
            var words_count = $(this).val().split(" ");
            if (words_count.length > max_words) {
                if ($(this).parent().find('.text-danger').length <= 0) {
                    $(this).after("<span class='text-danger'>Please enter maximum " + max_words + " words</span>");
                }
                errors += 1;
            }
            else {
                $(this).next().remove();
            }
        });

        if (errors > 0) {
            return false;
        }
    });

    // handle custom template creation
    $(".createcustomtemplate-create").click(function () {

        var sel_template = $("#currentTemplateX").val();
        let op_type = $(this).attr('data-op');
        let post_fix = op_type == 'edit' ? '-edit' : '';
        let template_name = $("#custom-template-name" + post_fix).val();
        let template_desc = $("#custom-template-desc" + post_fix).val();
        let template_default = $("#custom-template-default" + post_fix).is(':checked') ? 1 : 0;

        if (template_name == '') {
            Swal.fire({
                text: 'Please enter template name',
                icon: 'error',
            });
        }
        else if (template_desc == '') {
            Swal.fire({
                text: 'Please enter template description',
                icon: 'error',
            });
        }
        else {
            let template_data = {
                _token: csrf_token,
                template_name: template_name,
                template_description: template_desc,
                is_default: template_default
            };
            if (op_type == 'edit' && edit_template_id !== "") {
                template_data['id'] = edit_template_id;
            }

            $(".loading").show();
            $.ajax({
                url: "phase_categories/custom_template",
                type: "POST",
                data: template_data,
                dataType: 'JSON',
                success: function (response) {
                    let icon_class = "error";
                    if (response.status) {
                        icon_class = "success";
                        // append template to current list
                        $('.createcustomtemplate-close').trigger('click');
                        $('body').removeClass('modal-open');
                        $('.modal-backdrop').remove();
                        if (op_type == 'create') {
                            $("#custom-template-name").val('');
                            $("#custom-template-desc").val('');
                            $("#custom-template-default").prop('checked', false);
                            addCustomTemplateTab(response.template.template_name, response.template.id);
                        }
                        else {
                            $("#custom-template-name-edit").val('');
                            $("#custom-template-desc-edit").val('');
                            $("#custom-template-default-edit").prop('checked', false);
                            // change if name changed
                            if (response.template.template_name !== response.template.old_name) {
                                $('.span_name[data-id="' + response.template.old_name + '"]').attr('data-id', response.template.template_name);
                                $('.span_name[data-id="' + response.template.template_name + '"]').text(response.template.template_name);
                                $('.span_name[data-id="' + response.template.template_name + '"]').attr('onclick', 'setSelectedTemplateInfo("' + response.template.template_name + '")');
                                if (sel_template == response.template.old_name) {
                                    $("#currentTemplateX").val(response.template.template_name);
                                }
                            }
                        }
                    }
                    Swal.fire({
                        text: response.message,
                        icon: icon_class,
                    });
                },
                error: function () {
                    $(".loading").hide();
                    Swal.fire({
                        text: 'Error to Process Your Request! Please try Again!',
                        icon: 'error',
                    });
                },
            }).done(function () {
                $(".loading").hide();
            });
        }
    });

    // handle edit custom template
    $(document).on("click", ".edit_custom_template", function () {
        var tid = $(this).attr('data-id');
        $(".loading").show();
        $.ajax({
            url: "phase_categories/get_custom_template",
            type: "POST",
            data: { 'template_id': tid },
            dataType: 'JSON',
            success: function (response) {
                let icon_class = "error";
                if (response.status) {
                    icon_class = "success";
                    // append template to current list
                    edit_template_id = response.template.id;
                    $('#customTemplateEditModal').modal('show');
                    $("#custom-template-name-edit").val(response.template.template_name);
                    $("#custom-template-desc-edit").val(response.template.template_description);
                    $("#custom-template-default-edit").prop('checked', (response.template.is_default ? true : false));
                }
                else {
                    Swal.fire({
                        text: response.message,
                        icon: icon_class,
                    });
                }
            },
            error: function () {
                $(".loading").hide();
                Swal.fire({
                    text: 'Error to Process Your Request! Please try Again!',
                    icon: 'error',
                });
            },
        }).done(function () {
            $(".loading").hide();
        });
    });
});

function addTab() {
    var valToAppend = $("#lstTemplates").val();
    forLoopArr = document.getElementsByClassName("span_name");
    var tocheck = true;
    for (var i = 0; i < forLoopArr.length; i++) {
        if (forLoopArr[i].dataset.id == valToAppend) {
            tocheck = false;
        }
    }
    if (tocheck) {
        if (valToAppend != "" && valToAppend != " ") {
            escapeValToAppend = escape(valToAppend);
            $("#divToAppendTAb").append(
                "<span class='btn span_name span_tabs' data-id=\"" +
                valToAppend +
                '" onclick=\'setSelectedTemplateInfo("' +
                escapeValToAppend +
                "\")' data-type='general'>" +
                valToAppend +
                "</span>"
            );
            current_template = setSelectedTemplateInfo(escapeValToAppend);
        }
    }
}

function addCustomTemplateTab(valToAppend, tid) {
    forLoopArr = document.getElementsByClassName("span_name");
    var tocheck = true;
    for (var i = 0; i < forLoopArr.length; i++) {
        if (forLoopArr[i].dataset.id == valToAppend) {
            tocheck = false;
        }
    }
    if (tocheck) {
        if (valToAppend != "" && valToAppend != " ") {
            escapeValToAppend = escape(valToAppend);
            $("#divToAppendTAb").append(
                "<div class='btn p-0 m-0'><span class='btn span_name span_tabs' data-id=\"" +
                valToAppend +
                '" onclick=\'setSelectedTemplateInfo("' +
                escapeValToAppend +
                "\")' data-type='custom'>" +
                valToAppend +
                "</span><span class='fa fa-edit edit_custom_template' data-id='" + tid + "'></span></div>"
            );
            current_template = setSelectedTemplateInfo(escapeValToAppend);
        }
    }
}


function setSelectedTemplateInfo(curTemplate) {
    curTemplate = unescape(curTemplate);
    var template_type = $('.span_name[data-id="' + curTemplate + '"]').attr('data-type');
    $('#currentTemplateX').val(curTemplate);
    $('#currentTemplateXType').val(template_type);
    $("#btnAddRow").css("display", "");
    $('.span_tabs').removeClass('active_tab');

    $.ajax("phase_category_title?template_name=" + curTemplate, {
        success: function (data) {
            if (data.tenant_phase_category_override_title?.title) {
                $("#categoryOverrideTitle").val(data.tenant_phase_category_override_title.title);
            } else {
                $("#categoryOverrideTitle").val('Our [projectTypeName] Case Timeline');
            }
        },
        error: function () {

        },
    });

    $.ajax("phase_categories_info?template_name=" + curTemplate + "&template_type=" + template_type, {
        success: function (data) {
            $("#categoryInfo").html(data);
            $('span[data-id="' + curTemplate + '"]').addClass('active_tab');

            var totRows = $("#RowCount").val();

            var totCats = $("#CategoryCount").val();
            $("#btnAddRow").css("display", "");
            if (parseInt(totRows) <= 0) {
                $("#btnAddRow").trigger('click');
            }
            var initialState = $('form').serialize();
            $('#initial_state').val(initialState);
            callCategoryTinyMce();
            callSortable();
        },
        error: function () {
            Swal.fire({
                text: "There was some error performing the AJAX call!",
                icon: "error",
            });
        },
    });
}
function callCategoryTinyMce() {
    tinymce.remove('.phaseCategorytxtDescription');
    tinymce.init({
        selector: '.phaseCategorytxtDescription',
        min_height: 500,
        plugins: [
            "advlist autolink lists link image charmap print preview anchor tinymcespellchecker",
            "searchreplace visualblocks code fullscreen",
            "insertdatetime media table paste",
            "media code"
        ],
        menubar: 'file edit insert view format table tools',
        toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | outdent indent | bullist numlist | spellchecker language spellcheckdialog | custom_action_button ',
        a11y_advanced_options: true,
        image_list: "get-image-list",
        spellchecker_dialog: true,
        smart_paste: true,
        branding: false,
        image_dimensions: false,
        file_picker_callback: function (callback, value, meta) {
            var input = document.createElement('input');
            input.setAttribute('type', 'file');
            if (meta.filetype == 'image') {
                input.setAttribute('accept', 'image/*');
            }
            if (meta.filetype == 'media') {
                input.setAttribute('accept', 'video/*');
            }
            input.onchange = function () {
                var file = this.files[0];
                var fileUrl = window.URL.createObjectURL(file);
                var xhr, formData;
                xhr = new XMLHttpRequest();
                xhr.withCredentials = false;
                xhr.open('POST', '/admin/phase_categories/upload');
                xhr.setRequestHeader("X-CSRF-Token", $('meta[name="csrf-token"]').attr('content'));
                xhr.onload = function () {
                    var json;
                    if (xhr.status != 200) {
                        console.log('HTTP Error: ' + xhr.status);
                        return;
                    }
                    json = JSON.parse(xhr.responseText);
                    if (typeof json.error_message != 'undefined') {
                        document.querySelectorAll('.tox-dialog-wrap').forEach(function (el) {
                            el.style.display = 'none';
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
                    if (!json || typeof json.location != 'string') {
                        console.log('Invalid JSON: ' + xhr.responseText);
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

function callSortable() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $(document).find(".ui-sortable").sortable({
        items: "> tr",
        cancel: "input, select, checkbox, a",
        beforeStop: function (event, ui) {
            callCategoryTinyMce();
        },
        update: function (event, ui) {
            var data = [];
            $.when($(document).find('#tblCatInfo').find('.id-user').each(function (index, item) {
                data.push({
                    id: $(this).val(),
                    index: index,
                })
            })).then(function () {
                $.ajax({
                    url: 'phase_categories/sort',
                    method: 'POST',
                    data: {
                        'data': data
                    },
                    success: function (response) {
                    }
                });
            });
        }
    });
}


$("input.is_display_timeline").change(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        url: "update_timeline_mapping_config",
        type: "POST",
        success: function (response) {

        },
    }).done(function () {
    });
});
