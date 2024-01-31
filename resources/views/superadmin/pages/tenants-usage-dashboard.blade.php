<?php
// die('I AM HERE');
?>
@extends('superadmin.layouts.default')

@section('title', 'Tenant Usage Dashboard')

@section('content')


<div class="main-content container">
    <!--begin::Card-->
    <div class="card card-custom mt-6">
        <div class="card-header flex-wrap border-0 pt-6 pb-0">
            <div class="card-title">
                <h3 class="card-label">Tenant Usage</h3>
            </div>
            <div class="card-toolbar">
                <!--begin::Dropdown-->
                <div class="dropdown dropdown-inline mr-2">
                    <button type="button" class="btn btn-light-primary font-weight-bolder dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="icon-xl la la-print"></i>
                        Export
                    </button>
                    <!--begin::Dropdown Menu-->
                    <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                        <!--begin::Navigation-->
                        <ul class="navi flex-column navi-hover py-2">
                            <li class="navi-header font-weight-bolder text-uppercase font-size-sm text-primary pb-2">Choose an option:</li>
                            <li class="navi-item">
                                <a href="{{ route('tenants_usage_export_csv') }}" class="navi-link">
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
                        <th title="Field #1">ID</th>
                        <th title="Field #2">Tenant Name</th>
                        <th title="Field #3">Price Level</th>
                        <th title="Field #4">Renewel Day</th>
                        <th title="Field #5">API Usage for Period</th>
                        <th title="Field #6">Overall Average API Usage Per Day</th>
                        <th title="Field #8">Twilio Aggregated Cost</th>
                        <th title="Field #10">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($all_tenants as $single_tenant)
                    <?php
                    $plan_price = "";
                    $stripe_product = ($single_tenant->customer && $single_tenant->customer->subscribed('default') && isset($single_tenant->customer->subscription('default')->items[0])) ? $single_tenant->customer->subscription('default')->items[0]->stripe_price : '';
                    // get price of strip product
                    if (!empty($stripe_product) && isset($all_plans[$stripe_product])) {
                        $plan_price = $all_plans[$stripe_product];
                    }
                    ?>
                    <tr>
                        <td>{{ $single_tenant->id }}</td>
                        <td>{{ $single_tenant->tenant_name }}</td>
                        <td>{!! $plan_price !!}</td>
                        <td>{{ isset($single_tenant->customer) ? \Carbon\Carbon::createFromTimeStamp($single_tenant->customer->subscription('default')->asStripeSubscription()->current_period_end)->format('m-d-Y') : '' }}</td>
                        <td>{{ isset($single_tenant->usage_stats) ? $single_tenant->usage_stats['api_usage'] : "" }}</td>
                        <td>{{ isset($single_tenant->usage_stats) ? $single_tenant->usage_stats['api_usage_per_day'] : "" }}</td>
                        <td>{{ isset($single_tenant->usage_stats) ? $single_tenant->usage_stats['twilio_aggregated_cost'] : "" }}</td>
                        <td>
                            <span style="overflow: visible; position: relative; width: 125px;">
                                @if(empty($stripe_product) || (!empty($stripe_product) && $stripe_product == $max_price_plan))
                                <a href="javascript:;" class="btn btn-sm btn-clean btn-icon mr-2" style=" cursor: default;" title="Upgrade Subscription Plan">
                                    <i class="icon-xl la la-money-check"></i>
                                </a>
                                @else
                                <a href="javascript:;" class="btn btn-sm btn-clean btn-icon mr-2 changeTenantPlan" title="Upgrade Subscription Plan" data-plan="{{$stripe_product}}" data-user-id="{{$single_tenant->id}}">
                                    <i class="icon-xl la la-money-check"></i>
                                </a>
                                @endif
                            </span>
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

@include('superadmin.includes.plan-change-modal')
@stop
@section('scripts')
<script>
    $(document).ready(function() {
        $(document).on('click', '.changeTenantPlan', function() {
            var user_id = $(this).attr('data-user-id');
            var plan_id = $(this).attr('data-plan');
            var subdomain = $(this).attr('data-subdomain');
            $('input[name="change_plan"]').prop('checked', false);
            $('#updatePlanModal').modal('show');
            $('#changeplan-item-' + plan_id).prop('checked', true);
            $('input[name="plan_tenant_id"]').val(user_id);
        });

        $(document).on('click', '#upgradePlan', function() {
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            var tenant_id = $('input[name="plan_tenant_id"]').val();
            var plan_id = $('input[name="change_plan"]:checked').val();
            $('#upgradePlan').attr('disabled', 'disabled');
            // send ajax request
            $.ajax({
                url: "{{url('admin/tenant/upgrade-plan')}}" + "/" + tenant_id,
                type: 'POST',
                data: {
                    '_token': CSRF_TOKEN,
                    'plan': plan_id
                },
                dataType: 'JSON',
                success: function(data) {
                    if (data.success) {
                        $('.upgrade-plan-error').addClass('alert-success');
                        $('.upgrade-plan-error').text(data.message);
                        setTimeout(() => {
                            $('.upgrade-plan-error').removeClass('alert-success');
                            $('.upgrade-plan-error').text('');
                            $('#updatePlanModal').modal('hide');
                        }, 2000);
                    } else {
                        $('.upgrade-plan-error').addClass('alert-danger');
                        $('.upgrade-plan-error').text(data.message);
                        setTimeout(() => {
                            $('.upgrade-plan-error').removeClass('alert-danger');
                            $('.upgrade-plan-error').text('');
                        }, 2000);
                    }
                    $('#upgradePlan').removeAttr('disabled');
                },
                error: function() {
                    $('#upgradePlan').removeAttr('disabled');
                }
            });
        });

    });
</script>
@endsection