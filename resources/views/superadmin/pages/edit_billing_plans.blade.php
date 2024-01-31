@extends('superadmin.layouts.default')

@section('title', 'Edit Billing Plan')

@section('styles')
<style>
    @media (min-width: 992px) {
        .container {
            max-width: calc(100% - 242px);
            margin-left: 242px;
        }
    }
</style>
@endsection
@section('content')

<div class="main-content container">
    <div class="row mt-6">
        <div class="col-lg-12">
            <!--begin::Card-->
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Edit Billing Plan</h3>
                </div>
                <!--begin::Form-->
                <form class="form" id="add_tenant_form" method="post" action="{{ route('edit_billing_plan_post', ['billing_plan_id' => $billing_plan_details->id]) }}">
                    @csrf
                    <div class="card-body">
                        @if ( session()->has('error') )
                        <div class="alert alert-danger" role="alert">
                            {{ session()->get('error') }}
                        </div>
                        @elseif( session()->has('success') )
                        <div class="alert alert-primary" role="alert">
                            {{ session()->get('success') }}
                        </div>
                        @endif
                        <div class="form-group">
                            <label>Plan Name:</label>
                            <input type="text" class="form-control form-control-solid" name="plan_name" placeholder="Enter Plan Name" value="{{ $billing_plan_details->plan_name }}" {{ $billing_plan_details->plan_is_default == 1 ? "disabled" : "" }} />
                            @error('plan_name')
                            <span class="form-text text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label>Plan Price ($):</label>
                            <input type="text" class="form-control form-control-solid" name="plan_price" placeholder="Enter Plan Price" value="{{ $billing_plan_details->plan_price }}" {{ $billing_plan_details->plan_is_default == 1 ? "disabled" : "" }}/>
                            @error('plan_price')
                            <span class="form-text text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label>Plan Interval:</label>
                            <select  class="form-control form-control-solid" name="plan_interval" readonly>
                                <option {{ $billing_plan_details->plan_interval == 'day' ? "selected" : "disabled" }} value="day">Day</option>
                                <option {{ $billing_plan_details->plan_interval == 'week' ? "selected" : "disabled" }} value="week">Week</option>
                                <option {{ $billing_plan_details->plan_interval == 'month' ? "selected" : "disabled" }} value="month">Month</option>
                                <option {{ $billing_plan_details->plan_interval == 'year' ? "selected" : "disabled" }} value="year">Year</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Plan Description:</label>
                            <textarea class="form-control form-control-solid" name="plan_description" placeholder="Enter Plan Description" {{ $billing_plan_details->plan_is_default == 1 ? "disabled" : "" }} />{{ $billing_plan_details->plan_description }}</textarea>
                            @error('plan_description')
                            <span class="form-text text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label>Plan Trial Days:</label>
                            <input type="number" class="form-control form-control-solid" name="plan_trial_days" placeholder="Enter Plan Trial Days" value="{{ $billing_plan_details->plan_trial_days }}" {{ $billing_plan_details->plan_is_default == 1 ? "disabled" : "" }}/>
                            @error('plan_trial_days')
                            <span class="form-text text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label>Plan Type:</label>
                            <select class="form-control form-control-solid" name="plan_type" {{ $billing_plan_details->plan_is_default == 1 ? "disabled" : "" }}>
                                <option {{ $billing_plan_details->plan_is_default == 0 ? "selected" : "" }} value="custom">Custom</option>
                                <option {{ $billing_plan_details->plan_is_default == 1 ? "selected" : "" }} value="default">Default</option>
                            </select>
                        </div>
                        <div class="form-group tenant-div {{ $billing_plan_details->plan_is_default ? 'd-none' : '' }}">
                            <label>Select Tenant:</label>
                            <select class="form-control form-control-solid" name="tenant_id">
                                <option value="">Select Tenant</option>
                                @foreach($tenants_data as $single_tenant_data)
                                    <option {{ $billing_plan_details->plan_tenant_id == $single_tenant_data->id ? "selected" : "" }} value="{{ $single_tenant_data->id }}">{{ $single_tenant_data->tenant_name }}</option>
                                @endforeach
                            </select>
                            @error('tenant_id')
                            <span class="form-text text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label>Plan Active:</label>
                            <span class="switch switch-success">
                                <label>
                                    <input type="checkbox" name="is_active" {{ $billing_plan_details->plan_is_active == 1 ? "checked" : "" }}>
                                    <span></span>
                                </label>
                            </span>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary mr-2">Submit</button>
                    </div>
                </form>
                <!--end::Form-->
            </div>
            <!--end::Card-->
        </div>
    </div>
</div>

@stop
