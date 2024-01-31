@extends('superadmin.layouts.default')

@section('title', 'Add Template')

@section('content')

	<div class="container">
		<div class="row mt-6">
			<div class="col-lg-12">
				<!--begin::Card-->
				<div class="card card-custom gutter-b example example-compact">
					<div class="card-header">
						<h3 class="card-title">Add Template</h3>
					</div>
					<!--begin::Form-->
					<form class="form" method="post" action="{{ route('add_template_post') }}">
						@csrf
						
						@if ( session()->has('error') )
							<div>{{ session()->get('error') }}</div>
						@endif
						
						<div class="card-body">
							<div class="form-group">
								<label>Template Name:</label>
								<input type="text" class="form-control form-control-solid" id="template_name" name="template_name" placeholder="Enter Template Name" value="{{ old('template_name') }}" />
								@error('template_name')
									<span class="form-text text-danger">{{ $message }}</span>
								@enderror
							</div>
							<div class="form-group">
								<label>Template Description:</label>
								<textarea class="form-control form-control-solid" name="template_description" placeholder="Enter Template Description" />{{ old('template_description') }}</textarea>
								@error('template_description')
									<span class="form-text text-danger">{{ $message }}</span>
								@enderror
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