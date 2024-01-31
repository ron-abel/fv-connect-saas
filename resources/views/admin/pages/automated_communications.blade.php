@extends('admin.layouts.default')

@section('title', 'VineConnect - Admin - Automated Communications Configurations')

@section('content')
    <style>
        /* for sm */
        .custom-switch.custom-switch-sm .custom-control-label {
            padding-left: 1rem;
            padding-bottom: 1rem;
        }

        .custom-switch.custom-switch-sm .custom-control-label::before {
            height: 1rem;
            width: calc(1rem + 0.75rem);
            border-radius: 2rem;
        }

        .custom-switch.custom-switch-sm .custom-control-label::after {
            width: calc(1rem - 4px);
            height: calc(1rem - 4px);
            border-radius: calc(1rem - (1rem / 2));
        }

        .custom-switch.custom-switch-sm .custom-control-input:checked~.custom-control-label::after {
            transform: translateX(calc(1rem - 0.25rem));
        }

        /* for md */

        .custom-switch.custom-switch-md .custom-control-label {
            padding-left: 2rem;
            padding-bottom: 1.5rem;
        }

        .custom-switch.custom-switch-md .custom-control-label::before {
            height: 1.5rem;
            width: calc(2rem + 0.75rem);
            border-radius: 3rem;
        }

        .custom-switch.custom-switch-md .custom-control-label::after {
            width: calc(1.5rem - 4px);
            height: calc(1.5rem - 4px);
            border-radius: calc(2rem - (1.5rem / 2));
        }

        .custom-switch.custom-switch-md .custom-control-input:checked~.custom-control-label::after {
            transform: translateX(calc(1.5rem - 0.25rem));
        }

        /* for lg */

        .custom-switch.custom-switch-lg .custom-control-label {
            padding-left: 3rem;
            padding-bottom: 2rem;
        }

        .custom-switch.custom-switch-lg .custom-control-label::before {
            height: 2rem;
            width: calc(3rem + 0.75rem);
            border-radius: 4rem;
        }

        .custom-switch.custom-switch-lg .custom-control-label::after {
            width: calc(2rem - 4px);
            height: calc(2rem - 4px);
            border-radius: calc(3rem - (2rem / 2));
        }

        .custom-switch.custom-switch-lg .custom-control-input:checked~.custom-control-label::after {
            transform: translateX(calc(2rem - 0.25rem));
        }

        /* for xl */

        .custom-switch.custom-switch-xl .custom-control-label {
            padding-left: 4rem;
            padding-bottom: 2.5rem;
        }

        .custom-switch.custom-switch-xl .custom-control-label::before {
            height: 2.5rem;
            width: calc(4rem + 0.75rem);
            border-radius: 5rem;
        }

        .custom-switch.custom-switch-xl .custom-control-label::after {
            width: calc(2.5rem - 4px);
            height: calc(2.5rem - 4px);
            border-radius: calc(4rem - (2.5rem / 2));
        }

        .custom-switch.custom-switch-xl .custom-control-input:checked~.custom-control-label::after {
            transform: translateX(calc(2.5rem - 0.25rem));
        }

        .d-none {
            display: none;
        }

        .btn-info {
            color: #181C32;
            background-color: #FFA800 !important;
            border-color: #FFA800 !important;
            -webkit-box-shadow: none;
            box-shadow: none;
        }
    </style>

    <!--begin::Subheader-->
    <div class="subheader py-2 py-lg-4 subheader-solid" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
            <!--begin::Info-->
            <div class="d-flex align-items-center flex-wrap mr-2">
                <!--begin::Page Title-->
                <h5 class="text-dark font-weight-bold mt-2 mb-2 mr-5">Automated Communications - Phase Change SMS</h5>
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
            @if (session()->has('error'))
                <div class="alert alert-danger" role="alert">
                    {{ session()->get('error') }}
                </div>
            @elseif(session()->has('success'))
                <div class="alert alert-primary" role="alert">
                    {{ session()->get('success') }}
                </div>
            @endif
            @error('sms_buffer_time')
                <span class="alert alert-danger">{{ $message }}</span>
            @enderror
            <!--begin::Row-->
            <div class="row">
                <div class="col-md-12">
                    <!--begin::Card-->
                    <div class="card card-custom gutter-b example example-compact">
                        <div class="card-header">
                            <h5 class="card-title mt-7">Send Text Notifications to the Client on Project Phase
                                Change</h5>
                        </div>
                        <div class="card-body">
                            <div class="pg_content">
                                <p><b>Instructions:</b> For each phase change that occurs in Filevine, you can enable a
                                    text message notification to be delivered to the client associated with the project.
                                </p>
                                <p><b>When you're finished with your configurations, toggle the GO LIVE button. To make
                                        changes, toggle the TEST button.</b> To turn SMS text messages off completely,
                                    toggle the OFF button. The SMS Time Buffer places a "delay" on sending the text
                                    message notification in seconds. At expiration of the delay, only if the phase still
                                    matches for the project is the notification sent. You can override this feature
                                    entirely by setting the buffer to "0".  If the Review is checked, this phase change
                                    will trigger an automated review request SMS to be sent to the client.</p>
                                {{-- <p>If the <b>Review</b> is checked, this phase change will trigger an automated review request SMS to be sent after the phase change SMS. That flow is configured on the <a href="/admin/google_review_automated_communications">Review Requests</a> admin page. Be sure to map each Phase for every Project Type Template you have active in your Filevine Org.</p> --}}
                                <div class="clear"></div>
                                <div class="callout_subtle lightgrey"><i class="fas fa-link"
                                        style="color:#383838;padding-right:5px;"></i>
                                    Support Article: <a
                                        href="https://intercom.help/vinetegrate/en/articles/5815370-phase-change-sms-notifications"
                                        target="_blank" />Phase Change SMS Notifications</a></div>
                                <div class="callout_subtle lightgrey"><i class="fa fa-key mr-3"></i><a
                                        href="{{ url('admin/variables') }}" target="_blank" />&nbsp;List of Variables</a>
                                </div>
                            </div>
                            <div class="container-fluid">
                                <div class="card card-body">
                                    <div class="row">
                                        <div class="col-4">
                                            @if ($auto_note_details)
                                                <div class="form-group txt-btn">
                                                    <input type="radio" id="on_btn"
                                                        onchange="activate_communication(this.value, 'is_on')"
                                                        {{ $auto_note_details->is_on == 1 ? 'checked' : '' }}
                                                        name="automate_connection_status" value="on" />
                                                    <label for="on_btn" type="button" class="btn">ON</label>
                                                    <input type="radio" id="off_btn"
                                                        onchange="activate_communication(this.value, 'is_on')"
                                                        {{ $auto_note_details->is_on == 0 ? 'checked' : '' }}
                                                        name="automate_connection_status" value="off" />
                                                    <label for="off_btn" type="button" class="btn btn-danger">OFF</label>
                                                </div>
                                                <div class="form-group txt-btn">
                                                    <input type="radio" id="go_live_btn"
                                                        onchange="activate_communication(this.value, 'is_live')"
                                                        {{ $auto_note_details->is_live == 1 ? 'checked' : '' }}
                                                        name="automate_connection_live" value="go_live" />
                                                    <label for="go_live_btn" type="button" class="btn btn-success">GO
                                                        LIVE</label>
                                                    <input type="radio" id="pause_btn"
                                                        onchange="activate_communication(this.value, 'is_live')"
                                                        {{ $auto_note_details->is_live == 0 ? 'checked' : '' }}
                                                        name="automate_connection_live" value="pause" />
                                                    <label for="pause_btn" type="button"
                                                        class="btn btn-danger">TEST</label>
                                                </div>
                                            @else
                                                <div class="form-group txt-btn">
                                                    <input type="radio" id="on_btn"
                                                        onchange="activate_communication(this.value, 'is_on')"
                                                        name="automate_connection_status" value="on" />
                                                    <label for="on_btn" type="button" class="btn">ON</label>
                                                    <input type="radio" id="off_btn"
                                                        onchange="activate_communication(this.value, 'is_on')" checked
                                                        name="automate_connection_status" value="off" />
                                                    <label for="off_btn" type="button" class="btn">OFF</label>
                                                </div>
                                                <div class="form-group txt-btn go_live">
                                                    <input type="radio" id="go_live_btn"
                                                        onchange="activate_communication(this.value, 'is_live')"
                                                        name="automate_connection_live" value="go_live" />
                                                    <label for="go_live_btn" type="button" class="btn">GO LIVE</label>
                                                    <input type="radio" id="pause_btn"
                                                        onchange="activate_communication(this.value, 'is_live')" checked
                                                        name="automate_connection_live" value="pause" />
                                                    <label for="pause_btn" type="button" class="btn">TEST</label>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="col-8">
                                            <div class="sms_buffer_time">
                                                <div class="row">
                                                    <form action="{{ url('/admin/save_sms_time_buffer') }}" method="post"
                                                        class="form-inline">
                                                        {{ csrf_field() }}
                                                        <div class="row">
                                                            <div class="col-md-5 mt-5 switch-div">
                                                                <div
                                                                    class="custom-control custom-switch custom-switch-md pl-0">
                                                                    <input type="radio"
                                                                        {{ isset($config_details->is_sms_buffer_time_enabled) && $config_details->is_sms_buffer_time_enabled == 1 ? 'checked' : '' }}
                                                                        name="enable_sms_buffer_time" value="enable"
                                                                        class="custom-control-input"
                                                                        id="enable_sms_buffer_time">
                                                                    <label class="custom-control-label ml-7 pl-4"
                                                                        for="enable_sms_buffer_time">Enable SMS Time
                                                                        Buffer</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-12">
                                                                <div class="form-group d-inline-flex" style="">
                                                                    <label for="sms_buffer_time" class="mr-3">SMS Time
                                                                        Buffer (in seconds)</label>
                                                                    <input type="number" class="form-control"
                                                                        id="sms_buffer_time" min="300"
                                                                        max="86400" name="sms_buffer_time"
                                                                        {{ isset($config_details->is_sms_buffer_time_enabled) && $config_details->is_sms_buffer_time_enabled == 1 ? '' : 'disabled' }}
                                                                        value="{{ $config_details->sms_buffer_time ?? env('SMS_NOTE_BUFFER_DEFAULT_TIME', '300') }}" />
                                                                </div>
                                                                <button type="submit" class="btn btn-primary ml-3">Save
                                                                </button>
                                                                <br />
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="phase_change_submit" class="row">
                                <div class="col-sm-12 mt-3" id="divToAppendTAb">
                                    @if (count($mappings) > 0)
                                        @foreach ($mappings as $project_type)
                                            <span
                                                class='btn span_name span_tabs {{ $project_type->project_type_id == $current_project_typeid ? 'active_tab' : '' }}'
                                                data-id='{{ $project_type->project_type_id }}'
                                                onclick='getPhasesProjectType("{{ $project_type->project_type_id }}", "{{ $project_type->project_type_name }}")'>
                                                {{ $project_type->project_type_name }}
                                            </span>
                                        @endforeach
                                    @endif
                                </div>
                                <div id="main-section" class="col-sm-12">
                                    @php $all_selected_cat_ids=[]; @endphp
                                    @foreach ($auto_note_phases as $single_note_phase)
                                        <div class="row ml-3 mr-0 mt-3 dv-webhook-row"
                                            id="id_webhook_{{ $single_note_phase->id }}"
                                            phaseId="{{ $single_note_phase->fv_phase_id }}">
                                            <div class="col-sm-12 px-1">
                                                <div class="form-group mt-10">
                                                    <div class="row mx-0 phase_form table_row"
                                                        fv_phase_id="{{ $single_note_phase->fv_phase_id }}">
                                                        <input type="hidden" name="project_type_id" id=""
                                                            class="form-control project_type"
                                                            value="{{ $single_note_phase->fv_project_type_id }}">
                                                        <div class="col-sm-2 px-1 ml-5 custom-input">
                                                            <label for="">Phase Change</label>
                                                            <select name="fv_phase_id" onchange="changeHiddenPhase(this)"
                                                                class="form-control fv_phase_id" required>
                                                                <option value="" selected="selected">--Select
                                                                    Phase--
                                                                </option>
                                                                @if (count($project_type_phases) > 0)
                                                                    @foreach ($project_type_phases as $phase)
                                                                        @php
                                                                            if (in_array($phase['name'], $auto_note_phases_Array) && $single_note_phase->phase_name != $phase['name']) {
                                                                                continue;
                                                                            }
                                                                            $selected = '';
                                                                            if ($single_note_phase->phase_name == $phase['name']) {
                                                                                $selected = 'selected';
                                                                            }
                                                                        @endphp
                                                                        <option {{ $selected }}
                                                                            value="{{ $phase['phaseId']['native'] }}">
                                                                            {{ $phase['name'] }}
                                                                        </option>
                                                                    @endforeach
                                                                @endif
                                                            </select>
                                                            <input name="phase_change_event" type="hidden"
                                                                value="{{ $single_note_phase->phase_name }}"
                                                                class="form-control phase_change_event">
                                                        </div>
                                                        <div class="col-sm-2 px-1 ml-5 custom-input">
                                                            <label for="">Enable/Disable</label>
                                                            <select name="phase_change_enable" id=""
                                                                value="" class="form-control phase_change_enable">
                                                                <option
                                                                    @if ($single_note_phase->is_active == 1) selected="selected" @endif
                                                                    value="1">Enable
                                                                </option>
                                                                <option
                                                                    @if ($single_note_phase->is_active == 0) selected="selected" @endif
                                                                    value="0">Disable
                                                                </option>
                                                            </select>
                                                        </div>
                                                        <div class="col-sm-3 px-1 ml-5">
                                                            <label for=""
                                                                style="display:{{ $single_note_phase->is_active == 0 ? 'none' : 'block' }}">Text
                                                                Message</label>
                                                            <textarea style="display:{{ $single_note_phase->is_active == 0 ? 'none' : 'block' }}" class="form-control"
                                                                name="custom_message" id="txtDescription_0" cols="60" rows="5" required="" spellcheck="true">{{ $single_note_phase->custom_message }}</textarea>
                                                        </div>
                                                        <div class="col-sm-1 px-1 ml-5">
                                                            <label for="">Review? <span
                                                                    class="fas fa-exclamation-circle"
                                                                    data-toggle="tooltip" title=""
                                                                    data-original-title="Check to send Google Review on this Phase Change."></span></label>
                                                            <input type="checkbox" class="form-control goog-check"
                                                                @if ($single_note_phase->is_send_google_review == 1) checked @endif
                                                                name="is_send_google_review">
                                                        </div>
                                                        <div class="col-sm-1 px-1 mt-6 ml-5">
                                                            <button type="submit"
                                                                class="btn ml-auto mt-1 btn-success btn-md save"
                                                                data-id="{{ $single_note_phase->id }}"
                                                                style="float:left;">Save
                                                            </button>
                                                        </div>
                                                        <div class="col-sm-1 px-1 mt-6">
                                                            <button class="btn ml-auto mt-1 btn-danger btn-md delete"
                                                                data-id="{{ $single_note_phase->id }}"
                                                                style="float:left;"><span
                                                                    class="fa fa-trash"></span></button>
                                                        </div>

                                                        <div class="process-result col-sm-3 px-1 mt-10"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @php  $all_selected_cat_ids[] = $single_note_phase->fv_phase_id; @endphp
                                    @endforeach
                                    <input type='hidden' name='AllSelectedCatIds' id='AllSelectedCatIds'
                                        value="{{ implode(',', $all_selected_cat_ids) }}">

                                </div>

                                <div id="id_new_webhook_row" class="">
                                </div>
                                <div class="row" id="id_dv_create_new">
                                    <div class="col-sm-12 ml-5 mr-0 mt-4">
                                        <button class="btn btn-md btn-success ml-auto mt-1" onclick="addAll()">Add All
                                            Phases
                                        </button>
                                        <button class="btn btn-md btn-success ml-auto mt-1" onclick="addDynamicRow()">
                                            Add New Row
                                        </button>
                                        <button class="btn btn-md btn-success ml-auto mt-1" onclick="saveAll()">Save
                                            All
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Card-->
                </div>

            </div>
        </div>
    </div>
    <style>
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            z-index: 2;
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
    </style>
    <?php
    $html = '<select name="fv_phase_id" onchange="changeHiddenPhase(this)" class="form-control fv_phase_id" required>';
    $html .= '<option value="" selected="selected">--Select Phase--</option>';
    foreach ($project_type_phases as $phase) {
        $html .= '<option  value="' . $phase['phaseId']['native'] . '">' . $phase['name'] . '</option>';
    }
    $html .= '</select>';
    $projectType = [];
    $projectTypeHtml = '<option value="0" selected="selected">--Select Phase--</option>';
    foreach ($mappings as $mapping) {
        $projectTypeHtml .= '<option  value="' . $mapping->project_type_id . '">' . $mapping->project_type_name . '</option>';
        $projectType[] = ['type_id' => $mapping->project_type_id];
    }

    ?>

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
    <script>
        var projectType = <?php echo json_encode($projectType); ?>;
        var phaseHtml = `<?php echo $html; ?>`;
        var projectTypeHtml = `<?php echo $projectTypeHtml; ?>`;
        var current_project_id = `<?php echo $current_project_typeid; ?>`;

        function changeHiddenPhase(obj) {
            let value = $(obj).val();
            let text = $(obj).find("option:selected").text();
            $(obj).parent().find(".phase_change_event").val(text.trim());
        }
    </script>
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
@endsection
