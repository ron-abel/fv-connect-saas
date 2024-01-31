@extends('admin.layouts.default')

@section('title', 'VineConnect Admin - Project Form')

@section('content')
    <!--begin::Subheader-->
    <div class="subheader py-2 py-lg-4 subheader-solid" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
            <!--begin::Info-->
            <div class="d-flex align-items-center flex-wrap mr-2">
                <!--begin::Page Title-->
                <h5 class="text-dark font-weight-bold mt-2 mb-2 mr-5">Form Builder</h5>
                <!--end::Page Title-->
            </div>
            <!--end::Info-->
        </div>
    </div>

    <div class="overlay loading"></div>
    <div class="spinner-border text-primary loading" role="status">
        <span class="sr-only">Loading...</span>
    </div>

    <div class="container" data-is-new-form="{{ isset($form) ? false:true }}">
        <div class="row form-list">
            <div class="col-md-12">
                <div class="card card-custom">
                    <div class="card-header ml-3">
                        <div class="row py-2">
                            <div class="clear"></div>
                            <h4 class="card-title mt-5">Form List</h4>
                            <div class="callout_subtle lightgrey ml-5"><i class="fas fa-link" style="color:#383838;"></i>
                                Support Article:
                                <a href="https://intercom.help/vinetegrate/en/articles/6698587-filevine-forms"
                                    target="_blank" />Filevine Forms</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="pg_content">
                            <p><b>Instructions:</b> When choosing a Form Type you’ll want to think about if this
                                information needs to be distributed outside of the client portal to determine if it
                                should be public or set for current clients. You’ll want to select field types that
                                correlate to the Filevine fields if you plan to map them, i.e. Date Field to Date Field,
                                Yes/No Toggle to Y/N field, etc.</p>
                        </div>
                        <form id="form-container">
                            <input type="hidden" name="form_id" value="{{ isset($form) ? $form->id : '' }}" />
                            <div class="row mt-8 mb-4 d-flex align-items-center">
                                <div class="col-6">
                                    <small class="text-danger" id="name-duplicate-message"></small>
                                    <input type="text" name="name" class="form-control"
                                        oninput="validateFormName(this, {{ isset($form) ? $form->id : null }})"
                                        placeholder="Form Name.."
                                        value="{{ isset($form) && isset($form->form_name) ? $form->form_name : '' }}">
                                </div>
                                <div class="col-6 d-flex align-items-center">
                                    <label class="custom-checkbox-switch mb-0">
                                        <input type="checkbox" name="is_active"
                                            {{ isset($form) && $form->is_active == 1 ? 'checked' : '' }}>
                                        <span class="slider round"></span>
                                    </label>
                                    <span class="ml-2">Eligibile</span>
                                </div>
                            </div>
                            <div class="form-group mb-6 row">
                                <div class="col-md-7">
                                    <textarea class="form-control" name="description" rows="4" placeholder="Form Description..">{{ isset($form) && isset($form->form_description) ? $form->form_description : '' }}</textarea>
                                </div>
                            </div>

                            <div class="form-group mb-6">
                                <div class="col-md-7 pl-0">
                                    <label class="font-weight-bold">Success Message</label>
                                    <textarea class="form-control form_success_message" name="form_success_message" cols="60" rows="6"
                                        id="form_success_message">{{ isset($form) && isset($form->success_message) ? $form->success_message : '' }}</textarea>
                                </div>
                            </div>

                            <div class="form-group mb-6 row col-md-7">
                                <label class="font-weight-bold">Choose Form Type</label>
                                <select class="form-control" name="is_public_form">
                                    <option value="">Choose Form Type</option>

                                    @if(isset($form))

                                    <option value="1" {{ $form->is_public_form ? 'selected' : '' }}>
                                        Public Forms
                                    </option>

                                    @else


                                    <option value="1" selected>
                                        Public Forms
                                    </option>

                                    @endif


                                    <option value="0" {{ isset($form) && !$form->is_public_form ? 'selected' : '' }}>
                                        Current Clients
                                    </option>
                                </select>
                            </div>

                            <div
                                class="mb-2 row create-project-checkbox-div {{ isset($form) && (!empty($form->create_fv_project) || $form->is_public_form) ? '' : 'd-none' }}">
                                <div class="col-md-12">
                                    <div class="checkbox-inline">
                                        <label
                                            class="checkbox checkbox-outline checkbox-outline-2x checkbox-primary checkbox-lg">
                                            <input type="checkbox" name="create_fv_project"
                                                {{ isset($form) && $form->create_fv_project ? 'checked' : '' }}>
                                            <span></span>
                                            Create a Filevine Project on Form Submission?
                                        </label>
                                    </div>
                                    <p>To create a new Project from Public Form, the form must include client first name,
                                        last name, email, and phone number. This will create a contact record in Filevine
                                        and associate it with the new project. Choose how you would like to set the new
                                        Project Name as either the client’s name or as a form field.</p>
                                </div>
                            </div>


                            <div
                                class="mb-2 row sync-project-checkbox-div {{ isset($form) && !empty($form->sync_existing_fv_project) ? '' : 'd-none' }}">
                                <div class="col-md-12">
                                    <div class="checkbox-inline">
                                        <label
                                            class="checkbox checkbox-outline checkbox-outline-2x checkbox-primary checkbox-lg">
                                            <input type="checkbox" name="sync_existing_fv_project"
                                                {{ isset($form) && $form->sync_existing_fv_project ? 'checked' : '' }}>
                                            <span></span>
                                            Allow Response to Sync to Existing Filevine Project?
                                        </label>
                                    </div>
                                    <p>The form must include values for client first name, last name, email and phone
                                        number. On submit we will match to the first project we find that matches the chosen
                                        project template.</p>
                                </div>
                            </div>

                            <div class="mb-6 row">
                                <div
                                    class="form-group col-md-6 project-type-div">
                                    <label class="font-weight-bold">Choose Project Type</label>
                                    <select class="form-control" name="fv_project_type_id">
                                        <option value="">Choose Project Type</option>
                                        @foreach ($project_type_lists as $project_type_list)
                                            <option value="{{ $project_type_list->projectTypeId->native }}"
                                                {{ isset($form) && $project_type_list->projectTypeId->native == $form->fv_project_type_id ? 'selected' : '' }}>
                                                {{ $project_type_list->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- <div
                                    class="form-group col-md-6 project-div">
                                    <label class="font-weight-bold">Choose Project</label>
                                    <select class="form-control" name="fv_project_id">
                                        <option value="">Choose Project</option>
                                        @if (!empty($form->fv_project_id))
                                            <option value="{{ $form->fv_project_id }}" selected>
                                                {{ $form->fv_project_name }}</option>
                                        @endif
                                    </select>
                                </div> --}}
                                <div
                                    class="form-group col-md-6 assign-project-name-div">
                                    <label class="font-weight-bold">Assign Project Name As</label>
                                    <select class="form-control" name="assign_project_name_as">
                                        <option value="First and Last Name"
                                            {{ isset($form) && $form->assign_project_name_as == 'First and Last Name' ? 'selected' : '' }}>
                                            Client First and Last Name</option>
                                        <option value="Map a Field Value"
                                            {{ isset($form) && $form->assign_project_name_as == 'Map a Field Value' ? 'selected' : '' }}>
                                            Add a Form Field</option>
                                    </select>
                                </div>
                            </div>

                        </form>

                        <div class="row mt-8 mb-4 d-flex align-items-center ml-1"> <label class="font-weight-bold">Map your
                                Form Field to Filevine</label></div>
                        <div class="row mb-4">
                            <div class="col-md-6" id="form-builder-container"></div>
                            @if (isset($form))
                                <div class="col-md-6 fv-mapping-items">
                                    @foreach ($form_mappings as $key => $form_mapping)
                                        <div class="row mb-6 fv-mapping-item">
                                            <div class="form-group col-md-1">
                                                <div class="checkbox-inline mt-3">
                                                    <label
                                                        class="checkbox checkbox-outline checkbox-outline-2x checkbox-primary checkbox-lg">
                                                        <input type="checkbox" class="form_mapping_enable"
                                                            onclick="showHideFormMappingRow(event, this)"
                                                            name="form_mapping_enable[]"
                                                            {{ $form_mapping->form_mapping_enable ? 'checked' : '' }}>
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="form-group col-md-1" style="padding: 0">
                                                <label class="field-no mt-3">Field #{{ $key + 1 }}</label>
                                            </div>
                                            <div
                                                class="col-md-3 form-mapping-row {{ $form_mapping->form_mapping_enable ? '' : 'd-none' }}">
                                                <select class="form-control fv_section_name" name="fv_section_name[]"
                                                    onchange="getFieldList(event)">
                                                    <option value="{{ $form_mapping->fv_section_id }}">
                                                        {{ $form_mapping->fv_section_name }}</option>
                                                </select>
                                            </div>
                                            <div
                                                class="col-md-3 form-mapping-row {{ $form_mapping->form_mapping_enable ? '' : 'd-none' }}">
                                                <select class="form-control fv_field_name" name="fv_field_name[]">
                                                    <option value="{{ $form_mapping->fv_field_id }}">
                                                        {{ $form_mapping->fv_field_name }}</option>
                                                </select>
                                            </div>
                                            <div
                                                class="col-md-1 form-mapping-row {{ $form_mapping->form_mapping_enable ? '' : 'd-none' }}">
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-grey remove"><i
                                                            class="fa fa-trash"></i></button>
                                                    {{-- <button type="button" class="btn btn-sm btn-grey moveup p-1"><i
                                                            class="fa fa-arrow-up"></i></i></button>
                                                    <button type="button" class="btn btn-sm btn-grey movedown p-1"><i
                                                            class="fa fa-arrow-down"></i></button> --}}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="col-md-6 fv-mapping-items d-none">
                                    <div class="row mb-6 fv-mapping-item">
                                        <div class="form-group col-md-1">
                                            <div class="checkbox-inline mt-3">
                                                <label
                                                    class="checkbox checkbox-outline checkbox-outline-2x checkbox-primary checkbox-lg">
                                                    <input type="checkbox" class="form_mapping_enable"
                                                        onclick="showHideFormMappingRow(event, this)"
                                                        name="form_mapping_enable[]">
                                                    <span></span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-1" style="padding: 0">

                                            <label class="field-no mt-3">Field #1</label>
                                        </div>
                                        <div class="col-md-3 d-none form-mapping-row">
                                            <select class="form-control fv_section_name" name="fv_section_name[]"
                                                onchange="getFieldList(event)">
                                                <option value="">Section</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 d-none form-mapping-row">
                                            <select class="form-control fv_field_name" name="fv_field_name[]">
                                                <option value="">Field</option>
                                            </select>
                                        </div>
                                        <div class="col-md-1 d-none form-mapping-row">
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-grey remove"><i
                                                        class="fa fa-trash"></i></button>
                                                {{-- <button type="button" class="btn btn-sm btn-grey moveup p-1"><i
                                                        class="fa fa-arrow-up"></i></i></button>
                                                <button type="button" class="btn btn-sm btn-grey movedown p-1"><i
                                                        class="fa fa-arrow-down"></i></button> --}}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- <div class="form-group add-collection-button-div d-none">
                            <div class="col-md-6 text-right">
                                <button class="btn mr-6" id="add-collection"><i class="fa fa-plus"></i> &nbsp; Add Another
                                    Item</button>
                            </div>
                        </div> --}}

                        <div class="form-group mb-4 d-flex w-50 ml-auto">
                            <button class="btn btn-danger ml-auto" type="button" id="clear_form">Clear Form</button>
                            <button type="submit" class="btn btn-primary ml-2" id="save_form">Save Form</button>
                        </div>
                        <div class="d-flex mt-5">
                            <a href="{{ url('admin/forms') }}" class="btn btn-primary"> <i class="fa fa-arrow-left"
                                    aria-hidden="true"></i>
                                Back To Form List</a>
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

        .btn i {
            padding-right: 0px !important;
        }

        /* Hide form builder Add Option(+) Button */
        li[type="radio-group"] .option-actions {
            display: none;
        }

        li[type="text"] div.subtype-wrap {
            display: none;
        }

        li[type="textarea"] div.subtype-wrap {
            display: none;
        }

        .field_label_span {
            color: #8f8f8f;
            margin-left: 10px;
            font-size: 12px;
            font-weight: 600;
        }

        .hide-subtype .subtype-wrap,
        .select-field .option-value {
            display: none !important;
        }

        .select-field .option-label {
            width: 75% !important;
        }

        .form-wrap.form-builder .frmb .sortable-options>li .formbuilder-icon-cancel {
            position: relative;
            opacity: 1;
            float: right;
            right: 14px;
            height: 18px;
            width: 18px;
            top: 8px;
            font-size: 12px;
            padding: 0;
            color: #c10000;
        }

        .form-wrap.form-builder .frmb .radio-group-field .sortable-options li:nth-child(2) .remove-option,
        .form-wrap.form-builder .frmb .radio-group-field .sortable-options li:nth-child(2) .remove-option,
        .form-wrap.form-builder .frmb .sortable-options>li:nth-child(1) .remove-option,
        .form-wrap.form-builder .frmb .file-field .subtype-wrap {
            display: none;
        }

        .input-set-control .control-icon i {
            font-size: 12px;
            font-weight: 600;
            position: relative;
            top: 0;
            border-radius: 100%;
            border: 2px solid;
            width: 20px;
            height: 20px;
            text-align: center;
            line-height: 15px;
        }
    </style>
@stop
@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('js/select2.js') }}"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="{{ asset('js/form-builder.min.js') }}"></script>
    <script>
        var form_id = "{{ isset($form) ? $form->id : null }}";
        var form_mappings_count = "{{ isset($form_mappings_count) ? $form_mappings_count : 0 }}";
        var site_url = "{{ url('/') }}";
    </script>
    <script src="{{ asset('../js/admin/forms.js?' . time()) }}"></script>
@endsection
