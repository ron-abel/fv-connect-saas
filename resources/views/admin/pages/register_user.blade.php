<?php
$config_details = DB::table('config')->where('tenant_id', $tenant_id)->first();
$tenant = App\Models\Tenant::find($tenant_id);
$is_active = 0;
if ($tenant && isset($tenant->id)) {
    $is_active = $tenant->is_active;
}
?>
@extends('admin.layouts.logindefault')

@section('title', 'Vineconnect - Register a New User')

@section('content')
<div class="limiter">
    <div class="container-login100">
        <div class="wrap-login100">
            <div class="">
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
            <div class="login100-form-title">
                <span class="login100-form-title-1">
                    Register A New User
                </span>
            </div>
			<div class="login-text">
				<p>Registering your law firm for Vineconnect Client Portal Productivity Tools is easy! Simply fill out this form to begin the self-registration flow.</p>
			</div>
            <form method="post" action="{{ route('user.register', ['subdomain' => $subdomain]) }}" class="login100-form validate-form">
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

                <div class="wrap-input100 validate-input mt-3" data-validate="Name is required">
                    <input class="input100" type="text" disabled  value="{{ $subdomain }}" disabled />
                </div>

                <div class="wrap-input100 validate-input mt-3" data-validate="Name is required">
                    <input class="input100" type="text" name="name" placeholder="Your Name" value="{{ old('name') }}" required />
                </div>

                <input type="hidden" name="user-token" value="{{ $token }}">

                @error('name')
                <p class="text-sm text-danger" id="admin-name-error">{{ $message }}</p>
                @enderror
                <div class="wrap-input100 validate-input mt-3" data-validate="Email is required">
                    <input class="input100" type="email" name="email" value="{{ isset($invite->email)?$invite->email:'' }}" disabled placeholder="Your Email" required />
                </div>

                @error('email')
                <p class="text-sm text-danger" id="email-error">{{ $message }}</p>
                @enderror

                <div class="wrap-input100 validate-input mt-3" data-validate="Password is required">
                    <input name="password" class="input100" type="password" placeholder="Enter Your Password" required />


                </div>

                @error('password')
                <p class="text-sm text-danger" id="password-error">{{ $message }}</p>
                @enderror


                <div class="wrap-input100 validate-input mt-3" data-validate="Confirm Password is required">
                    <input name="confirm_password" class="input100" type="password" placeholder="Confirm Password" required />
                </div>

                @error('confirm_password')
                <p class="text-sm text-danger" id="confirm-password-error">{{ $message }}</p>
                @enderror


                <div class="container-login100-form-btn mt-3">
                    @php
                        $disabled = "";
                        if($error) $disabled = "disabled";
                    @endphp
                    <input type="submit" {{$disabled}} class="login100-form-btn" value="Register New User" />
                </div>
            </form>
            @if($error)
                <p class="text-sm text-danger text-center">{{ $error }}</p>
            @endif
		<div class="login-text">
			<p>Need Help? Email <a href="mailto:support@vinetegrate.com" target="_blank" >Support@Vinetegrate.com</a></p>
		</div>
        </div>
    </div>
@extends('admin.layouts.footerfront')
</div>
@stop
@section('scripts')
<script src="{{ asset('../js/superadmin/register.js') }}"></script>
@endsection
