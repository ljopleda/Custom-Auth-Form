jQuery(document).ready(function($) {
    var alert_ico = '<span class="glyphicon glyphicon-alert" aria-hidden="true"></span> '
    // Perform AJAX login on form submit
    $('form#login').on('submit', function(e){
        $('form#login p.status').show().removeClass('alert-success alert-danger').text(ajax_auth_object.loadingmessage);
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: ajax_auth_object.ajaxurl,
            data: {
                'action': 'ajaxlogin', //calls wp_ajax_nopriv_ajaxlogin
                'username': $('form#login #username').val(),
                'password': $('form#login #password').val(),
                'security': $('form#login #security').val() },
            success: function(data){

                if (data.loggedin == true){
                  $('form#login p.status').html(data.message);
                    $('form#login p.status').removeClass('alert-danger').addClass('alert-success');
                    document.location.href = ajax_auth_object.redirecturl;
                }else{
                  $('form#login p.status').html(alert_ico+data.message);
                    $('form#login p.status').removeClass('alert-success').addClass('alert-danger');
                }
            }
        });
        e.preventDefault();
    });

    // Perform AJAX Register on form submit
    $('form#form-register').on('submit',function(e){
      $('form#form-register p.status').show().removeClass('alert-success alert-danger').text(ajax_auth_object.loadingmessage);
      $.ajax({
        type: 'POST',
        dataType: 'json',
        url: ajax_auth_object.ajaxurl,
        data: {
          'action': 'ajaxregister',
          'username': $('#rg-username').val(),
          'fullname': $('#rg-fullname').val(),
          // 'password': $('#rg-password').val(),
          'email': $('#rg-email').val(),
          'security': $('#registersecurity').val()
        },
        success: function (data) {
          if (data.status == true) {
            $('.btn-register').addClass('disabled');
            $('.btn-register, form#form-register input').prop('disabled',true);
            $('form#form-register p.status').show().html('Thank you! A confirmation email has been sent to '+$('#rg-email').val()+'. Please click on the activation link to activate your account.').removeClass('alert-danger').addClass('alert-success');
          }else{
            $('form#form-register p.status').show().html(alert_ico+'Registration failed '+data.message).removeClass('alert-success').addClass('alert-danger');
          }
        }
      });
      e.preventDefault();
    })

    // Perform AJAX Set password on form submit
    $('form#form-setpassword').on('submit',function(e){
      $('form#form-setpassword p.status').show().removeClass('alert-success alert-danger').text(ajax_auth_object.loadingmessage);
      if($('#sp-password').val()==$('#sp-cpassword').val()&&$('#sp-password').val().length){
        var pw_len = $('#sp-password').val().length;
        if(pw_len>5&&pw_len<16){
          $.ajax({
            type: 'POST',
            dataType: 'json',
            url: ajax_auth_object.ajaxurl,
            data: {
              'action': 'ajaxsetpassword',
              'password': $('#sp-password').val(),
              'cpassword': $('#sp-cpassword').val(),
              'email': $('#sp-email').val(),
              'confirm': $('#sp-confirm').val(),
              'security': $('#setpasswordsecurity').val()
            },
            success: function (data) {
              if (data.status == 1) {
                $('.btn-savepassword').addClass('disabled');
                $('.btn-savepassword, form#form-setpassword input').prop('disabled',true);
                $('form#form-setpassword p.status').show().html(data.message);
                document.location.href = ajax_auth_object.redirecturl;
              }else{
                $('form#form-setpassword p.status').show().html(alert_ico+'Setting password failed ').removeClass('alert-success').addClass('alert-danger');
              }
            }
          });
        }else{
          $('form#form-setpassword p.status').show().html(alert_ico+'Password must be 6 to 14 characters long').removeClass('alert-success').addClass('alert-danger');
        }
      }else{
        $('form#form-setpassword p.status').show().html(alert_ico+'Passwords don\'t match').removeClass('alert-success').addClass('alert-danger');
      }
      e.preventDefault();
    })

});
