$(document).ready(function () {
    $('.repeater').repeater({
        initEmpty: false,
        show: function () {
            $(this).slideDown();

            $(this).find('input[type="checkbox"]').prop('checked', false);
            $(this).find('input[type="text"]').val('');
            $(this).find('input[type="email"]').val('');

            $(this).find('.btn-fetch').addClass('btn-warning').removeClass('btn-secondary');
            $(this).find('.btn-static').removeClass('btn-warning').addClass('btn-secondary');
            $(this).find('.static_element').addClass('d-none');
            $(this).find('.fetch_element').removeClass('d-none');
            // $(this).find('select').val('');
            $(this).find('.id').val('');
        },
        hide: function (deleteElement) {
            Swal.fire({
                title: 'Are you sure want to delete?',
                showDenyButton: true,
                confirmButtonText: 'Yes',
                denyButtonText: 'No'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.when($(this).slideUp()).then(function () {
                        var id = $(this).find('.id').val();

                        if (id) {
                            $.ajax({
                                url: route_legal_team_destroy,
                                method: 'POST',
                                data: {
                                    '_token': csrf_token,
                                    'id': id
                                },
                                success: function (response) {
                                }
                            })
                        }
                        $(deleteElement).remove();
                    });
                }
            });
        },
        ready: function (setIndexes) {
        },
    });

    $(".table-role tbody").sortable({
        items: "> tr",
        cancel: "input, select, checkbox, a",
        update: function (event, ui) {
            var data = [];
            $.when($(document).find('.repeater').find('.id').each(function (index, item) {
                data.push({
                    id: $(this).val(),
                    index: index,
                })
            })).then(function () {
                $.ajax({
                    url: route_legal_team_sort,
                    method: 'POST',
                    data: {
                        '_token': csrf_token,
                        'data': data
                    },
                    success: function (response) {
                        console.log(response)
                    }
                })
            })
        }
    });
    $(".table-person tbody").sortable({
        items: "> tr",
        cancel: "input, select, checkbox, a",
        update: function (event, ui) {
            var data = [];
            $.when($(document).find('.table-person').find('.id-person').each(function (index, item) {
                data.push({
                    id: $(this).val(),
                    index: index,
                })
            })).then(function () {
                $.ajax({
                    url: route_legal_team_person_sort,
                    method: 'POST',
                    data: {
                        '_token': csrf_token,
                        'data': data
                    },
                    success: function (response) {
                        console.log(response)
                    }
                })
            })
        }
    });
});

//Fetch button click
$(document).on('click', '.btn-fetch-field', function () {
    if ($(this).hasClass("disabled")) {
        return;
    }
    $(this).closest('tr').find('.fetch_element_field').removeClass('d-none');
    $(this).closest('tr').find('.static_element_field').addClass('d-none');
    $(this).addClass('btn-warning').removeClass('btn-secondary');
    $(this).closest('tr').find('.btn-static-field').removeClass('btn-warning').addClass('btn-secondary');
    $(this).closest('tr').find('.typeField').val('fetch');
});

$(document).on('click', '.btn-static-field', function () {
    if ($(this).hasClass("disabled")) {
        return;
    }
    $(this).closest('tr').find('.fetch_element_field').addClass('d-none');
    $(this).closest('tr').find('.static_element_field').removeClass('d-none');
    $(this).addClass('btn-warning').removeClass('btn-secondary');
    $(this).closest('tr').find('.btn-fetch-field').removeClass('btn-warning').addClass('btn-secondary');
    $(this).closest('tr').find('.typeField').val('static');
});

//Fetch button click
$(document).on('click', '.btn-fetch', function () {
    $(this).closest('tr').find('.fetch_element').removeClass('d-none');
    $(this).closest('tr').find('.static_element').addClass('d-none');
    $(this).addClass('btn-warning').removeClass('btn-secondary');
    $(this).closest('tr').find('.btn-static').removeClass('btn-warning').addClass('btn-secondary');
});

$(document).on('click', '.btn-static', function () {
    $(this).closest('tr').find('.fetch_element').addClass('d-none');
    $(this).closest('tr').find('.static_element').removeClass('d-none');
    $(this).addClass('btn-warning').removeClass('btn-secondary');
    $(this).closest('tr').find('.btn-fetch').removeClass('btn-warning').addClass('btn-secondary');
});

//on save button click
$(document).on('click', '.btn-save', function () {
    var tr = $(this).closest('tr');
    var btn = $(this);
    var error = 0;

    if (tr.find('.btn-fetch').hasClass('btn-warning')) {
        if (!tr.find('.role').val()) {
            error++;
            tr.find('.role').addClass('border-danger')
        } else {
            tr.find('.role').removeClass('border-danger')
        }
        var roleTitle = tr.find('.role option:selected').text();
    } else {
        if (!tr.find('.role_title').val()) {
            error++;
            tr.find('.role_title').addClass('border-danger')
        } else {
            tr.find('.role_title').removeClass('border-danger')
        }
        var roleTitle = tr.find('.role_title').val();
    }

    if (error == 0) {
        var fetchType = tr.find('.btn-fetch').hasClass('btn-warning') ? LegalteamConfig_type_fetch : LegalteamConfig_type_static;
        var name = tr.find('.role_name').val();

        if (fetchType == LegalteamConfig_type_static) {
            if (name == '') {
                Swal.fire({
                    text: "Name is required",
                    icon: "error",
                });
                return false;
            }
        }

        var data = {
            '_token': csrf_token,
            'id': tr.find('.id').val(),
            'type': fetchType,
            'role': tr.find('.role').val(),
            'role_title': roleTitle,
            'role_name': tr.find('.role_name').val(),
            'phone': tr.find('.phone').val(),
            'email': tr.find('.email').val(),
            'follower_required': tr.find('.follower_required').is(':checked') ? LegalteamConfig_yes : LegalteamConfig_no,
            'enable_email': tr.find('.enable_email').is(':checked') ? LegalteamConfig_yes : LegalteamConfig_no,
            'enable_feedback': tr.find('.enable_feedback').is(':checked') ? LegalteamConfig_yes : LegalteamConfig_no,
        };

        $.ajax({
            url: route_legal_team_store,
            method: 'POST',
            data: data,
            beforeSend: function () {
                btn.addClass('kt-spinner kt-spinner--right kt-spinner--sm kt-spinner--light');
            },
            success: function (response) {
                if (response.status === true) {
                    if (response.legalteamConfig_id > 0) {
                        tr.find('.id').val(response.legalteamConfig_id);
                    }
                    Swal.fire({
                        text: response.message,
                        icon: "success",
                    }).then(function () {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        text: response.message,
                        icon: "error",
                    });
                }
            },
            complete: function () {
                btn.removeClass('kt-spinner kt-spinner--right kt-spinner--sm kt-spinner--light');
            }
        });
    }
});
$(document).on('click', '.save_all', function () {
        var formData = [];
        var error = 0;
        $(".table_row").each(function () {
          var tr = $(this);
          var btn = $(this);
          if (tr.find('.btn-fetch').hasClass('btn-warning')) {
              if (!tr.find('.role').val()) {
                  error++;
                  tr.find('.role').addClass('border-danger')
              } else {
                  tr.find('.role').removeClass('border-danger')
              }
              var roleTitle = tr.find('.role option:selected').text();
          } else {
              if (!tr.find('.role_title').val()) {
                  error++;
                  tr.find('.role_title').addClass('border-danger')
              } else {
                  tr.find('.role_title').removeClass('border-danger')
              }
              var roleTitle = tr.find('.role_title').val();
          }
          if (error == 0) {
              var fetchType = tr.find('.btn-fetch').hasClass('btn-warning') ? LegalteamConfig_type_fetch : LegalteamConfig_type_static;
              var name = tr.find('.role_name').val();

              if (fetchType == LegalteamConfig_type_static) {
                  if (name == '') {
                      Swal.fire({
                          text: "Name is required",
                          icon: "error",
                      });
                      return false;
                  }
              }
            id =   tr.find('.id').val();
            type = fetchType;
            role = tr.find('.role').val();
            role_title = roleTitle;
            role_name = tr.find('.role_name').val();
            phone = tr.find('.phone').val();
            email = tr.find('.email').val();
            follower_required = tr.find('.follower_required').is(':checked') ? LegalteamConfig_yes : LegalteamConfig_no;
            enable_email = tr.find('.enable_email').is(':checked') ? LegalteamConfig_yes : LegalteamConfig_no;
            enable_feedback =tr.find('.enable_feedback').is(':checked') ? LegalteamConfig_yes : LegalteamConfig_no;
            formData.push({ 'id': id, 'type': type, 'role': role, 'role_title': role_title, 'role_name': role_name, 'phone': phone, "email": email, 'follower_required': follower_required, 'enable_email': enable_email,'enable_feedback':enable_feedback });
      }else{
        return false;
      }
    });
    if (error == 0) {
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
      });
      $.ajax({
          url: route_legal_team_all_store,
          method: 'POST',
          data: {
              'title': $('#legalTeamTitle').val(),
              'is_legal_team_by_roles': $("#config_role").val(),
              'formData': formData
          },
          beforeSend: function () {
              // btn.addClass('kt-spinner kt-spinner--right kt-spinner--sm kt-spinner--light');
          },
          success: function (response) {
              if (response.status === true) {

                  Swal.fire({
                      text: response.message,
                      icon: "success",
                  }).then(function () {
                      location.reload();
                  });
              } else {
                  Swal.fire({
                      text: response.message,
                      icon: "error",
                  });
              }
          },
          complete: function () {
              // btn.removeClass('kt-spinner kt-spinner--right kt-spinner--sm kt-spinner--light');
          }
      });
    }
});

$(document).on('click', '.person_save_all', async function () {
  var formData = [];
  var error = 0;
    await $(".table_person").each(function () {
        var tr = $(this);
        let name = $(this).find(".role_name").val();
        let email = $(this).find(".email").val();
        let phone = $(this).find(".phone").val();
        var fetchType = tr.find('.btn-fetch-field').hasClass('btn-warning') ? LegalteamConfig_type_fetch : LegalteamConfig_type_static;
        if (fetchType == LegalteamConfig_type_static) {
            var nameMsg = validateName(name);
            if (nameMsg != '') {
                Swal.fire({
                    text: nameMsg,
                    icon: "error",
                });
                return false;
            }
        }
        $(".loading").show();
        let tenant_id = $(this).find('.save-config-person').attr("data-id");
        let fv_project_type_id = $(this).find('.save-config-person').attr("fv_project_type_id");
        let fv_section_id = $(this).find('.save-config-person').attr("fv_section_id");
        let fv_person_field_id = $(this).find('.save-config-person').attr("fv_person_field_id");
        let is_static_name = $(this).find(".is_static_name").val();
        let is_enable_feedback = $(this).find(".is_enable_feedback").val();
        let is_enable_email = $(this).find(".is_enable_email").val();
        let is_enable_phone = $(this).find(".is_enable_phone").val();
        let is_override_phone = $(this).find(".is_override_phone").val();
        let is_override_email = $(this).find(".is_override_email").val();
        let override_phone = $(this).find(".override_phone").val();
        let override_email = $(this).find(".override_email").val();
        let override_name = $(this).find(".override_name").val();


        if (!$(this).find(".is_static_name").is(":checked")) {
            is_static_name = 0;
        }
        if (!$(this).find(".is_enable_feedback").is(":checked")) {
            is_enable_feedback = 0;
        }
        if (!$(this).find(".is_enable_phone").is(":checked")) {
            is_enable_phone = 0;
        }
        if (!$(this).find(".is_enable_email").is(":checked")) {
            is_enable_email = 0;
        }
        if (!$(this).find(".is_override_phone").is(":checked")) {
            is_override_phone = 0;
        }
        if (!$(this).find(".is_override_email").is(":checked")) {
            is_override_email = 0;
        }
        if (fetchType == LegalteamConfig_type_static) {
            override_phone = phone;
            override_email = email;
            override_name = name;
        }
        var formdata = {
            tenant_id: tenant_id,
            fv_project_type_id: fv_project_type_id,
            fv_section_id: fv_section_id,
            fv_person_field_id: fv_person_field_id,
            is_static_name: is_static_name,
            is_enable_feedback: is_enable_feedback,
            is_enable_phone: is_enable_phone,
            is_enable_email: is_enable_email,
            is_override_phone: is_override_phone,
            is_override_email: is_override_email,
            override_phone: override_phone,
            override_email: override_email,
            override_name: override_name,
            fetchType: 'person-fields'
        };
        formData.push({
            'tenant_id': tenant_id, 'fv_project_type_id': fv_project_type_id,
            'fv_section_id': fv_section_id, 'fv_person_field_id': fv_person_field_id,
            'is_static_name': is_static_name, 'is_enable_feedback': is_enable_feedback,
            "is_enable_phone": is_enable_phone, 'is_enable_email': is_enable_email,
            'is_override_phone': is_override_phone, 'is_override_email': is_override_email,
            'override_phone': override_phone, 'override_email': override_email,
            'override_name': override_name, 'fetchType': fetchType,
        });
    });

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        url: update_all_legalteam_config,
        type: "POST",
        data: {
            'title': $('#legalTeamTitle').val(),
            'formData': formData
        },
        success: function(json){
            $(".loading").hide();
            Swal.fire({
                text: "Setting saved successfully!",
                icon: "success",
            });
        }
    });
});
function validateName(name) {
    var msg = '';
    if (name == undefined || name == "") {
        msg = 'Please enter Name';
    }
    return msg;
}

function validateEmailCustm(email) {
    var msg = '';
    var mailFormat = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;

    if (email == undefined || email == "") {
        msg = 'Please enter Email Id';
    } else if (!mailFormat.test(email)) {
        msg = 'Email is not valid, Please enter a valid Email';
    }
    return msg;
}

function validatePhoneCustm(phone) {
    var msg = '';
    var phoneFormat = /^[0-9\-\(\)\+\s]+$/;

    if (phone == undefined || phone == "") {
        msg = 'Please enter phone';
    } else if (!phone.match(phoneFormat)) {
        msg = 'Phone is not valid, Please provide a valid Phone';
    }
    return msg;
}
