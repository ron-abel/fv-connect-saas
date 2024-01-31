@extends('admin.layouts.default')

@section('title', 'VineConnect Admin - Client Portal Custom Project Vitals')

@section('content')
    <style>
        /* For toggle */
        .toggleSwitch span span {
            display: none;
        }

        .toggleSwitch {
            display: inline-block;
            height: 18px;
            position: relative;
            overflow: visible;
            padding: 0;
            cursor: pointer;
            width: 400px;
            background-color: #fafafa;
            border: 1px solid #ccc;
            border-radius: 5px;
            height: 34px;
            user-select: none;
        }

        .toggleSwitch * {
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            box-sizing: border-box;
        }

        .toggleSwitch label,
        .toggleSwitch>span {
            line-height: 20px;
            height: 20px;
            vertical-align: middle;
        }

        .toggleSwitch input:focus~a,
        .toggleSwitch input:focus+label {
            outline: none;
        }

        .toggleSwitch label {
            position: relative;
            z-index: 3;
            display: block;
            width: 100%;
        }

        .toggleSwitch input {
            position: absolute;
            opacity: 0;
            z-index: 5;
        }

        .toggleSwitch>span {
            position: absolute;
            left: 0;
            width: calc(100% - 6px);
            margin: 0;
            text-align: left;
            white-space: nowrap;
            margin: 0 3px;
        }

        .toggleSwitch>span span {
            position: absolute;
            top: 0;
            left: 0;
            z-index: 5;
            display: block;
            width: 50%;
            margin-left: 50px;
            text-align: left;
            font-size: 0.9em;
            width: auto;
            left: 0;
            top: -1px;
            opacity: 1;
            width: 40%;
            text-align: center;
            line-height: 34px;
        }

        .toggleSwitch a {
            position: absolute;
            right: 50%;
            z-index: 4;
            display: block;
            top: 3px;
            bottom: 3px;
            padding: 0;
            left: 3px;
            width: 50%;
            background-color: #26A9DF;
            border-radius: 4px;
            -webkit-transition: all 0.2s ease-out;
            -moz-transition: all 0.2s ease-out;
            transition: all 0.2s ease-out;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .toggleSwitch>span span:first-of-type {
            color: #FFF;
            opacity: 1;
            left: 0;
            margin: 0;
            width: 50%;
        }

        .toggleSwitch>span span:last-of-type {
            left: auto;
            right: 0;
            color: #999;
            margin: 0;
            width: 50%;
        }

        .toggleSwitch>span:before {
            content: '';
            display: block;
            width: 100%;
            height: 100%;
            position: absolute;
            left: 0;
            top: -2px;
            /* background-color: #fafafa;
                            border: 1px solid #ccc; */
            border-radius: 30px;
            -webkit-transition: all 0.2s ease-out;
            -moz-transition: all 0.2s ease-out;
            transition: all 0.2s ease-out;
        }

        .toggleSwitch input:checked~a {
            left: calc(50% - 3px);
        }

        .toggleSwitch input:checked~span:before {
            /* border-color: #0097D1;
                            box-shadow: inset 0 0 0 30px #0097D1; */
        }

        .toggleSwitch input:checked~span span:first-of-type {
            left: 0;
            color: #999;
        }

        .toggleSwitch input:checked~span span:last-of-type {
            /* opacity: 1;
                            color: #fff;	 */
            color: #FFF;
        }

        /* Switch Sizes */
        .toggleSwitch.large {
            width: 60px;
            height: 27px;
        }

        .toggleSwitch.large a {
            width: 27px;
        }

        .toggleSwitch.large>span {
            height: 29px;
            line-height: 28px;
        }

        .toggleSwitch.large input:checked~a {
            left: 41px;
        }

        .toggleSwitch.large>span span {
            font-size: 1.1em;
        }

        .toggleSwitch.large>span span:first-of-type {
            left: 50%;
        }

        .toggleSwitch.xlarge {
            width: 80px;
            height: 36px;
        }

        .toggleSwitch.xlarge a {
            width: 36px;
        }

        .toggleSwitch.xlarge>span {
            height: 38px;
            line-height: 37px;
        }

        .toggleSwitch.xlarge input:checked~a {
            left: 52px;
        }

        .toggleSwitch.xlarge>span span {
            font-size: 1.4em;
        }

        .toggleSwitch.xlarge>span span:first-of-type {
            left: 50%;
        }

        .display-setting-color {
            padding: 0;
            border: 1px solid #ccc;
            border-radius: 2px;
            outline: none; /* Remove the default blue outline on focus */
            cursor: pointer; /* Change the cursor to a pointer on hover */
        }

        .display-setting-color::-webkit-color-swatch-wrapper {
            padding: 0;
        }

        .display-setting-color::-webkit-color-swatch {
            border: 0;
            border-radius: 2px;
            padding: 0;
            background: transparent; /* Remove the background color */
        }
    </style>
    <!--begin::Subheader-->
    <div class="subheader py-2 py-lg-4 subheader-solid" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
            <!--begin::Info-->
            <div class="d-flex align-items-center flex-wrap mr-2">
                <!--begin::Page Title-->
                <h4 class="text-dark font-weight-bold mt-2 mb-2 mr-5">Portal Display Settings</h4>
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

            @if (session()->has('msg_err') && session()->get('msg_err'))
                <div class="alert alert-danger" role="alert"> {{ session()->get('msg_err') }}
                </div>
            @else
                @if (session()->get('success'))
                    <div class="alert alert-success" role="alert"> {{ session()->get('success') }}
                    </div>
                @endif
            @endif
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h5 style="margin-top:25px;" class="card-title">Brand Your Client Portal</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group mb-8">
                                <div class="alert alert-custom alert-default" role="alert">
                                    <div class="alert-icon">
                                        <span class="svg-icon svg-icon-primary svg-icon-xl">
                                            <!--begin::Svg Icon | path:assets/media/svg/icons/Tools/Compass.svg-->
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px"
                                                viewBox="0 0 24 24" version="1.1">
                                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                    <rect x="0" y="0" width="24" height="24">
                                                    </rect>
                                                    <path
                                                        d="M7.07744993,12.3040451 C7.72444571,13.0716094 8.54044565,13.6920474 9.46808594,14.1079953 L5,23 L4.5,18 L7.07744993,12.3040451 Z M14.5865511,14.2597864 C15.5319561,13.9019016 16.375416,13.3366121 17.0614026,12.6194459 L19.5,18 L19,23 L14.5865511,14.2597864 Z M12,3.55271368e-14 C12.8284271,3.53749572e-14 13.5,0.671572875 13.5,1.5 L13.5,4 L10.5,4 L10.5,1.5 C10.5,0.671572875 11.1715729,3.56793164e-14 12,3.55271368e-14 Z"
                                                        fill="#000000" opacity="0.3"></path>
                                                    <path
                                                        d="M12,10 C13.1045695,10 14,9.1045695 14,8 C14,6.8954305 13.1045695,6 12,6 C10.8954305,6 10,6.8954305 10,8 C10,9.1045695 10.8954305,10 12,10 Z M12,13 C9.23857625,13 7,10.7614237 7,8 C7,5.23857625 9.23857625,3 12,3 C14.7614237,3 17,5.23857625 17,8 C17,10.7614237 14.7614237,13 12,13 Z"
                                                        fill="#000000" fill-rule="nonzero"></path>
                                                </g>
                                            </svg>
                                            <!--end::Svg Icon-->
                                        </span>
                                    </div>
                                    <div class="alert-text">Upload your brand or logo to display. Be aware that the
                                        background within the Client Protal is dark blue, and the login screen
                                        background is
                                        white so transparent PNGs are recommended.
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-2">
                                <input name="image" id="img" type="file" style="display:none;">
                                <label class="d-flex align-items-center" for="img">
                                    <span class="btn btn-secondary file-upload-button">Upload Your Logo</span>
                                    <span class="js-firm-logo-name ml-5">
                                        @if (isset($config_details->logo))
                                            {{ $config_details->logo }}
                                        @else
                                            {{ 'No file chosen' }}
                                        @endif
                                    </span>
                                </label>
                            </div>

                            <div class="form-group mb-2">
                                <input name="background" id="client_background" type="file" style="display:none;">
                                <label class="d-flex align-items-center" for="client_background">
                                    <span class="btn btn-secondary file-upload-button">Upload Background</span>
                                    <span class="js-background-name ml-5">
                                        @if (isset($config_details->background))
                                            {{ $config_details->background }}
                                        @else
                                            {{ 'No file chosen' }}
                                        @endif
                                    </span>
                                </label>
                            </div>

                            <div class="form-group d-flex align-items-center mb-2">
                                <label class="m-0">Logo Background Color</label>
                                <input type="color" class="display-setting-color ml-4" value="{{ $config_details->color_logo ?? '#333333' }}" name="color_logo" />
                            </div>

                            <div class="form-group d-flex align-items-center mb-2">
                                <label class="m-0">Client Portal Accent Color</label>
                                <input type="color" class="display-setting-color ml-4" value="{{ $config_details->color_main ?? '#185598' }}" name="color_main" />
                            </div>

                            <div class="form-group d-flex align-items-center mb-2">
                                <label class="m-0">Client Portal Accent Text Color</label>
                                <input type="color" class="display-setting-color ml-4" value="{{ $config_details->color_text ?? '#26a9e0' }}" name="color_text" />
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <h6>Branded Display Name</h6>
                                <span class="instructions">The name of your firm, company, or brand. This is used
                                    throughout the system and automations to identify the Client Portal as yours.</span>
                                <input name="lf_display_name" type="text" class="form-control"
                                    value="@if (isset($Tenant->tenant_law_firm_name)) {{ $Tenant->tenant_law_firm_name }} @endif">
                            </div>

                            <div class="form-group mt-2" style="margin-top: 25px !important;">
                                <h6>Main Org Phone Number to Display</h6>
                                <span class="instructions">A phone number you can display throughout the Client Portal as a
                                    general point of contact.</span>
                                <input class="form-control" type="text" name="display_phone_number"
                                    value="{{ isset($tenantCustomVital->display_phone_number) ? $tenantCustomVital->display_phone_number : '' }}"
                                    required>
                            </div>

                            <div style="padding: 0px 0" class="form-group mt-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="show_archieved_phase"
                                        id="flexCheckChecked"
                                        {{ isset($config_details->is_show_archieved_phase) && $config_details->is_show_archieved_phase == 1 ? 'checked' : '' }}>
                                    <label class="form-check-label" for="flexCheckChecked">
                                        Allow Archived Projects to be Accessed in Client Portal
                                    </label>
                                </div>
                            </div>

                            <div class="form-group mt-2" style="margin-top: 25px !important;">
                                <h6>Test SMS Number</h6>
                                <span class="instructions">Set an SMS-capable number here to test the front end of Client
                                    Portal, Phase Change SMS, Review Request SMS. Use only the 10-digit numerical
                                    format.</span>
                                <input class="form-control" type="text" name="test_tfa_number"
                                    value="{{ isset($tenantLive->test_tfa_number) ? $tenantLive->test_tfa_number : '' }}"
                                    required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group p-1 mt-10">
                            <a href="javascript:window.location.href=window.location.href"
                                class="btn btn-success mr-2">Save</a>
                        </div>
                        <!-- end::Card Body-->
                    </div>
                </div>
            </div>
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h5 class="card-title">Project Naming Convention</h5>
                </div>
                <div class="card-body">
                    <p><b>Instructions:</b>When you select the option to Customize the Project Name Convention, you'll be
                        given a dropdown of two options for your Display Name. If you select Primary Field Value you will
                        then need to choose the <b>Project Type</b>, the <b>Section</b> the field is in, and the
                        <b>Field</b> itself.
                    </p>

                    <div class="row">
                        <div class="col-6">
                            <div style="padding: 0px 0" class="form-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="clientCustomProjectCheck"
                                        name="client_custom_project_name">
                                    <label class="form-check-label" for="clientCustomProjectCheck">
                                        Customize the Project Name Convention
                                    </label>
                                </div>
                            </div>
                            <div style="padding:0;" class="row client-custom-project-settings m-0 p-0 d-none">
                                <div class="form-group col-sm-12 p-1">
                                    <label class="">Display Project As</label>
                                    <select id="display_project_as" class="form-control">
                                        <option value="client_full_name">Client Full Name</option>
                                        <option value="field_value">A Primary Field Value</option>
                                        <!-- <option value="client_full_name-field_value" class="d-none">Client Full Name - A Field Value</option>
                                                                <option value="field_value-field_value" class="d-none">A Field Value - A Field Value</option> -->
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div style="padding:0;" class="row client-custom-project-settings m-0 p-0 d-none">
                                <div style="padding: 0px 0" class="form-group col-sm-12 p-1">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                            id="clientCustomProjectAppendCheck"
                                            name="client_custom_project_name_append_another_field">
                                        <label class="form-check-label" for="clientCustomProjectAppendCheck">
                                            Append A Field Value
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group col-sm-12 client-custom-project-main d-none p-1">
                                    <div class="row p-0 m-0">
                                        <div class="form-group col-sm-12 p-0 m-0">
                                            <label class="">Primary Field Value</label>
                                        </div>
                                    </div>
                                    <div class="row p-0 m-0">
                                        <div class="form-group col-md-4 p-0 m-0">
                                            <label>Choose Project Type</label>
                                            <input type="hidden" id="client_custom_project_type_name">
                                            <select id="client_custom_project_selector" class="form-control"
                                                data-type="main">
                                                <option value="">Choose Option</option>
                                                @if (count($project_type_lists) > 0)
                                                    @foreach ($project_type_lists as $type)
                                                        <option value="{{ $type['projectTypeId']['native'] }}">
                                                            {{ $type['name'] }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        <div class="form-group col-md-4 p-0 m-0">
                                            <label>Choose Section</label>
                                            <input type="hidden" id="client_custom_section_name">
                                            <select id="client_custom_section_selector" class="form-control"
                                                data-type="main">
                                            </select>
                                        </div>
                                        <div class="form-group col-md-4 p-0 m-0">
                                            <label>Choose Field</label>
                                            <input type="hidden" id="client_custom_field_name">
                                            <select id="client_custom_field_selector" class="form-control"
                                                data-type="main">
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group col-sm-12 client-custom-project-optional p-1 d-none">
                                    <div class="row p-0 m-0">
                                        <div class="form-group col-sm-12 p-0 m-0">
                                            <label class="">Secondary Field Value</label>
                                        </div>
                                    </div>
                                    <div class="row p-0 m-0">
                                        <div class="form-group col-md-4 p-0 m-0">
                                            <label>Choose Project Type</label>
                                            <input type="hidden" id="client_custom_project_type_name_optional">
                                            <select id="client_custom_project_selector_optional" class="form-control"
                                                data-type="optional">
                                                <option value="">Choose Option</option>
                                                @if (count($project_type_lists) > 0)
                                                    @foreach ($project_type_lists as $type)
                                                        <option value="{{ $type['projectTypeId']['native'] }}">
                                                            {{ $type['name'] }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        <div class="form-group col-md-4 p-0 m-0">
                                            <label>Choose Section</label>
                                            <input type="hidden" id="client_custom_section_name_optional">
                                            <select id="client_custom_section_selector_optional" class="form-control"
                                                data-type="optional">
                                            </select>
                                        </div>
                                        <div class="form-group col-md-4 p-0 m-0">
                                            <label>Choose Field</label>
                                            <input type="hidden" id="client_custom_field_name_optional">
                                            <select id="client_custom_field_selector_optional" class="form-control"
                                                data-type="optional">
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group p-1 mt-12">
                        <button class="btn btn-success" id="save-client-custom-project-settings">Save</button>
                    </div>
                </div>
            </div>
            {{-- </form> --}}
            <!--end::Project Naming Convention Card-->
            {{-- </form> --}}
            <!--begin::Row-->
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-custom gutter-b example example-compact">
                        <div class="card-header">
                            <h5 class="card-title">Setting Up Your Custom Project Vitals</h5>
                        </div>
                        <div class="card-body">
                            <p><b>Instructions:</b> You can configure up to ten (10) additional Project Vitals for
                                each
                                Project Type in your Filevine Org and sort the order in which they appear; vitals
                                configured here will only display in Client Portal if data is present. If you add a
                                new
                                Vital to any Project Type Template in Filevine, be sure to <b>FETCH</b> to enable
                                those
                                new vitals below. You can also choose to display default information or display only
                                the
                                data you prefer your clients to see.</p>
                            @if (session()->get('notice_success'))
                                <div class="alert alert-success" role="alert"> {{ session()->get('notice_success') }}
                                </div>
                            @endif
                            <div class="row mt-5">
                                <div class="col-6">
                                    <div class="input-group">
                                        <div>
                                            <input style="margin-left: 0" class="form-check-input"
                                                id="is_show_project_email" type="checkbox" name="is_show_project_email"
                                                @if (isset($tenantCustomVital->is_show_project_email) and !empty($tenantCustomVital->is_show_project_email)) {{ 'checked' }} @endif>
                                        </div>
                                        <label class="form-check-label" style="margin-left: 2rem" for="flexCheckChecked">
                                            Display Project Email Address in Client Portal
                                        </label>

                                    </div>
                                    <div class="input-group mt-4">
                                        <div>
                                            <input style="margin-left: 0" class="form-check-input"
                                                id="is_show_project_sms_number" type="checkbox"
                                                name="is_show_project_sms_number"
                                                @if (isset($tenantCustomVital->is_show_project_sms_number) and !empty($tenantCustomVital->is_show_project_sms_number)) {{ 'checked' }} @endif>
                                        </div>
                                        <label class="form-check-label" style="margin-left: 2rem" for="flexCheckChecked">
                                            Display Project SMS Number
                                        </label>
                                    </div>
                                    <div id="display_project_sms_instruction"
                                        class="mt-2 {{ isset($tenantCustomVital->is_show_project_sms_number) && !empty($tenantCustomVital->is_show_project_sms_number) ? 'd-block' : 'd-none' }}">
                                        In order to display Project SMS Number, you must add Project SMS Number to your
                                        Project
                                        Type Vitals from the Customs Editor in Filevine. You do not need to add Project
                                        SMS
                                        Number here in Custom Vitals - Client Portal will automatically display the
                                        number
                                        if
                                        it’s present in Filevine Vitals
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="input-group mt-4">
                                        <div>
                                            <input style="margin-left: 0" class="form-check-input"
                                                id="is_show_project_clientname" type="checkbox"
                                                name="is_show_project_clientname"
                                                @if (isset($tenantCustomVital->is_show_project_clientname) and !empty($tenantCustomVital->is_show_project_clientname)) {{ 'checked' }} @endif>
                                        </div>
                                        <label class="form-check-label" style="margin-left: 2rem" for="flexCheckChecked">
                                            Display default vital for Client Name
                                        </label>
                                    </div>
                                    <div class="input-group mt-4">
                                        <div>
                                            <input style="margin-left: 0" class="form-check-input"
                                                id="is_show_project_name" type="checkbox" name="is_show_project_name"
                                                @if (isset($tenantCustomVital->is_show_project_name) and !empty($tenantCustomVital->is_show_project_name)) {{ 'checked' }} @endif>
                                        </div>
                                        <label class="form-check-label" style="margin-left: 2rem" for="flexCheckChecked">
                                            Display default vital for Project Name
                                        </label>
                                    </div>
                                    <div class="input-group mt-4">
                                        <div>
                                            <input style="margin-left: 0" class="form-check-input"
                                                id="is_show_project_id" type="checkbox" name="is_show_project_id"
                                                @if (isset($tenantCustomVital->is_show_project_id) and !empty($tenantCustomVital->is_show_project_id)) {{ 'checked' }} @endif>
                                        </div>
                                        <label class="form-check-label" style="margin-left: 2rem" for="flexCheckChecked">
                                            Display default vital for Project ID
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-5">
                                <div class="col-6">
                                    <div class="form-group row">
                                        <label for="projectVitalOverrideTitle"
                                            class="col-auto col-form-label col-form-label-sm">Override
                                            Title:</label>
                                        <div class="col-sm-8">
                                            <input placeholder="Override Title" class="form-control" type="text"
                                                name="project_vital_override_title" value="{{ isset($tenantCustomVital->project_vital_override_title) ? $tenantCustomVital->project_vital_override_title : '' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="notice-client" style="margin-top: 25px;">
                                <div class="row">
                                    <div class="col-md-4 pt-3">
                                        <label> Choose Project Type</label>
                                        <select class="form-control" id="projectType">
                                            <option value="">Select Project Type</option>
                                            @foreach ($project_type_lists as $project_type_list)
                                                <option value="{{ $project_type_list['projectTypeId']['native'] }}">
                                                    {{ $project_type_list['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 pt-3">
                                        <label>Fetch Vitals</label>
                                        <button type="button" class="form-control btn btn-warning fetch-vital">FETCH
                                        </button>
                                    </div>
                                    <div class="col-md-4 pt-3">
                                        <label> Select A Vital </label>
                                        <select class="form-control" id="ProjectsVital">
                                            <option value="">Available Vitals</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 pt-3">
                                        <button type="button" class="btn btn-success mt-8 add-more">Add</button>
                                    </div>
                                </div>
                                <div class="row mt-6">
                                    <div class="col-md-2 font-weight-bold">Order</div>
                                    <div class="col-md-3 font-weight-bold">Field Name</div>
                                    <div class="col-md-3 font-weight-bold">Override Title</div>
                                    <div class="col-md-4 font-weight-bold">Action</div>
                                </div>
                                <div class="more-feilds mt-5" id="sortable-more-feilds">

                                </div>
                                <div class="row">
                                    <div class="save-button col-md-12 pt-3">
                                        <button class="btn btn-success save-Project-Vitals">Save</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end::row -->

            <div class="row">
                <div class="col-md-12">
                    <!--begin::Card-->
                    <div class="card card-custom gutter-b example example-compact">
                        <div class="card-header">
                            <h5 class="card-title mt-7">Display Your Project's Team Assignments in Client Portal</h5>
                        </div>
                        <div class="card-body">
                            <div class="pg_content">
                                <p><b>Instructions:</b> If you use the Team section in Filevine, select “Config by Org
                                    Roles" which will tell VineConnect which roles you want displayed in the Client Portal.
                                    For firms that rely on Person Fields in Static Sections, select “Config by Person Field”
                                    instead.</p>
                                <p>The difference between "Fetch" and "Static" is simply that "Fetch" will dynamically
                                    reference the team member however it is set in your Filevine project, while a "Static"
                                    selection allows you to set field values that don't change from project to project.</p>
                                <div class="callout_subtle lightgrey"><i class="fas fa-link"
                                        style="color:#383838;padding-right:5px;"></i>
                                    Support Article: <a
                                        href="https://intercom.help/vinetegrate/en/articles/5804695-configure-your-legal-team"
                                        target="_blank" />Legal Team Configurations</a></div>
                            </div>
                            <!-- pg_content" -->

                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="mt-5 switch-div">
                                        @php
                                            $class = 'btn-light';
                                            if ($legal_team_by_role) {
                                                $class = 'btn-success';
                                            }
                                        @endphp
                                        <div class="custom-control custom-switch custom-switch-md pl-0">
                                            {{-- <label class="custom-control-label ml-7 pl-4" for="person-fields">Config by --}}
                                            {{-- Person Field</label> --}}
                                            {{-- <input type="checkbox" --}}
                                            {{-- @if ($legal_team_by_role){{ 'checked'}} @endif class="custom-control-input toggle-config" --}}
                                            {{-- name="org-roles" id="org-roles"> --}}
                                            {{-- <a></a> --}}
                                            {{-- <span> --}}
                                            {{-- <span class="left-span">Enabled</span> --}}
                                            {{-- <span class="right-span">Disabled</span> --}}
                                            {{-- </span> --}}
                                            {{-- <label class="custom-control-label ml-7 pl-4" for="org-roles">Config by Org --}}
                                            {{-- Role</label> --}}

                                            <input type="hidden" class="custom-control-input toggle-config"
                                                name="person-fields" id="person-fields">
                                            <label class="toggleSwitch nolabel" onclick="">
                                                <input type="checkbox" name="is_legal_team_by_roles" class="role"
                                                    id="config_role"
                                                    @if ($legal_team_by_role) {{ 'checked' }} @endif
                                                    value="{{ $legal_team_by_role }}" />
                                                <a></a>
                                                <span>
                                                    <span class="left-span">Config by Person Field</span>
                                                    <span class="right-span">Config by Org Role</span>
                                                </span>
                                            </label>
                                        </div>

                                        {{-- <button type="button" class="btn {{ $class }} toggle-config" id="org-roles">Config by Org Role</button> --}}
                                        {{-- @php --}}
                                        {{-- $class = "btn-light"; --}}
                                        {{-- if(!$legal_team_by_role) $class = "btn-success"; --}}
                                        {{-- @endphp --}}
                                        {{-- <button type="button" class="btn {{ $class }} toggle-config" id="person-fields">Config by Person Field</button> --}}
                                    </div>
                                </div>
                                {{-- <div class="col-sm-4"> --}}
                                {{-- <div class="mt-8 switch-div"> --}}
                                {{-- <div class="custom-control custom-switch custom-switch-md pl-0"> --}}
                                {{-- <input type="radio" --}}
                                {{-- @if (!$legal_team_by_role){{ 'checked'}} @endif class="custom-control-input toggle-config" --}}
                                {{-- name="person-fields" id="person-fields"> --}}
                                {{-- <label class="custom-control-label ml-7 pl-4" for="person-fields">Config by --}}
                                {{-- Person Field</label> --}}
                                {{-- </div> --}}
                                {{-- </div> --}}
                                {{-- </div> --}}

                                <div class="col-sm-6">
                                    <div class="mt-4">
                                        <div class="form-group row">
                                            <label for="legalTeamTitle"
                                                class="col-auto col-form-label col-form-label-sm">Override
                                                Title:</label>
                                            <div class="col-sm-8">
                                                <input placeholder="Title" class="form-control" type="text"
                                                    name="title" id="legalTeamTitle"
                                                    @if (isset($tenant_override_title->title)) value="{{ $tenant_override_title->title }}" @endif />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @php
                                $style = 'display:none;';
                                if (!$legal_team_by_role) {
                                    $style = 'display:block;';
                                }
                            @endphp
                            <div class="login-form validate-form " id="person-fields-form" style="{{ $style }}">
                                <table class="table w-100 repeater">
                                    <form action="{{ url('/admin/legalteam_person') }}" method="post">
                                        {!! csrf_field() !!}
                                        <tbody data-repeater-list="group-a">
                                            <tr>
                                                <td style="width:20% !important">
                                                    <input type="hidden" name="type" class="typeField"
                                                        value="fetch">
                                                    <label for="">Choose to fetch your Legal Team by Person Fields
                                                        <br>or
                                                        statically set your Team</label>
                                                    <br>
                                                    <button type="button"
                                                        class="btn-fetch-field btn ml-auto mt-1 btn-warning btn-md fetchAdd"
                                                        style="float:left;">FETCH
                                                    </button>
                                                    <button type="button"
                                                        class="btn-static-field btn ml-3 mt-1 btn-secondary btn-md staticAdd"
                                                        style="float:left;">STATIC
                                                    </button>
                                                </td>
                                                <td style="width:18% !important" class="static_element_field d-none">
                                                    <div class="form-group">
                                                        <label>Name</label>
                                                        <input type="text" name="override_name"
                                                            class="form-control role_name" placeholder="Enter name">
                                                    </div>
                                                </td>
                                                <td style="width:18% !important" class="static_element_field d-none">
                                                    <div class="form-group">
                                                        <label>Phone</label>
                                                        <input type="text" name="override_phone"
                                                            class="form-control phone" placeholder="Enter phone">
                                                    </div>
                                                </td>
                                                <td style="width:18% !important" class="static_element_field d-none">
                                                    <div class="form-group">
                                                        <label>Email</label>
                                                        <input type="email" name="override_email"
                                                            class="form-control email" placeholder="Enter email">
                                                    </div>
                                                </td>
                                                <td style="width:18% !important" class="fetch_element_field">
                                                    <div class="form-group">
                                                        <label>Project Type</label>
                                                        <input type="hidden" name="fv_project_type_name"
                                                            id="fv_project_type_name">
                                                        <select name="fv_project_type_id" id="project_type_id"
                                                            class="form-control">
                                                            <option value="">Select Project Type</option>
                                                            @if (isset($fv_project_type_list['items']))
                                                                @foreach ($fv_project_type_list['items'] as $type)
                                                                    <option
                                                                        value="{{ $type['projectTypeId']['native'] }}">
                                                                        {{ $type['name'] }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                </td>
                                                <td style="width:18% !important" class="fetch_element_field">
                                                    <div class="form-group">
                                                        <label>Project Type Section</label>
                                                        <input type="hidden" name="fv_section_name"
                                                            id="fv_section_name">
                                                        <select name="fv_section_id" id="section_selector"
                                                            class="form-control">
                                                            <option value="">Select Project Type Section</option>
                                                        </select>
                                                    </div>
                                                </td>
                                                <td style="width:18% !important" class="fetch_element_field">
                                                    <div class="form-group">
                                                        <label>Person Field</label>
                                                        <input type="hidden" name="fv_person_field_name"
                                                            id="fv_person_field_name">
                                                        <select name="fv_person_field_id" id="section_selector_field"
                                                            class="form-control">
                                                            <option value="">Select a Person Field</option>
                                                        </select>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group mt-8">
                                                        <button type="submit"
                                                            class="btn btn-success addButton">ADD</button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </form>
                                </table>
                                <table class="table table-person w-100">
                                    <tbody>
                                        @foreach ($data_person_config as $config)
                                            @php
                                                $class = '';
                                                if (empty($config->fv_person_field_name)) {
                                                    $class = 'disabled';
                                                }
                                            @endphp
                                            <tr class="table_person">
                                                <td style="width:20% !important">
                                                    <input type="hidden" name="type" class="typeField"
                                                        value="{{ $config->type }}">
                                                    <input type="hidden" class="id" value="{{ $config->id }}">
                                                    <label for="">Choose to fetch your Legal Team by Person Fields
                                                        <br>or
                                                        statically set your Team</label>
                                                    <br>
                                                    <button type="button"
                                                        class="{{ $class }} btn-fetch-field btn ml-auto mt-1 {{ $config->type == \App\Models\LegalteamPersonConfig::TYPE_FETCH ? 'btn-warning' : 'btn-secondary' }} btn-md"
                                                        style="float:left;">FETCH
                                                    </button>
                                                    <button type="button"
                                                        class="{{ $class }} btn-static-field btn ml-3 mt-1 {{ $config->type == \App\Models\LegalteamPersonConfig::TYPE_STATIC ? 'btn-warning' : 'btn-secondary' }}  btn-md"
                                                        style="float:left;">STATIC
                                                    </button>
                                                </td>
                                                <td style="width:10% !important"
                                                    class="static_element_field {{ $config->type == \App\Models\LegalteamPersonConfig::TYPE_FETCH ? 'd-none' : null }}">
                                                    <div class="form-group">
                                                        <label>Name</label>
                                                        <input type="text" class="form-control role_name"
                                                            value="{{ $config->override_name }}"
                                                            placeholder="Enter name">
                                                    </div>
                                                </td>
                                                <td style="width:10% !important"
                                                    class="static_element_field {{ $config->type == \App\Models\LegalteamPersonConfig::TYPE_FETCH ? 'd-none' : null }}">
                                                    <div class="form-group">
                                                        <label>Phone</label>
                                                        <input type="text" class="form-control phone"
                                                            value="{{ $config->override_phone }}"
                                                            placeholder="Enter phone">
                                                    </div>
                                                </td>
                                                <td style="width:10% !important"
                                                    class="static_element_field {{ $config->type == \App\Models\LegalteamPersonConfig::TYPE_FETCH ? 'd-none' : null }}">
                                                    <div class="form-group">
                                                        <label>Email</label>
                                                        <input type="email" class="form-control email"
                                                            value="{{ $config->override_email }}"
                                                            placeholder="Enter email">
                                                    </div>
                                                </td>
                                                <td style="width:15%;"
                                                    class="fetch_element_field {{ $config->type == \App\Models\LegalteamPersonConfig::TYPE_STATIC ? 'd-none' : null }}">
                                                    <div class="form-group">
                                                        <input type="hidden" class="id-person"
                                                            value="{{ $config->id }}">
                                                        <p class="mt-3 mb-1">
                                                            {{ $config->fv_person_field_name }}
                                                        </p>
                                                    </div>
                                                    @php
                                                        $checked = '';
                                                        $style = 'display:none;';
                                                    @endphp
                                                    @if ($config->is_static_name)
                                                        @php
                                                            $checked = 'checked';
                                                            $style = 'display:block;';
                                                        @endphp
                                                    @endif
                                                    <div class="form-group">
                                                        <input type="checkbox" {{ $checked }} name="is_static_name"
                                                            value="1"
                                                            class="is_static_name form-control goog-check float-left">
                                                        <label class="mt-2 ml-2">
                                                            Set Static Person
                                                        </label>
                                                    </div>
                                                    <div class="form-group" style="{{ $style }}">
                                                        <input type="text" name="override_name"
                                                            value="{{ $config->override_name }}"
                                                            class="override_name form-control" placeholder="Name">
                                                    </div>
                                                </td>
                                                <td style="width:16%;"
                                                    class="fetch_element_field {{ $config->type == \App\Models\LegalteamPersonConfig::TYPE_STATIC ? 'd-none' : null }}">
                                                    @php
                                                        $checkedzero = '';
                                                        $checkedone = '';
                                                        $style = 'display:none;';
                                                    @endphp
                                                    @if ($config->is_enable_phone)
                                                        @php
                                                            $checkedzero = 'checked';
                                                        @endphp
                                                    @endif
                                                    @if ($config->is_override_phone)
                                                        @php
                                                            $checkedone = 'checked';
                                                            $style = 'display:block;';
                                                        @endphp
                                                    @endif
                                                    <div class="form-group">
                                                        <input type="checkbox" {{ $checkedzero }} name="is_enable_phone"
                                                            value="1"
                                                            class="is_enable_phone form-control goog-check float-left">
                                                        <label class="mt-2 ml-2">
                                                            Enable Phone Number
                                                        </label>
                                                    </div>
                                                    <div class="form-group">
                                                        <input type="checkbox" {{ $checkedone }}
                                                            name="is_override_phone" value="1"
                                                            class="is_override_phone form-control goog-check float-left"
                                                            placeholder="Phone Number">
                                                        <label class="mt-2 ml-2">
                                                            Override Phone
                                                        </label>
                                                    </div>
                                                    <div class="form-group" style="{{ $style }}">
                                                        <input type="text" name="override_phone"
                                                            value="{{ $config->override_phone }}"
                                                            class="override_phone form-control">
                                                    </div>
                                                </td>
                                                <td style="width:17%;"
                                                    class="fetch_element_field {{ $config->type == \App\Models\LegalteamPersonConfig::TYPE_STATIC ? 'd-none' : null }}">
                                                    @php
                                                        $checkedzero = '';
                                                        $checkedone = '';
                                                        $style = 'display:none;';
                                                    @endphp
                                                    @if ($config->is_enable_email)
                                                        @php
                                                            $checkedzero = 'checked';
                                                        @endphp
                                                    @endif
                                                    @if ($config->is_override_email)
                                                        @php
                                                            $checkedone = 'checked';
                                                            $style = 'display:block;';
                                                        @endphp
                                                    @endif
                                                    <div class="form-group">
                                                        <input type="checkbox" {{ $checkedzero }} name="is_enable_email"
                                                            value="1"
                                                            class="is_enable_email form-control goog-check float-left">
                                                        <label class="mt-2 ml-2">
                                                            Enable Email Address
                                                        </label>
                                                    </div>
                                                    <div class="form-group">
                                                        <input type="checkbox" {{ $checkedone }}
                                                            name="is_override_email" value="1"
                                                            class="is_override_email form-control goog-check float-left">
                                                        <label class="mt-2 ml-2">
                                                            Override Email Address
                                                        </label>
                                                    </div>
                                                    <div class="form-group" style="{{ $style }}">
                                                        <input type="text" name="override_email"
                                                            value="{{ $config->override_email }}"
                                                            class="override_email form-control"
                                                            placeholder="Email Address">
                                                    </div>
                                                </td>
                                                <td>
                                                    @php
                                                        $checked = '';
                                                    @endphp
                                                    @if ($config->is_enable_feedback)
                                                        @php
                                                            $checked = 'checked';
                                                        @endphp
                                                    @endif
                                                    <div class="form-group mt-9">
                                                        <input type="checkbox" {{ $checked }}
                                                            name="is_enable_feedback" value="1"
                                                            class="is_enable_feedback form-control goog-check float-left">
                                                        <label class="mt-2 ml-2">
                                                            Enable Feedback
                                                        </label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <label>Order of Appearance</label><br>
                                                    <button type="button" data-id="{{ $config->tenant_id }}"
                                                        fv_person_field_id="{{ $config->fv_person_field_id }}"
                                                        fv_section_id="{{ $config->fv_section_id }}"
                                                        fv_project_type_id="{{ $config->fv_project_type_id }}"
                                                        class="btn ml-auto mt-1 btn-success btn-md save-config-person">
                                                        SAVE
                                                    </button>
                                                    <button type="button" data-id="{{ $config->tenant_id }}"
                                                        fv_person_field_id="{{ $config->fv_person_field_id }}"
                                                        fv_section_id="{{ $config->fv_section_id }}"
                                                        fv_project_type_id="{{ $config->fv_project_type_id }}"
                                                        class="btn btn-danger btn-icon delete-config-person"
                                                        style="margin-top: 4px;"><i class="fa fa-trash"></i></button>
                                                    <button type="button"
                                                        class="btn btn-hover-bg-secondary btn-drag-drop-order btn-icon"
                                                        style="margin-top: 4px;"><i class="fa fa-sort"></i></button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tbody class="ui-sortable">
                                        <tr class="ui-sortable-handle">
                                            <td colspan="100">
                                                <button type="button" class="btn btn-success mr-2 person_save_all">Save
                                                    All
                                                </button>
                                            </td>
                                        </tr>

                                    </tbody>
                                </table>
                            </div>
                            @php
                                $style = 'display:none;';
                                if ($legal_team_by_role) {
                                    $style = 'display:block;';
                                }
                            @endphp
                            <div class="login-form validate-form " id="org-roles-form" style="{{ $style }}">
                                <table class="table table-role w-100 repeater">
                                    <tbody data-repeater-list="group-a">
                                        @forelse($data as $item)
                                            <tr data-repeater-item class="table_row">
                                                <td style="width:25% !important">
                                                    <input type="hidden" class="id" value="{{ $item->id }}">
                                                    <label for="">Choose to fetch your Org's Team Roles <br>or
                                                        statically
                                                        set your Team</label>
                                                    <br>
                                                    <button type="button"
                                                        class="btn-fetch btn ml-auto mt-1 {{ $item->type == \App\Models\LegalteamConfig::TYPE_FETCH ? 'btn-warning' : 'btn-secondary' }} btn-md"
                                                        style="float:left;">FETCH
                                                    </button>
                                                    <button type="button"
                                                        class="btn-static btn ml-3 mt-1 {{ $item->type == \App\Models\LegalteamConfig::TYPE_STATIC ? 'btn-warning' : 'btn-secondary' }}  btn-md"
                                                        style="float:left;">STATIC
                                                    </button>
                                                </td>
                                                <td style="width:20% !important"
                                                    class="fetch_element {{ $item->type == \App\Models\LegalteamConfig::TYPE_STATIC ? 'd-none' : null }}">
                                                    <div class="form-group">
                                                        <label>Choose which Org Role you want <br> to display in Client
                                                            Portal</label>
                                                        <select class="form-control role org_role_select">
                                                            <option value="">Select an item</option>
                                                            @foreach ($legal_tem_config_types as $role_item)
                                                                <option value="{{ $role_item['orgRoleId']['native'] }}"
                                                                    {{ $role_item['orgRoleId']['native'] == @$item->fv_role_id ? 'selected' : '' }}>
                                                                    {{ $role_item['name'] }}</option>
                                                            @endforeach

                                                        </select>
                                                    </div>
                                                </td>
                                                <td style="width:10% !important"
                                                    class="static_element {{ $item->type == \App\Models\LegalteamConfig::TYPE_FETCH ? 'd-none' : null }}">
                                                    <div class="form-group">
                                                        <label>Role Title <br>&nbsp;</label>
                                                        <input type="text" class="form-control role_title"
                                                            value="{{ $item->role_title }}"
                                                            placeholder="Enter Role Title">
                                                    </div>
                                                </td>
                                                <td style="width:10% !important"
                                                    class="static_element {{ $item->type == \App\Models\LegalteamConfig::TYPE_FETCH ? 'd-none' : null }}">
                                                    <div class="form-group">
                                                        <label>Name <br>&nbsp;</label>
                                                        <input type="text" class="form-control role_name"
                                                            value="{{ $item->name }}" placeholder="Enter name">
                                                    </div>
                                                </td>
                                                <td style="width:10% !important"
                                                    class="static_element {{ $item->type == \App\Models\LegalteamConfig::TYPE_FETCH ? 'd-none' : null }}">
                                                    <div class="form-group">
                                                        <label>Phone <br>&nbsp;</label>
                                                        <input type="text" class="form-control phone"
                                                            value="{{ $item->phone }}" placeholder="Enter phone">
                                                    </div>
                                                </td>
                                                <td style="width:10% !important"
                                                    class="static_element {{ $item->type == \App\Models\LegalteamConfig::TYPE_FETCH ? 'd-none' : null }}">
                                                    <div class="form-group">
                                                        <label>Email <br>&nbsp;</label>
                                                        <input type="email" class="form-control email"
                                                            value="{{ $item->email }}" placeholder="Enter email">
                                                    </div>
                                                </td>
                                                <td style="width:10% !important"
                                                    class="fetch_element {{ $item->type == \App\Models\LegalteamConfig::TYPE_STATIC ? 'd-none' : null }}">
                                                    <div class="form-group">
                                                        <label>Subscriber <br> Required</label>
                                                        <input type="checkbox"
                                                            class="form-control follower_required goog-check"
                                                            {{ $item->is_follower_required == \App\Models\LegalteamConfig::YES ? 'checked' : null }}
                                                            name="enable_feedback">
                                                    </div>
                                                </td>
                                                <td style="width:10% !important"
                                                    class="fetch_element {{ $item->type == \App\Models\LegalteamConfig::TYPE_STATIC ? 'd-none' : null }}">
                                                    <div class="form-group">
                                                        <label>Enable <br> Email</label>
                                                        <input type="checkbox"
                                                            class="form-control enable_email goog-check"
                                                            {{ $item->is_enable_email == \App\Models\LegalteamConfig::YES ? 'checked' : null }}
                                                            name="enable_feedback">

                                                    </div>
                                                </td>
                                                <td style="width:10% !important"
                                                    class="fetch_element {{ $item->type == \App\Models\LegalteamConfig::TYPE_STATIC ? 'd-none' : null }}">
                                                    &nbsp;

                                                </td>
                                                <td style="width:10% !important">
                                                    <div class="form-group">
                                                        <label>Enable <br> Feedback</label>
                                                        <input type="checkbox"
                                                            class="form-control enable_feedback goog-check"
                                                            {{ $item->is_enable_feedback == \App\Models\LegalteamConfig::YES ? 'checked' : null }}
                                                            name="enable_feedback">
                                                    </div>
                                                </td>
                                                <td style="width:15% !important">
                                                    <label>Order of <br> Appearance</label>
                                                    <div class="from-group">
                                                        <button type="button"
                                                            class="btn ml-auto mt-1 btn-success btn-md btn-save">SAVE
                                                        </button>
                                                        <button type="button" data-repeater-delete
                                                            class="btn btn-danger btn-icon" style="margin-top: 4px;"><i
                                                                class="fa fa-trash"></i></button>
                                                        <button type="button"
                                                            class="btn btn-hover-bg-secondary btn-drag-drop btn-icon"
                                                            style="margin-top: 4px;"><i class="fa fa-sort"></i></button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr data-repeater-item>
                                                <td style="width:25% !important">
                                                    <input type="hidden" class="id" value="">
                                                    <label for="">Choose to fetch your Org's Team Roles <br>or
                                                        statically
                                                        set your Team.</label>
                                                    <br>
                                                    <button type="button"
                                                        class="btn-fetch btn ml-auto mt-1 btn-warning btn-md"
                                                        style="float:left;">FETCH
                                                    </button>
                                                    <button type="button"
                                                        class="btn-static btn ml-3 mt-1 btn-secondary btn-md"
                                                        style="float:left;">STATIC
                                                    </button>
                                                </td>
                                                <td style="width:20% !important" class="fetch_element">
                                                    <div class="form-group">
                                                        <label>Choose which Org Role you want <br> to display in Client
                                                            Portal</label>
                                                        <select class="form-control role org_role_select">
                                                            <option value="">Select an item</option>
                                                            @foreach ($legal_tem_config_types as $list)
                                                                @if (isset($list['orgRoleId'], $list['orgRoleId']['native'], $list['name']))
                                                                    <option value="{{ $list['orgRoleId']['native'] }}">
                                                                        {{ $list['name'] }}</option>
                                                                @endif
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </td>
                                                <td style="width:20% !important" class="static_element d-none">
                                                    <div class="form-group">
                                                        <label>Role Title <br>&nbsp;</label>
                                                        <input type="text" class="form-control role_title"
                                                            placeholder="Role Title">
                                                    </div>
                                                </td>
                                                <td style="width:10% !important" class="static_element d-none">
                                                    <div class="form-group">
                                                        <label>Name <br>&nbsp;</label>
                                                        <input type="text" class="form-control role_name"
                                                            placeholder="Name">
                                                    </div>
                                                </td>
                                                <td style="width:10% !important" class="static_element d-none">
                                                    <div class="form-group">
                                                        <label>Phone <br>&nbsp;</label>
                                                        <input type="text" class="form-control phone"
                                                            placeholder="Phone Number">
                                                    </div>
                                                </td>
                                                <td style="width:10% !important" class="static_element d-none">
                                                    <div class="form-group">
                                                        <label>Email <br>&nbsp;</label>
                                                        <input type="email" class="form-control email"
                                                            placeholder="Email Address">
                                                    </div>
                                                </td>
                                                <td style="width:10% !important" class="fetch_element">
                                                    <div class="form-group">
                                                        <label>Follower <br> Required</label>
                                                        <input type="checkbox"
                                                            class="form-control follower_required goog-check"
                                                            name="enable_feedback">
                                                    </div>
                                                </td>
                                                <td style="width:10% !important" class="fetch_element">
                                                    <div class="form-group">
                                                        <label>Enable <br> Email</label>
                                                        <input type="checkbox"
                                                            class="form-control enable_email goog-check"
                                                            name="enable_feedback">

                                                    </div>
                                                </td>
                                                <td style="width:10% !important" class="fetch_element">&nbsp;

                                                </td>
                                                <td style="width:10% !important">
                                                    <div class="form-group">
                                                        <label>Enable <br> Feedback</label>
                                                        <input type="checkbox"
                                                            class="form-control enable_feedback goog-check"
                                                            name="enable_feedback">
                                                    </div>
                                                </td>
                                                <td style="width:15% !important">
                                                    <div class="from-group" style="padding-top:3.1rem;">
                                                        <button type="button"
                                                            class="btn ml-auto mt-1 btn-success btn-md btn-save">SAVE
                                                        </button>
                                                        <button type="button" data-repeater-delete
                                                            class="btn btn-danger btn-icon" style="margin-top: 4px;"><i
                                                                class="fa fa-trash"></i></button>
                                                        <button type="button"
                                                            class="btn btn-hover-bg-secondary btn-drag-drop btn-icon"
                                                            style="margin-top: 4px;"><i class="fa fa-sort"></i></button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="100">
                                                <button type="button" class="btn btn-success mr-2 save_all">Save All
                                                </button>
                                                <button type="button" data-repeater-create class="btn btn-warning mr-2">
                                                    ADD NEW
                                                </button>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div><!-- card_body end -->
                    </div>
                </div>
            </div>
        </div><!-- end Container-->
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
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('../js/portal_display_settings.js?20230822') }}"></script>
    <script src="{{ asset('js/select2.js') }}"></script>
    <script src="{{ asset('../js/settings.js') }}"></script>
    <script src="{{ asset('../js/admin/legal_team.js') }}"></script>
@stop
@section('scripts')
    <script>
        $('#is_show_project_sms_number').click(function(e) {
            if ($('#is_show_project_sms_number').prop("checked") == true) {
                $('#display_project_sms_instruction').removeClass('d-none');
                $('#display_project_sms_instruction').addClass('d-block');
            } else {
                $('#display_project_sms_instruction').removeClass('d-block');
                $('#display_project_sms_instruction').addClass('d-none');
            }
        });


        var route_legal_team_destroy = '{{ route('legal_team_destroy', ['subdomain' => $subdomain]) }}';
        var route_legal_team_sort = '{{ route('legal_team_sort', ['subdomain' => $subdomain]) }}';
        var route_legal_team_person_sort = '{{ route('legal_team_person_sort', ['subdomain' => $subdomain]) }}';
        var route_legal_team_store = '{{ route('legal_team_store', ['subdomain' => $subdomain]) }}';
        var route_legal_team_all_store = '{{ route('legal_team_all_store', ['subdomain' => $subdomain]) }}';
        var update_all_legalteam_config = '{{ route('update_all_legalteam_config', ['subdomain' => $subdomain]) }}'
        var csrf_token = '{{ csrf_token() }}';


        var LegalteamConfig_yes = '{{ \App\Models\LegalteamConfig::YES }}';
        var LegalteamConfig_no = '{{ \App\Models\LegalteamConfig::NO }}';
        var LegalteamConfig_type_fetch = '{{ \App\Models\LegalteamConfig::TYPE_FETCH }}';
        var LegalteamConfig_type_static = '{{ \App\Models\LegalteamConfig::TYPE_STATIC }}';

        var legal_tem_config_types = '{{ json_encode($legal_tem_config_types) }}';

        $("#project_type_id").change(function() {
            $(".loading").show();
            let type_id = $(this).val();
            let field_text = $(this).find("option:selected").text();
            $("#fv_project_type_name").val(field_text);
            $.ajax({
                url: "{{ url('/admin/get_project_section') }}/" + type_id,
                success: function(html) {
                    $(".loading").hide();
                    $("#section_selector").html(html);
                }
            });
        });
        $("#section_selector").change(function() {
            $(".loading").show();
            let type_id = $("#project_type_id").val();
            let selector = $(this).val();
            let field_text = $(this).find("option:selected").text();
            $("#fv_section_name").val(field_text);
            $.ajax({
                url: "{{ url('/admin/get_project_section_field') }}/" + type_id + "/" + selector,
                success: function(html) {
                    $(".loading").hide();
                    $("#section_selector_field").html(html);
                }
            });
        });

        $("#section_selector_field").change(function() {
            let field_id = $(this).val();
            let field_text = $(this).find("option:selected").text();
            $("#fv_person_field_name").val(field_text);
        });

        $(".toggle-config").click(function() {
            $(".loading").show();
            let id = $(this).attr('id');
            $.ajax({
                url: "{{ url('/admin/update_legalteam_config') }}",
                type: "POST",
                data: {
                    type: id,
                    "_token": "{{ csrf_token() }}"
                },
                success: function(html) {
                    $(".loading").hide();
                    $("#section_selector_field").html(html);
                }
            });
            if (id == "org-roles") {
                $("#person-fields-form").hide();
                $("#org-roles-form").show();
                $("#org-roles").addClass("btn-success");
                $("#person-fields").addClass("btn-light");
                $("#org-roles").removeClass("btn-light");
                $("#person-fields").removeClass("btn-success");
            } else {
                $("#person-fields-form").show();
                $("#org-roles-form").hide();
                $("#person-fields").addClass("btn-success");
                $("#org-roles").addClass("btn-light");
                $("#person-fields").removeClass("btn-light");
                $("#org-roles").removeClass("btn-success");
            }
        });

        $(".delete-config-person").click(function() {
            let that = $(this);
            let tenant_id = $(this).attr("data-id");
            let fv_project_type_id = $(this).attr("fv_project_type_id");
            let fv_section_id = $(this).attr("fv_section_id");
            let fv_person_field_id = $(this).attr("fv_person_field_id");

            var formdata = {
                tenant_id: tenant_id,
                fv_project_type_id: fv_project_type_id,
                fv_section_id: fv_section_id,
                fv_person_field_id: fv_person_field_id
            };

            formdata._token = "{{ csrf_token() }}";
            formdata.type = "delete-data";

            Swal.fire({
                title: 'Are you sure to delete?',
                showDenyButton: true,
                showCancelButton: false,
                confirmButtonText: 'Delete',
                denyButtonText: `Cancel`,
            }).then((result) => {
                if (result.isConfirmed) {
                    $(".loading").show();
                    $.ajax({
                        url: "{{ url('/admin/update_legalteam_config') }}",
                        type: "POST",
                        data: formdata,
                        success: function(json) {
                            $(".loading").hide();
                            that.parent().parent().remove();
                            Swal.fire({
                                text: "Setting saved successfully!",
                                icon: "success",
                            });
                        }
                    });
                }
            });
        });

        $(".save-config-person").click(function() {
            var tr = $(this).closest('tr');
            let name = $(this).parent().parent().find(".role_name").val();
            let email = $(this).parent().parent().find(".email").val();
            let phone = $(this).parent().parent().find(".phone").val();
            var fetchType = tr.find('.btn-fetch-field').hasClass('btn-warning') ? LegalteamConfig_type_fetch :
                LegalteamConfig_type_static;
            if (fetchType == LegalteamConfig_type_static) {
                var nameMsg = validateName(name);
                if (nameMsg != '') {
                    Swal.fire({
                        text: nameMsg,
                        icon: "error",
                    });
                    return false;
                }
            }

            $(".loading").show();
            let tenant_id = $(this).attr("data-id");
            let fv_project_type_id = $(this).attr("fv_project_type_id");
            let fv_section_id = $(this).attr("fv_section_id");
            let fv_person_field_id = $(this).attr("fv_person_field_id");
            let is_static_name = $(this).parent().parent().find(".is_static_name").val();
            let is_enable_feedback = $(this).parent().parent().find(".is_enable_feedback").val();
            let is_enable_email = $(this).parent().parent().find(".is_enable_email").val();
            let is_enable_phone = $(this).parent().parent().find(".is_enable_phone").val();
            let is_override_phone = $(this).parent().parent().find(".is_override_phone").val();
            let is_override_email = $(this).parent().parent().find(".is_override_email").val();
            let override_phone = $(this).parent().parent().find(".override_phone").val();
            let override_email = $(this).parent().parent().find(".override_email").val();
            let override_name = $(this).parent().parent().find(".override_name").val();


            if (!$(this).parent().parent().find(".is_static_name").is(":checked")) {
                is_static_name = 0;
            }
            if (!$(this).parent().parent().find(".is_enable_feedback").is(":checked")) {
                is_enable_feedback = 0;
            }
            if (!$(this).parent().parent().find(".is_enable_phone").is(":checked")) {
                is_enable_phone = 0;
            }
            if (!$(this).parent().parent().find(".is_enable_email").is(":checked")) {
                is_enable_email = 0;
            }
            if (!$(this).parent().parent().find(".is_override_phone").is(":checked")) {
                is_override_phone = 0;
            }
            if (!$(this).parent().parent().find(".is_override_email").is(":checked")) {
                is_override_email = 0;
            }
            if (fetchType == LegalteamConfig_type_static) {
                override_phone = phone;
                override_email = email;
                override_name = name;
            }
            var formdata = {
                tenant_id: tenant_id,
                fv_project_type_id: fv_project_type_id,
                fv_section_id: fv_section_id,
                fv_person_field_id: fv_person_field_id,
                is_static_name: is_static_name,
                is_enable_feedback: is_enable_feedback,
                is_enable_phone: is_enable_phone,
                is_enable_email: is_enable_email,
                is_override_phone: is_override_phone,
                is_override_email: is_override_email,
                override_phone: override_phone,
                override_email: override_email,
                override_name: override_name,
                fetchType: fetchType
            };

            formdata._token = "{{ csrf_token() }}";
            formdata.type = "update-data";

            $.ajax({
                url: "{{ url('/admin/update_legalteam_config') }}",
                type: "POST",
                data: formdata,
                success: function(json) {
                    $(".loading").hide();
                    Swal.fire({
                        text: "Setting saved successfully!",
                        icon: "success",
                    });
                }
            });
        });

        $(".is_static_name").change(function() {
            if ($(this).is(":checked")) {
                $(this).parent().parent().find(".override_name").parent().show();
            } else {
                $(this).parent().parent().find(".override_name").parent().hide();
            }
        });

        $(".is_override_phone").change(function() {
            if ($(this).is(":checked")) {
                $(this).parent().parent().find(".is_override_phone").prop('checked', true);
                $(this).parent().parent().find(".override_phone").parent().show();
            } else {
                $(this).parent().parent().find(".is_override_phone").prop('checked', false);
                $(this).parent().parent().find(".override_phone").parent().hide();
            }
        });
        $(".is_override_email").change(function() {
            let is_override_phone = $(this).val();
            if ($(this).is(":checked")) {
                $(this).parent().parent().find(".is_override_email").prop('checked', true);
                $(this).parent().parent().find(".override_email").parent().show();
            } else {
                $(this).parent().parent().find(".is_override_email").prop('checked', false);
                $(this).parent().parent().find(".override_email").parent().hide();
            }
        });

        $("#config_role").on('change', function() {
            if ($("#config_role").is(':checked')) {
                $("#config_role").val('1');
                $("#person-fields-form").css('display', 'none');
                $("#org-roles-form").css('display', 'block');
            } else {
                $("#config_role").val('0');
                $("#person-fields-form").css('display', 'block');
                $("#org-roles-form").css('display', 'none');
            }
        });
    </script>
@endsection
