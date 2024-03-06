$(function (){

    var width = $("body").width();

    setCookie('width',width);

    $("select[name='require_paypal_payment']").change(function () {
        if($("select[name='require_paypal_payment']").val() === '1')
        {
            $.post(getBaseURL() + 'settings/website/enable_paypal', function (data) {
                // Enable user to enter paypal information
                $("input[name='paypal_account']").prop('readonly', false);
                $("input[name='percentage_of_required_paypal_payment']").prop('readonly', false);
                console.log(data);
            });

        }
        else
        {
            $.post(getBaseURL() + 'settings/website/disable_paypal', function (data) {
                console.log(data);
                $("input[name='paypal_account']").prop('readonly', true);
                $("input[name='percentage_of_required_paypal_payment']").prop('readonly', true);
            });


        }
    });
    $('.form_unconfirmed_reservations').on('submit', function (e) {
        e.preventDefault();
        var post_data = {
            'book_over_unconfirmed_reservations' : $('input[name="book_over_unconfirmed_reservations"]').prop('checked') ? 1 : 0
        };
        $.ajax({
            type   : "POST",
            url    : getBaseURL() + 'settings/integrations/update_unconfirmed_reservations_AJAX',
            data   : post_data,
            dataType: "json",
            success: function (data) {
                if(data.status){
                    alert(l('Settings updated successfully!'));
                }else{
                    alert(l('Some error occured! Please try again.'));
                }
            }
        });
        return false;
    });

    $('.form_is_total_balance_include_forecast').on('submit', function (e) {
        e.preventDefault();
        var post_data = {
            'is_total_balance_include_forecast' : $('input[name="is_total_balance_include_forecast"]').prop('checked') ? 1 : 0
        };
        $.ajax({
            type   : "POST",
            url    : getBaseURL() + 'settings/company/update_total_balance_include_forecast_AJAX',
            data   : post_data,
            dataType: "json",
            success: function (data) {
                if(data.status){
                    alert(l('Settings updated successfully!'));
                }else{
                    alert(l('Some error occured! Please try again.'));
                }
            }
        });
        return false;
    });


    $('.form_is_display_tooltip').on('submit', function (e) {
        e.preventDefault();
        var post_data = {
            'is_display_tooltip' : $('input[name="is_display_tooltip"]').prop('checked') ? 1 : 0
        };
        $.ajax({
            type   : "POST",
            url    : getBaseURL() + 'settings/company/update_display_tooltip_AJAX',
            data   : post_data,
            dataType: "json",
            success: function (data) {
                if(data.status){
                    alert(l('Settings updated successfully!'));
                }else{
                    alert(l('Some error occured! Please try again.'));
                }
            }
        });
        return false;
    });

    $('.form_auto_no_show').on('submit', function (e) {
        e.preventDefault();
        var post_data = {
            'auto_no_show' : $('input[name="auto_no_show"]').prop('checked') ? 1 : 0
        };
        $.ajax({
            type   : "POST",
            url    : getBaseURL() + 'settings/company/update_no_show_AJAX',
            data   : post_data,
            dataType: "json",
            success: function (data) {
                if(data.status){
                    alert(l('Settings updated successfully!'));
                }else{
                    alert(l('Some error occured! Please try again.'));
                }
            }
        });
        return false;
    });

    $('.form_feature_settings').on('submit', function (e) {
        e.preventDefault();

        var width = $("body").width();

        var daysBeforeToday = Math.round(width / 400);
        var daysAfterToday = Math.round(width / 60);

        var calendarDays = parseInt(daysBeforeToday + daysAfterToday);

        var post_data = {
            'is_total_balance_include_forecast' : $('input[name="is_total_balance_include_forecast"]').prop('checked') ? 1 : 0,
            'ui_theme' : $('#ui_theme').val(),
            'is_display_tooltip' : $('input[name="is_display_tooltip"]').prop('checked') ? 1 : 0,
            'auto_no_show' : $('input[name="auto_no_show"]').prop('checked') ? 1 : 0,
            'book_over_unconfirmed_reservations' : $('input[name="book_over_unconfirmed_reservations"]').prop('checked') ? 1 : 0,
            'send_invoice_email_automatically' : $('input[name="send_invoice_email_automatically"]').prop('checked') ? 1 : 0,
            'ask_for_review_in_invoice_email' : $('input[name="ask_for_review_in_invoice_email"]').prop('checked') ? 1 : 0,
            'hide_decimal_places' : $('input[name="hide_decimal_places"]').prop('checked') ? 1 : 0,
            'redirect_to_trip_advisor' : $('input[name="redirect_to_trip_advisor"]').prop('checked') ? 1 : 0,
            'tripadvisor_link' : $('input[name="tripadvisor_link"]').val(),
            'automatic_email_confirmation' : $('input[name="automatic_email_confirmation"]').prop('checked') ? 1 : 0,
            'automatic_email_cancellation' : $('input[name="automatic_email_cancellation"]').prop('checked') ? 1 : 0,
            'send_booking_notes' : $('input[name="send_booking_notes"]').prop('checked') ? 1 : 0,
            'email_confirmation_for_ota_reservations' : $('input[name="email_confirmation_for_ota_reservations"]').prop('checked') ? 1 : 0,
            'email_cancellation_for_ota_reservations' : $('input[name="email_cancellation_for_ota_reservations"]').prop('checked') ? 1 : 0,
            'allow_non_continuous_bookings' : $('input[name="allow_non_continuous_bookings"]').prop('checked') ? 1 : 0,
            'maximum_no_of_blocks' : $('input[name="maximum_no_of_blocks"]').val(),
            'make_guest_field_mandatory' : $('input[name="make_guest_field_mandatory"]').prop('checked') ? 1 : 0,
            'payment_capture' : $('#payment_capture').val(),
            'include_cancelled_noshow_bookings' : $('input[name="include_cancelled_noshow_bookings"]').prop('checked') ? 1 : 0,
            'force_room_selection' : $('input[name="force_room_selection"]').prop('checked') ? 1 : 0,
            'hide_forecast_charges' : $('input[name="hide_forecast_charges"]').prop('checked') ? 1 : 0,
            'send_copy_to_additional_emails' : $('input[name="send_copy_to_additional_emails"]').prop('checked') ? 1 : 0,
            'additional_company_emails' : $('input[name="additional_company_emails"]').val(),
            'automatic_feedback_email' : $('input[name="automatic_feedback_email"]').prop('checked') ? 1 : 0,
            'avoid_dmarc_blocking' : $('input[name="avoid_dmarc_blocking"]').prop('checked') ? 1 : 0,
            'allow_free_bookings' : $('input[name="allow_free_bookings"]').prop('checked') ? 1 : 0,
            'default_charge_name' : $('input[name="default_charge_name"]').val(),
            'default_room_singular' : $('input[name="default_room_singular"]').val(),
            'default_room_plural' : $('input[name="default_room_plural"]').val(),
            'default_room_type' : $('input[name="default_room_type"]').val(),
            'date_format' : $('#date_format').val(),
            'default_checkin_time' : $('#default_checkin_time').val(),
            'default_checkout_time' : $('#default_checkout_time').val(),
            'enable_hourly_booking' : $('input[name="enable_hourly_booking"]').prop('checked') ? 1 : 0,
            'enable_api_access' : $('input[name="enable_api_access"]').prop('checked') ? 1 : 0,
            'customer_modify_booking' : $('input[name="customer_modify_booking"]').prop('checked') ? 1 : 0,
            'booking_cancelled_with_balance' : $('input[name="booking_cancelled_with_balance"]').prop('checked') ? 1 : 0,
            'hide_room_name' : $('input[name="hide_room_name"]').prop('checked') ? 1 : 0,
            'enable_new_calendar' : $('input[name="enable_new_calendar"]').prop('checked') ? 1 : 0,
            'restrict_booking_dates_modification' : $('input[name="restrict_booking_dates_modification"]').prop('checked') ? 1 : 0,
            'restrict_checkout_with_balance' : $('input[name="restrict_checkout_with_balance"]').prop('checked') ? 1 : 0,
            'show_guest_group_invoice' : $('input[name="show_guest_group_invoice"]').prop('checked') ? 1 : 0,
            'restrict_cvc_not_mandatory' : $('input[name="restrict_cvc_not_mandatory"]').prop('checked') ? 1 : 0,
            'restrict_edit_after_checkout' : $('input[name="restrict_edit_after_checkout"]').prop('checked') ? 1 : 0,
            'calendar_days' :  $('input[name="calendar_days"]').val() == calendarDays ? calendarDays : $('input[name="calendar_days"]').val(),
            'allow_change_previous_booking_status' : $('input[name="allow_change_previous_booking_status"]').prop('checked') ? 1 : 0,
        };
        $.ajax({
            type   : "POST",
            url    : getBaseURL() + 'settings/company/update_features_AJAX',
            data   : post_data,
            dataType: "json",
            success: function (data) {
                if(data.status){
                    alert(l('Settings updated successfully!'));
                }else{
                    alert(l('Some error occured! Please try again.'));
                }
            }
        });
        return false;
    });


    $('.api_access_settings').on('submit', function (e) {
        e.preventDefault();
        var post_data = {
            'enable_api_access' : $('input[name="enable_api_access"]').prop('checked') ? 1 : 0,
        };
        $.ajax({
            type   : "POST",
            url    : getBaseURL() + 'settings/company/update_api_AJAX',
            data   : post_data,
            dataType: "json",
            success: function (data) {
                if(data.status){
                    alert(l('Settings updated successfully!'));
                }else{
                    alert(l('Some error occured! Please try again.'));
                }
            }
        });
        return false;
    });

    innGrid.toggleMaximumNumberBlocks();
    $("input[name=allow_non_continuous_bookings]").on("click", function() {
        innGrid.toggleMaximumNumberBlocks();
    });

    innGrid.toggleHideForecastCharges();
    $("input[name=hide_forecast_charges]").on("click", function() {
        innGrid.toggleHideForecastCharges();
    });

    innGrid.toggleEnableApiAccess();
    $("input[name=enable_api_access]").on("click", function() {
        innGrid.toggleEnableApiAccess();
    });

    $('#save-all-booking-fields-button').on("click", function () { 
        innGrid.saveAllBookingEngineFields();
    });
});

innGrid.generateNewAPIKey = function() {
    var api_key = innGrid.generateAPIKey();

    $.ajax({
        type   : "POST",
        url    : getBaseURL() + 'settings/company/insert_company_api_key',
        data   : {api_key: api_key},
        dataType: "json",
        success: function (data) {
            console.log('data ',data);
            if(data.success)
                $('#api_key').val(data.response.key);
        }
    });
}

innGrid.toggleEnableApiAccess = function() {
    if($("input[name='enable_api_access']:checked").length > 0) {
        innGrid.generateNewAPIKey();
        $(".api-key").show();
    } else {
        $(".api-key").hide();
    }
}

innGrid.toggleMaximumNumberBlocks = function() {
    if($("input[name='allow_non_continuous_bookings']:checked").length > 0)
    {
        $(".max-number-blocks").show();
    }
    else
    {
        $(".max-number-blocks").hide();
    }
}

innGrid.toggleHideForecastCharges = function() {
    if($("input[name='hide_forecast_charges']:checked").length > 0)
    {
        $("input[name='is_total_balance_include_forecast']").prop("checked", false);
        $("input[name='is_total_balance_include_forecast']").prop("disabled", true);
    }
    else
    {
        $("input[name='is_total_balance_include_forecast']").prop("disabled", false);
    }
}

innGrid.generateAPIKey = function() {

    function s4() {
        return Math.floor((1 + Math.random()) * 0x10000)
          .toString(16)
          .substring(1);
    }
    
    return s4() + s4() + s4() + s4() +s4() + s4() + s4() + s4();
}

innGrid.saveAllBookingEngineFields = function () {
    var updatedBookingEngineFields = {};
    $(".booking-field-tr").each(function()
    {
        var bookingFieldTr = $(this);
        var bookingFieldId = bookingFieldTr.attr('id');
        
        updatedBookingEngineFields[bookingFieldId] = {
            id: bookingFieldId,
            show_on_booking_form: (bookingFieldTr.find('[name="show_on_booking_form"]').prop('checked')) ? 1 : 0,
            is_required: (bookingFieldTr.find('[name="is_required"]').prop('checked')) ? 1 : 0 
        };
    });

    $.post(getBaseURL() + 'settings/integrations/update_booking_engine_fields', {
            updated_booking_engine_fields: updatedBookingEngineFields
        }, function (result) {
            if(result.success)
            {
                alert(l('All booking engine fields saved'));
            }
            else
            {
                alert(result.error);
            }
    }, 'json');
}

// Update registration card form settings
$('.form_registration_card_settings').on('submit', function (e) {
    e.preventDefault();
    var post_data = {
        'show_logo_on_registration_card' : $('input[name="show_logo_on_registration_card"]').prop('checked') ? 1 : 0,
        'show_rate_on_registration_card' : $('input[name="show_rate_on_registration_card"]').prop('checked') ? 1 : 0
    };
    $.ajax({
        type   : "POST",
        url    : getBaseURL() + 'settings/reservations/update_registration_card_settings_AJAX',
        data   : post_data,
        dataType: "json",
        success: function (data) {
            if(data.status){
                alert(l('Settings updated successfully!'));
            }else{
                alert(l('Some error occured! Please try again.'));
            }
        }
    });
    return false;
});