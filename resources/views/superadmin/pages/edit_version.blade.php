@extends('superadmin.layouts.default')

@section('title', 'Edit Version')

@section('styles')
<style>
    <blade media|%20(min-width%3A%20992px)%20%7B>.container {
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
                    <h3 class="card-title">Edit Version</h3>
                </div>
                <!--begin::Form-->
                <form
                    class="form"
                    id="add_version_form"
                    method="post"
                    action="{{ route('version_management.edit_post', ['id' => $version->id]) }}"
                >
                    @csrf

                    @if( session()->has('error') )
                        <div>{{ session()->get('error') }}</div>
                    @endif

                    <div class="card-body">
                        <div class="form-group">
                            <label>Name:</label>
                            <input
                                type="text"
                                class="form-control form-control-solid"
                                id="version_name"
                                name="version_name"
                                placeholder="Version Name"
                                value="{{ $version->version_name }}"
                            />
                            @error('version_name')
                                <span class="form-text text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label>Description:</label>
                            <textarea
                                class="form-control"
                                name="description"
                                id="description"
                                placeholder="Version Description"
                                cols="30"
                                rows="10"
                            >{{ $version->description }}</textarea>
                            @error('description')
                                <span class="form-text text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="row">

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Major:</label>
                                    <input
                                        type="number"
                                        min="0"
                                        max="100"
                                        class="form-control form-control-solid"
                                        id="major"
                                        name="major"
                                        placeholder="Version Major"
                                        value="{{ $version->major }}"
                                    />
                                    @error('major')
                                        <span class="form-text text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Minor:</label>
                                    <input
                                        type="number"
                                        min="0"
                                        max="100"
                                        class="form-control form-control-solid"
                                        id="minor"
                                        name="minor"
                                        placeholder="Version Minor"
                                        value="{{ $version->minor }}"
                                    />
                                    @error('minor')
                                        <span class="form-text text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                            </div>

                            <div class="col-md-4">

                                <div class="form-group">
                                    <label>Patch:</label>
                                    <input
                                        type="number"
                                        min="0"
                                        max="100"
                                        class="form-control form-control-solid"
                                        id="patch"
                                        name="patch"
                                        placeholder="Version Patch"
                                        value="{{ $version->patch }}"
                                    />
                                    @error('patch')
                                        <span class="form-text text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="card-footer">
                        <button
                            type="submit"
                            class="btn btn-primary mr-2"
                        >Submit</button>
                        <a
                            href="{{ route('version_management') }}"
                            type="button"
                            role="button"
                            class="btn btn-secondary mr-2"
                        >Cancel</a>
                    </div>
                </form>
                <!--end::Form-->
            </div>
            <!--end::Card-->
        </div>
    </div>
</div>

@stop
