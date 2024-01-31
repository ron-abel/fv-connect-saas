@extends('superadmin.layouts.default')

@section('title', 'DB Backup')

@section('content')

    <div class="main-content container">
        <!--begin::Card-->
        <div class="card card-custom mt-6">
            <div class="card-header flex-wrap border-0 pt-6 pb-0">
                <div class="card-title">
                    <h3 class="card-label">DB Backup</h3>
                </div>
                <div class="card-toolbar">
                    <button type="button" class="btn btn-light-primary font-weight-bolder db-file-create">
                        <span class="svg-icon svg-icon-md">
                            <i class="icon-xl la la-plus"></i>
                        </span>Create a New Backup</button>
                </div>
            </div>
            <div class="card-body">
                <!--begin::Search Form-->
                <div class="mb-7">
                    <div class="row align-items-center">
                        <div class="col-lg-9 col-xl-8">
                            <div class="row align-items-center">
                                <div class="col-md-4 my-2 my-md-0">
                                    <div class="input-icon">
                                        <input type="text" class="form-control" placeholder="Search..."
                                            id="kt_datatable_search_query" />
                                        <span>
                                            <i class="flaticon2-search-1 text-muted"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Search Form-->
                <!--begin: Datatable-->
                <table class="datatable datatable-bordered datatable-head-custom" id="kt_datatable">
                    <thead>
                        <tr>
                            <th title="Field #1">File Name</th>
                            <th title="Field #2">Created At</th>
                            <th title="Field #3">Download</th>
                            <th title="Field #4">Delete</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($db_backups as $db)
                            <tr>
                                <td>{{ $db->file_name }}</td>
                                <td>{{ \Carbon\Carbon::parse($db->created_at)->format('F j, Y, g:i a') }}</td>
                                <td><a href="{{ url('backup/' . $db->file_name) }}" class="btn" download><i
                                            class="fa fa-download"></i></a></td>
                                <td><a class="btn btn-danger db-file-delete" data-target="{{ $db->id }}"><i
                                            class='fa fa-trash'></i></a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <!--end: Datatable-->
            </div>
        </div>
        <!--end::Card-->
    </div>
@stop
