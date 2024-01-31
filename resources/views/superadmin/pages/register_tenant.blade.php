@extends('admin.layouts.logindefault')

@section('title', 'Vineconnect - Register a New Account')

@section('content')
<div class="limiter">
    <div class="container-login100">
        <div class="wrap-login100">
            <div class="">
                <a href="">
                    <img src="{{ asset('img/client/vineconnect_logo.png') }}" alt="VineConnect Logo" class="login-logo">
                </a>
            </div>
            <div class="login100-form-title">
                <span class="login100-form-title-1">
                    Register A New Account
                </span>
            </div>
			<div class="login-text">
				<p style="color:white">INSTRUCTIONS: Your Filevine Login URL should be either https://app.filevine.com or https://{lawfirm}.filevineapp.com. Please ensure the cell phone number you input below is SMS-enabled to receive text messages. Registering your new account requires an email verification for security purposes.</p>
			</div>
            <form method="post" id="registerForm" action="{{ route('submit_tenant_registration') }}" class="login100-form validate-form">
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
                <div class="wrap-input100 validate-input" data-validate="Filevine Login URL is required">
                    <label class="font-weight-bold">Your Filevine Login URL</label>
                    <input class="input100" type="text" id="fv_tenant_base_url" name="fv_tenant_base_url" value="{{ old('fv_tenant_base_url') }}" placeholder="https://app.filevine.com or https://{lawfirm}.filevineapp.com" required />
                </div>
                <p class="text-sm text-danger" id="tenant_name-error"></p>

                <div class="wrap-input100 validate-input mt-3" data-validate="Unique Identifying Name is required">
                    <label class="font-weight-bold">Choose a Branded Name for Client Portal URL</label>
                    <input class="input100" type="text" id="tenant_name" name="tenant_name" value="{{ old('tenant_name') }}" placeholder='Like "Lawfirm"' required />
                </div>

                @error('tenant_name')
                <p class="text-sm text-danger" id="tenant_name-error">{{ $message }}</p>
                @enderror

                <div class="wrap-input100 validate-input mt-3" data-validate="Admin Name is required">
                    <label class="font-weight-bold">Your Name</label>
                    <input class="input100" type="text" name="admin-name" placeholder="First and Last Name" value="{{ old('admin-name') }}" required />
                </div>
                @error('admin-name')
                <p class="text-sm text-danger" id="admin-name-error">{{ $message }}</p>
                @enderror

                <div class="wrap-input100 validate-input mt-3" data-validate="Email is required">
                    <label class="font-weight-bold">Your Email Address</label>
                    <input class="input100" type="email" name="email" value="{{ old('email') }}" placeholder='Requires "@" Format' required />
                </div>
                @error('email')
                <p class="text-sm text-danger" id="email-error">{{ $message }}</p>
                @enderror


                <div class="wrap-input100 validate-input mt-3" data-validate="SMS Enabled Cell Phone Number is required">
                    <label class="font-weight-bold">Your Cell Phone or SMS-Capable Number</label>
                    <input class="input100" type="text" name="test_tfa_number" value="{{ old('test_tfa_number') }}" placeholder="10-Digit Numbers Only" required />
                </div>
                @error('test_tfa_number')
                <p class="text-sm text-danger" id="test_tfa_number-error">{{ $message }}</p>
                @enderror


                <div class="wrap-input100 validate-input mt-3" data-validate="Password is required">
                    <label class="font-weight-bold">Create Password</label>
                    <input name="password" class="input100" type="password" placeholder="" required />
                </div>

                @error('password')
                <p class="text-sm text-danger" id="password-error">{{ $message }}</p>
                @enderror


                <div class="wrap-input100 validate-input mt-3" data-validate="Confirm Password is required">
                    <label class="font-weight-bold">Confirm Password</label>
                    <input name="confirm_password" class="input100" type="password" placeholder="" required />
                </div>

                @error('confirm_password')
                <p class="text-sm text-danger" id="confirm-password-error">{{ $message }}</p>
                @enderror


                <div class="container-login100-form-btn mt-3">
                    <input type="submit" class="login100-form-btn" value="Register New Account" />
                </div>
            </form>
		<div class="login-text">
			<p>Need Help? Email <a href="mailto:support@vinetegrate.com" target="_blank" >Support@Vinetegrate.com</a></p>
		</div>
        </div>
    </div>
@extends('admin.layouts.footerfront')
</div>

<!-- Intercom Snippet -->
<script>
  window.intercomSettings = {
    app_id: "yskhl95g",
    name: "", 							
    email: "", 								
	user_id: "",		 						
	tenant_name: "",						
	tenant_law_firm_name: "", 		
    user_hash: "{{hash_hmac( 'sha256', rand() , config('services.intercom.identiy_secret_key')) }}"  // when identity verification, used.
  };
</script>
<script>
(function(){var w=window;var ic=w.Intercom;if(typeof ic==="function"){ic('reattach_activator');ic('update',w.intercomSettings);}else{var d=document;var i=function(){i.c(arguments);};i.q=[];i.c=function(args){i.q.push(args);};w.Intercom=i;var l=function(){var s=d.createElement('script');s.type='text/javascript';s.async=true;s.src='https://widget.intercom.io/widget/yskhl95g';var x=d.getElementsByTagName('script')[0];x.parentNode.insertBefore(s,x);};if(document.readyState==='complete'){l();}else if(w.attachEvent){w.attachEvent('onload',l);}else{w.addEventListener('load',l,false);}}})();
</script>

@stop
@section('scripts')
<script src="{{ asset('../js/superadmin/register.js') }}"></script>
@endsection
