@extends('admin.layouts.default')

@section('title', 'VineConnect Admin - Project Timeline Templates')
@section('content')

<!--begin::Subheader-->
<div class="subheader py-2 py-lg-4 subheader-solid" id="kt_subheader">
    <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
        <!--begin::Info-->
        <div class="d-flex align-items-center flex-wrap mr-2">
            <!--begin::Page Title-->
            <h4 class="text-dark font-weight-bold mt-2 mb-2 mr-5">Timeline Setup</h4>
            <!--end::Page Title-->

        </div>
        <!--end::Info-->
    </div>
</div>
<!--end::Subheader-->
<div class="d-flex flex-column-fluid">
    <!--begin::Container-->
    <div class="container">
        <!--begin::Row-->
        <div class="row">
            <div class="col-md-12">
                <!--begin::Card-->
                <div class="card card-custom gutter-b example example-compact">
                    <div class="card-header">
                        <h5 class="card-title mt-7">Setting Up Your Timeline Templates</h5>
                    </div>
                    <div class="card-body">
						<div class="pg_content">
                            <p><b>Instructions:</b> Choose a Timeline Template that matches your practice and click Add Timeline OR create a custom timeline that suits your needs. You can think of Categories as milestones along a project timeline and Category Description as the birds-eye-view information you’ll present to the client as the life of their case. This tool supports text styling, outbound links, and embeddable media such as images or video from YouTube or Vimeo.  You can override Category Titles and sort the categories to effectively create your own custom Timeline.</p>
							{{--<p>Use <b>Add Row</b> to add each Phase Category to your Timeline. You can override Category Titles and sort the categories to effectively create your own custom Timeline.</p>--}}
							{{-- <p>You can use the following variables in your Timeline Descriptions to automatically pull the correct data: {{ $variable_keys }}.</p> --}}
                            <div class="clear"></div>
							<div class="callout_subtle lightgrey"><i class="fas fa-link" style="color:#383838;padding-right:5px;"></i> Support Article: <a href="https://intercom.help/vinetegrate/en/articles/5814275-timeline-templates" target="_blank" />Timeline Templates Config</a></div>
                            <div class="callout_subtle lightgrey"><i class="fa fa-key mr-3"></i><a href="{{ url('admin/variables') }}" target="_blank" />&nbsp;List of Variables</a></div>
						</div>
                        <div class="card">
                            <div class="row card-body">

                                <div class="row mb-4">
                                    <div class="col-md-12 form-group">
                                        <label class="custom-checkbox-switch">
                                            <input type="checkbox" class="is_display_timeline"
                                                name="is_display_timeline"
                                                {{ isset($config->is_display_timeline) && $config->is_display_timeline ? 'checked' : '' }}>
                                            <span class="slider round"></span>
                                        </label>
                                        <span class="font-weight-bold ml-4">Display Timeline on Client Portal</span>
                                    </div>
                                </div>


                                <form action="{{ route('add_phase_categories', ['subdomain' => $subdomain]) }}" method="post" id="submit-form" class="form-horizontal form-material col-md-12">
                                    @csrf
									@if(session()->has('message'))
									<h6 class="save_success"><i class="fas fa-check-circle save_success"></i>&nbsp;{{ session()->get('message') }}</h6>
                        			@endif
                                    <div class="row mb-7 form-inline">
                                        <select name="lstTemplates" id="lstTemplates" class="form-control" {{ (count($selected_phase_templates) > 0 ? '' : 'required') }}>
                                            <option value="" selected="selected">--Select Timeline Template--</option>
                                            @if(count($available_phase_templates) > 0)
                                                @foreach ($available_phase_templates as $phase_template)
                                                <option value="{{ $phase_template->template_name }}">
                                                    {{ $phase_template->template_name }}
                                                </option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <span class="btn ml-1 btn-success btn-sm text-white" onclick="addTab()">Add Template</span>
                                        <span class="btn ml-1 btn-success btn-sm text-white" data-toggle="modal" data-target="#customTemplateModal">Create Custom Template</span>
                                    </div>

                                    <div class="form-group">
                                        <div class="col-sm-12 mt-3 mb-3" id="divToAppendTAb" style="height: 50px;">
                                            @if(count($selected_phase_templates) > 0)
                                                @foreach ($selected_phase_templates as $phase_template)
                                                <span class='btn span_name span_tabs' data-id='{{ $phase_template->template_name }}' data-type="general" onclick='setSelectedTemplateInfo("{{ $phase_template->template_name }}")'>{{ $phase_template->template_name }}</span>
                                                @endforeach
                                            @endif
                                            <!-- check for tenant templates if any -->
                                            @if(count($available_tenant_templates) > 0)
                                                @foreach ($available_tenant_templates as $tenant_template)
                                                <div class="btn p-0 m-0">
                                                    <span class='btn span_name span_tabs' data-id='{{ $tenant_template->template_name }}' data-type="custom" onclick='setSelectedTemplateInfo("{{ $tenant_template->template_name }}")'>{{ $tenant_template->template_name }}</span>
                                                    <span class="fa fa-edit edit_custom_template" data-id="{{ $tenant_template->id }}"></span>
                                                </div>
                                                @endforeach
                                            @endif
                                        </div>
                                        <div class="col-sm-12 d-flex form-inline">
                                            <div class="w-100">
                                                <div class="form-group row mb-3">
                                                    <label for="legalTeamTitle" class="col-auto col-form-label col-form-label-sm">Customize Timeline Title:</label>
                                                    <div class="col-sm-9">
                                                        <input placeholder="Title" class="form-control w-100" type="text" name="title" value="" id="categoryOverrideTitle"/>
                                                    </div>
                                                </div>
                                            </div>
                                            <table class="table no-wrap">
                                                <tr>
                                                    <th style="padding:5px 0 0 23px;width:310px;">Categories</th>
                                                    <th style="padding:5px 0 0 12px;">Category Description</th>
                                                    <!-- <th style="padding:5px 0 0 12px;width: 195px;">Default? <span class="fas fa-exclamation-circle" id="default-tooltip" data-toggle="tooltip" title="" data-original-title="Select the phase to be mapped by default."></span></th> -->
                                                </tr>
                                                <tr>
                                                    <!-- <td style=" vertical-align:top;" id="lstTemplatesParent"></td> -->
                                                    <td colspan="3" id="categoryInfo" style="padding-top:0px;"></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="col-sm-12 d-flex py-1">
                                            <div>
                                                <input type="hidden" name="currentTemplateX" id="currentTemplateX" value="{{ $selected_phase_template }}" />
                                                <input type="hidden" name="currentTemplateXType" id="currentTemplateXType" value="" />
                                                <button id="save-button" class="btn btn-success mx-auto mx-md-0 text-white">Save</button>
                                                <input id="btnAddRow" type="button" class="btn btn-success text-white" value="Add Row" style="margin-left:20px; display:none;" />
                                                <input id="deleteTemplate" type="button" class="btn btn-danger text-white" value="Delete Template" style="margin-left:20px;" />
                                            </div>
                                            <!-- Noor Work Start -->
                                            <div>
                                                <?php
                                                if (isset($msg_err)) {
                                                    if ($msg_err) {
                                                        echo '<p class="text-success"><i class="fas fa-check-circle"></i> Looks Good </p>';
                                                    } else {
                                                        echo '<p class="text-danger"><i class="fas fa-times-circle"></i> Error: Something went wrong </p>';
                                                    }
                                                }
                                                ?>
                                            </div>
                                            <!-- Noor Work End -->
                                        </div>
                                    </div>
                                </form>
                                <input type="hidden" id="initial_state">
                                <input type="hidden" id="max_desc_words" value="{{config('app.max_count_description')}}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- modal for adding template -->
<div class="modal" id="customTemplateModal" tabindex="-1" role="dialog" aria-labelledby="customTemplateModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customTemplateModalLabel">Create Custom Template</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-xl-12">
                        <!--begin::Input-->
                        <div class="form-group">
                            <label for="custom-template-name">Name</label>
                            <input id="custom-template-name" type="text" class="form-control form-control-solid form-control-lg" name="custom-template-name" placeholder="" />
                        </div>
                        <!--end::Input-->
                    </div>
                    <div class="col-xl-12 mt-2">
                        <!--begin::Input-->
                        <div class="form-group">
                            <label for="custom-template-desc">Description</label>
                            <input id="custom-template-desc" type="text" class="form-control form-control-solid form-control-lg" name="custom-template-desc" placeholder="" />
                        </div>
                        <!--end::Input-->
                    </div>
                    <div class="col-xl-12 mt-2">
                        <!--begin::Input-->
                        <div class="form-group">
                            <div class="checkbox-inline">
                                <label for="custom-template-default" class="checkbox checkbox-outline checkbox-outline-2x checkbox-primary checkbox-lg">
                                    <input type="checkbox" class="" name="custom-template-default" id="custom-template-default">
                                    <span class="mr-2"></span>
                                    Allow on mapping page
                                </label>
                            </div>
                        </div>
                        <!--end::Input-->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary createcustomtemplate-close" data-dismiss="modal" id="createcustomtemplate-close">Close</button>
                <button type="button" class="btn btn-primary createcustomtemplate-create" id="createcustomtemplate-create" data-op="create">Create</button>
            </div>
        </div>
    </div>
</div>
<!-- modal for editing template -->
<div class="modal" id="customTemplateEditModal" tabindex="-1" role="dialog" aria-labelledby="customTemplateEditModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customTemplateEditModalLabel">Edit Custom Template</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-xl-12">
                        <!--begin::Input-->
                        <div class="form-group">
                            <label for="custom-template-name-edit">Name</label>
                            <input id="custom-template-name-edit" type="text" class="form-control form-control-solid form-control-lg" name="custom-template-name-edit" placeholder="" />
                        </div>
                        <!--end::Input-->
                    </div>
                    <div class="col-xl-12 mt-2">
                        <!--begin::Input-->
                        <div class="form-group">
                            <label for="custom-template-desc-edit">Description</label>
                            <input id="custom-template-desc-edit" type="text" class="form-control form-control-solid form-control-lg" name="custom-template-desc-edit" placeholder="" />
                        </div>
                        <!--end::Input-->
                    </div>
                    <div class="col-xl-12 mt-2">
                        <!--begin::Input-->
                        <div class="form-group">
                            <div class="checkbox-inline">
                                <label for="custom-template-default-edit" class="checkbox checkbox-outline checkbox-outline-2x checkbox-primary checkbox-lg">
                                    <input type="checkbox" class="" name="custom-template-default-edit" id="custom-template-default-edit">
                                    <span class="mr-2"></span>
                                    Allow on mapping page
                                </label>
                            </div>
                        </div>
                        <!--end::Input-->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary createcustomtemplate-close" data-dismiss="modal" id="createcustomtemplate-close">Close</button>
                <button type="button" class="btn btn-primary createcustomtemplate-create" id="createcustomtemplate-create" data-op="edit">Update</button>
            </div>
        </div>
    </div>
</div>
@php
    $message = "";
    if(old('message')){
        $message = old('message');
    }
    $template_name = "";
    if(old('template_name')){
        $template_name = htmlspecialchars(old('template_name'));
    }
@endphp
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"></script>
<script type="text/javascript">
    var curTemplate = "{{ $selected_phase_template }}";
    var template_name = "{{ $template_name }}";
    if(template_name){
        curTemplate = template_name.replace("&#039;", "\'");
    }
    if(curTemplate){
        curTemplate = curTemplate.replace("&#039;", "\'");
    }
    var curTenantTemplate = "{{ $selected_tenant_phase_template }}";
    if(curTenantTemplate){
        curTenantTemplate = curTenantTemplate.replace("&#039;", "\'");
    }
    $('#default-tooltip').tooltip();
</script>

<style>
    .tox-notifications-container{display:none;}
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
    .edit_custom_template {
        position: relative;
        display: inline-block;
        background: #f2f7f8 !important;
        color: #009efb !important;
        border-radius: 0px !important;
        padding: 14.3px 15px 16px 15px;
        line-height: 1.5;
        clear: both;
        margin-left: -4px;
    }
    .edit_custom_template:hover {
        background: #019acb !important;
        color: #fff !important;
    }

</style>

@stop
@section('scripts')
<script>
$(document).on("click", ".isDefault", function () {
    if ($(this).is(":checked")) {
        $('.isDefault').prop('checked', false);
        $(this).prop('checked', true);
    }
});
var csrf_token = '{{ csrf_token() }}';
var message = "{{ $message }}";
if (message != "") {
    Swal.fire({
        text: message,
        icon: "success",
    });
}
var edit_template_id = "";
</script>
<script src="https://code.jquery.com/ui/1.13.0/jquery-ui.js"></script>
@endsection
