@extends('admin.layouts.logindefault')

@section('title', 'Vineconnect - Super Admin Login Credentials Required')

@section('content')
<div class="main-container">
    <div class="container-login">
        <div class="wrap-login">
            <div class="logo">
                <a href="{{route('super.welcome')}}">
                    <img src="{{ asset('img/client/vineconnect_logo.png') }}" style="width:100%;max-height:100px;"  alt="VineConnect Logo">
                </a>
            </div>
            <form method="post" action="{{ route('login_post', ['subdomain' => config('app.superadmin') ]) }}" class="login-form validate-form">
                @csrf
                @if ( session()->has('error') )
                <div style="width:100%; padding:5px; font-size:13px; font-weight:bold; color:#f00; text-align:center;">
                    {{ session()->get('error') }}
                </div>
                @endif
                @if(session()->has('success'))
                <div class="text-success" style="width:100%; padding:5px; font-size:13px; font-weight:bold;  text-align-center">
                    {{ session()->get('success') }}
                </div>
                @endif
                @if(session()->has('info'))
                    <div class="text-info" style="width:100%; padding:5px; font-size:13px; font-weight:bold; text-align-center">
                        {{ session()->get('info') }}
                    </div>
                @endif

				<div class="login100-form-title">
                	<span class="login100-form-title-1">
                    Super Admin Login
                	</span>
	            </div>

                <div class="wrap-input validate-input mt-2" data-validate="User email is required">
                    <input class="input" type="email" name="email" placeholder="Enter your email" required />
                </div>

                @error('email')
                <p class="mt-2 text-sm text-red-600" id="email-error">{{ $message }}</p>
                @enderror

                <div class="wrap-input validate-input" data-validate="Password is required">
                    <input name="password" class="input" type="password" placeholder="Enter Your Password" required />
                </div>

                @error('email')
                <p class="mt-2 text-sm text-red-600" id="email-error">{{ $message }}</p>
                @enderror

                <div class="form-check col-md-12 ml-1">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label style="font-size:14px;" class="" for="remember">
                        Remember Me
                    </label>
                </div>

				<div class="container-login-form-btn">
                    <input type="submit" class="login-form-btn" value="Login" />
                </div>

                <div class="col-md-12 text-center pt-2">
                    <a href="/forgot_password">Forgot Password?</a>
                </div>
            </form>
        </div>
    </div>
</div>
@stop
