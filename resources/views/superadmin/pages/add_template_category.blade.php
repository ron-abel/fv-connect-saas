@extends('superadmin.layouts.default')

@section('title', 'Add Template Category')

@section('content')

<div class="container">
    <div class="row mt-6">
        <div class="col-lg-12">
            <!--begin::Card-->
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title">Add Template Category</h3>
                </div>
                <!--begin::Form-->
                <form class="form" method="post" action="{{ route('add_template_category_post', ['template_id' => $template_details->id]) }}">
                    @csrf

                    @if ( session()->has('error') )
                    <div>{{ session()->get('error') }}</div>
                    @endif

                    <div class="card-body">
                        <div class="form-group">
                            <label>Template Category Name:</label>
                            <input type="text" class="form-control form-control-solid" id="template_category_name" name="template_category_name" placeholder="Enter Template Category Name" value="{{ old('template_category_name') }}" />
                            @error('template_category_name')
                            <span class="form-text text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label>Template Category Description:</label>
                            <textarea class="form-control form-control-solid" name="template_category_description" placeholder="Enter Template Category Description" />{{ old('template_category_description') }}</textarea>
                            @error('template_category_description')
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