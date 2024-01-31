@extends('admin.layouts.default')

@section('title', 'Media Locker')

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
            <h4 class="text-dark font-weight-bold mt-2 mb-2 mr-5">Manage VineConnect Admin Media Locker</h4>
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
                    <div class="card-header flex-column">
                        <h5 class="card-title mt-7">Add Media Objects</h5>
						<p><b>Instructions:</b> Add media object to your locker here. Select <b>Add New</b> and put in the media object details to save it to your locker for future usage.</p>
					</div>
                    <div class="card-body">
                        @if( session()->has('success') )
                            <div class="alert alert-primary" role="alert">
                                {{ session()->get('success') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif
                        @if( session()->has('error') )
                            <div class="alert alert-danger" role="alert">
                                {{ session()->get('error') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif
                        @error('media_code')
                            <div class="alert alert-danger" id="code-error">{{ $message }}</div>
                        @enderror
                        @error('file')
                            <div class="alert alert-danger" id="file-error">{{ $message }}</div>
                        @enderror
                        <div class="mb-7">
                            <div class="row align-items-center">
                                <div class="col-lg-3 col-xl-4">
                                    <button class="btn btn-success float-left" id="add_media">Add New</button>
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
                                   <th title="Field #1">ID</th>
                                   <th  title="Field #2">Code</th>
                                   <th  title="Field #3">URL</th>
                                   <th  title="Field #4">Created At</th>
                                   <th  title="Field #5">Actions</th>
                               </tr>
                           </thead>
                           <tbody>
                               @foreach($media as  $key => $med)
                               <tr>
                                   <td>{{ $med->id }}</td>
                                   <td>
                                        <div style="display:flex;flex-direction:row;align-items:center;">
                                            <pre style="font-size: inherit;padding: 0px;margin: 0px;font-family: inherit;font-weight: inherit;">&lt;MEDIA&gt;{{ $med->media_code }}&lt;/MEDIA&gt;</pre>
                                            <a
                                                class="btn btn-grey copy-button"
                                                onclick="copyMediaCode(this,'<MEDIA>{{ $med->media_code }}</MEDIA>')"
                                                data-toggle="tooltip" data-placement="top"
                                                title="Copy Media Code">
                                                <span class="fa far fa-copy"></span>
                                            </a>
                                        </div>
                                    </td>
                                   <td><a href="{{ $med->media_url }}" target="_blank">Media Link</a></td>
                                   <td>{{ $med->created_at }}</td>
                                   <td style="">
                                    <div>
                                            <button role="button" type="button" data-id="{{ $med->id }}" data-code="{{ $med->media_code }}" class="btn btn-primary edit_media"><span class="fa fa-edit"></span></button>
                                            <button role="button" type="button" data-id="{{ $med->id }}" class="btn btn-danger delete_media"><span class="fa fa-trash"></span></button>
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
          <h5 class="modal-title">Save Media Object</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <form id="addNewForm" action="{{ route('media_locker.save', ['subdomain' => $subdomain]) }}" method="post" enctype="multipart/form-data">
                @method("POST")
                @csrf
                <div class="row">
                    <div class="form-group col-md-12">
                        <label><b>Shortcode:</b></label>
                        <input class="form-control" name="media_code" required="required" id="media_code">
                        <input type="hidden" name="edit_id" value="" id="edit_id">
                    </div>
                </div>
                <div class="row">
                <div class="form-group col-md-12">
                        <label><b>Media File:</b></label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" name="file" id="media_file" />
                            <label class="custom-file-label" for="media_file"><span style="margin-right: 73px;">Select Media File<span></label>
                        </div>
                    </div>
                </div>
                <div class="text-right mt-5">
                    <button class="btn btn-success">Save</button>
                </div>
            </form>
        </div>
      </div>
    </div>
  </div>
@endsection
@section('scripts')
<script>
    $("body").on("click", '#add_media', function(){
        $('#edit_id').val('');
        $('#media_code').val('');
        $('#addNew').modal('show');
    });

    $("body").on("click", '.edit_media', function(){
        let media_id = $(this).attr("data-id");
        let media_code = $(this).attr("data-code");
        $('#edit_id').val(media_id);
        $('#media_code').val(media_code);
        $('#addNew').modal('show');
    });

    $("body").on("click", '.delete_media', function(){
        let media_id = $(this).attr("data-id");
        Swal.fire({
            title: 'Are you sure?',
            showDenyButton: true,
            showCancelButton: false,
            confirmButtonText: 'Delete',
            denyButtonText: `Cancel`,
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "media_locker/delete?media_id="+media_id,
                    success: function(res){
                        if(res.success) {
                            window.location.reload();
                        }
                        else {
                            Swal.fire({
                                title: res.message,
                                showDenyButton: false,
                                showCancelButton: false,
                                confirmButtonText: 'Ok'
                            });               
                        }
                    }
                });
            }
        });
    });

    function copyMediaCode(element, text) {
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val(text).select();
        document.execCommand("copy");
        $temp.remove();
        $('.copy-button').attr('title', 'Copy')
            .tooltip('_fixTitle');
        $(element).attr('title', 'Copied')
            .tooltip('_fixTitle')
            .tooltip('show');
    }
</script>
@endsection
