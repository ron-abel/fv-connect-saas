$(document).ready(function (e) {

    $("#fileToUploadForm").on('submit', (function (e) {
        e.preventDefault();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            url: "mass_updates/upload_csv",
            type: "POST",
            data: new FormData(this),
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function () {
                //$("#preview").fadeOut();
                $("#err").fadeOut();
            },
            success: function (data) {
                if (data == 'invalid') {
                    // invalid file format.
                    // $("#err").html("Invalid File !").fadeIn();
                    Swal.fire({
                        text: "Invalid File !",
                        icon: "error",
                    })
                } else {
                    // view uploaded file.
                    $("#uploaded_file")[0].setAttribute('data-val', $("#fileToUpload")[0].files.item(0).name);
                    // $("#preview").html(data).fadeIn();
                    var n = data.indexOf("Success");
                    if (n > -1) {
                        Swal.fire({
                            text: data,
                            icon: "success",
                        })
                        $("#fileToUploadForm")[0].reset();
                    } else {
                        Swal.fire({
                            text: data,
                            icon: "error",
                        })
                    }
                }
            },
            error: function (e) {
                Swal.fire({
                    text: e,
                    icon: "error",
                })
                // $("#err").html(e).fadeIn();
            }
        });
    }));

    $("#addContactsBtn").on('click', (function (e) {

        var operation = "contact";
        var filename = $("#uploaded_file").attr('data-val');
        console.log(filename);
        e.preventDefault();

        callAction(operation, filename);
    }));

    $("#addPersonTypesBtn").on('click', (function (e) {

        var operation = "personType";
        var filename = $("#uploaded_file").attr('data-val');
        console.log(filename);
        e.preventDefault();

        callAction(operation, filename);
    }));


    function callAction(operation, filename) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(".loading").show();
        $.ajax({
            url: "mass_updates/add_csv_data",
            type: 'POST',
            dataType: 'json',
            data: "operation=" + operation + "&filename=" + filename,
            beforeSend: function () {
                $("#preview").fadeOut();
            },
            success: function (data) {
                if (data.result == 'error') {
                    // $("#err").html(data.message).fadeIn();
                    Swal.fire({
                        text: data.message,
                        icon: "error",
                    });

                } else {
                    var html_result = '<p>' + data.result.failed_count + '</p>';
                    html_result += '<p>' + data.result.success_added_count + '</p>';
                    html_result += '<p>' + data.result.success_update_count + '</p>';
                    if (data.result.hasOwnProperty("failed_records")) {
                        data.result.failed_records.forEach(function (v, j) {
                            html_result += '<p>' + v + '</p>';
                        });
                    }

                    $("#preview").html(html_result).fadeIn();
                }
            }, error: function () {
                $(".loading").hide();
                alert("Error to Process Your Request! Please try Again!");
            },
        }).done(function () {
            $(".loading").hide();
        });
    }
});
