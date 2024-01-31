
$(document).ready(function () {
    $('#tenant_name').keypress(function () {
        var value = String.fromCharCode(event.which);
        var pattern = new RegExp(/^[a-z]+$/);
        return pattern.test(value);
    });

    $('.login100-form-btn').click(function (e) {
        if ($('#fv_tenant_base_url').length) {
            e.preventDefault();
            var appFilevine = $('#fv_tenant_base_url').val();
            if (appFilevine == '') {
                $('#tenant_name-error').html('The fv tenant base url field is required..');
                return false;
            }
            var domain = appFilevine.replace('http://', '').replace('https://', '').split(/[/?#]/)[0];
            
            var myKey = 'filevine.com';
            var myKey1 = 'filevineapp.com';
            var myMatch = domain.search(myKey);
            var myMatch1 = domain.search(myKey1);
            if (myMatch != -1 || myMatch1 != -1) {
                $('#registerForm').submit();
            }
            else {
                $('#tenant_name-error').html('The format of the Filevine Tenant base url is invalid.');
            }
        } else {
            $('#registerForm').submit();
        }

    });

    $('.add_tenant_save').click(function (e) {
        if ($('#fv_tenant_base_url').length) {
            e.preventDefault();
            var appFilevine = $('#fv_tenant_base_url').val();
            if (appFilevine == '') {
                $('#tenant_name-error').html('The Filevine tenant base url is required.');
                return false;
            }
            var domain = appFilevine.replace('http://', '').replace('https://', '').split(/[/?#]/)[0];
            var myKey = 'filevine.com';
            var myKey1 = 'filevineapp.com';
            var myMatch = domain.search(myKey);
            var myMatch1 = domain.search(myKey1);
            if (myMatch != -1 || myMatch1 != -1) {
                $('#add_tenant_form').submit();
            }
            else {
                $('#tenant_name-error').html('The format of the Filevine Tenant base url is invalid.');

            }
        } else {
            $('#add_tenant_form').submit();
        }

    });


});
