<?php
$config_details = DB::table('config')
    ->where('tenant_id', $cur_tenant_id)
    ->first();
?>
@extends('client.layouts.default')

@section('title', 'VineConnect Client Portal - Verify Your Identity with 2FA')

@section('content')

    <style>
        .login-logo {
            padding: 0px;
            width: auto !important;
            /* transform: scale(1.3); */
        }

        @media screen and (max-width: 768px) {
            .login-logo {
                transform: scale(1);
            }
        }

        .language-select.w-auto {
            width: auto !important;
        }

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
    </style>

    <div class="overlay loading"></div>
    <div class="spinner-border text-primary loading" role="status">
        <span class="sr-only">Loading...</span>
    </div>

    <div class="limiter">
        <div class="container-login100">
            <div class="wrap-login100">
                <div class="logo">
                </div>
                <div class="d-flex justify-content-end">
                    <div class="language-select w-auto" id="language-select">
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
                            <li>
                                <a href="{{ url()->current() }}?lang=en">English</a>
                            </li>
                            <li>
                                <a href="{{ url()->current() }}?lang=es">Español</a>
                            </li>
                            <li>
                                <a href="{{ url()->current() }}?lang=fr">Français</a>
                            </li>
                            <li>
                                <a href="{{ url()->current() }}?lang=pt">Português</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="d-flex align-items-center bg-logo p-2">
                    @if (isset($config_details->logo))
                        <div class="w-100">
                            <img src="{{ asset('uploads/client_logo/' . $config_details->logo) }}" alt="Logo"
                                class="login-logo">
                        </div>
                    @else
                        <div class="w-100">
                            <img src="{{ asset('img/client/vineconnect_logo.png') }}" alt="VineConnect Logo"
                                class="login-logo">
                        </div>
                    @endif
                </div>
                <div class="login100-form-title bg-title">
                    <span class="login100-form-title-1">
                        {{ __('Enter Your Verification Code') }}
                    </span>
                </div>

                <form method="post"
                    action="{{ route('verifyCheck', ['subdomain' => str_replace('//', '', substr(url()->current(), stripos(url()->current(), '//'), stripos(url()->current(), '.') - stripos(url()->current(), '//')))]) }}"
                    class="login100-form" id="submit_form">
                    @csrf
                    <div class="wrap-input100 validate-input m-b-20">

                        @if (session()->get('match_field'))
                            <div class="align-items-center alert alert-secondary mt-3 text-center" role="alert">
                                @if (session()->get('match_field') == 'phone_email' || session()->get('match_field') == 'phone')
                                    <span> Check your phone for a one-time code. </span>
                                @else
                                    <span> Check your email for a one-time code. </span>
                                @endif
                            </div>
                        @endif

                        <label class="font-weight-bold">
                            @if (session()->get('match_field') == 'phone_email' || session()->get('match_field') == 'phone')
                                <span id="send_by_label">SMS</span>
                            @else
                                <span id="send_by_label">Email</span>
                            @endif
                            {{ __('Verification Code') }}
                        </label>
                        <input type="text" class="input100" name="verification_code" inputmode="numeric" pattern="[0-9]*"
                            autocomplete="one-time-code" placeholder="{{ __('Verification Code') }}" required />
                        <input type="hidden" name="fv_project_id" value="{{ $projectID }}" />
                        <input type="hidden" name="service_sid"
                            value="@if (isset($service_sid)) {{ $service_sid }} @endif" />
                        <br>
                        <a href="javascript:void(0)" onclick="send2faEmail()">Send Another Code</a> or
                        @if (session()->get('match_field') == 'phone_email' || session()->get('match_field') == 'phone')
                            <a href="javascript:void(0)" onclick="reSend2fa(1)">Send by Email Instead?</a>
                        @else
                            <a href="javascript:void(0)" onclick="reSend2fa(2)">Send by SMS Instead?</a>
                        @endif
                    </div>
                    <div class="container-login100-form-btn mb-3">
                        @if (session()->has('message'))
                            <small id=""
                                class="form-text text-danger cg-error error">{{ session()->get('message') }}</small>
                        @endif
                        <small class="d-none response-message form-text text-success cg-error error">Verification Code
                            Sent!</small>
                        {{-- @if (session()->has('phoneinvalid'))
                  <small id="" class="form-text text-danger cg-error error">{{ session()->get('botherror') }}</small>
                    @endif
                    @if (session()->has('2fainvalid'))
                    <small
                        id=""
                        class="form-text text-danger cg-error error"
                    >{{ session()->get('2fainvalid') }}</small>
                    @endif
                    @if (session()->has('twilioinvalid'))
                    <small
                        id=""
                        class="form-text text-danger cg-error error"
                    >{{ session()->get('twilioinvalid') }}</small>
                    @endif
                    @if (session()->has('noprojectid'))
                    <small
                        id=""
                        class="form-text text-danger cg-error error"
                    >{{ session()->get('noprojectid') }}</small>
                    @endif --}}
                    </div>
                    <div class="container-login100-form-btn">
                        <input type="submit" class="login-form-btn login100-form-btn bg-accent" name="verify_phone" value="VERIFY" />
                    </div>
                    <div class="wrap-input100 validate-input text-center mt-3">
                        <a target="_blank"
                            href="https://intercom.help/vinetegrate/en/articles/5955540-accessing-vineconnect-client-portal"
                            class="font-weight-bold text-accent"><i class="fa fa-question-circle" aria-hidden="true"></i>&nbsp;CLICK
                            HERE FOR HELP</a>
                        <a href="{{ route('client', ['subdomain' => session()->get('subdomain')]) }}"
                            class="back-login-link text-accent">{{ __('Back to Login') }}</a>
                    </div>
                </form>
            </div>
        </div>
        @include('client.includes.footer_copyright')
    </div>


    <script src="{{ asset('js/jquery-3.2.1.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            document.getElementById('language-select').addEventListener('click', function(e) {
                $('#language-dropdown').toggleClass('show-dropdown');
                $('.text-lang-color .fas').toggleClass('fa-angle-down fa-angle-up');
                e.stopPropagation();
            })
            $(document).click(() => {
                if ($('#language-dropdown').hasClass('show-dropdown')) {
                    $('#language-dropdown').removeClass('show-dropdown')
                }
            })

            $('form#submit_form').submit(function() {
                $(this).find(':input[type=submit]').prop('disabled', true);
            });
        });

        function send2faEmail() {
            $(".loading").show();
            let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                url: "/send_2fa_email",
                data: {
                    '_token': CSRF_TOKEN
                },
                type: "POST",
                success: function(response) {
                    $(".loading").hide();
                    if (response.success) {
                        $(".response-message").removeClass("d-none");
                        $("input[name='service_sid']").val(response.service_sid);
                    }
                },
                error: function() {
                    $(".loading").hide();
                    alert("Error to Process Your Request! Please try Again!");
                },
            }).done(function() {});
        }

        function reSend2fa(send_by) {
            $(".loading").show();
            let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                url: "/re_send_2fa",
                data: {
                    '_token': CSRF_TOKEN,
                    'send_by': send_by,
                    'service_sid': $("input[name='service_sid']").val()
                },
                type: "POST",
                success: function(response) {
                    $(".loading").hide();
                    if (response.success) {
                        $(".response-message").removeClass("d-none");
                        if (send_by == 1) {
                            $("#send_by_label").text("Email");
                        } else {
                            $("#send_by_label").text("SMS");
                        }
                    } else {
                        alert(response.message);
                    }
                },
                error: function() {
                    $(".loading").hide();
                    alert("Error to Process Your Request! Please try Again!");
                },
            }).done(function() {});
        }
    </script>
@stop
