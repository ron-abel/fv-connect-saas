$(document).ready(function () {
    loadNotificationLog();
});

function loadNotificationLog() {
    var notification_log_start_date = moment().startOf('month');
    var notification_log_end_date = moment();

    function cb(start, end) {
        $('#notificationlog span').html(start.format('MM/D/YYYY') + ' - ' + end.format('MM/D/YYYY'));

        let export_href = $(".export-notification-log").attr("href");
        export_href = export_href.split("?")[0] + '?log_start_date=' + start.format('YYYY-MM-DD') + '&log_end_date=' + end.format('YYYY-MM-DD') + '&notification_event_name=' + $("#notification_event_name").val();
        $(".export-notification-log").attr("href", export_href);

        $('#kt_datatable_notificationlog').DataTable({
            responsive: true,
            bDestroy: true,
            order: [[3, 'desc']],
            ajax: {
                url: 'get/notificationlog',
                method: 'get',
                data: {
                    'log_start_date': start.format('YYYY-MM-DD'),
                    'log_end_date': end.format('YYYY-MM-DD'),
                    'notification_event_name': $("#notification_event_name").val()
                },
            },
            columns: [
                {
                    data: 'event_name',
                },
                {
                    data: 'fv_project_id',
                },
                {
                    data: 'fv_project_name',
                },
                {
                    data: 'fv_client_id',
                },
                {
                    data: 'fv_client_name',
                },
                {
                    data: 'notification_body',
                },
                {
                    data: 'created_at',
                },
                {
                    data: 'sent_email_notification_at',
                },
                {
                    data: 'sent_post_to_filevine_at',
                }
            ]
        });
    }

    $('#notificationlog').daterangepicker({
        startDate: notification_log_start_date,
        endDate: notification_log_end_date,
        ranges: {
            'Today': [moment(), moment()],
            'This Week': [moment().startOf('isoWeek'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
        }
    }, cb);

    cb(notification_log_start_date, notification_log_end_date);
}


$("body").on("change", "select[name='notification_event_name']", async function () {
    loadNotificationLog();
});

$("body").on("click", "button.remove-failed-submit-log", async function () {
    let log_id = $(this).attr('data-id');
    Swal.fire({
        title: "Are you sure want to delete?",
        showDenyButton: true,
        confirmButtonText: 'Yes',
        denyButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: "post",
                url: "dashboard/delete/failed_submit_log",
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    log_id: log_id
                },
                success: function (response) {
                    Swal.fire({
                        text: response.message,
                        icon: "success",
                    }).then(function () {
                        location.reload();
                    });
                },
            });
        }
    });
});
