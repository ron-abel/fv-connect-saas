<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
	<head>
		@include('client.includes.head')

        <style>
            .container-login:before, .container-login100:before { background-image: url("{{ !$config || is_null($config->background) ? '/assets/img/vinetegrate-bg.jpg' : '/assets/uploads/client_background/'.$config->background }}"); }
            .bg-logo { background: {{ $config->color_logo ?? '#333333' }} !important; }
            .bg-title { background: {{ $config->color_main ?? '#b0cff3' }} !important; }
            .bg-accent { background: {{ $config->color_main ?? '#185598' }} !important; }
            .text-accent { color: {{ $config->color_text ?? '#26a9e0' }} !important; }
        </style>
	</head>

	<body>
		@yield('content')

		{{-- <script src="{{ asset('js/jquery-3.2.1.min.js') }}"></script>
		<script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script> --}}

	</body>
</html>
