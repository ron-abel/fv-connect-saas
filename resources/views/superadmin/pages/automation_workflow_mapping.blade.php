@extends('superadmin.layouts.default')

@section('title', 'Super Admin - Automation Workflow Mapping Rules')

@section('content')

    <div class="main-content container">
        <!--begin::Card-->
        <div class="card card-custom mt-6">
            <div class="card-header flex-wrap border-0 pt-6 pb-0">
                <div class="card-title">
                    <h3 class="card-label">Automation Workflow Trigger Action Mapping Rules</h3>
                </div>
                <div class="card-toolbar">
                    <button class="btn btn-primary font-weight-bolder add-mapping-rule" data-toggle="modal"
                        data-target="#addMappingRule">
                        <i class="icon-xl la la-plus"></i>
                        Add New Rule
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
                            <th title="Field #1">Primary Trigger</th>
                            <th title="Field #2">Trigger Event</th>
                            <th title="Field #3">Action Name</th>
                            <th title="Field #4">Created At</th>
                            <th title="Field #5">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($mapping_rules as $mapping_rule)
                            @php
                                $action_name_arr = explode(',', $mapping_rule->action_name);
                            @endphp
                            <tr>
                                <td>{{ $mapping_rule->primary_trigger }}</td>
                                <td>{{ $mapping_rule->trigger_event }}</td>
                                <td>
                                    <ul class="action_list">
                                        @foreach ($action_name_arr as $key => $value)
                                            <li class="action_name mt-1">{{ $value }}</li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($mapping_rule->created_at)->format('F j, Y, g:i a') }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a type="button" data-json="{{ json_encode($mapping_rule) }}"
                                            class="btn btn-sm btn-clean btn-icon edit-mapping-rule mr-3" data-toggle="modal"
                                            data-target="#addMappingRule" title="Edit Mapping Rule"><i
                                                class="icon-xl la la-edit"></i></a>
                                        <a type="button" data-id="{{ $mapping_rule->ids }}"
                                            class="btn btn-sm btn-clean btn-icon remove-mapping-rule" data-container="body"
                                            data-toggle="tooltip" data-placement="top" title="Delete Mapping Rule"><i
                                                class="icon-xl la la-trash"></i></a>
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


        <div class="modal fade" id="addMappingRule" tabindex="-1" role="dialog" aria-labelledby="addMappingRuleLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <form action="{{ route('automation_workflow_mapping_post') }}" method="post">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addMappingRuleLabel">Add Trigger Action Mapping Rule</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <i aria-hidden="true" class="ki ki-close"></i>
                            </button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="mapping_ids">
                            <div class="form-group">
                                <label> Choose Primary Trigger</label>
                                <select class="form-control" name="primary_trigger" required>
                                    <option value="">Select Trigger</option>
                                    <option value="Appointment">Calendar Apppointments</option>
                                    <option value="CalendarFeedback">Calendar Feedback</option>
                                    <option value="TeamMessageReply">Client Portal Team Message Reply from
                                        Client</option>
                                    <option value="CollectionItem">Collection Items</option>
                                    <option value="Contact">Contact Records</option>
                                    <option value="DocumentUploaded">Document - Uploaded</option>
                                    <option value="DocumentShared">Document - Shared</option>
                                    <option value="FormSubmitted">Form Submitted</option>
                                    <option value="ProjectRelation">Project Relations</option>
                                    <option value="Project">Project Triggers</option>
                                    <option value="Section">Section Visibility Toggled</option>
                                    <option value="SMSReceived">SMS Received</option>
                                    <option value="Note">Task Triggers</option>
                                </select>
                            </div>
                            <div class="form-group mt-3">
                                <label> Choose Trigger Event</label>
                                <select class="form-control" name="trigger_event" required>
                                    <option value="">Select Trigger Event</option>
                                </select>
                            </div>
                            <div class="form-group mt-3">
                                <label style="width: 100%"> Choose Action</label>
                                <select style="width: 100%" class="form-control select2" id="kt_select2_3"
                                    name="action_name[]" multiple="multiple" required>
                                    @foreach ($actions as $action)
                                        <option value="{{ $action['action_short_code'] }}-{{ $action['action_name'] }}">
                                            {{ $action['action_name'] }}
                                        </option>
                                    @endforeach
                                </select>
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

    <style>
        .btn i {
            padding-right: 0px !important;
        }
    </style>
@stop
