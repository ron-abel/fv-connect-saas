@extends('admin.layouts.default')

@section('title', 'Vineconnect - Admin Dashboard')

@section('content')
    <style>
        /* for sm */
        .custom-switch.custom-switch-sm .custom-control-label {
            padding-left: 1rem;
            padding-bottom: 1rem;
        }

        .custom-switch.custom-switch-sm .custom-control-label::before {
            height: 1rem;
            width: calc(1rem + 0.75rem);
            border-radius: 2rem;
        }

        .custom-switch.custom-switch-sm .custom-control-label::after {
            width: calc(1rem - 4px);
            height: calc(1rem - 4px);
            border-radius: calc(1rem - (1rem / 2));
        }

        .custom-switch.custom-switch-sm .custom-control-input:checked~.custom-control-label::after {
            transform: translateX(calc(1rem - 0.25rem));
        }

        /* for md */

        .custom-switch.custom-switch-md .custom-control-label {
            padding-left: 2rem;
            padding-bottom: 1.5rem;
        }

        .custom-switch.custom-switch-md .custom-control-label::before {
            height: 1.5rem;
            width: calc(2rem + 0.75rem);
            border-radius: 3rem;
        }

        .custom-switch.custom-switch-md .custom-control-label::after {
            width: calc(1.5rem - 4px);
            height: calc(1.5rem - 4px);
            border-radius: calc(2rem - (1.5rem / 2));
        }

        .custom-switch.custom-switch-md .custom-control-input:checked~.custom-control-label::after {
            transform: translateX(calc(1.5rem - 0.25rem));
        }

        /* for lg */

        .custom-switch.custom-switch-lg .custom-control-label {
            padding-left: 3rem;
            padding-bottom: 2rem;
        }

        .custom-switch.custom-switch-lg .custom-control-label::before {
            height: 2rem;
            width: calc(3rem + 0.75rem);
            border-radius: 4rem;
        }

        .custom-switch.custom-switch-lg .custom-control-label::after {
            width: calc(2rem - 4px);
            height: calc(2rem - 4px);
            border-radius: calc(3rem - (2rem / 2));
        }

        .custom-switch.custom-switch-lg .custom-control-input:checked~.custom-control-label::after {
            transform: translateX(calc(2rem - 0.25rem));
        }

        .d-none {
            display: none;
        }
    </style>
    <!--begin::Subheader-->
    <div class="subheader py-2 py-lg-4 subheader-solid" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
            <!--begin::Info-->
            <div class="d-flex align-items-center flex-wrap mr-2">
                <!--begin::Page Title-->
                <h5 class="text-dark font-weight-bold mt-2 mb-2 mr-5">Automated Communications - Review Requests</h5>
                <!--end::Page Title-->

            </div>
            <!--end::Info-->
        </div>
    </div>
    <!--end::Subheader-->
    <div class="d-flex flex-column-fluid">
        <!--begin::Container-->
        <div class="container">
            @if (session()->has('success'))
                <div class="alert alert-success" role="alert">
                    {!! session()->get('success') !!}
                </div>
            @endif
            @if (session()->has('error'))
                <div class="alert alert-danger" role="alert">
                    {!! session()->get('error') !!}
                </div>
            @endif
            <!--begin::Row-->
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-custom gutter-b example example-compact">
                        <div class="card-body">

                            <ul class="nav nav-pills" id="v-pills-tab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link btn btn-outline-primary active" id="review-request-tab"
                                        data-toggle="tab" href="#review-request" role="tab"
                                        aria-controls="review-request" aria-selected="true"><span class="nav-text">Setup
                                            Review Request</span></a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link btn btn-outline-primary" id="review-links-tab" data-toggle="tab"
                                        href="#review-links" role="tab" aria-controls="review-links"
                                        aria-selected="false"><span class="nav-text">Configure Review Links</span></a>
                                </li>
                                {{-- <li class="nav-item"> --}}
                                {{-- <a class="nav-link btn btn-outline-primary" id="monitor-responses-tab" data-toggle="tab" href="#monitor-responses" --}}
                                {{-- role="tab" aria-controls="monitor-responses" aria-selected="false"><span class="nav-text">Monitor Responses</span></a> --}}
                                {{-- </li> --}}
                            </ul>

                            <div class="tab-content mt-6">
                                <div id="review-request" role="tabpanel" aria-labelledby="review-request-tab"
                                    class="tab-pane fade show active">
                                    <div class="mt-5">
                                        <p>
                                            <b>Instructions:</b> When you’ve selected a phase to initiate a Review Request
                                            SMS, VineConnect can first send a qualifying text message to the client to
                                            solicit a feedback score. This allows you to ensure only satisfied clients will
                                            receive the review request link by setting a minimum score threshold to release
                                            the link. Toggle the Set a Qualified Response Threshold to enable this feature.
                                        </p>
                                        <p><b>Note:</b> If you do not set a threshold, the Review Request Text will be
                                            released to all eligible review request clients automatically.</p>
                                        <div class="callout_subtle lightgrey"><i class="fas fa-link"
                                                style="color:#383838;padding-right:5px;"></i> Support Article: <a
                                                href="https://intercom.help/vinetegrate/en/articles/5839053-review-requests"
                                                target="_blank" />Review Request</a></div>
                                        <div class="callout_subtle lightgrey"><i class="fa fa-key mr-3"></i><a
                                                href="{{ url('admin/variables') }}" target="_blank" />&nbsp;List of
                                            Variables</a></div>
                                        <form method="post" name="setup_review_request_form"
                                            action="{{ route('save_auto_note_google_review', ['subdomain' => session()->get('subdomain')]) }}">
                                            {{ csrf_field() }}
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="form-group">
                                                        <div class="custom-control custom-switch custom-switch-md pl-2">
                                                            <input
                                                                {{ isset($auto_note_google_review->is_set_qualified_response_threshold) && $auto_note_google_review->is_set_qualified_response_threshold == 1 ? 'checked' : '' }}
                                                                type="checkbox" name="is_set_qualified_response_threshold"
                                                                value="enable" class="custom-control-input"
                                                                id="send_qualified_response_request">
                                                            <label class="custom-control-label ml-7 pl-4"
                                                                for="send_qualified_response_request"><b>Set a Qualified
                                                                    Response Threshold</b></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="{{ isset($auto_note_google_review->is_set_qualified_response_threshold) && $auto_note_google_review->is_set_qualified_response_threshold == 1 ? '' : 'd-none' }}"
                                                id="qualifying_response_request_content">
                                                <div class="form-group align-items-center d-flex form-group">
                                                    <label for="minimumScore"><b>Sees Only Send a Review Request Message
                                                            when Response Score is equal to or greater than
                                                            (0-5):</b></label>
                                                    <input type="number" class="form-control w-60px ml-3" id="minimumScore"
                                                        name="minimum_score" min="0" max="5" pattern=".{0,5}"
                                                        value="{{ isset($auto_note_google_review->minimum_score) ? $auto_note_google_review->minimum_score : '' }}"
                                                        {{ isset($auto_note_google_review->is_set_qualified_response_threshold) && $auto_note_google_review->is_set_qualified_response_threshold == 1 ? 'required' : '' }} />
                                                </div>
                                                <div class="row mt-10">
                                                    <div class="col-6">
                                                        <div class="form-group">
                                                            <label><b>Send a Qualifying Response Request</b></label>
                                                            <textarea name="qualified_review_request_msg_body" rows="5" class="form-control mt-12">
@if (isset($auto_note_google_review->qualified_review_request_msg_body))
{{ $auto_note_google_review->qualified_review_request_msg_body }}
@else
Hello [client_firstname]! How would you rate the service [law_firm_name] has provided? Please reply with only a number (0-5).
@endif
</textarea>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="form-group">
                                                            <label>
                                                                <div
                                                                    class="custom-control custom-switch custom-switch-md pl-0">
                                                                    <input
                                                                        {{ isset($auto_note_google_review->is_send_unqualified_response_request) && $auto_note_google_review->is_send_unqualified_response_request == 1 ? 'checked' : '' }}
                                                                        type="checkbox"
                                                                        name="is_send_unqualified_response_request"
                                                                        value="enable" class="custom-control-input"
                                                                        id="send_unqualified_response_request">
                                                                    <label class="custom-control-label ml-7 pl-4"
                                                                        for="send_unqualified_response_request"><b>Send
                                                                            Unqualified Response Request</b></label>
                                                                </div>
                                                            </label>
                                                            <div class="{{ isset($auto_note_google_review->is_send_unqualified_response_request) && $auto_note_google_review->is_send_unqualified_response_request == 1 ? '' : 'd-none' }}"
                                                                id="unqualified_response_request_content">
                                                                <textarea class="form-control" rows="5" name="unqualified_review_request_msg_body">{{ isset($auto_note_google_review->unqualified_review_request_msg_body) ? $auto_note_google_review->unqualified_review_request_msg_body : 'Thank you for the feedback. We will notify the appropriate team.' }}</textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-6 mt-10">
                                                    <div class="form-group">
                                                        <label><b>Review Request Text Body</b></label>
                                                        <textarea required class="form-control" rows="5" name="review_request_text_body">
@if (isset($auto_note_google_review->review_request_text_body))
{{ $auto_note_google_review->review_request_text_body }}
@else
Would you take a moment to leave us a review? [review_link]
@endif
</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-6 mt-10">
                                                    <div class="form-group">
                                                        <button type="submit" class="btn btn-success mr-2">
                                                            SAVE
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div id="review-links" role="tabpanel" aria-labelledby="review-links-tab"
                                    class="tab-pane fade">
                                    <div>
                                        <h5 class="card-title mt-7">Send Google/Facebook/Yelp Reveiw Requests by Client
                                            Location</h5>
                                        <p><b>Instructions:</b> You can configure as many review links as you would like
                                            with Google, Facebook, Yelp, and Avvo links. Map different review request links
                                            which associate with the client's Zip Code in their contact card. This unique
                                            mapping by zip code allows you to send review links by specific location. Select
                                            only one (1) of these links below as your Default so if a client doesn’t match
                                            your mapped zip codes, they receive that default link.</p>
                                        <p>Our Template Download Tool allows you to easily configure many links mapped to
                                            zip codes in one spreadsheet, which can be uploaded in the system all at once.
                                            Download the template below and pull a report of clients from Filevine that
                                            includes zip codes. From here you can organize your zip codes and map them to a
                                            link with a description if desired.</p>
                                        <div class="callout_subtle lightgrey"><i class="fas fa-link"
                                                style="color:#383838;padding-right:5px;"></i>
                                            Support Article: <a
                                                href="https://intercom.help/vinetegrate/en/articles/5839053-automated-review-requests"
                                                target="_blank" />Automated Review Requests</a></div>
                                        <div class="clear"></div>
                                    </div>
                                    <div class="overlay loading"></div>
                                    <div class="spinner-border text-primary loading" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            @if ($auto_note_details)
                                                <div class="form-group txt-btn">
                                                    <input type="radio" id="on_google_review_btn"
                                                        onchange="activate_communication(this.value, 'google_review_is_on')"
                                                        {{ $auto_note_details->google_review_is_on == 1 ? 'checked' : '' }}
                                                        name="automate_google_review_status" value="on" />
                                                    <label for="on_google_review_btn" type="button"
                                                        class="btn">ON</label>
                                                    <input type="radio" id="off_google_review_btn"
                                                        onchange="activate_communication(this.value, 'google_review_is_on')"
                                                        {{ $auto_note_details->google_review_is_on == 0 ? 'checked' : '' }}
                                                        name="automate_google_review_status" value="off" />
                                                    <label for="off_google_review_btn" type="button"
                                                        class="btn btn-danger">OFF</label>
                                                </div>
                                                <div class="form-group txt-btn">
                                                    <input type="radio" id="go_live_google_review_btn"
                                                        onchange="activate_communication(this.value, 'google_review_is_live')"
                                                        {{ $auto_note_details->google_review_is_live == 1 ? 'checked' : '' }}
                                                        name="automate_google_review_live" value="go_live" />
                                                    <label for="go_live_google_review_btn" type="button"
                                                        class="btn btn-success">GO
                                                        LIVE</label><input type="radio" id="pause_google_review_btn"
                                                        onchange="activate_communication(this.value, 'google_review_is_live')"
                                                        {{ $auto_note_details->google_review_is_live == 0 ? 'checked' : '' }}
                                                        name="automate_google_review_live" value="pause" />
                                                    <label for="pause_google_review_btn" type="button"
                                                        class="btn btn-danger">TEST</label>
                                                </div>
                                            @else
                                                <div class="form-group txt-btn">
                                                    <input type="radio" id="on_google_review_btn"
                                                        onchange="activate_communication(this.value, 'google_review_is_on')"
                                                        name="automate_google_review_status" value="on" />
                                                    <label for="on_google_review_btn" type="button"
                                                        class="btn">ON</label>
                                                    <input type="radio" id="off_google_review_btn"
                                                        onchange="activate_communication(this.value, 'google_review_is_on')"
                                                        checked name="automate_google_review_status" value="off" />
                                                    <label for="off_google_review_btn" type="button"
                                                        class="btn btn-danger">OFF</label>
                                                </div>
                                                <div class="form-group txt-btn go_live">
                                                    <input type="radio" id="go_live_google_review_btn"
                                                        onchange="activate_communication(this.value, 'google_review_is_live')"
                                                        name="automate_google_review_live" value="go_live" />
                                                    <label for="go_live_google_review_btn" type="button"
                                                        class="btn">GO LIVE</label>
                                                    <input type="radio" id="pause_google_review_btn"
                                                        onchange="activate_communication(this.value, 'google_review_is_live')"
                                                        checked name="automate_google_review_live" value="pause" />
                                                    <label for="pause_google_review_btn" type="button"
                                                        class="btn">TEST</label>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="col-md-5">
                                            <!-- File upload form -->
                                            <div class="form-group mr-5">
                                                <div class="callout_subtle lightgrey"><i class="fas fa-link"
                                                        style="color:#383838;padding-right:5px;"></i>
                                                    Download Template: <a
                                                        href="{{ url('assets/sample_templates/map_sample.csv') }}"
                                                        target="_blank">Map Client Zip Codes</a></div>
                                                <div class="clear"></div>
                                                <form method="post"
                                                    action="{{ route('google_review_automated_communications_upload', ['subdomain' => $subdomain]) }}"
                                                    class="form form-inline" enctype="multipart/form-data">
                                                    @csrf
                                                    <div class="input-group">
                                                        <div class="custom-file">
                                                            <input type="file" class="custom-file-input"
                                                                name="google_review_file"
                                                                id="google_review_zip_codes_file" required />
                                                            <label class="custom-file-label"
                                                                for="google_review_zip_codes_file"><span
                                                                    style="margin-right: 73px;">Select Mapping
                                                                    File<span></label>
                                                        </div>
                                                        <div class="input-group-append">
                                                            <button type="submit"
                                                                class="input-group-text btn-success">Import
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <span class="text-danger">{!! $errors->first('google_review_file_response') !!}</span>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <form method="post" id="google_review_submit">
                                        <div id="main-section">
                                            @foreach ($google_reviews_links as $single_google_review_link)
                                                <div class="row ml-3 mr-0 mt-3 dv-webhook-row js-review"
                                                    id="id_google_review_{{ $single_google_review_link->id }}">
                                                    <div class="col-sm-12 px-1">
                                                        <div class="form-group mt-10">
                                                            <div class="row mx-0 phase_form">
                                                                <div class="col-sm-4">
                                                                    <label for="">Review Request Link to
                                                                        Send</label>
                                                                    <input name="review_link"
                                                                        value="{{ $single_google_review_link->review_link }}"
                                                                        class="form-control" required>
                                                                </div>
                                                                <div class="col-sm-3 custom-select-input">
                                                                    <label for="">Description</label>
                                                                    <input name="handle_type"
                                                                        value="{{ $single_google_review_link->description }}"
                                                                        class="form-control phase_form_select" required>
                                                                </div>
                                                                <div
                                                                    class="col-sm-2  {{ count($google_reviews_links) === 1 && count($google_reviews_cities[$single_google_review_link->id]) == 1 ? 'd-none' : '' }} js-client-zip-code">
                                                                    <label for="">Client Zip Code</label>
                                                                    <input name="zip_code"
                                                                        value="{{ isset($google_reviews_cities[$single_google_review_link->id][0]) ? $google_reviews_cities[$single_google_review_link->id][0]->zip_code : '' }}"
                                                                        class="form-control zip_code_class" required>
                                                                </div>
                                                                <div
                                                                    class="col-sm-1 {{ count($google_reviews_links) === 1 && count($google_reviews_cities[$single_google_review_link->id]) == 1 ? 'd-none' : '' }} js-is-default">
                                                                    <label for="">Default? <span
                                                                            class="fas fa-exclamation-circle"
                                                                            data-toggle="tooltip" title=""
                                                                            data-original-title="If the system doesn’t find a zip code match, this link is sent by default."></span></label>
                                                                    <input type="checkbox" class="form-control goog-check"
                                                                        name="is_default" value="1"
                                                                        {{ $single_google_review_link->is_default === 1 ? "checked='checked'" : '' }}>
                                                                </div>
                                                                <div class="col-sm-1 mt-6">
                                                                    <button
                                                                        class="btn ml-auto mt-1 btn-success btn-md google_review_or"
                                                                        data-id="{{ $single_google_review_link->id }}"
                                                                        onclick="add_or_dynamic_row(this)"
                                                                        style="float:left;">OR
                                                                    </button>
                                                                </div>
                                                                <input type="hidden" class="google_review_save"
                                                                    data-city-id="{{ isset($google_reviews_cities[$single_google_review_link->id]) && $google_reviews_cities[$single_google_review_link->id]->count() > 1 ? $google_reviews_cities[$single_google_review_link->id][0]->id : $single_google_review_link->id }}"
                                                                    data-id="{{ $single_google_review_link->id }}"
                                                                    data-google-review-link-id="{{ $single_google_review_link->id }}">
                                                                <!-- <div class="col-sm-1 mt-6">
                                                                        <button type="submit" class="btn ml-auto mt-1 btn-success btn-md google_review_save" data-city-id="{{ isset($google_reviews_cities[$single_google_review_link->id][0]) ? $google_reviews_cities[$single_google_review_link->id][0]->id : $single_google_review_link->id }}" data-id="{{ $single_google_review_link->id }}" data-google-review-link-id="{{ $single_google_review_link->id }}" style="float:left;">Save</button>
                                                                    </div> -->
                                                                <div class="col-sm-1 mt-6">
                                                                    <button
                                                                        class="btn ml-auto mt-1 btn-danger btn-md {{ isset($google_reviews_cities[$single_google_review_link->id]) && $google_reviews_cities[$single_google_review_link->id]->count() > 1 ? 'google_review_city_delete' : 'google_review_delete' }}"
                                                                        data-id="{{ isset($google_reviews_cities[$single_google_review_link->id]) && $google_reviews_cities[$single_google_review_link->id]->count() > 1 ? $google_reviews_cities[$single_google_review_link->id][0]->id : $single_google_review_link->id }}"
                                                                        style="float:left;"><span
                                                                            class="fa fa-trash"></span>
                                                                    </button>
                                                                </div>
                                                                <div
                                                                    class="process_result_google_review col-sm-3 px-1 mt-10">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div id="append_or_row_{{ $single_google_review_link->id }}">
                                                            @if (isset($google_reviews_cities[$single_google_review_link->id]) &&
                                                                    $google_reviews_cities[$single_google_review_link->id]->count() > 1)
                                                                @foreach ($google_reviews_cities[$single_google_review_link->id] as $key => $single_google_review_city)
                                                                    @if ($key > 0)
                                                                        <div class="form-group phase_city_sec">
                                                                            <div class="row mx-0 phase_form">
                                                                                <div class="col-sm-7">
                                                                                </div>
                                                                                <div class="col-sm-2">
                                                                                    <input name="zip_code"
                                                                                        value="{{ $single_google_review_city->zip_code }}"
                                                                                        class="form-control zip_code_class"
                                                                                        required>
                                                                                </div>
                                                                                <div class="col-sm-1">
                                                                                </div>
                                                                                <div class="col-sm-1 or_btn">
                                                                                    <button
                                                                                        class="btn ml-auto mt-1 btn-success btn-md google_review_or"
                                                                                        data-id="{{ $single_google_review_link->id }}"
                                                                                        style="float:left;"
                                                                                        onclick="add_or_dynamic_row(this)">OR
                                                                                    </button>
                                                                                </div>
                                                                                <input type="hidden"
                                                                                    class="google_review_save"
                                                                                    data-id="{{ $single_google_review_city->id }}"
                                                                                    data-google-review-link-id="{{ $single_google_review_link->id }}">
                                                                                <div class="col-sm-1 or_save">
                                                                                    <button
                                                                                        class="btn ml-auto mt-1 btn-danger btn-md google_review_city_delete"
                                                                                        data-id="{{ $single_google_review_city->id }}"
                                                                                        style="float:left;"><span
                                                                                            class="fa fa-trash"></span></button>
                                                                                </div>
                                                                                <div
                                                                                    class="process_result_google_review_city col-sm-6 px-1 mt-10">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    @endif
                                                                @endforeach
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                        <div id="id_new_review_row" class="dv-webhook-row">
                                        </div>

                                        <div class="row" id="id_dv_create_new">
                                            <div class="col-sm-12 ml-5 mr-0 mt-4">
                                                <button class="btn btn-md btn-success ml-auto mt-1"
                                                    onclick="addReviewRow()">Add New
                                                    Row
                                                </button>

                                                @if (isset($single_google_review_city))
                                                    <button type="submit"
                                                        class="btn ml-auto mt-1 btn-success btn-md google_review_save"
                                                        data-id="{{ $single_google_review_city->id }}"
                                                        data-google-review-link-id="{{ $single_google_review_link->id }}"
                                                        style="margin-right: 20px;">Save All
                                                    </button>
                                                @else
                                                    <button type="submit"
                                                        class="btn ml-auto mt-1 btn-success btn-md google_review_save"
                                                        style="margin-right: 20px;">Save All
                                                    </button>
                                                @endif

                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <div id="monitor-responses" role="tabpanel" aria-labelledby="monitor-responses-tab"
                                    class="tab-pane fade">
                                    <!--begin::Card-->
                                    {{-- <div class="card card-custom" --}}
                                    {{-- style="-webkit-box-shadow: none;-moz-box-shadow: none;-o-box-shadow: none;box-shadow: none;"> --}}
                                    {{-- <div class="card-header flex-wrap border-0 pb-0" style="padding: 0px"> --}}
                                    {{-- <div class="card-title"> --}}
                                    {{-- <h3 class="card-label">Review Message Log</h3> --}}
                                    {{-- </div> --}}
                                    {{-- <div class="card-toolbar"> --}}
                                    {{-- <div class="dropdown dropdown-inline mr-2"> --}}
                                    {{-- <div class="row"> --}}
                                    {{-- <div class="col-6"> --}}
                                    {{-- <form class="log-form-msg"> --}}
                                    {{-- <div id="logreportrangemsg" class="custom-date-picker"> --}}
                                    {{-- <i class="fa fa-calendar"></i>&nbsp; --}}
                                    {{-- <span></span> <i class="fa fa-caret-down"></i> --}}
                                    {{-- </div> --}}
                                    {{-- </form> --}}
                                    {{-- </div> --}}
                                    {{-- <div class="col-6 text-right"> --}}
                                    {{-- <button type="button" --}}
                                    {{-- class="btn btn-light-primary font-weight-bolder dropdown-toggle" --}}
                                    {{-- data-toggle="dropdown" aria-haspopup="true" --}}
                                    {{-- aria-expanded="false"> --}}
                                    {{-- <span class="svg-icon svg-icon-md"> --}}
                                    {{-- <i class="icon-xl la la-print"></i> --}}
                                    {{-- </span>Export</button> --}}
                                    {{-- <div --}}
                                    {{-- class="dropdown-menu dropdown-menu-sm dropdown-menu-right"> --}}
                                    {{-- <ul class="navi flex-column navi-hover py-2"> --}}
                                    {{-- <li --}}
                                    {{-- class="navi-header font-weight-bolder text-uppercase font-size-sm text-primary pb-2"> --}}
                                    {{-- Choose an option:</li> --}}
                                    {{-- <li class="navi-item"> --}}
                                    {{-- <a href="{{ route('message_log_export_csv', ['subdomain' => $subdomain]) }}" --}}
                                    {{-- class="navi-link export-custom-log"> --}}
                                    {{-- <span class="navi-icon"> --}}
                                    {{-- <i class="la la-file-text-o"></i> --}}
                                    {{-- </span> --}}
                                    {{-- <span class="navi-text">CSV</span> --}}
                                    {{-- </a> --}}
                                    {{-- </li> --}}
                                    {{-- </ul> --}}
                                    {{-- </div> --}}
                                    {{-- </div> --}}
                                    {{-- </div> --}}
                                    {{-- </div> --}}
                                    {{-- </div> --}}
                                    {{-- </div> --}}
                                    {{-- <div class="card-body" style="padding:0"> --}}
                                    {{-- <table class="table stylish-table no-wrap" --}}
                                    {{-- id="kt_datatable_messages_logs"></table> --}}
                                    {{-- </div> --}}
                                    {{-- </div> --}}
                                    <!--end::Card-->
                                </div>

                            </div>
                        </div>
                    </div>
                    <!--end::Card-->
                </div>
            </div>
        </div>
    </div>
    <style>
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            z-index: 2;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, .7);
            transition: .3s linear;
            z-index: 1000;
        }

        .loading {
            display: none;
        }

        .spinner-border.loading {
            position: fixed;
            top: 48%;
            left: 48%;
            z-index: 1001;
            width: 5rem;
            height: 5rem;
        }

        .nav-link:hover,
        .nav-link.active {
            background: #26A9DF !important;
            color: #fff !important;
        }

        .nav .nav-link:hover:not(.disabled) .nav-text {
            color: #fff !important;
        }
    </style>
@stop
