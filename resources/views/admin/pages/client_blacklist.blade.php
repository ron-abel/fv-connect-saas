@extends('admin.layouts.default')

@section('title', 'VineConnect Admin - Client Portal Base Configurations')

@section('content')
@php
$all_data = \Request::all();
$tenant_details = [];
if(isset($all_data['tenant_details'])) {
$tenant_details = $all_data['tenant_details'];
}
@endphp
<style>
    .client-loader {
        position: absolute;
        right: 25px;
        top: 12px;
    }
    .clients-search-dropdown.hide { display: none; }
    .clients-search-dropdown {
        list-style: none;
        padding: 10px;
        background-color: white;
        border: 1px solid #eee;
        border-radius: 5px;
        width: 500px;
        position: absolute;
        z-index: 9;
        max-height: 300px;
        overflow: auto;
    }
    .clients-search-dropdown > li {
        padding: 10px 5px;
    }
    .clients-search-dropdown > li:hover {
        background: #eee;
    }
    .single-client-info .add-all-clients, .client-info-group .add-client-project {
        float: right;
        color: #F5A22F;
        cursor: pointer;
    }
    .client-info-group .add-client-project i {
        color: #F5A22F;
    }
    .single-client-info .add-all-clients.disabled, .client-info-group .add-client-project.disabled, .client-info-group .add-client-project.disabled i {
        color: #ccc;
        cursor: not-allowed;
        pointer-events: none;
    }
    .clients-search-dropdown .list-group-item {
        border: 0;
    }
    .green{
        color: green;
    }
</style>

<!--begin::Subheader-->
<div class="subheader py-2 py-lg-4 subheader-solid" id="kt_subheader">
    <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
        <!--begin::Info-->
        <div class="d-flex align-items-center flex-wrap mr-2">
            <!--begin::Page Title-->
            <h4 class="text-dark font-weight-bold mt-2 mb-2 mr-5">Client/Project Blacklist Tool</h4>
            <!--end::Page Title-->
        </div>
        <!--end::Info-->
    </div>
</div>
<!--end::Subheader-->
<!--begin::Entry-->
<div class="d-flex flex-column-fluid">
    <!--begin::Container-->
    <div class="container">
        <!--begin::Dashboard-->

        <div class="row">
            <div class="col-md-12">
                <div class="card card-custom gutter-b example example-compact">
                    <div class="card-header">
                        <h3 class="card-title">How To Use the Blacklist Tool</h3>
                    </div>
                    <div class="card-body">
                        <div class="pg_container_ mb-5">
                            <div class="pg_content">
                                <p><b>Instructions:</b> Use the search function to find <b>Clients</b> or <b>Projects</b> in your Filevine Org that you would like to add to the Blacklist. Blacklist restrictions include the ability to disable access to the Client Portal and/or disable notifications.</p>
								<p>Note that restricting at the <b>Client</b> level will prevent the Client and all associated projects, while restricting at the <b>Project</b> will prevent the Client from accessing or receiving notifications only for that project.</p>
                                <div class="callout_subtle lightgrey"><i class="fas fa-link" style="color:#383838;padding-right:5px;"></i> Support Article: <a href="https://intercom.help/vinetegrate/en/articles/5973285-client-blacklist" target="_blank">Blacklist Tool</a></div>
                            </div>
                        </div>

                        <div class="row">
                            <label for="search_filter" class="col-md-1 mb-3 col-form-label">Search By</label>
                            <div class="col-md-3 mb-3">
                                <div class="form-group">
                                    <select class="form-control" name="search_filter" id="search_filter">
                                        <option value="">Client and Project</option>
                                        <option value="Client">Client</option>
                                        <option value="Project">Project</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-group">
                                    <input placeholder="Begin typing a client or project name..." class="form-control" type="text" name="search_client" id="search_client" />
                                    <ul class="clients-search-dropdown hide"></ul>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <form action="{{ route('update_client_blacklist', ['subdomain' => $subdomain]) }}" method="POST">
                                        @csrf
                                        <table class="table">
                                            <tr>
                                                <th width="40%">Client Name or Project Name</th>
                                                <th>Client</th>
                                                <th>Project</th>
                                                <th>Allow Client Portal</th>
                                                <th>Allow Notifications</th>
                                                <th>Actions</th>
                                            </tr>
                                            @forelse ($blacklists as $blacklist)
                                                <tr>
                                                    <td>
                                                        {{ $blacklist->fv_full_name . ' (' . (!empty($blacklist->fv_project_id) ? "{$blacklist->fv_project_id}" : $blacklist->fv_client_id) . ')' }}
                                                        <input type="hidden" class="{{ !empty($blacklist->fv_project_id) ? 'project_id_' . $blacklist->fv_project_id : 'client_id_' . $blacklist->fv_client_id }}" name="ids[]" value="{{ $blacklist->id }}">
                                                    </td>
                                                    <td>
                                                        @if(!$blacklist->fv_project_id)
                                                            <i class="fa fa-check-circle green" aria-hidden="true"></i>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($blacklist->fv_project_id)
                                                            <i class="fa fa-check-circle green" aria-hidden="true"></i>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" class="allowed_portals" name="allow_portal[{{ $blacklist->id }}]" value="true" {{ $blacklist->is_allow_client_potal == 1 ? 'checked' : '' }} />
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" class="allowed_notification" name="allow_notification[{{ $blacklist->id }}]" value="true" {{ $blacklist->is_allow_notification == 1 ? 'checked' : '' }} />
                                                    </td>
                                                    <td>
                                                        <button href="javascript:;" data-href="{{ route('client_blacklist.delete', ['subdomain' => $subdomain, 'id' => $blacklist->id]) }}" class="btn btn-danger delete-client-record"><span class="fa fa-trash"></span></button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center">Empty Client Blacklist!</td>
                                                </tr>
                                            @endforelse
                                        </table>
                                        @if(count($blacklists) > 0)
                                            <div class="">
                                                <button type="submit" class="btn btn-success update-blacklist-changes">Save</button>
                                            </div>
                                        @endif
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--end::Container-->
<!--end::Entry-->

@php
    $success = "";
    $error = "";
    if(session()->has('success')){
        $success = session()->get('success');
    }
    if(session()->has('error')){
        $error = session()->get('error');
    }
@endphp

<script src="https://code.jquery.com/jquery-3.6.0.min.js">
</script>
<script src="{{ asset('../js/client_blacklist.js') }}">
</script>
@stop

@section('scripts')
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
