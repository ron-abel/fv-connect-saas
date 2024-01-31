<?php
$config_details = DB::table('config')->where('tenant_id', $tenant_id)->first();
$tenant = App\Models\Tenant::find($tenant_id);
$is_active = 0;
if ($tenant && isset($tenant->id)) {
    $is_active = $tenant->is_active;
}
?>
@extends('admin.layouts.logindefault')

@section('title', 'VineConnect Admin Login')

@section('content')
<div class="limiter">
    <div class="container-login100">
        <div class="wrap-login100">
            <div class="bg-logo p-2">
                @if(isset($config_details->logo))
                <a href="">
                    <img src="{{ asset('uploads/client_logo/' . $config_details->logo) }}" alt="Logo" class="login-logo">
                </a>
                @else
                <a href="">
                    <img src="{{ asset('img/client/vineconnect_logo.png') }}" alt="VineConnect Logo" class="login-logo">
                </a>
                @endif
            </div>
            <div class="login100-form-title bg-title">
                <span class="login100-form-title-1">
                    Tenant Admin Login
                </span>
            </div>
            <form method="post" action="{{ route('login_post', ['subdomain' => $subdomain ,'token'=> request()->get('token', '')]) }}" class="login100-form validate-form">
                @csrf
                @if ( session()->has('error') )
                <div style="width:100%; padding:5px; font-size:13px; font-weight:bold; color:#f00; text-align:center;">
                    {{ session()->get('error') }}
                </div>
                @endif
                @if(session()->has('success'))

                <div class="text-success" style="width:100%; padding:5px; font-size:13px; font-weight:bold; text-align-center">
                    {{ session()->get('success') }}
                </div>
                @endif
                @if(session()->has('info'))
                <div class="text-info" style="width:100%; padding:5px; font-size:13px; font-weight:bold; text-align-center">
                    {{ session()->get('info') }}
                </div>
                @endif

                <div class="wrap-input100 validate-input m-b-20 mt-2" data-validate="User email is required">
                    <input class="input100" type="email" name="email" placeholder="Enter your email" required />
                </div>

                @error('email')
                <p class="mt-2 text-sm text-red-600" id="email-error">{{ $message }}</p>
                @enderror

                <div class="wrap-input100 validate-input m-b-15" data-validate="Password is required">
                    <input name="password" class="input100" type="password" placeholder="Enter Your Password" required />


                </div>

                @error('email')
                <p class="mt-2 text-sm text-red-600" id="email-error">{{ $message }}</p>
                @enderror

                <div class="form-check col-md-12  ml-1">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label style="font-size:14px;" class="" for="remember">
                        Remember Me
                    </label>
                </div>

                <div class="container-login100-form-btn">
                    @if($is_active && $tenant && $tenant->is_verified)
                    <input type="submit" class="login100-form-btn bg-accent" value="Login" />
                    @else
                    <button disabled="disabled" type="button" role="button" class="login-form-btn login100-form-btn bg-accent">Login</button>
                    @endif
                </div>


                <div class="col-md-12 text-center">
                    <a href="/forgot_password text-accent">Forgot Password?</a>
                </div>
                @if(!$tenant)
                <div class="col-md-12 text-center">
                    <p class="text-danger mt-2">
                        The Tenant is invalid. Please ask to support team.
                    </p>
                </div>
                @elseif(!$tenant->is_verified)
                <div class="col-md-12 text-center">
                    <p class="text-danger mt-2">
                        The Tenant is unverified now, Please ask to support team.
                    </p>
                </div>
                @else
                @if(!$is_active)
                <div class="col-md-12 text-center">
                    <p class="text-danger mt-2">
                        This portal is inactive. Please email vineconnect@vinetegrate.com for help.
                    </p>
                </div>
                @endif
                @endif
            </form>

        </div>
    </div>
    <footer class="lc-md bg-accent">
        <div class="footer-r">Powered by <img src="{{ asset('img/client/vinetegrate-emblem.png') }}" class="footer-logo"><span><a href="http://vinetegrate.com/" target="_blank">Vinetegrate</a> - VineConnect Client Portal</span>
            <span class="ml-4">© {{ __('Copyright') }} {{ date('Y') }} | {{ __('All Rights Reserved') }} | @version('footer-version')</span>
        </div>
    </footer>
</div>
@php
    $success = session()->get('successMessage');
    session()->forget('successMessage');
@endphp
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
</script>
@endsection
