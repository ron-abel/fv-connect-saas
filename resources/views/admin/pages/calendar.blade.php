@extends('admin.layouts.default')

@section('title', 'VineConnect Admin - Calendar')
@section('content')

    <!--begin::Subheader-->
    <div class="subheader py-2 py-lg-4 subheader-solid" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
            <!--begin::Info-->
            <div class="d-flex align-items-center flex-wrap mr-2">
                <!--begin::Page Title-->
                <h4 class="text-dark font-weight-bold mt-2 mb-2 mr-5">Filevine Calendar Sync</h4>
                <!--end::Page Title-->

            </div>
            <!--end::Info-->
        </div>
    </div>
    <!--end::Subheader-->
    <div class="overlay loading"></div>
    <div class="spinner-border text-primary loading" role="status">
        <span class="sr-only">Loading...</span>
    </div>

    <div class="d-flex flex-column-fluid">
        <!--begin::Container-->
        <div class="container">

            <!--begin::Row-->
            <div class="row">
                <div class="col-md-12">
                    <!--begin::Card-->
                    <div class="card card-custom gutter-b example example-compact">
                        <div class="card-header">
                            <h5 class="card-title mt-7">Configure Your Calendar Display Settings in Client Portal</h5>
                        </div>
                        <div class="card-body">
                            <div class="pg_content">
                            </div>

                            <ul class="nav nav-pills" id="myTab1" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link btn btn-outline-primary active" id="calendar-tab" data-toggle="tab"
                                        href="#calendar_setting">
                                        <span class="nav-text">Calendar Setting</span>
                                    </a>
                                </li>
                                {{-- <li class="nav-item">
                                    <a class="nav-link btn btn-outline-primary" id="notification-tab" data-toggle="tab"
                                        href="#notification_setting" aria-controls="profile">
                                        <span class="nav-text">Notification</span>
                                    </a>
                                </li> --}}
                            </ul>

                            <div class="tab-content mt-6" id="myTabContent1">
                                <div class="tab-pane fade show active" id="calendar_setting" role="tabpanel"
                                    aria-labelledby="trigger-tab">

                                    <form action="{{ route('calendar_save_setting', ['subdomain' => $subdomain]) }}"
                                        method="post">
                                        @csrf

                                        <div class="row mt-4">
                                            <div class="col-md-12 form-group">
                                                <label class="custom-checkbox-switch">
                                                    <input type="checkbox" class="calendar_visibility"
                                                        name="calendar_visibility"
                                                        {{ isset($calendar_setting->calendar_visibility) && $calendar_setting->calendar_visibility ? 'checked' : '' }}>
                                                    <span class="slider round"></span>
                                                </label>
                                                <span class="font-weight-bold ml-4">Calendar On/Off</span>
                                            </div>
                                        </div>

                                        <div
                                            class="row mt-4 collect_appointment_feedback_div {{ isset($calendar_setting->calendar_visibility) && $calendar_setting->calendar_visibility ? '' : 'd-none' }}">
                                            <div class="col-md-12 form-group">
                                                <label class="custom-checkbox-switch">
                                                    <input type="checkbox" class="collect_appointment_feedback"
                                                        name="collect_appointment_feedback"
                                                        {{ isset($calendar_setting->collect_appointment_feedback) && $calendar_setting->collect_appointment_feedback ? 'checked' : '' }}>
                                                    <span class="slider round"></span>
                                                </label>
                                                <span class="font-weight-bold ml-4">Collect Appointment Feedback from
                                                    Clients in
                                                    Filevine</span>
                                            </div>
                                        </div>

                                        <div
                                            class="row mt-4 feedback_type_div {{ isset($calendar_setting->calendar_visibility) && $calendar_setting->calendar_visibility && $calendar_setting->collect_appointment_feedback ? '' : 'd-none' }}">

                                            <div class="col-md-12 mb-4">
                                                <p>
                                                    <b>Instructions:</b> VineConnect will allow your clients to provide
                                                    notes, feedback, and other important information about a calendar
                                                    appointment item and you can configure how this information populates in
                                                    Filevine. Allow feedback to override the appointment note, create a new
                                                    collection item, or update collection item.
                                                </p>
                                            </div>

                                            <div class="col-md-6 form-group">
                                                <label> Choose Method of Feedback Collection</label>
                                                <select class="form-control" name="feedback_type">
                                                    <option value="">-Select-</option>
                                                    <option value="1"
                                                        {{ isset($calendar_setting->feedback_type) && $calendar_setting->feedback_type == 1 ? 'selected' : '' }}>
                                                        Allow Feedback to Override Appointment Notes
                                                    </option>
                                                    <option value="2"
                                                        {{ isset($calendar_setting->feedback_type) && $calendar_setting->feedback_type == 2 ? 'selected' : '' }}>
                                                        Sync Feedback to a Collection Section</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div
                                            class="row mt-4 sync_feedback_type_div {{ isset($calendar_setting->calendar_visibility) && $calendar_setting->calendar_visibility && isset($calendar_setting->feedback_type) && $calendar_setting->feedback_type == 2 ? '' : 'd-none' }}">
                                            <div class="col-md-6 form-group">
                                                <label> If Sync Feedback to a Collection Section</label>
                                                <select class="form-control" name="sync_feedback_type">
                                                    <option value="">-Select-</option>
                                                    <option value="1"
                                                        {{ isset($calendar_setting->sync_feedback_type) && $calendar_setting->sync_feedback_type == 1 ? 'selected' : '' }}>
                                                        Create New Collection Section Item</option>
                                                    <option value="2"
                                                        {{ isset($calendar_setting->sync_feedback_type) && $calendar_setting->sync_feedback_type == 2 ? 'selected' : '' }}>
                                                        Sync to Existing Collection Section Item</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div
                                            class="row mt-6 collection_div {{ isset($calendar_setting->calendar_visibility) && $calendar_setting->calendar_visibility && isset($calendar_setting->feedback_type) && $calendar_setting->feedback_type == 2 ? '' : 'd-none' }}">
                                            <div class="col-md-3">
                                                <label> Choose Project Type</label>
                                                <select class="form-control" name="project_type_id" id="project_type_id">
                                                    <option value="">Select Project Type</option>
                                                    @foreach ($project_type_list as $project_type)
                                                        <option value="{{ $project_type->projectTypeId->native }}"
                                                            {{ isset($calendar_setting->project_type_id) && $calendar_setting->project_type_id == $project_type->projectTypeId->native ? 'selected' : '' }}>
                                                            {{ $project_type->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="project_section_label"> Choose Section</label>
                                                <select class="form-control" name="collection_section_id"
                                                    id="collection_section_id">
                                                    <option value="">Choose Section</option>
                                                    @foreach ($collection_sections as $section)
                                                        <option value="{{ $section['sectionSelector'] }}"
                                                            {{ isset($calendar_setting->collection_section_id) && $calendar_setting->collection_section_id == $section['sectionSelector'] ? 'selected' : '' }}>
                                                            {{ $section['name'] }}</option>';
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                {{-- <label class="choose-field-label"> {{ isset($calendar_setting->sync_feedback_type) && $calendar_setting->sync_feedback_type == 1 ? 'Choose a Text Field for Feedback and a Date Field for Data Received' : 'Choose Field' }}</label> --}}
                                                <label class="choose-field-label">Choose Field</label>
                                                <select style="width: 100%" class="form-control select2 field_id"
                                                    id="kt_select2_3" multiple="multiple" name="field_id[]">
                                                    <option value="">Choose Field</option>
                                                    @foreach ($collection_section_fields as $field)
                                                        <option value="{{ $field['fieldSelector'] }}"
                                                            {{ in_array($field['fieldSelector'], $calendar_setting_section_fields) ? 'selected' : '' }}>
                                                            {{ $field['name'] . ' (' . $field['customFieldType'] . ')' }}
                                                        </option>';
                                                    @endforeach
                                                </select>
                                            </div>

                                            {{-- <div
                                                class="form-group col-md-2 display_as_div d-none {{ isset($calendar_setting->sync_feedback_type) && $calendar_setting->sync_feedback_type == 2 ? '' : 'd-none' }}">
                                                <label>Display As</label>
                                                <div class="checkbox-inline">
                                                    <label
                                                        class="checkbox checkbox-outline checkbox-outline-2x checkbox-primary checkbox-lg">
                                                        <input type="checkbox" name="display_as"
                                                            {{ isset($calendar_setting->display_as) && $calendar_setting->display_as ? 'checked' : '' }}>
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </div> --}}

                                            <div
                                                class="col-md-5 mt-6 section_display_div {{ isset($calendar_setting->sync_feedback_type) && $calendar_setting->sync_feedback_type == 2 ? '' : 'd-none' }}">
                                                <label class="project_section_label"> Choose Section Field to Display
                                                    Collection
                                                    Item By</label>
                                                <select class="form-control" name="display_item_collection_section_id"
                                                    id="display_item_collection_section_id">
                                                    <option value="">Choose Field</option>
                                                    @foreach ($collection_section_display_fields as $field)
                                                        <option value="{{ $field['fieldSelector'] }}"
                                                            {{ isset($calendar_setting->display_item_collection_section_id) && $calendar_setting->display_item_collection_section_id == $field['fieldSelector'] ? 'selected' : '' }}>
                                                            {{ $field['name'] . ' (' . $field['customFieldType'] . ')' }}
                                                        </option>';
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row mt-6">
                                            <div class="col-md-2">
                                                <button type="submit" class="btn btn-success mt-8">Save</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <div class="tab-pane fade" id="notification_setting" role="tabpanel"
                                    aria-labelledby="map-tab">
                                    <div class="row">
                                        <h4 class="mt-6">Appointment Notifications - Upcoming</h4>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            z-index: 100;
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

        .btn i {
            padding-right: 0px !important;
        }

        .alert.alert-custom {
            padding: 0rem 2rem;
        }
    </style>
    @php
        $success = '';
        $error = '';
        if (session()->has('success')) {
            $success = session()->get('success');
        }
        if (session()->has('error')) {
            $error = session()->get('error');
        }
    @endphp
@stop

@section('scripts')
    <script src="{{ asset('../js/admin/calendar.js') }}"></script>
    <script>
        var success = "{{ $success }}";
        var error = "{{ $error }}";
        if (success != "") {
            Swal.fire({
                text: success,
                icon: "success",
            });
        }
        if (error != "") {
            Swal.fire({
                text: error,
                icon: "error",
            });
        }
    </script>
@endsection
