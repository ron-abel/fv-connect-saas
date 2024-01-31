
window.projectTypeSectionList = []

var form_item_names = [];
var fieldId = null;

function toggleFormEligibity(e, form_id, is_active) {
    let dom = new DOMParser();
    $.ajax({
        url: site_url + "/admin/toggle_form_eligibility",
        type: 'POST',
        data: {
            '_token': $('meta[name="csrf-token"]').attr('content'),
            'form_id': form_id,
            'is_active': is_active == 1 ? 0 : 1,
        },
        dataType: 'JSON',
        success: function (res) {
            if (res.success) {
                syn = dom.parseFromString(`<input type="checkbox"
                                    onclick="toggleFormEligibity(this, ${form_id}, ${res.value} )"
                                     ${res.value == 1 ? 'checked' : ''}>`,
                    'text/html');
                e.replaceWith(syn.body.querySelector('input'));
                Swal.fire({
                    text: res.message,
                    icon: "success",
                    iconColor: '#a0de82',
                });
            } else {
                if (is_active == 1) {
                    e.checked = true;
                } else {
                    e.checked = false;
                }
                Swal.fire({
                    text: res.message,
                    icon: "error",
                    iconColor: '#d33'
                });
            }
        },
        error: function () {
            e.checked = true;
            Swal.fire({
                text: res.message,
                icon: "error",
                iconColor: '#d33'
            });
        }
    });
}

function deleteForm(e, id) {
    Swal.fire({
        text: "Are you sure to delete the form?",
        confirmButtonText: "Yes!",
        showCancelButton: true,
        cancelButtonText: "No.",
        confirmButtonColor: '#d33',
        cancelButtonColor: '#B5B5C3'
    }).then((result) => {
        if (result.isConfirmed) {
            //e.parentNode.parentNode.style.display = "none";
            $.ajax({
                url: site_url + "/admin/delete_form",
                type: 'POST',
                data: {
                    '_token': $('meta[name="csrf-token"]').attr('content'),
                    'form_id': id
                },
                dataType: 'JSON',
                success: function (res) {
                    if (res.success) {
                        e.parentNode.parentNode.remove();
                        Swal.fire({
                            text: res.message,
                            icon: "success",
                            iconColor: '#a0de82',
                        });
                    } else {
                        e.parentNode.parentNode.style.display = "block";
                        Swal.fire({
                            text: res.message,
                            icon: "error",
                            iconColor: '#d33'
                        });
                    }
                },
                error: function () {
                    e.parentNode.parentNode.style.display = "block";
                    Swal.fire({
                        text: res.message,
                        icon: "error",
                        iconColor: '#d33'
                    });
                }
            });
        }
    });
}


$(function () {
    if (document.getElementById("logreportrange")) {
        var log_start_date = moment();
        var log_end_date = moment();

        function cb(start, end) {
            $('#logreportrange span').html(start.format('MM/D/YYYY') + ' - ' + end.format('MM/D/YYYY'));
            let export_href = $(".export-custom-log").attr("href");
            export_href = export_href.split("?")[0] + '?log_start_date=' + start.format('YYYY-MM-DD') + '&log_end_date=' + end.format('YYYY-MM-DD');
            $(".export-custom-log").attr("href", export_href);
            BasicDatatablesDataSourceHtml.init(start, end);
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
});

var BasicDatatablesDataSourceHtml = function (start, end) {
    var formLogDatatable = function (start, end) {
        var table = $('#forms_log_datatable');
        table.DataTable({
            responsive: true,
            destroy: true,
            ajax: {
                url: 'form_response_logs',
                type: 'GET',
                data: {
                    'log_start_date': start.format('YYYY-MM-DD'),
                    'log_end_date': end.format('YYYY-MM-DD'),
                },
            },
            columns: [
                { data: 'form_name' },
                { data: 'fv_project_id' },
                { data: 'fv_client_id' },
                { data: 'form_response_values_json' },
                { data: 'created_at', responsivePriority: -1 },
                { data: 'id' },
            ],
            columnDefs: [
                {
                    targets: -1,
                    orderable: false,
                    render: function (dta, type, full, meta) {
                        return '<i data-json="' + encodeURIComponent(full.form_response_values_json) + '" data-toggle="modal" data-target="#logDetails" class="fa fa-eye text-success show-log-details"  style="cursor:pointer;"></i>';
                    },
                },
                {
                    targets: -2,
                    orderable: false,
                    render: function (dta, type, full, meta) {
                        return moment(full.created_at).format('YYYY-MM-DD HH:mm');
                    },
                },
                {
                    targets: -3,
                    orderable: false,
                    render: function (dta, type, full, meta) {
                        let form_response = JSON.parse(full.form_response_values_json);
                        let form_total_item = form_response.length;
                        let form_item_submitted = 0;
                        form_response.forEach(element => {
                            if (element.value) {
                                form_item_submitted++;
                            }
                        });
                        let completion = parseInt(form_item_submitted / form_total_item * 100);
                        return '<div class="progress"><div class="progress-bar" role="progressbar" style="width:' + completion + '%;" aria-valuenow="25" aria-valuemin="0"aria-valuemax="100">' + completion + '%</div></div>';
                    },
                },

            ],
        });
    };
    return {
        init: function (start, end) {
            formLogDatatable(start, end);
        },
    };
}();


$("body").on("click", ".show-log-details", async function () {
    let json_details = JSON.parse(decodeURIComponent($(this).data('json')));
    let table_body = "";
    json_details.forEach(element => {
        table_body += "<tr>";
        table_body += "<td>" + element.label + "</td>";
        table_body += "<td>" + element.value + "</td>";
        table_body += "</tr>";
    });
    $(".log_table_body").html(table_body);
});


const options = {
    disabledActionButtons: ['data', 'save', 'clear'],
    controlPosition: 'left',
    editOnAdd: true,
    stickyControls: {
        enable: true,
        offset: {
            top: 200,
        }
    },
    controlOrder: [
        'text',
        'number',
        'date',
        'file',
        'textarea'
    ],
    disableFields: ['autocomplete', 'button', 'paragraph', 'hidden', 'file', 'checkbox-group'],
    disabledAttrs: ['className', 'description', 'access', 'max', 'maxlength', 'min', 'style', 'value', 'step',
        'multiple', 'other', 'name'
    ],
    disabledSubtypes: {
        textarea: ['tinymce', 'quill'],
        text: ['password', 'email', 'color', 'telephone'],
    },
    onAddField: function (fieldId, formData) {
        let inner_form_name = formData.name;
        let form_type = formData.type;
        var fieldId = fieldId;
        if (form_type.toLowerCase() != 'header' && form_type.toLowerCase() != 'paragraph') {
            if (form_item_names.indexOf(inner_form_name) === -1) {
                form_item_names.push(inner_form_name);
                addMappingDiv(fieldId, formData);
            }
        }
        else {
            // updateSortOrder();
        }
        updateCollectionSectionAttribs();
    },
    replaceFields: [
        {
            type: "radio-group",
            label: "Yes/No Toggle",
            values: [{ label: "Yes", value: "true" }, { label: "No", value: "false" }],
        },
    ],
    inputSets: [
        {
            label: 'Instructions',
            icon: 'I',
            fields: [
                {
                    type: 'paragraph',
                    subtype: 'p',
                    label: 'Instructions',
                    className: ''
                }
            ]
        },
        {
            label: 'Person Link',
            icon: 'I',
            fields: [
                {
                    type: 'text',
                    label: 'Person Link',
                    className: 'form-control person-link-input'
                },
            ]
        },
        {
            label: 'Deadline Date',
            icon: 'I',
            fields: [
                {
                    type: 'date',
                    label: 'Deadline Date',
                    className: 'form-control deadline-date-input'
                },
            ]
        },
        {
            label: 'Document Upload',
            icon: 'I',
            fields: [
                {
                    type: 'file',
                    subtype: 'file',
                    label: 'Document Upload',
                    className: 'form-control documents-input'
                },
            ]
        },
        {
            label: 'Collection Start',
            fields: [
                {
                    type: 'header',
                    subtype: 'h3',
                    label: 'Collection Start',
                    className: 'collection-section-start'
                }
            ]
        },
        {
            label: 'Collection End',
            fields: [
                {
                    type: 'header',
                    subtype: 'h3',
                    label: 'Collection End',
                    className: 'collection-section-end'
                }
            ]
        }
    ]
}

if ($("#form-builder-container").length > 0) {
    var fb = $(document.getElementById('form-builder-container')).formBuilder(options);
    fb.promise.then(formBuilder => {
        // set icon for custom element
        // $('.input-set-control').addClass('formbuilder-icon-header');
        //console.log($('.input-set-control').find('.control-icon')[0]);
        $($('.input-set-control').find('.control-icon')[0]).html('<i class="fa fa-info"></i>');
        $($('.input-set-control').find('.control-icon')[1]).html('<i class="fa fa-user"></i>');
        $($('.input-set-control').find('.control-icon')[2]).html('<i class="fa fa-check"></i>');
        $($('.input-set-control').find('.control-icon')[3]).html('<i class="fa fa-file"></i>');
    });
    // bind sort event
    $(document).on("sortupdate", ".stage-wrap", function (event, ui) {
        if (item_count > 0) {
            if (fb.actions.getData().length > $('.fv-mapping-items .fv-mapping-item').length) {
                updateSortOrder();
            }
            else {
                updateSortOrder();
            }
        }
    });
}

function updateCollectionSectionAttribs() {
    setTimeout(() => {
        var collection_number = 1;
        var collection_item_counter = 1;
        var is_collection = false;
        $('.stage-wrap li').each(function (index, item) {
            // check if field needs collection class
            if ($(item).hasClass('form-field') && $(item).attr('type') != 'header' && $(item).attr('type') != 'paragraph') {
                var elem_id = $(item).find('.prev-holder .form-group');
                elem_id = elem_id[0].classList[elem_id[0].classList.length - 1];
                if (is_collection == true) {
                    $('.' + elem_id).removeAttr('data-is-collection-item').attr('data-is-collection-item', 'yes').attr('data-collection-order', collection_item_counter).attr('data-parent-collection-order', collection_number).attr('data-combined-collection-order', 'parent-' + collection_number + '-child-' + collection_item_counter);
                    collection_item_counter++;
                }
                else {
                    $('.' + elem_id).removeAttr('data-is-collection-item').removeAttr('data-collection-order').removeAttr('data-parent-collection-order').removeAttr('data-combined-collection-order');
                }
            }
            else if ($(item).hasClass('form-field') && $(item).attr('type') == 'header') {
                // get input name and inject as id to sections
                var collection_start = $(item).find('.prev-holder .collection-section-start');
                var collection_end = $(item).find('.prev-holder .collection-section-end');
                if (collection_start.length > 0) {
                    $(collection_start).attr('data-collection-order-start', collection_number);
                    is_collection = true;
                    collection_item_counter = 1;
                }
                if (collection_end.length > 0) {
                    $(collection_end).attr('data-collection-order-end', collection_number);
                    collection_number++;
                    collection_item_counter = 1;
                    is_collection = false;
                }
            }
        });
    }, 500);


    projectSelectionListOptions()
}

var previous_project_type = 0;

function checkIfCollectionItem(type = "", elem_class, value) {
    // get all variables from data
    var is_collection_item = $('.' + elem_class).attr('data-is-collection-item');
    var collection_order = $('.' + elem_class).attr('data-collection-order');
    var parent_collection_order = $('.' + elem_class).attr('data-parent-collection-order');
    var combined_collection_order = $('.' + elem_class).attr('data-combined-collection-order');
    if (is_collection_item == 'yes') {
        if (type == 'section') {
            previous_project_type = value;
        }

        // get first element from collection
        var first_collection_item = $('div[data-combined-collection-order="parent-' + parent_collection_order + '-child-1"]');
        // check if elment is first element itself
        if (combined_collection_order == 'parent-' + parent_collection_order + 'child-1') {
            return { 'isValid': true, 'isCollection': true };
        }
        else {
            // get first element relevant data and compare
            var elem_id = first_collection_item[0].classList[first_collection_item[0].classList.length - 1];
            var project_type = $($('#' + elem_id).find('select.fv_project_type_name')[0]).val();
            var project_section = $($('#' + elem_id).find('select.fv_section_name')[0]).val();
            // find data type within container
            if (type == 'section' && project_type != '' && project_type != value) {
                return { 'isValid': false, 'isCollection': true };
            }
            else if (type == 'field' && project_section != '' && project_section != value) {
                return { 'isValid': false, 'isCollection': true };
            }
            else {
                return { 'isValid': true, 'isCollection': true };
            }
        }
    }
    else {
        if (type == 'section' && form_id == '') {
            if (previous_project_type != 0 && previous_project_type != value) {
                return { 'isValid': false, 'isCollection': false };
            }
            previous_project_type = value;
        }
        return { 'isValid': true, 'isCollection': false };
    }
}

function updateSortOrder() {
    updateSortOrderIds();
    setTimeout(() => {
        var current_mappings = [];
        $('.fv-mapping-items .fv-mapping-item').each(function (index, item) {
            var elem_id = $(item).attr('id');
            current_mappings[elem_id] = item;
        });
        if (Object.keys(current_mappings).length > 0) {
            $('.fv-mapping-items').html('');
            $('.stage-wrap li').each(function (index, item) {
                if ($(item).hasClass('form-field')) {
                    var elem_id = $(item).find('.prev-holder .form-group');
                    elem_id = elem_id[0].classList[elem_id[0].classList.length - 1];
                    $('.fv-mapping-items').append(current_mappings[elem_id]);
                }
            });
            mappingItemOrder();
        }
    }, 500);
}


function updateSortOrderIds() {
    $('.remove.formbuilder-icon-cancel').addClass('remove-option');
    $('.remove-option').removeClass('remove');
    $('.stage-wrap li').each(function (index, item) {
        // add type after label
        var label_elem = $(item).find('.field-label');
        $(item).find('.field_label_span').remove();

        if (label_elem.text() == "Instructions") {
            $(item).addClass('hide-subtype');
            $(label_elem).after('<span class="field_label_span">[instructions]</span>');
        } else if (label_elem.text() == "Collection Section") {
            $(item).addClass('hide-subtype');
            $(label_elem).after('<span class="field_label_span">[Collection Section Title]</span>');
            $(".add-collection-button-div").removeClass("d-none");
        } else {
            $(label_elem).after('<span class="field_label_span">[' + $(item).attr('type') + ']</span>');
        }

        if ($(item).hasClass('form-field') && $(item).attr('type') != 'header' && $(item).attr('type') != 'paragraph') {
            // get input name and inject as id to sections
            var elem_id = $(item).find('.prev-holder .form-group');
            elem_id = elem_id[0].classList[elem_id[0].classList.length - 1];
            if ($('#' + elem_id).length == 0) {
                var counter = 0;
                $('.fv-mapping-items .fv-mapping-item').each(function (index, item) {
                    if ((typeof $(item).attr('id') == "undefined" || $(item).attr('id') == "") && counter == 0) {
                        $(item).attr('id', elem_id);
                        counter++;
                    }
                });
                // $('.fv-mapping-items .fv-mapping-item').eq(counter).attr('id', elem_id);
                // counter++;
            }
        }
    });
    updateCollectionSectionAttribs();
}

setTimeout(() => {
    $('.frmb.stage-wrap.pull-right.ui-sortable').css({
        'min-height': '309px',
        'border': '1px dotted',
        'padding': '2px 5px',
        'border-radius': '5px'
    })
}, 2000);
let forms = null;
let nameValidateFlag = false;
setTimeout(() => {
    $.ajax({
        type: "GET",
        url: site_url + "/admin/get_tenant_forms",
        success: function (response) {
            res = JSON.parse(response);
            if (res.status == 200) {
                forms = res.forms;
                if (form_id) {
                    form = forms.find(form => form.id == form_id);
                    fb.actions.setData(form.form_fields_json);
                    setTimeout(() => {
                        updateSortOrderIds();
                    }, 500);

                }
            }
        },
        error: function (param) {
            Swal.fire({
                text: "You have no forms. Please add new forms for collecting client response",
                icon: "info",
            });
        }
    });
}, 1000);

$('#form-container').submit(function (e) {
    event.preventDefault();
});


$('#save_form').click(function (e) {
    let is_public_form = $('select[name="is_public_form"]').val();
    let create_fv_project = $('input[name="create_fv_project"]').is(":checked") ? 1 : 0;
    let fv_project_type_id = $('select[name="fv_project_type_id"]').val();
    let fv_project_type_id_name = $('select[name="fv_project_type_id"] option:selected').text();
    let success_message = tinymce.get('form_success_message').getContent();
    let sync_existing_fv_project = $('input[name="sync_existing_fv_project"]').is(":checked") ? 1 : 0;
    let fv_project_id = $('select[name="fv_project_id"]').val();
    let fv_project_name = $('select[name="fv_project_id"] option:selected').text();
    let assign_project_name_as = $('select[name="assign_project_name_as"]').val();

    let swal_msg = "You cannot use duplicate form name!";
    let error_project_type = false;
    if (create_fv_project && !fv_project_type_id) {
        error_project_type = true;
        swal_msg = "Please check project type!";
    }

    if (nameValidateFlag || error_project_type) {
        Swal.fire({
            text: swal_msg,
            icon: "warning",
            iconColor: '#faec39',
            confirmButtonColor: '#faec39'
        });
    } else {
        let form = document.getElementById('form-container');
        data = fb.actions.getData();

        form_mapping_enable = [];
        fv_project_type_name = [];
        fv_section_name = [];
        fv_field_name = [];

        $('.fv_project_type_name').each(function (i, obj) {
            fv_project_type_name.push({
                fv_project_type_name: $.trim($(this).find(":selected").text()),
                fv_project_type_id: $.trim($(this).find(":selected").val()),
            });
        });

        $('.fv_section_name').each(function (i, obj) {
            fv_section_name.push({
                fv_section_name: $.trim($(this).find(":selected").text()),
                fv_section_id: $.trim($(this).find(":selected").val()),
            });
        });

        $('.fv_field_name').each(function (i, obj) {
            fv_field_name.push({
                fv_field_name: $.trim($(this).find(":selected").text()),
                fv_field_id: $.trim($(this).find(":selected").val()),
            });
        });

        $('.form_mapping_enable').each(function (i, obj) {
            form_mapping_enable.push({
                form_mapping_enable: $(this).prop("checked") ? 1 : 0
            });
        });

        if (is_public_form == 0) {
            create_fv_project = 0;
            fv_project_type_id = 0;
            fv_project_type_id_name = '';
            sync_existing_fv_project = 0;
            fv_project_id = 0;
            fv_project_name = '';
        }

        if (create_fv_project == 1) {
            fv_project_id = 0;
            fv_project_name = '';
        }

        if (sync_existing_fv_project == 1) {
            fv_project_type_id = 0;
            fv_project_type_id_name = '';
            fv_project_id = 0;
            fv_project_name = '';
        }

        if (create_fv_project == 0) {
            assign_project_name_as = "";
        }

        let form_data = {
            form_id: form.elements['form_id'] ? form.elements['form_id'].value : null,
            name: form.elements['name'].value,
            description: form.elements['description'].value,
            form_data: JSON.stringify(data),
            is_active: form.elements['is_active'].checked ? 1 : 0,
            form_mapping_enable: JSON.stringify(form_mapping_enable),
            fv_project_type_name: JSON.stringify(fv_project_type_name),
            fv_section_name: JSON.stringify(fv_section_name),
            fv_field_name: JSON.stringify(fv_field_name),
            is_public_form: is_public_form,
            create_fv_project: create_fv_project,
            fv_project_type_id: fv_project_type_id,
            fv_project_type_id_name: fv_project_type_id_name,
            success_message: success_message,
            sync_existing_fv_project: sync_existing_fv_project,
            fv_project_id: fv_project_id,
            fv_project_name: fv_project_name,
            assign_project_name_as: assign_project_name_as
        };

        let project_item_count = 0;
        if (is_public_form && create_fv_project) {
            data.forEach(element => {
                let str_label = element.label;
                str_label = str_label.toLowerCase().replace(/\s/g, "");
                if (str_label.includes("firstname") || str_label.includes("lastname") || str_label.includes("phone") || str_label.includes("email")) {
                    project_item_count++;
                }
            });
            if (project_item_count < 4) {
                Swal.fire({
                    title: "To create a new Project from Public Form, the form must include client first name, last name, email, and phone number! Are you sure want to submit without these field?",
                    showDenyButton: true,
                    confirmButtonText: 'Yes',
                    denyButtonText: 'No'
                }).then((result) => {
                    if (result.isConfirmed) {
                        saveFormAjax(form_data);
                    }
                });
            } else {
                saveFormAjax(form_data);
            }
        } else {
            saveFormAjax(form_data);
        }
    }
});


function saveFormAjax(form_data) {
    $.ajax({
        type: "POST",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: site_url + "/admin/save_form_data",
        data: form_data,
        success: function (response) {
            let msg = "You have added a new form successfully";
            if (form_id) {
                msg = "The form updated successfully";
            }
            Swal.fire({
                text: msg,
                icon: "success",
                iconColor: '#a0de82',
            }).then((result) => {
                if (form_id == '') {
                    window.location.replace(site_url + "/admin/forms");
                }
            });
        },
        error: function (param) {
            Swal.fire({
                text: "Something was wrong. Try again later!",
                icon: "error"
            })
        }
    });
}

$('#clear_form').click(function (e) {
    fb.actions.clearFields();
    // clear mapped fields as well
    var map_element = $("div.fv-mapping-item:first").clone();
    $('.fv-mapping-items').addClass('d-none');
    $('.fv-mapping-items').html('');
    item_count = 0;
    form_mappings_count = 0;
    first_add = false;
    form_item_names = [];
    $('.fv-mapping-items').append(map_element);
    $("div.fv-mapping-item:last input[type='checkbox']").prop('checked', false);
    $("div.fv-mapping-item:last").attr('id', '');
    $("div.fv-mapping-item:last select:not(:first)").find('option').remove();
    $("div.fv-mapping-item:last div.form-mapping-row").addClass("d-none");
    $("div.fv-mapping-item:last label.field-no").text("Field #1");
});


function validateFormName(e, id) {
    name = e.value;
    let res;
    if (id) {
        res = forms.find(form => {
            if (form.id != id && name == form.form_name) {
                return true;
            }
        })
    } else {
        res = forms.find(form => {
            if (name == form.form_name) {
                return true;
            }
        })
    }
    if (res) {
        $('#name-duplicate-message').text("You cannot use duplicate form name!");
        nameValidateFlag = true;
    } else {
        $('#name-duplicate-message').text(null);
        nameValidateFlag = false;
    }
}


var first_add = false;
var item_count = 0;

function addMappingDiv(fieldId, formData) {
    item_count++;
    if (item_count >= form_mappings_count) {
        if (!first_add) {
            $(".fv-mapping-items").removeClass("d-none");
            first_add = true;
            updateSortOrderIds();
        } else {
            $("div.fv-mapping-item:first").clone().insertAfter("div.fv-mapping-item:last");
            $("div.fv-mapping-item:last input[type='checkbox']").prop('checked', false);
            $("div.fv-mapping-item:last").attr('id', '');
            $("div.fv-mapping-item:last select:not(:first)").find('option').remove();
            $("div.fv-mapping-item:last div.form-mapping-row").addClass("d-none");
            $("div.fv-mapping-item:last label.field-no").text("Field #" + item_count);

            if (formData.name == "text-20220805014444-project-name") {
                $("div.fv-mapping-item:last input[type='checkbox']").attr("disabled", true);
            }

            // Load section list by project type
            setTimeout(() => {
                let parent_elem_id = $("div.fv-mapping-item:last").attr('id');
                let temp_project_type = $("div.fv-mapping-item:last select:first").val();
                $(".fv_project_type_name").each(function () {
                    if (temp_project_type == '') {
                        temp_project_type = $(this).val();
                    }
                });
                $("div.fv-mapping-item:last select:first").val(temp_project_type);
                if (temp_project_type && parent_elem_id) {
                    let collection_check = checkIfCollectionItem('section', parent_elem_id, temp_project_type);
                    $(".loading").show();
                    $.ajax({
                        url: site_url + "/admin/forms/get_project_type_section_list",
                        type: "GET",
                        data: {
                            project_type_id: temp_project_type,
                            is_collection: collection_check.isCollection
                        },
                        success: function (response) {
                            $("div.fv-mapping-item:last select:eq(1)").html(response.html);
                        },
                        error: function () {
                            $(".loading").hide();
                            alert("Error to Process Your Request! Please try Again!");
                        },
                    }).done(function () {
                        $(".loading").hide();
                    });
                }
            }, 1500);

            updateSortOrderIds();
        }
    }
    // $("div.fv-mapping-item").eq(item_count - 1).attr("id", fieldId);
}


function getSectionList(event) {
    let project_type_id = $(event.target).val();
    let parent_elem = $(event.target).parent().parent();
    // check if collection item
    let collection_check = checkIfCollectionItem('section', $(parent_elem).attr('id'), project_type_id);
    if (collection_check.isValid == true) {
        // let total_item = $(".fv-mapping-item .fv_project_type_name").length;
        // var event_index = $(".fv_project_type_name").index($(event.target));
        // for (let i = (event_index + 1); i < total_item; i++) {
        //     $(".fv_project_type_name").eq(event_index + i).val(project_type_id);
        // }
        $(".loading").show();

        $.ajax({
            url: site_url + "/admin/forms/get_project_type_section_list",
            type: "GET",
            data: {
                project_type_id: project_type_id,
                is_collection: collection_check.isCollection
            },
            success: function (response) {
                $(event.target).closest('select').parent().next().find('.fv_section_name').html(response.html);
                // for (let j = (event_index + 1); j < total_item; j++) {
                //     $(".fv_section_name").eq(event_index + j).html(response.html);
                // }
            },
            error: function () {
                $(".loading").hide();
                alert("Error to Process Your Request! Please try Again!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    }
    else {
        Swal.fire({
            text: "Please select same project type!",
            icon: "warning",
            iconColor: '#faec39',
            confirmButtonColor: '#faec39'
        });
        $(event.target).val('');
    }
}

function getFieldType(elem) {
    let field_type = "";
    let elem_id = elem.attr('id');
    if (typeof elem_id != 'undefined') {
        let input_elem_id = elem_id.replace('field-', '');
        if ($('#' + input_elem_id).length > 0) {
            if ($('#' + input_elem_id).hasClass('person-link-input')) {
                field_type = ['PersonLink'];
            }
            else if ($('#' + input_elem_id).hasClass('deadline-date-input')) {
                field_type = ['Deadline'];
            }
            else if ($('#' + input_elem_id).hasClass('documents-input')) {
                field_type = ['SingleDoc', 'DocList'];
            }
        }
    }
    return field_type;
}

function getFieldList(event) {
    let project_section_selector = $(event.target).val();
    let parent_elem = $(event.target).parent().parent();
    // check if collection item
    let collection_check = checkIfCollectionItem('field', $(parent_elem).attr('id'), project_section_selector);

    if (collection_check.isValid == true) {
        let project_type_id = $('select[name=fv_project_type_id]').val();
        let currentIndex = $(event.target).parent().parent().index();
        let currentFormData = fb.actions.getData();
        let ftype = currentFormData[currentIndex].type;
        let is_boolean_value = false;

        let match_index = -1;
        let match_object = {};
        $.each(currentFormData, function (key, value) {
            match_index++;
            if (value.type == "header" || value.type == "paragraph") {
                match_index--;
            }
            if (match_index == currentIndex) {
                ftype = value.type;
                match_object = value;
                return false;
            }
        });


        if (ftype == 'radio-group') {
            // let cvalues = currentFormData[currentIndex].values;
            let cvalues = match_object.values;
            cvalues.forEach(element => {
                if (element.value == 'true' || element.value == 'false') {
                    is_boolean_value = true;
                }
            });
        }

        if (is_boolean_value && ftype == 'radio-group') {
            ftype = 'radio-group-boolean';
        }

        $(".loading").show();
        $.ajax({
            url: site_url + "/admin/forms/get_project_section_field",
            type: "GET",
            data: {
                form_item_type: ftype,
                project_type_id: project_type_id,
                project_section_selector: project_section_selector,
                field_type: getFieldType(parent_elem)
            },
            success: function (response) {
                $(event.target).closest('select').parent().next().find('.fv_field_name').html(response.html);
            },
            error: function () {
                $(".loading").hide();
                alert("Error to Process Your Request! Please try Again!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    }
    else {
        Swal.fire({
            text: "Please select same section for same collection items!",
            icon: "warning",
            iconColor: '#faec39',
            confirmButtonColor: '#faec39'
        });
        $(event.target).val('');
    }
}

$("body").on("click", ".copy-mapping", async function () {
    $(this).parents().parents().parents(".fv-mapping-item").clone().insertAfter($(this).parents().parents().parents(".fv-mapping-item"));
});

$("body").on("click", ".remove-option", async function () {
    var _self = $(this);
    Swal.fire({
        title: 'Are you sure want to delete?',
        showDenyButton: true,
        confirmButtonText: 'Yes',
        denyButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            _self.parent().remove();
        }
    });
});

$("body").on('DOMSubtreeModified', "ol.sortable-options", function () {
    setTimeout(() => {
        $('.remove.formbuilder-icon-cancel').addClass('remove-option');
        $('.remove-option').removeClass('remove');
    }, 1000);
});

$("body").on("click", ".remove", async function () {
    Swal.fire({
        title: 'Are you sure want to delete?',
        showDenyButton: true,
        confirmButtonText: 'Yes',
        denyButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            let itemlist = $('.fv-mapping-items');
            let len = $(itemlist).children().length;
            if (len > 1) {
                $(this).parents().parents().parents(".fv-mapping-item").remove();
                mappingItemOrder();
            }
        }
    });
});

$("body").on("click", ".moveup", async function () {
    let itemlist = $('.fv-mapping-items');
    let selected = $(this).parents().parents().parents().index();
    if (selected > 0) {
        jQuery($(itemlist).children().eq(selected - 1)).before(jQuery($(itemlist).children().eq(selected)));
    }
});


$("body").on("click", ".movedown", async function () {
    let itemlist = $('.fv-mapping-items');
    let len = $(itemlist).children().length;
    let selected = $(this).parents().parents().parents().index();
    if (selected < len) {
        jQuery($(itemlist).children().eq(selected + 1)).after(jQuery($(itemlist).children().eq(selected)));
    }
});

function copyFormPublicLink(element, link_text) {
    var $temp = $("<input>");
    $("body").append($temp);
    $temp.val(link_text).select();
    document.execCommand("copy");
    $temp.remove();

    $('.copy-button').attr('title', 'Copy')
        .tooltip('_fixTitle');

    $(element).attr('title', 'Copied')
        .tooltip('_fixTitle')
        .tooltip('show');
}

function showHideFormMappingRow(event, _this) {
    let check_value = _this.checked;

    let is_public_form = $('select[name="is_public_form"]').val();
    let create_fv_project = $('input[name="create_fv_project"]').is(":checked") ? 1 : 0;
    let fv_project_type_id = $('select[name="fv_project_type_id"]').val();

    if (is_public_form && create_fv_project && !fv_project_type_id) {
        Swal.fire({
            text: "Please check project type!",
            icon: "warning",
            iconColor: '#faec39',
            confirmButtonColor: '#faec39'
        });
        $(event.target).prop('checked', false);
        return;
    }

    if (check_value) {
        $(event.target).closest(".fv-mapping-item").children(".form-mapping-row").removeClass('d-none');
    } else {
        $(event.target).closest(".fv-mapping-item").children(".form-mapping-row").addClass('d-none');
    }
}


$("body").on("change", "select[name='fv_project_type_id']", async function () {
    let fv_project_type_id = $(this).val();
    $(".fv_project_type_name").find("option").addClass("d-none");
    $(".fv_project_type_name").find("option[value='']").removeClass("d-none");
    $(".fv_project_type_name").find("option[value='" + fv_project_type_id + "']").removeClass("d-none");
});

$("body").on("click", "a.delete-confirm", async function () {
    let del_type = $(this).closest("li.form-field").attr('type');
    let form_item_index = $(this).closest("li.form-field").index();
    if (del_type != 'header' && del_type != 'paragraph') {
        $("div.fv-mapping-items div.fv-mapping-item:eq(" + form_item_index + ")").remove();
    }
    mappingItemOrder();
});

function mappingItemOrder() {
    let row = 1;
    $("div.fv-mapping-item").each(function () {
        $(this).find('.field-no').text("Field #" + row++);
    });
}

$("body").on("change", "select[name='is_public_form']", async function () {
    let is_public_form = $(this).val();
    if (is_public_form == 1) {
        $(".create-project-checkbox-div").removeClass("d-none");
        $(".sync-project-checkbox-div").removeClass("d-none");
        $('.assign-project-name-div').removeClass('d-none')
    } else {
        $(".create-project-checkbox-div").addClass("d-none");
        $(".sync-project-checkbox-div").addClass("d-none");
        $('.assign-project-name-div').addClass('d-none')
    }
});


$("body").on("click", "input[name='create_fv_project'], input[name='sync_existing_fv_project']", async function () {
    createProjectItem();
});

function createProjectItem() {
    let create_fv_project = $("input[name='create_fv_project']").is(":checked");
    let sync_existing_fv_project = $("input[name='sync_existing_fv_project']").is(":checked");

    $(".assign-project-name-div").addClass("d-none");

    let first_name_json =
    {
        "type": "text",
        "required": true,
        "label": "First Name",
        "placeholder": "Enter your first name",
        "className": "form-control",
        "name": "text-20221029235555-first-name",
        "subtype": "text"
    };
    let last_name_json = {
        "type": "text",
        "required": true,
        "label": "Last Name",
        "placeholder": "Enter your last name",
        "className": "form-control",
        "name": "text-20221029235555-last-name",
        "subtype": "text"
    };
    let phone = {
        "type": "text",
        "required": true,
        "label": "Phone",
        "placeholder": "Enter your phone number",
        "className": "form-control",
        "name": "text-20221029235555-phone",
        "subtype": "text"
    };
    let email = {
        "type": "text",
        "required": true,
        "label": "Email",
        "placeholder": "Enter your email",
        "className": "form-control",
        "name": "text-20221029235555-email",
        "subtype": "text"
    };

    let current_data = fb.actions.getData();
    if (create_fv_project || sync_existing_fv_project) {
        $(".project-div").addClass("d-none");
        let first_name_status = false;
        let last_name_status = false;
        let phone_status = false;
        let email_status = false;

        current_data.forEach(element => {
            let str_label = element.label;
            str_label = str_label.toLowerCase().replace(/\s/g, "");
            if (str_label.includes("firstname")) {
                first_name_status = true;
            } else if (str_label.includes("lastname")) {
                last_name_status = true;
            } else if (str_label.includes("phone")) {
                phone_status = true;
            } else if (str_label.includes("email")) {
                email_status = true;
            }
        });

        if (!first_name_status) {
            current_data.push(first_name_json);
        }
        if (!last_name_status) {
            current_data.push(last_name_json);
        }
        if (!phone_status) {
            current_data.push(phone);
        }
        if (!email_status) {
            current_data.push(email);
        }
        // item_count = form_item_names.length;
        fb.actions.setData(current_data);
        updateSortOrder();
    } else {
        let new_data = [];
        form_item_names = [];
        current_data.forEach(element => {
            if (element.name != "text-20221029235555-first-name" && element.name != "text-20221029235555-last-name" && element.name != "text-20221029235555-phone" && element.name != "text-20221029235555-email") {
                new_data.push(element);
                form_item_names.push(element.name);
            }
        });
        // item_count = form_item_names.length - 1;
        fb.actions.setData(new_data);
        updateSortOrder();
        $(".project-div").removeClass("d-none");
    }
}


$("body").on("click", "input[name='create_fv_project']", async function () {
    if ($("input[name='create_fv_project']").is(":checked")) {
        $("input[name='sync_existing_fv_project']").prop("checked", false);
        $(".assign-project-name-div").removeClass("d-none");
    }
});

$("body").on("click", "input[name='sync_existing_fv_project']", async function () {
    if ($("input[name='sync_existing_fv_project']").is(":checked")) {
        $("input[name='create_fv_project']").prop("checked", false);
    }
});

$(document).ready(function () {

    let newForm = $('.container').attr('data-is-new-form')

    if (newForm) {
        $('select[name=is_public_form]').change();
        //$('input[name=create_fv_project]').prop('checked', true);

        setTimeout(() => {
            $('input[name=create_fv_project]').trigger("click");
        }, 1000);
    }

});


$("body").on("change", "select[name='assign_project_name_as']", async function () {
    let assign_project_name_as = $("select[name='assign_project_name_as']").val();

    let project_name_json = {
        "type": "text",
        "required": true,
        "label": "Project Name",
        "placeholder": "Enter Project Name",
        "className": "form-control",
        "name": "text-20220805014444-project-name",
        "subtype": "text"
    };

    let current_data = fb.actions.getData();
    if (assign_project_name_as == 'Map a Field Value') {
        let project_name_status = false;
        current_data.forEach(element => {
            if (element.name == 'text-20220805014444-project-name') {
                project_name_status = true;
            }
        });
        if (!project_name_status) {
            current_data.push(project_name_json);
        }
        fb.actions.setData(current_data);
    } else {
        let new_data = [];
        form_item_names = [];
        current_data.forEach(element => {
            if (element.name != "text-20220805014444-project-name") {
                new_data.push(element);
                form_item_names.push(element.name);
            }
        });
        fb.actions.setData(new_data);
    }
    updateSortOrder();
});


$("body").on("click", "a[type='copy'].copy-button", async function () {
    addMappingDiv();
});

$(document).ready(function () {
    $(document).on('keyup', '.option-label', function () {
        var _self = $(this);
        var value = _self.val();
        _self.next('.option-value').val(value);
    });
    // init success message tinymce
    tinymce.remove('.form_success_message');
    tinymce.init({
        selector: '.form_success_message',
        min_height: 250,
        plugins: [
            "advlist autolink lists link image charmap print preview anchor tinymcespellchecker",
            "searchreplace visualblocks code fullscreen",
            "insertdatetime media table paste",
            "media code"
        ],
        menubar: 'file edit insert view format table tools',
        toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | outdent indent | spellchecker language spellcheckdialog | custom_action_button',
        spellchecker_dialog: true,
        smart_paste: true,
        branding: false,
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
                        Swal.fire({
                            text: json.error_message,
                            icon: "error"
                        });
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
                formData.append('file', file);
                xhr.send(formData);
            };
            input.click();
        },
        setup: function (editor) {
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
});


$('#add-collection').click(function (e) {
    let collection_item =
    {
        type: 'header',
        subtype: 'h3',
        label: 'Collection Start',
        className: 'collection-section-start'
    };
    let current_data = fb.actions.getData();
    current_data.push(collection_item);
    fb.actions.setData(current_data);
});



$("body").on("change", "select[name='fv_project_type_id']", async function () {
    let project_type_id = $(this).val();
    getProjectSectionList(project_type_id);

    /*
    $(".loading").show();
    $.ajax({
        url: site_url + "/admin/forms/get_project_list_by_project_type",
        type: "GET",
        data: {
            project_type_id: project_type_id,
        },
        success: function (response) {
            $("select[name='fv_project_id']").html(response.html);

            getProjectSectionList(project_type_id)
        },
        error: function () {
            $(".loading").hide();
            alert("Error to Process Your Request! Please try Again!");
            $(".loading").hide();
        },
    }); */


});


async function getProjectSectionList(projectTypeId) {
    $.ajax({
        type: "GET",
        url: site_url + "/admin/forms/get_project_type_section_list",
        data: { project_type_id: projectTypeId },
        dataType: "json",
        success: function (response) {

            window.projectTypeSectionList = response.sections
            projectSelectionListOptions(true)

            $(".loading").hide();

        }
    });
}


function projectSelectionListOptions(changeProjectSectionObject = false) {

    let isCollectionOptions = '<option value="">Choose Collection Section</option>';
    let noCollectionOptions = '<option value="">Choose Static Section</option>';

    // data-is-collection-item
    $.each(window.projectTypeSectionList, function (indexInArray, valueOfElement) {
        if (valueOfElement.isCollection) {
            isCollectionOptions += '<option value="' + valueOfElement.sectionSelector + '">' + valueOfElement.name + '</option>'
        }
        else {
            noCollectionOptions += '<option value="' + valueOfElement.sectionSelector + '">' + valueOfElement.name + '</option>'
        }
    });


    $.each($('.fv-mapping-item'), function (indexInArray, valueOfElement) {

        setTimeout(() => {

            let formInputInCollection = $('.' + $(this).attr('id')).attr('data-is-collection-item') == 'yes' ? 'true' : 'false';

            if (window.projectTypeSectionList.length > 0) {
                if (changeProjectSectionObject == false && $(this).attr('data-is-collection-options') == formInputInCollection) return;

                if (formInputInCollection === 'true') {
                    $(this).attr('data-is-collection-options', true)
                    $(this).find('.fv_section_name').html(isCollectionOptions)
                }
                else {
                    $(this).attr('data-is-collection-options', false)
                    $(this).find('.fv_section_name').html(noCollectionOptions)
                }
            }

        }, 500)

    });

}



$("body").on("change", ".fv_field_name", async function () {
    let field_val = $(this).find(':selected').text();
    let parent_elem = $(this).parent().parent();
    let file_input_name = $(parent_elem).attr('id').replace('field-', '');
    if ($('input[name="' + file_input_name + '"]').hasClass('documents-input')) {
        if (field_val.includes('DocList')) {
            $('input[name="' + file_input_name + '"]').addClass('multiple-files');
        }
        else {
            $('input[name="' + file_input_name + '"]').removeClass('multiple-files');
        }
    }
});




$('body').on('click', '.del-button', function () {
    updateCollectionSectionAttribs()
});
