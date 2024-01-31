$(function () {
    "use strict";

    $(".preloader").fadeOut();
    // this is for close icon when navigation open in mobile view
    $(".nav-toggler").on('click', function () {
        $("#main-wrapper").toggleClass("show-sidebar");
        $(".nav-toggler i").toggleClass("ti-menu");
    });
    $(".search-box a, .search-box .app-search .srh-btn").on('click', function () {
        $(".app-search").toggle(200);
        $(".app-search input").focus();
    });

    // ==============================================================
    // Resize all elements
    // ==============================================================
    $("body, .page-wrapper").trigger("resize");
    $(".page-wrapper").delay(20).show();

    //****************************
    /* This is for the mini-sidebar if width is less then 1170*/
    //****************************
    var setsidebartype = function () {
        var width = (window.innerWidth > 0) ? window.innerWidth : this.screen.width;
        if (width < 1170) {
            $("#main-wrapper").attr("data-sidebartype", "mini-sidebar");
        } else {
            $("#main-wrapper").attr("data-sidebartype", "full");
        }
    };
    $(window).ready(setsidebartype);
    $(window).on("resize", setsidebartype);

    $('#tenant_name').keypress(function () {
        var value = String.fromCharCode(event.which);
        var pattern = new RegExp(/^[a-z]+$/);
        return pattern.test(value);
    });

    $('body').on('click', '#delete_tenant', function () {
        if (confirm("Are you sure want to delete?")) {
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            var route_url = $(this).attr('data-url');

            $.ajax({
                url: route_url,
                type: 'POST',
                data: { '_token': CSRF_TOKEN },
                dataType: 'JSON',
                success: function (data) {
                    window.location.href = data.tenants_url;
                }
            });
        }
    });

    $('body').on('click', '#delete_template', function () {
        if (confirm("Are you sure want to delete?")) {
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            var route_url = $(this).attr('data-url');

            $.ajax({
                url: route_url,
                type: 'POST',
                data: { '_token': CSRF_TOKEN },
                dataType: 'JSON',
                success: function (data) {
                    window.location.href = data.template_url;
                }
            });
        }
    });

    $('body').on('click', '#delete_template_category', function () {
        if (confirm("Are you sure want to delete?")) {
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            var route_url = $(this).attr('data-url');

            $.ajax({
                url: route_url,
                type: 'POST',
                data: { '_token': CSRF_TOKEN },
                dataType: 'JSON',
                success: function (data) {
                    window.location.href = data.template_category_url;
                }
            });
        }
    });


    $(document).on("click", ".db-file-delete", function () {
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        var file_id = $(this).attr('data-target');
        Swal.fire({
            title: 'Are you sure want to delete?',
            showDenyButton: true,
            confirmButtonText: 'Yes',
            denyButtonText: 'No'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'db_backup_delete',
                    type: 'POST',
                    data: { '_token': CSRF_TOKEN, file_id: file_id },
                    dataType: 'JSON',
                    success: function (data) {
                        location.reload();
                    }
                });
            }
        });
    });

    $(document).on("click", ".db-file-create", function () {
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        Swal.fire({
            title: 'Are you sure want to create a new backup?',
            showDenyButton: true,
            confirmButtonText: 'Yes',
            denyButtonText: 'No'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'db_backup_create',
                    type: 'POST',
                    data: { '_token': CSRF_TOKEN },
                    dataType: 'JSON',
                    success: function (data) {
                        Swal.fire({
                            text: data.message,
                            icon: "success",
                        });
                    }
                });
            }
        });
    });

    $(document).on("change", "select[name='plan_type']", function () {
        let plan_type = $(this).val();
        if (plan_type == 'default') {
            $(".tenant-div").addClass("d-none");
        } else {
            $(".tenant-div").removeClass("d-none");
        }
    });


    $("body").on("change", "select[name='primary_trigger']", async function () {
        let primary_trigger = $(this).val();
        let trigger_event_options = '<option value="">Select Trigger Event</option>';
        if (primary_trigger == 'Project') {
            trigger_event_options += '<option value="Created">Created</option>' +
                '<option value="PhaseChanged">Phase Changed</option>' +
                '<option value="AddedHashtag">Project Hashtag Added</option>' +
                '<option value="Created-PhaseChanged-AddedHashtag">Select All</option>';
        } else if (primary_trigger == 'Contact') {
            trigger_event_options += '<option value="Created">Created</option>' +
                '<option value="Updated">Updated</option>' +
                '<option value="Created-Updated">Select All</option>';
        } else if (primary_trigger == 'Note') {
            trigger_event_options += '<option value="Created">Created</option>' +
                '<option value="Completed">Completed</option>' +
                '<option value="TaskflowButtonTrigger">Taskflow Button Trigger</option>' +
                '<option value="Created-Completed-TaskflowButtonTrigger">Select All</option>';
        } else if (primary_trigger == 'CollectionItem') {
            trigger_event_options = '<option value="Created">Created</option>' +
                '<option value="Deleted">Deleted</option>' +
                '<option value="Created-Deleted">Select All</option>';
        } else if (primary_trigger == 'Appointment') {
            trigger_event_options += '<option value="Created">Created</option>' +
                '<option value="Updated">Updated</option>' +
                '<option value="Deleted">Deleted</option>' +
                '<option value="Created-Updated-Deleted">Select All</option>';
        } else if (primary_trigger == 'Section') {
            trigger_event_options += '<option value="Visible">Visible</option>' +
                '<option value="Hidden">Hidden</option>' +
                '<option value="Visible-Hidden">Select All</option>';;
        } else if (primary_trigger == 'ProjectRelation') {
            trigger_event_options += '<option value="Related">Related</option>' +
                '<option value="Unrelated">Unrelated</option>' +
                '<option value="Related-Unrelated">Select All</option>';
        } else if (primary_trigger == 'TeamMessageReply' || primary_trigger == 'DocumentUploaded' || primary_trigger == 'FormSubmitted' || primary_trigger == 'SMSReceived' || primary_trigger == 'DocumentShared') {
            trigger_event_options = '<option value="N/A">Trigger Event</option>';
        } else if (primary_trigger == 'CalendarFeedback') {
            trigger_event_options = '<option value="Received">Received</option>';
        } else {
            trigger_event_options = '<option value="">Trigger Event</option>';
        }
        $("select[name='trigger_event']").html(trigger_event_options);
    });

    $(document).on("click", "a.remove-mapping-rule", function () {
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        var rule_id = $(this).attr('data-id');
        Swal.fire({
            title: 'Are you sure want to delete?',
            showDenyButton: true,
            confirmButtonText: 'Yes',
            denyButtonText: 'No'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'automation_workflow_mapping_delete',
                    type: 'POST',
                    data: { '_token': CSRF_TOKEN, rule_id: rule_id },
                    dataType: 'JSON',
                    success: function (data) {
                        location.reload();
                    }
                });
            }
        });
    });

    $("body").on("click", "a.edit-mapping-rule", async function () {
        let json_details = ($(this).data('json'));
        let mapping_ids = json_details.ids;
        let primary_trigger = json_details.primary_trigger;
        let trigger_event = json_details.trigger_event;
        let action_name = json_details.action_name;
        let action_short_code = json_details.action_short_code;

        $("input[name='mapping_ids']").val(mapping_ids);
        $("select[name='primary_trigger']").val(primary_trigger).change();
        $("select[name='trigger_event']").val(trigger_event);

        let action_short_codes = action_short_code.split(",");
        let action_names = action_name.split(",");

        let option_values = [];
        $.each(action_short_codes, function (i) {
            let option_value = action_short_codes[i] + '-' + action_names[i];
            option_values.push(option_value);
        });
        $("#kt_select2_3").select2().val(option_values).trigger("change");
    });

    $("body").on("click", "button.add-mapping-rule", async function () {
        $("input[name='mapping_ids']").val("");
        $("select[name='primary_trigger']").val("").change();
        $("select[name='trigger_event']").val("");
        $("#kt_select2_3").select2().val([]).trigger("change");
    });

    var BasicDatatablesDataSourceHtml = function () {
        var superadminBasicDatatable = function () {
            var table = $('#superadmin_basic_datatable');
            table.DataTable({
                responsive: true,
                order: [[3, 'desc']],
                columnDefs: [
                    { width: 150, targets: 3 }
                ],
            });
        };
        return {
            init: function () {
                superadminBasicDatatable();
            },
        };
    }();

    jQuery(document).ready(function () {
        BasicDatatablesDataSourceHtml.init();
    });


    /* Start Subscription Plan Mapping Page */

    $(document).on("click", "a.remove-subscription-mapping", function () {
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        var mapping_id = $(this).attr('data-id');
        Swal.fire({
            title: 'Are you sure want to delete?',
            showDenyButton: true,
            confirmButtonText: 'Yes',
            denyButtonText: 'No'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'subscription_plan_mapping_delete',
                    type: 'POST',
                    data: { '_token': CSRF_TOKEN, mapping_id: mapping_id },
                    dataType: 'JSON',
                    success: function (data) {
                        location.reload();
                    }
                });
            }
        });
    });


    $("body").on("click", "a.edit-subscription-mapping", async function () {
        let json_details = ($(this).data('json'));
        let subscription_plan_mapping_id = json_details.id;
        let subscription_plan_id = json_details.subscription_plan_id;
        let project_count_from = json_details.project_count_from;
        let project_count_to = json_details.project_count_to;

        $("input[name='subscription_plan_mapping_id']").val(subscription_plan_mapping_id);
        $("select[name='subscription_plan_id']").val(subscription_plan_id).change();
        $("input[name='project_count_from']").val(project_count_from);
        $("input[name='project_count_to']").val(project_count_to);
    });

    $("body").on("click", "button.add-subscription-mapping", async function () {
        $("input[name='subscription_plan_mapping_id']").val("");
        $("select[name='subscription_plan_id']").val("").change();
        $("input[name='project_count_from']").val("");
        $("input[name='project_count_to']").val("");
    });

    /* End Subscription Plan Mapping Page */

});


tinymce.init({
    selector: 'textarea#description',
    plugins: [ 'advlist', 'autolink', 'link', 'image', 'lists', 'charmap', 'preview' ],

  });
