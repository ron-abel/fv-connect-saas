@extends('admin.layouts.default')

@section('title', 'VineConnect Admin - Filevine Project Phase Mapping')
@section('content')

    <!--begin::Subheader-->
    <div class="subheader py-2 py-lg-4 subheader-solid" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
            <!--begin::Info-->
            <div class="d-flex align-items-center flex-wrap mr-2">
                <!--begin::Page Title-->
                <h4 class="text-dark font-weight-bold mt-2 mb-2 mr-5">Project Phase - Timeline Mapping</h4>
                <!--end::Page Title-->

            </div>
            <!--end::Info-->
        </div>
    </div>
    <!--end::Subheader-->
    <div class="d-flex flex-column-fluid">
        <!--begin::Container-->
        <div class="container">
            <div class="overlay loading"></div>
            <div class="spinner-border text-primary loading" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <!--begin::Row-->
            <div class="row">
                <div class="col-md-12">
                    <!--begin::Card-->
                    <div class="card card-custom gutter-b example example-compact">
                        <div class="card-header">
                            <h5 class="card-title mt-7">Map Your Filevine Project Phases to your Timeline Templates</h5>
                        </div>
                        <div class="card-body">
                            <div class="pg_content">
                                <p><b>Instructions:</b> Map each of your Filevine Phases to the Timeline Template you
                                    created on the <a href="/admin/phase_categories" />Timeline Templates</a> page. Start
                                    by
                                    selecting a Project Type from your Filevine Org and select the Timeline Template you
                                    want to match. Click "Add Mapping".</p>
                                {{-- <p>Be sure to map each Filevine Project Phase to a Timeline Category and provide a description for each phase. Descriptions support rich text styling and embeddable media including images or videos from YouTube or Vimeo. You can use the following variables in your phase description: {{ $variable_keys }}.</p> --}}
                                <p>Anytime you modify, remove, or add a Project Phase in your Filevine Org, be sure to
                                    click
                                    the Fetch Project Phases button to refresh the list to map and provide your
                                    description.</p>
                                <div class="clear"></div>
                                <div class="callout_subtle lightgrey"><i class="fas fa-link"
                                        style="color:#383838;padding-right:5px;"></i>
                                    Support Article: <a
                                        href="https://intercom.help/vinetegrate/en/articles/5814318-timeline-mapping"
                                        target="_blank" />Phase Mapping</a></div>
                                <div class="callout_subtle lightgrey"><i class="fa fa-key mr-3"></i><a
                                        href="{{ url('admin/variables') }}" target="_blank" />&nbsp;List of Variables</a>
                                </div>
                            </div>
                            <div class="card">
                                <div class="row card-body">

                                  <!-- <div class="row ml-4">
                                        <div class="col-md-12 form-group">
                                            <label class="custom-checkbox-switch">
                                                <input type="checkbox" class="default_phase_mapping"
                                                    name="default_phase_mapping"
                                                    {{ isset($config->default_phase_mapping) && $config->default_phase_mapping ? 'checked' : '' }}>
                                                <span class="slider round"></span>
                                            </label>
                                            <span class="font-weight-bold ml-4">Default Phase Mapping</span>
                                        </div>
                                    </div> -->

                                    <form action="{{ route('add_phase_mappings', ['subdomain' => $subdomain]) }}"
                                        method="post" class="form-horizontal form-material col-md-12">
                                        @csrf

                                        <div
                                            style="width:100%; padding:5px; font-size:13px; font-weight:bold; color:#006700; text-align:center;">
                                        </div>

                                        <div class="form-group">
                                            <div class="col-sm-12 form-inline">

                                                <select id="id_project_types" class="form-control"
                                                    {{ count($added_projects) > 0 ? '' : 'required' }}>
                                                    <option value="" selected="selected">--Select Project Type--
                                                    </option>
                                                    @if (count($available_fv_project_types_all) > 0)
                                                        @foreach ($available_fv_project_types_all as $project_type)
                                                            @if (!in_array($project_type['projectTypeId']['native'], $mapped_fv_project_type_ids))
                                                                <option
                                                                    value="{{ $project_type['projectTypeId']['native'] }}">
                                                                    {{ $project_type['name'] }}
                                                                </option>
                                                            @endif
                                                        @endforeach
                                                    @endif
                                                </select>


                                                <select id="id_phase_templates" class="form-control ml-2"
                                                    {{ count($added_projects) > 0 ? '' : 'required' }}>
                                                    <option value="" selected="selected">--Match Timeline--</option>
                                                    <caption></caption>
                                                    @if (count($phase_templates_all) > 0)
                                                        @foreach ($phase_templates_all as $project_type)
                                                            @if (!in_array($project_type->template_name, $mapped_phase_templates_arr))
                                                                <option value="{{ $project_type->template_name }}">
                                                                    {{ $project_type->template_name }}
                                                                </option>
                                                            @endif
                                                        @endforeach
                                                    @endif
                                                </select>

                                                <span class="btn ml-1 btn-success btn-sm text-white"
                                                    onclick="addNewMapping()">Add Mapping</span>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <div class="col-sm-12 mt-3" id="divToAppendTAb">
                                                @if (count($added_projects) > 0)
                                                    @foreach ($added_projects as $project_type)
                                                        <span class='btn span_name span_tabs'
                                                            data-id='{{ $project_type->project_type_id }}'
                                                            data-template='{{ $project_type->template_name }}'
                                                            onclick='getPhases("{{ $project_type->project_type_id }}", "{{ $project_type->project_type_name }}", "{{ $project_type->template_name }}")'>
                                                            {{ $project_type->project_type_name }}
                                                        </span>
                                                    @endforeach
                                                @endif
                                            </div>
                                            <div class="col-sm-12 mt-3">
                                                <div id="currentProjectTypeContent"
                                                    style="display: {{ count($added_projects) > 0 ? '' : 'none' }}">
                                                    <h2><b>Current Template:</b> <span
                                                            id="currentTimelineTemplateName">{{ $current_template_name }}</span>
                                                    </h2>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 mt-3 mb-3">
                                                <div class="form-group row mb-3">
                                                    <label for="legalTeamTitle"
                                                        class="col-auto col-form-label col-form-label-sm">
                                                        Override Title:
                                                    </label>
                                                    <div class="col-sm-8">
                                                        <input placeholder="Title" class="form-control" type="text"
                                                            name="title" value="" id="title" />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-12 mt-3 mb-3">

                                                <hr />
                                                <div class="row d-flex align-items-center" style="margin-left: 0px">
                                                    <div>
                                                        <button
                                                            class='btn btn-warning mx-auto mx-md-0 text-white fetch_types'
                                                            type='button' style="display:none;">FETCH PROJECT PHASES
                                                        </button>
                                                        <span class='fetch_message'
                                                            style="line-height: 30px;margin-left: 10px;display: none;"></span>
                                                    </div>
                                                    <div class="ml-2">
                                                        <button class='btn btn-danger mx-auto mx-md-0 text-white'
                                                            type='button' style="display:none;"
                                                            id="delete_mapped_timeline">Delete Mapped Timeline
                                                        </button>
                                                        <span class='fetch_message'
                                                            style="line-height: 30px;margin-left: 10px;display: none;"></span>
                                                    </div>
                                                </div>


                                            </div>
                                            <div class="col-sm-12 d-flex">
                                                <table class="table user-table no-wrap">
                                                    <tr>
                                                        <!-- <th style="padding:5px;padding-left:12px;width:200px;">Project Types</th> -->
                                                        <th style="padding:5px;padding-left:16px;" class="w-20">
                                                            Mapping
                                                        </th>
                                                        {{-- <th style="padding:5px;padding-left:5px;" class="w-25">Phase Category</th> --}}
                                                        <th style="padding:5px;padding-left:5px;" class="w-80">Project
                                                            Phase Description
                                                        </th>
                                                        <!-- <th style="padding:5px;padding-left:40px;" class="w-25">
                                                            <span class="fas fa-exclamation-circle" data-toggle="tooltip"
                                                                title=""
                                                                data-original-title="Select the phase to be mapped by default."></span>
                                                        </th> -->
                                                    </tr>
                                                    <tr>
                                                        <!-- <td style=" vertical-align:top;" id="selectedTamplate"></td> -->
                                                        <td id="categoryInfo" colspan="5"
                                                            style="padding-top:0px; padding-left:5px;"></td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <div class="col-sm-12 d-flex">
                                                <div>
                                                    <a type="button" class="btn btn-md btn-success ml-auto mr-6 mt-1" onclick="addAllPhase()">Add All
                                                        Phases
                                                    </a>
                                                    <button class="btn btn-success mx-auto mx-md-0 text-white">Save All
                                                        Phases
                                                    </button>
                                                    <input type="hidden" name="currentProjectTypeId"
                                                        id="currentProjectTypeId"
                                                        value="{{ $current_project_typeid }}" />
                                                    <input type="hidden" name="currentProjectTypeName"
                                                        id="currentProjectTypeName"
                                                        value="{{ $current_project_type_name }}" />
                                                    <input type="hidden" name="currentTemplateName"
                                                        id="currentTemplateName" value="{{ $current_template_name }}" />
                                                    <input type="hidden" name="phaseName" id="phaseName"
                                                        value="" />
                                                    <input id="btnAddRow2" type="button"
                                                        class="btn btn-success text-white" value="Add Row"
                                                        style="margin-left:20px; display:none;" />
                                                </div>
                                                <!-- Noor Work Start -->
                                                <div>
                                                    <?php
                                                    if (isset($msg_err)) {
                                                        if ($msg_err) {
                                                            echo '<p class="awa-text text-success"><i class="fas fa-check-circle"></i> Looks Good </p>';
                                                        } else {
                                                            echo '<p class="awa-text text-danger"><i class="fas fa-times-circle"></i> Error: Something went wrong </p>';
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                                <!-- Noor Work End -->
                                            </div>
                                        </div>

                                    </form>
                                    <input type="hidden" id="initial_state">
                                    <input type="hidden" id="max_desc_words"
                                        value="{{ config('app.max_count_description') }}">
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
    </style>
    @php
        $message = '';
        if (session()->has('message')) {
            $message = session()->get('message');
        }
    @endphp
    <script type="text/javascript">
        var current_project_typeid = "{{ $current_project_typeid }}";
        var current_project_type_name = "{{ $current_project_type_name }}";
        var current_template_name = "{{ $current_template_name }}";
        var curPhase = "{{ $current_phase }}";
        var curCat = "{{ $current_cat }}";
        if (current_template_name) {
            current_template_name = current_template_name.replace("&#039;", "\'");
        }
    </script>
    <style>
        .tox-notifications-container {
            display: none;
        }

        .span_tabs.active_tab {
            background: #019acb !important;
            color: #fff !important;
        }

        .span_tabs {
            background: #f2f7f8 !important;
            color: #009efb !important;
            border: unset;
            border-radius: 0px !important;
            padding: 15px 10px 15px 10px;
        }
    </style>

@stop
@section('scripts')
    <script>
        var csrf_token = '{{ csrf_token() }}';
        var message = "{{ $message }}";
        if (message != "") {
            Swal.fire({
                text: message,
                icon: "success",
            });
        }
    </script>
@endsection
