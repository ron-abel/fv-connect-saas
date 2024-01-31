var phase_html = `<div class="row mx-0 phase_form">
                    <div class="col-sm-2 px-1">
                        <label for="">If Phase Change</label>
                        <select name="item_change_type" id="" class="form-control phase_form_select" data-id="phase_form">
                            <option value="Equals Exactly">Equals Exactly</option>
                        </select>
                    </div>
                    <div class="col-sm-3 px-1">
                        <label for="">Phase Changed To</label>
                        `+ phaseHtml + `
                        <input type="hidden" class="form-control phase_change_event" name="phase_change_event" />
                    </div>
                    <div class="col-sm-4 px-1">
                        <label for="">Deliver to Webhook URL Endpoint </label>
                        <input type="text" class="form-control" name="delivery_hook_url" />
                    </div>
                `;
var collection_html = `<div class="row mx-0 phase_form">
                <div class="col-sm-2 px-1">
                    <label for="">If Collection</label>
                    <select name="item_change_type" id="" class="form-control phase_form_select" data-id="phase_form">
                        <option value="Equals Exactly">Equals Exactly</option>
                    </select>
                </div>
                <div class="col-sm-3 px-1">
                    <label for="">Collection Item Created To</label>
                    <input type="text" class="form-control" name="collection_changed" />
                </div>
                <div class="col-sm-4 px-1">
                    <label for="">Deliver to Webhook URL Endpoint </label>
                    <input type="text" class="form-control" name="delivery_hook_url" />
                </div>
            `;
var task_html = `<div class="row mx-0 phase_form">
            <div class="col-sm-2 px-1">
                <label for="">If Task</label>
                <select name="item_change_type" id="" class="form-control phase_form_select" data-id="phase_form">
                    <option value="Contains">Contains</option>
                </select>
            </div>
            <div class="col-sm-3 px-1">
                <label for="">Task Created To</label>
                <input type="text" class="form-control" name="task_changed" />
            </div>
            <div class="col-sm-4 px-1">
                <label for="">Deliver to Webhook URL Endpoint </label>
                <input type="text" class="form-control" name="delivery_hook_url" />
            </div>
        `;

var other_html = `<div class="row mx-0 form-group">
                    <div class="col-sm-6 others_form px-1">
                        <label for="">Destination Webhook URL</label>
                        <input type="text" name="delivery_hook_url" class="form-control" />
                    </div>
                `;


$('#id_sel_webhook_action').on('change', function () {
    fetchData($(this).val())
})

// add a new webhook html
function addDynamicRow() {
    // validate the trigger action.
    var trigger = $('#id_sel_webhook_action').val();
    if (trigger == "") {
        Swal.fire({
            text: "Invalid Trigger Action!",
            icon: "error",
        });
        return;
    }

    var id = Math.random();

    var html = `<div class="row ml-3 mr-0 mt-3" id="id_webhook_` + id + `">
                <div class="col-sm-12 px-1">`;

    if (trigger == 'PhaseChanged') {
        html = html + phase_html;
    } else if (trigger == 'CollectionItemCreated') {
        html = html + collection_html;
    } else if (trigger == 'TaskCreated') {
        html = html + task_html;
    } else {
        html = html + other_html;
    }

    html = html +
        `       <div class="col-sm-1 px-1 mt-6">
                    <button type="submit" class="btn ml-auto mt-1 btn-success btn-md save new-row-btn"  data-id="`+ id + `" style="float:left;">Save</button>
                </div>
				<div class="process-result col-sm-3 px-1 mt-10"></div>
            </div>

            </div>
        </div>`;

    $('#id_new_webhook_row').append(html);
    $('#id_new_webhook_row').addClass(trigger);
    showTabInfo(trigger);

}

// show the selected trigger action webhooks.
function showTabInfo(trigger_action) {
    $('#id_sel_webhook_action').val(trigger_action);
    var webhook_inbound_url = webhook_inbound_urls[trigger_action];
    $('#id_webhook_inbound_url').val(webhook_inbound_url);

    // show only the trigger action rows.
    $('.dv-webhook-row').hide();
    $('.dv-webhook-row.' + trigger_action).show();

    // show the webhook confirmed label.
    if (webhook_inbound_urls_confirmed[trigger_action] != undefined && webhook_inbound_urls_confirmed[trigger_action] == 1) {
        hideSubscriptionNotification();

        if (webhook_settings_count_parse[trigger_action] == 1) {
            $('#is_confirmed_webhook').show();
        }

    } else {
        hideSubscriptionNotification();
        if (webhook_settings_count_parse[trigger_action] == 1) {
            $('#is_not_confirmed_webhook').show();
        }
    }
    if ($('.' + trigger_action).length == 0) {
        hideSubscriptionNotification();
    }


    // show the create new button.
    if (trigger_action == 'PhaseChanged' || trigger_action == 'CollectionItemCreated' || trigger_action == 'TaskCreated') {
        $('#id_dv_create_new').show();
    } else {
        // check if the webhook setting exist.
        if ($('.' + trigger_action).length == 0) {
            $('#id_dv_create_new').show();
        } else {
            $('#id_dv_create_new').hide();
        }

    }
}

// hide the subscription notifications.
function hideSubscriptionNotification() {
    $('#is_confirmed_webhook').hide();
    $('#is_not_confirmed_webhook').hide();
    $('#is_created_webhook').hide();
}

$(document).ready(function (e) {
    // select the phaseChanged as the default of the trigger action.
    showTabInfo("PhaseChanged");

    // change the trigger action type.
    $(document).on("change", ".webhook_action", function () {
        var trigger_action = $(this).val();
        if (trigger_action == "") {
          Swal.fire({
              text: "Invalid Trigger Actio!",
              icon: "error",
          });
          return;
        }

        // hide the new row info.
        $('#id_new_webhook_row').html("");
        for (var i = 0; i < triggerActions.length; i++) {
            $('#id_new_webhook_row').removeClass(triggerActions[i]);
        }


        showTabInfo(trigger_action);
    });

    // $(document).on("change", ".phase_form_select", function () {
    //     var selection = $(this).val();
    //     var type = $('.webhook_action').val();
    //     var target = $(this).attr('data-id');
    //     getWebhookDetail(type, target, selection);
    // });


    // save the row info.
    $(document).on("click", ".save", function (e) {
        e.preventDefault();
        var id = $(this).attr('data-id');
        var webhook_row = $(this).parent().parent();
        var trigger_action = $('#id_sel_webhook_action').val();
        var delivery_hook_url = $(webhook_row).find('[name="delivery_hook_url"]').val();

        var re = /^(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/;

        if (re.test(delivery_hook_url) == false) {
          Swal.fire({
              text: "Delivery Webhook URL is invalid!",
              icon: "error",
          });
            return "die";
        }

        var item_change_type = "";
        var phase_change_event = "";
        var collection_changed = "";
        var task_changed = "";
        var fv_phase_id = "";
        if (trigger_action == 'PhaseChanged' || trigger_action == 'CollectionItemCreated' || trigger_action == 'TaskCreated') {
            item_change_type = $(webhook_row).find('[name="item_change_type"]').val();
            if (trigger_action == 'PhaseChanged') {
                phase_change_event = $(webhook_row).find('[name="phase_change_event"]').val();
                fv_phase_id = $(webhook_row).find('[name="fv_phase_id"]').val();
            } else if (trigger_action == 'CollectionItemCreated') {
                collection_changed = $(webhook_row).find('[name="collection_changed"]').val();
            } else if (trigger_action == 'TaskCreated') {
                task_changed = $(webhook_row).find('[name="task_changed"]').val();
            }
        }

        var is_new = 0;
        if ($(this).hasClass('new-row-btn')) {
            is_new = 1;
            $(this).prop('disabled', true)
        }
        var _self = $(this);

        var formData = {
            trigger_action: trigger_action,
            delivery_hook_url: delivery_hook_url,
            item_change_type: item_change_type,
            phase_change_event: phase_change_event,
            collection_changed: collection_changed,
            task_changed: task_changed,
            fv_phase_id: fv_phase_id,
            id: id,
            is_new: is_new,
        };

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type: "post",
            url: "process_webhook",
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
                    // $(webhook_row).parent().find('.process-result').empty().prepend('<p class="awa-text text-success" style="' + style + '"> Looks Good </p>');
                    // _self.removeClass('new-row-btn')
                    if (res.new_data) {
                        _self.attr('data-id', res.new_data.id)
                        _self.prop('disabled', false)
                    }
                }
                else {
                  Swal.fire({
                      text: res.message,
                      icon: "error",
                  });
                    // $(webhook_row).parent().find('.process-result').empty().prepend('<p class="awa-text text-danger" style="' + style + '"> Error: ' + res.message + '</p>');
                }

                // update webhook_settings_count_parse
                webhook_settings_count_parse[trigger_action] = 1;

                // check if the new subscription was created.
                if (res.is_created_fv_subscription == 1) {
                    // show the alert message.
                    hideSubscriptionNotification();
                    $('#is_created_webhook').show();
                } else {
                    showTabInfo(trigger_action);
                }
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
        }
        else {
            var destination = $('.' + target).find('input[name="destination"]').val();
            formData = { 'destination': destination, 'type': type };
            classes = 'mt-4';
            style = '';
        }


    });

    $(document).on("click", ".delete", function (e) {
        e.preventDefault();
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

        Swal.fire({
            title: 'Are you sure want to delete?',
            showDenyButton: true,
            confirmButtonText: 'Yes',
            denyButtonText: 'No'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "post",
                    url: "process_webhook",
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
                            // $(webhook_row).parent().find('.process-result').empty().prepend('<p class="awa-text text-success" style="height: 23px;"> Deleted Successfully </p>');
                        }
                        else {
                          Swal.fire({
                              text: res.message,
                              icon: "error",
                          });
                            // $(webhook_row).parent().find('.process-result').empty().prepend('<p class="awa-text text-danger" style="height: 23px;">Error: ' + res.message + '</p>');
                        }

                        setTimeout(function () { location.reload(); }, 1000);
                    },
                });
            }
        });
    });

});



function addDynamicHtml(val) {
    if (val == 'PhaseChanged') {
        $('#others').css('display', 'none');
        $('#phase_changed').css('display', 'block');
        $('#extra_rows').css('display', 'block');
        $('#phase_changed').html(phase_html);
    }
    else if (val == 'ContactCreated' || val == 'ProjectCreated') {
        $('#phase_changed').css('display', 'none');
        $('#extra_rows').css('display', 'none');
        $('#others').css('display', 'block');
        $('#others').html(other_html);
        var webhook_url = $('#webhook_url').val();
        if (val == 'ContactCreated') {
            webhook_url += 'contact_created.php'
        }
        else {
            webhook_url += 'project_created.php'
        }
        $('.webhook_url').text(webhook_url);
    }
}

function getWebhookDetail(type, target, phase = '') {
    $('.' + target).find('input[name="phase_change"]').val('');
    $('.' + target).find('input[name="destination"]').val('');

    var formData = { 'phase': phase, 'type': type, 'fetch': 1 };

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.ajax({
        type: "post",
        url: "process_webhook",
        data: formData,
        success: function (res) {
            res = jQuery.parseJSON(res);
            if (res.success) {
                $('.' + target).find('input[name="phase_change"]').val(res.data.phase_change);
                $('.' + target).find('input[name="destination"]').val(res.data.destination);
            }
        },
    });
}

function fetchData(value) {
    var main_section = $('#main-section')
    $.ajax({
        type: "get",
        url: "/admin/webhooks/fetch-data/" + value,
        success: function (res) {
            if (res.success) {
                main_section.html(res.html);
            }
        }
    });
}
