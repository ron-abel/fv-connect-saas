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
        var pattern = new RegExp(/^[a-z0-9-]+$/);
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
});


var BasicDatatablesDataSourceHtml = function () {
    var tenantAdminBasicDatatable = function () {
        var table = $('#tenantadmin_basic_datatable');
        table.DataTable({
            responsive: true,
            columnDefs: [
                { width: 150, targets: 0 }
            ],
        });
    };
    return {
        init: function () {
            tenantAdminBasicDatatable();
        },
    };
}();

jQuery(document).ready(function () {
    BasicDatatablesDataSourceHtml.init();
});


$('.version-description').click(function (e) {
    e.preventDefault();
    let title = $(this).html()
    let description = $(this).closest('.version-header').data('description')

    $('#versionModal').find('.modal-title').html(title)
    $('#versionModal').find('.modal-body').html(description)

    $('#versionModal').modal('show')
});


function searchList() {
    let input = $('#searchInput').val();

    $('.version-footer').addClass('d-none');
    $.ajax({
        type: "GET",
        url: "/admin/filter_versions",
        data: { formInput: input },
        dataType: "json",
        success: function (response) {
            let html = '';

            $.each(response.data, function (indexInArray, version) {
                 html += '<li class="versions-list-item"><div class="version-header" data-description="'+version.description+'"><h4><a class="version-description" href="#">'+version.major+'.'+version.minor+'.'+version.patch+'-'+version.version_name+'</a></h4></div></li>';
            });

            $('.versions-list').html(html)
        }
    });
}
