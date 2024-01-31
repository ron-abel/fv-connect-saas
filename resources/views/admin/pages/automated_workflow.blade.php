@extends('admin.layouts.default')

@section('title', 'VineConnect Admin - Automated Workflows')
@section('content')

<!--begin::Subheader-->
<div class="subheader py-2 py-lg-4 subheader-solid" id="kt_subheader">
    <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
        <!--begin::Info-->
        <div class="d-flex align-items-center flex-wrap mr-2">
            <!--begin::Page Title-->
            <h4 class="text-dark font-weight-bold mt-2 mb-2 mr-5">Automated Workflows</h4>
            <!--end::Page Title-->

        </div>
        <!--end::Info-->
    </div>
</div>
<!--end::Subheader-->
<div class="overlay loading"></div>
<div class="spinner-border text-primary loading" role="status">
    <span class="sr-only">Loading...</span>
</div>

<div class="d-flex flex-column-fluid">
    <!--begin::Container-->
    <div class="container">
        <!--begin::Row-->
        <div class="row">
            <div class="col-md-12">
                <!--begin::Card-->
                <div class="card card-custom gutter-b example example-compact">
                    <div class="card-header">
                        <h5 class="card-title mt-7">Automated Workflows</h5>
                    </div>
                    <div class="card-body">
                        <div class="pg_content">
                        </div>

                        <ul class="nav nav-pills" id="myTab1" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link btn btn-outline-primary active" id="trigger-tab" data-toggle="tab" href="#configure_trigger">
                                    <span class="nav-text">Configure Triggers</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link btn btn-outline-primary" id="configure-action-tab" data-toggle="tab" href="#configure_action" aria-controls="profile">
                                    <span class="nav-text">Configure Actions</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link btn btn-outline-primary" id="map-tab" data-toggle="tab" href="#map_workflow" aria-controls="contact">
                                    <span class="nav-text">Map Your Workflows</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link btn btn-outline-primary" id="logs-tab" data-toggle="tab" href="#logs" aria-controls="contact">
                                    <span class="nav-text">Logs</span>
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content mt-6" id="myTabContent1">

                            <div class="tab-pane fade show active" id="configure_trigger" role="tabpanel" aria-labelledby="trigger-tab">
                                <p><b>Instructions:</b> When selecting a trigger, think about what you want to have take
                                    place that will alert the system to action something. These triggers are also
                                    capable of filtering down to specific Filevine data which allows for both broad and
                                    specified triggers to occur. Be sure to toggle the Trigger “Eligible” once you’ve
                                    saved it to utilize it during mapping.</p>

                                <div class="row">
                                    <div class="col-md-2">
                                        <label> Choose Primary Trigger</label>
                                        <select class="form-control" name="primary_trigger">
                                            <option value="">Select Trigger</option>
                                            <option value="Appointment">Calendar Appointment</option>
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
                                    <div class="col-md-2 d-none trigger_event_div">
                                        <label> Choose Trigger Event</label>
                                        <select class="form-control" name="trigger_event">
                                            <option value="">Select Trigger Event</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-1 d-none filter_selection_div">
                                        <label>Filter</label>
                                        <div class="checkbox-inline">
                                            <label class="checkbox checkbox-outline checkbox-outline-2x checkbox-primary checkbox-lg">
                                                <input type="checkbox" name="filter_selection">
                                                <span></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-2 d-none project_type_div">
                                        <label> Choose Project Type</label>
                                        <select class="form-control" name="project_type_id">
                                            <option value="">Project Type...</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 d-none phase_name_div">
                                        <label> Phase Changed To</label>
                                        <select class="form-control" name="phase_name_id">
                                            <option value="">Phase...</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 d-none filter_contact_by_div">
                                        <label> Filter Contacts By</label>
                                        <select class="form-control" name="filter_contact_by">
                                            <option value="">Select Contact By</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 d-none person_type_selection_div">
                                        <label> Person Type Selection</label>
                                        <select class="form-control" name="person_type_selection_id">
                                            <option value="">Person Types</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 d-none project_section_div">
                                        <label class="project_section_label"> Choose Section</label>
                                        <select class="form-control" name="project_section_selector">
                                            <option value="">Choose Section</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 d-none project_section_field_div">
                                        <label> Choose Field</label>
                                        <select class="form-control" name="project_section_field_selector">
                                            <option value="">Choose Field</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 d-none filter_task_by_div">
                                        <label> Filter Tasks By</label>
                                        <select class="form-control" name="filter_task_by">
                                            <option value="">Select Task By</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 d-none filter_appointment_by_div">
                                        <label> Filter Appointments By</label>
                                        <select class="form-control" name="filter_appointment_by">
                                            <option value="">Select Appointment</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 d-none org_user_div">
                                        <label> Select Org User</label>
                                        <select class="form-control" name="org_user_id">
                                            <option value="">User...</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 d-none project_hashtag_div">
                                        <label class="hastag_label"> Project Hashtag</label>
                                        <input type="text" name="project_hashtag" class="form-control" placeholder="sample">
                                    </div>
                                    <div class="col-md-2 d-none tenant_form_div">
                                        <label> Form Submitted</label>
                                        <select class="form-control" name="tenant_form_id">
                                            <option value="">Choose Form</option>
                                            @foreach ($tenant_forms as $tenant_form)
                                            <option value="{{ $tenant_form->id }}">{{ $tenant_form->form_name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 d-none client_file_upload_configuration_div">
                                        <label> Select File Upload Scheme</label>
                                        <select class="form-control" name="client_file_upload_configuration_id">
                                            <option value="">Choose Upload Scheme</option>
                                            @foreach ($client_file_upload_configurations as $client_file_upload_configuration)
                                            <option value="{{ $client_file_upload_configuration->id }}">
                                                {{ $client_file_upload_configuration->choice }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 d-none sms_line_div">
                                        <label> Select SMS Line</label>
                                        <select class="form-control" name="sms_line">
                                            <option value="">Choose SMS Line</option>
                                            <option value="Phase Change">Phase Change</option>
                                            <option value="Review Request">Review Request</option>
                                            <option value="Mass Message">Mass Message</option>
                                            <option value="2FA Verification">2FA Verification</option>
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <label> Name This Trigger</label>
                                        <input type="text" name="trigger_name" class="form-control" placeholder="Primary Trigger-Event">
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-success mt-8 save-trigger">Save</button>
                                    </div>
                                </div>

                                <div class="row mt-6">
                                    <div class="col-md-12 mb-6">
                                        <h4 class="mt-6">Saved Triggers</h4>
                                    </div>
                                    <div class="col-md-12">
                                        <table class="table table-bordered table-hover" id="trigger_datatable">
                                            <thead>
                                                <tr>
                                                    <th>Trigger ID</th>
                                                    <th>Primary Trigger</th>
                                                    <th>Trigger Event</th>
                                                    <th>Trigger Name</th>
                                                    <th>Trigger Filters</th>
                                                    <th>Eligible</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($triggers as $trigger)
                                                <tr class="trigger-row">
                                                    <td>{{ $trigger->trigger_table_id }}</td>
                                                    <td>{{ $trigger->primary_trigger_display }}</td>
                                                    <td>{{ $trigger->trigger_event }}</td>
                                                    <td>{{ $trigger->trigger_name }}</td>
                                                    <td>{{ $trigger->filter }}</td>
                                                    <td>
                                                        <label class="custom-checkbox-switch">
                                                            <input value="{{ $trigger->trigger_table_id }}" type="checkbox" class="trigger-eligible" {{ $trigger->is_active ? 'checked' : '' }}>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <button type="button" data-json="{{ json_encode($trigger) }}" class="btn btn-sm btn-success trigger-edit mr-3" data-toggle="modal" data-target="#triggerEditModal" title="Edit Trigger"><i class="fa fa-edit"></i></button>
                                                            <button type="button" data-id="{{ $trigger->trigger_table_id }}" data-used="{{ $trigger->is_used ? '1' : '0' }}" class="btn btn-sm btn-danger remove" data-container="body" data-toggle="tooltip" data-placement="top" title="Delete Trigger"><i class="fa fa-trash"></i></button>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th colspan="2"><button type="button" class="btn btn-sm btn-success trigger-all-status" title="All Toggle On">All Toggle On</button></th>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="configure_action" role="tabpanel" aria-labelledby="configure-action-tab">
                                <p><b>Instructions:</b> The options for Actions indicate what will happen once the
                                    trigger takes place, this means you want to think about how that trigger will effect
                                    your action taken either in Filevine or otherwise. Once saved you will need to
                                    toggle the Action “Eligible” to utilize it during mapping.</p>
                                <div class="callout_subtle lightgrey"><i class="fa fa-key mr-3"></i><a href="{{ url('admin/variables') }}" target="_blank" />&nbsp;List of
                                    Variables</a></div>
                                <form action="{{ route('automated_workflow_add_action', ['subdomain' => $subdomain]) }}" method="post">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-2">
                                            <label> Choose Action</label>
                                            <select class="form-control" name="initial_action_id" required>
                                                <option value="">Choose Action</option>
                                                @foreach ($initial_actions as $action)
                                                <option value="{{ $action->id }}-{{ $action->action_short_code }}" {{ $action->disabled }}>
                                                    {{ $action->action_name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <!-- <div class="col-md-4 d-none client_sms_body_div">
                                                <label class="sms_body_label">Body</label>
                                                <textarea name="client_sms_body" class="form-control" rows="4"></textarea>
                                            </div> -->
                                        <div class="col-md-2 d-none send_sms_choice_div">
                                            <label> Send SMS Choice</label>
                                            <select class="form-control" name="send_sms_choice">
                                                <option value="">SMS Choice...</option>
                                                <option value="To Project Client">To Project Client</option>
                                                <option value="To Person Field">To Person Field</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2 d-none person_field_project_type_div">
                                            <label> Choose Project Type</label>
                                            <select class="form-control" name="person_field_project_type_id">
                                                <option value="">Project Type...</option>
                                            </select>
                                        </div>
                                        <input type="hidden" name="person_field_project_type_name">

                                        <div class="col-md-2 d-none person_field_project_type_section_selector_div">
                                            <label> Choose Section Selector</label>
                                            <select class="form-control" name="person_field_project_type_section_selector">
                                                <option value="">Section Selector...</option>
                                            </select>
                                        </div>
                                        <input type="hidden" name="person_field_project_type_section_selector_name">

                                        <div class="col-md-2 d-none person_field_project_type_section_field_selector_div">
                                            <label> Choose Field Selector</label>
                                            <select class="form-control" name="person_field_project_type_section_field_selector">
                                                <option value="">Field Selector...</option>
                                            </select>
                                        </div>
                                        <input type="hidden" name="person_field_project_type_section_field_selector_name">


                                        <!-- Mirror Project Type, Section Selector, Field Selector -->
                                        <div class="col-md-2 d-none mirror_div">
                                            <label> Mirror From Project Type</label>
                                            <select class="form-control mirror-select-item" name="mirror_from_field_project_type_id">
                                                <option value="">Project Type...</option>
                                            </select>
                                        </div>
                                        <input type="hidden" name="mirror_from_field_project_type_name">

                                        <div class="col-md-2 d-none mirror_div">
                                            <label> Choose Section Selector</label>
                                            <select class="form-control mirror-select-item" name="mirror_from_field_project_type_section_selector">
                                                <option value="">Section Selector...</option>
                                            </select>
                                        </div>
                                        <input type="hidden" name="mirror_from_field_project_type_section_selector_name">

                                        <div class="col-md-2 d-none mirror_div">
                                            <label> Choose Field Selector</label>
                                            <select class="form-control mirror-select-item" name="mirror_from_field_project_type_section_field_selector">
                                                <option value="">Field Selector...</option>
                                            </select>
                                        </div>
                                        <input type="hidden" name="mirror_from_field_project_type_section_field_selector_name">

                                        <div class="col-md-2 d-none mirror_div">
                                            <label> Mirror To Project Type</label>
                                            <select class="form-control mirror-select-item" name="mirror_to_field_project_type_id">
                                                <option value="">Project Type...</option>
                                            </select>
                                        </div>
                                        <input type="hidden" name="mirror_to_field_project_type_name">

                                        <div class="col-md-2 d-none mirror_div">
                                            <label> Choose Section Selector</label>
                                            <select class="form-control mirror-select-item" name="mirror_to_field_project_type_section_selector">
                                                <option value="">Section Selector...</option>
                                            </select>
                                        </div>
                                        <input type="hidden" name="mirror_to_field_project_type_section_selector_name">

                                        <div class="col-md-2 d-none mirror_div">
                                            <label> Choose Field Selector</label>
                                            <select class="form-control mirror-select-item" name="mirror_to_field_project_type_section_field_selector">
                                                <option value="">Field Selector...</option>
                                            </select>
                                        </div>
                                        <input type="hidden" name="mirror_to_field_project_type_section_field_selector_name">


                                        <!-- Update Project Team -->
                                        <div class="col-md-2 d-none project_team_choice_div">
                                            <label> Project Team Choice</label>
                                            <select class="form-control" name="project_team_choice">
                                                <option value="">Team Choice...</option>
                                                <option value="Add a Team Member">Add a Team Member</option>
                                                <option value="Remove a Team Member">Remove a Team Member</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2 d-none team_member_user_div">
                                            <label> Team User</label>
                                            <select class="form-control" name="team_member_user_id">
                                                <option value="">Select User</option>
                                            </select>
                                        </div>
                                        <input type="hidden" name="team_member_user_name">
                                        <div class="col-md-2 d-none add_team_member_choice_div">
                                            <label> Team Member Choice</label>
                                            <select class="form-control" name="add_team_member_choice">
                                                <option value="">Member Choice...</option>
                                                <option value="Is Primary">Is Primary</option>
                                                <option value="Is Admin">Is Admin</option>
                                                <option value="Is First Primary">Is First Primary</option>
                                                <option value="Level">Level</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2 d-none add_team_member_choice_level_div">
                                            <label> Team Member Choice Level</label>
                                            <select class="form-control" name="add_team_member_choice_level">
                                                <option value="">Member Choice Level...</option>
                                                <option value="Follower">Follower</option>
                                                <option value="Collaborator">Collaborator</option>
                                                <option value="Guest">Guest</option>
                                            </select>
                                        </div>


                                        <div class="form-group col-md-1 d-none fv_project_note_with_pin_div">
                                            <label>With Pin</label>
                                            <div class="checkbox-inline">
                                                <label class="checkbox checkbox-outline checkbox-outline-2x checkbox-primary checkbox-lg">
                                                    <input type="checkbox" name="fv_project_note_with_pin">
                                                    <span></span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-3 d-none fv_project_hashtag_div">
                                            <label class="fv_project_hashtag_label">Hashtag (do not include
                                                "#")</label>
                                            <input type="text" name="fv_project_hashtag" class="form-control" placeholder="sample">
                                        </div>
                                        <div class="col-md-2 d-none fv_project_task_assign_type_div">
                                            <label>Assign By</label>
                                            <select class="form-control" name="fv_project_task_assign_type">
                                                <option value="">Select Assign Type</option>
                                                <option value="user">Assign to User</option>
                                                {{-- <option value="role">Assign to Role</option> --}}
                                            </select>
                                        </div>
                                        <div class="col-md-2 d-none fv_project_task_assign_user_div">
                                            <label> Assign User</label>
                                            <select class="form-control" name="fv_project_task_assign_user_id">
                                                <option value="">Select User</option>
                                            </select>
                                        </div>
                                        <input type="hidden" name="fv_project_task_assign_user_name">
                                        <div class="col-md-2 d-none fv_project_task_assign_user_role_div">
                                            <label>Assign Role</label>
                                            <select class="form-control" name="fv_project_task_assign_user_role">
                                                <option value="">Select Role</option>
                                            </select>
                                        </div>
                                        <input type="hidden" name="fv_project_task_assign_user_role_name">
                                        <div class="col-md-2 d-none section_visibility_project_type_div">
                                            <label> Project Type</label>
                                            <select class="form-control" name="section_visibility_project_type_id">
                                                <option value="">Select Project Type</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2 d-none section_visibility_section_selector_div">
                                            <label> Section Selector</label>
                                            <select class="form-control" name="section_visibility_section_selector">
                                                <option value="">Select Section Selector</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2 d-none section_visibility_div">
                                            <label> Section Visibility</label>
                                            <select class="form-control" name="section_visibility">
                                                <option value="">Select Section Visibility</option>
                                                <option value="Visible">Visible</option>
                                                <option value="Hidden">Hidden</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2 d-none phase_assignment_div">
                                            <label>Phase Assignment</label>
                                            <select class="form-control" name="phase_assignment">
                                                <option value="">Select Phase Assignment</option>
                                                <option value="Next_Sequential_Phase">Next Sequential Phase</option>
                                                <option value="Specific_Phase">Specific Phase</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2 d-none phase_assignment_project_type_div">
                                            <label> Project Type</label>
                                            <select class="form-control" name="phase_assignment_project_type_id">
                                                <option value="">Select Project Type</option>
                                            </select>
                                        </div>
                                        <input type="hidden" name="phase_assignment_project_type_name">
                                        <div class="col-md-2 d-none project_phase_id_native_div">
                                            <label> Project Phase</label>
                                            <select class="form-control" name="project_phase_id_native">
                                                <option value="">Select Project Phase</option>
                                            </select>
                                        </div>
                                        <input type="hidden" name="project_phase_id_native_name">
                                        <div class="col-md-4 d-none delivery_hook_url_div">
                                            <label class="delivery_hook_url_label">Delivery Hook URL</label>
                                            <input type="url" name="delivery_hook_url" class="form-control" placeholder="https://example.com">
                                        </div>
                                        <div class="col-md-2">
                                            <label>Name This Action</label>
                                            <input type="text" name="configure_action_name" class="form-control" placeholder="Action Name" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label>Action Description</label>
                                            <input type="text" name="action_description" class="form-control">
                                            <!-- <textarea name="action_description" class="form-control" rows="1"></textarea> -->
                                        </div>
                                        
                                    </div>
                                    <div class="row">
                                    <div class="col-md-12 d-none client_sms_body_div">
                                                <label class="sms_body_label">Body</label>
                                                <textarea name="client_sms_body" class="form-control" rows="4"></textarea>
                                            </div>
                                    <div class="col-md-12 d-none client_tiny_sms_body_div">
                                                <label class="sms_body_label">Body</label>
                                                <textarea name="client_sms_body" class="form-control clientEmailBody" rows="4"></textarea>
                                            </div>
                                            <div class="col-md-1">
                                            <button type="submit" class="btn btn-success mt-8">Save</button>
                                        </div>
                                    </div>
                                </form>

                                <div class="row mt-6">
                                    <div class="col-md-12 mb-6">
                                        <h4 class="mt-6">List of Action</h4>
                                    </div>

                                    <div class="col-md-12">
                                        <table class="table table-bordered table-hover" id="action_datatable">
                                            <thead>
                                                <tr>
                                                    <th>Action ID</th>
                                                    <th>Action Name</th>
                                                    <th>Description</th>
                                                    <th>Action Body</th>
                                                    <th>Eligible</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($configure_actions as $configure_action)
                                                <tr class="action-row">
                                                    <td>{{ $configure_action->id }}</td>
                                                    <td>{{ $configure_action->action_name }}</td>
                                                    <td>{{ $configure_action->action_description }}</td>
                                                    <td>{{ $configure_action->action_body }}</td>
                                                    <td>
                                                        <label class="custom-checkbox-switch">
                                                            <input value="{{ $configure_action->id }}" type="checkbox" class="action-status" {{ $configure_action->is_active ? 'checked' : '' }}>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <button type="button" data-json="{{ json_encode($configure_action) }}" class="btn btn-sm btn-success action-edit mr-3" data-toggle="modal" data-target="#actionEditModal" title="Edit Action"><i class="fa fa-edit"></i></button>
                                                            <button type="button" data-id="{{ $configure_action->id }}" data-used="{{ $configure_action->is_used }}" class="btn btn-sm btn-danger remove-action" data-container="body" data-toggle="tooltip" data-placement="top" title="Delete Action"><i class="fa fa-trash"></i></button>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th colspan="2"><button type="button" class="btn btn-sm btn-success action-all-status" title="All Toggle On">All Toggle On</button></th>
                                            </tfoot>
                                        </table>
                                    </div>

                                    {{-- @if ($disable_add_hashtag)
                                            <div class="col-md-12 mt-6 alert alert-custom alert-notice alert-light-danger fade show mb-5"
                                                role="alert">
                                                <div class="alert-icon">
                                                    <i class="flaticon2-warning"></i>
                                                </div>
                                                <div class="alert-text">"Add Project Hashtag" action is not available! It's
                                                    possible only when the FileVine API URL is not the default URL:
                                                    "https://app.filevine.com"!</div>
                                            </div>
                                        @endif  --}}
                                </div>
                            </div>

                            <div class="tab-pane fade" id="map_workflow" role="tabpanel" aria-labelledby="map-tab">
                                <p><b>Instructions:</b> When selecting a Trigger and Action be sure to think about the
                                    way you can connect other mapped workflows together as well. You can test these
                                    workflows first while the status is in Test, by selecting the edit symbol next to
                                    the workflow. The Logs will show if that workflow was successful.</p>

                                <form action="{{ route('automated_workflow_map_add', ['subdomain' => $subdomain]) }}" method="post">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-2">
                                            <label> Choose Trigger</label>
                                            <select class="form-control selectpicker" name="map_trigger_id" data-live-search="true" data-size="5" required>
                                                <option value="">Select Trigger</option>
                                                @foreach ($initial_triggers as $trigger)
                                                <option value="{{ $trigger->id }}">
                                                    {{ $trigger->trigger_name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label> Choose Action</label>
                                            <select class="form-control selectpicker" name="map_action_id" data-live-search="true" data-size="5" required>
                                                <option value="">Select Action</option>
                                                @foreach ($eligible_actions as $action)
                                                <option class="action-option" value="{{ $action->id }}">
                                                    {{ $action->action_name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label>Workflow Description</label>
                                            <textarea name="workflow_description" class="form-control" rows="4"></textarea>
                                        </div>
                                        <div class="col-md-1">
                                            <button type="submit" class="btn btn-success mt-8 save-action-map">Save</button>
                                        </div>
                                    </div>
                                </form>

                                <div class="row mt-6">
                                    <div class="col-md-12 mb-6">
                                        <h4 class="mt-6">Saved Map Workflow</h4>
                                    </div>
                                    <div class="col-md-12">
                                        <table class="table table-bordered table-hover" id="action_map_datatable">
                                            <thead>
                                                <tr>
                                                    <th>Map ID</th>
                                                    <th>Trigger</th>
                                                    <th>Trigger ID</th>
                                                    <th>Action Name</th>
                                                    <th>Action ID</th>
                                                    <th>Workflow Description</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($action_maps as $action_map)
                                                <tr class="action-row">
                                                    <td>{{ $action_map->map_id }}</td>
                                                    <td>{{ $action_map->trigger_name }}</td>
                                                    <td>{{ $action_map->trigger_id }}</td>
                                                    <td>{{ $action_map->action_name }}</td>
                                                    <td>{{ $action_map->id }}</td>
                                                    <td>{{ $action_map->workflow_description }}</td>
                                                    <td>{{ ucfirst($action_map->status) }}</td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <button type="button" data-json="{{ json_encode($action_map) }}" class="btn btn-sm btn-success action-map-edit mr-3" data-toggle="modal" data-target="#actionMapModal" title="Edit Action Map"><i class="fa fa-edit"></i></button>
                                                            <button type="button" data-id="{{ $action_map->map_id }}" class="btn btn-sm btn-danger remove-map" data-container="body" data-toggle="tooltip" data-placement="top" title="Delete Map"><i class="fa fa-trash"></i></button>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="logs" role="tabpanel" aria-labelledby="logs-tab">
                                <!--begin::Card-->
                                <div class="card card-custom" style="-webkit-box-shadow: none;-moz-box-shadow: none;-o-box-shadow: none;box-shadow: none;">
                                    <div class="card-header flex-wrap border-0 pb-0" style="padding:0">
                                        <div class="card-title">
                                            <h3 class="card-label">All Trigger Logs</h3>
                                        </div>
                                        <div class="card-toolbar">
                                            <!--begin::Dropdown-->
                                            <div class="dropdown dropdown-inline mr-2">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <form class="log-form">
                                                            <div id="logreportrange" class="custom-date-picker">
                                                                <i class="fa fa-calendar"></i>&nbsp;
                                                                <span></span> <i class="fa fa-caret-down"></i>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            <!--end::Dropdown-->
                                        </div>
                                    </div>
                                    <div class="card-body" style="padding:0">
                                        <table class="table table-bordered table-hover" id="kt_datatable_custom_logs">
                                            <thead>
                                                <tr>
                                                    <th>Trigger ID</th>
                                                    <th>Map IDs</th>
                                                    <th>Action IDs</th>
                                                    <th>FV Project ID</th>
                                                    <th>Workflow Description</th>
                                                    <th>Is Handled?</th>
                                                    <th>Timestamp</th>
                                                    <th>Details</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                                <!--end::Card-->
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Logs Details Modal-->
        <div class="modal fade" id="logDetails" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Request JSON Details</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <i aria-hidden="true" class="ki ki-close"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <textarea name="json_details" class="json-details" cols="70" rows="15"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light-primary font-weight-bold" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Logs Details Modal-->
        <div class="modal fade" id="actionLogDetails" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Action Log Details</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <i aria-hidden="true" class="ki ki-close"></i>
                        </button>
                    </div>
                    <div class="modal-body" style="padding-top:0px">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">Action ID</th>
                                    <th scope="col">Project ID</th>
                                    <th scope="col">Client ID</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Phone</th>
                                    <th scope="col">Note</th>
                                </tr>
                            </thead>
                            <tbody class="action_log_table_body">
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light-primary font-weight-bold" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Info Modal-->
        {{-- <div class="modal fade" id="actionInfo" tabindex="-1" role="dialog" aria-labelledby="actionInfoLabel"
                aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <form action="{{ route('automated_workflow_action_update_data', ['subdomain' => $subdomain]) }}"
        method="post">
        @csrf
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="actionInfoLabel">Update Action Info</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i aria-hidden="true" class="ki ki-close"></i>
                </button>
            </div>
            <div class="modal-body">
                <input name="action_id" type="hidden" class="form-control">
                <div class="form-group mt-3">
                    <label>Action Name</label>
                    <input name="action_name" type="text" class="form-control" readonly>
                </div>
                <div class="form-group mt-3">
                    <label>Action Description</label>
                    <input name="action_description" type="text" class="form-control" required>
                </div>
                <div class="form-group mt-3">
                    <label>Status</label>
                    <label class="custom-checkbox-switch">
                        <input type="checkbox" name="is_active">
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-light-success font-weight-bold">Submit</button>
                <button type="button" class="btn btn-light-primary font-weight-bold" data-dismiss="modal">Cancel</button>
            </div>
        </div>
        </form>
    </div>
</div> --}}

<!-- Action Edit Info Modal-->
<div class="modal fade" id="actionEditModal" tabindex="-1" role="dialog" aria-labelledby="actionEditModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('automated_workflow_action_update', ['subdomain' => $subdomain]) }}" method="post">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="actionEditModalLabel">Update Action Information <span class="action_edit_id"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i aria-hidden="true" class="ki ki-close"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <input name="update_map_action_id" type="hidden">
                    <input name="update_map_action_short_code" type="hidden">
                    <div class="form-group">
                        <label>Action Name</label>
                        <input name="update_map_action_name" type="text" class="form-control" required>
                    </div>
                    <div class="form-group mt-3">
                        <label>Description</label>
                        <input name="update_map_action_description" type="text" class="form-control">
                    </div>
                    {{-- <div class="form-group mt-3">
                                    <label>Note</label>
                                    <textarea name="update_map_action_note" class="form-control" rows="5"></textarea>
                                </div> --}}
                    <div class="form-group mt-3 d-none update_client_sms_body_div">
                        <label>Action Body</label>
                        <textarea name="update_client_sms_body" class="form-control" rows="5"></textarea>
                    </div>

                    <div class="form-group mt-3 d-none update_send_sms_choice_div">
                        <label> Send SMS Choice</label>
                        <select class="form-control" name="update_send_sms_choice">
                            <option value="">SMS Choice...</option>
                            <option value="To Project Client">To Project Client</option>
                            <option value="To Person Field">To Person Field</option>
                        </select>
                    </div>
                    <div class="form-group mt-3 d-none update_person_field_project_type_div">
                        <label> Choose Project Type</label>
                        <select class="form-control" name="update_person_field_project_type_id">
                            <option value="">Project Type...</option>
                        </select>
                    </div>
                    <input type="hidden" name="update_person_field_project_type_name">

                    <div class="form-group mt-3 d-none update_person_field_project_type_section_selector_div">
                        <label> Choose Section Selector</label>
                        <select class="form-control" name="update_person_field_project_type_section_selector">
                            <option value="">Section Selector...</option>
                        </select>
                    </div>
                    <input type="hidden" name="update_person_field_project_type_section_selector_name">

                    <div class="form-group mt-3 d-none update_person_field_project_type_section_field_selector_div">
                        <label> Choose Field Selector</label>
                        <select class="form-control" name="update_person_field_project_type_section_field_selector">
                            <option value="">Field Selector...</option>
                        </select>
                    </div>
                    <input type="hidden" name="update_person_field_project_type_section_field_selector_name">


                    <!-- Mirror Project Type, Section Selector, Field Selector -->
                    <div class="form-group mt-3 d-none update_mirror_div">
                        <label> Mirror From Project Type</label>
                        <select class="form-control mirror-select-item" name="update_mirror_from_field_project_type_id">
                            <option value="">Project Type...</option>
                        </select>
                    </div>
                    <input type="hidden" name="update_mirror_from_field_project_type_name">

                    <div class="form-group mt-3 d-none update_mirror_div">
                        <label> Choose Section Selector</label>
                        <select class="form-control mirror-select-item" name="update_mirror_from_field_project_type_section_selector">
                            <option value="">Section Selector...</option>
                        </select>
                    </div>
                    <input type="hidden" name="update_mirror_from_field_project_type_section_selector_name">

                    <div class="form-group mt-3 d-none update_mirror_div">
                        <label> Choose Field Selector</label>
                        <select class="form-control mirror-select-item" name="update_mirror_from_field_project_type_section_field_selector">
                            <option value="">Field Selector...</option>
                        </select>
                    </div>
                    <input type="hidden" name="update_mirror_from_field_project_type_section_field_selector_name">

                    <div class="form-group mt-3 d-none update_mirror_div">
                        <label> Mirror To Project Type</label>
                        <select class="form-control update-mirror-select-item" name="update_mirror_to_field_project_type_id">
                            <option value="">Project Type...</option>
                        </select>
                    </div>
                    <input type="hidden" name="update_mirror_to_field_project_type_name">

                    <div class="form-group mt-3 d-none update_mirror_div">
                        <label> Choose Section Selector</label>
                        <select class="form-control mirror-select-item" name="update_mirror_to_field_project_type_section_selector">
                            <option value="">Section Selector...</option>
                        </select>
                    </div>
                    <input type="hidden" name="update_mirror_to_field_project_type_section_selector_name">

                    <div class="form-group mt-3 d-none update_mirror_div">
                        <label> Choose Field Selector</label>
                        <select class="form-control mirror-select-item" name="update_mirror_to_field_project_type_section_field_selector">
                            <option value="">Field Selector...</option>
                        </select>
                    </div>
                    <input type="hidden" name="update_mirror_to_field_project_type_section_field_selector_name">


                    <!-- Update Project Team -->
                    <div class="form-group mt-3 d-none update_project_team_choice_div">
                        <label> Project Team Choice</label>
                        <select class="form-control" name="update_project_team_choice">
                            <option value="">Team Choice...</option>
                            <option value="Add a Team Member">Add a Team Member</option>
                            <option value="Remove a Team Member">Remove a Team Member</option>
                        </select>
                    </div>
                    <div class="form-group mt-3 d-none update_team_member_user_div">
                        <label> Team User</label>
                        <select class="form-control" name="update_team_member_user_id">
                            <option value="">Select User</option>
                        </select>
                    </div>
                    <input type="hidden" name="update_team_member_user_name">
                    <div class="form-group mt-3 d-none update_add_team_member_choice_div">
                        <label> Team Member Choice</label>
                        <select class="form-control" name="update_add_team_member_choice">
                            <option value="">Member Choice...</option>
                            <option value="Is Primary">Is Primary</option>
                            <option value="Is Admin">Is Admin</option>
                            <option value="Is First Primary">Is First Primary</option>
                            <option value="Level">Level</option>
                        </select>
                    </div>
                    <div class="form-group mt-3 d-none update_add_team_member_choice_level_div">
                        <label> Team Member Choice Level</label>
                        <select class="form-control" name="update_add_team_member_choice_level">
                            <option value="">Member Choice Level...</option>
                            <option value="Follower">Follower</option>
                            <option value="Collaborator">Collaborator</option>
                            <option value="Guest">Guest</option>
                        </select>
                    </div>


                    <div class="form-group mt-3 d-none update_fv_project_note_with_pin_div">
                        <label>With Pin</label>
                        <div class="checkbox-inline">
                            <label class="checkbox checkbox-outline checkbox-outline-2x checkbox-primary checkbox-lg">
                                <input type="checkbox" name="update_fv_project_note_with_pin">
                                <span></span>
                            </label>
                        </div>
                    </div>
                    <div class="form-group d-none update_fv_project_task_assign_type_div">
                        <label>Assign By</label>
                        <select class="form-control" name="update_fv_project_task_assign_type">
                            <option value="">Select Assign Type</option>
                            <option value="user">Assign to User</option>
                            {{-- <option value="role">Assign to Role</option> --}}
                        </select>
                    </div>
                    <div class="form-group mt-3 d-none update_fv_project_task_assign_user_div">
                        <label> Assign User</label>
                        <select class="form-control" name="update_fv_project_task_assign_user_id">
                            <option value="">Select User</option>
                        </select>
                    </div>
                    <input type="hidden" name="update_fv_project_task_assign_user_name">
                    <div class="form-group d-none update_fv_project_task_assign_user_role_div">
                        <label>Assign Role</label>
                        <select class="form-control" name="update_fv_project_task_assign_user_role">
                            <option value="">Select Role</option>
                        </select>
                    </div>
                    <input type="hidden" name="update_fv_project_task_assign_user_role_name">
                    <div class="form-group mt-3 d-none update_fv_project_hashtag_div">
                        <label>Hashtag (do not include "#")</label>
                        <input name="update_fv_project_hashtag" type="text" class="form-control">
                    </div>
                    <div class="form-group d-none update_section_visibility_project_type_div">
                        <label> Project Type</label>
                        <select class="form-control" name="update_section_visibility_project_type_id">
                            <option value="">Select Project Type</option>
                        </select>
                    </div>
                    <div class="form-group d-none update_section_visibility_section_selector_div">
                        <label> Section Selector</label>
                        <select class="form-control" name="update_section_visibility_section_selector">
                            <option value="">Select Section Selector</option>
                        </select>
                    </div>
                    <div class="form-group d-none update_section_visibility_div">
                        <label> Section Visibility</label>
                        <select class="form-control" name="update_section_visibility">
                            <option value="">Select Section Visibility</option>
                            <option value="Visible">Visible</option>
                            <option value="Hidden">Hidden</option>
                        </select>
                    </div>
                    <div class="form-group d-none update_phase_assignment_div">
                        <label>Phase Assignment</label>
                        <select class="form-control" name="update_phase_assignment">
                            <option value="">Select Phase Assignment</option>
                            <option value="Next_Sequential_Phase">Next Sequential Phase</option>
                            <option value="Specific_Phase">Specific Phase</option>
                        </select>
                    </div>
                    <div class="form-group d-none update_phase_assignment_project_type_div">
                        <label> Project Type</label>
                        <select class="form-control" name="update_phase_assignment_project_type_id">
                            <option value="">Select Project Type</option>
                        </select>
                    </div>
                    <input type="hidden" name="update_phase_assignment_project_type_name">
                    <div class="form-group d-none update_project_phase_id_native_div">
                        <label> Project Phase</label>
                        <select class="form-control" name="update_project_phase_id_native">
                            <option value="">Select Project Phase</option>
                        </select>
                    </div>
                    <input type="hidden" name="update_project_phase_id_native_name">
                    <div class="form-group d-none update_delivery_hook_url_div">
                        <label>Delivery Hook URL</label>
                        <input type="url" name="update_delivery_hook_url" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-light-success font-weight-bold">Submit</button>
                    <button type="button" class="btn btn-light-primary font-weight-bold" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Map Action Info Modal-->
<div class="modal fade" id="actionMapModal" tabindex="-1" role="dialog" aria-labelledby="actionMapModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('automated_workflow_map_update', ['subdomain' => $subdomain]) }}" method="post">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="actionMapModalLabel">Update Action Map Information<span class="action_map_edit_id"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i aria-hidden="true" class="ki ki-close"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <input name="update_map_id" type="hidden">
                    <div class="form-group">
                        <label>Trigger Name</label>
                        <input name="update_trigger_name" type="text" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label>Action Name</label>
                        <input name="update_action_name" type="text" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label>Workflow Description</label>
                        <textarea name="update_workflow_description" class="form-control" rows="4"></textarea>
                    </div>
                    <div class="form-group mt-3">
                        <label> Choose Status</label>
                        <select class="form-control" name="update_map_status" required>
                            <option value="">Select Status</option>
                            <option value="test">Test</option>
                            <option value="pause">Pause</option>
                            <option value="live">Live</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-light-success font-weight-bold">Submit</button>
                    <button type="button" class="btn btn-light-primary font-weight-bold" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>


<!-- Trigger Info Edit Modal-->
<div class="modal fade" id="triggerEditModal" tabindex="-1" role="dialog" aria-labelledby="triggerEditModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="triggerEditModalLabel">Edit Trigger Item <span class="trigger_edit_id"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i aria-hidden="true" class="ki ki-close"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Primary Trigger</label>
                    <input type="text" name="edit_primary_trigger" class="form-control" placeholder="Primary Trigger" readonly>
                </div>
                <div class="form-group mt-3 d-none edit_trigger_event_div">
                    <label>Trigger Event</label>
                    <input type="text" name="edit_trigger_event" class="form-control" placeholder="Trigger Event" readonly>
                </div>
                <div class="form-group mt-3 d-none edit_filter_selection_div">
                    <label>Filter</label>
                    <div class="checkbox-inline">
                        <label class="checkbox checkbox-outline checkbox-outline-2x checkbox-primary checkbox-lg">
                            <input type="checkbox" name="edit_filter_selection">
                            <span></span>
                        </label>
                    </div>
                </div>
                <div class="form-group mt-3 d-none edit_project_type_div">
                    <label> Choose Project Type</label>
                    <select class="form-control" name="edit_project_type_id">
                        <option value="">Project Type...</option>
                    </select>
                </div>
                <div class="form-group mt-3 d-none edit_phase_name_div">
                    <label> Phase Changed To</label>
                    <select class="form-control" name="edit_phase_name_id">
                        <option value="">Phase...</option>
                    </select>
                </div>
                <div class="form-group mt-3 d-none edit_filter_contact_by_div">
                    <label> Filter Contacts By</label>
                    <select class="form-control" name="edit_filter_contact_by">
                        <option value="">Select Contact By</option>
                    </select>
                </div>
                <div class="form-group mt-3 d-none edit_person_type_selection_div">
                    <label> Person Type Selection</label>
                    <select class="form-control" name="edit_person_type_selection_id">
                        <option value="">Person Type</option>
                    </select>
                </div>
                <div class="form-group mt-3 d-none edit_project_section_div">
                    <label class="edit_project_section_label"> Choose Section</label>
                    <select class="form-control" name="edit_project_section_selector">
                        <option value="">Choose Section</option>
                    </select>
                </div>
                <div class="form-group mt-3 d-none edit_project_section_field_div">
                    <label> Choose Field</label>
                    <select class="form-control" name="edit_project_section_field_selector">
                        <option value="">Choose Field</option>
                    </select>
                </div>
                <div class="form-group mt-3 d-none edit_filter_task_by_div">
                    <label> Filter Tasks By</label>
                    <select class="form-control" name="edit_filter_task_by">
                        <option value="">Select Task By</option>
                    </select>
                </div>
                <div class="form-group mt-3 d-none edit_filter_appointment_by_div">
                    <label> Filter Appointments By</label>
                    <select class="form-control" name="edit_filter_appointment_by">
                        <option value="">Select Appointment</option>
                    </select>
                </div>
                <div class="form-group mt-3 d-none edit_org_user_div">
                    <label> Select Org User</label>
                    <select class="form-control" name="edit_org_user_id">
                        <option value="">User...</option>
                    </select>
                </div>
                <div class="form-group mt-3 d-none edit_project_hashtag_div">
                    <label class="hastag_label"> Project Hashtag</label>
                    <input type="text" name="edit_project_hashtag" class="form-control" placeholder="sample">
                </div>


                <div class="form-group mt-3 d-none edit_tenant_form_div">
                    <label> Form Submitted</label>
                    <select class="form-control" name="edit_tenant_form_id">
                        <option value="">Choose Form</option>
                        @foreach ($tenant_forms as $tenant_form)
                        <option value="{{ $tenant_form->id }}">{{ $tenant_form->form_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group d-none edit_client_file_upload_configuration_div">
                    <label> Select File Upload Scheme</label>
                    <select class="form-control" name="edit_client_file_upload_configuration_id">
                        <option value="">Choose Upload Scheme</option>
                        @foreach ($client_file_upload_configurations as $client_file_upload_configuration)
                        <option value="{{ $client_file_upload_configuration->id }}">
                            {{ $client_file_upload_configuration->choice }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group d-none edit_sms_line_div">
                    <label> Select SMS Line</label>
                    <select class="form-control" name="edit_sms_line">
                        <option value="">Choose SMS Line</option>
                        <option value="Phase Change">Phase Change</option>
                        <option value="Review Request">Review Request</option>
                        <option value="Mass Message">Mass Message</option>
                        <option value="2FA Verification">2FA Verification</option>
                    </select>
                </div>

                <div class="form-group mt-3">
                    <label> Name of the Trigger</label>
                    <input type="text" name="edit_trigger_name" class="form-control" placeholder="Primary Trigger-Event">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-light-success font-weight-bold edit-trigger-save">Submit</button>
                <button type="button" class="btn btn-light-primary font-weight-bold" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

</div>
</div>

<style>
    .overlay {
        position: fixed;
        top: 0;
        left: 0;
        z-index: 100;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, .7);
        transition: .3s linear;
        z-index: 1000;
    }

    .loading {
        display: none;
    }

    .spinner-border.loading {
        position: fixed;
        top: 48%;
        left: 48%;
        z-index: 1001;
        width: 5rem;
        height: 5rem;
    }

    .nav-link:hover,
    .nav-link.active {
        background: #26A9DF !important;
        color: #fff !important;
    }

    .nav .nav-link:hover:not(.disabled) .nav-text {
        color: #fff !important;
    }

    .btn i {
        padding-right: 0px !important;
    }

    .alert.alert-custom {
        padding: 0rem 2rem;
    }
</style>
@php
$success = '';
$error = '';
if (session()->has('success')) {
$success = session()->get('success');
}
if (session()->has('error')) {
$error = session()->get('error');
}
@endphp
@stop

@section('scripts')
<script src="{{ asset('../js/admin/automated_workflow.js?20231007') }}"></script>
<script src="{{ asset('../js/admin/automated_workflow_edit.js') }}"></script>
<script>
    var success = "{{ $success }}";
    var error = "{{ $error }}";
    if (success != "") {
        Swal.fire({
            text: success,
            icon: "success",
        });
    }
    if (error != "") {
        Swal.fire({
            text: error,
            icon: "error",
        });
    }
</script>
<script>
    var trigger_action_rules = @json($trigger_action_rules);

    $("body").on("change", "select[name='map_trigger_id']", async function() {
        let this_map_trigger_id = parseInt($(this).val());
        $("select[name='map_action_id'] option[class='action-option']").hide();
        if (this_map_trigger_id in trigger_action_rules) {
            let temp_actions = trigger_action_rules[this_map_trigger_id];
            for (let i = 0; i < temp_actions.length; i++) {
                $("select[name='map_action_id'] option[value=" + temp_actions[i] + "]").show();
            }
        }

    });
</script>
@endsection