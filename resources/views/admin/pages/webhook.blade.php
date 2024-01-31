@extends('admin.layouts.default')

@section('title', 'VineConnect Admin - Configure Outbound Webhooks')

@section('content')

<!--begin::Subheader-->
<div class="subheader py-2 py-lg-4 subheader-solid" id="kt_subheader">
    <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
        <!--begin::Info-->
        <div class="d-flex align-items-center flex-wrap mr-2">
            <!--begin::Page Title-->
            <h4 class="text-dark font-weight-bold mt-2 mb-2 mr-5">Configure Filevine Events as Outbound Webhooks</h4>
            <!--end::Page Title-->

        </div>
        <!--end::Info-->
    </div>
</div>
<!--end::Subheader-->
<div class="d-flex flex-column-fluid">
    <!--begin::Container-->
    <div class="container">
        <!--begin::Row-->
        <div class="row">
            <div class="col-md-12">
                <!--begin::Card-->
                <div class="card card-custom gutter-b example example-compact">
                    <div class="card-header">
                        <h4 class="card-title mt-7">Filter Events and Send Meaningful Data Payloads to Zapier</h5>
                            <div class="pg_content">
                                <p>This tool delivers contextually meaningful data to a webhook or URL endpoing that you specify, such as a Zapier receiving webhook. Data delivered includes project vitals, project phase, and client contact information. A common service for receiving webhook data and integrating data across multiple platforms is Zapier. The destination URL for your data doesn't have to Zapier; any endpoint that is capable of processing JSON data can be configured.</p>
								<p>Start by choosing a <b>Trigger Action</b> provided by VineConnect, the provide any additional configurations required. This is a great way to automate workflows and Filevine data based on actions that occur in your Filevine projects!</p>
                                <p><b>Note:</b> The inbound webhook URL provided below will not be confirmed until you send your first test to VineConnect. Choose your trigger action, save, then send a test from your Filevine Org to confirm the receieving endpoint. You'll need to have your Filevine API Key and Key Secret active in the Configurations section to receive your test data.</p>
                            </div>
                    </div>
                    <div class="card-body">
                        <!-- Container fluid  -->
                        <div class="container-fluid">
                            <div class="row card card-body">
                                <div class="dv-webhooks">
                                    <div class="row ml-3 mr-0" id="divToAppendRow">
                                        <div class="col-sm-3 px-1">
                                            <label for="webhook_action">Choose Trigger Action</label>
                                            <select name="webhook_action" class="form-control webhook_action" id="id_sel_webhook_action">
                                                <option value="">Select Action</option>
                                                <option value="PhaseChanged">On Phase Change</option>
                                                <option value="ContactCreated">When Contact Created</option>
                                                <option value="ProjectCreated">When Project Created</option>
                                                <!-- <option value="CollectionItemCreated">When Collection Item Created</option>
                                                <option value="TaskCreated">When Task Created</option> -->
                                            </select>
                                        </div>

                                        <div class="col-sm-6">
                                            <label for="webhook_action">Vineconnect Inbound URL</label>
                                            <input class="form-control" id="id_webhook_inbound_url" value="">
                                        </div>
                                        <div class="col-sm-3 px-1 mt-11">
                                            <p id="is_confirmed_webhook" class="text-success" style="height: 23px; display:none;"><i class="fas fa-check-circle"></i> Webhook URL Confirmed</p>
                                            <p id="is_not_confirmed_webhook" class="text-danger" style="height: 23px; display:none;"><i class="fas fa-exclamation-circle"></i> Webhook URL Not Confirmed</p>
                                            <p id="is_created_webhook" class="text-success" style="height: 23px; display:none;"><i class="fas fa-check-circle"></i> Webhook Created Successfully</p>
                                        </div>
                                    </div>
                                    <hr />
                                    <div id="main-section">
                                        @foreach($webhooks as $webhook_obj)
                                        @php $webhook_settings_count_parse[$webhook_obj->trigger_action_name] = 1; @endphp
                                        <div class="row ml-3 mr-0 mt-3 dv-webhook-row {{ $webhook_obj->trigger_action_name }}" id="id_webhook_{{$webhook_obj->id }}">
                                            <div class="col-sm-12 px-1">
                                                @if($webhook_obj->trigger_action_name == 'PhaseChanged')
                                                <!-- Phase Changed row -->
                                                <div class="row mx-0 phase_form">
                                                    <div class="col-sm-2 px-1">
                                                        <label for="">If Phase Change</label>
                                                        <select name="item_change_type" id="" value="{{ $webhook_obj->item_change_type }}" class="form-control phase_form_select" data-id="phase_form">
                                                            <option value="Equals Exactly" @if($webhook_obj->item_change_type == "Equals Exactly") selected="selected"
                                                                @endif >Equals Exactly</option>
                                                            <!--<option value="Does Not Equal" @if($webhook_obj->item_change_type == "Does Not Equal") selected="selected" @endif >Does Not Equal</option>
                                                                <option value="Is Not" @if($webhook_obj->item_change_type == "Is Not") selected="selected" @endif >Is Not</option>
                                                                <option value="Contains" @if($webhook_obj->item_change_type == "Contains") selected="selected" @endif >Contains</option>-->
                                                        </select>
                                                    </div>
                                                    <div class="col-sm-3 px-1">
                                                        <label for="">Phase Changed To</label>
                                                        <select name="fv_phase_id" onchange="changeHiddenPhase(this)" class="form-control" required }}>
                                                            <option value="" selected="selected">--Select Phase--</option>
                                                            @if(count($project_type_phases) > 0)
                                                                @foreach ($project_type_phases as $phase)
                                                                @php
                                                                    $selected = "";
                                                                    if($webhook_obj->phase_change_event == $phase['name']) $selected="selected";
                                                                @endphp
                                                                <option {{$selected}} value="{{ $phase['phaseId']['native'] }}">
                                                                    {{ $phase['name'] }}
                                                                </option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                        <input type="hidden" class="form-control phase_change_event" name="phase_change_event" value="{{ $webhook_obj->phase_change_event }}" />
                                                    </div>
                                                    <div class="col-sm-4 px-1">
                                                        <label for="">Deliver to Webhook URL Endpoint</label>
                                                        <input type="text" class="form-control" name="delivery_hook_url" value="{{ $webhook_obj->delivery_hook_url }}" />
                                                    </div>
                                                    <div class="col-sm-1 px-1 mt-6">
                                                        <button type="submit" class="btn ml-auto mt-1 btn-success btn-md save" data-id="{{ $webhook_obj->id }}" style="float:left;">Save</button>
                                                    </div>
                                                    <div class="col-sm-1 px-1 mt-6">
                                                        <button class="btn ml-auto mt-1 btn-danger btn-md delete" data-id="{{ $webhook_obj->id }}" style="float:left;"><span class="fa fa-trash"></span></button>
                                                    </div>
                                                    <div class="process-result col-sm-3 px-1 mt-10"></div>
                                                </div>

                                                @elseif($webhook_obj->trigger_action_name == 'CollectionItemCreated')

                                                <div class="row mx-0 phase_form">
                                                    <div class="col-sm-2 px-1">
                                                        <label for="">If Collection</label>
                                                        <select name="item_change_type" id="" value="{{ $webhook_obj->item_change_type }}" class="form-control phase_form_select" data-id="phase_form">
                                                            <option value="Equals Exactly" @if($webhook_obj->item_change_type == "Equals Exactly") selected="selected"
                                                                @endif >Equals Exactly</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-sm-3 px-1">
                                                        <label for="">Collection Item Created To</label>
                                                        <input type="text" class="form-control" name="collection_changed" value="{{ $webhook_obj->collection_changed }}" />
                                                    </div>
                                                    <div class="col-sm-4 px-1">
                                                        <label for="">Deliver to Webhook URL Endpoint</label>
                                                        <input type="text" class="form-control" name="delivery_hook_url" value="{{ $webhook_obj->delivery_hook_url }}" />
                                                    </div>
                                                    <div class="col-sm-1 px-1 mt-6">
                                                        <button type="submit" class="btn ml-auto mt-1 btn-success btn-md save" data-id="{{ $webhook_obj->id }}" style="float:left;">Save</button>
                                                    </div>
                                                    <div class="col-sm-1 px-1 mt-6">
                                                        <button class="btn ml-auto mt-1 btn-danger btn-md delete" data-id="{{ $webhook_obj->id }}" style="float:left;"><span class="fa fa-trash"></span></button>
                                                    </div>
                                                    <div class="process-result col-sm-3 px-1 mt-10"></div>
                                                </div>

                                                @elseif($webhook_obj->trigger_action_name == 'TaskCreated')

                                                    <div class="row mx-0 phase_form">
                                                        <div class="col-sm-2 px-1">
                                                            <label for="">If Task</label>
                                                            <select name="item_change_type" id="" value="{{ $webhook_obj->item_change_type }}" class="form-control phase_form_select" data-id="phase_form">
                                                                <option value="Contains" @if($webhook_obj->item_change_type == "Contains") selected="selected"
                                                                    @endif >Contains</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-sm-3 px-1">
                                                            <label for="">Task Created To</label>
                                                            <input type="text" class="form-control" name="task_changed" value="{{ $webhook_obj->task_changed }}" />
                                                        </div>
                                                        <div class="col-sm-4 px-1">
                                                            <label for="">Deliver to Webhook URL Endpoint</label>
                                                            <input type="text" class="form-control" name="delivery_hook_url" value="{{ $webhook_obj->delivery_hook_url }}" />
                                                        </div>
                                                        <div class="col-sm-1 px-1 mt-6">
                                                            <button type="submit" class="btn ml-auto mt-1 btn-success btn-md save" data-id="{{ $webhook_obj->id }}" style="float:left;">Save</button>
                                                        </div>
                                                        <div class="col-sm-1 px-1 mt-6">
                                                            <button class="btn ml-auto mt-1 btn-danger btn-md delete" data-id="{{ $webhook_obj->id }}" style="float:left;"><span class="fa fa-trash"></span></button>
                                                        </div>
                                                        <div class="process-result col-sm-3 px-1 mt-10"></div>
                                                    </div>

                                                @else

                                                    <div class="row mx-0 form-group">
                                                        <div class="col-sm-6 px-1 others_form">
                                                            <label for="">Deliver to Webhook URL Endpoint</label>
                                                            <input type="text" name="delivery_hook_url" class="form-control" value="{{ $webhook_obj->delivery_hook_url }}" />
                                                        </div>
                                                        <div class="col-sm-1 px-1 mt-3">
                                                            <button type="submit" class="btn btn-success mt-4 btn-md text-white save" data-id="{{ $webhook_obj->id }}" style="float:left;">Save</button>
                                                        </div>
                                                        <div class="col-sm-1 px-1 mt-6">
                                                            <button class="btn ml-auto mt-1 btn-danger btn-md  delete" data-id="{{ $webhook_obj->id }}" style="float:left;"><span class="fa fa-trash"></span></button>
                                                        </div>
                                                        <div class="process-result col-sm-2 px-1 mt-10"></div>
                                                    </div>
                                                @endif

                                            </div>
                                        </div>

                                        @endforeach
                                    </div>
                                    <div id="id_new_webhook_row" class="dv-webhook-row">

                                    </div>

                                    <div class="row" id="id_dv_create_new" style="display:none;">
                                        <div class="col-sm-12 ml-3 mr-0 mt-4">
                                            <button class="btn btn-md btn-success ml-auto mt-1" onclick="addDynamicRow()">Create new action</button>
                                        </div>
                                    </div>
                                </div>


                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@php
    $html = '<select name="fv_phase_id" onchange="changeHiddenPhase(this)" class="form-control" required }}>';
    $html .= '<option value="" selected="selected">--Select Phase--</option>';
    foreach ($project_type_phases as $phase) {
        $html .= '<option  value="'.$phase['phaseId']['native'].'">'.$phase['name'].'</option>';
    }
    $html .= "</select>";
@endphp
<script>
    // webhook urls for every trigger actions.
    var webhook_inbound_urls = {
        'PhaseChanged': "{{ url('api/v1/webhook/phase_changed') }}",
        'ContactCreated': "{{ url('api/v1/webhook/contact_created') }}",
        'ProjectCreated': "{{ url('api/v1/webhook/project_created') }}",
        'CollectionItemCreated': "{{ url('api/v1/webhook/collectionitem_created') }}",
        'TaskCreated': "{{ url('api/v1/webhook/task_created') }}",
    }

    var webhook_inbound_urls_confirmed = {
        'PhaseChanged': '<?php echo (isset($webhook_logs_parse['PhaseChanged']) && $webhook_logs_parse['PhaseChanged'] > 0) ? 1 : 0; ?>',
        'ContactCreated': '<?php echo (isset($webhook_logs_parse['ContactCreated']) && $webhook_logs_parse['ContactCreated'] > 0) ? 1 : 0; ?>',
        'ProjectCreated': '<?php echo (isset($webhook_logs_parse['ProjectCreated']) && $webhook_logs_parse['ProjectCreated'] > 0) ? 1 : 0; ?>',
        'CollectionItemCreated': '<?php echo (isset($webhook_logs_parse['CollectionItemCreated']) && $webhook_logs_parse['CollectionItemCreated'] > 0) ? 1 : 0; ?>',
        'TaskCreated': '<?php echo (isset($webhook_logs_parse['TaskCreated']) && $webhook_logs_parse['TaskCreated'] > 0) ? 1 : 0; ?>',
    }

    var webhook_settings_count_parse = {
        'PhaseChanged': '<?php echo (isset($webhook_settings_count_parse['PhaseChanged']) && $webhook_settings_count_parse['PhaseChanged'] == 1) ? 1 : 0; ?>',
        'ContactCreated': '<?php echo (isset($webhook_settings_count_parse['ContactCreated']) && $webhook_settings_count_parse['ContactCreated'] == 1) ? 1 : 0; ?>',
        'ProjectCreated': '<?php echo (isset($webhook_settings_count_parse['ProjectCreated']) && $webhook_settings_count_parse['ProjectCreated'] == 1) ? 1 : 0; ?>',
        'CollectionItemCreated': '<?php echo (isset($webhook_settings_count_parse['CollectionItemCreated']) && $webhook_settings_count_parse['CollectionItemCreated'] == 1) ? 1 : 0; ?>',
        'TaskCreated': '<?php echo (isset($webhook_settings_count_parse['TaskCreated']) && $webhook_settings_count_parse['TaskCreated'] == 1) ? 1 : 0; ?>',
    }

    var triggerActions = ['PhaseChanged', 'ContactCreated', 'ProjectCreated', 'CollectionItemCreated', 'TaskCreated'];
    var phaseHtml = `<?php echo $html ?>`;
    function changeHiddenPhase(obj){
        let value = $(obj).val();
        let text = $(obj).find("option:selected").text();
        $(obj).parent().find(".phase_change_event").val(text.trim());
    }
</script>
@stop
