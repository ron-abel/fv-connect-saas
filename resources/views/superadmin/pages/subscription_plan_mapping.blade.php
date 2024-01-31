@extends('superadmin.layouts.default')

@section('title', 'Super Admin - Billing Plan Mapping')

@section('content')

    <div class="main-content container">
        <!--begin::Card-->
        <div class="card card-custom mt-6">
            <div class="card-header flex-wrap border-0 pt-6 pb-0">
                <div class="card-title">
                    <h3 class="card-label">Billing Plan Project Count Mapping</h3>
                </div>
                <div class="card-toolbar">
                    <button class="btn btn-primary font-weight-bolder add-subscription-mapping" data-toggle="modal"
                        data-target="#addMapping">
                        <i class="icon-xl la la-plus"></i>
                        Add New Mapping
                    </button>
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
                            <th title="Field #1">Plan Name</th>
                            <th title="Field #2">Project Count From</th>
                            <th title="Field #3">Project Count To</th>
                            <th title="Field #4">Created At</th>
                            <th title="Field #5">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($mappings as $mapping)
                            <tr>
                                <td>{{ $mapping->plan_name }}</td>
                                <td>{{ $mapping->project_count_from }}</td>
                                <td>{{ $mapping->project_count_to }}</td>
                                <td>{{ \Carbon\Carbon::parse($mapping->created_at)->format('Y-m-d H:i:s') }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a type="button" data-json="{{ json_encode($mapping) }}"
                                            class="btn btn-sm btn-clean btn-icon edit-subscription-mapping mr-3"
                                            data-toggle="modal" data-target="#addMapping" title="Edit Mapping"><i
                                                class="icon-xl la la-edit"></i></a>
                                        <a type="button" data-id="{{ $mapping->id }}"
                                            class="btn btn-sm btn-clean btn-icon remove-subscription-mapping"
                                            data-container="body" data-toggle="tooltip" data-placement="top"
                                            title="Delete Mapping"><i class="icon-xl la la-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <!--end: Datatable-->
            </div>
        </div>
        <!--end::Card-->


        <div class="modal fade" id="addMapping" tabindex="-1" role="dialog" aria-labelledby="addMappingLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <form action="{{ route('subscription_plan_mapping_post') }}" method="post">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addMappingLabel">Add/Update Billing Plan Project Mapping</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <i aria-hidden="true" class="ki ki-close"></i>
                            </button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="subscription_plan_mapping_id">
                            <div class="form-group">
                                <label> Choose Primary Trigger</label>
                                <select class="form-control" name="subscription_plan_id" required>
                                    <option value="">Select Billing Plan</option>
                                    @foreach ($subscription_plans as $subscription_plan)
                                        <option value="{{ $subscription_plan->id }}">
                                            {{ $subscription_plan->plan_name }}
                                            ({{ $subscription_plan->plan_price }}/{{ $subscription_plan->plan_interval }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label> Project Count From </label>
                                <input type="number" class="form-control" name="project_count_from" required />
                            </div>
                            <div class="form-group">
                                <label> Project Count To </label>
                                <input type="number" class="form-control" name="project_count_to" required />
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-light-success font-weight-bold">Submit</button>
                            <button type="button" class="btn btn-light-primary font-weight-bold"
                                data-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
@stop
