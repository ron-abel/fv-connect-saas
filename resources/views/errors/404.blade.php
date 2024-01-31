<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vinetegrate -- Error</title>
    <!--begin::Fonts-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
    <!--end::Fonts-->
    <link href="{{ asset('img/favicon.png') }}" rel="shortcut icon" type="image/png">
    <!--begin::Page Custom Styles(used by this page)-->
    <link href="{{ asset('css/error.css') }}" rel="stylesheet" type="text/css" />
    <!--end::Page Custom Styles-->
    <!--begin::Global Theme Styles(used by all pages)-->
    <link href="{{ asset('css/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('css/prismjs.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('css/style.bundle.admin.css') }}" rel="stylesheet" type="text/css" />
    <!--end::Global Theme Styles-->

    <style type="text/css">
        .custom-text-secondary {
            color: #585556 !important;
        }

        a {
            text-decoration: underline !important;
        }
    </style>

</head>
<!--end::Head-->
<!--begin::Body-->

<body id="kt_body"
    class="header-fixed header-mobile-fixed subheader-enabled subheader-fixed aside-enabled aside-fixed aside-minimize-hoverable page-loading bg-white">
    <!--begin::Main-->
    <div class="d-flex flex-column flex-root">
        <!--begin::Error-->
        <div class="error error-6 d-flex flex-row-fluid bgi-size-cover bgi-position-center">
            <!--begin::Content-->
            <div class="d-flex flex-column flex-row-fluid text-center">
                <h1 class="display-1 font-weight-boldest" style="margin-top: 3rem; color:#26A9E0">UH OH.</h1>
                <h2 class="font-weight-bold custom-text-secondary">A connection with our servers has been interrupted.
                    <br> Please email us at
                    <a href="mailto:vineconnect@vinetegrate.com"
                        class="text-dark text-decoration-underline">vineconnect@vinetegrate.com</a>
                </h2>
                <div class="text-center mt-12">
                    <img src="{{ asset('img/vineconnect-404-icon.png') }}" class="img-fluid" alt="vineconnect 404 icon">
                </div>
            </div>
            <!--end::Content-->
        </div>
        <!--end::Error-->
    </div>
    <!--end::Main-->

    <!--begin::Global Theme Bundle(used by all pages)-->
    <script src="{{ asset('js/plugins.bundle.js') }}"></script>
    <!--end::Global Theme Bundle-->

</body>
<!--end::Body-->

</html>
