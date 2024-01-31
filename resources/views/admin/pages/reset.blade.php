@extends('admin.layouts.logindefault')

@section('title', 'VineConnect Admin Login')

@section('content')
<div class="limiter">
    <div class="container-login100">
        <div class="wrap-login100">
            <div class="">
                <a href="">
                    <img src="{{ asset('img/client/vineconnect_logo.png') }}" alt="Logo" class="login-logo">
                </a>

            </div>
            <div class="login100-form-title">
                <span class="login100-form-title-1">
                    Reset Password
                </span>
            </div>
            <form method="post" action="{{ url('/reset_password_with_token') }}" class="login100-form validate-form" id="reset-form">
                @csrf
                <input value="{{ $token }}" name="token" hidden>
                <input value="{{ $email }}" name="email" hidden>
                @if ( session()->has('error') )
                <div style="width:100%; padding:5px; font-size:13px; font-weight:bold; color:#f00; text-align:center;">
                    {{ session()->get('error') }}
                </div>
                @endif
                @if(session()->has('status'))
                <div style="width:100%; padding:5px; font-size:13px; font-weight:bold; color:rgb(26, 243, 232); text-align:center;">
                    {{ session()->get('status') }}
                </div>
                @endif


                <div class="
                    wrap-input100
                    validate-input
                    mt-3" data-validate="Password is required">
                    <input class="input100" type="password" name="password" id="new-password" placeholder="Enter your password" />
                </div>
                <p class="mt-2 text-danger text-sm text-red-600" id="password-error">
                    @error('password')
                    {{ $message }}
                    @enderror
                </p>
                <div class="
                    wrap-input100
                    validate-input
                    mt-3" data-validate="Confirm Password is required">
                    <input class="input100" type="password" name="confirm_password" id="confirm-password" placeholder="Confirm Your Password" />
                </div>

                <p class="mt-2 text-danger text-sm text-red-600" id="confirm-password-error">
                    @error('confirm_password')
                    {{ $message }}
                    @enderror
                </p>

                <div class="container-login100-form-btn">
                    <input type="submit" class="login100-form-btn mt-3" value="Reset Password" id="submit-button" />
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
@section('script')
<script>
    function validateForm(event) {
        let new_password = $('#new-password').val()
        let confirm_password = $('#confirm-password').val()
        let password_error = $('#password-error')
        let confirm_password_error = $('#confirm-password-error')
        password_error.text(' ')
        confirm_password_error.text(' ')

        if (!new_password || new_password == '' || new_password == null) {
            event.preventDefault()
            password_error.text('Password is required')
        }

        if (!confirm_password || confirm_password == '' || confirm_password == null) {
            event.preventDefault()
            confirm_password_error.text('Confirm Password is required')
        } else {
            if (new_password.length < 6) {
                event.preventDefault()
                password_error.text('Password length should be more than 6 letters.')
            }
            if (new_password != confirm_password) {
                event.preventDefault()
                confirm_password_error.text('Confirm Password is invalid.')
            }
        }
    }

    $('#reset-form').submit(function(event) {
        validateForm(event)
    })
</script>
@endsection
