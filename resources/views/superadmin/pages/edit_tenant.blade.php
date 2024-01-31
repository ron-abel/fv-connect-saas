@extends('superadmin.layouts.default')

@section('title', 'Edit Tenant')

@section('content')

	<div class="main-content container">
		<div class="row mt-6">
			<div class="col-lg-12">
				<!--begin::Card-->
				<div class="card card-custom gutter-b example example-compact">
					<div class="card-header">
						<h3 class="card-title">Edit Tenant</h3>
					</div>
					<!--begin::Form-->
					<form class="form" method="post" action="{{ route('edit_tenant_post', ['tenant_id' => $tenant_details->id]) }}">
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
								<label>Tenant Name:</label>
								<input type="text" class="form-control form-control-solid" id="tenant_name" name="tenant_name" placeholder="Enter Tenant Name" value="{{ $tenant_details->tenant_name }}" />
								
								<a href="{{route('home', ['subdomain' => $tenant_details->tenant_name])}}" class="form-text mt-3">{{route('home', ['subdomain' => $tenant_details->tenant_name])}}</a>
								
								@error('tenant_name')
									<span class="form-text text-danger">{{ $message }}</span>
								@enderror
							</div>
							<div class="form-group">
								<label>Tenant Description:</label>
								<textarea class="form-control form-control-solid" name="tenant_description" placeholder="Enter Tenant Description" />{{ $tenant_details->tenant_description }}</textarea>
								
								@error('tenant_description')
									<span class="form-text text-danger">{{ $message }}</span>
								@enderror
							</div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $tenant_details->is_active ? "checked" : "" }} />
                                <span style="font-size:14px;">
                                    Active
                                </span>
                            </div>
						</div>
						<div class="card-footer">
							<button type="submit" class="btn btn-primary mr-2">Save</button>
						</div>
					</form>
					<!--end::Form-->
				</div>
				<!--end::Card-->
			</div>
		</div>
	</div>

@stop