"use strict";

$(document).ready(function () {
    var id = '#file_uploader';

    var uppyDrag = Uppy.Core({
        autoProceed: true,
        handle: false,
        restrictions: {
            maxFileSize: 20000000, // 20mb
            maxNumberOfFiles: 5,
            minNumberOfFiles: 1,
            allowedFileTypes: ['image/*', 'video/*', 'application/*']
        },
        meta: {
            'scheme_id': ''
        }
    });

    uppyDrag.use(Uppy.DragDrop, {
        target: id + ' .uppy-drag',
        locale: {
            strings: {
                dropHereOr: 'Drop files here or %{browse}',
                browse: 'choose files',
            },
        }
    });
    // uppyDrag.use(Uppy.ProgressBar, {
    //     target: id + ' .uppy-progress',
    //     hideUploadButton: false,
    //     hideAfterFinish: true,
    // });

    uppyDrag.use(Uppy.XHRUpload, {
        endpoint: $('#uppy_endpoint').val(),
        method: 'post',
        timeout: 0,
        bundle: true,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        getResponseData(responseText, response) {
            setDragDropLabel();
            return response;
        }
    });

    uppyDrag.use(Uppy.StatusBar, {
        target: id + ' .uppy-status',
        hideAfterFinish: true,
        showProgressDetails: true,
        hideUploadButton: true,
        hideRetryButton: true,
        hidePauseResumeButton: true,
        hideCancelButton: true,
        doneButtonHandler: null,
        locale: {},
    });

    uppyDrag.on('file-added', (file) => {
        let upload_schema = $("#upload_schema").val();
        if (!upload_schema) {
            Swal.fire({
                text: "Please choose the type of document you are uploading!",
                icon: "error",
            });
            uppyDrag.removeFile(file.id);
            return;
        }

        var allowed_extensions = ['png', 'jpg', 'jpeg', 'gif', 'svg', 'mp4', 'flv', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];
        if ($.inArray(file.extension, allowed_extensions) == -1) {
            uppyDrag.removeFile(file.id);
            return;
        }
        var imagePreview = "";
        var thumbnail_inner = "";
        if ((/image/).test(file.type)) {
            thumbnail_inner = '<img src="" style="width:30px;" />';
            // thumbnail_inner = '<i class="fa fa-image" style="font-size: 20px;"></i>';
        }
        else if ((/video/).test(file.type)) {
            thumbnail_inner = '<i class="fa fa-video" style="font-size: 20px;"></i>';
        }
        else if ((/application/).test(file.type)) {
            thumbnail_inner = '<i class="fa fa-file" style="font-size: 20px;"></i>';
        }
        var thumbnail = '<div class="uppy-thumbnail col-sm-2">' + thumbnail_inner + '</div>';

        var sizeLabel = "bytes";
        var filesize = file.size;
        if (filesize > 1024) {
            filesize = filesize / 1024;
            sizeLabel = "kb";
            if (filesize > 1024) {
                filesize = filesize / 1024;
                sizeLabel = "MB";
            }
        }
        imagePreview += '<div class="uppy-thumbnail-container p-3 row col-md-12 alert alert-warning" data-id="' + file.id + '">' + thumbnail + ' <span class="uppy-thumbnail-label col-sm-8">' + file.name + ' (' + Math.round(filesize, 2) + ' ' + sizeLabel + ')</span><span data-id="' + file.id + '" class="uppy-remove-thumbnail col-sm-2 text-right"><i class="fas fa-circle-notch fa-spin"></i></span></div>';

        // append to view
        $(id + ' .uppy-thumbnails').append(imagePreview);
    });

    uppyDrag.on('upload-success', (file, response) => {
        var response = JSON.parse(response.body.response);
        $('.uppy-thumbnail-container[data-id="' + file.id + '"').removeClass('alert-warning');
        $('.uppy-thumbnail-container[data-id="' + file.id + '"').addClass('alert-success');
        $($('.uppy-thumbnail-container[data-id="' + file.id + '"').find('.uppy-remove-thumbnail')[0]).html('<i class="fa fa-check-circle"></i>');
        if (typeof response.url !== 'undefinfed' && typeof response.url !== '') {
            $($($('.uppy-thumbnail-container[data-id="' + file.id + '"').find('.uppy-thumbnail')[0]).find('img')[0]).attr('src', response.url);
        }
    });

    uppyDrag.on('upload-error', (file, error, response) => {
        $('.uppy-thumbnail-container[data-id="' + file.id + '"').removeClass('alert-warning');
        $('.uppy-thumbnail-container[data-id="' + file.id + '"').addClass('alert-danger');
        $($('.uppy-thumbnail-container[data-id="' + file.id + '"').find('.uppy-remove-thumbnail')[0]).html('<i class="fa fa-times-circle"></i>');
    });

    // $(document).on('click', id + ' .uppy-thumbnails .uppy-remove-thumbnail', function(){
    //     var imageId = $(this).attr('data-id');
    //     uppyDrag.removeFile(imageId);
    //     $(id + ' .uppy-thumbnail-container[data-id="'+imageId+'"').remove();
    // });

    $(document).on('change', '#upload_schema', function () {
        var value = $(this).val().split("*");
        uppyDrag.setMeta({ 'scheme_id': value[0] });
        let target_field_type = value[1];
        $(".upload-scheme-message-div").removeClass("d-none");
        if (target_field_type == 'DocList') {
            $("#upload-scheme-message").text("This selection allows for multiple uploads.");
            uppyDrag.setOptions({ restrictions: { maxNumberOfFiles: 5 } });
        } else {
            $("#upload-scheme-message").text("This selection allows for only a single upload file.");
            uppyDrag.setOptions({ restrictions: { maxNumberOfFiles: 1 } });
        }
        setDragDropLabel();
    });
    // init tooltips
    var initTooltip = function (el) {
        var theme = el.data('theme') ? 'tooltip-' + el.data('theme') : '';
        var width = el.data('width') == 'auto' ? 'tooltop-auto-width' : '';
        var trigger = el.data('trigger') ? el.data('trigger') : 'hover';

        $(el).tooltip({
            trigger: trigger,
            template: '<div class="tooltip ' + theme + ' ' + width + '" role="tooltip">\
                <div class="arrow"></div>\
                <div class="tooltip-inner"></div>\
            </div>'
        });
    }
    $('[data-toggle="tooltip"]').each(function () {
        initTooltip($(this));
    });

});
