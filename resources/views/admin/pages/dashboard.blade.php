@extends('admin.layouts.default')

@section('title', 'VineConnect Admin - Dashboard')

@section('content')

    <!--begin::Subheader-->
    <div class="subheader py-2 py-lg-4 subheader-solid" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
            <!--begin::Info-->
            <div class="d-flex align-items-center flex-wrap mr-2 ">
                <!--begin::Page Title-->
                {{-- <h5 class="text-dark font-weight-bold mt-2 mb-2 mr-5">Dashboard</h5> --}}
                <h4 class="text-dark font-weight-bold mt-2 mb-2 mr-5">VineConnect Usage Dashboard</h4>
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
        <div class="container">
            <!--begin::Dashboard-->
            <!--begin::Row-->
            <div class="row">
                <div class="col-md-6">
                    <!--begin::Card-->
                    <div class="card card-custom gutter-b example example-compact">
                        <div class="card-header">
                            <h3 class="card-title">Daily Client Logins</h3>
                        </div>
                        <div class="card-body">
                            <div class="text-right">
                                <h2 class="font-light m-b-0"><i class="ti-arrow-up text-success"></i>
                                    <span id="today_client"></span>
                                </h2>
                                <span class="text-muted">Total</span>
                            </div>
                        </div>
                        {{-- <div class="card-footer">

                            </div> --}}

                    </div>
                    <!--end::Card-->
                </div>
                <!--begin::Second Column-->
                <div class="col-md-6">
                    <!--begin::Card-->
                    <div class="card card-custom gutter-b example example-compact">
                        <div class="card-header">
                            <h3 class="card-title">Weekly Client Logins</h3>
                        </div>
                        <div class="card-body">
                            <div class="text-right">
                                <h2 class="font-light m-b-0"><i class="ti-arrow-up text-success"></i>
                                    <span id="week_client"></span>
                                </h2>
                                <span class="text-muted">Total</span>
                            </div>
                        </div>
                        {{-- <div class="card-footer">

                            </div> --}}
                    </div>
                    <!--end::Card-->
                </div>
                <!--end::Second Column-->
                <!--begin::Third Column-->
                <div class="col-md-12">
                    <!--begin::Card-->
                    <div class="card card-custom gutter-b example example-compact">
                        <div class="card-header">
                            <h3 class="card-title">Weekly LookUp</h3>
                        </div>
                        <div class="card-body">
                            <div class="text-right pb-3 pr-5 mr-5"><span class="color-blue py-1 px-5">'</span><span
                                    class="pl-2">Lookup Count</span></div>
                            <canvas id="myChart" class="w-100" height="230"></canvas>
                        </div>
                        {{-- <div class="card-footer">

                            </div> --}}
                    </div>
                    <!--end::Card-->
                </div>
                <!--end::Third Column-->

                <!--begin::Fourth Column-->
                <div class="col-md-12">
                    <!--begin::Card-->
                    <div class="card card-custom gutter-b example example-compact">
                        <div class="card-header flex-wrap border-0 pt-6 pb-0">
                            <div class="card-title">
                                <h3 class="card-label">Client Portal Usage Log</h3>
                            </div>
                            <div class="card-toolbar">
                                <!--begin::Dropdown-->
                                <div class="dropdown dropdown-inline mr-2">
                                    <div class="row">
                                        <div class="col-9">
                                            <form class="log-form-log">
                                                <input type="hidden" name="log_start" value="{{ $start_date_log }}" />
                                                <input type="hidden" name="log_end" value="{{ $end_date_log }}" />
                                                <input type="hidden" name="log_start_date" value="{{ $start_date }}" />
                                                <input type="hidden" name="log_end_date" value="{{ $end_date }}" />
                                                <input type="hidden" name="msg_start_date" value="{{ $start_date_msg }}" />
                                                <input type="hidden" name="msg_end_date" value="{{ $end_date_msg }}" />
                                                <input type="hidden" name="startDateLogTrouble"
                                                    value="{{ $startDateLogTrouble }}" />
                                                <input type="hidden" name="endDateLogTrouble"
                                                    value="{{ $endDateLogTrouble }}" />
                                                <input type="hidden" name="login_status" value="" />

                                                <div class="form-group row">
                                                    <div class="col-4">
                                                        <input type="text" value="{{ $client_name }}"
                                                            class="form-control" name="client_name" id="client_name"
                                                            placeholder="Search Client Name">
                                                    </div>
                                                    <div class="col-4">
                                                        <select name="login_status" id="login_status_field"
                                                            class="form-control">
                                                            <option value="">Login Status</option>
                                                            <option value="1"
                                                                {{ $login_status == '1' ? 'selected' : '' }}>
                                                                Successful</option>
                                                            <option value="0"
                                                                {{ $login_status == '0' ? 'selected' : '' }}>
                                                                Unsuccessful</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-4">
                                                        <div id="logreportrangelog" class="custom-date-picker">
                                                            <i class="fa fa-calendar"></i>&nbsp;
                                                            <span></span> <i class="fa fa-caret-down"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="col-3 text-right">
                                            <button type="button"
                                                class="btn btn-light-primary font-weight-bolder dropdown-toggle"
                                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <span class="svg-icon svg-icon-md">
                                                    <!--begin::Svg Icon | path:assets/media/svg/icons/Design/PenAndRuller.svg-->
                                                    <i class="icon-xl la la-print"></i>
                                                    <!--end::Svg Icon-->
                                                </span>Export</button>
                                            <!--begin::Dropdown Menu-->
                                            <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                                                <!--begin::Navigation-->
                                                <ul class="navi flex-column navi-hover py-2">
                                                    <li
                                                        class="navi-header font-weight-bolder text-uppercase font-size-sm text-primary pb-2">
                                                        Choose an option:</li>
                                                    <li class="navi-item">
                                                        <a href="{{ route('usage_log_export_csv', ['subdomain' => $subdomain]) }}?{{ 'log_start=' . $start_date_log . '&log_end=' . $end_date_log . '&login_status=' . $login_status }}"
                                                            class="navi-link">
                                                            <span class="navi-icon">
                                                                <i class="la la-file-text-o"></i>
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
                        <div class="card-body">
                            <div class="table-responsive m-t-40">
                                <p><b>INSTRUCTIONS:</b> To determine the error code or reason of an unsuccessful login,
                                    click the red <b>X</b> under the Success column.</p>
                                <table class="table stylish-table no-wrap">
                                    <thead>
                                        <tr>
                                            <th class="border-top-0">Name Entry</th>
                                            <th class="border-top-0">Matched Client</th>
                                            <th class="border-top-0">Phone</th>
                                            <th class="border-top-0">Email</th>
                                            <th class="border-top-0">Matched Project Id</th>
                                            <th class="border-top-0">Success</th>
                                            <th class="border-top-0">Timestamp</th>
                                        </tr>
                                    </thead>
                                    <tbody id="lookupData">
                                        @foreach ($logs as $log)
                                            <tr>
                                                <td class="border-top-0">
                                                    <span class="round">{{ $log->Lookup_Name }}</span>
                                                </td>
                                                <td class="border-top-0">{{ $log->Result_Client_Name }}</td>
                                                <td class="border-top-0">{{ $log->Lookup_Phone_num }}</td>
                                                <td class="border-top-0">{{ $log->lookup_email }}</td>
                                                <td class="border-top-0">{{ $log->Result_Project_Id }}</td>
                                                @if ($log->Result == 1)
                                                    <td class="border-top-0"><i class="fa fa-check text-success"></td>
                                                @else
                                                    <td class="border-top-0 log-note-details" data-toggle="modal"
                                                        data-value="{{ json_encode($log->note) }}"
                                                        data-target="#logNote"><i style="cursor: pointer"
                                                            class="fa fa-times text-danger"></td>
                                                @endif
                                                <td class="border-top-0">
                                                    {{ \Carbon\Carbon::parse($log->created_at)->timezone('America/Vancouver')->format('Y-m-d H:i:s') }}
                                                </td>
                                            </tr>
                                        @endforeach

                                    </tbody>
                                </table>
                            </div>
                            {!! $logs->appends($_GET)->links('admin.pages.pagination') !!}

                        </div>
                        {{-- <div class="card-footer">

                            </div> --}}
                    </div>
                    <!--end::Card-->
                </div>
                <!--end::Fourth Column-->

                <div class="col-md-12">
                    <!--begin::Card-->
                    <div class="card card-custom gutter-b example example-compact">
                        <div class="card-header flex-wrap border-0 pt-6 pb-0">
                            <div class="card-title">
                                <h3 class="card-label">Failed Login Information</h3>
                            </div>
                            <div class="card-toolbar">
                                <!--begin::Dropdown-->
                                <div class="dropdown dropdown-inline mr-2">
                                    <div class="row">
                                        <div class="col-6">
                                            <form class="log-form-trouble">
                                                <input type="hidden" name="startDateLogTrouble"
                                                    value="{{ $startDateLogTrouble }}" />
                                                <input type="hidden" name="endDateLogTrouble"
                                                    value="{{ $endDateLogTrouble }}" />
                                                <input type="hidden" name="log_start" value="{{ $start_date_log }}" />
                                                <input type="hidden" name="log_end" value="{{ $end_date_log }}" />
                                                <input type="hidden" name="log_start_date"
                                                    value="{{ $start_date }}" />
                                                <input type="hidden" name="log_end_date" value="{{ $end_date }}" />
                                                <input type="hidden" name="msg_start_date"
                                                    value="{{ $start_date_msg }}" />
                                                <input type="hidden" name="msg_end_date" value="{{ $end_date_msg }}" />
                                                <div id="logreportrangetrouble" class="custom-date-picker">
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
                                            <!--begin::Dropdown Menu-->
                                            <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                                                <!--begin::Navigation-->
                                                <ul class="navi flex-column navi-hover py-2">
                                                    <li
                                                        class="navi-header font-weight-bolder text-uppercase font-size-sm text-primary pb-2">
                                                        Choose an option:</li>
                                                    <li class="navi-item">
                                                        <a href="{{ route('submitted_log_exportcsv', ['subdomain' => $subdomain]) }}?{{ 'log_start=' . $startDateLogTrouble . '&log_end=' . $endDateLogTrouble }}"
                                                            class="navi-link">
                                                            <span class="navi-icon">
                                                                <i class="la la-file-text-o"></i>
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
                        <div class="card-body">
                            <div class="table-responsive m-t-40">
                                <table class="table stylish-table no-wrap">
                                    <thead>
                                        <tr>
                                            <th class="border-top-0">Name Entry</th>
                                            <th class="border-top-0">Phone</th>
                                            <th class="border-top-0">Email</th>
                                            <th class="border-top-0">Client IP</th>
                                            <th class="border-top-0">Timestamp</th>
                                            <th class="border-top-0">Status</th>
                                            <th class="border-top-0">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="lookupData">
                                        @foreach ($logTroubles as $logTrouble)
                                            <tr>
                                                <td class="border-top-0">
                                                    <span
                                                        class="round">{{ $logTrouble->lookup_first_name . ' ' . $logTrouble->lookup_last_name }}</span>
                                                </td>
                                                <td class="border-top-0">{{ $logTrouble->lookup_phone }}</td>
                                                <td class="border-top-0">{{ $logTrouble->lookup_email }}</td>
                                                <td class="border-top-0">{{ $logTrouble->client_ip }}</td>
                                                <td class="border-top-0">
                                                    {{ \Carbon\Carbon::parse($logTrouble->created_at)->format('Y-m-d H:i:s') }}
                                                </td>
                                                @if ($logTrouble->is_handled)
                                                    <td class="border-top-0"><i class="fa fa-check text-success"></td>
                                                @else
                                                    <td class="border-top-0"></td>
                                                @endif
                                                <td class="border-top-0">
                                                    <button type="button" class="btn btn-secondary log-trouble-details"
                                                        data-toggle="modal" data-value="{{ json_encode($logTrouble) }}"
                                                        data-target="#matchToClient"><i class="fas fa-search"></i> Find
                                                        Matching Client</button>
                                                    <button type="button" data-id="{{ $logTrouble->id }}"
                                                        class="btn btn-sm btn-danger remove-failed-submit-log"
                                                        data-container="body" data-toggle="tooltip" data-placement="top"
                                                        title="Delete Log"><i class="fa fa-trash"></i></button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            {!! $logTroubles->appends($_GET)->links('admin.pages.pagination') !!}
                        </div>
                    </div>
                    <!--end::Card-->
                </div>

                <!--begin::Card-->
                <div class="col-md-12">
                    <div class="card card-custom mt-6">
                        <div class="card-header flex-wrap border-0 pt-6 pb-0">
                            <div class="card-title">
                                <h3 class="card-label">Client Feedback Log</h3>
                            </div>
                            <div class="card-toolbar">
                                <!--begin::Dropdown-->
                                <div class="dropdown dropdown-inline mr-2">
                                    <div class="row">
                                        <div class="col-6">
                                            <form class="log-form">
                                                <input type="hidden" name="log_start" value="{{ $start_date_log }}" />
                                                <input type="hidden" name="log_end" value="{{ $end_date_log }}" />
                                                <input type="hidden" name="log_start_date"
                                                    value="{{ $start_date }}" />
                                                <input type="hidden" name="log_end_date" value="{{ $end_date }}" />
                                                <input type="hidden" name="msg_start_date"
                                                    value="{{ $start_date_msg }}" />
                                                <input type="hidden" name="msg_end_date" value="{{ $end_date_msg }}" />
                                                <input type="hidden" name="startDateLogTrouble"
                                                    value="{{ $startDateLogTrouble }}" />
                                                <input type="hidden" name="endDateLogTrouble"
                                                    value="{{ $endDateLogTrouble }}" />
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
                                                    <!--begin::Svg Icon | path:assets/media/svg/icons/Design/PenAndRuller.svg-->
                                                    <i class="icon-xl la la-print"></i>
                                                    <!--end::Svg Icon-->
                                                </span>Export</button>
                                            <!--begin::Dropdown Menu-->
                                            <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                                                <!--begin::Navigation-->
                                                <ul class="navi flex-column navi-hover py-2">
                                                    <li
                                                        class="navi-header font-weight-bolder text-uppercase font-size-sm text-primary pb-2">
                                                        Choose an option:</li>
                                                    <li class="navi-item">
                                                        <a href="{{ route('feedbacks_export_csv', ['subdomain' => $subdomain]) }}?{{ 'log_start_date=' . $start_date . '&log_end_date=' . $end_date }}"
                                                            class="navi-link">
                                                            <span class="navi-icon">
                                                                <i class="la la-file-text-o"></i>
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
                        <div class="card-body">
                            <!--begin: Search Form-->
                            <!--begin::Search Form-->
                            <div class="mb-7">
                                <div class="row align-items-center">
                                    <div class="col-lg-9 col-xl-8">
                                        <div class="row align-items-center">
                                            <div class="col-md-4 my-2 my-md-0">
                                                <div class="input-icon">
                                                    <input type="text" class="form-control searchFeedback"
                                                        placeholder="Search..." id="kt_datatable_search_query" />
                                                    <span>
                                                        <i class="flaticon2-search-1 text-muted"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--end::Search Form-->
                            <!--end: Search Form-->
                            <!--begin: Datatable-->
                            <table class="table stylish-table no-wrap">
                                <thead>
                                    <tr>
                                        <th title="Field #1">Client Name</th>
                                        <th title="Field #2">Project Name</th>
                                        <th title="Field #3">Project ID</th>
                                        <th title="Field #4">Team Member</th>
                                        <th title="Field #5">Project Phase</th>
                                        <th title="Field #10">Timestamp</th>
                                        <th title="Field #10">Feedback Text</th>
                                    </tr>
                                </thead>
                                <tbody id="feedBackResult">
                                    @foreach ($feedbacks as $key => $single_feedback)
                                        <tr>
                                            <td>{{ $single_feedback->client_name }}</td>
                                            <td>{{ $single_feedback->project_name }}</td>
                                            <td>{{ $single_feedback->project_id }}</td>
                                            <td>{{ $single_feedback->legal_team_name }}</td>
                                            <td>{{ $single_feedback->project_phase }}</td>
                                            <td>{{ \Carbon\Carbon::parse($single_feedback->created_at)->timezone('America/Vancouver')->format('Y-m-d H:i:s') }}
                                            </td>
                                            <td>
                                                <i class="fa fa-eye text-success" id="{{ $key }}"
                                                    onclick="get_feedback_data(this.id)" data-toggle="modal"
                                                    data-target="#starRatingModal" style="cursor:pointer;"></i>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            {!! $feedbacks->appends($_GET)->links('admin.pages.pagination') !!}

                            <!-- Modal -->
                            <div class="modal fade" id="starRatingModal" tabindex="-1" role="dialog"
                                aria-labelledby="starRatingModalLabel" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal"
                                                aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <h5 class="modal-title" id="starRatingModalLabel">Feedback</h5>
                                                <label for="exampleFormControlTextarea1" id="fd_service_label"
                                                    class="text-label">How satisfied are you with the legal service has
                                                    provided?</label>
                                                <div class="starRating">
                                                    <input type="radio" name="fd_mark_legal_service" value="5"
                                                        id="5_fd_mark_legal_service">
                                                    <label for="fifth"></label>
                                                    <input type="radio" name="fd_mark_legal_service" value="4"
                                                        id="4_fd_mark_legal_service">
                                                    <label for="fourth"></label>
                                                    <input type="radio" name="fd_mark_legal_service" value="3"
                                                        id="3_fd_mark_legal_service">
                                                    <label for="thirth"></label>
                                                    <input type="radio" name="fd_mark_legal_service" value="2"
                                                        id="2_fd_mark_legal_service">
                                                    <label for="second"></label>
                                                    <input type="radio" name="fd_mark_legal_service" value="1"
                                                        id="1_fd_mark_legal_service">
                                                    <label for="first"></label>
                                                    <!-- Show Result  -->
                                                    <span class="result"></span>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="exampleFormControlTextarea1" class="text-label">How likely are
                                                    you to recommend our firm to others?</label>
                                                <div class="starRating">
                                                    <input type="radio" name="fd_mark_recommend" value="5"
                                                        id="5_fd_mark_recommend">
                                                    <label for="fifth"></label>
                                                    <input type="radio" name="fd_mark_recommend" value="4"
                                                        id="4_fd_mark_recommend">
                                                    <label for="fourth"></label>
                                                    <input type="radio" name="fd_mark_recommend" value="3"
                                                        id="3_fd_mark_recommend">
                                                    <label for="thirth"></label>
                                                    <input type="radio" name="fd_mark_recommend" value="2"
                                                        id="2_fd_mark_recommend">
                                                    <label for="second"></label>
                                                    <input type="radio" name="fd_mark_recommend" value="1"
                                                        id="1_fd_mark_recommend">
                                                    <label for="first"></label>
                                                    <!-- Show Result  -->
                                                    <span class="result"></span>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="exampleFormControlTextarea1" class="text-label">How useful
                                                    have you found this Client Portal to be?</label>
                                                <div class="starRating">
                                                    <input type="radio" name="fd_mark_useful" value="5"
                                                        id="5_fd_mark_useful">
                                                    <label for="fifth"></label>
                                                    <input type="radio" name="fd_mark_useful" value="4"
                                                        id="4_fd_mark_useful">
                                                    <label for="fourth"></label>
                                                    <input type="radio" name="fd_mark_useful" value="3"
                                                        id="3_fd_mark_useful">
                                                    <label for="thirth"></label>
                                                    <input type="radio" name="fd_mark_useful" value="2"
                                                        id="2_fd_mark_useful">
                                                    <label for="second"></label>
                                                    <input type="radio" name="fd_mark_useful" value="1"
                                                        id="1_fd_mark_useful">
                                                    <label for="first"></label>
                                                    <!-- Show Result  -->
                                                    <span class="result"></span>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="exampleFormControlTextarea1" class="text-label mt-4">Is there
                                                    anything we could be doing better?</label>
                                                <textarea required class="form-control" name="fd_content" id="feedback_content" rows="3"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--end: Datatable-->
                        </div>
                    </div>
                </div>
                <!--end::Card-->

                <!--begin::Card-->
                <div class="col-md-12">
                    <div class=" card card-custom mt-6">
                        <div class="card-header flex-wrap border-0 pt-6 pb-0">
                            <div class="card-title">
                                <h3 class="card-label">All Messages Log</h3>
                            </div>
                            <div class="card-toolbar">
                                <div class="dropdown dropdown-inline mr-2">
                                    <div class="row">
                                        <div class="col-9">
                                            <form class="log-form-msg">
                                                <input type="hidden" name="log_start" value="{{ $start_date_log }}" />
                                                <input type="hidden" name="log_end" value="{{ $end_date_log }}" />
                                                <input type="hidden" name="log_start_date"
                                                    value="{{ $start_date }}" />
                                                <input type="hidden" name="log_end_date" value="{{ $end_date }}" />
                                                <input type="hidden" name="msg_start_date"
                                                    value="{{ $start_date_msg }}" />
                                                <input type="hidden" name="msg_end_date" value="{{ $end_date_msg }}" />
                                                <input type="hidden" name="startDateLogTrouble"
                                                    value="{{ $startDateLogTrouble }}" />
                                                <input type="hidden" name="endDateLogTrouble"
                                                    value="{{ $endDateLogTrouble }}" />
                                                <input type="hidden" name="type_of_line" value="" />
                                                <div class="form-group row">
                                                    <div class="col-5">
                                                        <select name="type_of_line" id="type_of_line_field"
                                                            class="form-control">
                                                            <option value="">Type Of Line</option>
                                                            <option value="PhaseChange"
                                                                {{ $type_of_line == 'PhaseChange' ? 'selected' : '' }}>
                                                                Phase Change</option>
                                                            <option value="ReviewRequest"
                                                                {{ $type_of_line == 'ReviewRequest' ? 'selected' : '' }}>
                                                                Review Request</option>
                                                            <option value="MassMessage"
                                                                {{ $type_of_line == 'MassMessage' ? 'selected' : '' }}>Mass
                                                                Message</option>
                                                            <option value="2FAVerification"
                                                                {{ $type_of_line == '2FAVerification' ? 'selected' : '' }}>
                                                                2FA Verification</option>
                                                            <option value="AWSMS"
                                                                {{ $type_of_line == 'AWSMS' ? 'selected' : '' }}>
                                                                AW SMS</option>
                                                            <option value="AWEmail"
                                                                {{ $type_of_line == 'AWEmail' ? 'selected' : '' }}>
                                                                AW Email</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-7">
                                                        <div id="logreportrangemsg" class="custom-date-picker">
                                                            <i class="fa fa-calendar"></i>&nbsp;
                                                            <span></span> <i class="fa fa-caret-down"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="col-3">
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
                                                        <a href="{{ route('message_log_export_csv', ['subdomain' => $subdomain]) }}?{{ 'msg_start_date=' . $start_date_msg . '&msg_end_date=' . $end_date_msg . '&type_of_line=' . $type_of_line }}"
                                                            class="navi-link">
                                                            <span class="navi-icon">
                                                                <i class="la la-file-text-o"></i>
                                                            </span>
                                                            <span class="navi-text">CSV</span>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="m-t-40">
                                <table class="table stylish-table no-wrap">
                                    <thead>
                                        <tr>
                                            <th title="Field #1">Client Name</th>
                                            <th title="Field #2">Client Number/Email</th>
                                            <th title="Field #3">Message</th>
                                            <th>Type</th>
                                            <th>Type Of Line</th>
                                            <th title="Field #4">Created At</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (count($google_review_reply_messages) > 0)
                                            @foreach ($google_review_reply_messages as $key => $single_google_review_reply_message)
                                                <tr>
                                                    <td>{{ $single_google_review_reply_message->client_name }}</td>
                                                    @if ($single_google_review_reply_message->msg_type == 'email')
                                                        <td>{{ $single_google_review_reply_message->to_number }}</td>
                                                    @else
                                                        <td>{{ $single_google_review_reply_message->msg_type == 'out' || $single_google_review_reply_message->type_of_line == 'MassMessage' ? (!empty($single_google_review_reply_message->to_number) && substr($single_google_review_reply_message->to_number, 0, 1) != '+' ? '+1' . $single_google_review_reply_message->to_number : $single_google_review_reply_message->to_number) : (!empty($single_google_review_reply_message->from_number) && substr($single_google_review_reply_message->from_number, 0, 1) != '+' ? '+1' . $single_google_review_reply_message->from_number : $single_google_review_reply_message->from_number) }}
                                                        </td>
                                                    @endif
                                                    <td>{{ $single_google_review_reply_message->message }}</td>
                                                    <td>
                                                        @if (
                                                            $single_google_review_reply_message->msg_type == 'out' ||
                                                                $single_google_review_reply_message->msg_type == 'email' ||
                                                                !$single_google_review_reply_message->msg_type)
                                                            <i style="font-size:22px"
                                                                class="bi bi-telephone-outbound-fill text-primary"></i>
                                                        @else
                                                            <i style="font-size:22px"
                                                                class="bi bi-telephone-inbound-fill text-primary"></i>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($single_google_review_reply_message->type_of_line == 'MassMessage')
                                                            {{ !empty($single_google_review_reply_message->job_id) ? 'Mass Message' . ' (' . $single_google_review_reply_message->job_id . ')' : 'Mass Message' }}
                                                        @elseif($single_google_review_reply_message->type_of_line == 'ReviewRequest')
                                                            Review Request
                                                        @elseif($single_google_review_reply_message->type_of_line == 'PhaseChange')
                                                            Phase Change
                                                        @elseif($single_google_review_reply_message->type_of_line == '2FAVerification')
                                                            2FA Verification
                                                        @else
                                                            {{ ucfirst($single_google_review_reply_message->type_of_line) }}
                                                        @endif
                                                    </td>
                                                    <td>{{ (new \DateTime($single_google_review_reply_message->created_at))->format('Y-m-d H:i:s') }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                                {!! count($google_review_reply_messages) > 0
                                    ? $google_review_reply_messages->appends($_GET)->links('admin.pages.pagination')
                                    : '' !!}
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Card-->


                <!--begin::Card-->
                <div class="col-md-12">
                    <div class=" card card-custom mt-6">
                        <div class="card-header flex-wrap border-0 pt-6 pb-0">
                            <div class="card-title">
                                <h3 class="card-label">Notification Log</h3>
                            </div>
                            <div class="card-toolbar">
                                <div class="dropdown dropdown-inline mr-2">
                                    <div class="row">
                                        <div class="col-9">
                                            <form class="log-form-notification">
                                                <div class="form-group row">
                                                    <div class="col-5">
                                                        <select name="notification_event_name"
                                                            id="notification_event_name" class="form-control">
                                                            <option value="">Notification Event</option>
                                                            @foreach ($notification_event_names as $notification_event_name)
                                                                <option
                                                                    value="{{ $notification_event_name->event_name }}">
                                                                    {{ $notification_event_name->event_name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-7">
                                                        <div id="notificationlog" class="custom-date-picker">
                                                            <i class="fa fa-calendar"></i>&nbsp;
                                                            <span></span> <i class="fa fa-caret-down"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="col-3">
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
                                                        <a href="{{ route('export_notificationlog', ['subdomain' => $subdomain]) }}"
                                                            class="navi-link export-notification-log">
                                                            <span class="navi-icon">
                                                                <i class="la la-file-text-o"></i>
                                                            </span>
                                                            <span class="navi-text">CSV</span>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="m-t-40">
                                <table class="table stylish-table no-wrap" id="kt_datatable_notificationlog">
                                    <thead>
                                        <tr>
                                            <th>Event Name</th>
                                            <th>Project Id</th>
                                            <th>Project Name</th>
                                            <th>Client Id</th>
                                            <th>Client Name</th>
                                            <th>Notification Body</th>
                                            <th>Created At</th>
                                            <th>Email Notification At</th>
                                            <th>Post to FV At</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Card-->


            </div>
        </div>
        <!--end::Container-->

        <div class="modal fade" id="logNote" tabindex="-1" role="dialog" aria-labelledby="logNoteLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Response Details</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="w-100">
                            <p><span class="font-weight-bold">Message: </span><span id="log_note"></span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="matchToClient" tabindex="-1" role="dialog" aria-labelledby="matchToClientLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Match to Client</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="w-100">
                            <table class="table stylish-table no-wrap">
                                <thead>
                                    <tr>
                                        <th>Client ID</th>
                                        <th>Client Name</th>
                                        <th>Client Email</th>
                                        <th>Action</th>
                                        {{-- <th>Send Mail</th>
                                        <th>Block</th> --}}
                                    </tr>
                                </thead>
                                <tbody id="clients-block-tbody">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>
    <!--end::Entry-->

    <style>
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            z-index: 100;
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

@stop
@section('scripts')
    <script src="{{ asset('../js/admin/dashboard.js?20230719') }}"></script>
    <script type="text/javascript">
        $(".searchFeedback").on("keyup change", function(e) {
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            var searchvalue = $(this).val();
            $.ajax({
                url: "{{ url('admin/searchfeedBack') }}",
                type: 'POST',
                data: {
                    '_token': CSRF_TOKEN,
                    'searchvalue': searchvalue
                },
                success: function(data) {

                    $('#feedBackResult').html(data);
                }
            });

        })
        var last7days = {!! json_encode($week_date) !!};

        function get_feedback_data(data_id) {

            var feedbacks = <?php echo json_encode($feedbacks); ?>

            $('#' + feedbacks.data[data_id]['fd_mark_legal_service'] + '_fd_mark_legal_service').prop("checked", true);
            $('#' + feedbacks.data[data_id]['fd_mark_recommend'] + '_fd_mark_recommend').prop("checked", true);
            $('#' + feedbacks.data[data_id]['fd_mark_useful'] + '_fd_mark_useful').prop("checked", true);
            $('#feedback_content').val(feedbacks.data[data_id]['fd_content']);

            $('#starRatingModalLabel').text('Feedback for ' + feedbacks.data[data_id]['legal_team_name']);

            $('#fd_service_label').text('How satisfied are you with the legal service ' + feedbacks.data[data_id][
                'legal_team_name'
            ] + ' has provided?');
        }

        $(function() {
            var form = $('.log-form');
            var firstLoad = true;
            var start = moment(form.find('input[name="log_start_date"]').val(), "YYYY-MM-DD");
            var end = moment(form.find('input[name="log_end_date"]').val(), "YYYY-MM-DD");

            function cb(start, end) {
                $('#logreportrange span').html(start.format('MM/D/YYYY') + ' - ' + end.format('MM/D/YYYY'));

                if (!firstLoad) {
                    form.find('input[name="log_start_date"]').val(start.format('YYYY-MM-DD'));
                    form.find('input[name="log_end_date"]').val(end.format('YYYY-MM-DD'));
                    form.submit();
                }

                firstLoad = false;
            }

            $('#logreportrange').daterangepicker({
                startDate: start,
                endDate: end,
                ranges: {
                    'Today': [moment(), moment()],
                    'This Week': [moment().startOf('isoWeek'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                }
            }, cb);

            cb(start, end);
        });

        $(function() {
            var form = $('.log-form-log');
            var firstLoad = true;
            var start = moment(form.find('input[name="log_start"]').val(), "YYYY-MM-DD");
            var end = moment(form.find('input[name="log_end"]').val(), "YYYY-MM-DD");

            function cblog(start, end) {
                $('#logreportrangelog span').html(start.format('MM/D/YYYY') + ' - ' + end.format('MM/D/YYYY'));

                if (!firstLoad) {
                    form.find('input[name="log_start"]').val(start.format('YYYY-MM-DD'));
                    form.find('input[name="log_end"]').val(end.format('YYYY-MM-DD'));
                    form.submit();
                }

                firstLoad = false;
            }

            $('#logreportrangelog').daterangepicker({
                startDate: start,
                endDate: end,
                ranges: {
                    'Today': [moment(), moment()],
                    'This Week': [moment().startOf('isoWeek'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                }
            }, cblog);

            $('#login_status_field').change(function(e) {
                cblog(start, end);
            });

            $('#client_name').keypress(function(e) {
                var key = e.which;
                if (key == 13) // the enter key code
                {
                    cblog(start, end);
                }
            });

            cblog(start, end);
        });

        $(function() {
            var form = $('.log-form-msg');
            var firstLoad = true;
            var start = moment(form.find('input[name="msg_start_date"]').val(), "YYYY-MM-DD");
            var end = moment(form.find('input[name="msg_end_date"]').val(), "YYYY-MM-DD");

            function cbmsg(start, end) {
                $('#logreportrangemsg span').html(start.format('MM/D/YYYY') + ' - ' + end.format('MM/D/YYYY'));

                if (!firstLoad) {
                    form.find('input[name="msg_start_date"]').val(start.format('YYYY-MM-DD'));
                    form.find('input[name="msg_end_date"]').val(end.format('YYYY-MM-DD'));
                    form.submit();
                }

                firstLoad = false;
            }

            $('#logreportrangemsg').daterangepicker({
                startDate: start,
                endDate: end,
                ranges: {
                    'Today': [moment(), moment()],
                    'This Week': [moment().startOf('isoWeek'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                }
            }, cbmsg);
            $('#type_of_line_field').change(function(e) {
                cbmsg(start, end);
            });

            cbmsg(start, end);
        });

        $(function() {
            var form = $('.log-form-trouble');
            var firstLoad = true;
            var start = moment(form.find('input[name="startDateLogTrouble"]').val(), "YYYY-MM-DD");
            var end = moment(form.find('input[name="endDateLogTrouble"]').val(), "YYYY-MM-DD");

            function cbmsg(start, end) {
                $('#logreportrangetrouble span').html(start.format('MM/D/YYYY') + ' - ' + end.format('MM/D/YYYY'));

                if (!firstLoad) {
                    form.find('input[name="startDateLogTrouble"]').val(start.format('YYYY-MM-DD'));
                    form.find('input[name="endDateLogTrouble"]').val(end.format('YYYY-MM-DD'));
                    form.submit();
                }

                firstLoad = false;
            }

            $('#logreportrangetrouble').daterangepicker({
                startDate: start,
                endDate: end,
                ranges: {
                    'Today': [moment(), moment()],
                    'This Week': [moment().startOf('isoWeek'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                }
            }, cbmsg);

            cbmsg(start, end);
        });

        // var datatable = $('#kt_datatable1').KTDatatable();

        $(document).on('click', '.log-note-details', function() {
            let note_details = JSON.parse($(this).attr('data-value'));
            let message = "";
            try {
                message = JSON.parse(note_details).message;
            } catch (e) {
                message = note_details;
            }
            $("#log_note").text(message);
        });

        $(document).on('click', '.log-trouble-details', function() {
            $(".loading").show();
            let details = JSON.parse($(this).attr('data-value'));
            $("#clients-block-tbody").empty();
            $.ajax({
                url: "dashboard_search_client",
                data: {
                    'lookup_first_name': details.lookup_first_name,
                    'lookup_last_name': details.lookup_last_name
                },
                type: "GET",
                success: function(response) {
                    $(".loading").hide();
                    if (response.status) {
                        let clients = response.data;
                        let client_row = "";
                        clients.forEach(element => {
                            client_row += '<tr><td>' + element.client_id + '</td>';
                            client_row += '<td>' + element.full_name + '</td>';
                            client_row += '<td>' + element.primaryEmail + '</td>';
                            // client_row +=
                            //     '<td><button type="button" class="btn btn-success mail-client" onClick="sendClientInfo(' +
                            //     element.client_id + ',' + details.id +
                            //     ')">Send Mail</button></td>';
                            // client_row +=
                            //     '<td><button type="button" class="btn btn-success" onClick="blockClientInfo(' +
                            //     element.client_id + ',' + details.id +
                            //     ')">Block</button></td></tr>';
                            client_row +=
                                '<td><button type="button" class="btn btn-success" onClick="updateClientInfo(' +
                                element.client_id + ',' + details.id +
                                ')">Update Contact Info</button></td></tr>';
                        });
                        $("#clients-block-tbody").html(client_row);
                    }
                },
                error: function() {
                    alert("Error to Process Your Request! Please try Again!");
                },
            }).done(function() {
                $(".loading").hide();
            });
        });

        function sendClientInfo(client_id, log_id) {
            $('#matchToClient').modal('hide');
            $(".loading").show();
            $.ajax({
                url: "dashboard_send_client_info",
                data: {
                    'client_id': client_id,
                    'log_id': log_id
                },
                type: "GET",
                success: function(response) {
                    $(".loading").hide();
                    Swal.fire({
                        text: response.message,
                        icon: "success",
                    });
                },
                error: function() {
                    alert("Error to Process Your Request! Please try Again!");
                },
            });
        }

        function blockClientInfo(client_id, log_id) {
            $('#matchToClient').modal('hide');
            $(".loading").show();
            $.ajax({
                url: "dashboard_block_client",
                data: {
                    'client_id': client_id,
                    'log_id': log_id
                },
                type: "GET",
                success: function(response) {
                    $(".loading").hide();
                    Swal.fire({
                        text: response.message,
                        icon: "success",
                    });
                },
                error: function() {
                    alert("Error to Process Your Request! Please try Again!");
                },
            });
        }

        function updateClientInfo(client_id, log_id) {
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            $('#matchToClient').modal('hide');
            $(".loading").show();
            $.ajax({
                url: "dashboard_update_client",
                data: {
                    '_token': CSRF_TOKEN,
                    'client_id': client_id,
                    'log_id': log_id
                },
                type: "POST",
                success: function(response) {
                    $(".loading").hide();
                    Swal.fire({
                        text: response.message,
                        icon: "success",
                    });
                },
                error: function() {
                    alert("Error to Process Your Request! Please try Again!");
                },
            });
        }
    </script>

@endsection
