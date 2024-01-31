@extends('admin.layouts.default')

@section('title', 'VineConnect Admin - Project Form')

@section('content')
    <!--begin::Subheader-->
    <div class="subheader py-2 py-lg-4 subheader-solid" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
            <!--begin::Info-->
            <div class="d-flex align-items-center flex-wrap mr-2">
                <!--begin::Page Title-->
                <h5 class="text-dark font-weight-bold mt-2 mb-2 mr-5">Project Form view</h5>
                <!--end::Page Title-->
            </div>
            <!--end::Info-->
        </div>
    </div>
    <div class="container">
        <div class="row form-list">
            <div class="col-md-12">
                <div class="card card-custom">
                    <div class="card-header">
                        <h4 class="card-title">{{ $form_name }}</h4>
                    </div>
                    <div class="card-body">
                        <div id="show-form"></div>
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
@stop
@section('scripts')
    <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="https://formbuilder.online/assets/js/form-builder.min.js"></script>
    <script src="https://formbuilder.online/assets/js/form-render.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.26/dist/sweetalert2.all.min.js"></script>
    <script>
        const form_id = "{{ $form_id }}"
        const fRender = document.getElementById("show-form");
        $.ajax({
            type: "GET",
            url: "{{ url('admin/get_tenant_forms') }}",
            success: function(response) {
                res = JSON.parse(response);
                if (res.status == 200) {
                    forms = res.forms;
                    if (form_id) {
                        form = forms.find(form => form.id == form_id);
                        const form_fields = JSON.parse(form.form_fields_json);
                        const submit_btn = {
                            "type": "button",
                            "label": "Submit",
                            "subtype": "submit",
                            "className": "btn-primary btn tenant_submit",
                            "access": false,
                            "style": "primary"
                        }
                        form_fields.push(submit_btn);
                        jQuery(function($) {
                            const formData = JSON.stringify(form_fields);
                            $(fRender).formRender({
                                formData
                            });
                        });
                    }
                }
            },
            error: function(param) {
                Swal.fire({
                    text: "You have no forms. Please add new forms for collecting client response",
                    icon: "info",
                });
            }
        });
        setTimeout(() => {
            $('.tenant_submit').click(function(e) {
                e.preventDefault();
                Swal.fire({
                    text: "You cannot submit the form here.",
                    icon: "warning",
                    confirmButtonColor: "#3b6dfd"
                });
            });
        }, 2000);
    </script>
    <style>
        /* custom form styles */
        .swal2-icon.swal2-warning.swal2-icon-show{
            margin: 10px auto;
        }

        #show-form {
            width: 100%;
            background: #fff;
            border-radius: 20px;
        }

        .rendered-form {
            width: 75%;
            margin: 0 auto;
            padding: 20px 10px
        }

        .form-group.formbuilder-button .btn {
            min-width: 30%;
            line-height: 30px;
            font-size: 18px;
            letter-spacing: 0.7px;
            border-radius: 5px;
            background: #55a9e2
        }

        .form-group .form-control {
            font-size: 16px;
            border: none;
            border-radius: 7px;
            background: #ecf2f6;
            height: 45px !important;
        }

        .form-group label {
            font-size: 14px;
        }

        .rendered-form textarea.form-control {
            min-height: 120px !important;
        }

        .formbuilder-file.form-group .form-control {
            background: transparent;
        }
    </style>
@endsection
