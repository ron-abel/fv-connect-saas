@extends('admin.layouts.default')

@section('title', 'VineConnect Admin - Project Form')

@section('content')
    <!--begin::Subheader-->
    <div class="subheader py-2 py-lg-4 subheader-solid" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
            <!--begin::Info-->
            <div class="d-flex align-items-center flex-wrap mr-2">
                <!--begin::Page Title-->
                <h5 class="text-dark font-weight-bold mt-2 mb-2 mr-5">Filevine Forms</h5>
                <!--end::Page Title-->
            </div>
            <!--end::Info-->
        </div>
    </div>
    <div class="container">
        <div class="row form-list">
            <div class="col-md-12">
                <div class="card card-custom">
                    <div class="card-header ml-3">
                        <div class="row py-2">
                            <div class="clear"></div>
                            <h4 class="card-title mt-5">Form List</h4>
                            <div class="callout_subtle lightgrey ml-5"><i class="fas fa-link" style="color:#383838;"></i>
                                Support Article: <a
                                    href="https://intercom.help/vinetegrate/en/articles/6698587-filevine-forms"
                                    target="_blank" />Filevine Forms</a></div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row mt-5">
                            <div class="col-md-2 mb-4"><b>Name</b></div>
                            <div class="col-md-2 mb-4"><b>Decsription</b></div>
                            <div class="col-md-2 mb-4"><b>Form Type</b></div>
                            <div class="col-md-1 mb-4"><b>Eligible</b></div>
                            <div class="col-md-2 mb-4"><b>Date Created</b></div>
                            <div class="col-md-3 mb-4"><b>Actions</b></div>
                        </div>
                        @foreach ($forms as $form)
                            <div class="row mt-5">
                                <div class="col-md-2"><a href="form_view/{{ $form->id }}">{{ $form->form_name }}</a>
                                </div>
                                <div class="col-md-2 mb-4" data-toggle="tooltip" data-placement="top"
                                    title="{{ strlen($form->form_description) > 30 ? $form->form_description : '' }}">
                                    {{ strlen($form->form_description) > 30 ? substr($form->form_description, 0, 30) . '...' : $form->form_description }}
                                </div>
                                <div class="col-md-2">{{ $form->is_public_form ? 'Public Forms' : 'Current Clients' }}</div>
                                <div class="col-md-1">
                                    <label class="custom-checkbox-switch">
                                        <input type="checkbox"
                                            onclick="toggleFormEligibity(this, {{ $form->id }}, {{ $form->is_active }})"
                                            {{ $form->is_active == 1 ? 'checked' : '' }}>
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                                <div class="col-md-2 mb-4">{{ $form->created_at }}</div>
                                <div class="col-md-3">
                                    <a href="{{ url('admin/form/' . $form->id) }}" class="btn btn-primary">
                                        <span class="fa fa-edit"></span>
                                    </a>
                                    <a href="{{ url('admin/form/responses/' . $form->id) }}" class="btn btn-success"
                                        data-toggle="tooltip" data-placement="top"
                                        title="{{ $form->responses }} Responses">
                                        <span class="fa fa-eye"></span>
                                    </a>
                                    <button class="btn btn-danger" onclick="deleteForm(this, {{ $form->id }})">
                                        <span class="fa fa-trash"></span>
                                    </button>
                                    @if ($form->is_public_form)
                                        <button class="btn btn-grey copy-button"
                                            onclick="copyFormPublicLink(this,'{{ url('/share/views/open/form') }}/{{ $form->id }}')"
                                            data-toggle="tooltip" data-placement="top" title="Copy">
                                            <span class="fa far fa-copy"></span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                        <div class="row" style="margin-top: 35px">
                            <div class="ml-2">
                                <a href="{{ url('admin/form/') }}" class="btn btn-primary">Add A New Form</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-6">
        <div class="row">
            <div class="col-md-12">
                <!--begin::Card-->
                <div class="card card-custom"
                    style="-webkit-box-shadow: none;-moz-box-shadow: none;-o-box-shadow: none;box-shadow: none;">
                    <div class="card-header flex-wrap">
                        <div class="card-title">
                            <h3 class="card-label">Form Logs</h3>
                        </div>
                        <div class="card-toolbar">
                            <!--begin::Dropdown-->
                            <div class="dropdown dropdown-inline mr-2">
                                <div class="row">
                                    <div class="col-6">
                                        <form class="log-form">
                                            <div id="logreportrange" class="custom-date-picker">
                                                <i class="fa fa-calendar"></i>&nbsp;
                                                <span></span> <i class="fa fa-caret-down"></i>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="col-6 text-right">
                                        <button type="button"
                                            class="btn btn-light-primary font-weight-bolder dropdown-toggle"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <span class="svg-icon svg-icon-md">
                                                <i class="icon-xl la la-print"></i>
                                            </span>Export</button>
                                        <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                                            <ul class="navi flex-column navi-hover py-2">
                                                <li
                                                    class="navi-header font-weight-bolder text-uppercase font-size-sm text-primary pb-2">
                                                    Choose an option:</li>
                                                <li class="navi-item">
                                                    <a href="{{ route('form_response_logs_csv', ['subdomain' => $subdomain]) }}"
                                                        class="navi-link export-custom-log">
                                                        <span class="navi-icon">
                                                            <i class="la la-file-text-o"></i>
                                                        </span>
                                                        <span class="navi-text">CSV</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                        <!--end::Dropdown Menu-->
                                    </div>
                                </div>
                            </div>
                            <!--end::Dropdown-->
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-hover" id="forms_log_datatable">
                            <thead>
                                <tr>
                                    <th title="Field #1">Form Name</th>
                                    <th title="Field #2">Project ID</th>
                                    <th title="Field #3">Client ID</th>
                                    <th title="Field #4">Response Completion</th>
                                    <th title="Field #5">Timestamp</th>
                                    <th title="Field #6">Details</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
                <!--end::Card-->
            </div>
        </div>
    </div>

    <!-- Logs Details Modal-->
    <div class="modal fade" id="logDetails" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Form Field & Submitted Data Information</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i aria-hidden="true" class="ki ki-close"></i>
                    </button>
                </div>
                <div class="modal-body" style="padding-top:0px">
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">Form Field</th>
                                <th scope="col">Submitted Value</th>
                            </tr>
                        </thead>
                        <tbody class="log_table_body">
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-primary font-weight-bold"
                        data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

@stop
@section('scripts')
    <script>
        var form_id = 0;
        var site_url = "{{ url('/') }}";
    </script>
    <script src="{{ asset('../js/admin/forms.js') }}"></script>
@endsection
