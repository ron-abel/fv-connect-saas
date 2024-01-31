@extends('admin.layouts.default')

@section('title', 'VineConnect Admin - Versions History')

@section('content')
    <style>
        .versions-list {
            width: 100%;
            list-style: none;
            padding: 0px;
        }

        .version-header h3 {
            font-size: 16px;
            font-weight: 700;
        }

        .version-header {
            padding: 10px 10px 0px 10px;
        }

        .version-body {
            padding: 0px 10px 10px 10px;
            margin-top: 15px;
        }

        .version-body p {
            display: list-item;
            /* list-style: disc; */
        }

        .versions-list li {
            padding: 10px 0px;
        }

        .versions-list li:not(:last-child) {
            border-bottom: 2px solid #e9e9e9;
        }

        .versions-list-item {
            padding: 10px;
        }

        .versions-list-item:hover {
            color: #3F4254;
            background-color: #E4E6EF;
        }
    </style>
    <!--begin::Subheader-->
    <div class="subheader py-2 py-lg-4 subheader-solid" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
            <!--begin::Info-->
            <div class="d-flex align-items-center flex-wrap mr-2">
                <!--begin::Page Title-->
                <h4 class="text-dark font-weight-bold mt-2 mb-2 mr-5"><i class="icon-xl la la-support"></i> VineConnect
                    Version History</h4>
                <!--end::Page Title-->
            </div>
            <!--end::Info-->
        </div>
    </div>
    <!--end::Subheader-->

    <!--begin::Entry-->
    <div class="d-flex flex-column-fluid">
        <!--begin::Container-->
        <div class="container">
            <!--begin::Dashboard-->
            <!--begin::Row-->
            <div class="row">
                <div class="col-md-12">
                    <!--begin::Card-->
                    <div class="card card-custom gutter-b example example-compact">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-9 pull-left text-muted">
                                    <h6 class="card-title">
                                        {{ __('Showing') }} {{ $versions->firstItem() }} -
                                        {{ $versions->lastItem() }} / {{ $versions->total() }}
                                        ({{ __('page') }} {{ $versions->currentPage() }} )
                                    </h6>
                                </div>
                                <div class="col-md-3 pull-right">
                                    <div class="form-group pull-right">
                                      <input type="text" class="form-control" id="searchInput" onkeyup="searchList()" aria-describedby="helpId" placeholder="Search...">
                                    </div>
                                </div>
                            </div>


                            @if (count($versions) > 0)
                                <ul class="versions-list" id="searchUl">
                                    @foreach ($versions as $version)
                                        <li class="versions-list-item">
                                            <div class="version-header" data-description="{{ $version->description }}">
                                                <h4><a class="version-description" href="#">
                                                        {{ $version->major }}.{{ $version->minor }}.{{ $version->patch }}
                                                        - {{ $version->version_name }} </a></h4>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-secondary">No versions history found yet</p>
                            @endif
                        </div>
                        <div class="card-footer version-footer">
                            {!! $versions->appends($_GET)->links('admin.pages.pagination') !!}
                        </div>
                    </div>
                    <!--end::Card-->
                </div><!-- col-md-12 -->
            </div><!-- row -->
        </div><!-- container -->
    </div><!-- d-flex flex-column fluid -->


    {{-- bootstrap modal --}}
    <!-- Modal -->
    <div class="modal fade" id="versionModal" tabindex="-1" role="dialog" aria-labelledby="modelTitleId"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Body
                </div>
            </div>
        </div>
    </div>


@endsection
