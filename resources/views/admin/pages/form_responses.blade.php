@extends('admin.layouts.default')

@section('title', 'VineConnect Admin - Project Form')

@section('content')
    <!--begin::Subheader-->
    <div class="subheader py-2 py-lg-4 subheader-solid" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
            <!--begin::Info-->
            <div class="d-flex align-items-center flex-wrap mr-2">
                <!--begin::Page Title-->
                <h5 class="text-dark font-weight-bold mt-2 mb-2 mr-5">Form Responses</h5>
                <!--end::Page Title-->
            </div>
            <!--end::Info-->
        </div>
    </div>
    <div class="container">
        <div class="row form-list">
            <div class="col-md-12">
                <div class="card card-custom">
                    <div class="card-header">
                        <h4 class="card-title" style="text-transform: capitalize">Responses List Of
                            {{ isset($form_name) && !empty($form_name) ? $form_name : '' }}
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row mt-5">
                            <div class="col-md-1 mb-4"><b>Client ID</b></div>
                            <div class="col-md-2 mb-4"><b>Client Name</b></div>
                            <div class="col-md-1 mb-4"><b>Project ID</b></div>
                            <div class="col-md-2 mb-4"><b>Project Name</b></div>
                            <div class="col-md-3 mb-4"><b>Response</b></div>
                            <div class="col-md-2 mb-4"><b>Date</b></div>
                            <div class="col-md-1 mb-4"><b>Actions</b></div>
                        </div>
                        @foreach ($responses as $key => $response)
                            <div class="row mt-5">
                                <div class="col-md-1">{{ $response->fv_client_id }}</div>
                                <div class="col-md-2">{{ $response->client_name }}</div>
                                <div class="col-md-1 mb-4">{{ $response->fv_project_id }}</div>
                                <div class="col-md-2 mb-4">{{ $response->project_name }}</div>
                                <div class="col-md-3" data-toggle="tooltip" data-placement="top"
                                    title="{{ strlen($response->form_response_values_json) > 100 ? $response->form_response_values_json : '' }}">
                                    {{ strlen($response->form_response_values_json) > 100 ? substr($response->form_response_values_json, 0, 100) . '...' : $response->form_response_values_json }}
                                </div>
                                <div class="col-md-2 mb-4">{{ $response->created_at }}</div>
                                <div class="col-md-1">
                                    <button class="btn btn-success" data-toggle="modal"
                                        data-target="#form_response-{{ $key }}">
                                        <span class="fa fa-eye"></span>
                                    </button>
                                </div>
                            </div>
                            <div class="modal fade" id="form_response-{{ $key }}" tabindex="-1" role="dialog"
                                aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                    <div class="modal-content p-2 pt-5">
                                        @foreach (json_decode($response->form_response_values_json) as $item)
                                            <div class="form-group mb-5">
                                                <label
                                                    for="">{{ !empty($item->label) ? $item->label : $item->name }}</label>
                                                <div class="form-value">{{ is_array($item->value) ? implode(",", $item->value) : $item->value }}</div>
                                            </div>
                                        @endforeach
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        <div class="d-flex mt-5">
                            <a href="{{ url('admin/forms') }}" class="btn btn-primary"> <i class="fa fa-arrow-left"
                                    aria-hidden="true"></i>
                                Back To Form List</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
<style>
    .form-value {
        border: 1px solid #3e2a8d10;
        border-radius: 10px;
        padding: 5px 10px;
        font-size: 14px;
        background: #3e2a8d10;
        color: #222;
    }
</style>
