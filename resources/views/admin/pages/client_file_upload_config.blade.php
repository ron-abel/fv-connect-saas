@extends('admin.layouts.default')

@section('title', 'Vineconnect - Admin Dashboard')

@section('content')
    <style>
        /* for sm */
        .custom-switch.custom-switch-sm .custom-control-label {
            padding-left: 1rem;
            padding-bottom: 1rem;
        }

        .custom-switch.custom-switch-sm .custom-control-label::before {
            height: 1rem;
            width: calc(1rem + 0.75rem);
            border-radius: 2rem;
        }

        .custom-switch.custom-switch-sm .custom-control-label::after {
            width: calc(1rem - 4px);
            height: calc(1rem - 4px);
            border-radius: calc(1rem - (1rem / 2));
        }

        .custom-switch.custom-switch-sm .custom-control-input:checked~.custom-control-label::after {
            transform: translateX(calc(1rem - 0.25rem));
        }

        /* for md */

        .custom-switch.custom-switch-md .custom-control-label {
            padding-left: 2rem;
            padding-bottom: 1.5rem;
        }

        .custom-switch.custom-switch-md .custom-control-label::before {
            height: 1.5rem;
            width: calc(2rem + 0.75rem);
            border-radius: 3rem;
        }

        .custom-switch.custom-switch-md .custom-control-label::after {
            width: calc(1.5rem - 4px);
            height: calc(1.5rem - 4px);
            border-radius: calc(2rem - (1.5rem / 2));
        }

        .custom-switch.custom-switch-md .custom-control-input:checked~.custom-control-label::after {
            transform: translateX(calc(1.5rem - 0.25rem));
        }

        /* for lg */

        .custom-switch.custom-switch-lg .custom-control-label {
            padding-left: 3rem;
            padding-bottom: 2rem;
        }

        .custom-switch.custom-switch-lg .custom-control-label::before {
            height: 2rem;
            width: calc(3rem + 0.75rem);
            border-radius: 4rem;
        }

        .custom-switch.custom-switch-lg .custom-control-label::after {
            width: calc(2rem - 4px);
            height: calc(2rem - 4px);
            border-radius: calc(3rem - (2rem / 2));
        }

        .custom-switch.custom-switch-lg .custom-control-input:checked~.custom-control-label::after {
            transform: translateX(calc(2rem - 0.25rem));
        }

        .d-none {
            display: none;
        }
    </style>
    <!--begin::Subheader-->
    <div class="subheader py-2 py-lg-4 subheader-solid" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
            <!--begin::Info-->
            <div class="d-flex align-items-center flex-wrap mr-2">
                <!--begin::Page Title-->
                <h5 class="text-dark font-weight-bold mt-2 mb-2 mr-5">Client Portal Document Uploads Configuration</h5>
                <!--end::Page Title-->

            </div>
            <!--end::Info-->
        </div>
    </div>
    <!--end::Subheader-->
    <div class="d-flex flex-column-fluid">
        <!--begin::Container-->
        <div class="container">
            @if (session()->has('success'))
                <div class="alert alert-success" role="alert">
                    {!! session()->get('success') !!}
                </div>
            @endif
            @if (session()->has('error'))
                <div class="alert alert-danger" role="alert">
                    {!! session()->get('error') !!}
                </div>
            @endif
            <!--begin::Row-->
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-custom gutter-b example example-compact">
                        <div class="card-body">
                            <input type="hidden" id="tenant_id" value="{{ $tenant_id }}">
                            <div class="overlay loading"></div>
                            <div class="spinner-border text-primary loading" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>

                            <ul class="nav nav-pills" id="myTab1" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link btn btn-outline-primary active" id="configure-tab" data-toggle="tab"
                                        href="#configure_document_upload">
                                        <span class="nav-text">Configure Document Upload</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link btn btn-outline-primary" id="uploaded-document-tab" data-toggle="tab"
                                        href="#uploaded_document" aria-controls="profile">
                                        <span class="nav-text">Uploaded Document Log</span>
                                    </a>
                                </li>
                            </ul>

                            <div class="tab-content mt-6" id="myTabContent1">
                                <div class="tab-pane fade show active" id="configure_document_upload" role="tabpanel"
                                    aria-labelledby="configure-tab">
                                    <div class="mt-5">
                                        <p>
                                            <b>Instructions:</b> Client can upload documents, photos, videos, and other file
                                            formats
                                            via the Client Portal when this feature is enabled. There are several options
                                            for
                                            categorizing and organizing the files on upload to the client's project. Begin
                                            by
                                            enabling document uploads using the toggle below. Clients cannot view the upload
                                            module
                                            in Client Portal until this toggle is enabled.
                                        </p>
                                        <div class="clear"></div>
                                        <div class="callout_subtle lightgrey"><i class="fas fa-link"
                                                style="color:#383838;padding-right:5px;"></i> Support Article: <a
                                                href="https://intercom.help/vinetegrate/en/articles/6691727-document-uploads"
                                                target="_blank" />Document Upload</a></div>
                                    </div>

                                    <div class="row">
                                        <div class="col-6">
                                            <label for=""><b>Update Hashtag for Document Sharing from
                                                    Filevine</b></label>
                                            <div class="form-group align-items-center d-flex form-group">
                                                <input type="text" name="config_hashtag" class="form-control"
                                                    value="{{ isset($fv_document_config->hashtag) ? $fv_document_config->hashtag : '' }}">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group mt-8">
                                                <button type="button" role="button" class="btn btn-success mr-2"
                                                    id="update_hashtag">
                                                    Update
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-5">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <div class="custom-control custom-switch custom-switch-md pl-2">
                                                    <input type="checkbox" name="is_enable_file_uploads" value="enable"
                                                        class="custom-control-input" id="enable_file_uploads"
                                                        {{ isset($config_details->is_enable_file_uploads) && $config_details->is_enable_file_uploads ? 'checked' : '' }}>
                                                    <label class="custom-control-label ml-7 pl-4"
                                                        for="enable_file_uploads"><b>Enable Document Uploads in Client
                                                            Portal</b></label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <div class="custom-control custom-switch custom-switch-md pl-2">
                                                    <input type="checkbox" name="is_defined_organization_scheme"
                                                        value="enable" class="custom-control-input"
                                                        id="defined_organization_scheme"
                                                        {{ isset($config_details->is_defined_organization_scheme) && $config_details->is_defined_organization_scheme ? 'checked' : '' }}>
                                                    <label class="custom-control-label ml-7 pl-4"
                                                        for="defined_organization_scheme"><b>Define an Organizational Scheme
                                                            for
                                                            Files on Upload</b></label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-3 no-choices-mapping">
                                        <p>
                                            Note: If organization schemes are not defined, all uploaded documents will be
                                            stored by
                                            default in the root Docs Folders for the project.
                                        </p>
                                    </div>

                                    <div class="choices-mapping">

                                        <div class="mt-3">
                                            <p>
                                                <b>Instructions</b>: To define an organizational scheme for document
                                                uploads, begin
                                                by creating a dropdown item list of options that will be displayed to the
                                                client in
                                                the Client Portal. You'll then define how that file is handled based on each
                                                choice.
                                                Be sure to map all choices. Unmapped choice will deliver the document upload
                                                to the
                                                project's root Doc Folder.
                                            </p>
                                        </div>

                                        <div class="row">
                                            <div class="col-6">
                                                <label for=""><b>Create Document Type Options for your Client to
                                                        Choose When
                                                        Uploading</b></label>
                                                <div class="form-group align-items-center d-flex form-group">
                                                    <input type="text" name="choice" id="choice_input"
                                                        class="form-control"
                                                        placeholder="(eg: 'Photos', 'Medical Records', 'Insurance Claim Form')">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-6 mt-5">
                                                <div class="form-group">
                                                    <button type="button" role="button" class="btn btn-success mr-2"
                                                        id="add_choice">
                                                        Add
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- display list of choices -->
                                        <div class="row choices-mapping-list mt-5"></div>
                                        <!-- display mapping table -->
                                        <div class="row choices-mapping-scheme mt-5 col-md-12">
                                            <p class="mt-3">
                                                <b>Map Each Choice you Create to an Upload Scheme</b>
                                            </p>

                                            <table class="w-100">
                                                <tbody data-repeater-list="group-a">
                                                    <tr>
                                                        <td style="width:18% !important">
                                                            <div class="form-group">
                                                                <label>Choice Equals</label>
                                                                <select id="choice_selector" class="form-control">
                                                                </select>
                                                            </div>
                                                        </td>
                                                        <td style="width:16% !important">
                                                            <div class="form-group">
                                                                <label>Handle The File As...</label>
                                                                <select id="action_selector" class="form-control">
                                                                    @if (count($config_actions) > 0)
                                                                        @foreach ($config_actions as $key => $value)
                                                                            <option value="{{ $key }}">
                                                                                {{ $value }}
                                                                            </option>
                                                                        @endforeach
                                                                    @endif
                                                                </select>
                                                            </div>
                                                        </td>
                                                        <td style="width:16% !important"
                                                            class="fetch_element_field d-none">
                                                            <div class="form-group">
                                                                <label>Choose Project Type</label>
                                                                <input type="hidden" id="project_type_name">
                                                                <select id="project_selector" class="form-control">
                                                                    <option value="">Choose Option</option>
                                                                    @if (isset($project_types['items']))
                                                                        @foreach ($project_types['items'] as $type)
                                                                            <option
                                                                                value="{{ $type['projectTypeId']['native'] }}">
                                                                                {{ $type['name'] }}</option>
                                                                        @endforeach
                                                                    @endif
                                                                </select>
                                                            </div>
                                                        </td>
                                                        <td style="width:16% !important"
                                                            class="fetch_element_field d-none">
                                                            <div class="form-group">
                                                                <label>Choose Section</label>
                                                                <input type="hidden" id="target_section_name">
                                                                <select id="section_selector" class="form-control">
                                                                </select>
                                                            </div>
                                                        </td>
                                                        <td style="width:16% !important"
                                                            class="fetch_element_field d-none">
                                                            <div class="form-group">
                                                                <label>Choose Field</label>
                                                                <input type="hidden" id="target_field_name">
                                                                <select id="field_selector" class="form-control">
                                                                </select>
                                                            </div>
                                                        </td>
                                                        <td style="width:18% !important">
                                                            <div class="form-group">
                                                                <label>Optional Hashtag</label>
                                                                <input type="text" class="form-control"
                                                                    placeholder="Hashtag" id="hashtag">
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <button type="submit" class="btn btn-success"
                                                                id="save-choice-scheme"
                                                                style="margin-top:25px;">SAVE</button>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>

                                        </div>

                                        <!-- display mapping table -->
                                        <div class="row mt-5 col-md-12">
                                            <p class="mt-3">
                                                <b>Mapped Upload Schemes</b>
                                            </p>
                                        </div>
                                        <div id="choices-mapping-scheme-table" class="row mt-3 col-md-12"></div>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="uploaded_document" role="tabpanel"
                                    aria-labelledby="uploaded-document-tab">
                                    <table class="table table-bordered table-hover" id="tenantadmin_basic_datatable">
                                        <thead>
                                            <tr>
                                                <th>File Name</th>
                                                <th>Size (KB)</th>
                                                <th>Upload On</th>
                                                <th>Type of Document</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($fv_client_upload_documents as $document)
                                                <tr>
                                                    <td>{{ $document->fv_filename }}
                                                        <a href="{{ route('download_fv_document', ['subdomain' => session()->get('subdomain')]) }}?{{ 'id=' . $document->id . '&type=upload' }}"
                                                            type="button"
                                                            class="btn btn-sm btn-outline-dark document-download ml-2">Download</a>
                                                    </td>
                                                    <td>{{ round($document->doc_size / 1024) }}</td>
                                                    <td>{{ $document->fv_upload_date }}</td>
                                                    <td>{{ $document->choice }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Card-->
                </div>
            </div>
        </div>
    </div>
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

        .nav-link:hover,
        .nav-link.active {
            background: #26A9DF !important;
            color: #fff !important;
        }

        .nav .nav-link:hover:not(.disabled) .nav-text {
            color: #fff !important;
        }
    </style>
@stop
@section('scripts')
    <script>
        $(document).ready(function() {
            showHideChoicesContainer();
            // update file upload settings
            $(document).on("click", "#enable_file_uploads", function() {
                let value = $(this).prop('checked');
                value = value ? 1 : 0;
                updateFileUploadSettings("update-file-uploads", value);
            });
            // update organizations scheme settings
            $(document).on("click", "#defined_organization_scheme", function() {
                let value = $(this).prop('checked');
                value = value ? 1 : 0;
                showHideChoicesContainer();
                updateFileUploadSettings("update-file-scheme", value);
            });

            // capture create choice button
            $(document).on("click", "#add_choice", function() {
                let element = $('#choice_input');
                let choice = element.val();
                if (choice.trim() == '') {
                    $(".loading").hide();
                    Swal.fire({
                        text: "Please enter choice name to add!",
                        icon: "error",
                    });
                    return;
                } else {
                    $(".loading").show();
                    var formdata = {
                        choice: choice,
                        _token: "{{ csrf_token() }}"
                    };
                    $.ajax({
                        url: "{{ url('/admin/add_choice_client_file_upload_config') }}",
                        type: "POST",
                        data: formdata,
                        success: function(json) {
                            $(".loading").hide();
                            Swal.fire({
                                text: json.message,
                                icon: json.status ? "success" : "error",
                            });
                            if (json.status) {
                                element.val('');
                                getChoices();
                            }
                        }
                    });
                }

            });

            // capture delete choice button
            $(document).on("click", ".delete-choice", function() {
                let value = $(this).attr('data-target');
                Swal.fire({
                    text: "Are you sure you wanted to delete selected dropdown choice ?",
                    icon: "warning",
                    buttonsStyling: false,
                    confirmButtonText: "Delete",
                    showCancelButton: true,
                    cancelButtonText: "Cancel",
                    customClass: {
                        confirmButton: "btn font-weight-bold btn-light-primary",
                        cancelButton: "btn font-weight-bold btn-danger"
                    }
                }).then(function(response) {
                    if (response.isConfirmed) {
                        $(".loading").show();
                        var formdata = {
                            choice: value,
                            _token: "{{ csrf_token() }}"
                        };
                        $.ajax({
                            url: "{{ url('/admin/delete_choice_client_file_upload_config') }}",
                            type: "POST",
                            data: formdata,
                            success: function(json) {
                                $(".loading").hide();
                                Swal.fire({
                                    text: json.message,
                                    icon: json.status ? "success" : "error",
                                });
                                // refresh options
                                if (json.status) {
                                    getChoices();
                                    getMappedChoices();
                                }
                            }
                        });
                    }
                });
            });

            // capture action selector
            $(document).on("change", "#action_selector", function() {
                let value = $(this).val();
                if (value > 1) {
                    $('.fetch_element_field').removeClass('d-none');
                } else {
                    $('.fetch_element_field').addClass('d-none');
                }
                $('#field_selector').html('');
                $('#target_field_name').val('');
                $('#section_selector').html('');
                $('#target_section_name').val('');
            });

            // get section from project type selected
            $(document).on("change", "#project_selector", function() {
                let value = $(this).val();
                let name = $(this).find('option:selected').text();
                let action = $('#action_selector').val();
                if (value !== "") {
                    $(".loading").show();
                    $.ajax({
                        url: "{{ url('/admin/get_project_section_client_file_upload_config') }}/" +
                            value + "/" + action,
                        type: "GET",
                        success: function(json) {
                            $(".loading").hide();
                            $('#field_selector').html('');
                            $('#project_type_name').val(name);
                            $('#section_selector').html(json.html);
                        }
                    });
                } else {
                    $('#project_type_name').val('');
                    $('#field_selector').html('');
                    $('#target_field_name').val('');
                    $('#section_selector').html('');
                    $('#target_section_name').val('');
                }

            });

            // get fields from section selected
            $(document).on("change", "#section_selector", function() {
                let project_type_id = $('#project_selector').val();
                let value = $(this).val();
                let name = $(this).find('option:selected').text();
                if (value !== "") {
                    $(".loading").show();
                    $.ajax({
                        url: "{{ url('/admin/get_project_section_field_client_file_upload_config') }}/" +
                            project_type_id + "/" + value,
                        type: "GET",
                        success: function(json) {
                            $(".loading").hide();
                            $('#target_section_name').val(name);
                            $('#field_selector').html(json.html);
                        }
                    });
                } else {
                    $('#target_section_name').val('');
                    $('#field_selector').html('');
                    $('#target_field_name').val('');
                }
            });

            // get fields from section selected
            $(document).on("change", "#field_selector", function() {
                let value = $(this).val();
                let name = $(this).find('option:selected').text();
                if (value !== "") {
                    $('#target_field_name').val(name);
                } else {
                    $('#target_field_name').val('');
                }
            });

            // get details for selected choice if any
            $(document).on("change", "#choice_selector", function() {
                let value = $(this).val();
                if (value !== "") {
                    $(".loading").show();
                    var formdata = {
                        scheme: value,
                        _token: "{{ csrf_token() }}"
                    };
                    $.ajax({
                        url: "{{ url('/admin/get_choice_detail_client_file_upload_config') }}",
                        type: "POST",
                        data: formdata,
                        success: function(json) {
                            $(".loading").hide();
                            if (json.status) {
                                $('#action_selector').val(json.scheme.handle_files_action);
                                if (json.scheme.handle_files_action > 1) {
                                    $('.fetch_element_field').removeClass('d-none');
                                    $('#project_selector').val(json.scheme.project_type_id);
                                    $('#project_type_name').val(json.scheme.project_type_name);
                                    $('#section_selector').html(json.sections);
                                    $('#section_selector').val(json.scheme.target_section_id);
                                    $('#target_section_name').val(json.scheme
                                        .target_section_name);
                                    $('#field_selector').html(json.fields);
                                    $('#field_selector').val(json.scheme.target_field_id + '*' +
                                        json.scheme.target_field_type);
                                    $('#target_field_name').val(json.scheme.target_field_name);
                                } else {
                                    $('.fetch_element_field').addClass('d-none');
                                }
                                $('#hashtag').val(json.scheme.hashtag);
                            } else {
                                $('#project_selector').val('');
                                $('#field_selector').html('');
                                $('#section_selector').html('');
                                $('#hashtag').val('');
                            }
                        }
                    });
                } else {
                    $('#field_selector').html('');
                    $('#section_selector').html('');
                    $('#hashtag').val('');
                }

            });

            // save scheme settings
            $(document).on("click", "#save-choice-scheme", function() {
                var formdata = {
                    choice_id: $('#choice_selector').val(),
                    project_type_id: $('#project_selector').val(),
                    project_type_name: $('#project_type_name').val(),
                    handle_files_action: $('#action_selector').val(),
                    target_section_id: $('#section_selector').val(),
                    target_section_name: $('#target_section_name').val(),
                    target_field_id: $('#field_selector').val(),
                    target_field_name: $('#target_field_name').val(),
                    hashtag: $('#hashtag').val(),
                    _token: "{{ csrf_token() }}"
                };
                $(".loading").show();
                $.ajax({
                    url: "{{ url('/admin/add_scheme_client_file_upload_config') }}",
                    type: "POST",
                    data: formdata,
                    success: function(json) {
                        $(".loading").hide();
                        Swal.fire({
                            html: json.message,
                            icon: json.status ? "success" : "error",
                        });

                        $("#choice_selector").val($("#choice_selector option:first").val());
                        $("#project_selector").val($("#project_selector option:first").val());
                        $("#section_selector").val($("#section_selector option:first").val());
                        $("#field_selector").val($("#field_selector option:first").val());
                        $("#hashtag").val("");

                        if (json.status) {
                            getMappedChoices();
                        }
                    }
                });

            });

            // capture delete choice button
            $(document).on("click", ".delete-scheme", function() {
                let value = $(this).attr('data-target');
                Swal.fire({
                    text: "Are you sure you wanted to delete selected mapped scheme ?",
                    icon: "warning",
                    buttonsStyling: false,
                    confirmButtonText: "Delete",
                    showCancelButton: true,
                    cancelButtonText: "Cancel",
                    customClass: {
                        confirmButton: "btn font-weight-bold btn-light-primary",
                        cancelButton: "btn font-weight-bold btn-danger"
                    }
                }).then(function(response) {
                    if (response.isConfirmed) {
                        $(".loading").show();
                        var formdata = {
                            scheme: value,
                            _token: "{{ csrf_token() }}"
                        };
                        $.ajax({
                            url: "{{ url('/admin/delete_mapped_choice_client_file_upload_config') }}",
                            type: "POST",
                            data: formdata,
                            success: function(json) {
                                $(".loading").hide();
                                Swal.fire({
                                    text: json.message,
                                    icon: json.status ? "success" : "error",
                                });
                                // refresh options
                                if (json.status) {
                                    getMappedChoices();
                                }
                            }
                        });
                    }
                });
            });

        });

        function showHideChoicesContainer() {
            let value = $("#defined_organization_scheme").prop('checked');
            if (value) {
                $('.no-choices-mapping').hide();
                $('.choices-mapping').show();
                getChoices();
                getMappedChoices();
            } else {
                $('.choices-mapping').hide();
                $('.no-choices-mapping').show();
            }
        }

        function updateFileUploadSettings(type, value) {
            if (type == "") {
                return;
            }
            $(".loading").show();
            if (type == "update-file-uploads") {
                var formdata = {
                    is_enable_file_uploads: value
                };

                formdata._token = "{{ csrf_token() }}";
                formdata.type = type;
            } else if (type == "update-file-scheme") {
                var formdata = {
                    is_defined_organization_scheme: value
                };
                formdata._token = "{{ csrf_token() }}";
                formdata.type = type;
            }
            $.ajax({
                url: "{{ url('/admin/update_client_file_upload_config') }}",
                type: "POST",
                data: formdata,
                success: function(json) {
                    $(".loading").hide();
                    Swal.fire({
                        text: json.message,
                        icon: "success",
                    });
                }
            });
        }

        function getChoices() {
            var formdata = {
                _token: "{{ csrf_token() }}"
            };
            $.ajax({
                url: "{{ url('/admin/get_choices_client_file_upload_config') }}",
                type: "POST",
                data: formdata,
                success: function(json) {
                    $('#choice_selector').html(json.options);
                    $('.choices-mapping-list').html(json.choices);
                }
            });
        }

        function getMappedChoices() {
            var formdata = {
                _token: "{{ csrf_token() }}"
            };
            $.ajax({
                url: "{{ url('/admin/get_mapped_choices_client_file_upload_config') }}",
                type: "POST",
                data: formdata,
                success: function(json) {
                    $('#choices-mapping-scheme-table').html(json);
                }
            });
        }

        // Update config hashtag
        $(document).on("click", "#update_hashtag", function() {
            let config_hashtag = $('input[name="config_hashtag"]').val();
            if (config_hashtag.trim() == '') {
                Swal.fire({
                    text: "Hashtag can't be empty!",
                    icon: "error",
                });
                return;
            } else {
                $(".loading").show();
                var formdata = {
                    hashtag: config_hashtag,
                    _token: "{{ csrf_token() }}"
                };
                $.ajax({
                    url: "{{ url('/admin/update_config_hashtag') }}",
                    type: "POST",
                    data: formdata,
                    success: function(json) {
                        $(".loading").hide();
                        Swal.fire({
                            text: json.message,
                            icon: json.status ? "success" : "error",
                        });
                    },
                    error: function() {
                        $(".loading").hide();
                        alert("Failed to handle your request! Please try again 5 mins later!");
                    }
                });
            }
        });

        // Update config hashtag
        /* $(document).on("click", ".upload-document-download", function() {
             let id = $(this).data('id');
             $(".loading").show();
             var formdata = {
                 id: id,
                 _token: "{{ csrf_token() }}"
             };
             $.ajax({
                 url: "{{ url('/admin/document/download_uploaded_file') }}",
                 type: "GET",
                 data: formdata,
                 success: function(json) {
                     $(".loading").hide();
                     if (json.status) {
                         const url = window.URL.createObjectURL(blob);
                         const a = document.createElement('a');
                         a.style.display = 'none';
                         a.href = url;
                         a.download = 'todo-1.json';
                         document.body.appendChild(a);
                         a.click();
                         window.URL.revokeObjectURL(url);
                     }
                 },
                 error: function() {
                     $(".loading").hide();
                     alert("Failed to handle your request! Please try again 5 mins later!");
                 }
             });
         }); */
    </script>
@endsection
