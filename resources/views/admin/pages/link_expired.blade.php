@extends('admin.layouts.logindefault')
@section('content')
<div class="main-container">
    <div class="container-login">
        <div class="wrap-login">

            <img src="{{asset('/img/client/vinetegrate_logo.png')}}">

            <p>This link has been expired. Please try again</p>
            <a href="/forgot_password"> Reset Password </a>
            <br>
            <a href="{{ route('admin.login',session()->get('subdomain')) }}">Login</a>
            <br>
        </div>
    </div>
</div>
@stop
