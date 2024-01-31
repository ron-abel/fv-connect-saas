@extends('client.layouts.default')

@section('title', 'VineConnect Client Portal - Client Form')

@section('content')
    <link href="{{ asset('uppy/uppy.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('css/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .language-select {
            cursor: pointer;
            position: relative;
            width: 20%;
            font-weight: 500;
            font-size: 16px;
            line-height: 26px;
        }

        .language-icon {
            max-width: 20px;
            margin-right: 4px;
        }

        #language-dropdown {
            position: absolute;
            background: #0F2C4D;
            padding: 2px;
        }

        #language-dropdown li {
            margin: 5px 0px;
            padding: 5px;
        }

        #language-dropdown li a:hover {
            color: #ffc107 !important
        }

        @media screen and (max-width: 768px) {
            .language-select {
                width: 55%;
            }
        }

        .field-add_collection_item {
            text-align: right !important;
        }

        .add-collection-item {
            cursor: pointer;
            text-decoration: none;
        }
    </style>
    <?php $note_id = ''; ?>
    <div class="client-portal-body">
        <div class="accordion-started accordion-bral row">
            <div class="w-100">
                <input class="ac-input" id="ac-{{ 1 }}" name="accordion-1" type="radio"
                    {{ $lookup_data['active_project_id'] == $lookup_data['project_id'] ? 'checked' : '' }}>
                @if (isset($lookup_data['config_details']))
                    @if (isset($lookup_data['project_override_name']) && !empty($lookup_data['project_override_name']))
                        <label class="ac-label bg-title"
                            for="ac-{{ 1 }}"><span>{{ $lookup_data['project_override_name'] }}</span><i></i></label>
                    @elseif (isset($lookup_data['results']['projectName']))
                        <label class="ac-label bg-title"
                            for="ac-{{ 1 }}"><span>{{ $lookup_data['results']['projectName'] }}</span><i></i></label>
                    @else
                        <label class="ac-label bg-title" for="ac-{{ 1 }}"><span></span><i></i></label>
                    @endif
                    <div class="article ac-content">
                        <header class="bg-logo">
                            <div class="container-fluid">
                                <div class="row align-items-center justify-content-between">
                                    <div class="col-md-4 col-sm-0">
                                        @if (isset($lookup_data['last_login']))
                                            <p>{{ __('Last Login:') }} {{ $lookup_data['last_login'] }}</time></p>
                                        @else
                                            <p>{{ __('Last Login:') }} </time></p>
                                        @endif
                                        <span class="icon_menu" onclick="openNav();openNav3()"><img
                                                src="{{ asset('img/client//menu.png') }}" alt=""></span>
                                    </div>
                                    <div class="col-md-4 col-sm-5">
                                        @if (isset($lookup_data['config_details']->logo))
                                            <a href="/lookup/{{ $lookup_project_id }}" class="d-flex align-items-center">
                                                <img src="{{ asset('uploads/client_logo/' . $lookup_data['config_details']->logo) }}"
                                                    alt="{{ __('Logo') }}" class="login-logo">
                                            </a>
                                        @else
                                            <a href="/lookup/{{ $lookup_project_id }}" class="d-flex align-items-center">
                                                <img src="{{ asset('img/client/vineconnect_logo.png') }}"
                                                    alt="VineConnect Logo" class="login-logo">
                                            </a>
                                        @endif
                                    </div>
                                    <div class="col-md-4 col-sm-5 d-flex justify-content-end align-items-center">
                                        <div class="language-select" id="language-select">
                                            @if (!is_null(session('lang')))
                                                @if (session('lang') == 'es')
                                                    <div class="text-lang-color text-white">
                                                        Español <i class="fas fa-angle-down"></i>
                                                    </div>
                                                @elseif(session('lang') == 'fr')
                                                    <div class="text-lang-color text-white">
                                                        Français <i class="fas fa-angle-down"></i>
                                                    </div>
                                                @elseif(session('lang') == 'pt')
                                                    <div class="text-lang-color text-white">
                                                        Português <i class="fas fa-angle-down"></i>
                                                    </div>
                                                @else
                                                    <div class="text-lang-color text-white">
                                                        English <i class="fas fa-angle-down"></i>
                                                    </div>
                                                @endif
                                            @else
                                                <div class="text-lang-color text-white">
                                                    English <i class="fas fa-angle-down"></i>
                                                </div>
                                            @endif
                                            <ul id="language-dropdown">
                                                <li><a
                                                        href="{{ route('client_active_forms', ['lookup_project_id' => $lookup_data['active_project_id'], 'subdomain' => session()->get('subdomain'), 'lang' => 'en']) }}">English</a>
                                                </li>
                                                <li><a
                                                        href="{{ route('client_active_forms', ['lookup_project_id' => $lookup_data['active_project_id'], 'subdomain' => session()->get('subdomain'), 'lang' => 'es']) }}">Español</a>
                                                </li>
                                                <li><a
                                                        href="{{ route('client_active_forms', ['lookup_project_id' => $lookup_data['active_project_id'], 'subdomain' => session()->get('subdomain'), 'lang' => 'fr']) }}">Français</a>
                                                </li>
                                                <li><a
                                                        href="{{ route('client_active_forms', ['lookup_project_id' => $lookup_data['active_project_id'], 'subdomain' => session()->get('subdomain'), 'lang' => 'pt']) }}">Português</a>
                                                </li>
                                            </ul>
                                        </div>

                                        <div class="client-header-dropdown" onclick="showDropdown()">
                                            <svg id="client-header-dropdown1" width="50" height="50"
                                                viewBox="0 0 50 50" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <rect width="50" height="50" rx="6" fill="{{$config->color_main ?? '#26A9DF'}}" />
                                                <rect x="20" y="16" width="3" height="3"
                                                    fill="white" />
                                                <rect x="13" y="16" width="3" height="3"
                                                    fill="white" />
                                                <rect x="13" y="23" width="3" height="3"
                                                    fill="white" />
                                                <rect x="13" y="30" width="3" height="3"
                                                    fill="white" />
                                                <rect x="20" y="23" width="3" height="3"
                                                    fill="white" />
                                                <rect x="20" y="30" width="3" height="3"
                                                    fill="white" />
                                                <rect x="27" y="16" width="3" height="3"
                                                    fill="white" />
                                                <rect x="27" y="23" width="3" height="3"
                                                    fill="white" />
                                                <rect x="27" y="30" width="3" height="3"
                                                    fill="white" />
                                                <rect x="34" y="16" width="3" height="3"
                                                    fill="white" />
                                                <rect x="34" y="23" width="3" height="3"
                                                    fill="white" />
                                                <rect x="34" y="30" width="3" height="3"
                                                    fill="white" />
                                            </svg>
                                            <svg class="d-none" id="client-header-dropdown2" width="50"
                                                height="50" viewBox="0 0 50 50" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path d="M0 6C0 2.68629 2.68629 0 6 0H44C47.3137 0 50 2.68629 50 6V50H0V6Z"
                                                    fill="white" />
                                                <rect x="20" y="16" width="3" height="3"
                                                    fill="{{$config->color_main ?? '#26A9DF'}}" />
                                                <rect x="13" y="16" width="3" height="3"
                                                    fill="{{$config->color_main ?? '#26A9DF'}}" />
                                                <rect x="13" y="23" width="3" height="3"
                                                    fill="{{$config->color_main ?? '#26A9DF'}}" />
                                                <rect x="13" y="30" width="3" height="3"
                                                    fill="{{$config->color_main ?? '#26A9DF'}}" />
                                                <rect x="20" y="23" width="3" height="3"
                                                    fill="{{$config->color_main ?? '#26A9DF'}}" />
                                                <rect x="20" y="30" width="3" height="3"
                                                    fill="{{$config->color_main ?? '#26A9DF'}}" />
                                                <rect x="27" y="16" width="3" height="3"
                                                    fill="{{$config->color_main ?? '#26A9DF'}}" />
                                                <rect x="27" y="23" width="3" height="3"
                                                    fill="{{$config->color_main ?? '#26A9DF'}}" />
                                                <rect x="27" y="30" width="3" height="3"
                                                    fill="{{$config->color_main ?? '#26A9DF'}}" />
                                                <rect x="34" y="16" width="3" height="3"
                                                    fill="{{$config->color_main ?? '#26A9DF'}}" />
                                                <rect x="34" y="23" width="3" height="3"
                                                    fill="{{$config->color_main ?? '#26A9DF'}}" />
                                                <rect x="34" y="30" width="3" height="3"
                                                    fill="{{$config->color_main ?? '#26A9DF'}}" />
                                            </svg>
                                            <ul class="client-header-dropdown-div d-none">
                                                <li><a href="/lookup/{{ $lookup_project_id }}">{{ __('Home') }}</a>
                                                </li>
                                                <li><a href="/lookup/my_team_messages/{{ $lookup_project_id }}">
                                                        {{ __('Messages') }}</a></li>
                                                <li><a
                                                        href="/lookup/forms/{{ $lookup_project_id }}">{{ __('Submit Forms') }}</a>
                                                </li>
                                                <li><a href="/lookup/upload_files/{{ $lookup_project_id }}">
                                                        {{ __('Document Share') }}</a></li>
                                                @if($calendar_visibility)
                                                <li><a href="/lookup/calendar/{{ $lookup_project_id }}">
                                                    {{ __('Calendar') }}</a></li>
                                                @endif
                                                <li><a href="/client/logout" class="client-portal-logout">
                                                        {{ __('LogOut') }} <svg width="24" height="24"
                                                            viewBox="0 0 24 24" fill="none"
                                                            xmlns="http://www.w3.org/2000/svg">
                                                            <path
                                                                d="M3.72458 13.0909H16.3635C16.966 13.0909 17.4544 12.6025 17.4544 12C17.4544 11.3975 16.966 10.9091 16.3635 10.9091H3.72458L5.13505 9.49871C5.56109 9.07274 5.56109 8.38198 5.13505 7.95594C4.70916 7.5299 4.0184 7.5299 3.59229 7.95594L0.319782 11.2285C0.294473 11.2537 0.270618 11.2801 0.247927 11.3077C0.2424 11.3145 0.237673 11.3218 0.232291 11.3287C0.215636 11.3498 0.199273 11.3711 0.184291 11.3935C0.179927 11.4 0.176364 11.4069 0.172073 11.4135C0.157091 11.437 0.1424 11.4606 0.129164 11.4853C0.126618 11.4901 0.124509 11.4953 0.121964 11.5002C0.108218 11.5269 0.0949818 11.5539 0.0834182 11.5818C0.0819636 11.5853 0.0809454 11.5889 0.0795636 11.5924C0.0677091 11.6218 0.0567273 11.6515 0.0474182 11.6821C0.0461818 11.686 0.0455273 11.69 0.0444364 11.6939C0.0356364 11.7239 0.0275636 11.7543 0.0213818 11.7853C0.0195636 11.7946 0.0187636 11.8041 0.0170909 11.8134C0.0125818 11.8391 0.00807273 11.8649 0.00552727 11.8911C0.00181818 11.9271 0 11.9635 0 12C0 12.0365 0.00181818 12.0729 0.00552727 12.109C0.00807273 12.1356 0.0127273 12.1615 0.0171636 12.1875C0.0187636 12.1965 0.0195636 12.2058 0.0213818 12.2147C0.0276364 12.2461 0.0356364 12.2767 0.0445091 12.307C0.0456 12.3106 0.0462545 12.3143 0.0473454 12.3179C0.0567273 12.3487 0.0677091 12.3787 0.0797091 12.4084C0.0810182 12.4116 0.0819636 12.415 0.0833454 12.4182C0.0949818 12.4463 0.108291 12.4735 0.122182 12.5004C0.124582 12.5051 0.126618 12.5101 0.129091 12.5148C0.142473 12.5396 0.157236 12.5634 0.172436 12.5871C0.176509 12.5935 0.18 12.6002 0.184218 12.6065C0.199273 12.629 0.215782 12.6503 0.232364 12.6716C0.237673 12.6784 0.2424 12.6856 0.247855 12.6923C0.270618 12.7199 0.294473 12.7463 0.319709 12.7716L3.59222 16.0441C3.80524 16.2572 4.08444 16.3637 4.36364 16.3637C4.64284 16.3637 4.92204 16.2572 5.13498 16.0441C5.56102 15.6181 5.56102 14.9274 5.13498 14.5013L3.72458 13.0909Z"
                                                                fill="#EB5757" />
                                                            <path
                                                                d="M15.0764 3.07629C12.0987 3.07629 9.32819 4.55353 7.66528 7.02779C7.32928 7.52779 7.46222 8.20561 7.96222 8.54168C8.46229 8.87768 9.14004 8.74488 9.47619 8.24473C10.733 6.37455 12.8265 5.25804 15.0764 5.25804C18.7939 5.25811 21.8183 8.28248 21.8183 12C21.8183 15.7175 18.7939 18.7419 15.0764 18.7419C12.8331 18.7419 10.743 17.6304 9.48528 15.7687C9.1479 15.2695 8.46993 15.1383 7.97059 15.4755C7.47139 15.8128 7.34004 16.4909 7.67735 16.9901C9.34142 19.4532 12.1074 20.9237 15.0764 20.9237C19.9969 20.9237 24.0001 16.9206 24.0001 12C24.0001 7.07942 19.9969 3.07629 15.0764 3.07629Z"
                                                                fill="#EB5757" />
                                                        </svg>
                                                    </a></li>
                                            </ul>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </header>

                        <section class="container-fluid">
                            <div class="form-message">
                                <form id="show-form"></form>
                            </div>
                            <div class="pb-4 bg-white text-dark form-back-button">
                                <a href="/lookup/{{ $lookup_project_id }}" class="btn btn-link mr-2"> <i
                                        class="fa fa-arrow-left" aria-hidden="true"></i>
                                    {{ __('Back to Client Portal') }}</a>
                                <a href="/lookup/forms/{{ $lookup_project_id }}" class="btn btn-link"> <i
                                        class="fa fa-arrow-left" aria-hidden="true"></i>
                                    {{ __('Back to Forms') }}</a>
                            </div>
                        </section>
                        @include('client.includes.footer_copyright')
                    </div>
                @else
                    @if (isset($lookup_data['project_override_name']) && !empty($lookup_data['project_override_name']))
                        <label class="ac-label bg-title" for="ac-{{ 1 }}"
                            onclick="location.href='/lookup/{{ $lookup_data['project_id'] }}'">
                            <span>{{ $lookup_data['project_override_name'] }}</span><i></i>
                        </label>
                    @elseif (isset($lookup_data['results']['projectName']))
                        <label class="ac-label bg-title" for="ac-{{ 1 }}"
                            onclick="location.href='/lookup/{{ $lookup_data['project_id'] }}'">
                            <span>{{ $lookup_data['results']['projectName'] }}</span><i></i>
                        </label>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="https://formbuilder.online/assets/js/form-builder.min.js"></script>
    <script src="https://formbuilder.online/assets/js/form-render.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.26/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.8/js/select2.min.js" defer></script>
    <script src="{{ asset('js/select2.js') }}"></script>
    <script>
        const form_id = "{{ $lookup_data['form']->id }}";
        const project_id = "{{ $lookup_project_id }}";
        const client_id = "{{ $selected_client_native_id }}";
        const tenant_id = "{{ $tenant_id }}";
        const fRender = document.getElementById("show-form");
        var collection_item_data = {};
        var formData = [];
        var form_fields = [];
        var submit_btn = null;
        var collection_create_button = null;
        var mutliselectformname = [];

        $.ajax({
            url: `form/${form_id}`,
            type: 'GET',
            success: function(response) {
                form_fields = JSON.parse(response.data.form_fields_json);
                mutliselectformname = JSON.parse(response.mutliselectformname);
                submit_btn = {
                    "type": "button",
                    "label": "Submit",
                    "subtype": "submit",
                    "className": "btn-primary btn",
                    "access": false,
                    "style": "primary",
                    "name": "button-1660243936464-0"
                }

                // Get Collection Item Data
                let show_button = false;
                let item_push = false;
                let collection_item_index = 0;
                let item_index = 0;
                let multiselectindex = 0;
                // $.each(form_fields, function(key, value) {
                for (let item_counter = 0; item_counter < form_fields.length; item_counter++) {
                    let value = form_fields[item_counter];
                    if (value.type == 'select' && mutliselectformname.indexOf(value.name) != -1) {
                        value.multiple = "multiple";
                        value.style = "width: 100%";
                        value.className = value.className + " " + value.name + " select2 multiselecttwo-" +
                            multiselectindex;
                        multiselectindex++;
                    }
                    if (value.className == "collection-section-start") {
                        show_button = true;
                        item_push = true;
                    }
                    if (item_push) {
                        if (collection_item_data.hasOwnProperty('collection-' + collection_item_index)) {
                            collection_item_data['collection-' + collection_item_index]['data'].push(value);
                        } else {
                            collection_item_data['collection-' + collection_item_index] = {
                                'data': [value],
                                'button': '',
                                'position': 0
                            };
                        }
                    }
                    if (value.className == "collection-section-end") {
                        item_push = false;
                        if (show_button) {
                            collection_create_button = {
                                "type": "button",
                                "label": "<i class='fa fa-plus'></i> &nbsp; Add Another Item",
                                "subtype": "button",
                                "className": "btn-link add-collection-item collection-" +
                                    collection_item_index,
                                "name": "add_collection_item"
                            }
                            collection_item_data['collection-' + collection_item_index].button =
                                collection_create_button;
                            collection_item_data['collection-' + collection_item_index].position = item_index +
                                1;
                            form_fields.splice(item_index + 1, 0, collection_create_button);
                        }
                        collection_item_index++;
                        show_button = false;
                    }
                    item_index++;
                }
                // });

                form_fields.push(submit_btn);
                jQuery(function($) {
                    let formData = JSON.stringify(form_fields);
                    $(fRender).formRender({
                        formData
                    });
                    setTimeout(() => {
                        traverseThroughFilesInputs();
                        for (let i = 0; i < multiselectindex; i++) {
                            $('.multiselecttwo-' + i).select2({
                                placeholder: 'Select'
                            });
                        }
                    }, 500);
                });
            }
        });


        $("body").on("click", ".add-collection-item", async function() {
            let new_form_data = [];
            let total_items = form_fields.length;
            let processed_items = 0;
            // get item index for collection
            var collection_index = $(this)[0].classList[$(this)[0].classList.length - 1];
            var collection_length = collection_item_data[collection_index].data.length;
            var collection_postion = 0;
            // check for position
            $.each(form_fields, function(key, value) {
                if (value.hasOwnProperty('className')) {
                    var current_classes = value.className.split(' ');
                    if (current_classes[current_classes.length - 1] == collection_index) {
                        return false;
                    }
                }
                collection_postion++;
            });
            var collection_data = collection_item_data[collection_index].data;
            // push collection elements into form elements
            for (let index = 0; index < collection_length; index++) {
                form_fields.splice(collection_postion, 0, collection_data[index]);
                collection_postion++;
            }

            var serialized_form = $('#show-form').serializeArray();
            let formData = JSON.stringify(form_fields);
            $(fRender).formRender({
                formData
            });
            setTimeout(() => {
                traverseThroughFilesInputs();
            }, 500);
            // now rerender the data if any
            var pushed_elems = {};
            $.each(serialized_form, function(key, value) {
                var elem_type = $($('[name="' + value.name + '"]')[0]).attr('type');
                if (pushed_elems.hasOwnProperty(value.name)) {
                    pushed_elems[value.name] += 1;
                    // check if radio or checkbox
                    if (elem_type == 'radio' || elem_type == 'checkbox') {
                        $('[name="' + value.name + '"]:eq(' + (pushed_elems[value.name] - 1) + ')')
                            .prop('checked', (value.value == 'true' ? true : false));
                    } else {
                        $('[name="' + value.name + '"]:eq(' + (pushed_elems[value.name] - 1) + ')').val(
                            value.value);
                    }
                } else {
                    pushed_elems[value.name] = 1;
                    // check if radio or checkbox
                    if (elem_type == 'radio' || elem_type == 'checkbox') {
                        $('[name="' + value.name + '"]:eq(0)').prop('checked', (value.value == 'true' ?
                            true : false));
                    } else {
                        $('[name="' + value.name + '"]:eq(0)').val(value.value);
                    }
                }
            });
        });



        $('.nav-tabs li a').on('click', function() {
            $('.nav-tabs').find('li.active').removeClass('active');
            $(this).parent('li').addClass('active');
        });

        $(document).ready(function() {
            $(".ac-label").click(function(e) {
                e.preventDefault();
                $check = $(this).prev();
                if ($check.prop('checked'))
                    $check.prop("checked", false);
                else
                    $check.prop("checked", true);
            });

            let sw = screen.width;
            if (sw < 540) {
                $("iframe").width(sw - 70);
            }
            document.getElementById('language-select').addEventListener('click', function(e) {
                $('#language-dropdown').toggleClass('show-dropdown');
                $('.text-lang-color .fas').toggleClass('fa-angle-down fa-angle-up');
                e.stopPropagation();
            })
            $(document).click(() => {
                if ($('#language-dropdown').hasClass('show-dropdown')) {
                    $('#language-dropdown').removeClass('show-dropdown')
                }
            })
        });

        function showDropdown() {
            if ($('#language-dropdown').hasClass('show-dropdown')) {
                $('#language-dropdown').removeClass('show-dropdown');
                $('.text-lang-color .fas').toggleClass('fa-angle-down fa-angle-up');
            }
            $('.client-header-dropdown-div').toggleClass('d-none');
            document.getElementById("client-header-dropdown2").classList.toggle('d-none');
            document.getElementById("client-header-dropdown1").classList.toggle('d-none');
        }
        $('#show-form').submit(function(e) {
            e.preventDefault();
            const formData = [];
            const files = {};
            const mainData = new FormData();
            const inputBinding = $(this).serializeArray();
            let multiselect_names = [];
            $.each(inputBinding, function(i, field) {
                let field_name = field.name;
                if (field_name.indexOf("[]") == -1) {
                    let label = $(`label[for=${field.name}]`).text();
                    if (label[label.length - 1] == '*') {
                        label = label.slice(0, -1)
                    }
                    field['label'] = label;
                    formData.push(field);
                } else {
                    field_name = field_name.replace("[]", "");
                    if (multiselect_names.indexOf(field_name) == -1) {
                        multiselect_names.push(field_name);
                        field['name'] = field_name;
                        field['label'] = field_name;
                        field['value'] = $("." + field_name).val();
                        formData.push(field);
                    }
                }
            });
            mainData.append('content', JSON.stringify({
                tenant_id,
                project_id,
                form_id,
                client_id,
                response: formData
            }));

            if (Object.keys(uploaders).length > 0) {
                $.each(Object.keys(uploaders), function(j, file_id) {
                    let file_field_name = $('#' + file_id).attr('name');
                    let file_field_index = (!files[file_field_name] ? 0 : files[file_field_name].length);
                    if (uploaders[file_id].getFiles().length > 0) {
                        if (!files[file_field_name]) {
                            files[file_field_name] = [];
                        }
                        if (!files[file_field_name][file_field_index]) {
                            files[file_field_name][file_field_index] = [];
                        }
                        $.each(uploaders[file_id].getFiles(), function(k, file) {
                            mainData.append('documents[' + file_field_name + '][' +
                                file_field_index + '][]', file.data);
                            files[file_field_name][file_field_index].push(file.data);
                        });
                    }
                });
            }

            $.ajax({
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                processData: false,
                contentType: false,
                cache: false,
                url: "/handle_form_response",
                // data: JSON.stringify({
                //     tenant_id,
                //     project_id,
                //     form_id,
                //     client_id,
                //     response: formData
                // }),
                data: mainData,
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            text: "Your response has been submitted successfully!",
                            icon: "success",
                            iconColor: '#42ba96',
                            confirmButtonColor: "#3b6dfd"
                        }).then((result) => {
                            $('.form-message').html(response.message);
                            // window.location.replace(
                            //     "{{ route('client_active_forms', ['lookup_project_id' => $lookup_data['active_project_id'], 'subdomain' => session()->get('subdomain')]) }}"
                            // );
                        });
                    } else {
                        Swal.fire({
                            text: "Something went wrong. Try again later!",
                            icon: "success",
                            iconColor: '#f2c010',
                            confirmButtonColor: "#3b6dfd"
                        })
                    }
                },
                error: function() {
                    Swal.fire({
                        text: "Something went wrong. Try again later!",
                        icon: "success",
                        iconColor: '#f2c010',
                        confirmButtonColor: "#3b6dfd"
                    })
                }
            });
        });
    </script>
    <style>
        .casestatusspan {
            padding: 20px;
        }

        .contnt_right .casestatus .casestatusspan p {
            margin: revert !important;
            line-height: revert !important;
            font-weight: revert !important;
            padding: revert !important;
        }

        .modelLoadingImage {
            width: 100%;
            height: 100%;
            float: left;
            position: absolute;
            text-align: center;
            z-index: 1;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modelLoadingImage img {
            top: 45%;
            position: relative;
        }

        .reply-msg {
            font-size: 16px;
            padding: 1px 10px;
            width: fit-content;
            border-radius: 3px;
        }

        .success-msg {
            color: #270;
            background-color: #DFF2BF;
        }

        .error-msg {
            color: #D8000C;
            background-color: #FFBABA;
        }

        .msg-popup-close {
            color: #fafafa00;
            background: red !important;
            padding: 1px 6px 6px !important;
            border-radius: 50%;
            font-size: 16px;
            outline: none;
        }

        /* custom form styles */
        #show-form {
            width: 100%;
            background: #fff;
            border-radius: 20px;
        }

        .rendered-form {
            width: 75%;
            margin: 0 auto;
            padding: 20px 10px
        }

        /* .form-group{
                                                                                            width: 48.5%;
                                                                                            margin: 10px 10px 10px 0px;
                                                                                        }
                                                                                        .form-group.formbuilder-number, .form-group.formbuilder-date, .form-group.formbuilder-select, .formbuilder-file.form-group{
                                                                                            width: 32%;
                                                                                        }
                                                                                        .form-group.formbuilder-button, .form-group.formbuilder-textarea{
                                                                                            width: 100%;
                                                                                        } */
        .form-group.formbuilder-button .btn {
            min-width: 30%;
            line-height: 30px;
            font-size: 18px;
            letter-spacing: 0.7px;
            border-radius: 5px;
            background: #55a9e2
        }

        .form-group .form-control {
            font-size: 16px;
            border: none;
            border-radius: 7px;
            background: #ecf2f6;
            height: 45px !important;
        }

        .form-group label {
            font-size: 14px;
        }

        .rendered-form textarea.form-control {
            min-height: 120px !important;
        }

        .formbuilder-file.form-group .form-control {
            background: transparent;
        }

        .container-fluid {
            padding-left: 0px;
            padding-right: 0px;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.26/dist/sweetalert2.all.min.js"></script>
    <script src="{{ asset('uppy/uppy.bundle.js') }}"></script>
    <script>
        let uploaders = {};
        $(document).ready(function() {
            $(document).on('click', '.uppy-remove-thumbnail', function() {
                let target = $(this).attr('data-target');
                let file_id = $(this).attr('data-id');
                let instance = uploaders[target];
                instance.removeFile(file_id);
                $('div[data-id="' + file_id + '"]').remove();
            });
        });

        function traverseThroughFilesInputs() {
            let temp_uploaders = Object.assign({}, uploaders);
            uploaders = {};
            $('.documents-input').each(function(key, value) {
                let elem = $(value);
                let id = elem.attr('id');
                $(this).removeAttr('required');
                if (!Object.prototype.hasOwnProperty.call(uploaders, id)) {
                    elem.after('<div class="uppy col-md-12 p-0" id="' + id +
                        '-uploader"><div class="uppy-drag"></div><div class="uppy-status mt-2 mb-3"></div><div class="uppy-thumbnails d-flex row col-md-6 mx-auto"></div></div>'
                    );
                    uploaders[id] = initDocumentUploaders('#' + id + '-uploader');
                    $('#' + id).hide();
                    if (Object.prototype.hasOwnProperty.call(temp_uploaders, id)) {
                        if (temp_uploaders[id].getFiles().length > 0) {
                            $.each(temp_uploaders[id].getFiles(), function(k, file) {
                                uploaders[id].addFile(file);
                            });
                        }
                    }
                } else if (Object.prototype.hasOwnProperty.call(uploaders, id)) {
                    let updated_id = id + '-' + key;
                    elem.attr('id', updated_id);
                    elem.after('<div class="uppy col-md-12 p-0" id="' + updated_id +
                        '-uploader"><div class="uppy-drag"></div><div class="uppy-status mt-2 mb-3"></div><div class="uppy-thumbnails d-flex row col-md-6 mx-auto"></div></div>'
                    );
                    uploaders[updated_id] = initDocumentUploaders('#' + updated_id + '-uploader');
                    $('#' + updated_id).hide();
                    if (Object.prototype.hasOwnProperty.call(temp_uploaders, updated_id)) {
                        if (temp_uploaders[updated_id].getFiles().length > 0) {
                            $.each(temp_uploaders[updated_id].getFiles(), function(k, file) {
                                uploaders[updated_id].addFile(file);
                            });
                        }
                    }
                }
            });
        }

        function initDocumentUploaders(id) {
            var trimmed_id = id.replace('#', '').replace('-uploader', '');
            var uppyDrag = Uppy.Core({
                autoProceed: false,
                handle: false,
                restrictions: {
                    maxFileSize: 20000000, // 20mb
                    maxNumberOfFiles: 5,
                    minNumberOfFiles: 1,
                    allowedFileTypes: ['image/*', 'video/*', 'application/*']
                }
            });

            uppyDrag.use(Uppy.DragDrop, {
                target: id + ' .uppy-drag',
                locale: {
                    strings: {
                        dropHereOr: 'Drop files here or %{browse}',
                        browse: 'choose files',
                    },
                }
            });

            uppyDrag.use(Uppy.StatusBar, {
                target: id + ' .uppy-status',
                hideAfterFinish: true,
                showProgressDetails: true,
                hideUploadButton: true,
                hideRetryButton: true,
                hidePauseResumeButton: true,
                hideCancelButton: true,
                doneButtonHandler: null,
                locale: {},
            });

            uppyDrag.on('file-added', (file) => {
                var allowed_extensions = ['png', 'jpg', 'jpeg', 'gif', 'svg', 'mp4', 'flv', 'webp', 'pdf', 'doc',
                    'docx', 'xls', 'xlsx'
                ];
                if ($.inArray(file.extension, allowed_extensions) == -1) {
                    uppyDrag.removeFile(file.id);
                    return;
                }
                var imagePreview = "";
                var thumbnail_inner = "";
                if ((/image/).test(file.type)) {
                    thumbnail_inner = '<img src="" style="width:60px;" />';
                    // thumbnail_inner = '<i class="fa fa-image" style="font-size: 20px;"></i>';
                } else if ((/video/).test(file.type)) {
                    thumbnail_inner = '<i class="fa fa-video" style="font-size: 20px;"></i>';
                } else if ((/application/).test(file.type)) {
                    thumbnail_inner = '<i class="fa fa-file" style="font-size: 20px;"></i>';
                }
                var thumbnail = '<div class="uppy-thumbnail col-sm-2">' + thumbnail_inner + '</div>';

                var sizeLabel = "bytes";
                var filesize = file.size;
                if (filesize > 1024) {
                    filesize = filesize / 1024;
                    sizeLabel = "kb";
                    if (filesize > 1024) {
                        filesize = filesize / 1024;
                        sizeLabel = "MB";
                    }
                }
                imagePreview +=
                    '<div class="uppy-thumbnail-container p-3 row col-md-12 alert alert-success" data-id="' + file
                    .id + '">' + thumbnail + ' <span class="uppy-thumbnail-label col-sm-8">' + file.name + ' (' +
                    Math.round(filesize, 2) + ' ' + sizeLabel + ')</span><span data-target="' + trimmed_id +
                    '" data-id="' + file.id +
                    '" class="uppy-remove-thumbnail col-sm-2 text-right"><i class="fas fa-times-circle"></i></span></div>';

                // append to view
                $(id + ' .uppy-thumbnails').append(imagePreview);

                // show preview
                $($($('.uppy-thumbnail-container[data-id="' + file.id + '"').find('.uppy-thumbnail')[0]).find(
                    'img')[0]).attr('src', URL.createObjectURL(file.data));
            });
            return uppyDrag;
        }
    </script>
@stop
