@extends('superadmin.layouts.default')

@section('title', 'API Logs')

@section('content')

<div class="main-content container">
    <!--begin::Card-->
    <div class="card card-custom mt-6">
        <div class="card-header flex-wrap border-0 pt-6 pb-0">
            <div class="card-title">
                <h3 class="card-label">API Logs</h3>
            </div>
            <div class="card-toolbar">
                <!--begin::Dropdown-->
                <div class="dropdown dropdown-inline mr-2">
                    <div class="row">
                        <div class="col-8">
                            <form class="log-form">
                                <div class="form-row">
                                    <div class="form-group col-6">
                                        <select class="form-control form-control-solid" name="tenant_id">
                                            <option value="">Select Tenant</option>
                                            @foreach($tenants_data as $single_tenant_data)
                                                <option value="{{ $single_tenant_data->id }}" {{ $tenant_id == $single_tenant_data->id ? 'selected' : '' }}>{{ $single_tenant_data->tenant_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-6">
                                            <input type="hidden" name="log_start_date" value="{{ $start_date }}" />
                                            <input type="hidden" name="log_end_date" value="{{ $end_date }}" />
                                            <div id="logreportrange" class="custom-date-picker">
                                                <i class="fa fa-calendar"></i>&nbsp;
                                                <span></span> <i class="fa fa-caret-down"></i>
                                            </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="col-4 text-right">
                            <button type="button" class="btn btn-light-primary font-weight-bolder dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="svg-icon svg-icon-md">
                                <!--begin::Svg Icon | path:assets/media/svg/icons/Design/PenAndRuller.svg-->
                                <i class="icon-xl la la-print"></i>
                                <!--end::Svg Icon-->
                            </span>Export</button>
                        <!--begin::Dropdown Menu-->
                            <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                                <!--begin::Navigation-->
                                <ul class="navi flex-column navi-hover py-2">
                                    <li class="navi-header font-weight-bolder text-uppercase font-size-sm text-primary pb-2">Choose an option:</li>
                                    <li class="navi-item">
                                        <a href="{{ route('api_log_export_csv') }}?{{ 'log_start_date='. $start_date . '&log_end_date='. $end_date . '&tenant_id='. $tenant_id }}" class="navi-link">
                                            <span class="navi-icon">
                                                <i class="la la-file-text-o"></i>
                                            </span>
                                            <span class="navi-text">CSV</span>
                                        </a>
                                    </li>
                                </ul>
                                <!--end::Navigation-->
                            </div>
                        </div>
                    </div>
                    <!--end::Dropdown Menu-->
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
                                    <input type="text" class="form-control" placeholder="Search..." id="kt_datatable_search_query" />
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
            <table class="datatable datatable-bordered datatable-head-custom" id="kt_datatable">
                <thead>
                    <tr>
                        <th title="Field #1">Tenant</th>
                        <th title="Field #2">Domain</th>
                        <th title="Field #3">2FA Number</th>
                        <th title="Field #4">FV ProjectID</th>
                        <th title="Field #5">Code</th>
                        <th title="Field #6">API</th>
                        <th title="Field #7">Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($all_logs as $log)
                    <tr>
                        <td>{{ $log->tenant_name}}</td>
                        <td>{{ $log->request_domain }}</td>
                        <td>{{ $log->to_number }}</td>
                        <td>{{ $log->fv_project_id }}</td>
                        <td>{{ $log->verification_code }}</td>
                        <td>{{ $log->api_name }}</td>
                        <td>{{ \Carbon\Carbon::parse($log->created_at)->format('Y-m-d H:i:s') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <!--end: Datatable-->
        </div>
    </div>
    <!--end::Card-->
</div>

@stop

@section('scripts')
<script type="text/javascript">

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

$(document).on('change', 'select[name="tenant_id"]', function () {
    $('.log-form').submit();
});

</script>
@endsection
