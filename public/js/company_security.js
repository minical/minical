$( document ).ready(function() {


    const inputs = document.querySelectorAll('.otp-input input');
    inputs.forEach((input, index) => {
        input.addEventListener('input', (e) => {
            if (e.target.value.length === 1 && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && index > 0 && !e.target.value) {
                inputs[index - 1].focus();
            }
        });
    });




    $('body').on('click','.verify_otp',function(){

        let otp = '';
        inputs.forEach(input => {
            otp += input.value;
        });

        // var otp = $('#otp').val();
        var secret_code = $('#secret_code').val();
        var qr_code_url = $('#qr_code_url').val();

        console.log('inputs',otp);

        $.ajax({
            type: "POST",
            url: getBaseURL() + 'auth/verify_otp',
            dataType: 'json',
            data: {
                    otp: otp,
                    secret_code: secret_code,
                    qr_code_url: qr_code_url,
                    otp_verification: 1,
                    via: 'login'
                },
            success: function(resp){
                console.log('resp',resp);
                if(resp.success){
                    if(resp.redirect == 'admin')
                        window.location.href = getBaseURL() + 'admin';
                    else if(resp.redirect == 'room')
                        window.location.href = getBaseURL() + 'room';
                    else if(resp.redirect == 'booking')
                        window.location.href = getBaseURL() + 'menu/select_hotel/'+resp.company_id;
                } else {
                    alert(resp.error_msg);
                }
            }
        });
    });
});