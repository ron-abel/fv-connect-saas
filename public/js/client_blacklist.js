
function getItemNode(data) {
    var _items = $('<ul>').addClass('px-3 client-projects');
    var fullDisable = $('.client_id_' + data.client_info.id).length > 0 ? 'disabled' : '';
    var projectDisabled = '';
    let search_filter = $('#search_filter option:selected').val();

    $.each(data.projects, function (index, project) {
        var disable = $('.project_id_' + project.id).length > 0 ? 'disabled' : '';
        if (disable == 'disabled') {
            projectDisabled = 'disabled';
        }

        _items.append(
            $('<li>').addClass('list-group-item project-item').data('info', JSON.stringify(project)).append(
                $('<i>').addClass('fa fa-file')
            ).append(
                $('<span>').addClass('text-left ml-2').html(project.full_name)
            ).append(
                $('<span>').addClass('add-client-project').addClass(disable).addClass(fullDisable).html('<i class="fa fa-plus"></i>')
            )
        );
    });

    if (search_filter == "Project") {
        if (data.projects.length <= 0) {
            return;
        }
        return $('<li>').addClass('client-item').append(
            $('<div>').addClass('client-info-group').append(
                _items
            )
        );
    }

    return $('<li>').addClass('client-item').data('id', data.client_info.id).data('full_name', data.client_info.full_name).append(
        $('<div>').addClass('client-info-group').append(
            $('<div>').addClass('single-client-info').append(
                $('<i>').addClass('fa fa-user')
            ).append(
                $('<strong class="ml-2">').text(data.client_info.full_name)
            ).append(
                $('<span>').addClass('add-all-clients').addClass(projectDisabled).addClass(fullDisable).text('ADD ALL')
            )
        ).append(
            _items
        )
    );
}

var _searchAjax;

function sendSearchRequest(name, filter, source='') {
    var load_more_exist = false, offset=0, is_more=false;
    if($('#client-search-loader').length == 1) {
        offset = $('#client-search-loader').attr('data-offset');
        is_more = $('#client-search-loader').attr('data-ismore');
    }
    var searchEl = $('.clients-search-dropdown');
    _searchAjax = $.ajax({
        url: "get_clients_contacts",
        type: "POST",
        data: {
            name: name,
            search_filter: filter,
            offset: offset
        },
        success: function (data) {
            $('.client-loader').remove();
            if (data.status == true) {
                if(source == '') {
                    searchEl.removeClass('hide').html('');
                }

                // remove load more to shift to next page bottom
                $('#client-search-loader').remove();

                for (var i in data.data) {
                    searchEl.append(getItemNode(data.data[i]));
                }

                if(!load_more_exist) {
                    searchEl.append('<button class="btn btn-success btn-sm" id="client-search-loader" style="display:block;margin:auto;" data-offset="" data-ismore=""><i class="fa fa-arrow-circle-down"> </i> Load More</button>');
                }

                if(data.data.length > 0 && data.is_more) {
                    $('#client-search-loader').attr('data-offset', data.offset);
                    $('#client-search-loader').attr('data-ismore', data.is_more);
                }
                else {
                    $('#client-search-loader').attr('data-offset', data.data.length + 100);
                    $('#client-search-loader').attr('data-ismore', false);
                    $('#client-search-loader').hide();
                }
                
            }
        },
        error: function (e) {
            $('.client-loader').remove();
        }
    });
}

$(document).ready(function (e) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $("input[name=search_client]").on('keyup', (function (e) {
        e.preventDefault();
        // remove load more element on new search
        $('#client-search-loader').remove();
        var searchEl = $('.clients-search-dropdown'),
        loader = $('<div>').addClass('fa fa-circle-notch fa-spin client-loader'),
        val = $(this).val();

        if (_searchAjax) {
            _searchAjax.abort();
        }

        if (val.length == 0) {
            $('.client-loader').remove();
            searchEl.addClass('hide').html('');
        } else {
            loader.insertBefore(searchEl.html(''));
        }
        sendSearchRequest($(this).val().trim(), $('#search_filter option:selected').val());
        
    }));

    $(document).on('click', '#client-search-loader', (function (e) {
        e.preventDefault();
        var searchEl = $('.clients-search-dropdown'),
        loader = $('<div>').addClass('fa fa-circle-notch fa-spin client-loader');
        loader.insertBefore(searchEl);
        sendSearchRequest($('#search_client').val().trim(), $('#search_filter option:selected').val(), 'button');
    }));

    $(document).on('change', '#search_filter', (function (e) {
        var searchEl = $('.clients-search-dropdown');
        searchEl.addClass('hide').html('');
        $('#client-search-loader').remove();
    }));

    $(".clients-search-dropdown").on('click', '.add-client-project, .add-all-clients', (function (e) {
        e.preventDefault();

        var btn = $(this),
            li = btn.closest('li'),
            type = btn.hasClass('add-all-clients') ? 'client' : 'project',
            loader = $('<div>').addClass('fa fa-circle-notch fa-spin blacklist-loader');

        if (type == 'project') {
            var data = JSON.parse(li.data('info'));
        } else {
            var data = {
                id: li.data('id'),
                full_name: li.data('full_name')
            };
        }

        loader.insertAfter(btn);

        $.ajax({
            url: "client_blacklist",
            type: "POST",
            data: {
                type: type,
                data: data
            },
            success: function (data) {
                $('.blacklist-loader').remove();

                if (data.status == true) {
                    btn.addClass('disabled');
                    window.location.reload();
                }
            },
            error: function (e) {
                $('.blacklist-loader').remove();
            }
        });
    }));

    $("body").on('click', '.delete-client-record', (function (e) {
        e.preventDefault();

        var btn = $(this),
            href = btn.data('href');

        var form = $('<form>').hide().attr({ 'action': href, 'method': 'POST' }).append(
            $('<input>').attr({ 'type': 'hidden', 'name': '_method', 'value': 'DELETE' })
        ).append(
            $('<input>').attr({ 'type': 'hidden', 'name': '_token', 'value': $('meta[name="csrf-token"]').attr('content') })
        );

        Swal.fire({
            title: 'Are you sure to delete this record?',
            showDenyButton: true,
            showCancelButton: false,
            confirmButtonText: 'Delete',
            denyButtonText: `Cancel`,
        }).then((result) => {
            if (result.isConfirmed) {
                $('body').append(form);
                form.submit();
            }
        });


    }));
});
