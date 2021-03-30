var otaId = $('.linker-wrap').data("ota_id");
var linkData, 
	roomTypesAndRates;

// update room type DDL
$.post(getBaseURL()+'settings/integrations/get_link_data/' + otaId , function(data) {
    console.log(data);
	linkData = data.link_data;
	roomTypesAndRates = data.room_types_and_rates;
  
	// construct PMS room type <select> for each of the OTA's room types
	$(".room-type-panel").each(function () 
	{
		var roomType = $(this);
		var otaRoomTypeID = roomType.find(".ota-room-type").data("id");
						
		var pmsRoomTypeSelect = $('<select/>',
					{
						class:"room-type-select channel_manager"
					})
					.on('change', updateRatePlanDDL);

		$('<option />', {
			value: '0', 
			text: '-- not selected --'
		}).appendTo(pmsRoomTypeSelect);

		// construct <select> for innGrid room types
		for(var key in roomTypesAndRates) 
		{
            $('<option />', {
				value: key,
				text: roomTypesAndRates[key].room_type_name + ' (' + key + ')',
				selected: otaRoomTypeID in linkData && key === linkData[otaRoomTypeID].pms_room_type_id,
                "data-pms-room-type-occupency": (otaId == 4) ? roomTypesAndRates[key].room_type_max_occupency : ""
			}).appendTo(pmsRoomTypeSelect);
		}
		
		roomType.find('.pms-room-type').html(pmsRoomTypeSelect);
		$('.room-type-select').trigger('change'); // so it calls the updateRatePlanDDL()
	
	});

	setTimeout(function() {$('.room select').css({opacity: 1})}, 0);
}, 'json');


function updateRatePlanDDL() {
	var pmsRoomTypeID = $(this).val();
	var ratePlanSelect = $('<select/>', {class: "channel_manager"});

	$('<option />', {value: '0', text: '-- not selected --'}).appendTo(ratePlanSelect);

	if(pmsRoomTypeID in roomTypesAndRates) {
		var ratePlans = roomTypesAndRates[pmsRoomTypeID].rate_plans;

		for(var key in ratePlans) 
		{
			$('<option />', {
				value: ratePlans[key].rate_plan_id,
				text: ratePlans[key].rate_plan_name + ' (' + ratePlans[key].rate_plan_id + ')',
                "data-rate-plan-currency": ratePlans[key].rate_plan_currency_code
			}).appendTo(ratePlanSelect);
		}
	}

	var room = $(this).parents('.room-type-panel');
	room.find('.pms-rate-plan').html(ratePlanSelect);

	var otaRoomType = room.find('.ota-room-type').data("id");
	
	room.find('.rate-plan').each(function() {
		if (!(otaRoomType in linkData)) return;

		var otaRatePlan = $(this).find('.ota-rate-plan').data('id');
		var pmsRatePlan = '';
    if(linkData[otaRoomType].rate_plans){
      pmsRatePlan = linkData[otaRoomType].rate_plans[otaRatePlan];
    }
		if (pmsRatePlan) {
			$(this).find('.pms-rate-plan select option[value=' + pmsRatePlan + ']')
				.attr('selected', 'selected');
		}
	});
}

$('.save-all, #sync-occupancy-button').click(function() {
	var button = $(this);
	button.attr('disabled', 'disabled');
    
    if(otaId == 4){
        var errorMsg = "";
    }
  
	var payload = {
		room_types: {},
		rate_plans: {}
	}

    var ratePlanCurrencyAr = [];
    
    var otaRoomMaxOccupancy = {
		rate_plans: {}
    };

    var expediaPricingModel = $('select[name="expedia_pricing_model"]').val();
    if(expediaPricingModel == '')
    {
        alert(l('Please Select Pricing Model.'));
        button.removeAttr('disabled');
        return false;
    }
        
	$('.room-type-panel').each(function() {
        var otaRoomType = $(this).find('.ota-room-type')[0].dataset.id
		var pmsRoomType = $(this).find('.pms-room-type select').val();
		payload.room_types[otaRoomType] = pmsRoomType
        payload.rate_plans[otaRoomType] = {};
		otaRoomMaxOccupancy.rate_plans[otaRoomType] = {};
        
        if(otaId == 2){
            otaRoomMaxOccupancy.rate_plans[otaRoomType] = {};
        }
        
		$(this).find('.rate-plan').each(function() {
                var otaRatePlan = $(this).find('.ota-rate-plan').data('id');
                var pmsRatePlan = $(this).find('.pms-rate-plan select').val();
                var minicalratePlanCurrency = $(this).find('option[value="'+pmsRatePlan+'"]').attr('data-rate-plan-currency');
            
                if(minicalratePlanCurrency != undefined)
                    ratePlanCurrencyAr.push(minicalratePlanCurrency);
            
                payload.rate_plans[otaRoomType][otaRatePlan] = pmsRatePlan;

            if(otaId == 2){
                var occupancyAr = [];
                occupancyAr.push({
                    ota_room_max_occpancy:$(this).find('.ota-rate-plan').data('ota-room-max-occupancy'), 
                    ota_lead_occupancy:$(this).find('.ota-rate-plan').data('ota-lead-occupancy'),
                    adult1_adult2_rate_diff:$(this).find('.adult_diff').find('.adult1_adult2_rate_diff').val(),
                    adult2_adult3_rate_diff:$(this).find('.adult_diff').find('.adult2_adult3_rate_diff').val(),
                    adult3_adult4_rate_diff:$(this).find('.adult_diff').find('.adult3_adult4_rate_diff').val()
                });
                otaRoomMaxOccupancy.rate_plans[otaRoomType][otaRatePlan] = occupancyAr;
            }
            if(otaId == 3){
                var occupancyAr = [];
                occupancyAr.push({
                    ota_lead_occupancy:$(this).find('.ota-rate-plan').data('ota-lead-occupancy')
                });
                otaRoomMaxOccupancy.rate_plans[otaRoomType][otaRatePlan] = occupancyAr;
            }
            
		});
        
        if(otaId == 4 && pmsRoomType != 0)
        {
            var otaRoomTypeOccupancy = $(this).find('.ota-room-type').attr('data-ota-room-type-max-occupancy');
            var minicalRoomTypeOccupancy = $(this).find('option[value="'+pmsRoomType+'"]').attr('data-pms-room-type-occupency');
            var roomName = $(this).find('option[value="'+pmsRoomType+'"]').text();
            if(otaRoomTypeOccupancy != minicalRoomTypeOccupancy){
                errorMsg = "Error: Agoda room occupancy ("+otaRoomTypeOccupancy+") and minical room occupancy ("+minicalRoomTypeOccupancy+") for room "+roomName+" must be same.";
            }
        }
	});

    if(otaId == 4)
    {
        var otaCompanyCurrency = $(this).parents().find('.linker-wrap').attr('data-ota-currency-code');
        $.each(ratePlanCurrencyAr, function(index, ratePlanCurrency){
            if(ratePlanCurrency != otaCompanyCurrency)
            {
                errorMsg = 'Error: All minical rate plan currency ('+ratePlanCurrency+ ') and Agoda hotel currency ('+otaCompanyCurrency+') must be same.';
            }
        });
       
        if(errorMsg != "")
        {
            alert(errorMsg);
            button.removeAttr('disabled');
            return;
        }
    }
    var bookingDotComRateType = '';
    if(otaId == 2)
    {
        bookingDotComRateType = $('.booking-dot-com-rate-type select[name="booking_dot_rate_type"]').val();
        // if(bookingDotComRateType == 'occupancy_rate' && is_current_user_superadmin)
        // {
        //     alert('Please also change the pricing model for this hotel from https://connect.booking.com/pricing-type-switching');
        // }
        // else if(bookingDotComRateType == 'occupancy_rate')
        // {
        //     alert('Please contact support to switch to OBP model.');
        //     button.removeAttr('disabled');
        //     return false;
        // }
    }
	$.post(
		getBaseURL() + 'settings/integrations/save_links/'+otaId,
        {roomtypeRateplanInfo: payload, otaOccupancyInfo: otaRoomMaxOccupancy, bookingDotComRateType: bookingDotComRateType, expediaPricingModel:expediaPricingModel},
		function(res) {
            console.log('otaRoomMaxOccupancy ',otaRoomMaxOccupancy);
			button.removeAttr('disabled');
		}
	).always(function(res) {
        innGrid.updateRates("","","");
        alert(l('Data saved successfully!'));


        // if(otaId == 2) {
        //     if($('select[name="booking_dot_rate_type"]').attr('old-pricing_model') != bookingDotComRateType) {
        //         innGrid.updatePricingModel(bookingDotComRateType);
        //     }
        // }
	});
});

innGrid.updateRates = function(start_date, end_date, rate_plan_id) {
    
    $.ajax({
        type   : "POST",
        url    : getBaseURL() + "channel_manager/update_rates",
        data   : {
            start_date: start_date,
            end_date: end_date,
            rate_plan_id: rate_plan_id
        },
        dataType: "json",
        success: function (data) {
            console.log(data);
        }
    });
}

innGrid.updatePricingModel = function(booking_dot_com_rate_type) {
    
	$.ajax({
	    type   : "POST",
	    url    : getBaseURL() + "channel_manager/update_pricing_model",
	    data   : {
	        booking_dot_com_rate_type: booking_dot_com_rate_type
	    },
	    dataType: "json",
	    success: function (data) {
	        console.log(data);
	    }
	});
}

$(document).ready(function(){
    if(otaId == 2){
        $('.booking-dot-com-rate-type select[name="booking_dot_rate_type"]').on("change", function(){
            var oldPricingModel = $('select[name="booking_dot_rate_type"]').data('old-pricing_model');

            if($(this).val() == "derived_rate")
            {
                //$('#extra-derived-fields').removeClass("hidden");
                $('.adult_diff').removeClass("hidden");
                $('.show_occupancy').removeClass("hidden");
            }
            else if($(this).val() == "occupancy_rate")
            {
                if(is_current_user_superadmin || is_current_user_admin)
                {
                    alert(l('Please also change the pricing model for this hotel from')+'https://connect.booking.com/pricing-type-switching');
                }
                else
                {
                    alert(l('Please contact support to switch to OBP model.'));
                    $('.booking-dot-com-rate-type select[name="booking_dot_rate_type"]').val(oldPricingModel);
                }
            }
            else
            {
                //$('#extra-derived-fields').addClass("hidden");
                $('.adult_diff').addClass("hidden");
                $('.show_occupancy').addClass("hidden");
            }
        });
    } 
    var room_id = '';
    $('body').on('click', '.room-check', function(){
        
        room_id = $(this).val();
        if($(this).is(":checked"))
        {
            $('.rate-checked-'+room_id).attr('disabled', false);
        }
        else
        {
            $('.rate-checked-'+room_id).attr('disabled', true);
            $('.rate-checked-'+room_id).attr('checked', false);
        }
    });
    
    if($(this).is(":checked"))
    {
        $('.rate-checked-'+room_id).attr('disabled', false);
    }
    else
    {
        $('.rate-checked-'+room_id).attr('disabled', true);
        $('.rate-checked-'+room_id).attr('checked', false);
    }
    
    $('body').on('click', '.rate-check', function(){
        var rate_id = '';
        rate_id = $(this).val();
        
    });
    
    $('body').on('click', '.save-all-siteminder',function() {
	var button = $(this);
//	button.attr('disabled', 'disabled');
        var otaId = button.attr('ota_id');
        var payload = {
		room_types: {},
		rate_plans: {}
	}
        var pmsRoomType = 0;
        var siteminderHotelRegion = $('.siteminder-hotel-region select[name="siteminder_hotel_region"]').val();
        
        if(siteminderHotelRegion == '')
        {
            alert(l('Please Select Region.'));
            return false;
        }
        $(".room-check:checked").each(function() {
		pmsRoomType = $(this).val();
                payload.room_types[pmsRoomType] = pmsRoomType;
                var pmsRatePlan = 0;
                payload.rate_plans[pmsRoomType] = [];
                $('.rate-checked-'+pmsRoomType+":checked").each(function() {
                    payload.rate_plans[pmsRoomType][pmsRatePlan] = $(this).val();
                    pmsRatePlan++;
                });
	});
        
        $.post(getBaseURL() + 'settings/integrations/save_links/'+otaId,
            {roomtypeRateplanInfo: payload, siteminderHotelRegion:siteminderHotelRegion},
                    function(res) {
                            button.removeAttr('disabled');
                    }
            ).always(function(res) {
            innGrid.updateRates("","","");
            alert(l('Data saved successfully!'));
		//console.log(res);
	});
//        console.log(payload);
    });
    
    $('body').on('click', '.full-sync-siteminder',function() {
		var button = $(this);
		button.prop('disabled', true);
            $.post(getBaseURL() + 'channel_manager/update_full_refresh',
            {},
                function(res) {
                    button.prop('disabled', false);
                }
            ).always(function(res) {
            alert(l('Data refreshed successfully!'));
        });
    });
});
