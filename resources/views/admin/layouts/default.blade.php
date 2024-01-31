@php
    $tenant = App\Models\Tenant::find($cur_tenant_id);
    $all_plans = App\Services\SubscriptionService::getAllPlans();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('admin.includes.head')
</head>
@yield('css')


<body id="kt_body" class="header-fixed header-mobile-fixed subheader-enabled subheader-fixed aside-enabled aside-fixed aside-minimize-hoverable page-loading">
    <!--begin::Main-->
    <!--begin::Header Mobile-->
    <div id="kt_header_mobile" class="header-mobile align-items-center header-mobile-fixed">
        <!--begin::Logo-->
        <a href="#" class="brand-logo">
            @if(isset($config_details->logo))
            <img src="{{ asset('uploads/client_logo/' . $config_details->logo) }}" style="width:100%;" alt="Logo">
            @else
            <img src="{{ asset('img/client/vineconnect_logo_white.png') }}" style="width:100%;" alt="VineConnect Logo">
            @endif
        </a>
        <!--end::Logo-->
        <!--begin::Toolbar-->
        <div class="d-flex align-items-center">
            <!--begin::Aside Mobile Toggle-->
            <button class="btn p-0 burger-icon burger-icon-left" id="kt_aside_mobile_toggle">
                <span></span>
            </button>
            <!--end::Aside Mobile Toggle-->
            <!--begin::Header Menu Mobile Toggle-->
            <button class="btn p-0 btn-hover-text-primary ml-4" id="kt_header_mobile_toggle">
                <span>
                    <i class="fa fa-user fa-2x"></i>
                </span>
            </button>
            <!--end::Header Menu Mobile Toggle-->
        </div>
        <!--end::Toolbar-->
    </div>
    <!--end::Header Mobile-->
    <div class="d-flex flex-column flex-root">
        <!--begin::Page-->
        <div class="d-flex flex-row flex-column-fluid page">
            <!--begin::Aside-->
            @include('admin.includes.header')
            <!--end::Aside-->
            <!--begin::Wrapper-->
            <div class="d-flex flex-column flex-row-fluid wrapper" id="kt_wrapper">
                <!--begin::Header-->
                <div id="kt_header" class="header header-fixed">
                    <!--begin::Container-->
                    <div class="container-fluid d-flex align-items-stretch justify-content-end">
                        <!-- <div class="header-menu-wrapper-overlay"></div> -->
                        <div class="header-menu-wrapper header-menu-wrapper-left" id="kt_header_menu_wrapper">
                            <div id="kt_header_menu" class="header-menu header-menu-mobile header-menu-layout-default">
                                <ul class="menu-nav">
                                    <li class="menu-item menu-item-submenu menu-item-rel" aria-haspopup="true">
                                        <a style="background-color:#26A9DF;color:#fff;" href="https://intercom.help/vinetegrate/en/collections/3829923-vineconnect-client-portal" target="_blank" class="menu-link">
                                            <span class="menu-text" style="color:#fff;"><b>Support</b></span>
                                            <i class="menu-arrow"></i>
                                        </a>
                                    </li>

									<li class="menu-item menu-item-submenu menu-item-rel" aria-haspopup="true">
                                        <a href="{{ route('billing', ['subdomain' => $subdomain]) }}" class="menu-link">
                                            <span class="menu-text">{{Auth::user()->full_name ?? 'Profile'}}</span>
                                            <i class="menu-arrow"></i>
                                        </a>
                                    </li>

                                    <li class="menu-item menu-item-submenu menu-item-rel" data-menu-toggle="click" aria-haspopup="true">
                                        <a href="{{ route('logout', ['subdomain' => $subdomain]) }}" class="menu-link">
                                            <span class="menu-text">Logout</span>
                                            <i class="menu-arrow"></i>
                                        </a>
                                    </li>

                                </ul>
                            </div>
                        </div>
                    </div>
                    <!--end::Container-->
                </div>
                <!--end::Header-->
                <!--begin::Content-->
                <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
                    @if($tenant->upgrade_stripe_price && isset($all_plans[$tenant->upgrade_stripe_price]))
                    <div class="container justify-content-center">
                        <div class="alert alert-info" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <p>Please upgrade your Billing Price Plan to <strong>{{  $all_plans[$tenant->upgrade_stripe_price] }}</strong></p>
                        </div>
                    </div>
                    @endif
                    @yield('content')
                </div>
                <!--end::Content-->
                <!--begin::Footer-->
                @include('admin.includes.footer')
                <!--end::Footer-->
            </div>
            <!--end::Wrapper-->
        </div>
        <!--end::Page-->
    </div>
    <!--end::Main-->

    <!--start::Terms Modal-->
    <div class="modal fade" id="termsModal" tabindex="-1" role="dialog" aria-labelledby="termsModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="subscriptionModalLabel">Software License & Agreement</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-check col-md-12 p-0">
                        <p>This Services Agreement for VineConnect Client Portal SaaS Product (the "Agreement") is made and entered into by and between you ("Client"), and Goldenfarb Copeland FV Software Ventures, LLC d/b/a “Vinetegrate”, a Florida corporation having its principal place of business at 275 Toney Penna Dr. Suite 8, Jupiter, FL 33458 ("Vinetegrate"), collectively referred to as the "Parties."</p>
                        <h5>RECITALS</h5>
                        <p>WHEREAS, Vinetegrate has developed and owns a software-as-a-service (SaaS) product called "VineConnect Client Portal" (the "Product");</p>
                        <p>WHEREAS, Client desires to use the Product, and Vinetegrate agrees to grant Client the right to use the Product subject to the terms and conditions of this Agreement; and</p>
                        <p>WHEREAS, Vinetegrate desires to provide maintenance and support services to Client for the Product in accordance with the terms and conditions of this Agreement.</p>
                        <p>NOW, THEREFORE, in consideration of the mutual promises and covenants contained herein, the Parties agree as follows:</p>
                        <h6>License Grant</h6>
                        <p>Subject to the terms and conditions of this Agreement, Vinetegrate hereby grants to Client a limited, non-exclusive, non-transferable license to access and use the Product during the Term (as defined below) solely for its internal business purposes.</p>
                        <h6>Fees and Payment</h6>
                        <p>Client shall pay Vinetegrate the fees set forth by the service plan chosen for the license to use the Product and the maintenance and support services provided under this Agreement. The fees shall be payable on a monthly subscription-based plan, due on the day of the month in which the billing plan is set up. All fees are non-refundable.</p>
                        <h6>Term and Termination</h6>
                        <p>Either Party may terminate this Agreement upon written notice to the other Party in the event of a material breach of this Agreement by the other Party that remains uncured for 30 days after written notice of such breach is given to the breaching Party. Vinetegrate may also terminate this Agreement immediately if Client fails to pay any undisputed fees when due.</p>
                        <h6>Ownership and Intellectual Property</h6>
                        <p>The Product and all intellectual property rights therein and related thereto are and shall remain the exclusive property of Vinetegrate or its licensors. Nothing in this Agreement shall be construed as granting Client any ownership rights in the Product or any intellectual property rights therein or related thereto.</p>
                        <h6>Confidentiality</h6>
                        <p>Each Party agrees to keep confidential all information received from the other Party in connection with this Agreement, except to the extent necessary to perform its obligations under this Agreement or as required by law. We take protection of your data seriously. Please see our Privacy Policy (<a target="_blank" href="https://vinetegrate.com/privacy-policy/">https://vinetegrate.com/privacy-policy/</a>) and our Data Security declaration (<a target="_blank" href="https://vinetegrate.com/data-security/">https://vinetegrate.com/data-security/</a>) for more information.</p>
                        <h6>Warranties and Disclaimers</h6>
                        <p>Vinetegrate represents and warrants that it has the right and authority to enter into this Agreement and to grant the license to use the Product as set forth herein. Vinetegrate further represents and warrants that the Product will perform in all material respects in accordance with the documentation provided by Vinetegrate.</p>
                        <p>EXCEPT AS EXPRESSLY SET FORTH IN THIS AGREEMENT, VINTEGRATE MAKES NO REPRESENTATIONS OR WARRANTIES OF ANY KIND, WHETHER EXPRESS, IMPLIED, STATUTORY OR OTHERWISE, AND VINTEGRATE SPECIFICALLY DISCLAIMS ALL IMPLIED WARRANTIES, INCLUDING WITHOUT LIMITATION ANY WARRANTIES OF MERCHANTABILITY</p>
                    </div>
                    <div class="form-check col-md-12 ">
                        <input class="form-check-input" type="checkbox" id="terms_check">
                        <label style="font-size:14px;" class="ml-1" for="terms_check">
                            I've read the terms.
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <p class="terms-error"></p>
                    <button type="button" class="btn btn-primary terms-btn" data-target="agree">I AGREE</button>
                    <button type="button" class="btn btn-secondary terms-btn" data-target="disagree">I DISAGREE</button>
                </div>
            </div>
        </div>
    </div>
    <!--end::Terms Modal-->

    <script>
        var HOST_URL = "";
    </script>
    <!--begin::Global Config(global config for global JS scripts)-->
    <script>
        var KTAppSettings = {
            "breakpoints": {
                "sm": 576,
                "md": 768,
                "lg": 992,
                "xl": 1200,
                "xxl": 1400
            },
            "colors": {
                "theme": {
                    "base": {
                        "white": "#ffffff",
                        "primary": "#3699FF",
                        "secondary": "#E5EAEE",
                        "success": "#1BC5BD",
                        "info": "#8950FC",
                        "warning": "#FFA800",
                        "danger": "#F64E60",
                        "light": "#E4E6EF",
                        "dark": "#181C32"
                    },
                    "light": {
                        "white": "#ffffff",
                        "primary": "#E1F0FF",
                        "secondary": "#EBEDF3",
                        "success": "#C9F7F5",
                        "info": "#EEE5FF",
                        "warning": "#FFF4DE",
                        "danger": "#FFE2E5",
                        "light": "#F3F6F9",
                        "dark": "#D6D6E0"
                    },
                    "inverse": {
                        "white": "#ffffff",
                        "primary": "#ffffff",
                        "secondary": "#3F4254",
                        "success": "#ffffff",
                        "info": "#ffffff",
                        "warning": "#ffffff",
                        "danger": "#ffffff",
                        "light": "#464E5F",
                        "dark": "#ffffff"
                    }
                },
                "gray": {
                    "gray-100": "#F3F6F9",
                    "gray-200": "#EBEDF3",
                    "gray-300": "#E4E6EF",
                    "gray-400": "#D1D3E0",
                    "gray-500": "#B5B5C3",
                    "gray-600": "#7E8299",
                    "gray-700": "#5E6278",
                    "gray-800": "#3F4254",
                    "gray-900": "#181C32"
                }
            },
            "font-family": "Poppins"
        };
    </script>
    <!--end::Global Config-->
    <!--begin::Global Theme Bundle(used by all pages)-->
    <script src="{{ asset('js/plugins.bundle.js') }}"></script>
    <script src="{{ asset('js/prismjs.bundle.js') }}"></script>
    <script src="{{ asset('js/scripts.bundle.js') }}"></script>
    <!--end::Global Theme Bundle-->
    <!--begin::Page Vendors(used by this page)-->
    <script src="{{ asset('js/fullcalendar.bundle.js') }}"></script>
    <script src="{{ asset('js/datatables.bundle.js') }}"></script>
    <script src="{{ asset('js/bootstrap-datetimepicker.js') }}"></script>
    <!--end::Page Vendors-->
    <!--begin::Page Scripts(used by this page)-->
    <script src="{{ asset('js/widgets.min.js') }}"></script>
    <!--end::Page Scripts-->

    <script src="{{ asset('js/select2.js') }}"></script>
    <script src="{{ asset('js/bootstrap-select.js') }}"></script>


    <!--Custom JavaScript -->
    <script src="{{ asset('../js/admin/custom.js') }}"></script>
    <!--flot chart-->
    {{-- <script src="{{ asset('js/jquery.flot.js') }}"></script>
    <script src="{{ asset('js/jquery.flot.tooltip.min.js') }}"></script>
    <script src="{{ asset('js/jquery.flot.time.js') }}"></script> --}}
    <script src="{{ asset('../js/dashboard1.js') }}"></script>
    <script src="{{ asset('../js/checkout.js') }}"></script>

    @if(request()->is('admin/support') )
    <script src="{{ asset('js/jquery-3.2.1.min.js') }}"></script>
    <script src="{{ asset('js/lightbox.min.js') }}"></script>
    @endif

    <script src="{{ asset('js/html-table.min.js') }}"></script>

    @if(request()->is('admin/mass_messages') )
    <script src="{{ asset('../js/mass_messages.js?20230721') }}"></script>
    @endif

    @if(request()->is('admin/phase_mapping') )
    <script src="{{ asset('../js/phase_mapping.js') }}"></script>
    @endif
    @if(request()->is('admin/phase_categories') )
    <script src="{{ asset('../js/phase_category.js') }}"></script>
    @endif


    @if(request()->is('admin/webhooks') )
    <script src="{{ asset('../js/webhook.js') }}"></script>
    @endif

    @if(request()->is('admin/mass_updates') )
    <script src="{{ asset('../js/contact.js') }}"></script>
    @endif

    @if(request()->is('admin/phase_change_automated_communications'))
    <script src="{{ asset('../js/automated_communication.js') }}"></script>
    @endif
    @if(request()->is('admin/google_review_automated_communications'))
    <script src="{{ asset('../js/google_review.js') }}"></script>
    @endif

    @if(request()->is('admin/mass_emails') )
    <script src="{{ asset('../js/mass_emails.js?20230721') }}"></script>
    @endif

    @yield('scripts')

    <!--begin::Check if tenant has not accepted terms already -->
    @if($tenant && !$tenant->is_accept_license)
    <script>
        $(document).ready(function() {
            $('#termsModal').modal('show');

            // change terms status
            $(document).on('click', '.terms-btn', function() {
                $('.terms-error').removeClass('text-danger');
                $('.terms-error').text('');
                // check for checkbox
                var terms_check = $('#terms_check').prop('checked');
                var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                var tenant_id = "{{$tenant->id}}";
                var status = $(this).attr('data-target');

                if(!terms_check && status == 'agree') {
                    $('.terms-error').addClass('text-danger');
                    $('.terms-error').text('Please tick accept checkbox');
                    return false;
                }


                $('.terms-error').text('PROCESSING....');
                // send ajax request
                $.ajax({
                    url: "{{url('admin/accept_terms')}}",
                    type: 'POST',
                    data: {
                        '_token': CSRF_TOKEN,
                        'tenant_id': tenant_id,
                        'status': status
                    },
                    dataType: 'JSON',
                    success: function(data) {
                        location.reload();
                    },
                    error: function() {
                        $('.terms-error').text('');
                    }
                });
            });

        });
    </script>
    @endif
    <!--end::Check if tenant has not accepted terms already -->

</body>
<!--end::Body-->

</html>
