@extends('admin.layouts.logindefault')
@section('content')
<div class="main-container">
    <div class="container-login">
        <div class="wrap-login">

            <img src="{{asset('/img/client/vinetegrate-logo.png')}}">

            <p class="text-success">{{ $infoMessage }}</p>
            
        </div>
    </div>
</div>
@stop