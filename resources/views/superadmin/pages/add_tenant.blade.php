@extends('superadmin.layouts.default')

@section('title', 'Add Tenant')

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
                    <h3 class="card-title">Add Tenant</h3>
                </div>
                <!--begin::Form-->
                <form class="form" id="add_tenant_form" method="post" action="{{ route('add_tenant_post') }}">
                    @csrf

                    @if ( session()->has('error') )
                    <div>{{ session()->get('error') }}</div>
                    @endif

                    <div class="card-body">
                       <div class="form-group">
                            <label>Filevine Tenant Base URL:</label>
                            <input type="text" class="form-control form-control-solid" id="fv_tenant_base_url" name="fv_tenant_base_url" placeholder="Your Filevine Login URL" value="{{ old('fv_tenant_base_url') }}" />
                            <span class="form-text text-danger" id="tenant_name-error"></span>
                       </div>
                        <div class="form-group">
                            <label>Tenant Name:</label>
                            <input type="text" class="form-control form-control-solid" id="tenant_name" name="tenant_name" placeholder="Enter Tenant Name" value="{{ old('tenant_name') }}" />
                            @error('tenant_name')
                            <span class="form-text text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label>Tenant Description:</label>
                            <textarea class="form-control form-control-solid" name="tenant_description" placeholder="Enter Tenant Description" />{{ old('tenant_description') }}</textarea>
                            @error('tenant_description')
                            <span class="form-text text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label>SMS Enabled Cell Phone Number:</label>
                            <input type="text" class="form-control form-control-solid" name="test_tfa_number" placeholder="SMS Enabled Cell Phone Number" value="{{ old('test_tfa_number') }}" />
                            @error('test_tfa_number')
                            <span class="form-text text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label>Owner Name:</label>
                            <input type="text" class="form-control form-control-solid" id="owner_name" name="owner_name" placeholder="Enter Owner Name" value="{{ old('owner_name') }}" />
                            @error('owner_name')
                            <span class="form-text text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label>Owner Email Address:</label>
                            <input type="text" class="form-control form-control-solid" name="owner_email" placeholder="Enter Owner Email Address" value="{{ old('owner_email') }}" />
                            @error('owner_email')
                            <span class="form-text text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary add_tenant_save mr-2">Submit</button>
                    </div>
                </form>
                <!--end::Form-->
            </div>
            <!--end::Card-->
        </div>
    </div>
</div>

@stop
@section('scripts')
<script src="{{ asset('../js/superadmin/register.js') }}"></script>
@endsection
