$(document).ready(function (e) {

    $('.notification-active').on('click', function (e) {
        let parent = e.target.parentNode.parentNode;
        e.target.classList.add("btn-success")
        parent.querySelector('.notification-deactive').classList.remove("btn-danger");
        parent.querySelector('.is_active_on').click();
    });

    $('.notification-deactive').on('click', function (e) {
        let parent = e.target.parentNode.parentNode;
        e.target.classList.add("btn-danger")
        parent.querySelector('.notification-active').classList.remove("btn-success");
        parent.querySelector('.is_active_off').click();
    });

    $("#end_date").change(function () {
        var start_date = document.getElementById("start_date").value;
        var end_date = document.getElementById("end_date").value;
        if ((Date.parse(start_date) >= Date.parse(end_date))) {
            Swal.fire({
                text: "End date should be greater than Start date!",
                icon: "error",
            });
            document.getElementById("end_date").value = "";
        }
    });
});

function noticeChangeStatus(e, id) {
    $.ajax({
        type: "POST",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: "update_client_notification_status",
        data: {
            id
        },
        success: function (response) {

            let current_checkbox = $('#notification_config_toggle_' + id).prop('checked');

           /* $('.notice-status').each(function () {
                $(this).prop('checked', false);
            }); */

            if (current_checkbox) {
                $('#notification_config_toggle_' + id).prop('checked', true);
            }
        },
        error: function () {

        }
    });
}
