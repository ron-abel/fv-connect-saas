@extends('admin.layouts.default')

@section('title', 'VineConnect Admin - Client Portal &amp; Firm Settings')

@section('content')
@php
$all_data = \Request::all();
$tenant_details = [];
if(isset($all_data['tenant_details'])) {
$tenant_details = $all_data['tenant_details'];
}
@endphp
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

.custom-switch.custom-switch-sm .custom-control-input:checked ~ .custom-control-label::after {
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

.custom-switch.custom-switch-md .custom-control-input:checked ~ .custom-control-label::after {
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

.custom-switch.custom-switch-lg .custom-control-input:checked ~ .custom-control-label::after {
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

.custom-switch.custom-switch-xl .custom-control-input:checked ~ .custom-control-label::after {
    transform: translateX(calc(2.5rem - 0.25rem));
}
.d-none{
    display: none;
}
.form-check-input{
    margin-left: 5px;
    margin-top: 0.6rem;
    transform: scale(2);
}
.form-check-label{
    cursor: pointer;
    font-size: large;
}
</style>
<!--begin::Subheader-->
<div class="subheader py-2 py-lg-4 subheader-solid" id="kt_subheader">
    <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
        <!--begin::Info-->
        <div class="d-flex align-items-center flex-wrap mr-2">
            <!--begin::Page Title-->
            <h4 class="text-dark font-weight-bold mt-2 mb-2 mr-5">LAUNCHPAD</h4>
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
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h5 class="card-title">Schedule and Manage Your Client Portal Launch</h5>
                </div>
                <div class="card-body">
                    <form id="go_live" action="{{ route('go_live',['subdomain' => $subdomain]) }}" method="POST">
                        @csrf
                        <input type="hidden" name="schedule" value="0">
                        <div class="custom-control custom-switch custom-switch-md">
                            <input type="radio" {{ (!isset($tenantLive) || $tenantLive->status=="setup")?"checked":"" }} name="status" value="setup" class="custom-control-input" id="customSwitch1">
                            <label class="custom-control-label" for="customSwitch1">SETUP</label>
                        </div>
                        <div class="custom-control custom-switch custom-switch-md">
                            <input type="radio" {{ (isset($tenantLive) && $tenantLive->status=="scheduled")?"checked":"" }} name="status" value="scheduled" class="custom-control-input" id="customSwitch2">
                            <label class="custom-control-label" for="customSwitch2">SCHEDULED</label>
                        </div>
                        <div class="custom-control custom-switch custom-switch-md">
                            <input type="radio" {{ (isset($tenantLive) && $tenantLive->status=="live")?"checked":"" }} name="status" value="live" class="custom-control-input" id="customSwitch3">
                            <label class="custom-control-label" for="customSwitch3">LIVE</label>
                        </div>
                        <div class="row mt-3 d-none scheduled_date" >
                            <div class="col-md-12">
                            <label><b>Scheduled Date of Client Portal Launch</b></label><br>
                            <i>Set a future date that Client Portal will become active. Takes effect at midnight EST on the date.</i>
                                <input style="width:auto;margin:10px 0px;" type="date" name="scheduled_date" id="scheduled_date" value="<?php if(isset($tenantLive->scheduled_date) and !empty($tenantLive->scheduled_date)) echo $tenantLive->scheduled_date; else echo '';?>" min="{{ date('Y-m-d', strtotime('+2 days')) }}" class="form-control">
                            </div>
                        </div>
                        <div class="row mt-3 d-none tfa_number">
                                <div class="row mt-5">
                                    <div class="col-md-12">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input live-checklist" type="checkbox" data-live="true" data-live="true" name="is_api_key" id="is_api_key" {{ isset($checkList) && $checkList->is_api_key == 1 ? 'checked' : '' }}>
                                            <label class="form-check-label pl-10" for="is_api_key">
                                                <b class="pl-1">Admin Setup</b>
                                            </label>
                                            <p class="mt-1 mb-0">Your Setup allows you to set your Filevine API key which is your first step, from there you will determine notification settings and set your branding.</p>
                                            <a href="{{ route('settings', ['subdomain' => $subdomain]) }}" >Go There Now</a> | <a href="https://intercom.help/vinetegrate/en/articles/5804670-api-key-for-vineconnect" target="_blank">Support Article</a>
                                        </div>

                                        <div class="form-check mb-2">
                                            <input class="form-check-input live-checklist" type="checkbox" data-live="true"
                                                   name="is_client_portal_branded" id="is_client_portal_branded" {{ isset($checkList) && $checkList->is_client_portal_branded == 1 ? 'checked' : '' }}>
                                            <label class="form-check-label pl-10" for="is_client_portal_branded">
                                                <b class="pl-1">Brand Your Client Portal</b>
                                            </label>
                                            <p class="mt-1 mb-0">Ensure you have your firm or company's display name, main Org phone number, and branded logo displayed as intended for the front end of Client Portal.</p>
                                            <a href="{{ route('settings', ['subdomain' => $subdomain]) }}" >Go There Now</a> | <a href="https://intercom.help/vinetegrate/en/articles/5979358-client-portal-settings" target="_blank">Support Article</a>
                                        </div>

                                        <div class="form-check mb-2">
                                            <input class="form-check-input live-checklist" type="checkbox" data-live="true" name="is_custom_vitals" id="is_custom_vitals" {{ isset($checkList) && $checkList->is_custom_vitals == 1 ? 'checked' : '' }} >
                                            <label class="form-check-label pl-10" for="is_custom_vitals">
                                                <b class="pl-1">Custom Vitals</b>
                                            </label>
                                            <p class="mt-1 mb-0">Configure up to ten (10) Project Vitals to display in your Client Portal for each Project Type present in Filevine.Configure up to ten (10) Project Vitals and to display to your clients and determine the best naming convention for their projects.</p>
                                            <a href="{{ route('settings', ['subdomain' => $subdomain]) }}" >Go There Now</a> | <a href="https://intercom.help/vinetegrate/en/articles/5979358-client-portal-settings" target="_blank">Support Article</a>
                                        </div>

                                        <div class="form-check mb-2">
                                            <input class="form-check-input live-checklist" type="checkbox" data-live="true" name="is_legal_team" id="is_legal_team" {{ isset($checkList) && $checkList->is_legal_team == 1 ? 'checked' : '' }}>
                                            <label class="form-check-label pl-10" for="is_legal_team">
                                                <b class="pl-1">Display Your Team</b>
                                            </label>
                                            <p class="mt-1 mb-0">Use your Filevine Team Section or Static Section with Person Fields to configure and display the assigned legal team and collect internal feedback.</p>
                                            <a href="{{ route('legal_team', ['subdomain' => $subdomain]) }}" >Go There Now</a> | <a href="https://intercom.help/vinetegrate/en/articles/5804695-legal-team" target="_blank">Support Article</a>
                                        </div>

                                        <div class="form-check mb-2">
                                            <input class="form-check-input live-checklist" type="checkbox" data-live="true" name="is_case_status_mapping" id="is_case_status_mapping" {{ isset($checkList) && $checkList->is_case_status_mapping == 1 ? 'checked' : '' }}>
                                            <label class="form-check-label pl-10" for="is_case_status_mapping">
                                                <b class="pl-1">Timeline & Mapping</b>
                                            </label>
                                            <p class="mt-1 mb-0">Create robust descriptions which can include images, videos, and links to educate your clients on their case and its current status and create case transparency.</p>
                                            <a href="{{ route('phase_categories', ['subdomain' => $subdomain]) }}" >Go There Now</a> | <a href="https://intercom.help/vinetegrate/en/articles/5814275-phase-categories-your-timeline" target="_blank">Support Article</a>
                                        </div>

                                        <div class="form-check mb-2">
                                            <input class="form-check-input live-checklist" type="checkbox" data-live="true" name="is_sms_review_request" id="is_sms_review_request" {{ isset($checkList) && $checkList->is_sms_review_request == 1 ? 'checked' : '' }}>
                                            <label class="form-check-label pl-10" for="is_sms_review_request">
                                                <b class="pl-1">Automated Communication</b>
                                            </label>
                                            <p class="mt-1 mb-0">Use Phase Change SMS (and Review Requests) to automate communication with custom messages using variables, to your clients based on a project Phase change which includes links to the Client Portal.</p>
                                            <a href="{{ route('phase_change_automated_communications', ['subdomain' => $subdomain]) }}" >Go There Now</a> | <a href="https://intercom.help/vinetegrate/en/articles/5815370-phase-change-sms" target="_blank">Support Article</a>
                                        </div>

                                        <div class="form-check mb-2">
                                            <input class="form-check-input live-checklist" type="checkbox" data-live="true" name="is_sms_review_request" id="is_sms_review_request" {{ isset($checkList) && $checkList->is_sms_review_request == 1 ? 'checked' : '' }}>
                                            <label class="form-check-label pl-10" for="is_sms_review_request">
                                                <b class="pl-1">Documents & Forms</b>
                                            </label>
                                            <p class="mt-1 mb-0">Configure where in the Filevine Project you want this information to automatically route to once a client submits data on a Form or uploads a Document.</p>
                                            <a href="{{ route('phase_change_automated_communications', ['subdomain' => $subdomain]) }}" >Go There Now</a> | <a href="https://intercom.help/vinetegrate/en/articles/5815370-phase-change-sms" target="_blank">Support Article</a>
                                        </div>

                                        <div class="form-check mb-2">
                                            <input class="form-check-input live-checklist" type="checkbox" data-live="true" name="is_sms_review_request" id="is_sms_review_request" {{ isset($checkList) && $checkList->is_sms_review_request == 1 ? 'checked' : '' }}>
                                            <label class="form-check-label pl-10" for="is_sms_review_request">
                                                <b class="pl-1">Automated Workflows & Mass Text</b>
                                            </label>
                                            <p class="mt-1 mb-0">Automate multiple actions within Filevine based on simple triggers with a project to create more powerful processes for your team. Mass Text clients, employees, vendors, from one place with this simple feature.</p>
                                            <a href="{{ route('phase_change_automated_communications', ['subdomain' => $subdomain]) }}" >Go There Now</a> | <a href="https://intercom.help/vinetegrate/en/articles/5815370-phase-change-sms" target="_blank">Support Article</a>
                                        </div>

                                        <div class="form-check">
                                            <input class="form-check-input live-checklist" type="checkbox" data-live="true" name="is_test_case_reviewed" id="is_test_case_reviewed" {{ isset($checkList) && $checkList->is_test_case_reviewed == 1 ? 'checked' : '' }}>
                                            <label class="form-check-label pl-10" for="is_test_case_reviewed">
                                                <b class="pl-1">View a Test Case In Client Portal</b>
                                            </label>
                                            <p class="mt-1 mb-0">Testing the front end of Client Portal with a test case is a wonderful way to ensure your clients are seeing what you want them to see! To log into any case while in Setup mode, enter a 2FA override number above that you have access to, such as your personal mobile phone number. Login to Client Portal as any live project (use the client's name and phone number exactly as it is in the Filevine contact record). The override 2FA number will receive the authentication code to log in.</p>
                                            <br>
                                            <h2>Lets Schedule Your Launch!</h2>
                                            <p>Testing the front end of the Client Portal with a test case is a wonderful way to ensure your clients are seeing what you want them to see, see instructions at the top of this page to utilize! Once you've checked all the boxes on this list, you can move onto the next step and toggle your Launchpad status to <b>Scheduled</b> where we can provide you with tools and resources to promote and prepare for going live!</p>
                                        </div>
                                    </div>
                            </div>
                        </div>
						<div class="row mt-3 d-none scheduled_info">
                            <div class="col-md-12">
                                <p><b>Instructions:</b> When SETUP or SCHEDULED is active, use the 2FA Override Number field below to save a SMS-capable device or number you can access. Your cell phone number is easiest. This will allow you to access the front end Client Portal for any project in your Org. <b>NOTE:</b> To login, use the client's real name and phone number associated with the project you are testing. The override number you configure will capture the 2FA code required for authentication into Client Portal. Clients will not receive any 2FA codes until you toggle to <b>LIVE</b>.</p>
                            </div>
                            <div class="col-md-12 mt-5">
								<h5 style="color:#26A9DF"><i style="color:#26A9DF" class="fa-icon fas fa-rocket"></i> Resources for Your Upcoming Launch!</h5>
								<p>Our Support Team is here to ensure that launching your Client Portal is as successful as possible! The key to adoption and continued use of your portal is promoting the launch, training your staff to remind your clients to use it, and working the Portal into your legal process. We've got some tools and resources to help with each of these points.</p>
								<div style="margin:50px auto;text-align:center;" class="row">
									<div class="col-md-4 info-box">
										<h5>SCHEDULE CONSULTATION</h5>
										<p>Let's strategize together to ensure a smooth launch! Schedule a time on our calendar to meet.</p>
                                        <div style="margin: 20px 0;"></div>
										<a href="https://calendly.com/vineconnect/vineconnect-demo-clone" target="_blank" />Schedule</a>
									</div>
									<div class="col-md-4 info-box">
										<h5>SUPPORT ARTICLES</h5>
										<p>Check out our support articles for suggestions, tips, walkthroughs, and inspiration.</p>
										<div style="margin: 20px 0;"></div>
										<a href="https://intercom.help/vinetegrate/en/collections/3255061-launching-client-portal" target="_blank" />Go There</a>
									</div>
									<div class="col-md-4 info-box">
										<h5>FREE MARKETING</h5>
										<p>Promote your Client Portal in style! We have branded flyers and graphics for you to use.</p>
										<div style="margin: 20px 0;"></div>
										<a href="https://intercom.help/vinetegrate/en/articles/6035452-promoting-your-client-portal-with-branded-graphics" target="_blank" />Request Now</a>
									</div>
								</div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <p><b>Instructions:</b> Please ensure when your portal is live that you have turned on the Phase Change SMS so those automated texts can begin to go out to your clients. Remember, even though you’re live we are always adding new and exciting features to our Client Portal. Please feel free to reach out to us for a refresher or best practice moving forward. <b>Congrats on going Live!</b></p>
                                <button class="btn btn-success" id="submit_button">Save</button>
                                <button type="button" class="btn btn-danger cancel-button">Cancel</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!--end::Container-->
</div>
<!--end::Entry-->
@php
    $success = "";
    if(session()->has('go_live')){
        $success = session()->get('go_live');
    }
@endphp
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
@stop
@section('scripts')
<script>
    var success = "{{ $success }}";
    if (success != "") {
        Swal.fire({
            text: success,
            icon: "success",
        });
    }
    $(".cancel-button").click(function(e){
        e.preventDefault();
        $("input[name=scheduled_date]").val("");
    });
    $(document).ready(function(){
        //Trick - Make live checklist optional
        setTimeout(() => {
            $(".live-checklist").removeAttr('required');
        }, 500);
    });
    $('.live-checklist').change(function () {
        var name = $(this).prop('name');
        var value = $(this).is(':checked') == true ? 1 : 0;
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{ route('client_update_tenant_live_checklist', ['subdomain' => $subdomain]) }}",
            type: "POST",
            data: { type: name, value: value },
            success:function(data){
                if(data.status && value == 1){
                    Swal.fire({
                       text: data.message,
                       icon: "success"
                    });
                }
            }, error: function () {
            }
        })
    });
    $('input[name=status]').change(function() {
        var status = $(this).val();
        //Checklist is completed
        if(status != "setup"){
            if($("input[data-live='true']").length != $("input[data-live='true']:checked").length){
                $("#customSwitch1").prop('checked', true);
                $(".scheduled_date").find("input").removeAttr('required','required');
                $(".scheduled_date").addClass("d-none");
                $(".scheduled_info").addClass("d-none");
                $(".cancel-button").addClass("d-none");
                $(".tfa_number").removeClass("d-none");
                $(".tfa_number").find("input").attr('required','required');
                $("input[name=scheduled_date]").val("");
                Swal.fire({
                    text: "Setup not complete! Please review the checklist.",
                    icon: "warning"
                });
                return;
            }
        }
        if(status == "setup"){
            $(".scheduled_date").find("input").removeAttr('required','required');
            $(".scheduled_date").addClass("d-none");
            $(".scheduled_info").addClass("d-none");
            $(".cancel-button").addClass("d-none");
            $(".tfa_number").removeClass("d-none");
            $(".tfa_number").find("input").attr('required','required');
            $("input[name=scheduled_date]").val("");
        }else if(status == "scheduled"){
            $(".scheduled_date").find("input").attr('required','required');
            $(".scheduled_date").removeClass("d-none");
            $(".cancel-button").removeClass("d-none");
            $(".scheduled_info").removeClass("d-none");
            $(".tfa_number").addClass("d-none");
            $(".tfa_number").find("input").attr('required');
        }else{
            $(".scheduled_date").find("input").removeAttr('required','required');
            $(".scheduled_date").addClass("d-none");
            $(".tfa_number").addClass("d-none");
            $(".scheduled_info").addClass("d-none");
            $(".cancel-button").addClass("d-none");
            $(".tfa_number").find("input").removeAttr('required','required');
            $("input[name=scheduled_date]").val("");
        }
    });
    var status = $('input[name=status]:checked').val();
    if(status == "setup"){
        $(".scheduled_date").find("input").removeAttr('required','required');
        $(".scheduled_date").addClass("d-none");
        $(".scheduled_info").addClass("d-none");
        $(".cancel-button").addClass("d-none");
        $(".tfa_number").removeClass("d-none");
        $(".tfa_number").find("input").attr('required','required');
    }else if(status == "scheduled"){
        $(".scheduled_date").find("input").attr('required','required');
        $(".scheduled_date").removeClass("d-none");
        $(".scheduled_info").removeClass("d-none");
        $(".cancel-button").removeClass("d-none");
        $(".tfa_number").addClass("d-none");
        $(".tfa_number").find("input").attr('required');
    }else{
        $(".scheduled_date").addClass("d-none");
        $(".scheduled_info").addClass("d-none");
        $(".tfa_number").addClass("d-none");
        $(".cancel-button").addClass("d-none");
        $(".scheduled_date").find("input").removeAttr('required','required');
        $(".tfa_number").find("input").removeAttr('required','required');
    }

    $("#submit_button").click(function (event) {
        $(".live-checklist").removeAttr('required');
        let opt_status = $('input[name=status]:checked').val();
        if(opt_status == "setup"){
            $(".scheduled_date").find("input").removeAttr('min');
         }
    });
</script>
@endsection
