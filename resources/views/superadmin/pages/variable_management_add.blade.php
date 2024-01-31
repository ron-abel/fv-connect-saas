@extends('superadmin.layouts.default')

@section('title', 'Add Variable')

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
                        <h3 class="card-title">Add Variable</h3>
                    </div>
                    <!--begin::Form-->
                    <form class="form" method="post" action="{{ route('variable_management_add_post') }}">
                        @csrf

                        @if (session()->has('error'))
                            <div>{{ session()->get('error') }}</div>
                        @endif

                        <div class="card-body">
                            <div class="form-group">
                                <label>Variable Name</label>
                                <input type="text" class="form-control form-control-solid" id="variable_name"
                                    name="variable_name" placeholder="Variable Name" value="{{ old('variable_name') }}"
                                    required />
                                @error('variable_name')
                                    <span class="form-text text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Variable Key</label>
                                <input type="text" class="form-control form-control-solid" id="variable_key"
                                    name="variable_key" placeholder="Variable Key" value="{{ old('variable_key') }}"
                                    required />
                                @error('variable_key')
                                    <span class="form-text text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea class="form-control" name="variable_description" id="variable_description" placeholder="Variable Description"
                                    cols="30" rows="10">{{ old('variable_description') }}</textarea>
                                @error('variable_description')
                                    <span class="form-text text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary mr-2">Submit</button>
                            <a href="{{ route('variable_management') }}" type="button" role="button"
                                class="btn btn-secondary mr-2">Cancel</a>
                        </div>
                    </form>
                    <!--end::Form-->
                </div>
                <!--end::Card-->
            </div>
        </div>
    </div>

@stop
