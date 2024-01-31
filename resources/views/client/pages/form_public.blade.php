@extends('client.layouts.default')

@section('title', 'VineConnect Client Portal - Client Public Form')

@section('content')
    <link href="{{ asset('uppy/uppy.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('css/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />

    <div class="overlay loading"></div>
    <div class="spinner-border text-primary loading" role="status">
        <span class="sr-only">Loading...</span>
    </div>
    <div class="client-portal-body">
        <div class="accordion-started accordion-bral row">
            <div class="w-100">
                <input class="ac-input" id="ac-{{ 1 }}" name="accordion-1" type="radio" checked>
                <label class="ac-label bg-title"
                    for="ac-{{ 1 }}"><span>{{ isset($form) ? $form->form_name : null }}</span>
                </label>
                @if (isset($config_details->logo))
                    <a href="javascript:window.location.href=window.location.href"
                        class="d-flex align-items-center form-logo-bg">
                        <img src="{{ asset('uploads/client_logo/' . $config_details->logo) }}" alt="{{ __('Logo') }}"
                            class="login-logo">
                    </a>
                @else
                    <a href="javascript:window.location.href=window.location.href"
                        class="d-flex align-items-center form-logo-bg">
                        <img src="{{ asset('img/client/vineconnect_logo.png') }}" alt="VineConnect Logo" class="login-logo">
                    </a>
                @endif
                <div class="article ac-content">
                    @if ($form_assign)
                        <div class="alert alert-success" role="alert">This form was already assigned to the other FileVine
                            Project! Please ask to the support team!</div>
                    @else
                        <section class="form-area">
                            @if (!$is_submitted['success'])
                                <form id="show-form"></form>
                            @else
                                <div class="alert alert-warning" role="alert">You submitted a form response on
                                    {{ $is_submitted['timestamp'] }}. <a href="{{ $current_url }}?refresh=1">Click here to
                                        submit another.</a></div>
                            @endif
                        </section>
                    @endif
                    @include('client.includes.footer_copyright')
                </div>
            </div>
        </div>

        <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>
        <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
        <script src="https://formbuilder.online/assets/js/form-builder.min.js"></script>
        <script src="https://formbuilder.online/assets/js/form-render.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.26/dist/sweetalert2.all.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.8/js/select2.min.js" defer></script>
        <script src="{{ asset('js/select2.js') }}"></script>
        @if (!$is_submitted['success'])
            <script>
                var form_id = "{{ isset($form) ? $form->id : null }}";
                var site_url = "{{ url('/') }}";
                const fRender = document.getElementById("show-form");
                var collection_item_data = {};
                var formData = [];
                var form_fields = [];
                var submit_btn = null;
                var collection_create_button = null;
                var mutliselectformname = [];

                $.ajax({
                    url: site_url + '/form/' + form_id,
                    type: 'GET',
                    success: function(response) {
                        form_fields = JSON.parse(response.data.form_fields_json);
                        mutliselectformname = JSON.parse(response.mutliselectformname);
                        submit_btn = {
                            "type": "button",
                            "label": "Submit",
                            "subtype": "submit",
                            "className": "btn-primary btn",
                            "access": false,
                            "style": "primary",
                            "name": "button-1660243936464-0"
                        }

                        // Get Collection Item Data
                        let show_button = false;
                        let item_push = false;
                        let collection_item_index = 0;
                        let item_index = 0;
                        let multiselectindex = 0;
                        // $.each(form_fields, function(key, value) {
                        for (let item_counter = 0; item_counter < form_fields.length; item_counter++) {
                            let value = form_fields[item_counter];
                            if (value.type == 'select' && mutliselectformname.indexOf(value.name) != -1) {
                                value.multiple = "multiple";
                                value.style = "width: 100%";
                                value.className = value.className + " " + value.name + " select2 multiselecttwo-" +
                                    multiselectindex;
                                multiselectindex++;
                            }
                            //
                            if (value.className == "collection-section-start") {
                                show_button = true;
                                item_push = true;
                            }
                            if (item_push) {
                                if (collection_item_data.hasOwnProperty('collection-' + collection_item_index)) {
                                    collection_item_data['collection-' + collection_item_index]['data'].push(value);
                                } else {
                                    collection_item_data['collection-' + collection_item_index] = {
                                        'data': [value],
                                        'button': '',
                                        'position': 0
                                    };
                                }
                            }
                            if (value.className == "collection-section-end") {
                                item_push = false;
                                if (show_button) {
                                    collection_create_button = {
                                        "type": "button",
                                        "label": "<i class='fa fa-plus'></i> &nbsp; Add Another Item",
                                        "subtype": "button",
                                        "className": "btn-link add-collection-item collection-" +
                                            collection_item_index,
                                        "name": "add_collection_item"
                                    }
                                    collection_item_data['collection-' + collection_item_index].button =
                                        collection_create_button;
                                    collection_item_data['collection-' + collection_item_index].position = item_index +
                                        1;
                                    form_fields.splice(item_index + 1, 0, collection_create_button);
                                }
                                collection_item_index++;
                                show_button = false;
                            }
                            item_index++;
                        }
                        // });

                        form_fields.push(submit_btn);
                        jQuery(function($) {
                            let formData = JSON.stringify(form_fields);
                            $(fRender).formRender({
                                formData
                            });
                            setTimeout(() => {
                                traverseThroughFilesInputs();
                                for (let i = 0; i < multiselectindex; i++) {
                                    $('.multiselecttwo-' + i).select2({
                                        placeholder: 'Select'
                                    });
                                }
                            }, 500);
                        });
                    }
                });


                $("body").on("click", ".add-collection-item", async function() {
                    let new_form_data = [];
                    let total_items = form_fields.length;
                    let processed_items = 0;
                    // get item index for collection
                    var collection_index = $(this)[0].classList[$(this)[0].classList.length - 1];
                    var collection_length = collection_item_data[collection_index].data.length;
                    var collection_postion = 0;
                    // check for position
                    $.each(form_fields, function(key, value) {
                        if (value.hasOwnProperty('className')) {
                            var current_classes = value.className.split(' ');
                            if (current_classes[current_classes.length - 1] == collection_index) {
                                return false;
                            }
                        }
                        collection_postion++;
                    });
                    var collection_data = collection_item_data[collection_index].data;
                    // push collection elements into form elements
                    for (let index = 0; index < collection_length; index++) {
                        form_fields.splice(collection_postion, 0, collection_data[index]);
                        collection_postion++;
                    }

                    var serialized_form = $('#show-form').serializeArray();
                    let formData = JSON.stringify(form_fields);
                    $(fRender).formRender({
                        formData
                    });
                    setTimeout(() => {
                        traverseThroughFilesInputs();
                    }, 500);
                    // now rerender the data if any
                    var pushed_elems = {};
                    $.each(serialized_form, function(key, value) {
                        var elem_type = $($('[name="' + value.name + '"]')[0]).attr('type');
                        if (pushed_elems.hasOwnProperty(value.name)) {
                            pushed_elems[value.name] += 1;
                            // check if radio or checkbox
                            if (elem_type == 'radio' || elem_type == 'checkbox') {
                                $('[name="' + value.name + '"]:eq(' + (pushed_elems[value.name] - 1) + ')')
                                    .prop('checked', (value.value == 'true' ? true : false));
                            } else {
                                $('[name="' + value.name + '"]:eq(' + (pushed_elems[value.name] - 1) + ')').val(
                                    value.value);
                            }
                        } else {
                            pushed_elems[value.name] = 1;
                            // check if radio or checkbox
                            if (elem_type == 'radio' || elem_type == 'checkbox') {
                                $('[name="' + value.name + '"]:eq(0)').prop('checked', (value.value == 'true' ?
                                    true : false));
                            } else {
                                $('[name="' + value.name + '"]:eq(0)').val(value.value);
                            }
                        }
                    });
                });

                $('#show-form').submit(function(e) {
                    e.preventDefault();
                    const formData = [];
                    const files = {};
                    const mainData = new FormData();
                    const inputBinding = $(this).serializeArray();
                    let multiselect_names = [];
                    $.each(inputBinding, function(i, field) {
                        let field_name = field.name;
                        if (field_name.indexOf("[]") == -1) {
                            let label = $(`label[for=${field.name}]`).text();
                            if (label[label.length - 1] == '*') {
                                label = label.slice(0, -1)
                            }
                            field['label'] = label;
                            formData.push(field);
                        } else {
                            field_name = field_name.replace("[]", "");
                            if (multiselect_names.indexOf(field_name) == -1) {
                                multiselect_names.push(field_name);
                                field['name'] = field_name;
                                field['label'] = field_name;
                                field['value'] = $("." + field_name).val();
                                formData.push(field);
                            }
                        }
                    });

                    mainData.append('content', JSON.stringify({
                        form_id,
                        response: formData
                    }));

                    if (Object.keys(uploaders).length > 0) {
                        $.each(Object.keys(uploaders), function(j, file_id) {
                            let file_field_name = $('#' + file_id).attr('name');
                            let file_field_index = (!files[file_field_name] ? 0 : files[file_field_name].length);
                            if (uploaders[file_id].getFiles().length > 0) {
                                if (!files[file_field_name]) {
                                    files[file_field_name] = [];
                                }
                                if (!files[file_field_name][file_field_index]) {
                                    files[file_field_name][file_field_index] = [];
                                }
                                $.each(uploaders[file_id].getFiles(), function(k, file) {
                                    mainData.append('documents[' + file_field_name + '][' +
                                        file_field_index + '][]', file.data);
                                    files[file_field_name][file_field_index].push(file.data);
                                });
                            }
                        });
                    }
                    $(".loading").show();
                    $.ajax({
                        type: "POST",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        processData: false,
                        contentType: false,
                        cache: false,
                        url: site_url + "/share/views/open/form/save",
                        // data: JSON.stringify({
                        //     form_id,
                        //     response: formData,
                        //     documents: files
                        // }),
                        data: mainData,
                        success: function(response) {
                            $(".loading").hide();
                            if (response.success) {
                                Swal.fire({
                                    text: "Your response has been submitted successfully!",
                                    icon: "success",
                                    iconColor: '#42ba96',
                                    confirmButtonColor: "#3b6dfd"
                                }).then((result) => {
                                    $('.form-area').html(response.message);
                                });
                            } else {
                                if (response.hasOwnProperty('is_submitted')) {
                                    Swal.fire({
                                        text: response.message,
                                        icon: "warning",
                                        iconColor: '#f2c010',
                                        confirmButtonColor: "#3b6dfd"
                                    }).then((result) => {
                                        $('.form-area').html(
                                            '<div class="alert alert-warning" role="alert">' +
                                            response.message1 + '</div>'
                                        );
                                    });
                                } else {
                                    Swal.fire({
                                        title: "Something went wrong. Try again later!",
                                        text: response,
                                        icon: "warning",
                                        iconColor: '#f2c010',
                                        confirmButtonColor: "#3b6dfd"
                                    });
                                }
                            }
                        },
                        error: function(xhr, status, error) {
                            $(".loading").hide();
                            Swal.fire({
                                title: "Something went wrong. Try again later!",
                                text: xhr.responseText,
                                icon: "warning",
                                iconColor: '#f2c010',
                                confirmButtonColor: "#3b6dfd"
                            })
                        }
                    });
                });
            </script>
        @endif

        <style>
            body {
                font-size: 14px !important;
            }

            .msg-popup-close {
                color: #fafafa00;
                background: red !important;
                padding: 1px 6px 6px !important;
                border-radius: 50%;
                font-size: 16px;
                outline: none;
            }

            /* custom form styles */
            #show-form {
                width: 100%;
                background: #fff;
                border-radius: 20px;
            }

            .rendered-form {
                width: 75%;
                margin: 0 auto;
                padding: 20px 10px
            }

            .form-group.formbuilder-button .btn {
                min-width: 30%;
                line-height: 30px;
                font-size: 18px;
                letter-spacing: 0.7px;
                border-radius: 5px;
                background: #55a9e2
            }

            .form-group .form-control {
                font-size: 16px;
                border: none;
                border-radius: 7px;
                background: #ecf2f6;
                height: 45px !important;
            }

            .form-group label {
                font-size: 14px;
            }

            .rendered-form textarea.form-control {
                min-height: 120px !important;
            }

            .formbuilder-file.form-group .form-control {
                background: transparent;
            }

            .accordion-started.accordion-bral .ac-label {
                pointer-events: none;
            }

            .alert-success,
            .alert-warning {
                font-size: 16px;
            }

            .overlay {
                position: fixed;
                top: 0;
                left: 0;
                z-index: 2;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, .7);
                transition: .3s linear;
                z-index: 1000;
            }

            .loading {
                display: none;
            }

            .spinner-border.loading {
                position: fixed;
                top: 48%;
                left: 48%;
                z-index: 1001;
                width: 3rem;
                height: 3rem;
            }

            @-webkit-keyframes spinner-border {
                to {
                    -webkit-transform: rotate(360deg);
                    transform: rotate(360deg);
                }
            }

            @keyframes spinner-border {
                to {
                    -webkit-transform: rotate(360deg);
                    transform: rotate(360deg);
                }
            }

            .spinner-border {
                width: 2rem;
                height: 2rem;
                vertical-align: text-bottom;
                border: 0.25em solid currentColor;
                border-right-color: transparent;
                border-radius: 50%;
                -webkit-animation: 0.75s linear infinite spinner-border;
                animation: 0.75s linear infinite spinner-border;
            }

            .field-add_collection_item {
                text-align: right !important;
            }

            .add-collection-item {
                cursor: pointer;
                text-decoration: none;
            }

            .form-logo-bg {
                background-color: #333333;
                box-shadow: 0px 1px 12px 6px rgba(0, 26, 255, 0.03);
                padding-top: 1% !important;
                padding-bottom: 1% !important;
                border-radius: 20px;
                margin-bottom: 22px;
            }
        </style>

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.26/dist/sweetalert2.all.min.js"></script>
        <script src="{{ asset('uppy/uppy.bundle.js') }}"></script>
        <script>
            let uploaders = {};
            $(document).ready(function() {
                $(document).on('click', '.uppy-remove-thumbnail', function() {
                    let target = $(this).attr('data-target');
                    let file_id = $(this).attr('data-id');
                    let instance = uploaders[target];
                    instance.removeFile(file_id);
                    $('div[data-id="' + file_id + '"]').remove();
                });
            });

            function traverseThroughFilesInputs() {
                let temp_uploaders = Object.assign({}, uploaders);
                uploaders = {};
                $('.documents-input').each(function(key, value) {
                    let elem = $(value);
                    let id = elem.attr('id');
                    $(this).removeAttr('required');
                    if (!Object.prototype.hasOwnProperty.call(uploaders, id)) {
                        elem.after('<div class="uppy col-md-12 p-0" id="' + id +
                            '-uploader"><div class="uppy-drag"></div><div class="uppy-status mt-2 mb-3"></div><div class="uppy-thumbnails d-flex row col-md-6 mx-auto"></div></div>'
                        );
                        uploaders[id] = initDocumentUploaders('#' + id + '-uploader');
                        $('#' + id).hide();
                        if (Object.prototype.hasOwnProperty.call(temp_uploaders, id)) {
                            if (temp_uploaders[id].getFiles().length > 0) {
                                $.each(temp_uploaders[id].getFiles(), function(k, file) {
                                    uploaders[id].addFile(file);
                                });
                            }
                        }
                    } else if (Object.prototype.hasOwnProperty.call(uploaders, id)) {
                        let updated_id = id + '-' + key;
                        elem.attr('id', updated_id);
                        elem.after('<div class="uppy col-md-12 p-0" id="' + updated_id +
                            '-uploader"><div class="uppy-drag"></div><div class="uppy-status mt-2 mb-3"></div><div class="uppy-thumbnails d-flex row col-md-6 mx-auto"></div></div>'
                        );
                        uploaders[updated_id] = initDocumentUploaders('#' + updated_id + '-uploader');
                        $('#' + updated_id).hide();
                        if (Object.prototype.hasOwnProperty.call(temp_uploaders, updated_id)) {
                            if (temp_uploaders[updated_id].getFiles().length > 0) {
                                $.each(temp_uploaders[updated_id].getFiles(), function(k, file) {
                                    uploaders[updated_id].addFile(file);
                                });
                            }
                        }
                    }
                });
            }

            function initDocumentUploaders(id) {
                var trimmed_id = id.replace('#', '').replace('-uploader', '');
                var uppyDrag = Uppy.Core({
                    autoProceed: false,
                    handle: false,
                    restrictions: {
                        maxFileSize: 20000000, // 20mb
                        maxNumberOfFiles: 5,
                        minNumberOfFiles: 1,
                        allowedFileTypes: ['image/*', 'video/*', 'application/*']
                    }
                });

                uppyDrag.use(Uppy.DragDrop, {
                    target: id + ' .uppy-drag',
                    locale: {
                        strings: {
                            dropHereOr: 'Drop files here or %{browse}',
                            browse: 'choose files',
                        },
                    }
                });

                uppyDrag.use(Uppy.StatusBar, {
                    target: id + ' .uppy-status',
                    hideAfterFinish: true,
                    showProgressDetails: true,
                    hideUploadButton: true,
                    hideRetryButton: true,
                    hidePauseResumeButton: true,
                    hideCancelButton: true,
                    doneButtonHandler: null,
                    locale: {},
                });

                uppyDrag.on('file-added', (file) => {
                    var allowed_extensions = ['png', 'jpg', 'jpeg', 'gif', 'svg', 'mp4', 'flv', 'webp', 'pdf', 'doc',
                        'docx', 'xls', 'xlsx'
                    ];
                    if ($.inArray(file.extension, allowed_extensions) == -1) {
                        uppyDrag.removeFile(file.id);
                        return;
                    }
                    var imagePreview = "";
                    var thumbnail_inner = "";
                    if ((/image/).test(file.type)) {
                        thumbnail_inner = '<img src="" style="width:60px;" />';
                        // thumbnail_inner = '<i class="fa fa-image" style="font-size: 20px;"></i>';
                    } else if ((/video/).test(file.type)) {
                        thumbnail_inner = '<i class="fa fa-video" style="font-size: 20px;"></i>';
                    } else if ((/application/).test(file.type)) {
                        thumbnail_inner = '<i class="fa fa-file" style="font-size: 20px;"></i>';
                    }
                    var thumbnail = '<div class="uppy-thumbnail col-sm-2">' + thumbnail_inner + '</div>';

                    var sizeLabel = "bytes";
                    var filesize = file.size;
                    if (filesize > 1024) {
                        filesize = filesize / 1024;
                        sizeLabel = "kb";
                        if (filesize > 1024) {
                            filesize = filesize / 1024;
                            sizeLabel = "MB";
                        }
                    }
                    imagePreview +=
                        '<div class="uppy-thumbnail-container p-3 row col-md-12 alert alert-success" data-id="' + file
                        .id + '">' + thumbnail + ' <span class="uppy-thumbnail-label col-sm-8">' + file.name + ' (' +
                        Math.round(filesize, 2) + ' ' + sizeLabel + ')</span><span data-target="' + trimmed_id +
                        '" data-id="' + file.id +
                        '" class="uppy-remove-thumbnail col-sm-2 text-right"><i class="fas fa-times-circle"></i></span></div>';

                    // append to view
                    $(id + ' .uppy-thumbnails').append(imagePreview);

                    // show preview
                    $($($('.uppy-thumbnail-container[data-id="' + file.id + '"').find('.uppy-thumbnail')[0]).find(
                        'img')[0]).attr('src', URL.createObjectURL(file.data));
                });
                return uppyDrag;
            }
        </script>
    @stop
