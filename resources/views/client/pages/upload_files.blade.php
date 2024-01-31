@extends('client.layouts.default')

@section('title', 'VineConnect Client Portal - Client Dashboard')

@section('content')
    <link href="{{ asset('uppy/uppy.bundle.css') }}" rel="stylesheet" type="text/css" />
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
    </style>

    <input type="hidden"
        value="{{ route('upload_project_files', ['lookup_project_id' => $lookup_project_id, 'subdomain' => session()->get('subdomain'), 'lang' => 'en']) }}"
        id="uppy_endpoint">
    <div class="client-portal-body">
        <div class="accordion-started accordion-bral row">
            @foreach (Session::get('project_list_info') as $key => $project_details)
                <div class="w-100">
                    @if ($lookup_data[0]['project_id'] == $project_details['project_id'])
                        <input class="ac-input" id="ac-{{ $key }}" name="accordion-1" type="radio" checked>
                        @if (isset($project_details['project_override_name']) && !empty($project_details['project_override_name']))
                            <label class="ac-label bg-title"
                                for="ac-{{ $key }}"><span>{{ $project_details['project_override_name'] }}</span><i></i></label>
                        @elseif (isset($project_details['results']['project']['projectName']))
                            <label class="ac-label bg-title"
                                for="ac-{{ $key }}"><span>{{ $project_details['results']['project']['projectName'] }}</span><i></i></label>
                        @else
                            <label class="ac-label bg-title" for="ac-{{ $key }}"><span></span><i></i></label>
                        @endif
                        <div class="article ac-content">
                            <header class="bg-logo">
                                <div class="container-fluid">
                                    <div class="row align-items-center justify-content-between">
                                        <div class="col-md-4 col-sm-0">
                                            @if (isset($lookup_data[0]['last_login']))
                                                <p>{{ __('Last Login:') }} {{ $lookup_data[0]['last_login'] }}</time></p>
                                            @else
                                                <p>{{ __('Last Login:') }} </time></p>
                                            @endif
                                            <span class="icon_menu" onclick="openNav();openNav3()"><img
                                                    src="{{ asset('img/client//menu.png') }}" alt=""></span>
                                        </div>
                                        <div class="col-md-4 col-sm-5">
                                            @if (isset($lookup_data[0]['config_details']->logo))
                                                <a href="/lookup/{{ $lookup_project_id }}"
                                                    class="d-flex align-items-center">
                                                    <img src="{{ asset('uploads/client_logo/' . $lookup_data[0]['config_details']->logo) }}"
                                                        alt="{{ __('Logo') }}" class="login-logo">
                                                </a>
                                            @else
                                                <a href="/lookup/{{ $lookup_project_id }}"
                                                    class="d-flex align-items-center">
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
                                                            href="{{ route('upload_files', ['lookup_project_id' => $lookup_data[0]['active_project_id'], 'subdomain' => session()->get('subdomain'), 'lang' => 'en']) }}">English</a>
                                                    </li>
                                                    <li><a
                                                            href="{{ route('upload_files', ['lookup_project_id' => $lookup_data[0]['active_project_id'], 'subdomain' => session()->get('subdomain'), 'lang' => 'es']) }}">Español</a>
                                                    </li>
                                                    <li><a
                                                            href="{{ route('upload_files', ['lookup_project_id' => $lookup_data[0]['active_project_id'], 'subdomain' => session()->get('subdomain'), 'lang' => 'fr']) }}">Français</a>
                                                    </li>
                                                    <li><a
                                                            href="{{ route('upload_files', ['lookup_project_id' => $lookup_data[0]['active_project_id'], 'subdomain' => session()->get('subdomain'), 'lang' => 'pt']) }}">Português</a>
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
                                                    <path
                                                        d="M0 6C0 2.68629 2.68629 0 6 0H44C47.3137 0 50 2.68629 50 6V50H0V6Z"
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
                                                    <li><a
                                                            href="/lookup/{{ $lookup_project_id }}">{{ __('Home') }}</a>
                                                    </li>
                                                    <li><a href="/lookup/my_team_messages/{{ $lookup_project_id }}">
                                                            {{ __('Messages') }}</a></li>
                                                    <li><a
                                                            href="/lookup/forms/{{ $lookup_project_id }}">{{ __('Submit Forms') }}</a>
                                                    </li>
                                                    <li><a href="/lookup/upload_files/{{ $lookup_project_id }}">
                                                            {{ __('Document Share') }}</a></li>
                                                    @if ($calendar_visibility)
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

                            <section>
                                @if ($files_allowed)
                                    <div class="upload-files-container">
                                        <h1 class="col-md-6">{{ __('Upload Documents Related to Your Case') }} <i
                                                class="fa fa-info-circle text-dark" data-toggle="tooltip"
                                                data-theme="dark" data-html="true"
                                                title="Please upload max 20MB per file otherwise your file will not be uploaded"></i>
                                        </h1>
                                        <p class="col-md-6">
                                            {{ __('Sharing documents related to your case is important for us to do our job. Please upload all documents you receive here. You can upload new documents as you receive them. Accepted file formats include PDF, JPEG, and MOV files. You can upload photos, videos, or scans of documents, letters, and mail you receive.') }}
                                        </p>
                                        @if (count($upload_schemes))
                                            <div class="col-md-6 upload-scheme">
                                                <div class="form-group">
                                                    <select id="upload_schema" class="form-control">
                                                        <option value="">
                                                            {{ __('Please choose the type of document you are uploading') }}
                                                        </option>
                                                        @foreach ($upload_schemes as $scheme)
                                                            <option
                                                                value="{{ $scheme->id }}*{{ $scheme->target_field_type }}">
                                                                {{ $scheme->choice }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mt-3 alert alert-warning alert-dismissible fade show upload-scheme-message-div d-none"
                                                    role="alert">
                                                    <strong id="upload-scheme-message"></strong>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="uppy col-md-6" id="file_uploader">
                                            <div class="uppy-drag"></div>
                                            <div class="uppy-status mt-2 mb-3"></div>
                                            <!-- <div class="uppy-progress mt-2 mb-3"></div> -->
                                            <div class="uppy-thumbnails d-flex row col-md-8 mx-auto"></div>
                                        </div>

                                        <div class="col-md-12">
                                            <ul class="nav nav-pills" id="myTab1" role="tablist">
                                                <li class="nav-item" style="margin-right: 20px">
                                                    <a class="nav-link btn btn-outline-primary active"
                                                        id="download-document-tab" data-toggle="tab"
                                                        href="#download_document">
                                                        <span class="nav-text">Documents Shared with Me</span>
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link btn btn-outline-primary" id="uploaded-document-tab"
                                                        data-toggle="tab" href="#uploaded_document"
                                                        aria-controls="profile">
                                                        <span class="nav-text">My Documents Uploaded</span>
                                                    </a>
                                                </li>
                                            </ul>
                                            <div class="tab-content mt-6" id="myTabContent1">
                                                <div class="tab-pane fade show active" id="download_document"
                                                    role="tabpanel" aria-labelledby="download-document-tab">
                                                    <table class="table mt-4">
                                                        <thead>
                                                            <tr>
                                                                <th scope="col">File Name</th>
                                                                <th scope="col">Size (KB)</th>
                                                                <th scope="col">Shared On</th>
                                                                <th scope="col">Shared By</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($fv_shared_documents as $document)
                                                                <tr>
                                                                    <td>{{ $document->fv_filename }}
                                                                        <a href="{{ route('download_fv_document', ['subdomain' => session()->get('subdomain')]) }}?{{ 'id=' . $document->id . '&type=shared' }}"
                                                                            type="button"
                                                                            class="btn btn-sm btn-outline-dark document-download ml-2">Download</a>
                                                                    </td>
                                                                    <td>{{ round($document->doc_size / 1024) }}</td>
                                                                    <td>{{ date('m-d-Y H:i:s', strtotime($document->fv_upload_date)) }}
                                                                    </td>
                                                                    <td>{{ $document->fv_uploader_name }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div class="tab-pane fade" id="uploaded_document" role="tabpanel"
                                                    aria-labelledby="uploaded-document-tab">
                                                    <table class="table mt-4">
                                                        <thead>
                                                            <tr>
                                                                <th scope="col">File Name</th>
                                                                <th scope="col">Size (KB)</th>
                                                                <th scope="col">Uploaded On</th>
                                                                <th scope="col">Type of Document</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($fv_client_upload_documents as $document)
                                                                <tr>
                                                                    <td>{{ $document->fv_filename }}
                                                                        <a href="{{ route('download_fv_document', ['subdomain' => session()->get('subdomain')]) }}?{{ 'id=' . $document->id . '&type=upload' }}"
                                                                            type="button"
                                                                            class="btn btn-sm btn-outline-dark document-download ml-2">Download</a>
                                                                    </td>
                                                                    <td>{{ round($document->doc_size / 1024) }}</td>
                                                                    <td>{{ date('m-d-Y H:i:s', strtotime($document->fv_upload_date)) }}
                                                                    </td>
                                                                    <td>{{ $document->choice }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                @else
                                    <div class="upload-files-container">
                                        <h1 class="col-md-6 text-danger" style="margin-bottom:0px !important;">Document
                                            Uploads are not allowed!</h1>
                                    </div>
                                @endif
                            </section>
                            @include('client.includes.footer_copyright')
                        </div>
                    @else
                        @if (isset($project_details['project_override_name']) && !empty($project_details['project_override_name']))
                            <label class="ac-label bg-title"
                                onclick="location.href='/lookup/upload_files/{{ $project_details['project_id'] }}'"
                                for="ac-{{ $key }}"><span>{{ $project_details['project_override_name'] }}</span><i></i></label>
                        @elseif (isset($project_details['results']['project']['projectName']))
                            <label class="ac-label bg-title"
                                onclick="location.href='/lookup/upload_files/{{ $project_details['project_id'] }}'"
                                for="ac-{{ $key }}"><span>{{ $project_details['results']['project']['projectName'] }}</span><i></i></label>
                        @endif
                    @endif
            @endforeach
        </div>
    </div>
    </div>



    <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>
    <script src="{{ asset('js/tooltip.js') }}"></script>

    <script>
        var selected_client_native_id = {{ $selected_client_native_id ? $selected_client_native_id : null }}

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



        setDragDropLabel();
        var uppy_drag_label;

        function setDragDropLabel() {
            $.ajax({
                url: '/get_text_from_language',
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        //$('.uppy-DragDrop-browse').html($('.uppy-DragDrop-browse').html().replace(
                        //  'choose files', response.choose_files));
                        //$('.uppy-DragDrop-label').html($('.uppy-DragDrop-label').html().replace(
                        //'Drop files here or', response.Drop_files_here_or));
                        uppy_drag_label = response.Drop_files_here_or + ' <span class="uppy-DragDrop-browse">' +
                            response.choose_files + '</span>';
                        $('.uppy-DragDrop-label').html(uppy_drag_label);
                    }
                }
            });
        }
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
        table {
            font-size: 15px;
        }
    </style>

    @if ($files_allowed)
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.26/dist/sweetalert2.all.min.js"></script>
        <script src="{{ asset('uppy/uppy.bundle.js') }}"></script>
        <script src="{{ asset('uppy/uppy.js?20230806') }}"></script>
    @endif
@stop
