@extends('admin.layouts.logindefault')

@section('title', 'Vineconnect - Filevine Client Portal & Productivity Tools by Vinetegrate')

@section('content')
<div class="main-container">
    <div class="container-login">
        <div class="wrap-login">
            <img style="margin:0 auto;display:block;max-height:100px;width:100%"  src="{{asset('/img/client/vineconnect_logo.png')}}" alt="Vinetegrate-Vineconnect Logo">

			<div class="container-login100-form-btn mt-3">
                    <a href="{{ route('tenant_registration') }}">
					<input style="font-family:Avenir;text-transform:uppercase;" type="submit" class="login100-form-btn" value="Create A New Account">
					</a>
                </div>
			<div style="margin-top:25px;text-align:center;">
				<a href="{{ route('super.login') }}">Super Admin Login</a>
			</div>
        </div>
    </div>
@extends('admin.layouts.footerfront')
</div>
@stop
