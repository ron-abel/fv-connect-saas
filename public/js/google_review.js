function activate_communication(value, action_name) {
    let set_value = value;
    set_value = set_value.replace("_", " ");
    if(set_value == 'on' || set_value == 'off'){
        var swal_title = 'Are you sure you want to turn this "' + set_value.toUpperCase() + '"?';
    } else if (set_value == 'pause'){
        var swal_title = 'Are you sure you want to "TEST"?';
    } else {
        var swal_title = 'Are you sure you want to "' + set_value.toUpperCase() + '"?';
    }
    Swal.fire({
        title: swal_title,
        showDenyButton: true,
        confirmButtonText: 'Yes',
        denyButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            if (action_name === 'google_review_is_on') {
                if (value === 'off') {
                    $("#google_review_submit").hide();
                } else {
                    $("#google_review_submit").show();
                }
            } else if (action_name === 'is_on') {
                if (value === 'off') {
                    $("#phase_change_submit").hide();
                } else {
                    $("#phase_change_submit").show();
                }
            }

            if (action_name === 'google_review_is_on' && value === 'on') {
                $('#pause_google_review_btn').attr('checked', 'checked');

            } else if (action_name === 'is_on' && value === 'on') {
                $('#pause_btn').prop('checked', true);
            }

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            var formData = {
                action_name: action_name,
                value: value
            };

            $.ajax({
                type: "post",
                url: "update_auto_notes_occurence",
                data: formData,
                success: function (res) {
                    console.log(res);
                    if (action_name === 'google_review_is_on' && value === 'on') {
                        activate_communication('pause', 'google_review_is_live');
                    } else if (action_name === 'is_on' && value === 'on') {
                        activate_communication('pause', 'is_live');
                    }
                },
            });
        } else {
            if (value === 'off') {
                $('#off_google_review_btn').prop('checked', false);
                $('#on_google_review_btn').prop('checked', true);
            } else if (value === 'on') {
                $('#on_google_review_btn').prop('checked', false);
                $('#off_google_review_btn').prop('checked', true);
            } else if (value === 'pause') {
                $('#pause_google_review_btn').prop('checked', false);
                $('#go_live_google_review_btn').prop('checked', true);
            } else if (value === 'go_live') {
                $('#go_live_google_review_btn').prop('checked', false);
                $('#pause_google_review_btn').prop('checked', true);
            }
        }
    });
}

function addDynamicRow() {

    var is_live = $("input[name='automate_connection_live']:checked").val();

    if (is_live == 'go_live') {
        Swal.fire({
            text: "To make changes, click the PAUSE button to temporarily pause notifications while you work",
            icon: "error",
        });
        return false;
    }

    var id = Math.random();

    html = `<div class="row ml-3 mr-0 mt-3" id="id_webhook_` + id + `">
                <div class="col-sm-12 px-1">
                    <div class="row mx-0 phase_form">
                        <div class="col-sm-2 px-1 custom-input">
                            <label for="">If Phase Change</label>
                            <select name="phase_change_type" id="" value="" class="form-control phase_form_select" data-id="phase_form">
                            <option value="Equals Exactly">Equals Exactly</option>
                            </select>
                        </div>
                        <div class="col-sm-2 px-1 ml-5 custom-input">
                            <label for="">Phase Change</label>
                            ` + phaseHtml + `
                            <input type="hidden" name="phase_change_event" value="" class="form-control phase_change_event">
                        </div>
                        <div class="col-sm-2 px-1 ml-5 custom-input">
                            <label for="">Enable/Disable</label>
                            <select name="phase_change_enable" id="" class="form-control phase_change_enable">
                                <option value="1">Enable</option>
                                <option value="0">Disable</option>
                            </select>
                        </div>
                        <div class="col-sm-3 px-1 ml-5">
                            <label for="">Text Message</label>
                            <textarea class="form-control" name="custom_message" id="txtDescription_0" cols="60" rows="5" required="" spellcheck="true">Hello [client_firstname], your case with [law_firm_name] has an update! Log into our Client Portal to review: [client_portal_url]</textarea>
                        </div>
                        <div class="col-sm-1 px-1 ml-5">
                            <label for="">Review? <span class="fas fa-exclamation-circle" data-toggle="tooltip" title="" data-original-title="Check to send Google Review on this Phase Change."></span></label>
                            <input type="checkbox" class="form-control goog-check" name="is_send_google_review">
                        </div>
                        <div class="col-sm-1 px-1 mt-6 ml-5">
                        <button type="submit" class="btn ml-auto mt-1 btn-success btn-md save new-row-btn"  data-id="` + id + `" style="float:left;">Save</button>
                        </div>
                        <div class="process-result col-sm-3 px-1 mt-10"></div>
                    </div>
                </div>
            </div>`;

    $('#id_new_webhook_row').append(html);
}

function add_or_dynamic_row(obj) {

    var is_live = $("input[name='automate_google_review_live']:checked").val();

    if (is_live == 'go_live') {
        Swal.fire({
            text: "To make changes, click the PAUSE button to temporarily pause notifications while you work",
            icon: "error",
        });
        return false;
    }

    var google_review_link_id = $(obj).attr('data-id');

    var id = Math.random();

    html = `<div class="form-group mt-10 phase_city_sec">
                <div class="row mx-0 phase_form">
                    <div class="col-sm-7"></div>
                    <div class="col-sm-2">
                        <input name="zip_code" value="" class="form-control" required>
                    </div>
                    <div class="col-sm-1">
                    </div>
                    <div class="col-sm-1 or_btn">
                        <button class="btn ml-auto mt-1 btn-success btn-md google_review_or" data-id="` + google_review_link_id + `" onclick="add_or_dynamic_row(this)" style="float:left;">OR</button>
                    </div>
                    <input type="hidden" class="google_review_save new-row-btn" data-google-review-link-id="` + google_review_link_id + `" data-review-link-id="` + google_review_link_id + `" data-id="` + id + `">
                    <div class="process_result_google_review_city col-sm-6"></div>
                </div>
            </div>`;


    $("#append_or_row_" + google_review_link_id).append(html);
}

function addReviewRow() {
    var google_review_is_live = $("input[name='automate_google_review_live']:checked").val();

    if (google_review_is_live == 'go_live') {
        Swal.fire({
            text: "To make changes, click the PAUSE button to temporarily pause notifications while you work",
            icon: "error",
        });
        return false;
    }

    var id = Math.random();

    if ($('.js-review').length === 1) {
        $('.js-review').find('.js-client-zip-code').removeClass('d-none');
        $('.js-review').find('.js-is-default').removeClass('d-none');
        $($('.js-review').find('.js-is-default')[0]).find('input[name="is_default"]').prop('checked', true);
        $('.js-review').find('select[name="handle_type"]').val('Exactly Matches');
        $('.js-review').find('.custom-select-input').removeClass('d-none');
    }

    html = `<div class="row ml-3 mr-0 mt-5 js-review" id="id_review_` + id + `">
                <div class="col-sm-12 px-1">
                    <div class="row mx-0 phase_form">
                        <div class="col-sm-4">
                            <label for="">Review Request Link to Send</label>
                            <input name="review_link" value="" class="form-control" required>
                        </div>
                        <div class="col-sm-3 custom-select-input ` + ($('.js-review').length === 0 ? `d-none` : ``) + `">
                            <label for="">Description</label>
                            <input name="handle_type" value="Exactly Matches" class="form-control phase_form_select" required>
                        </div>
                        <div class="col-sm-2 ` + ($('.js-review').length === 0 ? `d-none` : ``) + ` js-client-zip-code">
                            <label for="">Client Zip Code</label>
                            <input name="zip_code" value="" class="form-control"  ` + ($('.js-review').length === 1 ? `required` : ``) + `>
                        </div>
                        <div class="col-sm-1 ` + ($('.js-review').length === 0 ? `d-none` : ``) + ` js-is-default">
                            <label for="">Default? <span class="fas fa-exclamation-circle" data-toggle="tooltip" title="" data-original-title="If the system doesn’t find a zip code match, this link is sent by default."></span></label>
                            <input type="checkbox" class="form-control goog-check" name="is_default" value="1">
                        </div>
                        <input type="hidden" class="google_review_save new-row-btn" data-id="` + id + `">
                        <div class="col-sm-1 mt-6"></div>
                        <div class="process_result_google_review col-sm-4 px-1 mt-10"></div>
                    </div>
                </div>
            </div>`;

    $('#id_new_review_row').append(html);

    $('[data-toggle="tooltip"]').tooltip();
}



$(document).ready(function (e) {
    if ($('.js-review').length === 1) {
        $('.js-review').find('.custom-select-input').addClass('d-none');
        $('.js-review').find('.google_review_or').parent().addClass('d-none');
    }

    if($('.zip_code_class').length > 1){
        $('.js-review').find('.custom-select-input').removeClass('d-none');
        $('.js-review').find('.google_review_or').parent().removeClass('d-none');
    }

    $(document).on("click", ".save", function (e) {

        e.preventDefault();

        var is_live = $("input[name='automate_connection_live']:checked").val();

        if (is_live == 'go_live') {
            Swal.fire({
                text: "To make changes, click the PAUSE button to temporarily pause notifications while you work!",
                icon: "error",
            });
            return false;
        }

        var id = $(this).attr('data-id');
        var webhook_row = $(this).parent().parent();
        var phase_change_type = $(webhook_row).find('[name="phase_change_type"]').val();
        var phase_change_event = $(webhook_row).find('[name="phase_change_event"]').val();
        var fv_phase_id = $(webhook_row).find('[name="fv_phase_id"]').val();
        var phase_change_enable = $(webhook_row).find('[name="phase_change_enable"]').val();
        var custom_message = $(webhook_row).find('[name="custom_message"]').val();
        var google_review_checked = $(webhook_row).find('[name="is_send_google_review"]').prop('checked');
        var is_send_google_review = 0;

        if (google_review_checked) {
            is_send_google_review = 1;
        }

        var is_new = 0;
        if ($(this).hasClass('new-row-btn')) {
            is_new = 1;
            $(this).prop('disabled', true)
        }
        var _self = $(this);

        var formData = {
            phase_change_enable: phase_change_enable,
            phase_change_type: phase_change_type,
            phase_change_event: phase_change_event,
            fv_phase_id: fv_phase_id,
            custom_message: custom_message,
            id: id,
            is_new: is_new,
            is_send_google_review: is_send_google_review
        };

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type: "post",
            url: "process_auto_notes_phase_settings",
            data: formData,
            success: function (res) {
                res = jQuery.parseJSON(res);
                if (res.success) {
                    //$(webhook_row).parent().find('.process-result').empty().prepend('<p class="awa-text text-success" style="' + style + '"> Looks Good </p>');
                    Swal.fire({
                        text: res.message,
                        icon: "success",
                    });
                    _self.removeClass('new-row-btn')
                    if (res.new_data) {
                        _self.attr('data-id', res.new_data.id)
                        _self.prop('disabled', false)
                    }
                } else {
                    Swal.fire({
                        text: res.message,
                        icon: "error",
                    });
                    //$(webhook_row).parent().find('.process-result').empty().prepend('<p class="awa-text text-danger" style="' + style + '"> Error: ' + res.message + '</p>');
                }

                setTimeout(function () { location.reload(); }, 1000);
            },
        });



        var target = $(this).attr('data-id');
        var type = $('.webhook_action').val();
        var formData = {};
        var classes = 'mt-0 ml-5';
        var style = 'height: 23px;';

        if (type == 'PhaseChanged') {
            var if_phase_change = $('.' + target).find('select[name="if_phase_change"]').val();
            var phase_change = $('.' + target).find('input[name="phase_change"]').val();
            var destination = $('.' + target).find('input[name="destination"]').val();
            formData = { 'if_phase_change': if_phase_change, 'phase_change': phase_change, 'destination': destination, 'type': type };
        } else {
            var destination = $('.' + target).find('input[name="destination"]').val();
            formData = { 'destination': destination, 'type': type };
            classes = 'mt-4';
            style = '';
        }


    });

    $(document).on("click", ".delete", function (e) {
        e.preventDefault();

        var is_live = $("input[name='automate_connection_live']:checked").val();

        if (is_live == 'go_live') {
            Swal.fire({
                text: "To make changes, click the PAUSE button to temporarily pause notifications while you work!",
                icon: "error",
            });
            return false;
        }

        var id = $(this).attr('data-id');
        var webhook_row = $(this).parent().parent();

        var formData = {
            id: id,
            delete_action: "delete_action",
        };

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type: "post",
            url: "process_auto_notes_phase_settings",
            data: formData,
            beforeSend: function () {
                $('.preloader').show();
                $('.preloader').css('background', 'none');
            },
            complete: function () {
                $('.preloader').hide();
                $('.preloader').css('background', '#fff');
            },
            success: function (res) {
                res = jQuery.parseJSON(res);
                if (res.success) {
                    Swal.fire({
                        text: res.message,
                        icon: "success",
                    });
                    //$(webhook_row).parent().find('.process-result').empty().prepend('<p class="awa-text text-success" style="height: 23px;"> Deleted Successfully </p>');
                } else {
                    Swal.fire({
                        text: res.message,
                        icon: "error",
                    });
                    //$(webhook_row).parent().find('.process-result').empty().prepend('<p class="awa-text text-danger" style="height: 23px;">Error: ' + res.message + '</p>');
                }

                setTimeout(function () { location.reload(); }, 1000);
            },
        });
    });

    $(document).on("submit", "#google_review_submit", function (e) {

        e.preventDefault();

        var is_live = $("input[name='automate_google_review_live']:checked").val();

        if (is_live == 'go_live') {
            Swal.fire({
                text: "To make changes, click the PAUSE button to temporarily pause notifications while you work!",
                icon: "error",
            });
            return false;
        }
        var google_review_row = $(this).parent().parent();
        var formData = [];
        var same_zip_code = false;
        $(".phase_form").each(function () {
            var id = $(this).find('.google_review_save').attr('data-id');
            var city_id2 = $(this).find('.google_review_save').attr('data-id');
            var city_id = $(this).find('.google_review_save').attr('data-city-id');
            var google_review_row = $(this).find('.google_review_save').parent().parent();
            var review_link = $(google_review_row).find('[name="review_link"]').val();
            var handle_type = $(google_review_row).find('[name="handle_type"]').val();
            var zip_code = $(google_review_row).find('[name="zip_code"]').val();
            var is_default = $(this).find('[name="is_default"]').is(":checked") ? 1 : 0;

            var google_review_link_id = "";

            var re = /^(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/;
            if (review_link) {
                if (re.test(review_link) == false) {
                    Swal.fire({
                        text: "Google Review Link is invalid.",
                        icon: "error",
                    });
                    return false;
                }
            }

            var google_review_link_validate = $(this).find('.google_review_save').attr('data-google-review-link-id');

            var city_validation = 0;

            $('#google_review_submit').find("input[name='zip_code']").each(function () {
                if (zip_code == this.value) {
                    city_validation++;
                }
            });

            if (city_validation >= 2) {
                Swal.fire({
                    text: zip_code + " already exist",
                    icon: "error",
                });
                same_zip_code = true;
                return false;
            }

            var is_new = 0;
            if ($(this).find('.google_review_save').hasClass('new-row-btn')) {
                is_new = 1;
                google_review_link_id = $(this).find('.google_review_save').attr('data-review-link-id');
                $(this).prop('disabled', true)
            }

            formData.push({
                review_link: review_link,
                handle_type: handle_type,
                zip_code: zip_code,
                is_default: is_default,
                id: id,
                is_new: is_new,
                city_id: city_id,
                city_id2: city_id2,
                google_review_link_id: google_review_link_id
            });
        });
        if (same_zip_code || formData.length <= 0) {
            return false;
        }

        var _self = $(this);

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(".loading").show();
        $.ajax({
            type: "post",
            url: "process_auto_notes_google_review_settings",
            data: { formData: formData },
            success: function (res) {
                console.log('res = ' + res);
                res = jQuery.parseJSON(res);
                if (res.success) {
                    Swal.fire({
                        text: res.message,
                        icon: "success",
                    });
                    //$(google_review_row).parent().find('.process_result_google_review').empty().prepend('<p class="awa-text text-success"> Looks Good </p>');
                    _self.removeClass('new-row-btn')
                    if (res.new_data) {
                        _self.attr('data-id', res.new_data.id)
                        _self.prop('disabled', false)
                    }
                } else {
                    Swal.fire({
                        text: res.message,
                        icon: "error",
                    });
                    //$(google_review_row).parent().find('.process_result_google_review').empty().prepend('<p class="awa-text text-danger"> Error: ' + res.message + '</p>');
                }

                setTimeout(function () { location.reload(); }, 1000);
            },
        }).done(function () {
            $(".loading").hide();
        });
    });

    $(document).on("click", ".google_review_delete", function (e) {
        e.preventDefault();

        var is_live = $("input[name='automate_google_review_live']:checked").val();

        if (is_live == 'go_live') {
            Swal.fire({
                text: "To make changes, click the PAUSE button to temporarily pause notifications while you work!",
                icon: "error",
            });
            return false;
        }

        var id = $(this).attr('data-id');
        var google_review_row = $(this).parent().parent();

        var formData = {
            id: id,
            delete_action: "delete_action",
        };

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        Swal.fire({
            title: 'Are you sure want to delete?',
            showDenyButton: true,
            confirmButtonText: 'Yes',
            denyButtonText: 'No'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "post",
                    url: "process_auto_notes_google_review_settings",
                    data: formData,
                    beforeSend: function () {
                        $('.preloader').show();
                        $('.preloader').css('background', 'none');
                    },
                    complete: function () {
                        $('.preloader').hide();
                        $('.preloader').css('background', '#fff');
                    },
                    success: function (res) {
                        res = jQuery.parseJSON(res);
                        if (res.success) {
                            Swal.fire({
                                text: res.message,
                                icon: "success",
                            });
                        } else {
                            Swal.fire({
                                text: res.message,
                                icon: "error",
                            });
                        }
                        setTimeout(function () { location.reload(); }, 1000);
                    },
                });
            }
        });
    });

    $(document).on("click", ".google_review_city_save", function (e) {

        e.preventDefault();

        var is_live = $("input[name='automate_google_review_live']:checked").val();

        if (is_live == 'go_live') {
            Swal.fire({
                text: "To make changes, click the PAUSE button to temporarily pause notifications while you work!",
                icon: "error",
            });
            return false;
        }

        var google_review_city_row = $(this).parent().parent();
        var formData = [];
        $(".phase_city_sec").each(function () {
            var city_id = $(this).find('.google_review_city_save').attr('data-id');
            var google_review_city_row = $(this).find('.google_review_city_save').parent().parent();
            var zip_code = $(google_review_city_row).find('[name="zip_code"]').val();
            var google_review_link_id = "";
            var google_review_link_validate = $(this).find('.google_review_city_save').attr('data-google-review-link-id');

            var city_validation = 0;

            $('#id_google_review_' + google_review_link_validate).find("input[name='zip_code']").each(function () {
                if (zip_code == this.value) {
                    city_validation++;
                }
            });

            if (city_validation >= 2) {
                Swal.fire({
                    text: zip_code + " already exist",
                    icon: "error",
                });
                return false;
            }

            var is_new = 0;
            if ($(this).find('.google_review_city_save').hasClass('new-row-btn')) {
                is_new = 1;
                google_review_link_id = $(this).find('.google_review_city_save').attr('data-review-link-id');
                $(this).prop('disabled', true)
            }
            formData.push({
                city_id: city_id,
                zip_code: zip_code,
                is_new: is_new,
                google_review_link_id: google_review_link_id
            });
        });

        var _self = $(this);

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type: "post",
            url: "process_auto_notes_google_review_cities",
            data: { formData: formData },
            success: function (res) {
                res = jQuery.parseJSON(res);
                if (res.success) {
                    Swal.fire({
                        text: res.message,
                        icon: "success",
                    });
                    //$(google_review_city_row).parent().find('.process_result_google_review_city').empty().prepend('<p class="awa-text text-success"> Looks Good </p>');
                    _self.removeClass('new-row-btn')
                    if (res.new_data) {
                        _self.attr('data-id', res.new_data.id)
                        _self.prop('disabled', false)
                    }
                } else {
                    Swal.fire({
                        text: res.message,
                        icon: "error",
                    });
                    //$(google_review_city_row).parent().find('.process_result_google_review_city').empty().prepend('<p class="awa-text text-danger"> Error: ' + res.message + '</p>');
                }

                setTimeout(function () { location.reload(); }, 1000);
            },
        });
    });

    $(document).on("click", ".google_review_city_delete", function (e) {
        e.preventDefault();

        var is_live = $("input[name='automate_google_review_live']:checked").val();

        if (is_live == 'go_live') {
            Swal.fire({
                text: "To make changes, click the PAUSE button to temporarily pause notifications while you work!",
                icon: "error",
            });
            return false;
        }

        var city_id = $(this).attr('data-id');
        var google_review_city_row = $(this).parent().parent();

        var formData = {
            city_id: city_id,
            delete_action: "delete_action",
        };

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        Swal.fire({
            title: 'Are you sure want to delete?',
            showDenyButton: true,
            confirmButtonText: 'Yes',
            denyButtonText: 'No'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "post",
                    url: "process_auto_notes_google_review_cities",
                    data: formData,
                    beforeSend: function () {
                        $('.preloader').show();
                        $('.preloader').css('background', 'none');
                    },
                    complete: function () {
                        $('.preloader').hide();
                        $('.preloader').css('background', '#fff');
                    },
                    success: function (res) {
                        res = jQuery.parseJSON(res);
                        if (res.success) {
                            Swal.fire({
                                text: res.message,
                                icon: "success",
                            });
                        } else {
                            Swal.fire({
                                text: res.message,
                                icon: "error",
                            });
                        }
                        setTimeout(function () { location.reload(); }, 1000);
                    },
                });
            }
        });
    });

    // handle display/view of message box
    $(document).on('change', '.phase_change_enable', function () {
        var _elem = $(this);
        var state = _elem.val();
        var _label_elem = _elem.parent().next().find('label')[0];
        var _textarea_elem = _elem.parent().next().find('textarea')[0];
        if (state == 1) {
            $(_textarea_elem).css('display', 'block');
            $(_label_elem).css('display', 'block');
        } else {
            $(_textarea_elem).css('display', 'none');
            $(_label_elem).css('display', 'none');
        }
    });

    //
    $(document).on('change', 'select[name="handle_type"]', function () {
        var _elem = $(this);
        var parentId = $(this).data('id');
        if (_elem.val() === 'Exactly Matches') {
            _elem.closest('.js-review').find('.js-client-zip-code').removeClass('d-none');
        } else {
            _elem.closest('.js-review').find('.js-client-zip-code').addClass('d-none');
        }
    });

    //
    $(document).on('click', 'input[name="is_default"]', function () {
        var _elem = $(this);
        if (_elem.is(":checked")) {
            $('input[name="is_default"]').not(_elem).prop("checked", false);
        }
    });

    if ($("input[name=automate_google_review_status]:checked").val() === 'off') {
        $("#google_review_submit").hide();
    }
    if ($("input[name=automate_connection_status]:checked").val() === 'off') {
        $("#phase_change_submit").hide();
    }


    $(document).on('change', '#send_qualified_response_request', function (e) {
        if (e.target.checked) {
            $("#qualifying_response_request_content").removeClass('d-none');
            $("input[name='minimum_score']").prop('required',true);
        } else {
            $("input[name='minimum_score']").prop('required',false);
            $("#qualifying_response_request_content").addClass('d-none');
        }
    });

    $(document).on('change', '#send_unqualified_response_request', function (e) {
        if (e.target.checked) {
            $("#unqualified_response_request_content").removeClass('d-none');
        } else {
            $("#unqualified_response_request_content").addClass('d-none');
        }
    });


    $(function () {
        var log_start_date = moment();
        var log_end_date = moment();

        function cb(start, end) {
            $('#logreportrangemsg span').html(start.format('MM/D/YYYY') + ' - ' + end.format('MM/D/YYYY'));

            let export_href = $(".export-custom-log").attr("href");
            export_href = export_href.split("?")[0] + '?msg_type=in&log_start_date=' + start.format('YYYY-MM-DD') + '&log_end_date=' + end.format('YYYY-MM-DD');
            $(".export-custom-log").attr("href", export_href);

            $('#kt_datatable_messages_logs').html('');

            $('#kt_datatable_messages_logs').KTDatatable({
                message: true,
                data: {
                    type: 'remote',
                    source: {
                        read: {
                            url: 'review_request_message_log',
                            method: 'get',
                            data: {
                                'log_start_date': start.format('YYYY-MM-DD'),
                                'log_end_date': end.format('YYYY-MM-DD'),
                            },
                            map: function (raw) {
                                var dataSet = raw.data;
                                if (typeof raw.data !== 'undefined') {
                                    dataSet = raw.data;
                                }
                                if (raw.data.length) {
                                    $(".datatable").removeClass("datatable-error");
                                } else {
                                    $(".datatable").addClass("datatable-error");
                                }
                                return dataSet;
                            },
                        },
                    },
                },
                columns: [
                    {
                        title: 'Client Name',
                        field: 'fv_client_name',
                    },
                    {
                        title: 'Client Number',
                        field: 'from_number',
                        template: function (row) {
                            return '+1' + row.from_number;
                        },
                    },
                    {
                        title: 'Message Body',
                        field: 'message_body',
                    },
                    {
                        title: 'Score',
                        field: 'score',
                    },
                    {
                        title: 'Created At',
                        field: 'created_at',
                        template: function (row) {
                            return '<span>' + moment(row.created_at).format('YYYY-MM-DD HH:mm'); + '</span>';
                        },
                    }
                ]
            });
        }

        $('#logreportrangemsg').daterangepicker({
            startDate: log_start_date,
            endDate: log_end_date,
            ranges: {
                'Today': [moment(), moment()],
                'This Week': [moment().startOf('isoWeek'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
            }
        }, cb);
        cb(log_start_date, log_end_date);
    });


    $('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
        sessionStorage.setItem('activeTab-RR', $(e.target).attr('href'));
    });
    var activeTab = sessionStorage.getItem('activeTab-RR');
    if (activeTab) {
        $('#v-pills-tab a[href="' + activeTab + '"]').tab('show');
    }



});
