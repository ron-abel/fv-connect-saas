@extends('superadmin.layouts.default')

@section('title', 'Tenants')
@section('content')
    <div class="main-content container">
        <!--begin::Card-->
        <div class=" card card-custom mt-6">
            <div class="card-header flex-wrap border-0 pt-6 pb-0">
                <div class="card-title">
                    <h3 class="card-label">Tenant Mangement</h3>
                </div>
                <div class="card-toolbar">
                    <!--begin::Dropdown-->
                    <div class="dropdown dropdown-inline mr-2">
                        <button type="button" class="btn btn-light-primary font-weight-bolder dropdown-toggle"
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
                                <li class="navi-header font-weight-bolder text-uppercase font-size-sm text-primary pb-2">
                                    Choose an option:</li>
                                <li class="navi-item">
                                    <a href="{{ route('tenants_export_csv') }}" class="navi-link">
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
                    <!--begin::Button-->
                    <a href="{{ route('add_tenant') }}" class="btn btn-primary font-weight-bolder">
                        <i class="icon-xl la la-plus"></i>
                        Add Tenant
                    </a>
                    <!--end::Button-->
                </div>
            </div>
            <div class="card-body">
                @if (session()->has('success'))
                    <div class="alert alert-success" role="alert"> {{ session()->get('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @elseif(session()->has('error'))
                    <div class="alert alert-danger" role="alert"> {{ session()->get('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <div class="overlay loading"></div>
                <div class="spinner-border text-primary loading" role="status">
                    <span class="sr-only">Loading...</span>
                </div>

                <!--begin: Datatable-->
                <table class="table table-bordered table-hover" id="superadmin_tenant_datatable">
                    <thead>
                        <tr>
                            <th title="Field #1">Tenant ID</th>
                            <th title="Field #2">Tenant Name</th>
                            <th title="Field #3">Subdomain Link</th>
                            <th title="Field #4">Owner</th>
                            <th title="Field #5">Email</th>
                            <!-- <th title="Field #5">Billing Plan</th> -->
                            <!-- <th title="Field #6" id="datatable-cell-sort-1">Billing Start</th> -->
                            <th title="Field #8">Tenant Status</th>
                            <!-- <th title="Field #7">Active</th> -->
                            <!-- <th title="Field #9">Registered At</th> -->
                            <!-- <th title="Field #10">IP Verifications</th> -->
                            <th title="Field #10">Actions</th>

                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($all_tenants as $single_tenant)
                            <?php
                            $stripe_product = $single_tenant->customer && $single_tenant->customer->subscribed('default') && isset($single_tenant->customer->subscription('default')->items[0]) ? $single_tenant->customer->subscription('default')->items[0]->stripe_price : '';
                            $subscription_customer_id = $single_tenant->customer && $single_tenant->customer->subscribed('default') ? $single_tenant->customer->id : '';
                            ?>
                            <tr>
                                <td>{{ $single_tenant->id }}</td>
                                <td><a href="javascript:void(0)" data-id="{{ $single_tenant->id }}" class="edit-tenant-name"
                                        data-toggle="modal" data-target="#editTenantName"
                                        title="Edit Tenant Name">{{ $single_tenant->tenant_name }}</a>
                                </td>
                                <td><a
                                        href="{{ $http }}{{ $single_tenant->tenant_name }}.{{ $domainName }}">{{ $http }}{{ $single_tenant->tenant_name }}.{{ $domainName }}</a>
                                </td>
                                <td>{{ $single_tenant->owner && $single_tenant->owner->full_name ? $single_tenant->owner->full_name : '' }}
                                </td>
                                <td>{{ $single_tenant->owner && $single_tenant->owner->email ? $single_tenant->owner->email : '' }}
                                </td>
                                {{-- <td>{{ $single_tenant->customer && $single_tenant->customer->subscribed('default') ? (isset($single_tenant->customer->subscription('default')->items[0], $all_plans[$single_tenant->customer->subscription('default')->items[0]->stripe_product]) ? $all_plans[$single_tenant->customer->subscription('default')->items[0]->stripe_product] : '') : $single_tenant->plan_name }}
                                </td>
                                <td>{{ $single_tenant->customer && $single_tenant->customer->subscribed('default') ? (new \DateTime($single_tenant->customer->subscription('default')->created_at))->format('m/d/Y') : $single_tenant->plan_start_date }}
                                </td> --}}
                                <td>
                                    <p id="reverification_sent_{{ $single_tenant->id }}"></p>
                                    @if ($single_tenant->status == 'Unverified')
                                        {{ $single_tenant->status }}
                                        <a href="javascript:void(0)" class="btn btn-primary btn-sm reverify_tenant"
                                            data-tenant='{{ $single_tenant->id }}'>
                                            Reverify
                                        </a>
                                    @else
                                        {{ $single_tenant->status }}
                                    @endif
                                </td>
                                {{-- <td>
                                    @if ($single_tenant->is_active)
                                        <i class="fa fa-check changeStatus" style="color:#1bc5bd;cursor:pointer;"
                                            data-tenant-id="{{ $single_tenant->id }}" data-status="0"></i>
                                    @else
                                        <i class="fa fa-check changeStatus" style="cursor:pointer;"
                                            data-tenant-id="{{ $single_tenant->id }}" data-status="1"></i>
                                    @endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($single_tenant->created_at)->format('m/d/Y') }}</td>
                                <td>
                                    <div><label class="ip-verification-switch">
                                        <input type="checkbox"
                                            onclick="toggleIPVerification(this, {{ $single_tenant->id }}, {{ $single_tenant->ip_verification_enable }})"
                                            {{ $single_tenant->ip_verification_enable == 1 ? 'checked' : '' }}>
                                        <span class="slider round"></span>
                                    </label></div>
                                </td> --}}
                                <td>
                                    @if (empty($stripe_product) || (!empty($stripe_product) && $stripe_product == $max_price_plan))
                                        <a href="javascript:;" data-toggle="modal" data-target="#noPlanModal"
                                            class="btn btn-sm btn-clean btn-icon mr-2" style=" cursor: default;"
                                            title="Upgrade Subscription Plan">
                                            <i class="icon-xl la la-money-check"></i>
                                        </a>
                                    @else
                                        <a href="javascript:;" class="btn btn-sm btn-clean btn-icon mr-2 changeTenantPlan"
                                            id="changeTenantPlan-{{ $single_tenant->id }}" title="Change Subscription Plan"
                                            data-tenant-name="{{ $single_tenant->tenant_name }}"
                                            data-plan="{{ $stripe_product }}" data-user-id="{{ $single_tenant->id }}"
                                            data-subdomain="{{ $single_tenant->tenant_name }}"
                                            data-registered-at="{{ \Carbon\Carbon::parse($single_tenant->created_at)->format('m/d/Y') }}"
                                            data-active="{{ $single_tenant->is_active }}"
                                            data-billing-cancel="{{ $single_tenant->plan_cancel_date }}"
                                            data-billing-plan-name="{{ $single_tenant->customer && $single_tenant->customer->subscribed('default') ? (isset($single_tenant->customer->subscription('default')->items[0], $all_plans[$single_tenant->customer->subscription('default')->items[0]->stripe_product]) ? $all_plans[$single_tenant->customer->subscription('default')->items[0]->stripe_product] : '') : $single_tenant->plan_name }}"
                                            data-billing-plan-start="{{ $single_tenant->customer && $single_tenant->customer->subscribed('default') ? (new \DateTime($single_tenant->customer->subscription('default')->created_at))->format('m/d/Y') : $single_tenant->plan_start_date }}"
                                            data-ip-verification="{{ $single_tenant->ip_verification_enable }}">
                                            <i class="icon-xl la la-money-check"></i>
                                        </a>
                                    @endif

                                    <a href="javascript:;" class="btn btn-sm btn-clean btn-icon mr-2 tenantDetails"
                                        id="tenantDetails-{{ $single_tenant->id }}" title="Tenant Details"
                                        data-user-id="{{ $single_tenant->id }}"
                                        data-subdomain="{{ $single_tenant->tenant_name }}"
                                        data-fv-project-count="{{ $single_tenant->fv_project_count }}"
                                        data-registered-at="{{ \Carbon\Carbon::parse($single_tenant->created_at)->format('m/d/Y') }}"
                                        data-active="{{ $single_tenant->is_active }}"
                                        data-billing-cancel="{{ $single_tenant->plan_cancel_date }}"
                                        data-billing-plan-name="{{ $single_tenant->customer && $single_tenant->customer->subscribed('default') ? (isset($single_tenant->customer->subscription('default')->items[0], $all_plans[$single_tenant->customer->subscription('default')->items[0]->stripe_product]) ? $all_plans[$single_tenant->customer->subscription('default')->items[0]->stripe_product] : '') : $single_tenant->plan_name }}"
                                        data-billing-plan-start="{{ $single_tenant->customer && $single_tenant->customer->subscribed('default') ? (new \DateTime($single_tenant->customer->subscription('default')->created_at))->format('m/d/Y') : $single_tenant->plan_start_date }}"
                                        data-ip-verification="{{ $single_tenant->ip_verification_enable }}"
                                        data-tenant-name="{{ $single_tenant->tenant_name }}"
                                        data-billing-plan-price="{{ $single_tenant->plan_price }}"
                                        data-tenant-link="{{ $http }}{{ $single_tenant->tenant_name }}.{{ $domainName }}"
                                        data-tenant-owner="{{ $single_tenant->owner && $single_tenant->owner->full_name ? $single_tenant->owner->full_name : '' }}"
                                        data-tenant-email="{{ $single_tenant->owner && $single_tenant->owner->email ? $single_tenant->owner->email : '' }}">
                                        <i class="icon-xl la la-eye"></i>
                                    </a>

                                    @if (!$single_tenant->is_active)
                                        <a href="javascript:;" class="btn btn-sm btn-clean btn-icon" id="delete_tenant"
                                            data-url="{{ route('delete_tenant', ['tenant_id' => $single_tenant->id]) }}"
                                            title="Delete">
                                            <i class="icon-xl la la-trash-o"></i>
                                        </a>
                                    @endif

                                    <!-- <a href="{{ route('edit_tenant', ['tenant_id' => $single_tenant->id]) }}" class="btn btn-sm btn-clean btn-icon mr-2" title="Edit details">
                                                                                                                    <i class="icon-xl la la-pen"></i>
                                                                                                                </a>
                                                                                                                <a class="btn btn-sm btn-clean btn-icon" id="view_tenant" href="{{ route('view_tenant', ['tenant_id' => $single_tenant->id]) }}" title="View">
                                                                                                                    <i class="icon-xl la la-eye"></i>
                                                                                                                </a> -->
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

    <!-- Change status modal. -->
    <div class="modal fade" id="changeStatusModal" tabindex="-1" role="dialog" aria-labelledby="changeStatusModal"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changeStatusModalLabel">Change Status</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h4>Are you sure to change the Active Status of the selected Tenant?</h4>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="confirmChangeStatus" data-tenant-id=""
                        data-status="">Confirm</a>
                </div>
            </div>
        </div>
    </div>

    <!-- No subscription plan modal. -->
    <div class="modal fade" id="noPlanModal" tabindex="-1" role="dialog" aria-labelledby="noPlanModal"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <p>There is not any Subscription Plan to upgrade!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editTenantName" tabindex="-1" role="dialog" aria-labelledby="editTenantNameLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form action="{{ route('update_tenant_name') }}" name="tenant_name_update_form" method="post">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addMappingRuleLabel">Update Tenant Name</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <i aria-hidden="true" class="ki ki-close"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="edit_tenant_id">
                        <div class="form-group">
                            <label> Tenant Name</label>
                            <input type="text" class="form-control form-control-solid" name="edit_tenant_name"
                                required />
                        </div>
                        <div class="alert alert-warning" role="alert"> If you change tenant name, all Filevine
                            subscription will be changed automatically to new tenant name! </div>
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


    <div class="modal fade" id="tenantDetails" tabindex="-1" role="dialog" aria-labelledby="tenantDetails"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" style="min-width:1000px;" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tenant Details of <span class="font-weight-bold"
                            id="modal-tenant-name"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row col-sm-12 justify-content-center text-left my-0 p-0 m-0">
                        <div class="col-md-3 col-sm-6 p-0 m-0">
                            <h6>Owner</h6>
                            <p id="modal-tenant-owner"></p>
                        </div>
                        <div class="col-md-3 col-sm-6 p-0 m-0">
                            <h6>Email</h6>
                            <p id="modal-tenant-email"></p>
                        </div>
                        <div class="col-md-3 col-sm-6 p-0 m-0">
                            <h6>Registered At</h6>
                            <p id="modal-registered-at"></p>
                        </div>
                        <div class="col-md-3 col-sm-6 p-0 m-0">
                            <h6>Link</h6>
                            <p><a href="" target="_blank" id="modal-tenant-link"></a></p>
                        </div>
                    </div>
                    <div class="row col-sm-12 justify-content-center text-left my-0 p-0 m-0 mt-4">
                        <div class="col-md-3 col-sm-6 p-0 m-0">
                            <h6>Billing Plan</h6>
                            <p id="modal-plan-name"></p>
                        </div>
                        <div class="col-md-3 col-sm-6 p-0 m-0">
                            <h6>Billing Start</h6>
                            <p id="modal-plan-start"></p>
                        </div>
                        <div class="col-md-3 col-sm-6 p-0 m-0">
                            <h6>Active</h6>
                            <p id="modal-active"></p>
                        </div>
                        <div class="col-md-3 col-sm-6 p-0 m-0">
                            <h6>IP Verifications</h6>
                            <p id="modal-ip-verification"></p>
                        </div>
                    </div>
                    <div class="row col-sm-12 justify-content-center text-left my-0 p-0 m-0 mt-4">
                        <div class="col-md-3 col-sm-6 p-0 m-0">
                            <h6>Billing Amount</h6>
                            <p id="modal-plan-price"></p>
                        </div>
                        <div class="col-md-3 col-sm-6 p-0 m-0">
                            <h6>Initial Project Count</h6>
                            <p id="modal-fv-project-count"></p>
                        </div>
                        <div class="col-md-3 col-sm-6 p-0 m-0">
                            <div class="d-none" id="modal-cancel-date-container">
                                <h6>Cancel Date</h6>
                                <p id="modal-cancel-date"></p>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 p-0 m-0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    @include('superadmin.includes.plan-change-modal')

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
    <script>
        $('#superadmin_tenant_datatable').DataTable({
            responsive: true,
        });

        function toggleIPVerification(e, tenant_id, val) {
            let dom = new DOMParser();
            $.ajax({
                url: "{{ url('tenant/toggle_ipverificationenable') }}",
                type: 'POST',
                data: {
                    '_token': $('meta[name="csrf-token"]').attr('content'),
                    'tenant_id': tenant_id,
                    'value': val,
                },
                dataType: 'JSON',
                success: function(data) {
                    syn = dom.parseFromString(`<input type="checkbox"
                                            onclick="toggleIPVerification(this, ${tenant_id}, ${data.value} )"
                                             ${data.value == 1 ? 'checked' : ''}>`,
                        'text/html');
                    e.replaceWith(syn.body.querySelector('input'));
                    $("#changeTenantPlan-" + tenant_id).attr('data-ip-verification', data.value);
                },
                error: function() {

                }
            });
        }
        $(document).on("click", ".reverify_tenant", function(e) {
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            var tenant = $(this).data('tenant');
            $.ajax({
                url: "{{ url('admin/tenant/reverify') }}" + "/" + tenant,
                type: 'POST',
                data: {
                    '_token': CSRF_TOKEN,
                },
                dataType: 'JSON',
                success: function(data) {
                    if (data.success) {
                        $("#reverification_sent_" + tenant).text(data.message)
                    }
                },
                error: function() {

                }
            });
        })
        $(document).ready(function() {
            $(document).on('click', '.changeStatus', function() {
                $('#updatePlanModal').modal('hide');
                var tenant_id = $(this).attr('data-tenant-id');
                var status = $(this).attr('data-status');
                $('#changeStatusModal').modal('show');
                $('#confirmChangeStatus').attr('data-tenant-id', tenant_id);
                $('#confirmChangeStatus').attr('data-status', status);
            });

            $(document).on('click', '#confirmChangeStatus', function() {
                var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                var tenant_id = $(this).attr('data-tenant-id');
                var status = $(this).attr('data-status');
                $('#changeStatusModal').modal('hide');
                // send ajax request
                $.ajax({
                    url: "{{ url('admin/tenant/edit-status') }}" + "/" + tenant_id,
                    type: 'POST',
                    data: {
                        '_token': CSRF_TOKEN,
                        'status': status
                    },
                    dataType: 'JSON',
                    success: function(data) {
                        if (data.success) {
                            if (status == 0) {
                                $('i.changeStatus[data-tenant-id=' + tenant_id + ']').attr(
                                    'data-status', '1');
                                $('i.changeStatus[data-tenant-id=' + tenant_id + ']').css(
                                    'color', '#B5B5C3');
                            } else {
                                $('i.changeStatus[data-tenant-id=' + tenant_id + ']').attr(
                                    'data-status', '0');
                                $('i.changeStatus[data-tenant-id=' + tenant_id + ']').css(
                                    'color', '#1bc5bd');
                            }
                            setTimeout(function() {
                                location.reload()
                            }, 1000)
                        }
                    },
                    error: function() {

                    }
                });
            });

            $(document).on('click', '.tenantDetails', function() {
                var user_id = $(this).attr('data-user-id');
                var subdomain = $(this).attr('data-subdomain');
                var registered_at = $(this).attr('data-registered-at');
                var active_status = $(this).attr('data-active');
                var billing_plan_name = $(this).attr('data-billing-plan-name');
                var billing_plan_start = $(this).attr('data-billing-plan-start');
                var ip_verification = $(this).attr('data-ip-verification');
                var billing_plan_price = $(this).attr('data-billing-plan-price');
                var cancel_status = $(this).attr('data-billing-cancel');

                $('input[name="change_plan"]').prop('checked', false);
                $('#tenantDetails').modal('show');
                $('#modal-registered-at').text(registered_at);
                $('#modal-plan-name').text(billing_plan_name);
                $('#modal-plan-start').text(billing_plan_start);
                $('#modal-tenant-name').text($(this).attr('data-tenant-name'));
                document.getElementById("modal-tenant-link").href = $(this).attr('data-tenant-link');
                $('#modal-tenant-link').text($(this).attr('data-tenant-link'));
                $('#modal-tenant-owner').text($(this).attr('data-tenant-owner'));
                $('#modal-tenant-email').text($(this).attr('data-tenant-email'));
                $('#modal-fv-project-count').text($(this).attr('data-fv-project-count'));
                $('#modal-plan-price').text(billing_plan_price);
                if(cancel_status != "") {
                    $('#modal-cancel-date-container').removeClass('d-none');
                    $('#modal-cancel-date').text(cancel_status);
                }
                else {
                    $('#modal-cancel-date-container').addClass('d-none');
                    $('#modal-cancel-date').text("");
                }

                // add status html to modal
                if (active_status == 1) {
                    $('#modal-active').html(
                        '<i class="fa fa-check changeStatus" style="color:#1bc5bd;cursor:pointer;" data-tenant-id="' +
                        user_id + '" data-status="0"></i>');
                } else {
                    $('#modal-active').html(
                        '<i class="fa fa-check changeStatus" style="cursor:pointer;" data-tenant-id="' +
                        user_id + '" data-status="1"></i>');
                }
                // add ip verifications html
                var ip_html = '<div><label class="ip-verification-switch">';
                ip_html += '<input type="checkbox" onclick="toggleIPVerification(this, ' + user_id + ', ' +
                    ip_verification + ')" ' + (ip_verification == 1 ? 'checked' : '') + '>';
                ip_html += '<span class="slider round"></span>';
                ip_html += '</label></div>';
                $('#modal-ip-verification').html(ip_html);
            });

            $(document).on('click', '.changeTenantPlan', function() {
                var user_id = $(this).attr('data-user-id');
                var plan_id = $(this).attr('data-plan');
                var subdomain = $(this).attr('data-subdomain');
                var billing_plan_name = $(this).attr('data-billing-plan-name');
                var billing_plan_start = $(this).attr('data-billing-plan-start');
                var tenant_name = $(this).attr('data-tenant-name');

                $('input[name="change_plan"]').prop('checked', false);
                $('#updatePlanModal').modal('show');
                $('input[name="plan_tenant_id"]').val(user_id);
                $('.extra-columns').removeClass('extra-columns-hide');
                $('#plan-tenant-name').text(tenant_name);

                $(".plan-list").html("");
                $(".loading").show();
                $.ajax({
                    url: "{{ url('admin/tenant/get_billing_plan') }}",
                    type: 'POST',
                    data: {
                        '_token': $('meta[name="csrf-token"]').attr('content'),
                        'tenant_id': user_id,
                        'tenant_name': subdomain,
                    },
                    dataType: 'JSON',
                    success: function(data) {
                        $(".loading").hide();
                        $(".plan-list").html(data.response_html);
                        $('#changeplan-item-' + plan_id).prop('checked', true);
                    },
                    error: function() {
                        $(".loading").hide();
                        $(".plan-list").html("");
                    }
                }).done(function() {
                    $(".loading").hide();
                });

            });

            $(document).on('click', '#upgradePlan', function() {
                var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

                var tenant_id = $('input[name="plan_tenant_id"]').val();
                var plan_id = $('input[name="change_plan"]:checked').val();
                $('#upgradePlan').attr('disabled', 'disabled');
                // send ajax request
                $.ajax({
                    url: "{{ url('admin/tenant/upgrade-plan') }}" + "/" + tenant_id,
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
                            }, 10000);
                        } else {
                            $('.upgrade-plan-error').addClass('alert-danger');
                            $('.upgrade-plan-error').text(data.message);
                            setTimeout(() => {
                                $('.upgrade-plan-error').removeClass('alert-danger');
                                $('.upgrade-plan-error').text('');
                            }, 10000);
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
