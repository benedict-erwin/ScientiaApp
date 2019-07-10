$(document).ready(function() {
    var klik = true;

    /* Event default */
    $("#tx_username").trigger('focus');
    $("#tx_username").enterKey(function(e) {
        e.preventDefault();
        klik = true;
        $("#tx_password").trigger('focus');
    });

    $("#tx_password").enterKey(function(e) {
        e.preventDefault();
        klik = true;
        $("#bt_login").click();
    });

    /* Click bt_login */
    $(document).on('click', '#bt_login', function() {
        if (klik) {
            klik = false;
            if ($('#tx_username').val() == '' || $('#tx_password').val() == '') {
                notification('Login Invalid', 'warn');
                $("#tx_username").trigger('focus');
                klik = true;
                return false;
            }
            $(this.element).prop('disabled', true);
            $('#tx_spin').html('<i class="fa fa-spinner fa-spin"></i> Please wait...');
            post_data = {
                'tx_username': $('#tx_username').val(),
                'tx_password': $('#tx_password').val()
            };
            $.ajax({
                "type": 'POST',
                "url": SiteRoot + 'clogin',
                "data": post_data,
                "dataType": 'json',
                "success": function(data, textStatus, jqXHR) {
                    if (data.success === true) {
                        set_token(API_TOKEN, jqXHR.getResponseHeader('JWT'));
                        notification(data.message, 'success');
                        setTimeout(function(){
                            window.location.replace('./home');
                        }, 1000);
                    } else {
                        notification((data.message.error) ? data.message : data.error, 'warn');
                        $('#tx_spin').html('LOGIN');
                        $(this.element).prop('disabled', false);
                        klik = true;
                    }
                },
                "error": function(jqXHR, textStatus, errorThrown) {
                    klik = true;
                    $(this.element).prop('disabled', false);
                    $('#tx_spin').html('LOGIN');
                    notification(errorThrown, 'error');
                    console.log(jqXHR);
                    console.log(textStatus);
                    console.log(errorThrown);
                }
            });
        }
    });
});
