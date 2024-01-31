@extends('superadmin.layouts.default')

@section('title', 'Tenants')

@section('content')
<div class="main-content container">
    <div class="row mt-6">
        <div class="col-lg-12">
            <!--begin::Card-->
            <div class="card card-custom gutter-b example example-compact">
                <!--begin::Form-->
                    
                    <div class="card-body">
                        <h3>User Details: </h3>
                     @if( session()->has('success') )
                        <div class="alert alert-primary" role="alert">
                            {{ session()->get('success') }}
                        </div>
                        @endif
                        @error('new-password')
                            <div class="alert alert-danger" role="alert">
                                {{$message}}
                            </div>
                        @enderror
                        @error('confirm-password')
                        <div class="alert alert-danger" role="alert">
                            {{$message}}
                        </div>
                       @enderror
                       @error('email')
                           <div class="alert alert-danger" role="alert">
                               {{$message}}
                           </div>
                       @enderror
                        <div class="mb-7">
                            <div class="row align-items-center">
                                <div class="col-lg-9 col-xl-8">
                                    <div class="row align-items-center">
                                        <div class="col-md-4 my-2 my-md-0">
                                            <div class="input-icon">
                                                <input type="text" class="form-control" placeholder="Search..." id="kt_datatable_search_query" />
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
                                   <th title="Field #1">No</th>
                                   <th  title="Field #2">Full Name</th>
                                   <th  title="Field #3">Email</th>
                                   <th  title="Field #4">Reset</th>
                               </tr>
                           </thead>
                           <tbody>
                               @foreach($tenant_details->users as  $ind=>$user)
                               <tr>
                                   <td>{{ $ind+1 }}</td>
                                   <td>{{ $user->full_name }}</td>
                                   <td>{{ $user->email }}</td>
                                   <td><button class="btn btn-success" data-toggle="modal" data-target="#resetPassword" data-id="{{ $user->id }}" data-email="{{ $user->email }}">Reset Password</button></td>
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

{{-- Reset Password Modal --}}
<div class="modal" id="resetPassword" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Reset Password</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="resetForm" action="" method="post">
            @method("POST")
             @csrf
        <div class="row">
            <div class="form-group col-md-12">
                <label><b>Email: </b></label>
                <input class="form-control email col-md-12" name="email">
            </div>
        </div>
            <div class="row">
                <div class="form-group col-md-12">
                    <label><b>New Password: </b></label>
                    <input class="form-control col-md-12" name="new-password" placeholder="Leave empty for no change">
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-12">
                    <label><b>Confirm Password: </b></label>
                    <input class="form-control col-md-12" name="confirm-password">
                </div>
            </div>
          <div class="text-right">
            <button class="btn btn-success">Reset</button>
          </div>
        </form>
        </div>
      </div>
    </div>
  </div>
@endsection
@section('scripts')
<script>
$('#resetPassword').on('show.bs.modal',function(event){
    let button = $(event.relatedTarget)
    let id = button.data('id')
    let email = button.data('email')

    let modal = $(this)
    modal.find('.modal-body form#resetForm').get(0).setAttribute('action',"/admin/user/reset/"+id);
    modal.find('.modal-body input.email').val(email);
  })
</script>
@endsection