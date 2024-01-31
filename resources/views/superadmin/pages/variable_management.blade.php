@extends('superadmin.layouts.default')

@section('title', 'Variable Management')

@section('content')

    <div class="main-content container">
        <!--begin::Card-->
        <div class="card card-custom mt-6">
            <div class="card-header flex-wrap border-0 pt-6 pb-0">
                <div class="card-title">
                    <h3 class="card-label">Variable Management</h3>
                </div>
                <div class="card-toolbar">
                    <a class="btn btn-primary font-weight-bolder add-mapping-rule"
                        href="{{ route('variable_management_add') }}">
                        <i class="icon-xl la la-plus"></i>
                        Add New Variable
                    </a>
                </div>
            </div>
            <div class="card-body">

                @if (session()->has('error'))
                    <div class="alert alert-danger" role="alert">
                        {{ session()->get('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @elseif(session()->has('success'))
                    <div class="alert alert-primary" role="alert">
                        {{ session()->get('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <!--begin: Datatable-->
                <table class="table table-bordered table-hover" id="superadmin_basic_datatable">
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
                            <th>Active</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($variables as $variable)
                            <tr class="variable-row{{ $variable->master_id }}">
                                <td>{{ $variable->variable_key }}</td>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_project_timeline"
                                            {{ $variable->is_project_timeline ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_timeline_mapping"
                                            {{ $variable->is_timeline_mapping ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_phase_change_sms"
                                            {{ $variable->is_phase_change_sms ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_review_request_sms"
                                            {{ $variable->is_review_request_sms ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_client_banner_message"
                                            {{ $variable->is_client_banner_message ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_automated_workflow_action"
                                            {{ $variable->is_automated_workflow_action ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_mass_text"
                                            {{ $variable->is_mass_text ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_email_template"
                                            {{ $variable->is_email_template ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td>
                                    <label class="ip-verification-switch">
                                        <input value="{{ $variable->master_id }}" type="checkbox" class="variable-active"
                                            {{ $variable->is_active ? 'checked' : '' }}>
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                                <td>
                                    <div class="btn-group" role="group" aria-label="Basic example">
                                        <a href="javascript:void(0)" data-id="{{ $variable->master_id }}"
                                            data-url="{{ route('variable_management_add_permission_post') }}"
                                            class="btn btn-sm btn-clean btn-icon save-permission" title="Save Permission">
                                            <i class="icon-xl la la-save"></i>
                                        </a>
                                        <a href="javascript:void(0)" data-row="{{ json_encode($variable) }}"
                                            data-toggle="modal" data-target="#editVariable"
                                            class="btn btn-sm btn-clean btn-icon edit_variable" title="Edit">
                                            <i class="icon-xl la la-edit"></i>
                                        </a>
                                        <a href="javascript:void(0)"
                                            data-url="{{ route('variable_management_delete', ['id' => $variable->master_id]) }}"
                                            class="btn btn-sm btn-clean btn-icon delete_variable" title="Delete">
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
        <!--end::Card-->
    </div>

    <div class="modal fade" id="editVariable" tabindex="-1" role="dialog" aria-labelledby="editVariableLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form action="{{ route('variable_management_update') }}" name="variable_management_update_form" method="post">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Update Variable Information</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <i aria-hidden="true" class="ki ki-close"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="edit_variable_id">
                        <div class="form-group">
                            <label>Variable Name</label>
                            <input type="text" class="form-control form-control-solid" name="edit_variable_name"
                                required />
                        </div>
                        <div class="form-group">
                            <label>Variable Key</label>
                            <input type="text" class="form-control form-control-solid" name="edit_variable_key"
                                readonly />
                        </div>
                        <div class="form-group">
                            <label>Variable Description</label>
                            <textarea class="form-control" name="edit_variable_description" cols="30" rows="7"></textarea>
                        </div>
                        <!--begin: Table-->
                        <table class="table table-bordered table-hover">
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
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                name="edit_is_project_timeline">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                name="edit_is_timeline_mapping">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                name="edit_is_phase_change_sms">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                name="edit_is_review_request_sms">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                name="edit_is_client_banner_message">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                name="edit_is_automated_workflow_action">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="edit_is_mass_text">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                name="edit_is_email_template">
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <!--end: Table-->
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-light-success font-weight-bold">Submit</button>
                        <button type="button" class="btn btn-light-primary font-weight-bold"
                            data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@stop

@section('scripts')
    <script>
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        $('body').on('click', '.save-permission', function() {
            let this_save = $(this);
            let route_url = this_save.attr('data-url');
            let id = this_save.attr('data-id');
            let variable_row = $(".variable-row" + id);
            $.ajax({
                url: route_url,
                type: 'POST',
                data: {
                    '_token': CSRF_TOKEN,
                    'variable_id': id,
                    'is_project_timeline': variable_row.find('input[name="is_project_timeline"]').is(
                        ':checked') ? 1 : 0,
                    'is_timeline_mapping': variable_row.find('input[name="is_timeline_mapping"]').is(
                        ':checked') ? 1 : 0,
                    'is_phase_change_sms': variable_row.find('input[name="is_phase_change_sms"]').is(
                        ':checked') ? 1 : 0,
                    'is_review_request_sms': variable_row.find('input[name="is_review_request_sms"]').is(
                        ':checked') ? 1 : 0,
                    'is_client_banner_message': variable_row.find('input[name="is_client_banner_message"]')
                        .is(':checked') ? 1 : 0,
                    'is_automated_workflow_action': variable_row.find(
                        'input[name="is_automated_workflow_action"]').is(':checked') ? 1 : 0,
                    'is_mass_text': variable_row.find('input[name="is_mass_text"]').is(':checked') ? 1 : 0,
                    'is_email_template': variable_row.find('input[name="is_email_template"]').is(
                        ':checked') ? 1 : 0,
                },
                dataType: 'JSON',
                success: function(data) {
                    if (data.success) {
                        Swal.fire({
                            title: data.message,
                            icon: 'success',
                        });
                    } else {
                        Swal.fire({
                            title: data.message,
                            icon: 'error',
                        });
                    }
                }
            });
        });

        $('body').on('click', '.delete_variable', function() {
            Swal.fire({
                title: 'Are you sure to delete selected variable?',
                icon: 'warning',
                showDenyButton: true,
                showCancelButton: false,
                confirmButtonText: 'Delete',
                denyButtonText: `Cancel`,
            }).then((result) => {
                if (result.isConfirmed) {
                    var route_url = $(this).attr('data-url');
                    $.ajax({
                        url: route_url,
                        type: 'POST',
                        data: {
                            '_token': CSRF_TOKEN
                        },
                        dataType: 'JSON',
                        success: function(data) {
                            if (data.success) {
                                Swal.fire({
                                    title: data.message,
                                    icon: 'success',
                                }).then((result) => {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: data.message,
                                    icon: 'error',
                                });
                            }
                        }
                    });
                }
            });
        });

        $("body").on("change", "input.variable-active", async function() {
            let variable_id = $(this).val();
            $.ajax({
                type: "post",
                url: "variable_management/update_active",
                data: {
                    _token: CSRF_TOKEN,
                    variable_id: variable_id
                },
                success: function(response) {},
            }).done(function() {
                $(".loading").hide();
            });
        });

        $('body').on('click', '.edit_variable', function() {
            let data_row = JSON.parse($(this).attr('data-row'));
            $("input[name='edit_variable_id']").val(data_row.master_id);
            $("input[name='edit_variable_name']").val(data_row.variable_name);
            $("input[name='edit_variable_key']").val(data_row.variable_key);
            $("textarea[name='edit_variable_description']").val(data_row.variable_description);
            $("input[name='edit_is_project_timeline']").prop('checked', data_row.is_project_timeline);
            $("input[name='edit_is_timeline_mapping']").prop('checked', data_row.is_timeline_mapping);
            $("input[name='edit_is_phase_change_sms']").prop('checked', data_row.is_phase_change_sms);
            $("input[name='edit_is_review_request_sms']").prop('checked', data_row.is_review_request_sms);
            $("input[name='edit_is_client_banner_message']").prop('checked', data_row.is_client_banner_message);
            $("input[name='edit_is_automated_workflow_action']").prop('checked', data_row
                .is_automated_workflow_action);
            $("input[name='edit_is_mass_text']").prop('checked', data_row.is_mass_text);
            $("input[name='edit_is_email_template']").prop('checked', data_row.is_email_template);
        });
    </script>
@endsection
