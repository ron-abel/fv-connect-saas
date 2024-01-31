@extends('admin.layouts.default')

@section('title', 'Tenants')

@section('content')
<style>
    .delete-btn{
        height: 40px !important;
        width: 40px !important;
    }
</style>
<!--begin::Subheader-->
<div class="subheader py-2 py-lg-4 subheader-solid" id="kt_subheader">
    <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
        <!--begin::Info-->
        <div class="d-flex align-items-center flex-wrap mr-2">
            <!--begin::Page Title-->
            <h4 class="text-dark font-weight-bold mt-2 mb-2 mr-5">Manage VineConnect Admin Users</h4>
            <!--end::Page Title-->

        </div>
        <!--end::Info-->
    </div>
</div>
<!--end::Subheader-->
<div class="main-content container">
    <div class="row mt-6">
        <div class="col-lg-12">
            <!--begin::Card-->
            <div class="card card-custom gutter-b example example-compact">
                <!--begin::Form-->
                    <div class="card-header">
                        <h5 class="card-title mt-7">Add Users with Admin or Read-Only Permissions</h5>
						<p><b>Instructions:</b> Add other users to VineConnect here. Select <b>Add New</b> and include the email address of the user to add. Check “Read Only” if you want the user to access only the VineConnect Dashboards. User invites that are unchecked will be granted Tenant Manager access with the ability to view and edit all configurations. User seats are limited to 5.
					</div>
                    <div class="card-body">
                        @if( session()->has('success') )
                            <div class="alert alert-primary" role="alert">
                                {{ session()->get('success') }}
                            </div>
                        @endif
                        @if( session()->has('error') )
                            <div class="alert alert-danger" role="alert">
                                {{ session()->get('error') }}
                            </div>
                        @endif
                        @error('email')
                            <div class="alert alert-danger" id="email-error">{{ $message }}</div>
                        @enderror
                        <div class="mb-7">
                            <div class="row align-items-center">
                                <div class="col-lg-3 col-xl-4">
                                    <button class="btn btn-success float-left" data-toggle="modal" data-target="#addNew">Add New</button>
                                </div>
								<div class="col-lg-9 col-xl-8">
                                    <div style="float:right;" class="row align-items-center">
                                        <div style="max-width:100%;flex:100%;" class="col-md-4 my-2 my-md-0">
                                            <div class="input-icon">
                                                <input type="text" class="form-control" placeholder="Search Users..." id="kt_datatable_search_query" />
                                                <span>
                                                    <i class="flaticon2-search-1 text-muted"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                       <table class="datatable datatable-bordered datatable-head-custom" id="kt_datatable">
                           <thead>
                               <tr>
                                   <th title="Field #1">Number</th>
                                   <th  title="Field #2">Name</th>
                                   <th  title="Field #3">Email</th>
                                   <th  title="Field #4">Role</th>
                                   <th  title="Field #5">Registered At</th>
                                   <th  title="Field #6">Invited At</th>
                                   <th  title="Field #7">Actions</th>
                               </tr>
                           </thead>
                           <tbody>
                               @foreach($tenant_details->user_invites as  $ind=>$user_invites)
                               <tr>
                                   <td>{{ $ind+1 }}</td>
                                   <td>{{ $user_invites->user?$user_invites->user->full_name:"" }}</td>
                                   <td>{{ $user_invites->email }}</td>
                                   <td>
                                        <select class="form-control change-role" data-id="{{ $user_invites->id }}">
                                            <option value="">Select Role</option>
                                            @foreach($roles as $role)
                                                @php
                                                    $selected = "";
                                                    if($role->id == $user_invites->user_role_id) $selected = "selected";
                                                @endphp
                                                <option {{$selected}} value="{{$role->id}}">{{$role->user_role_name}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                   <td>{{ $user_invites->user?$user_invites->user->created_at:"" }}</td>
                                   <td>{{ $user_invites->created_at }}</td>
                                   <td style="">
                                    <div>
                                    <button role="button" type="button" data-id="{{ $user_invites->id }}" class="btn btn-danger delete_invite_user" data-target="15"><span class="fa fa-trash"></span></button>
                                    </div>
                                   </td>
                               </tr>
                               @endforeach
                           </tbody>
                       </table>
                    </div>
                <!--end::Form-->
            </div>
            <!--end::Card-->
        </div>
    </div>
</div>

{{-- Add New Modal --}}
<div class="modal" id="addNew" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add New VineConnect User</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="addNewForm" action="{{ route('users.invite', ['subdomain' => $subdomain]) }}" method="post">
            @method("POST")
             @csrf
        <div class="row">
            <div class="form-group col-md-6">
                <label><b>Email To Invite:</b></label>
                <input class="form-control email col-md-8" name="email">
            </div>
        </div>
            <div class="row">
                <div style="margin-top:15px;" class="form-group col-md-12">
                    <label><b>Read Only Access?</b><br>
					Check for permissions limited to viewing only the dashbaord. Leave unchecked for full Admin Permissions.<br>
					<input style="margin-top:15px;" type="checkbox" class="form-control goog-check" value="1" name="read_only"></label>
                </div>
            </div>
          <div class="text-right">
            <button class="btn btn-success">Send</button>
          </div>
        </form>
        </div>
      </div>
    </div>
  </div>
@endsection
@section('scripts')
<script>
    $("body").on("change", '.change-role', function(){
        let invite_id = $(this).attr("data-id");
        let role_id = $(this).val();
        $.ajax({
            url: "change_invite_role?invite_id="+invite_id,
            data: {role_id: role_id},
            success: function(res){
                Swal.fire({
                    text: "User role updated successfully!",
                    icon: "success",
                });
            }
        });
    });
    $("body").on("click", '.delete_invite_user', function(){
        let invite_id = $(this).attr("data-id");
        Swal.fire({
            title: 'Are you sure?',
            showDenyButton: true,
            showCancelButton: false,
            confirmButtonText: 'Delete',
            denyButtonText: `Cancel`,
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "delete_invite_role?invite_id="+invite_id,
                    success: function(res){
                        window.location.reload();
                    }
                });
            }
        });
    });
</script>
@endsection
