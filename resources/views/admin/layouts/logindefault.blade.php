<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title')</title>
    
    <link href="/assets/img/favicon.png" rel="shortcut icon" type="image/png">
    <link href="{{ asset('css/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" type="text/css" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/font-awesome.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main_client.css') }}">
    <link rel="stylesheet" type="text/css" href="/css/superadmin/custom.css">
    <link rel="stylesheet" type="text/css" href="/css/admin/custom.css">
    
    @yield('css')
{{--
    <style>
        .container-login:before, .container-login100:before { background-image: url("{{ !$config || is_null($config->background) ? '/assets/img/vinetegrate-bg.jpg' : '/assets/uploads/client_background/'.$config->background }}"); }
        .bg-logo { background: {{ $config->color_logo ?? '#333333' }} !important; }
        .bg-title { background: {{ $config->color_main ?? '#b0cff3' }} !important; }
        .bg-accent { background: {{ $config->color_main ?? '#185598' }} !important; }
        .text-accent { color: {{ $config->color_text ?? '#26a9e0' }} !important; }
    </style>
    --}}
</head>

<body>
    @yield('content')

    <script src="{{ asset('js/plugins.bundle.js') }}"></script>
    <script src="{{ asset('js/jquery-3.2.1.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    @yield('scripts')

</html>