@extends('superadmin.layouts.default')

@section('title', 'Version Management')

@section('content')

    <div class="main-content container">
        <!--begin::Card-->
        <div class="card card-custom mt-6">
            <div class="card-header flex-wrap border-0 pt-6 pb-0">
                <div class="card-title">
                    <h3 class="card-label">Version Management</h3>
                </div>
                <div class="card-toolbar">
                    <a class="btn btn-primary font-weight-bolder add-mapping-rule" href="{{ route('version_management.add') }}">
                        <i class="icon-xl la la-plus"></i>
                        Add New Version
                    </a>
                </div>
            </div>
            <div class="card-body">

                @if (session()->has('error'))
                    <div class="alert alert-danger" role="alert">
                        {{ session()->get('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @elseif(session()->has('success'))
                    <div class="alert alert-primary" role="alert">
                        {{ session()->get('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <!--begin: Datatable-->
                <table class="table table-bordered table-hover" id="superadmin_basic_datatable">
                    <thead>
                        <tr>
                            <th title="Field #1">Version Name</th>
                            <th title="Field #2">Description</th>
                            {{-- <th title="Field #3">Major</th>
                            <th title="Field #4">Minor</th>
                            <th title="Field #5">Patch</th> --}}
                            <th title="Field #5">Full Version</th>
                            <th title="Field #5">Created At</th>
                            <th title="Field #6">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($versions as $version)
                            <tr>
                                <td>{{ $version->version_name }}</td>
                                <td>{{ $version->description }}</td>
                                {{-- <td>{{ $version->major }}</td>
                                <td>{{ $version->minor }}</td>
                                <td>{{ $version->patch }}</td> --}}
                                <td>{{ $version->major }}.{{ $version->minor }}.{{ $version->patch }}</td>
                                <td>{{ \Carbon\Carbon::parse($version->created_at)->format('Y-m-d H:i:s') }}</td>
                                <td>
                                    <a href="{{ route('version_management.edit', ['id' => $version->id]) }}" class="btn btn-sm btn-clean btn-icon" title="Edit Version">
                                        <i class="icon-xl la la-pencil"></i>
                                    </a>
                                    <a href="javascript:;" data-url="{{ route('version_management.delete', ['id' => $version->id]) }}" class="btn btn-sm btn-clean btn-icon delete_version" title="Delete">
                                        <i class="icon-xl la la-trash-o"></i>
                                    </a>
                                </td>
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
@section('scripts')
<script>
    $('body').on('click', '.delete_version', function () {
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        Swal.fire({
            title: 'Are you sure to delete selected version?',
            icon: 'warning',
            showDenyButton: true,
            showCancelButton: false,
            confirmButtonText: 'Delete',
            denyButtonText: `Cancel`,
        }).then((result) => {
            if (result.isConfirmed) {
                var route_url = $(this).attr('data-url');
                $.ajax({
                    url: route_url,
                    type: 'POST',
                    data: { '_token': CSRF_TOKEN },
                    dataType: 'JSON',
                    success: function (data) {
                        if(data.success) {
                            Swal.fire({
                                title: data.message,
                                icon: 'success',
                                confirmButtonText: 'Ok',
                            }).then((result) => {
                                window.location.reload();
                            });
                        }
                        else {
                            Swal.fire({
                                title: data.message,
                                icon: 'error',
                                confirmButtonText: 'Ok',
                            });
                        }
                    }
                });
            }
        });
    });
</script>
@endsection
