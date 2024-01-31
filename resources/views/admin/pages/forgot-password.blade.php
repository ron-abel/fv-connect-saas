<?php
$config_details = DB::table('config')->where('tenant_id', $cur_tenant_id)->first();
?>
@extends('admin.layouts.logindefault')

@section('title', 'VineConnect Admin Login')

@section('content')
<div class="limiter">
    <div class="container-login100">
        <div class="wrap-login100">
            <div class="">
                @if(isset($config_details->logo))
                <a href="">
                    <img src="{{ asset('uploads/client_logo/' . $config_details->logo) }}" alt="Logo"  class="login-logo" style="max-height:100px;width: auto;">
                </a>
                @else
                <a href="">
                    <img src="{{ asset('img/client/vineconnect_logo.png') }}" alt="VineConnect Logo" class="login-logo">
                </a>
                @endif

            </div>
            <div class="login100-form-title">
                <span class="login100-form-title-1">
                    Forgot Password
                </span>
            </div>
            <form method="post" action="{{ url('/reset_password_without_token') }}" class="login100-form validate-form">
                @csrf
                @if ( session()->has('error') )
                <div style="width:100%; padding:5px; font-size:13px; font-weight:bold; color:#f00; text-align:center;">
                    {{ session()->get('error') }}
                </div>
                @endif
                @if(session()->has('status'))
                <div class="text-success" style="width:100%; padding:5px; font-size:13px; font-weight:bold; text-align:center;">
                    {{ session()->get('status') }}
                </div>
                @endif

                @error('email')
                <div style="width:100%; padding:5px; font-size:13px; font-weight:bold; color:#f00; text-align:center;">
                    {{ $message }}
                </div>
                @enderror

                <div class="
                wrap-input100
                validate-input
                m-b-20" data-validate="User email is required">
                    <input class="input100" type="text" name="email" placeholder="Enter your email" />
                </div>

                <div class="container-login100-form-btn">
                    <input type="submit" class="login100-form-btn" value="Send Reset Link" />
                </div>
                <div class="mt-2 col-md-12 text-center">
                    <a href="{{ route('admin.login',['subdomain'=>session()->get('subdomain')]) }}">Back to login</a>
                </div>
            </form>


        </div>
    </div>
    <footer class="lc-md">
        <div class="footer-r">Powered by <img src="{{ asset('img/client/vinetegrate-emblem.png') }}" class="footer-logo"><span><a href="http://vinetegrate.com/" target="_blank">Vinetegrate</a> - Powerful Filevine Tools</span>
            <span class="ml-4">© {{ __('Copyright') }} {{ date('Y') }} | {{ __('All Rights Reserved') }} | @version('footer-version')</span>
        </div>
    </footer>
</div>

@endsection
