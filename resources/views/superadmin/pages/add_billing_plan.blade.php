@extends('superadmin.layouts.default')

@section('title', 'Add Billing Plan')

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
                    <h3 class="card-title">Add Billing Plan</h3>
                </div>
                <!--begin::Form-->
                <form class="form" id="add_tenant_form" method="post" action="{{ route('add_billing_plan_post') }}">
                    @csrf

                    @if ( session()->has('error') )
                    <div>{{ session()->get('error') }}</div>
                    @endif

                    <div class="card-body">
                        <div class="form-group">
                            <label>Plan Name:</label>
                            <input type="text" class="form-control form-control-solid" name="plan_name" placeholder="Enter Plan Name" value="{{ old('plan_name') }}" />
                            @error('plan_name')
                            <span class="form-text text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label>Plan Price ($):</label>
                            <input type="number" class="form-control form-control-solid" name="plan_price" placeholder="Enter Plan Price" value="{{ old('plan_price') }}" />
                            @error('plan_price')
                            <span class="form-text text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label>Plan Interval:</label>
                            <select  class="form-control form-control-solid" name="plan_interval">
                                <option value="day">Day</option>
                                <option value="week">Week</option>
                                <option value="month">Month</option>
                                <option value="year">Year</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Plan Description:</label>
                            <textarea class="form-control form-control-solid" name="plan_description" placeholder="Enter Plan Description" />{{ old('plan_description') }}</textarea>
                            @error('plan_description')
                            <span class="form-text text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label>Plan Trial Days:</label>
                            <input type="number" class="form-control form-control-solid" name="plan_trial_days" placeholder="Enter Plan Trial Days" value="{{ old('plan_trial_days') }}" />
                            @error('plan_trial_days')
                            <span class="form-text text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label>Plan Type:</label>
                            <select class="form-control form-control-solid" name="plan_type">
                                <option value="custom">Custom</option>
                                <option value="default">Default</option>
                            </select>
                        </div>
                        <div class="form-group tenant-div">
                            <label>Select Tenant:</label>
                            <select class="form-control form-control-solid" name="tenant_id">
                                <option value="">Select Tenant</option>
                                @foreach($tenants_data as $single_tenant_data)
                                    <option value="{{ $single_tenant_data->id }}">{{ $single_tenant_data->tenant_name }}</option>
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
                                    <input type="checkbox" name="is_active" checked>
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
