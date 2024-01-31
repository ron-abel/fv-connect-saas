@extends('admin.layouts.default')

@section('title', 'VineConnect Admin - List of Variables')

@section('content')
    <!--begin::Subheader-->
    <div class="subheader py-2 py-lg-4 subheader-solid" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
            <!--begin::Info-->
            <div class="d-flex align-items-center flex-wrap mr-2">
                <!--begin::Page Title-->
                <h4 class="text-dark font-weight-bold mt-2 mb-2 mr-5"><i class="fa-icon fas fa-recycle"></i> Custom Filevine
                    Variables</h4>
                <!--end::Page Title-->
            </div>
            <!--end::Info-->
        </div>
    </div>
    <!--end::Subheader-->

    <div class="overlay loading"></div>
    <div class="spinner-border text-primary loading" role="status">
        <span class="sr-only">Loading...</span>
    </div>

    <!--begin::Entry-->
    <div class="d-flex flex-column-fluid">
        <!--begin::Container-->
        <div class="container-fluid">
            <!--begin::Dashboard-->
            <!--begin::Row-->
            <div class="row">
                <div class="col-md-12">
                    <!--begin::Card-->
                    <div class="card card-custom gutter-b example example-compact">
                        <div class="card-body">
                            <p><b>Instructions:</b> Standard Variables are available for use by default throughout various
                                services VineConnect offers. Custom Variables extend the flexibility of variables by
                                allowing you to create variable data values from your Project Type Template(s) custom Static
                                Section fields. To create a custom variable, simply follow the prompt to Add New Variable.
                                Click the clipboard icon from either Standards or Custom tab next to the variable item to
                                paste the variable code anywhere in VineConnect.</p>
                            <ul class="nav nav-pills" id="myTab1" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link btn btn-outline-primary active" id="standard-variable-tab"
                                        data-toggle="tab" href="#standard_variable">
                                        <span class="nav-text">Standard Variables</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link btn btn-outline-primary" id="custom-variable-tab" data-toggle="tab"
                                        href="#custom_variable" aria-controls="profile">
                                        <span class="nav-text">Custom Variables</span>
                                    </a>
                                </li>
                            </ul>

                            <div class="tab-content mt-6" id="myTabContent1">
                                <div class="tab-pane fade show active" id="standard_variable" role="tabpanel"
                                    aria-labelledby="standard-variable-tab">
                                    <!--begin: Datatable-->
                                    <table class="table table-bordered table-hover" id="tenantadmin_basic_datatable">
                                        <thead>
                                            <tr>
                                                <th>Variable Name</th>
                                                <th>Variable Key</th>
                                                <th>Project Timeline</th>
                                                <th>Timeline Mapping</th>
                                                <th>Phase Change SMS</th>
                                                <th>Review Request SMS</th>
                                                <th>Client Banner Message</th>
                                                <th>Automated Workflow Action</th>
                                                <th>Mass Text</th>
                                                <th>Email Template</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($variables as $variable)
                                                <tr>
                                                    <td>{{ $variable->variable_name }}</td>
                                                    <td style="width: auto; min-width:200px;">
                                                        <button class="btn" style="float: left;padding: 0;margin: 0;"
                                                            onclick="copyToClipboard({{ $variable->master_id }})"><i
                                                                class="fa fa-copy"></i></button>
                                                        <span
                                                            id="copyText{{ $variable->master_id }}">{{ $variable->variable_key }}</span>
                                                    </td>
                                                    <td>
                                                        @if ($variable->is_project_timeline)
                                                            <img src="/assets/img/green-checkmark.png" class="w-20px">
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($variable->is_timeline_mapping)
                                                            <img src="/assets/img/green-checkmark.png" class="w-20px">
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($variable->is_phase_change_sms)
                                                            <img src="/assets/img/green-checkmark.png" class="w-20px">
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($variable->is_review_request_sms)
                                                            <img src="/assets/img/green-checkmark.png" class="w-20px">
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($variable->is_client_banner_message)
                                                            <img src="/assets/img/green-checkmark.png" class="w-20px">
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($variable->is_automated_workflow_action)
                                                            <img src="/assets/img/green-checkmark.png" class="w-20px">
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($variable->is_mass_text)
                                                            <img src="/assets/img/green-checkmark.png" class="w-20px">
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($variable->is_email_template)
                                                            <img src="/assets/img/green-checkmark.png" class="w-20px">
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    <!--end: Datatable-->
                                </div>

                                <div class="tab-pane fade" id="custom_variable" role="tabpanel"
                                    aria-labelledby="custom-variable-tab">
                                    <div class="row">
                                        <div class="text-right col-md-12">
                                            <a class="btn btn-primary font-weight-bolder add-new" data-toggle="modal"
                                                data-target="#addVariable">
                                                <i class="icon-xl la la-plus"></i>
                                                Add New Variable
                                            </a>
                                        </div>
                                    </div>
                                    <!--begin: Datatable-->
                                    <div class="row mt-6">
                                        <div class="col-md-12">
                                            <table class="table table-bordered table-hover" id="custom-variable-datatable">
                                                <thead>
                                                    <tr>
                                                        <th>Variable Key</th>
                                                        <th>Project Timeline</th>
                                                        <th>Timeline Mapping</th>
                                                        <th>Phase Change SMS</th>
                                                        <th>Review Request SMS</th>
                                                        <th>Client Banner Message</th>
                                                        <th>Automated Workflow Action</th>
                                                        <th>Mass Text</th>
                                                        <th>Email Template</th>
                                                        {{-- <th>Active</th> --}}
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($custom_variables as $variable)
                                                        <tr class="variable-row{{ $variable->master_id }}">
                                                            <td style="width: auto; min-width:200px;">
                                                                <button class="btn"
                                                                    style="float: left;padding: 0;margin: 0;"
                                                                    onclick="copyToClipboard({{ $variable->master_id }})"><i
                                                                        class="fa fa-copy"></i></button>
                                                                <span
                                                                    id="copyText{{ $variable->master_id }}">{{ $variable->variable_key }}</span>
                                                            </td>
                                                            <td>
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        name="is_project_timeline"
                                                                        {{ $variable->is_project_timeline ? 'checked' : '' }}>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        name="is_timeline_mapping"
                                                                        {{ $variable->is_timeline_mapping ? 'checked' : '' }}>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        name="is_phase_change_sms"
                                                                        {{ $variable->is_phase_change_sms ? 'checked' : '' }}>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        name="is_review_request_sms"
                                                                        {{ $variable->is_review_request_sms ? 'checked' : '' }}>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        name="is_client_banner_message"
                                                                        {{ $variable->is_client_banner_message ? 'checked' : '' }}>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        name="is_automated_workflow_action"
                                                                        {{ $variable->is_automated_workflow_action ? 'checked' : '' }}>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        name="is_mass_text"
                                                                        {{ $variable->is_mass_text ? 'checked' : '' }}>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        name="is_email_template"
                                                                        {{ $variable->is_email_template ? 'checked' : '' }}>
                                                                </div>
                                                            </td>
                                                            {{-- <td>
                                                                <label class="custom-checkbox-switch">
                                                                    <input value="{{ $variable->master_id }}"
                                                                        type="checkbox" class="variable-active"
                                                                        {{ $variable->is_active ? 'checked' : '' }}>
                                                                    <span class="slider round"></span>
                                                                </label>
                                                            </td> --}}
                                                            <td>
                                                                <div class="btn-group" role="group"
                                                                    aria-label="Basic example">
                                                                    <a href="javascript:void(0)"
                                                                        data-id="{{ $variable->master_id }}"
                                                                        data-url="{{ route('variable_update_permission', ['subdomain' => $subdomain]) }}"
                                                                        class="btn btn-sm btn-clean btn-icon save-permission"
                                                                        title="Save Permission">
                                                                        <i class="icon-xl la la-save"></i>
                                                                    </a>
                                                                    <a href="javascript:void(0)"
                                                                        data-row="{{ json_encode($variable) }}"
                                                                        data-toggle="modal" data-target="#addVariable"
                                                                        class="btn btn-sm btn-clean btn-icon edit_variable"
                                                                        title="Edit">
                                                                        <i class="icon-xl la la-edit"></i>
                                                                    </a>
                                                                    <a href="javascript:void(0)"
                                                                        data-url="{{ route('variable_delete', ['subdomain' => $subdomain, 'id' => $variable->master_id]) }}"
                                                                        class="btn btn-sm btn-clean btn-icon delete_variable"
                                                                        title="Delete">
                                                                        <i class="icon-xl la la-trash-o"></i>
                                                                    </a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                            <!--end: Datatable-->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Card-->
                </div><!-- col-md-12 -->
            </div><!-- row -->
        </div><!-- container -->
    </div><!-- d-flex flex-column fluid -->


    <div class="modal fade" id="addVariable" tabindex="-1" role="dialog" aria-labelledby="addVariableLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form action="{{ route('variable_add', ['subdomain' => $subdomain]) }}" name="variable_add_form"
                id="variable_add_form" method="post">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add/Update Variable Information</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <i aria-hidden="true" class="ki ki-close"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="variable_id">
                        <div class="form-group mt-4">
                            <label> Project Type</label>
                            <select class="form-control" name="fv_project_type" required>
                                <option value="">Project Type...</option>
                            </select>
                        </div>
                        <input type="hidden" name="fv_project_type_name">
                        <div class="form-group mt-4">
                            <label> Section Selector</label>
                            <select class="form-control" name="fv_section_selector" required>
                                <option value="">Section Selector...</option>
                            </select>
                        </div>
                        <input type="hidden" name="fv_section_selector_name">
                        <div class="form-group mt-4">
                            <label> Field Selector</label>
                            <select class="form-control" name="fv_field_selector" required>
                                <option value="">Field Selector...</option>
                            </select>
                        </div>
                        <input type="hidden" name="fv_field_selector_name">
                        <div class="form-group mt-4">
                            <label>Variable Name</label>
                            <input type="text" class="form-control form-control-solid" name="variable_name"
                                required />
                        </div>
                        <div class="form-group mt-4">
                            <label>Variable Key</label>
                            <input style="border:0;outline:0;" type="text" class="form-control" name="variable_key"
                                readonly />
                        </div>
                        <div class="form-group mt-4">
                            <label>Placeholder <span class="ml-2" data-theme="dark" data-container="body" data-toggle="tooltip" data-placement="top" title="When the data field is empty, this text will appear in place."><i class="fa fa-info"></i></span></label>
                            <input type="text" class="form-control form-control-solid" name="placeholder" />
                        </div>
                        <div class="form-group mt-4">
                            <label>Variable Description</label>
                            <input type="text" class="form-control form-control-solid" name="variable_description" />
                        </div>
                        <h6 class="mt-6">Assignments for Usage</h6>
                        <div class="form-group mt-4 mb-6">
                            <div class="form-check">
                                <input class="form-check-input permission-feature" type="checkbox" name="select_all">
                                <label class="ml-6">Select All</label>
                            </div>
                        </div>
                        <!--begin: Table-->
                        <table class="table table-bordered table-hover mt-4">
                            <thead>
                                <tr>
                                    <th>Project Timeline</th>
                                    <th>Timeline Mapping</th>
                                    <th>Phase Change SMS</th>
                                    <th>Review Request SMS</th>
                                    <th>Client Banner Message</th>
                                    <th>Automated Workflow Action</th>
                                    <th>Mass Text</th>
                                    <th>Email Template</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="height:40px;">
                                        <div class="form-check text-center pb-10">
                                            <input class="form-check-input permission-feature" type="checkbox"
                                                name="is_project_timeline">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-check text-center pb-10">
                                            <input class="form-check-input permission-feature" type="checkbox"
                                                name="is_timeline_mapping">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-check text-center pb-10">
                                            <input class="form-check-input permission-feature" type="checkbox"
                                                name="is_phase_change_sms">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-check text-center pb-10">
                                            <input class="form-check-input permission-feature" type="checkbox"
                                                name="is_review_request_sms">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-check text-center pb-10">
                                            <input class="form-check-input permission-feature" type="checkbox"
                                                name="is_client_banner_message">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-check text-center pb-10">
                                            <input class="form-check-input permission-feature" type="checkbox"
                                                name="is_automated_workflow_action">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-check text-center pb-10">
                                            <input class="form-check-input permission-feature" type="checkbox"
                                                name="is_mass_text">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-check text-center pb-10">
                                            <input class="form-check-input permission-feature" type="checkbox"
                                                name="is_email_template">
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <!--end: Table-->
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success font-weight-bold">Submit</button>
                        <button type="button" class="btn btn-light-primary font-weight-bold"
                            data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @php
        $success = '';
        $error = '';
        if (session()->has('success')) {
            $success = session()->get('success');
        }
        if (session()->has('error')) {
            $error = session()->get('error');
        }
    @endphp
@endsection


@section('scripts')
    <style type="text/css">
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            z-index: 100;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, .7);
            transition: .3s linear;
            z-index: 9999;
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

    <script src="{{ asset('../js/admin/variable.js') }}"></script>
    <script>
        var success = "{{ $success }}";
        var error = "{{ $error }}";
        if (success != "") {
            Swal.fire({
                text: success,
                icon: "success",
            });
        }
        if (error != "") {
            Swal.fire({
                text: error,
                icon: "error",
            });
        }
    </script>
@endsection
