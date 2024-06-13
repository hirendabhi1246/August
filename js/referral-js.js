jQuery(document).ready(function ($) {

    $('#referral-registration-form').on('submit', function (e) {
        e.preventDefault();
        // $('.cat-list_item').removeClass('active');
        // $(this).addClass('active');

        $.ajax({
            type: 'POST',
            url: referralAjax.ajaxurl,
            dataType: 'json',
            data: {
                action: 'register_user',
                formdata: jQuery(this).serialize(),
            },
            beforeSend: function(){
                $('#referral-registration-form span.error').empty();
            },
            success: function (res) {
                if (res.status == false) {
                    $.each(res.error_fields, function(key, value){
                    	// set the html of the id corresponding to the key to the value
                    	$('span.'+key).html(value);
                    });
                }else{
                    $("#referral-registration-form")[0].reset();
                    $('.ajax-response').html(res.message);
                    setTimeout(() => {
                        window.location.href = referralAjax.homeurl
                    }, 2000);
                }
                
            }
        });
    });
});
