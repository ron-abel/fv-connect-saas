@extends('client.layouts.default')

@section('title', 'VineConnect Client Portal - Client Dashboard')

@section('content')
    <style>
        .tab-content p {
            background-color: #0f2c4d !important;
            margin: revert !important;
            line-height: revert !important;
            font-weight: revert !important;
        }

        .panel-body span {
            padding: revert !important;
            display: inline !important;
        }

        .language-select {
            cursor: pointer;
            position: relative;
            width: 20%;
            font-weight: 500;
            font-size: 16px;
            line-height: 26px;
        }

        .language-icon {
            max-width: 20px;
            margin-right: 4px;
        }

        #language-dropdown {
            position: absolute;
            background: #0F2C4D;
            padding: 2px;
        }

        #language-dropdown li {
            margin: 5px 0px;
            padding: 5px;
        }

        #language-dropdown li a:hover {
            color: #ffc107 !important
        }

        @media screen and (max-width: 768px) {
            .language-select {
                width: 55%;
            }
        }
    </style>
    <div class="client-portal-body">
        <div class="accordion-started accordion-bral row">
            @foreach ($lookup_data as $key => $single_lookup_data)
                <div class="w-100">
                    <input class="ac-input" id="ac-{{ $key }}" name="accordion-1" type="radio"
                        {{ $single_lookup_data['active_project_id'] == $single_lookup_data['project_id'] ? 'checked' : '' }}>
                    @if (isset($single_lookup_data['config_details']))
                        @if (isset($single_lookup_data['project_override_name']) && !empty($single_lookup_data['project_override_name']))
                            <label class="ac-label bg-title"
                                for="ac-{{ $key }}"><span>{{ $single_lookup_data['project_override_name'] }}</span><i></i></label>
                        @elseif (isset($single_lookup_data['results']['project']['projectName']))
                            <label class="ac-label bg-title"
                                for="ac-{{ $key }}"><span>{{ $single_lookup_data['results']['project']['projectName'] }}</span><i></i></label>
                        @else
                            <label class="ac-label bg-title" for="ac-{{ $key }}"><span></span><i></i></label>
                        @endif
                        <div class="article ac-content">
                            <header class="bg-logo">
                                <div class="container-fluid">
                                    <div class="row align-items-center justify-content-between">
                                        <div class="col-md-4 col-sm-0">
                                            @if (isset($single_lookup_data['last_login']))
                                                <p>{{ __('Last Login:') }} {{ $single_lookup_data['last_login'] }}</time>
                                                </p>
                                            @else
                                                <p>{{ __('Last Login:') }} </time></p>
                                            @endif
                                            <span class="icon_menu" onclick="openNav();openNav3()"><img
                                                    src="{{ asset('img/client//menu.png') }}" alt=""></span>
                                        </div>
                                        <div class="col-md-4 col-sm-5 logo-cp">
                                            @if (isset($single_lookup_data['config_details']->logo))
                                                <a href="/lookup/{{ $lookup_project_id }}"
                                                    class="d-flex align-items-center">
                                                    <img src="{{ asset('uploads/client_logo/' . $single_lookup_data['config_details']->logo) }}"
                                                        alt="{{ __('Logo') }}" class="login-logo">
                                                </a>
                                            @else
                                                <a href="/lookup/{{ $lookup_project_id }}"
                                                    class="d-flex align-items-center">
                                                    <img src="{{ asset('img/client/vineconnect_logo.png') }}"
                                                        alt="VineConnect Logo" class="login-logo">
                                                </a>
                                            @endif
                                        </div>
                                        <div class="col-md-4 col-sm-5 d-flex justify-content-end align-items-center">
                                            <div class="language-select" id="language-select">
                                                @if (!is_null(session('lang')))
                                                    @if (session('lang') == 'es')
                                                        <div class="text-lang-color text-white">
                                                            Español <i class="fas fa-angle-down"></i>
                                                        </div>
                                                    @elseif(session('lang') == 'fr')
                                                        <div class="text-lang-color text-white">
                                                            Français <i class="fas fa-angle-down"></i>
                                                        </div>
                                                    @elseif(session('lang') == 'pt')
                                                        <div class="text-lang-color text-white">
                                                            Português <i class="fas fa-angle-down"></i>
                                                        </div>
                                                    @else
                                                        <div class="text-lang-color text-white">
                                                            English <i class="fas fa-angle-down"></i>
                                                        </div>
                                                    @endif
                                                @else
                                                    <div class="text-lang-color text-white">
                                                        English <i class="fas fa-angle-down"></i>
                                                    </div>
                                                @endif
                                                <ul id="language-dropdown">
                                                    <li><a
                                                            href="{{ route('lookup', ['lookup_project_id' => $single_lookup_data['active_project_id'], 'subdomain' => session()->get('subdomain'), 'lang' => 'en']) }}">English</a>
                                                    </li>
                                                    <li><a
                                                            href="{{ route('lookup', ['lookup_project_id' => $single_lookup_data['active_project_id'], 'subdomain' => session()->get('subdomain'), 'lang' => 'es']) }}">Español</a>
                                                    </li>
                                                    <li><a
                                                            href="{{ route('lookup', ['lookup_project_id' => $single_lookup_data['active_project_id'], 'subdomain' => session()->get('subdomain'), 'lang' => 'fr']) }}">Français</a>
                                                    </li>
                                                    <li><a
                                                            href="{{ route('lookup', ['lookup_project_id' => $single_lookup_data['active_project_id'], 'subdomain' => session()->get('subdomain'), 'lang' => 'pt']) }}">Português</a>
                                                    </li>
                                                </ul>
                                            </div>

                                            <div class="client-header-dropdown" onclick="showDropdown()">
                                                <svg id="client-header-dropdown1" width="50" height="50"
                                                    viewBox="0 0 50 50" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <rect width="50" height="50" rx="6" fill="{{$config->color_main ?? '#26A9DF'}}" />
                                                    <rect x="20" y="16" width="3" height="3"
                                                        fill="white" />
                                                    <rect x="13" y="16" width="3" height="3"
                                                        fill="white" />
                                                    <rect x="13" y="23" width="3" height="3"
                                                        fill="white" />
                                                    <rect x="13" y="30" width="3" height="3"
                                                        fill="white" />
                                                    <rect x="20" y="23" width="3" height="3"
                                                        fill="white" />
                                                    <rect x="20" y="30" width="3" height="3"
                                                        fill="white" />
                                                    <rect x="27" y="16" width="3" height="3"
                                                        fill="white" />
                                                    <rect x="27" y="23" width="3" height="3"
                                                        fill="white" />
                                                    <rect x="27" y="30" width="3" height="3"
                                                        fill="white" />
                                                    <rect x="34" y="16" width="3" height="3"
                                                        fill="white" />
                                                    <rect x="34" y="23" width="3" height="3"
                                                        fill="white" />
                                                    <rect x="34" y="30" width="3" height="3"
                                                        fill="white" />
                                                </svg>
                                                <svg class="d-none" id="client-header-dropdown2" width="50"
                                                    height="50" viewBox="0 0 50 50" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <path
                                                        d="M0 6C0 2.68629 2.68629 0 6 0H44C47.3137 0 50 2.68629 50 6V50H0V6Z"
                                                        fill="white" />
                                                    <rect x="20" y="16" width="3" height="3"
                                                        fill="{{$config->color_main ?? '#26A9DF'}}" />
                                                    <rect x="13" y="16" width="3" height="3"
                                                        fill="{{$config->color_main ?? '#26A9DF'}}" />
                                                    <rect x="13" y="23" width="3" height="3"
                                                        fill="{{$config->color_main ?? '#26A9DF'}}" />
                                                    <rect x="13" y="30" width="3" height="3"
                                                        fill="{{$config->color_main ?? '#26A9DF'}}" />
                                                    <rect x="20" y="23" width="3" height="3"
                                                        fill="{{$config->color_main ?? '#26A9DF'}}" />
                                                    <rect x="20" y="30" width="3" height="3"
                                                        fill="{{$config->color_main ?? '#26A9DF'}}" />
                                                    <rect x="27" y="16" width="3" height="3"
                                                        fill="{{$config->color_main ?? '#26A9DF'}}" />
                                                    <rect x="27" y="23" width="3" height="3"
                                                        fill="{{$config->color_main ?? '#26A9DF'}}" />
                                                    <rect x="27" y="30" width="3" height="3"
                                                        fill="{{$config->color_main ?? '#26A9DF'}}" />
                                                    <rect x="34" y="16" width="3" height="3"
                                                        fill="{{$config->color_main ?? '#26A9DF'}}" />
                                                    <rect x="34" y="23" width="3" height="3"
                                                        fill="{{$config->color_main ?? '#26A9DF'}}" />
                                                    <rect x="34" y="30" width="3" height="3"
                                                        fill="{{$config->color_main ?? '#26A9DF'}}" />
                                                </svg>
                                                <ul class="client-header-dropdown-div d-none">
                                                    <li><a href="">{{ __('Home') }}</a></li>
                                                    <li><a href="/lookup/my_team_messages/{{ $lookup_project_id }}">
                                                            {{ __('Messages') }}</a></li>
                                                    <li><a
                                                            href="/lookup/forms/{{ $lookup_project_id }}">{{ __('Submit Forms') }}</a>
                                                    </li>
                                                    <li><a href="/lookup/upload_files/{{ $lookup_project_id }}">
                                                            {{ __('Document Share') }}</a></li>
                                                    @if ($calendar_visibility)
                                                        <li><a href="/lookup/calendar/{{ $lookup_project_id }}">
                                                                {{ __('Calendar') }}</a></li>
                                                    @endif
                                                    <li><a href="/client/logout" class="client-portal-logout">
                                                            {{ __('LogOut') }} <svg width="24" height="24"
                                                                viewBox="0 0 24 24" fill="none"
                                                                xmlns="http://www.w3.org/2000/svg">
                                                                <path
                                                                    d="M3.72458 13.0909H16.3635C16.966 13.0909 17.4544 12.6025 17.4544 12C17.4544 11.3975 16.966 10.9091 16.3635 10.9091H3.72458L5.13505 9.49871C5.56109 9.07274 5.56109 8.38198 5.13505 7.95594C4.70916 7.5299 4.0184 7.5299 3.59229 7.95594L0.319782 11.2285C0.294473 11.2537 0.270618 11.2801 0.247927 11.3077C0.2424 11.3145 0.237673 11.3218 0.232291 11.3287C0.215636 11.3498 0.199273 11.3711 0.184291 11.3935C0.179927 11.4 0.176364 11.4069 0.172073 11.4135C0.157091 11.437 0.1424 11.4606 0.129164 11.4853C0.126618 11.4901 0.124509 11.4953 0.121964 11.5002C0.108218 11.5269 0.0949818 11.5539 0.0834182 11.5818C0.0819636 11.5853 0.0809454 11.5889 0.0795636 11.5924C0.0677091 11.6218 0.0567273 11.6515 0.0474182 11.6821C0.0461818 11.686 0.0455273 11.69 0.0444364 11.6939C0.0356364 11.7239 0.0275636 11.7543 0.0213818 11.7853C0.0195636 11.7946 0.0187636 11.8041 0.0170909 11.8134C0.0125818 11.8391 0.00807273 11.8649 0.00552727 11.8911C0.00181818 11.9271 0 11.9635 0 12C0 12.0365 0.00181818 12.0729 0.00552727 12.109C0.00807273 12.1356 0.0127273 12.1615 0.0171636 12.1875C0.0187636 12.1965 0.0195636 12.2058 0.0213818 12.2147C0.0276364 12.2461 0.0356364 12.2767 0.0445091 12.307C0.0456 12.3106 0.0462545 12.3143 0.0473454 12.3179C0.0567273 12.3487 0.0677091 12.3787 0.0797091 12.4084C0.0810182 12.4116 0.0819636 12.415 0.0833454 12.4182C0.0949818 12.4463 0.108291 12.4735 0.122182 12.5004C0.124582 12.5051 0.126618 12.5101 0.129091 12.5148C0.142473 12.5396 0.157236 12.5634 0.172436 12.5871C0.176509 12.5935 0.18 12.6002 0.184218 12.6065C0.199273 12.629 0.215782 12.6503 0.232364 12.6716C0.237673 12.6784 0.2424 12.6856 0.247855 12.6923C0.270618 12.7199 0.294473 12.7463 0.319709 12.7716L3.59222 16.0441C3.80524 16.2572 4.08444 16.3637 4.36364 16.3637C4.64284 16.3637 4.92204 16.2572 5.13498 16.0441C5.56102 15.6181 5.56102 14.9274 5.13498 14.5013L3.72458 13.0909Z"
                                                                    fill="#EB5757" />
                                                                <path
                                                                    d="M15.0764 3.07629C12.0987 3.07629 9.32819 4.55353 7.66528 7.02779C7.32928 7.52779 7.46222 8.20561 7.96222 8.54168C8.46229 8.87768 9.14004 8.74488 9.47619 8.24473C10.733 6.37455 12.8265 5.25804 15.0764 5.25804C18.7939 5.25811 21.8183 8.28248 21.8183 12C21.8183 15.7175 18.7939 18.7419 15.0764 18.7419C12.8331 18.7419 10.743 17.6304 9.48528 15.7687C9.1479 15.2695 8.46993 15.1383 7.97059 15.4755C7.47139 15.8128 7.34004 16.4909 7.67735 16.9901C9.34142 19.4532 12.1074 20.9237 15.0764 20.9237C19.9969 20.9237 24.0001 16.9206 24.0001 12C24.0001 7.07942 19.9969 3.07629 15.0764 3.07629Z"
                                                                    fill="#EB5757" />
                                                            </svg>
                                                        </a></li>
                                                </ul>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </header>
                            <section>
                                <div class="container-fluid">
                                    <div class="row">
                                        @if (isset($single_lookup_data['notification']))
                                            @foreach ($single_lookup_data['notification'] as $record)
                                                <div class="col-md-12 mt-2 mb-4 notice-bar {{ $record->banner_color }}">
                                                    @if (isset($record->notice_body))
                                                        <span style="margin-top: auto; margin-bottom:auto;"><i
                                                                class="fas fa-exclamation-circle"></i></span>
                                                        <span class="ml-2">{!! $record->notice_body !!}</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    <div class="row cstm-grid justify-content-between">
                                        <div class="col-md-4 col-lg-4 client-body-column">
                                            <div class="client_profile">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h4 class="text-accent">{{ isset($tenant_custom_vital->project_vital_override_title) && !empty($tenant_custom_vital->project_vital_override_title) ? $tenant_custom_vital->project_vital_override_title : __('My Case Vitals') }}
                                                    </h4>
                                                    <div class="right-line"></div>
                                                    <div class="edit_contact" role="button" data-toggle="modal"
                                                        data-target="#vitalContactInfoUpdate"><i
                                                            class="fas fa-pencil-alt"></i></div>
                                                </div>
                                                <div class="bottom-line"></div>
                                                <ul class="client-info">
                                                    @if(isset($tenant_custom_vital->is_show_project_clientname) && $tenant_custom_vital->is_show_project_clientname)
                                                    <li>
                                                        <span> {{__('Client Name:')}}</span>
                                                        @if (isset($single_lookup_data['results']['project']['clientName']))
                                                            <span>{{ $single_lookup_data['results']['project']['clientName'] }}</span>
                                                        @endif
                                                    </li>
                                                    @endif
                                                    @if(isset($tenant_custom_vital->is_show_project_name) && $tenant_custom_vital->is_show_project_name)
                                                    <li>
                                                        <span> {{__('Project Name:')}}</span>
                                                        @if (isset($single_lookup_data['results']['project']['projectName']))
                                                            <span>{{ $single_lookup_data['results']['project']['projectName'] }}</span>
                                                        @endif
                                                    </li>
                                                    @endif
                                                    @if(isset($tenant_custom_vital->is_show_project_id) && $tenant_custom_vital->is_show_project_id)
                                                    <li>
                                                        <span>{{__('Project ID:')}}</span>
                                                        @if (isset($single_lookup_data['results']['projectId']['native']))
                                                            <span
                                                                class="clientProjectId">{{ $single_lookup_data['results']['projectId']['native'] }}</span>
                                                        @endif
                                                    </li>
                                                    @endif
                                                    @if (isset($single_lookup_data['project_top_vitals']) && count($single_lookup_data['project_top_vitals']) > 0)
                                                        @foreach ($single_lookup_data['project_top_vitals'] as $vital)
                                                            @if (isset($vital['is_mail']) && $vital['is_mail'] == 1)
                                                                <li class="position-relative">
                                                                    <span><?= $vital['key'] ?>:</span>
                                                                    <span class="hover-text"> {{ $vital['value'] }} <i
                                                                            class="fas fa-copy ml-1"></i></span>
                                                                    <span role="button"
                                                                        onmouseover="showDataOnHover(this)"
                                                                        onmouseout="showDataOnHover(this)"><a
                                                                            href="mailto:{{ $vital['value'] }}"
                                                                            target="_blank">Send Email</a></span>
                                                                </li>
                                                            @elseif (isset($vital['is_sms_number']) && $vital['is_sms_number'] == 1)
                                                                <li class="position-relative">
                                                                    <span><?= $vital['key'] ?>:</span>
                                                                    <span class="hover-text"> {{ $vital['value'] }} <i
                                                                            class="fas fa-copy ml-1"></i></span>
                                                                    <span role="button"
                                                                        onmouseover="showDataOnHover(this)"
                                                                        onmouseout="showDataOnHover(this)"><a
                                                                            href="sms:{{ $vital['value'] }}"
                                                                            target="_blank">{{ $vital['value'] }}</a></span>
                                                                </li>
                                                            @elseif (isset($vital['is_url']) && $vital['is_url'] == 1)
                                                                <li class="position-relative">
                                                                    <span><?= $vital['key'] ?>:</span>
                                                                    <span role="button"><a href="{{ $vital['value'] }}"
                                                                            target="_blank">Click to Follow Link</a></span>
                                                                </li>
                                                            @else
                                                                <li>
                                                                    <span><?= $vital['key'] ?>:</span>
                                                                    <span>{{ $vital['value'] }}</span>
                                                                </li>
                                                            @endif
                                                        @endforeach
                                                    @endif
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-8 col-lg-8 pr-0" id="main">
                                            <div class="client-body-column">
                                                @if (isset($single_lookup_data['personFieldsTeam']) && count($single_lookup_data['personFieldsTeam']) > 0)
                                                    <div class="teamhead">
                                                        <h3 class="text-accent">{{ $single_lookup_data['tenant_override_title'] ? $single_lookup_data['tenant_override_title'] : __('My Assigned Team') }}
                                                        </h3>
                                                        <div class="right-line"></div>
                                                    </div>
                                                    <div class="team-member">
                                                        @foreach ($single_lookup_data['personFieldsTeam'] as $key => $legal_team_member_by_person)
                                                            <div class="teamcard">
                                                                <h5 class="mb-3">
                                                                    @if (isset($legal_team_member_by_person['picture_url']) && !empty($legal_team_member_by_person['picture_url']))
                                                                        <img src="{{ $legal_team_member_by_person['picture_url'] }}"
                                                                            alt="Assigned Team" class="team-image">
                                                                    @else
                                                                        <img src="{{ asset('img/client/team_default_image.png') }}"
                                                                            alt="Assigned Team" class="team-image">
                                                                    @endif
                                                                    @if (isset($legal_team_member_by_person['fullname']))
                                                                        {{ $legal_team_member_by_person['fullname'] }}
                                                                    @endif
                                                                </h5>
                                                                @if ($legal_team_member_by_person['config']->fv_person_field_name)
                                                                    <p><span>{{ __('Role:') }}</span> <span
                                                                            class="legal_role_title">{{ $legal_team_member_by_person['config']->fv_person_field_name }}</span>
                                                                    </p>
                                                                @endif
                                                                @if ($legal_team_member_by_person['config']->is_enable_email && $legal_team_member_by_person['email'])
                                                                    <p><span>{{ __('Email:') }}</span> <span
                                                                            class="legal_email">
                                                                            <a title="Send an email to {{ $legal_team_member_by_person['email'] }}"
                                                                                href="mailto:{{ $legal_team_member_by_person['email'] }}">{{ $legal_team_member_by_person['email'] }}</a></span>
                                                                    </p>
                                                                @endif
                                                                @if ($legal_team_member_by_person['config']->is_enable_phone && $legal_team_member_by_person['phone'])
                                                                    <p><span>{{ __('Phone:') }}</span> <span
                                                                            class="legal_phone">{{ $legal_team_member_by_person['phone'] }}</span>
                                                                    </p>
                                                                @endif
                                                                @if (isset($legal_team_member_by_person['email'], $legal_team_member_by_person['config']->is_enable_feedback) &&
                                                                        $legal_team_member_by_person['config']->is_enable_feedback)
                                                                    <div class="star_rating_btn key{{ $key }}"
                                                                        onclick="get_legal_details({{ $key }})"
                                                                        data-toggle="modal"
                                                                        data-target="#starRatingModal">
                                                                        <input type="hidden" name="email"
                                                                            value="{{ $legal_team_member_by_person['email'] ?? '' }}">
                                                                        <input type="hidden" name="phone"
                                                                            value="{{ $legal_team_member_by_person['phone'] ?? '' }}">
                                                                        <input type="hidden" name="name"
                                                                            value="{{ $legal_team_member_by_person['fullname'] ?? '' }}">
                                                                        <span
                                                                            class="team-member-feedback">{{ __('Leave Feedback') }}
                                                                            <i class="fas fa-star"></i></span>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    <div class="bottom-line"></div>
                                                    @if (isset($single_lookup_data['note']) && count($single_lookup_data['note']) > 0)
                                                        <h5 class="font-weight-bold">
                                                            {{ __('Messages from My Team') }}<span class="h6 pl-3">
                                                                {{ __('You have a new message') }}! </span></h5>
                                                        <div class="border border-secondary p-2 mt-3">
                                                            <strong class="pr-3">{!! htmlspecialchars_decode($single_lookup_data['note']['body']) !!}</strong>
                                                            <a href="/lookup/my_team_messages/{{ $lookup_project_id }}"
                                                                class="btn btn-primary btn-sm reply-click" role="button"
                                                                aria-pressed="true">{{ __('Reply') }}</a>
                                                        </div>
                                                    @endif
                                                    <div class="teamcard team-details">
                                                        @if (isset($tenant_custom_vital->display_phone_number) && !empty($tenant_custom_vital->display_phone_number))
                                                            <div class="team-details team-phone">
                                                                <i class="fas fa-phone"></i>
                                                                <a
                                                                    href="tel:{{ $tenant_custom_vital->display_phone_number }}">{{ $tenant_custom_vital->display_phone_number }}</a>
                                                            </div>
                                                        @endif
                                                        @if (isset($tenant_custom_vital->is_show_project_email) && !empty($tenant_custom_vital->is_show_project_email))
                                                            @if (isset($single_lookup_data['project_vital_data']['email']) &&
                                                                    $single_lookup_data['project_vital_data']['email'] != '')
                                                                <div class="team-details team-email">
                                                                    <i class="far fa-envelope"></i>
                                                                    <a title="Send an email to {{ $single_lookup_data['project_vital_data']['email'] }}"
                                                                        href="mailto:{{ $single_lookup_data['project_vital_data']['email'] }}">{{ __('Send Email to Team') }}</a>
                                                                    <input type="hidden"
                                                                        name="contact_project_email_address"
                                                                        value="{{ $single_lookup_data['project_vital_data']['email'] }}">
                                                                </div>
                                                            @endif
                                                        @endif
                                                    </div>
                                                @endif
                                                <!-- Team Config Part -->
                                                @if (isset($single_lookup_data['legal_teams']) && count($single_lookup_data['legal_teams']) > 0)
                                                    <div class="teamhead">
                                                        <h3 class="text-accent">{{ $single_lookup_data['tenant_override_title'] ? $single_lookup_data['tenant_override_title'] : __('My Assigned Team') }}
                                                        </h3>
                                                        <div class="right-line"></div>
                                                    </div>
                                                    <div class="team-member">
                                                        @foreach ($single_lookup_data['legal_teams'] as $key => $legal_team_member)
                                                            <div class="teamcard">
                                                                <h5 class="mb-3">
                                                                    @if (isset($legal_team_member['picture_url']) && !empty($legal_team_member['picture_url']))
                                                                        <img src="{{ $legal_team_member['picture_url'] }}"
                                                                            alt="Assigned Team" class="team-image">
                                                                    @else
                                                                        <img src="{{ asset('img/client/team_default_image.png') }}"
                                                                            alt="Assigned Team" class="team-image">
                                                                    @endif
                                                                    @if (isset($legal_team_member['name']))
                                                                        {{ $legal_team_member['name'] }}
                                                                    @endif
                                                                </h5>
                                                                <!-- show roles -->
                                                                @if (isset($legal_team_member['type']) && $legal_team_member['type'] == \App\Models\LegalteamConfig::TYPE_FETCH)
                                                                    @if (isset($legal_team_member['roles']) && count($legal_team_member['roles']) > 0)
                                                                        @php
                                                                            $roles = '';
                                                                        @endphp
                                                                        @foreach ($legal_team_member['roles'] as $legal_team_member_role)
                                                                            @php
                                                                                $roles .= $legal_team_member_role['name'] . ', ';
                                                                            @endphp
                                                                        @endforeach
                                                                        <p><span
                                                                                class="font-weight-bold">{{ __('Role:') }}</span>
                                                                            <span
                                                                                class="legal_role_title">{{ rtrim(trim($roles), ',') }}</span>
                                                                        </p>
                                                                    @endif
                                                                @else
                                                                    <p><span
                                                                            class="font-weight-bold">{{ __('Role:') }}</span>
                                                                        <span
                                                                            class="legal_role_title">{{ $legal_team_member['role_title'] }}</span>
                                                                    </p>
                                                                @endif

                                                                @if (isset($legal_team_member['name']) && false)
                                                                    <p><span>{{ __('Name:') }}</span> <span
                                                                            class="legal_name">{{ $legal_team_member['name'] }}</span>
                                                                    </p>
                                                                @endif
                                                                @if (isset($legal_team_member['email']) &&
                                                                        (($legal_team_member['is_enable_email'] == 1 &&
                                                                            $legal_team_member['type'] == \App\Models\LegalteamConfig::TYPE_FETCH) ||
                                                                            $legal_team_member['type'] == \App\Models\LegalteamConfig::TYPE_STATIC))
                                                                    <p><span
                                                                            class="font-weight-bold">{{ __('Email:') }}</span>
                                                                        <span class="legal_email">
                                                                            <a title="Send an email to {{ $legal_team_member['email'] }}"
                                                                                href="mailto:{{ $legal_team_member['email'] }}">{{ $legal_team_member['email'] }}</a>
                                                                        </span>
                                                                    </p>
                                                                @endif
                                                                @if (isset($legal_team_member['phone']))
                                                                    <p><span
                                                                            class="font-weight-bold">{{ __('Phone:') }}</span>
                                                                        <span
                                                                            class="legal_phone">{{ $legal_team_member['phone'] }}</span>
                                                                    </p>
                                                                @endif

                                                                @if (isset($legal_team_member['email'], $legal_team_member['is_enable_feedback']) &&
                                                                        $legal_team_member['is_enable_feedback'] == 1)
                                                                    <div class="star_rating_btn key{{ $key }}"
                                                                        onclick="get_legal_details({{ $key }})"
                                                                        data-toggle="modal"
                                                                        data-target="#starRatingModal">
                                                                        <input type="hidden" name="email"
                                                                            value="{{ $legal_team_member['email'] ?? '' }}">
                                                                        <input type="hidden" name="phone"
                                                                            value="{{ $legal_team_member['phone'] ?? '' }}">
                                                                        <input type="hidden" name="name"
                                                                            value="{{ $legal_team_member['name'] ?? '' }}">
                                                                        <span
                                                                            class="team-member-feedback">{{ __('Leave Feedback') }}
                                                                            <i class="fas fa-star"></i></span>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    <div class="bottom-line"></div>
                                                    @if (isset($single_lookup_data['note']) && count($single_lookup_data['note']) > 0)
                                                        <h5 class="font-weight-bold"> {{ __('Messages from My Team') }}
                                                            <span class="h6 pl-3"> {{ __('You have a new message') }}!
                                                            </span></h5>
                                                        <div class="border border-secondary p-2 mt-3">
                                                            <strong class="pr-3">{!! htmlspecialchars_decode($single_lookup_data['note']['body']) !!}</strong>
                                                            <a href="/lookup/my_team_messages/{{ $lookup_project_id }}"
                                                                class="btn btn-primary btn-sm reply-click" role="button"
                                                                aria-pressed="true">{{ __('Reply') }}</a>
                                                        </div>
                                                    @endif
                                                    <div class="teamcard team-details">
                                                        @if (isset($tenant_custom_vital->display_phone_number) && !empty($tenant_custom_vital->display_phone_number))
                                                            <div class="team-details team-phone">
                                                                <i class="fas fa-phone"></i>
                                                                <a
                                                                    href="tel:{{ $tenant_custom_vital->display_phone_number }}">{{ $tenant_custom_vital->display_phone_number }}</a>
                                                            </div>
                                                        @endif
                                                        @if (isset($tenant_custom_vital->is_show_project_email) && !empty($tenant_custom_vital->is_show_project_email))
                                                            @if (isset($single_lookup_data['project_vital_data']['email']) &&
                                                                    $single_lookup_data['project_vital_data']['email'] != '')
                                                                <div class="team-details team-email">
                                                                    <i class="far fa-envelope"></i>
                                                                    <a title="Send an email to {{ $single_lookup_data['project_vital_data']['email'] }}"
                                                                        href="mailto:{{ $single_lookup_data['project_vital_data']['email'] }}">{{ __('Send Email to Team') }}</a>
                                                                    <input type="hidden"
                                                                        name="contact_project_email_address"
                                                                        value="{{ $single_lookup_data['project_vital_data']['email'] }}">
                                                                </div>
                                                            @endif
                                                        @endif
                                                    </div>
                                                @endif
                                                @if (isset($single_lookup_data['personFieldsTeam']) &&
                                                        empty($single_lookup_data['personFieldsTeam']) &&
                                                        isset($single_lookup_data['legal_teams']) &&
                                                        empty($single_lookup_data['legal_teams']))
                                                    <div class="teamhead">
                                                        <h3 class="text-accent">{{ __('My Assigned Team') }}</h3>
                                                        <div class="right-line"></div>
                                                    </div>
                                                    <div class="mt-3 alert alert-warning alert-dismissible fade show"
                                                        role="alert">
                                                        <strong>Empty team members!</strong>
                                                        <button type="button" class="close" data-dismiss="alert"
                                                            aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                @endif


                                                @if (
                                                    $calendar_visibility &&
                                                        isset($single_lookup_data['appointment_items']) &&
                                                        count($single_lookup_data['appointment_items']))
                                                    <div class="row" style="margin:20px">
                                                        <table class="table">
                                                            <thead>
                                                                <tr>
                                                                    <th scope="col">Appointment Title</th>
                                                                    <th scope="col">Date</th>
                                                                    <th scope="col">Location</th>
                                                                    <th scope="col">Note</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($single_lookup_data['appointment_items'] as $item)
                                                                    <tr>
                                                                        <td>{{ $item['title'] }}</td>
                                                                        <td>{{ $item['start'] }}</td>
                                                                        <td>{{ $item['location'] }}</td>
                                                                        <td>{{ $item['notes'] }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="container-fluid" style="margin-top: 30px">
                                    <div class="row client-body-column">
                                        <div class="casestatus">
                                            @if (isset($single_lookup_data['results']['project']['phaseName']))
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h2 class="text-accent">{{ $single_lookup_data['tenant_phase_mapping_override_title'] ? $single_lookup_data['tenant_phase_mapping_override_title'] : __('Case Status') }}
                                                    </h2>
                                                    <div class="right-line"></div>
                                                </div>
                                                <h2 class="text-accent">
                                                    {{ $single_lookup_data['results']['project']['phaseName'] }}
                                                </h2>
                                                <div class="bottom-line mt-3 mb-3"></div>
                                            @endif
                                            @if (isset($single_lookup_data['description']))
                                                <div class="casestatusspan">
                                                    {!! $single_lookup_data['description'] !!}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                @if (isset($single_lookup_data['config_details']) && $single_lookup_data['config_details']->is_display_timeline)
                                    <div class="container-fluid" style="margin-top: 30px">
                                        <div class="row client-body-column">
                                            <div class="casetimeline w-100">
                                                @if (isset($single_lookup_data['project_type_name']))
                                                    <div class="d-flex justify-content-between align-items-center mt-4">
                                                        <div class="right-line"></div>
                                                        <h2 class="text-accent">{{ $single_lookup_data['tenant_phase_category_override_title'] ? $single_lookup_data['tenant_phase_category_override_title'] : 'Our ' . $single_lookup_data['project_type_name'] . 'Case Timeline' }}
                                                        </h2>
                                                        <div class="right-line"></div>
                                                    </div>
                                                @endif
                                                <!-- hsdghgd1 -->
                                                {!! $single_lookup_data['mobileNav'] !!}

                                                @if (!empty($single_lookup_data['mobileTimeline']))
                                                    <div class="row mt-3 mobile-timeline">
                                                        <div class="d-flex align-items-center">
                                                            <div class="flex-shrink-0 bg-custom-dark rounded-left">
                                                                <a href="javascript:void()"
                                                                    class="btn-left btn-link p-2 toggle text-white"><i
                                                                        class="fa fa-arrow-left"></i></a>
                                                            </div>
                                                            <div class="flex-grow-1 w-100 o-hidden">
                                                                <ul
                                                                    class="nav nav-fill small position-relative flex-nowrap">
                                                                    {!! $single_lookup_data['mobileTimeline'] !!}
                                                                </ul>
                                                            </div>
                                                            <div class="flex-shrink-0 bg-custom-dark rounded-right">
                                                                <a href="javascript:void()"
                                                                    class="btn-right btn-link toggle p-2 text-white"><i
                                                                        class="fa fa-arrow-right"></i></a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif

                                                <!-- hsdghgd2 -->
                                                {!! $single_lookup_data['tabs'] !!}
                                                <!-- hsdghgd3 -->
                                                {!! $single_lookup_data['pannels'] !!}

                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <!-- Modal -->
                                <div class="modal fade" id="starRatingModal" tabindex="-1" role="dialog"
                                    aria-labelledby="starRatingModalLabel" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal"
                                                    aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <form method="post" name="feedback_form"
                                                action="{{ route('client_feedback', ['subdomain' => session()->get('subdomain')]) }}">
                                                @csrf
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <h5 class="modal-title" id="starRatingModalLabel">
                                                            {{ __('Leave Feedback') }}
                                                        </h5>
                                                        <label for="exampleFormControlTextarea1" id="fd_service_label"
                                                            class="text-label">
                                                            {{ __('How satisfied are you with the legal service has provided?') }}</label>
                                                        <div class="starRating">
                                                            <input type="radio" required name="fd_mark_legal_service"
                                                                value="5" id="fifth">
                                                            <label for="fifth"></label>
                                                            <input type="radio" name="fd_mark_legal_service"
                                                                value="4" id="fourth">
                                                            <label for="fourth"></label>
                                                            <input type="radio" name="fd_mark_legal_service"
                                                                value="3" id="thirth">
                                                            <label for="thirth"></label>
                                                            <input type="radio" name="fd_mark_legal_service"
                                                                value="2" id="second">
                                                            <label for="second"></label>
                                                            <input type="radio" name="fd_mark_legal_service"
                                                                value="1" id="first">
                                                            <label for="first"></label>
                                                            <!-- Show Result  -->
                                                            <span class="result"></span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="exampleFormControlTextarea1" class="text-label">
                                                            {{ __('How likely are you to recommend our firm to others?') }}</label>
                                                        <div class="starRating">
                                                            <input type="radio" required name="fd_mark_recommend"
                                                                value="5" id="fifth1">
                                                            <label for="fifth1"></label>
                                                            <input type="radio" name="fd_mark_recommend" value="4"
                                                                id="fourth1">
                                                            <label for="fourth1"></label>
                                                            <input type="radio" name="fd_mark_recommend" value="3"
                                                                id="thirth1">
                                                            <label for="thirth1"></label>
                                                            <input type="radio" name="fd_mark_recommend" value="2"
                                                                id="second1">
                                                            <label for="second1"></label>
                                                            <input type="radio" name="fd_mark_recommend" value="1"
                                                                id="first1">
                                                            <label for="first1"></label>
                                                            <!-- Show Result  -->
                                                            <span class="result"></span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="exampleFormControlTextarea1" class="text-label">
                                                            {{ __('How useful have you found this Client Portal to be?') }}</label>
                                                        <div class="starRating">
                                                            <input type="radio" required name="fd_mark_useful"
                                                                value="5" id="fifth2">
                                                            <label for="fifth2"></label>
                                                            <input type="radio" name="fd_mark_useful" value="4"
                                                                id="fourth2">
                                                            <label for="fourth2"></label>
                                                            <input type="radio" name="fd_mark_useful" value="3"
                                                                id="thirth2">
                                                            <label for="thirth2"></label>
                                                            <input type="radio" name="fd_mark_useful" value="2"
                                                                id="second2">
                                                            <label for="second2"></label>
                                                            <input type="radio" name="fd_mark_useful" value="1"
                                                                id="first2">
                                                            <label for="first2"></label>
                                                            <!-- Show Result  -->
                                                            <span class="result"></span>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="exampleFormControlTextarea1"
                                                            class="text-label mt-4">{{ __('Is there anything we could be doing better?') }}</label>
                                                        <textarea required class="form-control" name="fd_content" id="exampleFormControlTextarea1" rows="3"></textarea>
                                                    </div>
                                                    <input type="hidden" name="client_name"
                                                        value="{{ isset($single_lookup_data['results']['project']['clientName']) ? $single_lookup_data['results']['project']['clientName'] : '' }}">
                                                    <input type="hidden" name="client_phone"
                                                        value="{{ isset($single_lookup_data['project_vital_data']['project']['phone']) ? $single_lookup_data['project_vital_data']['phone'] : '' }}">
                                                    <input type="hidden" name="legal_team_phone" id="legal_team_phone"
                                                        value="">
                                                    <input type="hidden" name="legal_team_email" id="legal_team_email"
                                                        value="">
                                                    <input type="hidden" name="project_id"
                                                        value="{{ isset($single_lookup_data['results']['projectId']['native']) ? $single_lookup_data['results']['projectId']['native'] : '' }}">
                                                    <input type="hidden" name="project_name"
                                                        value="{{ isset($single_lookup_data['results']['project']['projectName']) ? $single_lookup_data['results']['project']['projectName'] : '' }}">
                                                    <input type="hidden" name="project_phase"
                                                        value="{{ isset($single_lookup_data['results']['project']['phaseName']) ? $single_lookup_data['results']['project']['phaseName'] : '' }}">
                                                    <input type="hidden" name="legal_team_name" id="legal_team_name"
                                                        value="">
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" name="submit_feedback"
                                                        class="btn btn-primary">{{ __('Send') }}</button>
                                                    <button type="button" class="btn btn-secondary"
                                                        data-dismiss="modal">{{ __('Cancel') }}</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </section>
                            @include('client.includes.footer_copyright')
                        </div>
                    @else
                        @if (isset($single_lookup_data['results']['project']['projectName']))
                            <label class="ac-label bg-title" for="ac-{{ $key }}"
                                onclick="location.href='/lookup/{{ $single_lookup_data['project_id'] }}'">
                                <span>{{ isset($single_lookup_data['project_override_name']) && !empty($single_lookup_data['project_override_name']) ? $single_lookup_data['project_override_name'] : $single_lookup_data['results']['project']['projectName'] }}</span><i></i>
                            </label>
                        @endif
                    @endif
                </div>
            @endforeach
        </div>
    </div>
    <!-- Modal -->
    <div id="vitalContactInfoUpdate" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modelLoadingImage">
                <img src="/assets/img/loading.gif">
            </div>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Update My Contact Information') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body client_info">

                </div>
            </div>
        </div>
    </div>


    <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>

    <script>
        var selected_client_native_id = {{ $selected_client_native_id }};

        function openNav() {
            document.getElementById("mySidenav").style.width = "250px";
            document.getElementById("main").style.marginLeft = "250px";
            document.getElementById("myCanvasNav").style.width = "100%";
            document.getElementById("myCanvasNav").style.opacity = "0.8";
        }

        function closeNav() {
            document.getElementById("mySidenav").style.width = "0";
            document.getElementById("main").style.marginLeft = "0";
            document.getElementById("myCanvasNav").style.width = "0";
            document.getElementById("myCanvasNav").style.width = "0";
        }

        /**
         * {{ __('Leave Feedback') }} modal info
         */
        function get_legal_details(index) {
            var leagl_team_email = $('.star_rating_btn.key' + index).find('[name=email]').val();
            var legal_team_phone = $('.star_rating_btn.key' + index).find('[name=phone]').val();
            var legal_team_name = $('.star_rating_btn.key' + index).find('[name=name]').val();

            $('#legal_team_phone').val(legal_team_phone);
            $('#legal_team_email').val(leagl_team_email);
            $('#legal_team_name').val(legal_team_name);

            $('#starRatingModalLabel').text('{{ __('Leave Feedback') }} for ' + legal_team_name);
            $('#fd_service_label').text('How satisfied are you with the legal service ' + legal_team_name +
                ' has provided?');

        }

        $('.nav-tabs li a').on('click', function() {
            $('.nav-tabs').find('li.active').removeClass('active');
            $(this).parent('li').addClass('active');
        });

        $(document).ready(function() {

            $(".casetimeline img").addClass("img-fluid");
            $(".casestatus img").addClass("img-fluid");

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // $('.send_reply').on('click', function() {
            //     // $('.chatbox-items').addClass('hide');
            //     // $('.chatbox-replies').removeClass('hide');
            //     var _self = $(this);
            //     var noteId = $('#note_id').val();
            //     var formData = {
            //         note_id: noteId
            //     };

            //     $.ajax({
            //         type: "post",
            //         url: "/allow_note_editing",
            //         dataType: "JSON",
            //         data: formData,
            //         beforeSend: function() {
            //             _self.text('{{ __('Processing...') }}');
            //         },
            //         complete: function() {
            //             _self.text('{{ __('SEND REPLY') }}');
            //         },
            //         success: function(res) {
            //             if (res.success) {
            //                 _self.text('SEND REPLY');
            //                 $('.chatbox-items').addClass('hide');
            //                 $('.chatbox-replies').removeClass('hide');
            //             } else {
            //                 _self.text('SEND REPLY');
            //                 Swal.fire({
            //                     text: res.message,
            //                     icon: "error",
            //                 });
            //             }
            //         }
            //     });
            // });


            // $('.send_comment').on('click', function() {
            //     $('.send-reply-error').removeClass('text-success');
            //     $('.send-reply-error').removeClass('text-danger');
            //     $('.send-reply-error').text('');
            //     // check for comment value
            //     var comment = $('#note_reply').val();
            //     var noteId = $('#note_id').val();
            //     var projectId = $('#project_id').val();
            //     var clientName = $('#client_name').val();
            //     var clientEmail = $('#client_email').val();
            //     if (comment.trim() == "") {
            //         $('.send-reply-error').addClass('text-danger');
            //         $('.send-reply-error').text('Please enter reply to send');
            //         return;
            //     } else {
            //         var formData = {
            //             project_id: projectId,
            //             note_id: noteId,
            //             note_body: comment,
            //             client_Name: clientName,
            //             client_Email: clientEmail
            //         };

            //         $.ajax({
            //             type: "post",
            //             url: "/send_note_reply",
            //             dataType: "JSON",
            //             data: formData,
            //             beforeSend: function() {},
            //             complete: function() {},
            //             success: function(res) {
            //                 console.log(res);
            //                 console.log(res.success);
            //                 if (res.success == true) {
            //                     $('.send-reply-error').addClass('text-success');
            //                     $('.send-reply-error').text(res.message);
            //                     setTimeout(function() {
            //                         location.reload();
            //                     }, 2000);
            //                 } else {
            //                     $('.send-reply-error').addClass('text-danger');
            //                     $('.send-reply-error').text(res.message);
            //                 }
            //             }
            //         });
            //     }
            // });

        });

        $(document).ready(function() {
            $(".ac-label").click(function(e) {
                e.preventDefault();
                $check = $(this).prev();
                if ($check.prop('checked'))
                    $check.prop("checked", false);
                else
                    $check.prop("checked", true);
            });

            $('#vitalContactInfoUpdate').on('show.bs.modal', function(event) {
                var project_id = $('.clientProjectId').text();
                $.ajax({
                    type: 'POST',
                    url: '/get_contact_info',
                    data: {
                        project_id: project_id,
                        client_native_id: selected_client_native_id,
                        contact_project_email_address: $(
                            "input[name='contact_project_email_address']").length ? $(
                            "input[name='contact_project_email_address']").val() : '',
                        project_name: $("input[name='project_name']").length ? $(
                            "input[name='project_name']").val() : ''
                    },
                    success: function(response) {
                        $('.client_info').empty().append(response);
                        $('.modelLoadingImage').hide();
                    }
                });
            });
            $('#vitalContactInfoUpdate').on('hidden.bs.modal', function() {
                $('.modelLoadingImage').show();
            });

            let sw = screen.width;
            if (sw < 540) {
                $("iframe").width(sw - 70);
            }
            document.getElementById('language-select').addEventListener('click', function(e) {
                $('#language-dropdown').toggleClass('show-dropdown');
                $('.text-lang-color .fas').toggleClass('fa-angle-down fa-angle-up');
                e.stopPropagation();
            })
            $(document).click(() => {
                if ($('#language-dropdown').hasClass('show-dropdown')) {
                    $('#language-dropdown').removeClass('show-dropdown');
                    $('.text-lang-color .fas').toggleClass('fa-angle-down fa-angle-up');
                }
            })
        });

        function showtimelinecontent(e, catId) {
            $('.timeline-content-item').removeClass('show active');
            $(`#${catId}.timeline-content-item`).addClass('show active');
            $('.timeline-menu').removeClass('active');
            e.classList.add('active');
        }

        function showDropdown() {
            if ($('#language-dropdown').hasClass('show-dropdown')) {
                $('#language-dropdown').removeClass('show-dropdown');
                $('.text-lang-color .fas').toggleClass('fa-angle-down fa-angle-up');
            }
            $('.client-header-dropdown-div').toggleClass('d-none');
            document.getElementById("client-header-dropdown2").classList.toggle('d-none');
            document.getElementById("client-header-dropdown1").classList.toggle('d-none');
        }
        $('.client-header-dropdown').hover(function() {
            $('#client-header-dropdown1 rect').css('fill', '{{$config->color_main ?? "#26A9DF"}}');
            $('#client-header-dropdown1 rect:first-child').css('fill', 'white');
        }, function() {
            $('#client-header-dropdown1 rect').css('fill', 'white');
            $('#client-header-dropdown1 rect:first-child').css('fill', '{{$config->color_main ?? "#26A9DF"}}');
        });

        $(document).on('click', '.toggle', function() {
            $('.nav').toggleClass("justify-content-end");
            $('.toggle').toggleClass("text-light");
        });

        function showDataOnHover(ctx) {
            ctx.parentNode.querySelector('.hover-text').classList.toggle('visible');
        }
        $('form[name="feedback_form"]').submit(function() {
            $(this).find('button[type=submit]').prop('disabled', true);
        });
    </script>
    <style>
        .casestatusspan {
            padding: 20px;
        }

        .contnt_right .casestatus .casestatusspan p {
            margin: revert !important;
            line-height: revert !important;
            font-weight: revert !important;
            padding: revert !important;
        }

        .modelLoadingImage {
            width: 100%;
            height: 100%;
            float: left;
            position: absolute;
            text-align: center;
            z-index: 1;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modelLoadingImage img {
            top: 45%;
            position: relative;
        }

        .alert.alert-custom {
            padding: 0rem 2rem;
        }

        .o-hidden {
            overflow: hidden;
            border: 1px solid #efecf3;
        }

        .position-relative {
            position: relative;
        }

        .hover-text {
            visibility: hidden;
            width: fit-content;
            display: flex;
            align-items: center;
            background-color: #555;
            color: #fff;
            text-align: center;
            border-radius: 6px;
            padding: 5px;
            position: absolute;
            top: -28px;
            right: 0px;
            z-index: 100;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .hover-text::after {
            content: "";
            position: absolute;
            top: 100%;
            left: 50%;
            margin-left: -5px;
            border-width: 5px;
            border-style: solid;
            border-color: #555 transparent transparent transparent;
        }

        .visible {
            opacity: 1;
            visibility: visible;
        }

        @media screen and (max-width: 768px) {
            .visible {
                opacity: 0;
                visibility: hidden;
            }
        }
    </style>
@stop
