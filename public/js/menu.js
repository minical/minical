$(function () {

    $('.otp-input input').val('');
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

    var baseURL = getBaseURL();
    var companyLockTime = innGrid.featureSettings.companyLockTime ? innGrid.featureSettings.companyLockTime : 10;
    var sec = companyLockTime * 60; // 30 minutes remaining until timeout
    //30 minutes connection timeout.
    var timer = setInterval(function () {
        if(!is_current_user_superadmin){
            $('#hideMsg span').text(sec--);
            if (sec < 0) {
                $("#dialog-timeout").modal("show");

                $.post(getBaseURL() + 'settings/company_security/enable_otp_verification',
                    {}, function (str) {
                        if(!str.otp_verified){ // link to page
                            $("#dialog-timeout").modal("show");
                        }
                    }, "json"
                );

                $.post(getBaseURL() + 'auth/check_for_employee_auto_logout',
                    {}, function (str) {
                    }, "json"
                );
                clearInterval(timer);
            }
        }
    }, 1000);


    $.post(getBaseURL() + 'settings/company_security/get_otp_verification',
        {}, function (str) {
            console.log('str',str);
            if(!str.otp_verified && !is_current_user_superadmin){ // link to page
                $("#dialog-timeout").modal("show");
            }
        }, "json"
    );

    $('body').on('click','.verify_otp',function(){

        // var otp = $('#otp_value').val();

        let otp = '';
        inputs.forEach(input => {
            otp += input.value;
        });

        console.log('inputs',otp);

        $.ajax({
            type: "POST",
            url: getBaseURL() + 'settings/company_security/afk_verify_otp',
            dataType: 'json',
            data: {
                    otp: otp,
                    otp_verification: 1
                },
            success: function(resp){
                console.log('resp',resp);
                if(resp.success){
                    location.reload();
                } else {
                    alert(resp.error_msg);
                }
            }
        });
    });

    // change QR Code

    $('body').on('click','#change_qr_code',function(){
        // var qr_change = $('input[name="change_qr_code"]').prop('checked') ? 1 : 0;
        
        $.post(getBaseURL() + 'settings/company_security/change_new_qr_code',
            {}, function (str) {
                // console.log('str',str);
                //if(qr_change == 1){
                    // $('.company_security_settings').hide();
                    $('.show_qr_code_form').html(str);
                    $('.company_security_settings').hide();
                // } else {
                //     // $('.company_security_settings').show();
                //     $('.show_qr_code_form').html('');
                // }
                
            }
        );
    });

    $('body').on('click','.back_to_security',function(){
        $('.show_qr_code_form').html('');
        $('.company_security_settings').show();
    });

    // $('body').on('change','#lock_timer_setting',function(){
    //     var lock_timer = $('input[name="lock_timer_setting"]').prop('checked') ? 1 : 0;
        
    //     if(lock_timer == 1){
    //         $('.lock_timer_div').show();
    //     } else {
    //         $('.lock_timer_div').hide();
    //     }
    // });

    $('body').on('click','#disable_security',function(){
        
        $.post(getBaseURL() + 'settings/company_security/disable_security',
            {}, function (str) {
                $('.show_qr_code_form').html(str);
                $('.company_security_settings').hide();
            }
        );
    });

    // Verify new OTP

    $('body').on('click','.verify_new_otp',function(){

        // var otp = $('#new_otp').val();

        let otp = '';
        inputs.forEach(input => {
            otp += input.value;
        });

        console.log('inputs',otp);

        var secret = $('#secret_code').val();
        var qr_code_url = $('#qr_code_url').val();
        var security_name = $('#security_name').val();

        $.ajax({
            type: "POST",
            url: getBaseURL() + 'settings/company_security/verify_new_otp',
            dataType: 'json',
            data: {
                    otp: otp,
                    secret: secret,
                    qr_code_url: qr_code_url,
                    security_name: security_name,
                    via: 'company_security_first_time'
                },
            success: function(resp){
                console.log('resp',resp);
                if(resp.success){
                    location.reload();
                } else {
                    alert(resp.error_msg);
                }
            }
        });
    });


    // if user isn't active for 30 minutes, show time out screen
    $("body").on('click', function () {
        sec = companyLockTime * 60; // reset timer to 30 minutes
    });

    if(window.location.href.substr(-33) == 'account_settings/company_security'){ // subscription
        setTimeout(function () {
            $("#dialog-timeout").modal("hide");
        },2000);
    }

    if(window.location.href.substr(-12) != 'subscription'){ // subscription
        setTimeout(function () {
            $.post(
                getBaseURL() + 'auth/get_subscription_state_extended',
                {},
                function (response) {
                    //itodo fix this
                    if(response.message){
                        $("#dialog-update-billing").modal("show");
                        $('#dialog-update-billing .message').html(response.message);

                        if(response.show_link){ // link to page
                            $('#dialog-update-billing a').show();
                        }

                        if(response.is_blocking){
                            $("#dialog-update-billing").find(".close").hide();
                        }
                    }
                },
                'json'
            );
        },2000);
    }

    // if (is_current_user_activated == "0") {
    //     $("#dialog-verify-email-notification").modal("show");
    // }
    
    $('select#country[name="country"]').on('change', function () {
        updateCountryPhoneLang();
    });

    updateCountryPhoneLang();

});

function updateCountryPhoneLang()
{
    var countryCode = $('select#country[name="country"]').find('option:selected').data('country-code');
        
    var countryPhoneCode = $('select#phone_number_country_code[name="phone_number_country_code"]')
            .find('option[data-country-code="'+countryCode+'"]').prop('selected', true);

    var languageCode = $('select#language').data('lang_group');
    
    $('select#language').val('English');
    for(var lang in languageCode){
        for(var code in languageCode[lang]){
            if ( code == countryCode) {
                $('select#language option').each(function(){                        
                    if ( lang == $(this).val() ) {
                        $('select#language').val(lang);
                        return false;
                    }
                })
            }
        }
    }
}

 $('#property_type').on('change', function () {
    if($(this).val() == 0){
        $(".property").hide();

    }
    else if($(this).val() == 1){

        $(".property").show();
        $(".property_name").html(l('Hotel Name')+' <span style="color: red;">*</span>');
        $(".number_of_rooms").html(l('No of Rooms')+' <span style="color: red;">*</span>');

      
    }else if($(this).val() == 2){

        $(".property").show();
        $(".property_name").html(l('Hostel Name')+' <span style="color: red;">*</span>');
        $(".number_of_rooms").html(l('No of Beds')+' <span style="color: red;">*</span>');
    }
     else if($(this).val() == 3){
        
        $(".property").show();
        $(".property_name").html(l('Property Name')+' <span style="color: red;">*</span>');
        $(".number_of_rooms").html(l('No of Rooms')+' <span style="color: red;">*</span>');
    }
    else if($(this).val() == 4){
        
        $(".property").show();
        $(".property_name").html(l('Apartment Name')+' <span style="color: red;">*</span>');
        $(".number_of_rooms").html(l('No of Units')+' <span style="color: red;">*</span>');
    }
     else if($(this).val() == 5){
     
        $(".property").show();
        $(".property_name").html(l('Vehicle Name')+' <span style="color: red;">*</span>');
        $(".number_of_rooms").html(l('No of Vehicles')+' <span style="color: red;">*</span>');
    }
     else if($(this).val() == 6){
        
        $(".property").show();
        $(".property_name").html(l('Office Name')+' <span style="color: red;">*</span>');
        $(".number_of_rooms").html(l('No of Rooms')+' <span style="color: red;">*</span>');
    }else{
        $(".property").show();
    }
    });

function ajax_submit(url,form_id,source){
    var firs_name = $('#first_name').val();
    var last_name = $('#last_name').val();
    var property_name = $('#property_name').val();
    var property_type = $('#property_type').val();
    var number_of_rooms = $('#number_of_rooms').val();
    var country = $('#country').val();
    var phone_number = $('#phone_number').val();
    var lang_id = $('select#language option:selected').data('lang_id');
    var validate = '';
    
    var phone_number_country_code = $('#phone_number_country_code').val();
    phone_number = phone_number_country_code +""+ phone_number;
    
    var closeiodata = {
        name:property_name,
        number_of_rooms: number_of_rooms
    };


    if(firs_name==''){
        validate = validate+l("First name is required");
    }
    if(last_name==''){
        validate = validate+"\n"+l('Last name is required');
    }
    if(property_name==''){
        validate = validate+"\n"+l('Property name is required');
    }
    if(property_type== 0){
        validate = validate+"\n"+l('Property Type is required');
    }
    if(number_of_rooms==0){
        validate = validate+"\n"+l('Number of room is required');
    }
    if(number_of_rooms!=0 && isNaN(number_of_rooms)){
        validate = validate+"\n"+l('Number of room is not numeric');
    }
    if(country=='' || country==0){
        validate = validate+"\n"+l('Country must be selected')
    }
    if(phone_number == ''){
        validate = validate+"\n"+l('Phone number is required');
    }
    else if(phone_number.length < 7 || phone_number == 0){
        validate = validate+"\n"+l('Phone number is invalid');
    }

    if(validate==''){
        $.ajax({
            type: "POST",
            url: url,
            data: $(form_id).serialize() + "&lang_id="+lang_id, // serializes the form's elements.
            success: function(data)
            {
                if(source=='update_company'){
                    $('.alert-success').show();
                    $('.alert-danger').hide();
                    $('.alert-success').html(l('Successfully updated'));
                    $('#update-property-modal').modal('hide');

                    update_closeio(closeiodata);

                    // user just updated property after signup, fire Intercom event
					if (typeof Intercom !== "undefined") {
						Intercom('trackEvent', 'Signup_step_2');
					}

                    setTimeout(function (){location.reload();}, 2000);
                }
            }
        });
    }
    else{
        alert(validate);
    }

    setTimeout(function(){
        $('.alert-success').hide();
        $('.alert-danger').hide();
        //document.location.reload(); // reload to force guider
    }, 4000);
}

function update_closeio(data)
{
   
    //if (window.location.hostname !=  window.location.host) {
        $.ajax({
            type: "POST",
            url: getBaseURL() + "auth/add_to_close_io",
            data: data,
            success: function(data)
            {

            }
        });
    //}
}

function open_tos(){
    // Check if TOS needs to be agreed
    $.ajax({
        type    : "POST",
        url     : getBaseURL() + "user/agreed_to_tos_AJAX",
        dataType: "json",
        success : function (tos_agreed) {
            if (!tos_agreed) {
                // Added css to overcome a bug that prevented modal from scrolling down
                $(".modal-content").css("overflow", "auto");
                $(".modal-content").css("height", "100%");

                var myModal = $('#myModal');

                myModal.modal();
                myModal.find(".modal-title").html(l("Please accept our Terms of Service and Privacy Policy"));
                myModal.find(".modal-body").load(getBaseURL() + "auth/show_terms_of_service");
                myModal.find(".modal-footer").html(
                    $('<form/>', {id: 'tos-form'})
                        .append($('<button/>', {
                            type: 'submit',
                            class: 'btn btn-primary accept-button',
                            text: l('I accept')
                        }))
                        .append($('<a/>', {
                            href: 'http://www.minical.io',
                            type: 'button',
                            class: 'btn btn-default',
                            text: l('Close')
                        }))
                );
                //myModal.find(".modal-footer").append("");
                // set modal options to set title and content
                $('#tos-form').on('submit', function(e){
                    e.preventDefault();

                    if (typeof ga !== "undefined") {
                        console.log('_trackPageview', 'Virtual-Minical-Free-Trial-Registration');
                        ga('set', 'page', 'Virtual-Minical-Free-Trial-Registration');
                        ga('send', 'pageview');
                    }

                    
                    window.dataLayer = window.dataLayer || [];
                    window.dataLayer.push({
                        'event': 'tosSignUpFormSuccess',
                        'formId': 'tos-form',
                        'eventCategory': 'TrialSignup',
                        'eventAction': 'TOS form success',
                        'eventLabel': 'TrialSignupSuccess'
                    });
                    
                    // user just updated property after signup, fire Intercom event
					if (typeof Intercom !== "undefined") {
						Intercom('trackEvent', 'Signup_step_3');
					}
                    
                    // Google analytics Virtual Page view
					// if (typeof gtag !== "undefined") {
					// 	gtag('config', 'UA-25824665-5', {'page_path': 'virtual/auth/register.html'});
					// }
                    //
                    // //Hotjar Virtual Page View trigger.
					// if (typeof hj !== "undefined") {
					// 	hj('vpv', '/sign-up-step-3/');
					// }
                    
                    $.ajax({
                        type    : "POST",
                        url     : getBaseURL() + "user/accept_terms_of_service",
                        dataType: "json",
                        success : function () {
                            //document.location.reload(); // reload to force guider
                            
                            myModal.modal('hide');
                            // show tutorial video modal
                            $('#tutorial-video-modal').modal('show');

                        }
                    });
                });
//                $(".accept-button").on("click", function () {
//                    $.ajax({
//                        type    : "POST",
//                        url     : getBaseURL() + "user/accept_terms_of_service",
//                        dataType: "json",
//                        success : function () {
//                            document.location.reload(); // reload to force guider
//                        }
//                    });
//
//                });

            }
        }
    });
}

function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+ d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}
function getCookie(cname) {
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for(var i = 0; i <ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}
