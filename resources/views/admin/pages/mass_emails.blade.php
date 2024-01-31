@extends('admin.layouts.default')

@section('title', 'VineConnect - Admin - Mass Email Messaging Tool')

@section('content')

    <!--begin::Subheader-->
    <div class="subheader py-2 py-lg-4 subheader-solid" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
            <!--begin::Info-->
            <div class="d-flex align-items-center flex-wrap mr-2">
                <!--begin::Page Title-->
                <h5 class="text-dark font-weight-bold mt-2 mb-2 mr-5">Mass Email Messaging Tool</h5>
                <!--end::Page Title-->

            </div>
            <!--end::Info-->
        </div>
    </div>
    <div class="d-flex flex-column-fluid">
        <!--begin::Container-->
        <div class="container">
            <!--begin::Row-->
            <div class="row">
                <div class="col-md-12">
                    <!--begin::Card-->
                    <div class="card card-custom gutter-b example example-compact">
                        <div class="card-header">
                            <h5 class="card-title mt-7">Create, Send, and Review Mass Email Message Jobs</h5>
                        </div>
                        <div class="card-body">
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-md-12">
                                        <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link active btn btn-outline-primary"
                                                    id="createJobNavLink" type="button">Start A New Job</button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link btn btn-outline-primary" id="logsNavLink"
                                                    type="button">Job Logs</button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link btn btn-outline-primary" id="allLogsNavLink"
                                                    type="button">Job History</button>
                                            </li>
                                        </ul>
                                        <div class="tab-content" id="pills-tabContent">
                                            <div class="tab-pane fade show active" id="pillsCreateJob">
                                                <p><b>Instructions:</b> Use this tool send one-time mass email messages to a
                                                    defined list of recipients. You can define your recipients by segmenting
                                                    your Filevine contacts or by uploading a templated CSV file. To send to
                                                    your Filevine contacts, use the "Fetch Contacts In Filevine" option to
                                                    select a Label available from your Filevine Org. Choose "Send With CSV
                                                    File" to download and populate a CSV template to complete your job.
                                                    After configuring your recipients, you will have the opportunity to
                                                    write your Email message, review, and send your job.
                                                </p>
                                                <div class="clear"></div>
                                                <div class="callout_subtle lightgrey" style="margin-bottom:25px;"><i
                                                        class="fas fa-link" style="color:#383838;padding-right:5px;"></i>
                                                    Support Article: <a
                                                        href="https://intercom.help/vinetegrate/en/articles/6206905-mass-emails"
                                                        target="_blank" />Mass Email Messaging Jobs</a></div>
                                                <div class="callout_subtle lightgrey"><i class="fa fa-key mr-3"></i><a
                                                        href="{{ url('admin/variables') }}" target="_blank" />&nbsp;List of
                                                    Variables</a></div>
                                                <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link active btn btn-outline-success"
                                                            id="createFetchContactsNavLink" type="button">Fetch Contacts In
                                                            Filevine</button>
                                                    </li>
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link btn btn-outline-success"
                                                            id="uploadFileNavLink" type="button">Send With CSV
                                                            File</button>
                                                    </li>
                                                </ul>
                                                <div class="tab-content" id="pills-tabContent" style="margin-top:25px;">
                                                    <div class="tab-pane fade show active" id="fetchContactsTab">
                                                        <form class="row" id="fetchContactsForm">
                                                            <div class="col-md-3">
                                                                <label for="personTypeLabel">Choose a Person/Contact Type
                                                                    Label<span class="text-danger">*</span></label>
                                                                <select name="person_type" id="personTypeLabel"
                                                                    class="form-control" required>
                                                                    <option value="" selected="selected">Available
                                                                        Labels...
                                                                    </option>
                                                                    @if (isset($person_types->allowedValues))
                                                                        @foreach ($person_types->allowedValues as $allowed_value)
                                                                            <option value="{{ $allowed_value->value }}"
                                                                                data-name="{{ $allowed_value->name }}">
                                                                                {{ $allowed_value->name }}
                                                                                ({{ $allowed_value->value }})
                                                                            </option>
                                                                        @endforeach
                                                                    @endif
                                                                </select>
                                                            </div>
                                                            <div class="col-auto d-none exclude_blacklist_div">
                                                                <p>Optional Filters</p>
                                                                <div class="px-5">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        name="is_exclude_blacklist" id="isExcludeBlackList">
                                                                    <label class="form-check-label"
                                                                        for="isExcludeBlackList">
                                                                        Exclude Blacklist Contacts
                                                                    </label>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-1">
                                                                <button class="btn btn-warning mt-7" type="submit"
                                                                    id="fetchingContactsBtn">FETCH</button>
                                                            </div>
                                                            <div class="col">
                                                                <div class="align-items-center d-none h-100"
                                                                    id="fetchingContactsLoading">
                                                                    <div class="mr-5">
                                                                        <img src="/assets/img/loading.gif">
                                                                    </div>
                                                                    <div>Fetching Contacts...</div>
                                                                </div>
                                                                <div id="countResult" class="mt-8 d-none">
                                                                    <div class="d-flex">
                                                                        <img src="/assets/img/green-checkmark.png"
                                                                            class="mr-3 w-20px">
                                                                        <p class="m-0"></p>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </form>
                                                    </div>
                                                    <div class="tab-pane fade" id="uploadFileTab">
                                                        <p>
                                                            To send a job using a CSV file, start by downloading the CSV
                                                            template
                                                            below. Transpose your data file into the correct headers
                                                            following the correct format for each column: name (eg.
                                                            "Sally Jones"), email (eg. "john@gmail.com").</p>
                                                        <p>Don't have a data file? Run a Filevine Report! Choose the
                                                            pre-filled "Marketing List" report from the report choices. Be
                                                            sure to include the full name
                                                            and email columns and filter as you wish. Save
                                                            the exported Excel file and transpose the data in to the correct
                                                            columns on our CSV template.</p>

                                                        <div class="clear"></div>
                                                        <div class="callout_subtle lightgrey" style="margin-bottom: 25px">
                                                            <i class="far fa-file"
                                                                style="color:#383838;padding-right:5px;"></i> Download: <a
                                                                href="{{ asset('sample_templates/mass_emails.csv') }}"
                                                                download>CSV Template</a>
                                                        </div>

                                                        <form id="csvFileUploadForm" method="post"
                                                            enctype="multipart/form-data" class="row">
                                                            <div class="col-auto">
                                                                <div class="form-group mt-3">
                                                                    <input name="csv_file" id="upload_file"
                                                                        type="file" style="display:none;" />
                                                                    <label for="upload_file" class="col-md-12 ml-0">
                                                                        <span class="file-upload-button">Upload a
                                                                            File</span>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                            <div class="col-auto ml-6">
                                                                <button class="btn btn-success" type="submit"
                                                                    id="uploadCsvBtn">Upload</button>
                                                            </div>
                                                            <div class="col">
                                                                <div class="align-items-center d-none h-100"
                                                                    id="fetchingCsvContactsLoading">
                                                                    <div class="mr-5">
                                                                        <img src="/assets/img/loading.gif">
                                                                    </div>
                                                                    <div> Fetching Contacts...</div>
                                                                </div>
                                                                <div id="countResultCsv" class="mt-3 d-none">
                                                                    <div class="d-flex">
                                                                        <img src="/assets/img/green-checkmark.png"
                                                                            class="mr-3 w-20px">
                                                                        <p class="m-0"></p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                                <form class="row d-none" id="reviewJobForm">
                                                    <div class="col-12 mt-4">
                                                        <p>Email Campaign Name</p>
                                                        <div class="form-group">
                                                            <input class="form-control" required id="reviewJobTitle" />
                                                        </div>

                                                        <p>Email Message Body (Available Variables: {{ $variable_keys }}) <a
                                                                class="btn btn-grey copy-button"
                                                                onclick="copyContent(this,'{{ $variable_keys }}')"
                                                                data-toggle="tooltip" data-placement="top"
                                                                title="Copy Variable">
                                                                <span class="fa far fa-copy"></span>
                                                            </a>
                                                        </p>
                                                        <div class="form-group">
                                                            <textarea class="form-control review-job-message" required id="reviewJobMessage">Hi [client_firstname]! Your case with [law_firm_name] has an update! Please log into our Client Portal to view: https://[tenantname].vinetegrate.com.</textarea>
                                                        </div>


                                                        <div class="form-group row col-md-12 mt-6">
                                                            <label class="mr-6">Job Schedule Time?</label>
                                                            <div class="checkbox-inline">
                                                                <label
                                                                    class="checkbox checkbox-outline checkbox-outline-2x checkbox-primary checkbox-lg">
                                                                    <input type="checkbox" name="is_schedule_job">
                                                                    <span></span>
                                                                </label>
                                                            </div>
                                                        </div>

                                                        <div
                                                            class="form-group row col-md-12 mt-6 d-none schedule-time-div">
                                                            <label class="mr-6">Schedule Time</label>
                                                            <div class="col-lg-4 col-md-9 col-sm-12">
                                                                <div class="input-group date" id="kt_datetimepicker_1"
                                                                    data-target-input="nearest">
                                                                    <input type="text" name="schedule_time"
                                                                        class="form-control datetimepicker-input"
                                                                        placeholder="Select date &amp; time"
                                                                        data-target="#kt_datetimepicker_1" />
                                                                    <div class="input-group-append"
                                                                        data-target="#kt_datetimepicker_1"
                                                                        data-toggle="datetimepicker">
                                                                        <span class="input-group-text">
                                                                            <i class="ki ki-calendar"></i>
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="form-group mt-3">
                                                            <button class="btn btn-success p-2" type="submit">REVIEW
                                                                JOB</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="tab-pane fade" id="pillsProfile">
                                                <!--begin: Datatable-->
                                                <table class="table table-bordered table-hover"
                                                    id="mass_message_datatable">
                                                    <thead>
                                                        <tr>
                                                            <th title="Field #1">Created At</th>
                                                            <th title="Field #2">CSV</th>
                                                            <th title="Field #3">Fetch</th>
                                                            <th title="Field #4">Note</th>
                                                            <th title="Field #5">Total Emails</th>
                                                            <th title="Field #6">Progress</th>
                                                            <th title="Field #7">Created By</th>
                                                            <th title="Field #8">Details</th>
                                                            <th title="Field #9">Export</th>
                                                            <th title="Field #10">Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($mass_messages as $mass_message)
                                                            <tr>
                                                                <td>{{ date_format($mass_message->created_at, 'Y-m-d H:i:s') }}
                                                                </td>
                                                                <td>{!! $mass_message->is_upload_csv ? '<img src="/assets/img/green-checkmark.png" class="w-20px">' : '' !!}</td>
                                                                <td>{!! $mass_message->is_fetch ? '<img src="/assets/img/green-checkmark.png" class="w-20px">' : '' !!}</td>
                                                                <td>{{ $mass_message->note }}</td>
                                                                <td>{{ $mass_message->mass_email_logs->count() }}</td>
                                                                <td>
                                                                    <div class="progress">
                                                                        <div class="progress-bar" role="progressbar"
                                                                            style="width: {{ $mass_message->progress }}%;"
                                                                            aria-valuenow="25" aria-valuemin="0"
                                                                            aria-valuemax="100">
                                                                            {{ $mass_message->progress }}%</div>
                                                                    </div>
                                                                </td>
                                                                <td>{{ $mass_message->created_by }}</td>
                                                                <td>
                                                                    <button type="button"
                                                                        data-id="{{ $mass_message->id }}"
                                                                        data-toggle="modal" data-target="#massMessageLog"
                                                                        class="btn btn-hover-bg-secondary btn-icon show-mass-message-logs">
                                                                        <i class="fa fa-eye"></i>
                                                                    </button>
                                                                </td>
                                                                <td>
                                                                    <a type="button"
                                                                        href="{{ route('mass_emails_exportcsv', ['subdomain' => $subdomain, 'id' => $mass_message->id]) }}"
                                                                        class="btn btn-hover-bg-secondary btn-icon">
                                                                        <i class="la la-file-text-o"></i>
                                                                    </a>
                                                                </td>
                                                                <td>
                                                                    @if ($mass_message->progress < 100)
                                                                        <button type="button"
                                                                            class="btn btn-sm btn-danger remove"
                                                                            data-id="{{ $mass_message->id }}"
                                                                            data-container="body" data-toggle="tooltip"
                                                                            data-placement="top" title="Delete"><i
                                                                                class="fa fa-trash"></i></button>
                                                                        @if ($mass_message->is_schedule_job)
                                                                            <button type="button"
                                                                                class="btn btn-sm btn-success send-now"
                                                                                data-id="{{ $mass_message->id }}"
                                                                                data-container="body"
                                                                                data-toggle="tooltip" data-placement="top"
                                                                                title="Send Now"><i
                                                                                class="fa fa-rocket"></i></button>
                                                                        @endif
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                                <!--end: Datatable-->
                                            </div>
                                            <div class="tab-pane fade" id="pillsAllLogs">
                                                <!--begin::Card-->
                                                <div class="card card-custom"
                                                    style="-webkit-box-shadow: none;-moz-box-shadow: none;-o-box-shadow: none;box-shadow: none;">
                                                    <div class="card-header flex-wrap border-0 pb-0" style="padding:0">
                                                        <div class="card-title">
                                                            <h3 class="card-label">All Mass Email Logs</h3>
                                                        </div>
                                                        <div class="card-toolbar">
                                                            <!--begin::Dropdown-->
                                                            <div class="dropdown dropdown-inline mr-2">
                                                                <div class="row">
                                                                    <div class="col-6">
                                                                        <form class="log-form">
                                                                            <div id="logreportrange"
                                                                                class="custom-date-picker">
                                                                                <i class="fa fa-calendar"></i>&nbsp;
                                                                                <span></span> <i
                                                                                    class="fa fa-caret-down"></i>
                                                                            </div>
                                                                        </form>
                                                                    </div>
                                                                    <div class="col-6 text-right">
                                                                        <button type="button"
                                                                            class="btn btn-light-primary font-weight-bolder dropdown-toggle"
                                                                            data-toggle="dropdown" aria-haspopup="true"
                                                                            aria-expanded="false">
                                                                            <span class="svg-icon svg-icon-md">
                                                                                <!--begin::Svg Icon | path:assets/media/svg/icons/Design/PenAndRuller.svg-->
                                                                                <i class="icon-xl la la-print"></i>
                                                                                <!--end::Svg Icon-->
                                                                            </span>Export</button>
                                                                        <!--begin::Dropdown Menu-->
                                                                        <div
                                                                            class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                                                                            <!--begin::Navigation-->
                                                                            <ul class="navi flex-column navi-hover py-2">
                                                                                <li
                                                                                    class="navi-header font-weight-bolder text-uppercase font-size-sm text-primary pb-2">
                                                                                    Choose an option:</li>
                                                                                <li class="navi-item">
                                                                                    <a href="{{ route('mass_emails_custom_logs_csv', ['subdomain' => $subdomain]) }}"
                                                                                        class="navi-link export-custom-log">
                                                                                        <span class="navi-icon">
                                                                                            <i
                                                                                                class="la la-file-text-o"></i>
                                                                                        </span>
                                                                                        <span class="navi-text">CSV</span>
                                                                                    </a>
                                                                                </li>
                                                                            </ul>
                                                                            <!--end::Navigation-->
                                                                        </div>
                                                                        <!--end::Dropdown Menu-->
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!--end::Dropdown-->
                                                        </div>
                                                    </div>
                                                    <div class="card-body" style="padding:0">
                                                        {{-- <table class="table table-bordered table-hover" --}}
                                                        {{-- id="kt_datatable_mass_messages_custom_logs"></table> --}}

                                                        <!--begin: Datatable-->
                                                        <table class="table table-bordered table-hover"
                                                            id="mass_message_log_datatable">
                                                            <thead>
                                                                <tr>
                                                                    <th title="Field #1">Client Name</th>
                                                                    <th title="Field #2">Client Email</th>
                                                                    <th title="Field #2">CC Email</th>
                                                                    <th title="Field #3">Message Body</th>
                                                                    <th title="Field #4">Created At</th>
                                                                    <th title="Field #5">Sent At</th>
                                                                    <th title="Field #6">Status</th>
                                                                    <th title="Field #7">Note</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($mass_message_logs as $mass_message_log)
                                                                    <tr>
                                                                        <td>{{ $mass_message_log->person_name }}</td>
                                                                        <td>{{ $mass_message_log->person_email }}</td>
                                                                        <td>{{ $mass_message_log->cc_email }}</td>
                                                                        <td>{{ $mass_message_log->mass_email->message_body }}
                                                                        </td>
                                                                        <td>{{ $mass_message_log->created_at }}</td>
                                                                        <td>{{ $mass_message_log->sent_at }}</td>
                                                                        <td>{!! $mass_message_log->is_sent ? '<img src="/assets/img/green-checkmark.png" class="w-20px">' : '' !!}</td>
                                                                        <td>{{ $mass_message_log->note }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                        <!--end: Datatable-->
                                                    </div>
                                                </div>
                                                <!--end::Card-->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="massMessageLog" tabindex="-1" role="dialog" aria-labelledby="massMessageLog"
                aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document" style="max-width:1300px">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="updatePlanModal">Mass Email Logs</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="w-100">
                                <table class="datatable datatable-bordered datatable-head-custom"
                                    id="kt_datatable_mass_messages_logs"></table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="messageBodyDetails" tabindex="-1" role="dialog"
                aria-labelledby="messageBodyDetails" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="updatePlanModal">Mass Email Body</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="w-100">
                                <p id="mass_messages_body"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
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
    </style>
@endsection
