<?php
$config_details = DB::table('config')
    ->where('tenant_id', $cur_tenant_id)
    ->first();
$tenant = App\Models\Tenant::find($cur_tenant_id);
$is_active = 0;
if ($tenant && isset($tenant->id)) {
    $is_active = $tenant->is_active;
}
?>
@extends('client.layouts.default')

@section('title', 'VineConnect Client Portal - Login')

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
</style>

@section('content')
    <div class="limiter">
        <div class="container-login100">
            <div class="wrap-login100">
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
                            <li><a
                                    href="{{ route('client', ['subdomain' => session()->get('subdomain'), 'lang' => 'en']) }}">English</a>
                            </li>
                            <li><a
                                    href="{{ route('client', ['subdomain' => session()->get('subdomain'), 'lang' => 'es']) }}">Español</a>
                            </li>
                            <li><a
                                    href="{{ route('client', ['subdomain' => session()->get('subdomain'), 'lang' => 'fr']) }}">Français</a>
                            </li>
                            <li><a
                                    href="{{ route('client', ['subdomain' => session()->get('subdomain'), 'lang' => 'pt']) }}">Português</a>
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
                    <span class="login100-form-title-1" style="text-transform: uppercase;">
                        {{ __('Client Portal Login') }}
                    </span>
                </div>
                @if (session()->has('success'))
                    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                        <strong>Success!</strong> Your contact information has been submitted to our administrative team.
                        You will receive a confirmation when you can attempt another login.
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                <form method="post" action="{{ route('client_login', ['subdomain' => session()->get('subdomain')]) }}"
                    class="login100-form" id="submit_form">
                    @csrf
                    <div class="wrap-input100 validate-input m-b-20">
                        <div class="instructions" style="padding-bottom: 20px;">
                            <!-- <p><span style="background-color:#F5CC6C;font-size: 14px;color:#ff0000;"><b>ATTENTION:</b> Unfortunately, the Client Portal is down, and we are working to restore service. Sorry for the inconvenience.</span></p> -->
                            <p style="font-size:12px; color:#333;"><span
                                    style="font-weight:bold;">{{ __('INSTRUCTIONS:') }}</span>
                                {{ __('Please provide your first and last name and phone and/or email address to receive a one-time authentication code. Information provided must match our records exactly.') }}
                            </p>
                        </div>
                        <label class="font-weight-bold">{{ __('First Name') }}</label>
                        <input class="input100" type="text" name="FirstName" placeholder="Jane"
                            value="{{ old('FirstName') }}" />
                    </div>
                    <div class="wrap-input100 validate-input m-b-15">
                        <label class="font-weight-bold">{{ __('Last Name') }}</label>
                        <input type="text" class="input100" name="LastName" placeholder="Doe"
                            value="{{ old('LastName') }}" />
                    </div>
                    <div class="wrap-input100 validate-input m-b-15">
                        <label class="font-weight-bold">{{ __('Phone Number') }}</label>
                        <input type="text" pattern="[0-9-_.()/+]{5,20}" class="input100" name="PhoneNo"
                            inputmode="numeric" placeholder="10-Digit Numbers Only" />
                    </div>
                    <div class="wrap-input100 validate-input text-center">
                        <label class="font-weight-bold" style="margin-bottom: 0px !important">AND/OR</label>
                    </div>
                    <div class="wrap-input100 validate-input m-b-15">
                        <label class="font-weight-bold">{{ __('Email Address') }}</label>
                        <input type="email" class="input100" name="EmailAddress" placeholder="jane@example.com" />
                    </div>
                    <!-- <div class="wrap-input100 validate-input m-b-15">
                                                                                                                                                                                                                            <span class="or-seprator">or</span>
                                                                                                                                                                                                                        </div> -->
                    <div class="wrap-input100 validate-input m-b-20">
                        <!-- <input type="text" class="input100" name="ProjectId" placeholder="Project ID" />
                                                                                                                                                                                                                            <span class="help-dropdown">
                                                                                                                                                                                                                                <small class="form-text text-muted help-icon">
                                                                                                                                                                                                                                    <img src="{{ asset('img/client/help-blue.svg') }}" width="11">Find ProjectId
                                                                                                                                                                                                                                    <div class="cg-popover notification-popover">
                                                                                                                                                                                                                                        <i class="cg-popover-arrow"></i>
                                                                                                                                                                                                                                        Your ProjectId is unique string of numbers that can be found in your Associated Case
                                                                                                                                                                                                                                        Email address you use to communicate with your legal team. Example: <a href="mailto:name5555555@projects.filevine.com">name5555555@projects.filevine.com</a>
                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                </small>
                                                                                                                                                                                                                        </span> -->
                        <div class="container-login100-form-btn">
                            @if (session()->has('botherror'))
                                <small id=""
                                    class="form-text text-danger cg-error error">{{ session()->get('botherror') }}</small>
                            @endif
                            @if (session()->has('noprojectclient'))
                                <small id=""
                                    class="form-text text-danger cg-error error">{{ session()->get('noprojectclient') }}</small>
                            @endif
                            @if (session()->has('phoneinvalid'))
                                <small id=""
                                    class="form-text text-danger cg-error error">{{ session()->get('phoneinvalid') }}</small>
                            @endif
                            @if (session()->has('phoneclient'))
                                <small id=""
                                    class="form-text text-danger cg-error error">{{ session()->get('phoneclient') }}</small>
                            @endif
                            @if (session()->has('someError'))
                                <small id=""
                                    class="form-text text-danger cg-error error">{{ session()->get('someError') }}</small>
                            @endif
                            @if (session()->has('configdata'))
                                <small id=""
                                    class="form-text text-danger cg-error error">{{ session()->get('configdata') }}</small>
                            @endif
                            @if (session()->has('twilioinvalid'))
                                <small id=""
                                    class="form-text text-danger cg-error error">{{ session()->get('twilioinvalid') }}</small>
                            @endif
                            @if (session()->has('message'))
                                <small id=""
                                    class="form-text text-danger cg-error error">{{ session()->get('message') }}</small>
                            @endif
                        </div>

                        @if (session()->has('try_with') &&
                                (str_contains(session()->get('try_with'), 'phoneemail') ||
                                    str_contains(session()->get('try_with'), 'emailphone')))
                            <div class="align-items-center alert alert-secondary mt-3 text-center" role="alert">
                                <span>
                                    {{ __('We’re having trouble finding your client information.') }}
                                </span>
                                <a href="javascript:void(0)" class="link-success" data-toggle="modal"
                                    data-target="#submitInformation">Update my contact info now.</a>
                            </div>
                            @php  session()->forget('try_with'); @endphp
                        @elseif (session()->has('submit_field') &&
                                (session()->get('submit_field') == 'phone' || session()->get('submit_field') == 'email'))
                            <div class="wrap-input100 validate-input m-b-15">
                                @if (session()->get('submit_field') == 'phone')
                                    <label>{{ __('Try submitting with your email instead') }}.</label>
                                @endif
                                @if (session()->get('submit_field') == 'email')
                                    <label>{{ __('Try submitting with your phone number instead') }}.</label>
                                @endif
                            </div>
                            @php  session()->forget('submit_field'); @endphp
                        @endif

                        <div class="container-login-form-btn mt-5">
                            @if ($is_active && $tenant)
                                <input type="submit" class="login-form-btn login100-form-btn bg-accent"
                                    value="{{ __('SEND MY CODE') }}" />
                            @else
                                <button disabled="disabled" type="button" role="button"
                                    class="login-form-btn login100-form-btn bg-accent">{{ __('SEND MY CODE') }}</button>
                            @endif
                        </div>
                        @if (isset($tenantLiveInfo->status) && $tenantLiveInfo->status == 'setup')
                            <div class="col-md-12 text-center">
                                <p class="text-info mt-2">
                                    {{ __('You are currently in Setup mode. Only the SMS Override Number configured in Launchpad will receive 2FA codes.') }}
                                </p>
                            </div>
                        @elseif (isset($tenantLiveInfo->status) && $tenantLiveInfo->status == 'scheduled')
                            <div class="col-md-12 text-center">
                                <p class="text-info mt-2">
                                    {{ __('This Client Portal is currently scheduled to go live on ') }}
                                    <b>{{ $tenantLiveInfo->scheduled_date }}</b>{{ __('. Please come back at that time to experience this exciting new tool!') }}
                                </p>
                            </div>
                        @endif
                        @if (!$tenant)
                            <div class="col-md-12 text-center">
                                <p class="text-danger mt-2">
                                    {{ __('The Tenant is invalid. Please ask to support team.') }}
                                </p>
                            </div>
                        @else
                            @if (!$is_active)
                                <div class="col-md-12 text-center">
                                    <p class="text-danger mt-2">
                                        {{ __('This portal is inactive. Please email vineconnect@vinetegrate.com for help.') }}
                                    </p>
                                </div>
                            @endif
                        @endif
                        <div class="col-md-12 text-center mt-3">
                            <a target="_blank"
                                href="https://intercom.help/vinetegrate/en/articles/5955540-accessing-vineconnect-client-portal"
                                class="font-weight-bold text-accent"><i class="fa fa-question-circle"
                                    aria-hidden="true"></i>&nbsp;CLICK HERE FOR HELP</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @include('client.includes.footer_copyright')
    </div>


    <div id="submitInformation" tabindex="-1" class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Submit your most updated contact information') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body ">
                    <form method="post"
                        action="{{ route('submit_information', ['subdomain' => session()->get('subdomain')]) }}"
                        class="login100-form">
                        @csrf
                        <div class="wrap-input100 validate-input m-b-20">
                            <label class="font-weight-bold">{{ __('First Name') }}</label>
                            <input class="input100" type="text" name="modal_name" placeholder="Jane" required />
                        </div>
                        <div class="wrap-input100 validate-input m-b-15">
                            <label class="font-weight-bold">{{ __('Last Name') }}</label>
                            <input type="text" class="input100" name="modal_last_name" placeholder="Doe" required />
                        </div>
                        <div class="wrap-input100 validate-input m-b-15">
                            <label class="font-weight-bold">{{ __('Phone Number') }}</label>
                            <input type="text" pattern="[0-9-_.()/+]{5,20}" class="input100" name="modal_phone_no"
                                inputmode="numeric" placeholder="10-Digit Numbers Only" required />
                        </div>
                        <div class="wrap-input100 validate-input m-b-15">
                            <label class="font-weight-bold">{{ __('Email Address') }}</label>
                            <input type="email" class="input100" name="modal_email_address"
                                placeholder="jane@example.com" required />
                        </div>
                        <div class="container-login-form-btn mt-5">
                            <input type="submit" class="login-form-btn login100-form-btn"
                                value="{{ __('SUBMIT') }}" />
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/jquery-3.2.1.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
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

            $('form#submit_form').submit(function() {
                $(this).find(':input[type=submit]').prop('disabled', true);
            });
        })
    </script>
@stop
