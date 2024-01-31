@extends('admin.layouts.default')

@section('title', 'VineConnect Admin - Team Setup and Configuration')

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
            border-radius:5px;
            height:34px;
            user-select: none;
        }
        .toggleSwitch * {
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            box-sizing: border-box;
        }
        .toggleSwitch label,
        .toggleSwitch > span {
            line-height: 20px;
            height: 20px;
            vertical-align: middle;
        }
        .toggleSwitch input:focus ~ a,
        .toggleSwitch input:focus + label {
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
        .toggleSwitch > span {
            position: absolute;
            left: 0;
            width: calc(100% - 6px);
            margin: 0;
            text-align: left;
            white-space: nowrap;
            margin:0 3px;
        }
        .toggleSwitch > span span {
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
            width:40%;
            text-align: center;
            line-height:34px;
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
            background-color: #666;
            border-radius: 4px;
            -webkit-transition: all 0.2s ease-out;
            -moz-transition: all 0.2s ease-out;
            transition: all 0.2s ease-out;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }
        .toggleSwitch > span span:first-of-type {
            color: #FFF;
            opacity: 1;
            left: 0;
            margin: 0;
            width: 50%;
        }
        .toggleSwitch > span span:last-of-type {
            left:auto;
            right:0;
            color: #999;
            margin: 0;
            width: 50%;
        }
        .toggleSwitch > span:before {
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
        .toggleSwitch input:checked ~ a {
            left: calc(50% - 3px);
        }
        .toggleSwitch input:checked ~ span:before {
            /* border-color: #0097D1;
            box-shadow: inset 0 0 0 30px #0097D1; */
        }
        .toggleSwitch input:checked ~ span span:first-of-type {
            left:0;
            color:#999;
        }
        .toggleSwitch input:checked ~ span span:last-of-type {
            /* opacity: 1;
            color: #fff;	 */
            color:#FFF;
        }
        /* Switch Sizes */
        .toggleSwitch.large {
            width: 60px;
            height: 27px;
        }
        .toggleSwitch.large a {
            width: 27px;
        }
        .toggleSwitch.large > span {
            height: 29px;
            line-height: 28px;
        }
        .toggleSwitch.large input:checked ~ a {
            left: 41px;
        }
        .toggleSwitch.large > span span {
            font-size: 1.1em;
        }
        .toggleSwitch.large > span span:first-of-type {
            left: 50%;
        }
        .toggleSwitch.xlarge {
            width: 80px;
            height: 36px;
        }
        .toggleSwitch.xlarge a {
            width: 36px;
        }
        .toggleSwitch.xlarge > span {
            height: 38px;
            line-height: 37px;
        }
        .toggleSwitch.xlarge input:checked ~ a {
            left: 52px;
        }
        .toggleSwitch.xlarge > span span {
            font-size: 1.4em;
        }
        .toggleSwitch.xlarge > span span:first-of-type {
            left: 50%;
        }
    </style>

    <!--begin::Subheader-->
    <div class="subheader py-2 py-lg-4 subheader-solid" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
            <!--begin::Info-->
            <div class="d-flex align-items-center flex-wrap mr-2">
                <!--begin::Page Title-->
                <h4 class="text-dark font-weight-bold mt-2 mb-2 mr-5">Team Setup &amp; Configurations</h4>
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
                            <h5 class="card-title mt-7">Display Your Project's Team Assignments in Client Portal</h5>
                        </div>
                        <div class="card-body">
                            <div class="pg_content">
                                <p><b>Instructions:</b> If you use the Team tab in Filevine, use the default selection
                                    for "Team Config by Org Roles" which will tell VineConnect which roles you want
                                    displayed in the Client Portal. For firms that rely on Person Fields in Static
                                    Sections, you can toggle "Legal Team by Person Fields" instead.The difference
                                    between "Fetch" and "Static" is simply that "Fetch" will dynamically reference the
                                    team member however it is set in your Filevine project, while a "Static" selection
                                    allows you to set field values that don't change from project to project.If you
                                    would like to gather feedback from your clients on individual Legal Team members, be
                                    sure to checkmark Enable Feedback. Any feedback received will be logged in the
                                    Client Usage Dashboard.</p>
                                {{--<p>For firms that rely on people fields in static sections, you can toggle the selection for "Legal Team by Person Fields" to enable this configuration. From there you will tell VineConnect which people fields represent your legal team. You can configure as many people fields as you would like for as many Project Type templates you may have.</p>--}}
                                {{--<p>Either way you choose, you'll want to configure every role or person field for which a legal team member could be assigned.</p>--}}
                                {{--<p>The difference between "Fetch" and "Static" is simply that "Fetch" will dynamically reference the team member however it is set in your Filevine project, while a "Static" selection allows you to set field values that don't change from project to project. Static fields are great for displaying a managing attorney, or a customer service representative. You can override static settings not just for people, but for phone numbers and email addresses, too.</p>--}}
                                {{--<p>If you would like to gather feedback from your clients on individual Legal Team members, be sure to checkmark <b>Enable Feedback</b>. Any feedback received will be logged in the Client Usage Dashboard.</p>--}}
                                <div class="callout_subtle lightgrey"><i class="fas fa-link"
                                                                         style="color:#383838;padding-right:5px;"></i>
                                    Support Article: <a
                                        href="https://intercom.help/vinetegrate/en/articles/5804695-configure-your-legal-team"
                                        target="_blank"/>Legal Team Configurations</a></div>
                            </div><!-- pg_content" -->
                            <div class="overlay loading"></div>
                            <div class="spinner-border text-primary loading" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="mt-5 switch-div">
                                        @php
                                        $class = "btn-light";
                                        if($legal_team_by_role) $class = "btn-success";
                                        @endphp
                                        <div class="custom-control custom-switch custom-switch-md pl-0">
                                            {{--<label class="custom-control-label ml-7 pl-4" for="person-fields">Config by--}}
                                                {{--Person Field</label>--}}
                                            {{--<input type="checkbox"--}}
                                                   {{--@if($legal_team_by_role){{ 'checked'}} @endif class="custom-control-input toggle-config"--}}
                                                   {{--name="org-roles" id="org-roles">--}}
                                            {{--<a></a>--}}
                                            {{--<span>--}}
                                                {{--<span class="left-span">Enabled</span>--}}
                                                {{--<span class="right-span">Disabled</span>--}}
                                            {{--</span>--}}
                                            {{--<label class="custom-control-label ml-7 pl-4" for="org-roles">Config by Org--}}
                                                {{--Role</label>--}}


                                            <label class="toggleSwitch nolabel" onclick="">
                                                <input type="checkbox" id="role" @if($legal_team_by_role){{ 'checked'}} @endif />
                                                <a></a>
                                                <span>
                                                    <span class="left-span">Config by Person Field</span>
                                                    <span class="right-span">Config by Org Role</span>
                                                </span>
                                            </label>
                                        </div>

                                        {{--<button type="button" class="btn {{ $class }} toggle-config" id="org-roles">Config by Org Role</button>--}}
                                        {{--@php--}}
                                        {{--$class = "btn-light";--}}
                                        {{--if(!$legal_team_by_role) $class = "btn-success";--}}
                                        {{--@endphp--}}
                                        {{--<button type="button" class="btn {{ $class }} toggle-config" id="person-fields">Config by Person Field</button>--}}
                                    </div>
                                </div>
                                {{--<div class="col-sm-4">--}}
                                    {{--<div class="mt-8 switch-div">--}}
                                        {{--<div class="custom-control custom-switch custom-switch-md pl-0">--}}
                                            {{--<input type="radio"--}}
                                                   {{--@if(!$legal_team_by_role){{ 'checked'}} @endif class="custom-control-input toggle-config"--}}
                                                   {{--name="person-fields" id="person-fields">--}}
                                            {{--<label class="custom-control-label ml-7 pl-4" for="person-fields">Config by--}}
                                                {{--Person Field</label>--}}
                                        {{--</div>--}}
                                    {{--</div>--}}
                                {{--</div>--}}

                                <div class="col-sm-4">
                                    <div style="margin: 20px 0">
                                        <div class="form-group row">
                                            <label for="legalTeamTitle"
                                                   class="col-auto col-form-label col-form-label-sm">Override
                                                Title:</label>
                                            <div class="col-sm-8">
                                                <input placeholder="Title" class="form-control" type="text" name="title"
                                                       id="legalTeamTitle" value="{{$tenant_override_title}}"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @php
                                $style = "display:none;";
                                if(!$legal_team_by_role) $style = "display:block;";
                            @endphp
                            <div class="login-form validate-form " id="person-fields-form" style="{{ $style }}">
                                <table class="table w-100 repeater">
                                    <form action="{{ url('/admin/legalteam_person') }}" method="post">
                                        {!! csrf_field() !!}

                                        <tbody data-repeater-list="group-a">
                                        <tr>
                                            <td style="width:20% !important">
                                                <input type="hidden" name="type" class="typeField" value="fetch">
                                                <label for="">Choose to fetch your Legal Team by Person Fields <br>or
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
                                                    <input type="text" name="override_phone" class="form-control phone"
                                                           placeholder="Enter phone">
                                                </div>
                                            </td>
                                            <td style="width:18% !important" class="static_element_field d-none">
                                                <div class="form-group">
                                                    <label>Email</label>
                                                    <input type="email" name="override_email" class="form-control email"
                                                           placeholder="Enter email">
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
                                                        @if(isset($fv_project_type_list['items']))
                                                            @foreach($fv_project_type_list['items'] as $type)
                                                                <option
                                                                    value="{{ $type['projectTypeId']['native'] }}">{{ $type['name'] }}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </div>
                                            </td>
                                            <td style="width:18% !important" class="fetch_element_field">
                                                <div class="form-group">
                                                    <label>Project Type Section</label>
                                                    <input type="hidden" name="fv_section_name" id="fv_section_name">
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
                                                <label>Order of Appearance</label>
                                                <div class="form-group">
                                                    <button type="submit" class="btn btn-success addButton">ADD</button>
                                                </div>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </form>
                                </table>
                                <table class="table table-person w-100">
                                    <tbody>
                                    @foreach($data_person_config as $config)
                                        @php
                                            $class = "";
                                            if(empty($config->fv_person_field_name)){
                                                $class = "disabled";
                                            }
                                        @endphp
                                        <tr class="table_person">
                                            <td style="width:20% !important">
                                                <input type="hidden" name="type" class="typeField"
                                                       value="{{ $config->type }}">
                                                <input type="hidden" class="id" value="{{ $config->id }}">
                                                <label for="">Choose to fetch your Legal Team by Person Fields <br>or
                                                    statically set your Team</label>
                                                <br>
                                                <button type="button"
                                                        class="{{$class}} btn-fetch-field btn ml-auto mt-1 {{ $config->type == \App\Models\LegalteamPersonConfig::TYPE_FETCH ? 'btn-warning' : 'btn-secondary' }} btn-md"
                                                        style="float:left;">FETCH
                                                </button>
                                                <button type="button"
                                                        class="{{$class}} btn-static-field btn ml-3 mt-1 {{ $config->type == \App\Models\LegalteamPersonConfig::TYPE_STATIC ? 'btn-warning' : 'btn-secondary' }}  btn-md"
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
                                                    <input type="hidden" class="id-person" value="{{ $config->id }}">
                                                    <p class="mt-3 mb-1">
                                                        {{$config->fv_person_field_name}}
                                                    </p>
                                                </div>
                                                @php
                                                    $checked = "";
                                                    $style = "display:none;"
                                                @endphp
                                                @if($config->is_static_name)
                                                    @php
                                                        $checked = "checked";
                                                        $style = "display:block;"
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
                                                    $checkedzero = "";
                                                    $checkedone = "";
                                                    $style = "display:none;"
                                                @endphp
                                                @if($config->is_enable_phone)
                                                    @php
                                                        $checkedzero = "checked";
                                                    @endphp
                                                @endif
                                                @if($config->is_override_phone)
                                                    @php
                                                        $checkedone = "checked";
                                                        $style = "display:block;"
                                                    @endphp
                                                @endif
                                                <div class="form-group">
                                                    <input type="checkbox" {{$checkedzero}} name="is_enable_phone"
                                                           value="1"
                                                           class="is_enable_phone form-control goog-check float-left">
                                                    <label class="mt-2 ml-2">
                                                        Enable Phone Number
                                                    </label>
                                                </div>
                                                <div class="form-group">
                                                    <input type="checkbox" {{$checkedone}} name="is_override_phone"
                                                           value="1"
                                                           class="is_override_phone form-control goog-check float-left"
                                                           placeholder="Phone Number">
                                                    <label class="mt-2 ml-2">
                                                        Override Phone
                                                    </label>
                                                </div>
                                                <div class="form-group" style="{{$style}}">
                                                    <input type="text" name="override_phone"
                                                           value="{{ $config->override_phone }}"
                                                           class="override_phone form-control">
                                                </div>
                                            </td>
                                            <td style="width:17%;"
                                                class="fetch_element_field {{ $config->type == \App\Models\LegalteamPersonConfig::TYPE_STATIC ? 'd-none' : null }}">
                                                @php
                                                    $checkedzero = "";
                                                    $checkedone = "";
                                                    $style = "display:none;"
                                                @endphp
                                                @if($config->is_enable_email)
                                                    @php
                                                        $checkedzero = "checked";
                                                    @endphp
                                                @endif
                                                @if($config->is_override_email)
                                                    @php
                                                        $checkedone = "checked";
                                                        $style = "display:block;"
                                                    @endphp
                                                @endif
                                                <div class="form-group">
                                                    <input type="checkbox" {{$checkedzero}} name="is_enable_email"
                                                           value="1"
                                                           class="is_enable_email form-control goog-check float-left">
                                                    <label class="mt-2 ml-2">
                                                        Ennable Email Address
                                                    </label>
                                                </div>
                                                <div class="form-group">
                                                    <input type="checkbox" {{$checkedone}} name="is_override_email"
                                                           value="1"
                                                           class="is_override_email form-control goog-check float-left">
                                                    <label class="mt-2 ml-2">
                                                        Override Email Address
                                                    </label>
                                                </div>
                                                <div class="form-group" style="{{$style}}">
                                                    <input type="text" name="override_email"
                                                           value="{{ $config->override_email }}"
                                                           class="override_email form-control"
                                                           placeholder="Email Address">
                                                </div>
                                            </td>
                                            <td>
                                                @php
                                                    $checked = "";
                                                @endphp
                                                @if($config->is_enable_feedback)
                                                    @php
                                                        $checked = "checked";
                                                    @endphp
                                                @endif
                                                <div class="form-group">
                                                    <input type="checkbox" {{$checked}} name="is_enable_feedback"
                                                           value="1"
                                                           class="is_enable_feedback form-control goog-check float-left">
                                                    <label class="mt-2 ml-2">
                                                        OK
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <label>Order of <br> Appearance</label>
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
                                $style = "display:none;";
                                if($legal_team_by_role) $style = "display:block;";
                            @endphp
                            <div class="login-form validate-form " id="org-roles-form" style="{{ $style }}">
                                <table class="table table-role w-100 repeater">
                                    <tbody data-repeater-list="group-a">
                                    @forelse($data as $item)
                                        <tr data-repeater-item class="table_row">
                                            <td style="width:25% !important">
                                                <input type="hidden" class="id" value="{{ $item->id }}">
                                                <label for="">Choose to fetch your Org's Team Roles <br>or statically
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
                                                        @foreach($legal_tem_config_types as $role_item)
                                                            <option
                                                                value="{{ $role_item['orgRoleId']['native'] }}" {{ ($role_item['orgRoleId']['native'] == @$item->fv_role_id) ? 'selected' : '' }}>{{ $role_item['name'] }}</option>
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
                                                           {{ $item->is_follower_required == \App\Models\LegalteamConfig::YES ? 'checked' : null }} name="enable_feedback">
                                                </div>
                                            </td>
                                            <td style="width:10% !important"
                                                class="fetch_element {{ $item->type == \App\Models\LegalteamConfig::TYPE_STATIC ? 'd-none' : null }}">
                                                <div class="form-group">
                                                    <label>Enable <br> Email</label>
                                                    <input type="checkbox" class="form-control enable_email goog-check"
                                                           {{ $item->is_enable_email == \App\Models\LegalteamConfig::YES ? 'checked' : null }} name="enable_feedback">

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
                                                           {{ $item->is_enable_feedback == \App\Models\LegalteamConfig::YES ? 'checked' : null }} name="enable_feedback">
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
                                                <label for="">Choose to fetch your Org's Team Roles <br>or statically
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
                                                        @foreach($legal_tem_config_types as $list)
                                                            @if(isset($list['orgRoleId'],$list['orgRoleId']['native'],$list['name']))
                                                                <option
                                                                    value="{{ $list['orgRoleId']['native'] }}">{{ $list['name'] }}</option>
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
                                                    <input type="checkbox" class="form-control enable_email goog-check"
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
                                    <tfoor>
                                        <tr>
                                            <td colspan="100">
                                                <button type="button" class="btn btn-success mr-2 save_all">Save All
                                                </button>
                                                <button type="button" data-repeater-create class="btn btn-warning mr-2">
                                                    ADD NEW
                                                </button>
                                            </td>
                                        </tr>
                                    </tfoor>
                                </table>
                            </div>
                        </div><!-- card_body end -->
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
        @php
            $success = "";
            $error = "";
            if(session()->has('success')){
                $success = session()->get('success');
            }
            if(session()->has('error')){
                $error = session()->get('error');
            }
        @endphp
        @stop
        @section('scripts')
            <style>
                .w-60 {
                    width: 60%;
                }
            </style>
            <script src="https://code.jquery.com/ui/1.13.0/jquery-ui.js"></script>
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
                var route_legal_team_destroy = '{{ route('legal_team_destroy', ['subdomain' => $subdomain]) }}';
                var route_legal_team_sort = '{{ route('legal_team_sort', ['subdomain' => $subdomain]) }}';
                var route_legal_team_person_sort = '{{ route('legal_team_person_sort', ['subdomain' => $subdomain]) }}';
                var route_legal_team_store = '{{ route('legal_team_store', ['subdomain' => $subdomain]) }}';
                var route_legal_team_all_store = '{{ route('legal_team_all_store', ['subdomain' => $subdomain]) }}';
                var update_all_legalteam_config = '{{route('update_all_legalteam_config', ['subdomain' => $subdomain])}}'
                var csrf_token = '{{ csrf_token() }}';


                var LegalteamConfig_yes = '{{ \App\Models\LegalteamConfig::YES }}';
                var LegalteamConfig_no = '{{ \App\Models\LegalteamConfig::NO }}';
                var LegalteamConfig_type_fetch = '{{ \App\Models\LegalteamConfig::TYPE_FETCH }}';
                var LegalteamConfig_type_static = '{{ \App\Models\LegalteamConfig::TYPE_STATIC }}';

                var legal_tem_config_types = '{{json_encode($legal_tem_config_types)}}';

                $("#project_type_id").change(function () {
                    $(".loading").show();
                    let type_id = $(this).val();
                    let field_text = $(this).find("option:selected").text();
                    $("#fv_project_type_name").val(field_text);
                    $.ajax({
                        url: "{{ url('/admin/get_project_section') }}/" + type_id,
                        success: function (html) {
                            $(".loading").hide();
                            $("#section_selector").html(html);
                        }
                    });
                });
                $("#section_selector").change(function () {
                    $(".loading").show();
                    let type_id = $("#project_type_id").val();
                    let selector = $(this).val();
                    let field_text = $(this).find("option:selected").text();
                    $("#fv_section_name").val(field_text);
                    $.ajax({
                        url: "{{ url('/admin/get_project_section_field') }}/" + type_id + "/" + selector,
                        success: function (html) {
                            $(".loading").hide();
                            $("#section_selector_field").html(html);
                        }
                    });
                });

                $("#section_selector_field").change(function () {
                    let field_id = $(this).val();
                    let field_text = $(this).find("option:selected").text();
                    $("#fv_person_field_name").val(field_text);
                });

                $(".toggle-config").click(function () {
                    $(".loading").show();
                    let id = $(this).attr('id');
                    $.ajax({
                        url: "{{ url('/admin/update_legalteam_config') }}",
                        type: "POST",
                        data: {type: id, "_token": "{{ csrf_token() }}"},
                        success: function (html) {
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

                $(".delete-config-person").click(function () {
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
                                success: function (json) {
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

                $(".save-config-person").click(function () {
                    var tr = $(this).closest('tr');
                    let name = $(this).parent().parent().find(".role_name").val();
                    let email = $(this).parent().parent().find(".email").val();
                    let phone = $(this).parent().parent().find(".phone").val();
                    var fetchType = tr.find('.btn-fetch-field').hasClass('btn-warning') ? LegalteamConfig_type_fetch : LegalteamConfig_type_static;
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
                        success: function (json) {
                            $(".loading").hide();
                            Swal.fire({
                                text: "Setting saved successfully!",
                                icon: "success",
                            });
                        }
                    });
                });

                $(".is_static_name").change(function () {
                    if ($(this).is(":checked")) {
                        $(this).parent().parent().find(".override_name").parent().show();
                    } else {
                        $(this).parent().parent().find(".override_name").parent().hide();
                    }
                });

                $(".is_override_phone").change(function () {
                    if ($(this).is(":checked")) {
                        $(this).parent().parent().find(".is_override_phone").prop('checked', true);
                        $(this).parent().parent().find(".override_phone").parent().show();
                    } else {
                        $(this).parent().parent().find(".is_override_phone").prop('checked', false);
                        $(this).parent().parent().find(".override_phone").parent().hide();
                    }
                });
                $(".is_override_email").change(function () {
                    let is_override_phone = $(this).val();
                    if ($(this).is(":checked")) {
                        $(this).parent().parent().find(".is_override_email").prop('checked', true);
                        $(this).parent().parent().find(".override_email").parent().show();
                    } else {
                        $(this).parent().parent().find(".is_override_email").prop('checked', false);
                        $(this).parent().parent().find(".override_email").parent().hide();
                    }
                });

                $("#role").on('change', function () {
                    if ($("#role").is(':checked')) {
                        $("#person-fields-form").css('display','none');
                        $("#org-roles-form").css('display','block');
                    }else{
                        $("#person-fields-form").css('display','block');
                        $("#org-roles-form").css('display','none');
                    }
                });

            </script>
            <script type="text/javascript" src="{{ asset('../js/admin/legal_team.js?v=1.0') }}"></script>
@endsection
