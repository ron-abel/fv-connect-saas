@extends('admin.layouts.default')

@section('title', 'VineConnect Admin - Client Portal Basic Configurations &amp; Settings')

@section('content')
    @php
        $all_data = \Request::all();
        $tenant_details = [];
        if (isset($all_data['tenant_details'])) {
            $tenant_details = $all_data['tenant_details'];
        }
    @endphp
    <style>
        .invalid-custom-project {
            border: 1px solid #F64E60;
        }
    </style>
    <!--begin::Subheader-->
    <div class="subheader py-2 py-lg-4 subheader-solid" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
            <!--begin::Info-->
            <div class="d-flex align-items-center flex-wrap mr-2">
                <!--begin::Page Title-->
                <h4 class="text-dark font-weight-bold mt-2 mb-2 mr-5">Admin Settings</h4>
                <!--end::Page Title-->
            </div>
            <!--end::Info-->
        </div>
    </div>
    <div class="overlay loading"></div>
    <div class="spinner-border text-primary loading" role="status">
        <span class="sr-only">Loading...</span>
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
                        <div class="card-header">
                            <h3 class="card-title">Filevine API Credentials</h3>
                        </div>
                        <div class="card-body">
                            <p><b>Instructions:</b> You need Filevine API Portal Access enabled for your Filevine Org to
                                obtain an API Key. If you have Filevine API Portal access already, simply provide your
                                Filevine login URL, an API Key, and Key Secret generated from your portal. In order to
                                utilize all of VineConnect's features, be sure to provide a key pair will <b>all scopes</b>
                                enabled.</p>
                            <p>If you have never accessed your Filevine API Portal before, it may need to be enabled for
                                your Org. Only Fileine's team can enable the API Portal. Use the button below to start the
                                process with the Filevine Support Team. Visit our <a
                                    href="https://intercom.help/vinetegrate/en/articles/5804670-api-key-for-vineconnect"
                                    target="_blank" title="Connect API Keys for VineConnect">Support Article</a> for more
                                information.</p>
                            <div class="clear"></div>
                            <div class="callout_subtle lightgrey"><i class="far fa-envelope"
                                    style="color:#383838;padding-right:5px;"></i> Email Filevine Support: <a
                                    href="mailto:support@filevine.com?subject=Requesting%20API%20Portal%20Access&body=We%20are%20requesting%20API%20portal%20access.%20Thank%20you.%0AOrg%3A%0AName%3A"
                                    target="_blank">Click Here</a></div>
                            <!--begin::Form-->
                            <form id="settings_save_form" action="{{ route('settings_post', ['subdomain' => $subdomain]) }}"
                                enctype="multipart/form-data" method="post">
                                @csrf

                                @if (session()->has('msg_err') && session()->get('msg_err'))
                                    <div class="alert alert-danger" role="alert"> Error: Filevine Credential is invalid!
                                    </div>
                                @elseif (session()->has('info_setting') && session()->get('info_setting'))
                                    <div class="alert alert-info" role="alert"> {{ session()->get('info_setting') }}
                                        @php
                                            Session::forget('info_setting');
                                            Session::save();
                                        @endphp
                                    </div>
                                @else
                                    @if (session()->get('success'))
                                        <div class="alert alert-success" role="alert"> {{ session()->get('success') }}
                                        </div>
                                    @endif
                                @endif

                                <div class="form-group mt-2 mb-2">
                                    <label><b>Your Filevine Login URL</b></label><span class="text-danger">*</span><br>
                                    <span class="instructions">Input the URL you use to log into Filevine. This is
                                        either
                                        https://app.filevine.com (for early Orgs on Filevine) or if you have a Org
                                        specific
                                        login, then https://yourfirmname.filevineapp.com (for newer Orgs on Filevine).
                                        Do
                                        not include a tailing slash at the end.</span>
                                    <input style="margin-top: 10px;" name="fv_tenant_base_url" id="fv_tenant_base_url"
                                        type="text" class="form-control"
                                        value="@if (isset($tenant_details->fv_tenant_base_url)) {{ $tenant_details->fv_tenant_base_url }} @endif">
                                </div>

                                <div class="form-group mb-2">
                                    <label><b>Filevine API Key</b></label><span class="text-danger">*</span><br />
                                    <span class="instructions">Ensure all scopes are enabled.</span>
                                    <input name="fv_api_key" type="text" class="form-control"
                                        value="@if (isset($config_details->fv_api_key)) {{ $config_details->fv_api_key }} @endif">
                                    @error('fv_api_key')
                                        <span class="form-text text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label><b>Filevine Key Secret</b></label><span class="text-danger">*</span><br />
                                    <input name="fv_key_secret" type="text" class="form-control"
                                        value="@if (isset($config_details->fv_key_secret)) {{ $config_details->fv_key_secret }} @endif">
                                    @error('fv_key_secret')
                                        <span class="form-text text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group mt-6">
                                    <button name="settings_save" class="btn btn-success mr-2 "
                                        id="settings_save">Save</button>
                                </div>
                            </form>
                            <!--end::Form-->
                        </div><!-- end::card-body -->
                    </div><!-- end::Basic Config Card -->

                </div><!-- end:Brand Card -->

                <div class="col-md-12">
                    <div class="card card-custom gutter-b example example-compact">
                        <div class="card-header">
                            <h5 class="card-title">Notification Configurations</h5>
                        </div>
                        <div class="card-body">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th scope="col">Event</th>
                                        <th scope="col">Email Notification</th>
                                        <th scope="col">Post to Filevine Activity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($tenantNotificationConfigs as $config)
                                        <tr class="config-row" data-id="{{ $config->id }}">
                                            <td>{{ $config->event_name }}</td>
                                            <td>
                                                <label class="custom-checkbox-switch">
                                                    <input type="checkbox" data-name="email" class="notification-config"
                                                        {{ $config->is_email_notification ? 'checked' : '' }}>
                                                    <span class="slider round"></span>
                                                </label>
                                            </td>
                                            <td>
                                                <label class="custom-checkbox-switch">
                                                    <input type="checkbox" data-name="post" class="notification-config"
                                                        {{ $config->is_post_to_filevine ? 'checked' : '' }}>
                                                    <span class="slider round"></span>
                                                </label>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="card-header">
                            <h5 class="card-title">Notification Emails</h5>
                        </div>
                        <div class="card-body">
                            <p><b>Instructions:</b> Email addresses added here will receive admin notifications. Up to 5
                                different email addresses can be added. Notifications include Team Feedback.</p>
                            @csrf
                            @if (session()->get('feedback_email_success'))
                                <div class="alert alert-success" role="alert">
                                    {{ session()->get('feedback_email_success') }} </div>
                            @endif
                            <div class="notice-client">
                                @if ($notificationEmails->count() < 5)
                                    <form action="{{ route('save_notification_email', ['subdomain' => $subdomain]) }}"
                                        method="POST">
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-12">
                                                <label><b>Add New Email Address</b></label>
                                                <input style="margin: 10px 0px" type="text" name="email"
                                                    id="notification_email" class="form-control">
                                                @error('notification_email')
                                                    <span class="form-text text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="save-button col-md-12 pt-3">
                                                <button class="btn btn-success">Add</button>
                                            </div>
                                        </div>
                                    </form>
                                @endif
                                <div class="row">
                                    <div class="col-md-12 pt-3">
                                        @foreach ($notificationEmails as $email)
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <label>{{ $email->email }}</label>
                                                    <form method="post"
                                                        action="{{ route('delete_notification_email', ['subdomain' => $subdomain, 'id' => $email->id]) }}"
                                                        style="display: inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-borderless"><i
                                                                class="fa fa-times-circle text-danger"></i></button>
                                                    </form>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-header">
                            <h5 class="card-title">Reply To Org Email</h5>
                        </div>
                        <div class="card-body">
                            <p><b>Instructions:</b> Email addresses added here will be used as reply email address for every
                                mail.</p>
                            <div class="row">
                                <div class="col-md-12">
                                    <label><b>Add Email</b></label>
                                    <input type="text" name="reply_to_org_email" class="form-control"
                                        value="{{ isset($config_details->reply_to_org_email) ? $config_details->reply_to_org_email : '' }}">
                                    <button class="btn btn-success mt-6 add_reply_to_org_email">Save</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--start::SMS Delivery Method Card-->
                    <div class="card card-custom gutter-b example example-compact">
                        <div class="card-header">
                            <h5 class="card-title">SMS Delivery Settings</h5>
                        </div>
                        <div class="card-body">
                            <p><b>Instructions:</b>By default our system will send SMS to the first number listed on the
                                client contact card. If you would prefer a specific delivery method you will use the
                                checkbox to indicate you would like to customize it which will provide you with the option
                                to send to the number the client submits when they log into the portal for the first time,
                                or use the dropdown to specify further.</p>

                            <form action="{{ route('save_default_contact', ['subdomain' => $subdomain]) }}"
                                method="POST">
                                @csrf

                                <div class="form-group mt-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="default_sms_way_status"
                                            {{ isset($config_details->default_sms_way_status) && $config_details->default_sms_way_status == 1 ? 'checked' : '' }}>
                                        <label class="form-check-label">
                                            Customize SMS Delivery Method
                                        </label>
                                    </div>
                                </div>

                                <div
                                    class="form-group mt-2 number_submitted_by_user_div {{ isset($config_details->default_sms_way_status) ? '' : 'd-none' }}">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="number_submitted_by_user"
                                            {{ isset($config_details->number_submitted_by_user) && $config_details->number_submitted_by_user == 1 ? 'checked' : '' }}>
                                        <label class="form-check-label">
                                            Always send to number submitted by user
                                        </label>
                                    </div>
                                </div>

                                <div
                                    class="form-group mt-3 default_sms_way_div {{ isset($config_details->default_sms_way_status) ? '' : 'd-none' }}">
                                    <label> Select a New SMS Delivery Method</label>
                                    <select class="form-control" name="default_sms_way">
                                        <option value="first_number"
                                            {{ isset($config_details->default_sms_way) && $config_details->default_sms_way == 'first_number' ? 'selected' : '' }}>
                                            The
                                            1st number listed receives the message</option>
                                        <option value="broadcast_number"
                                            {{ isset($config_details->default_sms_way) && $config_details->default_sms_way == 'broadcast_number' ? 'selected' : '' }}>
                                            Broadcast to numbers listed with the below labels</option>
                                    </select>
                                </div>
                                <div
                                    class="form-group mt-3 default_sms_custom_contact_div {{ isset($config_details->default_sms_way) && $config_details->default_sms_way == 'broadcast_number' ? '' : 'd-none' }}">
                                    <label> Available Phone Labels</label>
                                    <select style="width: 100%"
                                        class="form-control default_sms_custom_contact_label select2" multiple="multiple"
                                        id="kt_select2_3" name="default_sms_custom_contact_label[]"
                                        {{ isset($config_details->default_sms_way) && $config_details->default_sms_way == 'broadcast_number' ? 'required' : '' }}>
                                        <?= $contact_type_html ?>
                                    </select>
                                </div>
                                <div class="form-group mt-12">
                                    <div class="save-button">
                                        <button class="btn btn-success">Save</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!--end::SMS Delivery Method Card-->


                    <!--start::SMS Line Setting Card-->
                    <div class="card card-custom gutter-b example example-compact">
                        <div class="card-header">
                            <h5 class="card-title">Automated SMS Reply Settings</h5>
                        </div>
                        <div class="card-body">
                            <p><b>Instructions:</b> This section allows you to tell VineConnect how you’d like any replies
                                from your clients or end users to our automated SMS lines to be handled within Filevine. The
                                first section allows you to turn OFF/ON posting SMS replies from clients to Filevine. The
                                next section allows you to set a specific message when a reply is received. Finally, the
                                third sections tells VineConnect your preferred order of operations to follow in how we post
                                the reply to Filevine. If a higher preference fails, we’ll automatically fall to the next
                                preference. <a
                                    href="https://intercom.help/vinetegrate/en/articles/5979358-admin-settings#h_53138da78f"
                                    target="_blank" title="Automated SMS Reply Settings">Read more here</a>.</p>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th scope="col">Post Automated SMS Line Replies to Filevine</th>
                                        <th scope="col">Toggle On/Off</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="config-row">
                                        <td>Post Phase Change SMS reply to Filevine</td>
                                        <td>
                                            <label class="custom-checkbox-switch">
                                                <input type="checkbox" name="post_phase_change_response"
                                                    class="sms-line-toggle"
                                                    {{ $sms_line_config->post_phase_change_response ? 'checked' : '' }}>
                                                <span class="slider round"></span>
                                            </label>
                                        </td>
                                    </tr>
                                    <tr class="config-row">
                                        <td>Post Review Request reply to Filevine</td>
                                        <td>
                                            <label class="custom-checkbox-switch">
                                                <input type="checkbox" name="post_review_request_response"
                                                    class="sms-line-toggle"
                                                    {{ $sms_line_config->post_review_request_response ? 'checked' : '' }}>
                                                <span class="slider round"></span>
                                            </label>
                                        </td>
                                    </tr>
                                    <tr class="config-row">
                                        <td>Post Mass Text reply to Filevine</td>
                                        <td>
                                            <label class="custom-checkbox-switch">
                                                <input type="checkbox" name="post_mass_text_response"
                                                    class="sms-line-toggle"
                                                    {{ $sms_line_config->post_mass_text_response ? 'checked' : '' }}>
                                                <span class="slider round"></span>
                                            </label>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <h5 class="card-title">Set Custom SMS Response Message</h5>

                            <form action="{{ route('save_sms_line_config', ['subdomain' => $subdomain]) }}"
                                method="POST">
                                @csrf

                                <div class="row">
                                    <div class="col-md-5 form-group">
                                        <label class="custom-checkbox-switch">
                                            <input type="checkbox" class="phase_change_response"
                                                name="phase_change_response"
                                                {{ isset($sms_line_config->phase_change_response) && $sms_line_config->phase_change_response ? 'checked' : '' }}>
                                            <span class="slider round"></span>
                                        </label>
                                        <span class="font-weight-bold ml-4">Phase Change SMS Response</span>
                                    </div>
                                    <div
                                        class="col-md-7 form-group phase_change_response_text_div {{ isset($sms_line_config->phase_change_response) && $sms_line_config->phase_change_response ? '' : 'd-none' }}">
                                        <textarea name="phase_change_response_text" class="form-control">{{ $sms_line_config->phase_change_response_text ? $sms_line_config->phase_change_response_text : '' }}</textarea>
                                    </div>
                                    <div
                                        class="col-md-7 form-group phase_change_response_text_default {{ isset($sms_line_config->phase_change_response) && $sms_line_config->phase_change_response ? 'd-none' : '' }}">
                                        <p>This SMS line is unmonitored. To speak with your Legal Team, please log into the
                                            Client Portal.</p>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-md-5 form-group">
                                        <label class="custom-checkbox-switch">
                                            <input type="checkbox" class="review_request_response"
                                                name="review_request_response"
                                                {{ isset($sms_line_config->review_request_response) && $sms_line_config->review_request_response ? 'checked' : '' }}>
                                            <span class="slider round"></span>
                                        </label>
                                        <span class="font-weight-bold ml-4">Review Request SMS Response</span>
                                    </div>
                                    <div
                                        class="col-md-7 form-group review_request_response_text_div {{ isset($sms_line_config->review_request_response) && $sms_line_config->review_request_response ? '' : 'd-none' }}">
                                        <textarea name="review_request_response_text" class="form-control">{{ $sms_line_config->review_request_response_text ? $sms_line_config->review_request_response_text : '' }}</textarea>
                                    </div>
                                    <div
                                        class="col-md-7 form-group review_request_response_text_default {{ isset($sms_line_config->review_request_response) && $sms_line_config->review_request_response ? 'd-none' : '' }}">
                                        <p>This SMS line is unmonitored. To speak with your Legal Team, please log into the
                                            Client Portal.</p>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-md-5 form-group">
                                        <label class="custom-checkbox-switch">
                                            <input type="checkbox" class="mass_text_response" name="mass_text_response"
                                                {{ isset($sms_line_config->mass_text_response) && $sms_line_config->mass_text_response ? 'checked' : '' }}>
                                            <span class="slider round"></span>
                                        </label>
                                        <span class="font-weight-bold ml-4">Mass Text SMS Response</span>
                                    </div>
                                    <div
                                        class="col-md-7 form-group mass_text_response_text_div {{ isset($sms_line_config->mass_text_response) && $sms_line_config->mass_text_response ? '' : 'd-none' }}">
                                        <textarea name="mass_text_response_text" class="form-control">{{ $sms_line_config->mass_text_response_text ? $sms_line_config->mass_text_response_text : '' }}</textarea>
                                    </div>
                                    <div
                                        class="col-md-7 form-group mass_text_response_text_default {{ isset($sms_line_config->mass_text_response) && $sms_line_config->mass_text_response ? 'd-none' : '' }}">
                                        <p>This SMS line is unmonitored. To speak with your Legal Team, please log into the
                                            Client Portal.</p>
                                    </div>
                                </div>

                                <h5 class="card-title mt-8">Set Preferred Order of Operations for Posting to Filevine</h5>

                                <div class="more-feilds" id="sortable-more-feilds">
                                    @foreach ($sms_line_config_post_orders as $key => $values)
                                        <div class="row project-vital">
                                            <div class="col-md-3 pt-3 vitalSlotOrder">Order # {{ $key }}</div>
                                            <div class="col-md-5 pt-3">
                                                {{ $values[1] }}
                                                <input type="hidden" class="postOrder" name="{{ $values[0] }}"
                                                    value="{{ $key }}">
                                            </div>
                                            <div class="col-md-4 pt-1">
                                                <button type="button" class="btn btn-sm btn-grey moveup"><i
                                                        class="fa fa-arrow-up"></i></button>
                                                <button type="button" class="btn btn-sm btn-grey movedown"
                                                    style="padding: 0px;"><i class="fa fa-arrow-down"></i></button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="row mt-4">
                                    <div class="col-md-12 form-group">
                                        <label>Default Org Mailroom Number</label>
                                        <input type="text" name="default_org_mailroom_number" class="form-control"
                                            value="{{ isset($sms_line_config->default_org_mailroom_number) ? $sms_line_config->default_org_mailroom_number : '' }}">
                                    </div>
                                </div>

                                <div class="form-group mt-12">
                                    <div class="save-button">
                                        <button class="btn btn-success">Save</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!--end::SMS Line Setting Card-->

                </div><!-- end:: right side -->

            </div><!-- end::row -->
        </div><!-- end Container-->


        <div class="modal fade" id="LoadingBillPage" tabindex="-1" role="dialog"
            aria-labelledby="LoadingBillPageLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="LoadingBillPageLabel">Loading Billing Page</h5>
                    </div>
                    <div class="modal-body">
                        <h6>Org ID: {{ $fv_org_id }}</h6>
                        <h6>Total Project Count: {{ $fv_total_project }}</h6>
                        <h4>Redirecting in: <span id="delayseconds">5</span>s</h4>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light-primary font-weight-bold refresh-credentials"
                            data-dismiss="modal">Refresh Credentials</button>
                    </div>
                </div>
                </form>
            </div>
        </div>

    </div>
    <!--end::d-flex-->
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

        #display_project_sms_instruction {
            font-size: 12px;
            background: #e9eaef;
            padding: 7px;
            border-radius: 7px;
        }

        .tox-notification.tox-notification--in.tox-notification--warning {
            display: none;
        }

        .modal .modal-header .close span {
            display: block;
            color: red;
            font-size: 22px;
            font-weight: 600;
        }

        .modal-dialog {
            min-height: calc(100vh - 60px);
            min-width: 40%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            overflow: auto;
        }
    </style>
    @php
        $success = '';
        $msg_err = '';
        $notice_success = '';
        $notice_error = '';
        if (session()->has('success')) {
            $success = session()->get('success');
        }
        if (session()->has('notice_success')) {
            $notice_success = session()->get('notice_success');
        }
        if (session()->has('notice_error')) {
            $notice_error = session()->get('notice_error');
        }
        if (session()->has('msg_err')) {
            $msg_err = session()->get('msg_err');
        }
    @endphp
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('js/select2.js') }}"></script>
    <script src="{{ asset('../js/settings.js') }}"></script>
@stop
@section('scripts')
    <script>
        let fv_org_id = "{{ $fv_org_id }}";
        if (fv_org_id) {
            $('#LoadingBillPage').modal('show');
            var countDownDate = new Date().getTime() + 7000;
            var x = setInterval(function() {
                let distance = countDownDate - new Date().getTime();
                let seconds = Math.floor((distance % (1000 * 60)) / 1000);
                if (seconds >= 0) {
                    $("#delayseconds").text(seconds);
                } else {
                    $('#LoadingBillPage').modal('hide');
                }
                if (distance <= 0) {
                    clearInterval(x);
                    window.location.href = "billing/0";
                }
            }, 1000);
        }

        $("body").on("click", "button.refresh-credentials", async function() {
            clearInterval(x);
        });



        var success = "{{ $success }}";
        var notice_success = "{{ $notice_success }}";
        var notice_error = "{{ $notice_error }}";
        var error = "{{ $msg_err }}";
        if (success != "") {
            Swal.fire({
                text: success,
                icon: "success",
            });
        }
        if (notice_success != "") {
            Swal.fire({
                text: notice_success,
                icon: "success",
            });
        }
        if (notice_error) {
            Swal.fire({
                text: notice_error,
                icon: "error",
            });
        }
        if (error) {
            Swal.fire({
                text: 'Error: Filevine API credentials are invalid! Pleae check your login URL, API Key, and Key Secret.',
                icon: "error",
            });
        }
    </script>
@endsection
