@extends('superadmin.layouts.default')

@section('title', 'Edit Template Category')

@section('content')

	<div class="main-content container">
		<div class="row mt-6">
			<div class="col-lg-12 col-md-12">
				<!--begin::Card-->
				<div class="card card-custom gutter-b example example-compact">
					<div class="card-header">
						<h3 class="card-title">Edit Template Category</h3>
					</div>
					<!--begin::Form-->
					<form class="form" method="post" action="{{ route('edit_template_category_post', ['template_category_id' => $template_category_details->id]) }}">
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
								<label>Template Name: <h3>{{$template->template_name}}</h3></label>

							</div>
							<div class="form-group">
								<label>Template Category Name:</label>
								<input type="text" class="form-control form-control-solid" id="template_category_name" name="template_category_name" placeholder="Enter Template Category Name" value="{{ $template_category_details->template_category_name}}" />

								@error('template_category_name')
									<span class="form-text text-danger">{{ $message }}</span>
								@enderror
							</div>
							<div class="form-group">
								<label>Template Category Description:</label>
								<textarea class="form-control form-control-solid" name="template_category_description" placeholder="Enter Template Category Description" />{{ $template_category_details->template_category_description }}</textarea>

								@error('template_category_description')
									<span class="form-text text-danger">{{ $message }}</span>
								@enderror
							</div>
						</div>
						<div class="card-footer">
							<button type="submit" class="btn btn-primary mr-2">Save</button>
							<a href="{{route('edit_template', ['template_id' => $template->id])}}" class="btn btn-default mr-2">Cancel</a>
						</div>
					</form>
					<!--end::Form-->
				</div>
				<!--end::Card-->
			</div>
		</div>
	</div>

@stop
