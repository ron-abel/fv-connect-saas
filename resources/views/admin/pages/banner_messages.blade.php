@extends('admin.layouts.default')

@section('title', 'VineConnect Admin - Client Portal Banner Message')

@section('content')
    <!--begin::Subheader-->
    <div class="subheader py-2 py-lg-4 subheader-solid" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
            <!--begin::Info-->
            <div class="d-flex align-items-center flex-wrap mr-2">
                <!--begin::Page Title-->
                <h4 class="text-dark font-weight-bold mt-2 mb-2 mr-5">Client Portal Banner Message</h4>
                <!--end::Page Title-->
            </div>
            <!--end::Info-->
        </div>
    </div>
    <div class="overlay loading"></div>
    <div class="spinner-border text-primary loading" role="status">
        <span class="sr-only">Loading...</span>
    </div>
    <!--end::Subheader-->
    <!--begin::Entry-->
    <div class="d-flex flex-column-fluid">
        <!--begin::Container-->
        <div class="container">
            <!--begin::Row-->
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-custom gutter-b example example-compact">
                        <div class="card-header">
                            <h5 class="card-title">Setting Up Your Banner Message</h5>
                        </div>
                        <div class="card-body">
                            <p><b>Instructions:</b> The Banner Message will display to all users above the Case Status section. You can turn this on during a specific date range by setting that in Start Date and End Date.</p>
                            <div class="clear"></div>
                            <div class="callout_subtle lightgrey ml-0"><i class="fas fa-link" style="color:#383838;"></i> Support Article: <a href="https://intercom.help/vinetegrate/en/articles/6445588-client-portal-banner-message" target="_blank" />Client Portal Banner Message</a></div>
                            <div class="callout_subtle lightgrey"><i class="fa fa-key mr-3"></i><a href="{{ url('admin/variables') }}" target="_blank" />&nbsp;List of Variables</a></div>
                            <form action="{{ route('notice_post', ['subdomain' => $subdomain]) }}" method="POST">
                                @csrf
                                <div class="notice-client">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label for="">Active</label><br>
                                            <label class="custom-checkbox-switch" class="form-control">
                                                <input
                                                    type="checkbox" class="action-status"
                                                    name="is_active">
                                                <span class="slider round"></span>
                                            </label>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="">Color Style</label>
                                            <select name="banner_color" id="banner_color" class="form-control">
                                                <option value="default" style="background-color: white">Default</option>
                                                <option value="notice" style="background-color: orange">Notice Style
                                                </option>
                                                <option value="warning" style="background-color: red">Warning Style</option>
                                                <option value="affirmation" style="background-color: green">Affirmation
                                                    Style</option>
                                                <option value="calming" style="background-color: lightblue">Calming Style
                                                </option>
                                                <option value="dark" style="background-color: black">Dark Style</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label>Start Date</label>
                                            <input type="date" name="start_date" class="form-control start_date"
                                                onchange="validateTimeline(this)">
                                            @error('start_date')
                                                <span class="form-text text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-md-3">
                                            <label>End Date</label>
                                            <input type="date" name="end_date" class="form-control end_date"
                                                onchange="validateTimeline(this)">
                                            @error('end_date')
                                                <span class="form-text text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div style="padding: 10px 0px" class="row">
                                        <div class="col-md-12 pt-3">
                                            <label> <b>Portal Message</b> (Character Limit: 512)</label>
                                            <textarea class="form-control notice_body" name="notice_body" cols="30" rows="10"></textarea>
                                            @error('notice_body')
                                                <span class="form-text text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="save-button col-md-12 pt-3">
                                            <button class="btn btn-success">Add</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <div class="mt-4 mb-4">
                                <h6 class="mb-4">Banner Message List</h6>
                                <div class="mt-4 mb-4">
                                    <div class="row">
                                        <div class="col-md-1 mx-auto my-auto font-weight-bold">
                                            Style
                                        </div>
                                        <div class="col-md-4 mx-auto my-auto font-weight-bold">
                                            Message
                                        </div>
                                        <div class="col-md-1 mx-auto my-auto font-weight-bold">
                                            Active
                                        </div>
                                        <div class="col-md-4 mx-auto my-auto font-weight-bold">
                                            Display Dates
                                        </div>
                                        <div class="col-md-1 mx-auto my-auto font-weight-bold">
                                            Edit
                                        </div>
                                        <div class="col-md-1 mx-auto my-auto font-weight-bold">
                                            Delete
                                        </div>
                                    </div>
                                </div>

                                @foreach ($notices as $notice)
                                    <div class="mt-4 mb-4">
                                        <div class="row">
                                            <div class="col-md-1 mx-auto my-auto">
                                                <div
                                                    class="banner-message-display {{ isset($notice->banner_color) && !empty($notice->banner_color) ? $notice->banner_color : '' }}">
                                                </div>
                                            </div>
                                            <div class="col-md-4 mx-auto my-auto">
                                                {{ strlen(strip_tags($notice->notice_body)) > 20 ? substr(strip_tags($notice->notice_body), 0, 20) . '...' : strip_tags($notice->notice_body) }}
                                            </div>
                                            <div class="col-md-1 mx-auto my-auto">
                                                <div class="form-group ml-2">
                                                    <label>
                                                        <div class="custom-control custom-switch custom-switch-md pl-0">
                                                            <input type="checkbox" class="custom-control-input notice-status"
                                                                id="notification_config_toggle_{{ $notice->id }}"
                                                                {{ $notice->is_active ? 'checked' : '' }}
                                                                onclick="noticeChangeStatus(this, '{{ $notice->id }}')">
                                                            <label class="custom-control-label ml-7 pl-4"
                                                                for="notification_config_toggle_{{ $notice->id }}"></label>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mx-auto my-auto">
                                                {{ isset($notice->start_date) ? \Carbon\Carbon::parse($notice->start_date)->format('m/d/Y') : '' }}
                                                to
                                                {{ isset($notice->end_date) ? \Carbon\Carbon::parse($notice->end_date)->format('m/d/Y') : '' }}
                                            </div>
                                            <div class="col-md-1 mx-auto my-auto">
                                                <div role="button" class="ml-1" data-toggle="modal"
                                                    data-target="#edit-modal-{{ $notice->id }}"><i
                                                        class="fa fa-edit text-secondary"></i></div>
                                            </div>
                                            <div class="col-md-1 mx-auto my-auto">
                                                <div role="button" class="ml-1" onclick="noticeDelete(this, '{{ $notice->id }}')"><i
                                                        class="fa fa-trash text-danger"></i></div>
                                            </div>
                                        </div>
                                        <div class="modal fade" id="edit-modal-{{ $notice->id }}"
                                            role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-lg" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="exampleModalLabel"></h5>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="edit-section">
                                                            <form
                                                                action="{{ route('notice_post', ['subdomain' => $subdomain]) }}"
                                                                method="post">
                                                                @csrf
                                                                <input type="hidden" name="id"
                                                                    value="{{ $notice->id }}">
                                                                <div class="row">
                                                                    <div class="col-md-3">
                                                                            <label for="">Active</label><br>
                                                                            <label class="custom-checkbox-switch" class="form-control">
                                                                                <input
                                                                                    type="checkbox" class="action-status"
                                                                                    name="is_active" {{ isset($notice->is_active) && $notice->is_active ? 'checked' : '' }}>
                                                                                <span class="slider round"></span>
                                                                            </label>
                                                                        </div>
                                                                    <div class="col-md-3">
                                                                        <label for="">Color Style</label>
                                                                        <select name="banner_color" id="banner_color"
                                                                            class="form-control">
                                                                            <option value="default"
                                                                                style="background-color: white"
                                                                                {{ isset($notice->banner_color) && $notice->banner_color == 'default' ? 'selected' : '' }}>
                                                                                Default</option>
                                                                            <option value="notice"
                                                                                style="background-color: orange"
                                                                                {{ isset($notice->banner_color) && $notice->banner_color == 'notice' ? 'selected' : '' }}>
                                                                                Notice Style</option>
                                                                            <option value="warning"
                                                                                style="background-color: red"
                                                                                {{ isset($notice->banner_color) && $notice->banner_color == 'warning' ? 'selected' : '' }}>
                                                                                Warning Style</option>
                                                                            <option value="affirmation"
                                                                                style="background-color: green"
                                                                                {{ isset($notice->banner_color) && $notice->banner_color == 'affirmation' ? 'selected' : '' }}>
                                                                                Affirmation Style</option>
                                                                            <option value="calming"
                                                                                style="background-color: lightblue"
                                                                                {{ isset($notice->banner_color) && $notice->banner_color == 'calming' ? 'selected' : '' }}>
                                                                                Calming Style</option>
                                                                            <option value="dark"
                                                                                style="background-color: black"
                                                                                {{ isset($notice->banner_color) && $notice->banner_color == 'dark' ? 'selected' : '' }}>
                                                                                Dark Style</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <label>Start Date</label>
                                                                        <input type="date" name="start_date"
                                                                            class="form-control start_date"
                                                                            onchange="validateTimeline(this)"
                                                                            value="{{ isset($notice->start_date) && !empty($notice->start_date) ? $notice->start_date : date('Y-m-d') }}">
                                                                        @error('start_date')
                                                                            <span
                                                                                class="form-text text-danger">{{ $message }}</span>
                                                                        @enderror
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <label>End Date</label>
                                                                        <input type="date" name="end_date"
                                                                            class="form-control end_date"
                                                                            onchange="validateTimeline(this)"
                                                                            value="{{ isset($notice->end_date) && !empty($notice->end_date) ? $notice->end_date : date('Y-m-d') }}">
                                                                        @error('end_date')
                                                                            <span
                                                                                class="form-text text-danger">{{ $message }}</span>
                                                                        @enderror
                                                                    </div>
                                                                </div>
                                                                <div style="padding: 10px 0px" class="row">
                                                                    <div class="col-md-12 pt-3">
                                                                        <label> <b>Portal Message</b> (Character Limit:
                                                                            512)</label>
                                                                        <textarea class="form-control notice_body" name="notice_body" cols="30" rows="10">{{ isset($notice->notice_body) ? $notice->notice_body : '' }}</textarea>
                                                                        @error('notice_body')
                                                                            <span
                                                                                class="form-text text-danger">{{ $message }}</span>
                                                                        @enderror
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="save-button col-md-12 pt-3">
                                                                        <button class="btn btn-success">Save</button>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- end::row -->
        </div><!-- end Container-->
    </div>
    <!--end::d-flex-->
    <style>
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
            width: 5rem;
            height: 5rem;
        }
    </style>
    @php
    $notice_success = '';
    $notice_error = '';
    if (session()->has('notice_success')) {
        $notice_success = session()->get('notice_success');
    }
    if (session()->has('notice_error')) {
        $notice_error = session()->get('notice_error');
    }
    @endphp
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('../js/banner_messages.js') }}"></script>
@stop
@section('scripts')
    <script>
        var notice_success = "{{ $notice_success }}";
        var notice_error = "{{ $notice_error }}";

        if (notice_success != "") {
            Swal.fire({
                text: notice_success,
                icon: "success",
            });
        }
        if (notice_error) {
            Swal.fire({
                text: notice_error,
                icon: "error",
            });
        }

        tinymce.init({
            selector: "textarea.notice_body",
            min_height: 500,
            plugins: [
                "advlist autolink lists link image charmap print preview anchor tinymcespellchecker",
                "searchreplace visualblocks code fullscreen",
                "insertdatetime media table paste",
                "media code"
            ],
            menubar: 'file edit insert view format table tools',
            toolbar: 'bullist numlist | insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | outdent indent | spellchecker language spellcheckdialog | custom_action_button',
            a11y_advanced_options: true,
            image_list: "get-image-list",
            spellchecker_dialog: true,
            smart_paste: true,
            image_dimensions: false,
            images_reuse_filename: true,
            branding: false,
            setup: function(editor) {
                var max = 512;
                editor.on('KeyUp', function(event) {
                    var numChars = tinymce.activeEditor.plugins.wordcount.body.getCharacterCount();
                    if (numChars > max) {
                        Swal.fire({
                            title: "Maximum " + max + " characters allowed.",
                            confirmButtonText: 'Ok',
                            confirmButtonColor: '#54a9e0',
                        })
                        event.preventDefault();
                        return false;
                    }
                });
            },
            paste_preprocess: function(plugin, args) {
                let editor = tinymce.get(tinymce.activeEditor.id);
                let content = editor.contentDocument.body.innerText;
                let len = content.length;
                if (len + args.content.length > 512) {
                    Swal.fire({
                        title: "Maximum " + 512 + " characters allowed.",
                        confirmButtonText: 'Ok',
                        confirmButtonColor: '#54a9e0',
                    })
                    args.content = '';
                    editor.contentDocument.body.innerText = content
                } else {
                    editor.contentDocument.body.innerText = content + args.content;
                }
            },
            image_title: true,
            automatic_uploads: true,
            // images_upload_url: "/admin/banner_messages/upload",
            file_picker_types: 'image',
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
            // file_picker_callback: function(cb, value, meta) {
            //     var input = document.createElement('input');
            //     input.setAttribute('type', 'file');
            //     input.setAttribute('accept', 'image/*');
            //     input.onchange = function() {
            //         var file = this.files[0];
            //         var reader = new FileReader();
            //         reader.readAsDataURL(file);
            //         reader.onload = function () {
            //             var id = 'blobid' + (new Date()).getTime();
            //             var blobCache =  tinymce.activeEditor.editorUpload.blobCache;
            //             var base64 = reader.result.split(',')[1];
            //             var blobInfo = blobCache.create(id, file, base64);
            //             blobCache.add(blobInfo);
            //             cb(blobInfo.blobUri(), { title: file.name });
            //         };
            //     };
            //     input.click();
            // },
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
        })

        function noticeDelete(e, id) {
            Swal.fire({
                title: 'Do you want to delete the notice?',
                showCancelButton: true,
                confirmButtonText: 'Yes!',
                cancelButtonText: 'No',
                confirmButtonColor: '#d33',
                cancelButtonColor: '#B5B5C3;',
            }).then((result) => {
                if (result.isConfirmed) {
                    e.parentNode.parentNode.parentNode.style.display = "none";
                    $.ajax({
                        type: "POST",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: "notice_delete",
                        data: {
                            id
                        },
                        success: function(response) {
                            res = JSON.parse(response);
                            if (res.success) {
                                e.parentNode.parentNode.parentNode.remove();
                            } else {
                                e.parentNode.parentNode.parentNode.style.display = "block";
                            }
                        },
                        error: function() {
                            e.parentNode.parentNode.parentNode.style.display = "block";
                        }
                    });
                }
            })
        }

        function validateTimeline(e) {
            let parent = e.parentNode.parentNode;
            if (e.classList.contains("start_date")) {
                let start_date = e.value;
                let end_date = parent.querySelector(".end_date").value;
                if ((Date.parse(start_date) > Date.parse(end_date))) {
                    Swal.fire({
                        text: "Start date should be less than or equal end date!",
                        icon: "error",
                    });
                    e.value = end_date;
                }
            } else if (e.classList.contains("end_date")) {
                let start_date = parent.querySelector(".start_date").value;
                let end_date = e.value;
                if ((Date.parse(start_date) > Date.parse(end_date))) {
                    Swal.fire({
                        text: "End date should be greater than or equal Start date!",
                        icon: "error",
                    });
                    e.value = start_date;
                }
            }
        }

       /* Orginal function of banner message where date is checked
       function validateTimeline(e, notices) {
            let parent = e.parentNode.parentNode;
            if (e.classList.contains("start_date")) {
                let start_date = e.value;
                let end_date = parent.querySelector(".end_date").value;
                if ((Date.parse(start_date) > Date.parse(end_date))) {
                    Swal.fire({
                        text: "End date should be greater than or equal Start date!",
                        icon: "error",
                    });
                    e.value = "";
                }
                notices.map(async (notice) => {
                    if ((Date.parse(notice.start_date) <= Date.parse(start_date)) && (Date.parse(start_date) <=
                            Date.parse(notice.end_date))) {
                        Swal.fire({
                            text: "Already saved the Banner message for these dates (" + notice
                                .start_date + " - " + notice.end_date + ")!",
                            icon: "error",
                        });
                        e.value = "";
                        return;
                    }
                })
            } else if (e.classList.contains("end_date")) {
                let start_date = parent.querySelector(".start_date").value;
                let end_date = e.value;
                if ((Date.parse(start_date) > Date.parse(end_date))) {
                    Swal.fire({
                        text: "End date should be greater than or equal Start date!",
                        icon: "error",
                    });
                    e.value = "";
                }
                notices.map(async (notice) => {
                    if ((Date.parse(notice.start_date) <= Date.parse(end_date)) && (Date.parse(end_date) <= Date
                            .parse(notice.end_date))) {
                        Swal.fire({
                            text: "Already saved the Banner message for these dates (" + notice
                                .start_date + " - " + notice.end_date + ")!",
                            icon: "error",
                        });
                        e.value = "";
                        return;
                    }
                });
            }
        } */
    </script>
@endsection
