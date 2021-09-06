var innGrid = innGrid || {};
innGrid.updateAvailabilities = function(start_date, end_date, room_type_id, channel_id) {
	$.ajax({
	    type   : "POST",
	    url    : getBaseURL() + "channel_manager/update_availabilities",
	    data   : {
	        start_date: start_date,
	        end_date: end_date,
                room_type_id: room_type_id ? room_type_id : '',
                channel_id: channel_id ? channel_id : ''
	    },
	    dataType: "json",
	    success: function (data) {
	        console.log(data);
            $("#loading_avail_img").hide();
	    }
	});
}

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

$(".configure-new-channel").on("click", function() {
    var otaId = $(this).data('ota_id');
    var data = {
        ota_id: otaId,
        ota_hotel_id: $('#ota-'+otaId).find('input[name="ota_hotel_id"]').val(),
        username: $('#ota-'+otaId).find('input[name="username"]').val(),
        password: $('#ota-'+otaId).find('input[name="password"]').val()
    };

    if(data.ota_hotel_id == ''){
        alert(l('Please enter Hotel ID'));
        return false;
    }
    if(data.username == ''){
        alert(l('Please enter Username'));
        return false;
    }
    if(data.password == ''){
        alert(l('Please enter Password'));
        return false;
    }

    $.ajax({
        type: "POST",
        url: getBaseURL() + "settings/integrations/configure_roomsy_channel_manager_AJAX",
        data: {
            data: data
        },
        success: function (response) {
            innGrid.updateRates("","","");
            alert(l('Channel Configuration Saved!'));
            $('#configure-ota-'+otaId).addClass('hidden');
            $('#manage-ota-'+otaId).removeClass('hidden');
            location.reload();
        }
    });    
});
$('.manage-channel').on("click", function() {
    var otaId = $(this).data('ota_id');
    window.location.href = getBaseURL() + "settings/integrations/roomsy_channel_manager/manage/"+otaId;
});
$(".edit-channel-configuration").on("click", function() {
    var otaId = $(this).data('ota_id');
    $('#manage-ota-'+otaId).addClass('hidden');
    $('#configure-ota-'+otaId).removeClass('hidden');
});

$(".deconfigure-channel").on("click", function() {
    var otaId = $(this).data('ota_id');
    $('#ota-'+otaId).find('input[name="ota_hotel_id"], input[name="username"], input[name="password"]').val(''),
    $.ajax({
        type: "POST",
        url: getBaseURL() + "settings/integrations/deconfigure_roomsy_channel_manager_AJAX",
        data: {
            ota_id: $(this).data('ota_id')
        },
        success: function (response) {
            innGrid.updateRates("","","");
            alert(l('Channel Configuration Updated!'));
            location.reload();
        }
    });    
});
    
jQuery.expr[':'].contains = function(a, i, m) {
  return jQuery(a).text().toUpperCase()
      .indexOf(m[3].toUpperCase()) >= 0;
};

$(".login-myallocator").on("click", function() {
    $(this).text('Loading..').attr('disabled', true);
    var otaId = $(this).data('ota_id');
    var data = {
        ota_id: otaId,
        ota_hotel_id: $('#ota-'+otaId).find('input[name="ota_hotel_id"]').val(),
        username: $('#ota-'+otaId).find('input[name="username"]').val(),
        password: $('#ota-'+otaId).find('input[name="password"]').val()
    };
    $.ajax({
        type: "POST",
        url: getBaseURL() + "settings/integrations/myallocator_login_AJAX",
        data: {
            data: data
        },
        dataType: 'json',
        success: function (response) {
            $(".login-myallocator").text('Sign in').attr('disabled', false);
            if(response.user_status == 'all_set'){
                window.location.href = getBaseURL() + "settings/integrations/myallocator/manage/"+otaId;
            }
            else if(response.user_status == 'ota_property_not_set' && !response.user_status_err_msg){
                window.location.reload();
            }
            else {
                alert(response['user_status_err_msg']);
            }
        }
    });    
});

$('.property-mapping-myallocator').click(function(){
    $(this).text('Loading..').attr('disabled', true);
    var propertyId = $('select.myallocator_property_mapping').val(); 
    var otaId = $(this).data('ota_id');
    $.ajax({
         type   : "POST",
         url    : getBaseURL() + "settings/integrations/myallocator_property_mapping_AJAX",
         data   : {
             propertyId: propertyId,
             ota_id: otaId
         },
         dataType: "json",
         success: function (data) {
             $(".login-myallocator").text('Save').attr('disabled', false);
             innGrid.updateRates("","","");
             alert(l('Channel Configuration Updated!'));
             window.location.reload();
         }
    });
});

$('.save_myallocator_mapping').on('submit', function(e){
    e.preventDefault();
    $('.save_myallocator_mapping_button').val('Loading..').attr('disabled', true);
    var mappingData = [];
    var otaId = $(this).data('ota_id');
    $('.myallocator_room_types').each(function(){
        mappingData.push({
            "myallocator_room_type_id": $(this).data('ota_room_id'),
            "roomsy_room_type_id": $(this).find('select[name="roomsy_room_type"]').val(),
            "roomsy_rate_plan_id": $(this).find('select[name="roomsy_rate_plan"]').val()
        });
    });
    $.ajax({
	    url    : getBaseURL() + "settings/integrations/save_myallocator_mapping_AJAX",
	    type   : "POST",
        dataType: "json",
        data   : { ota_id: otaId, mapping_data : mappingData},
	    success: function (data) {
            console.log(data);
            $('.save_myallocator_mapping_button').val('Save').attr('disabled', false);
            innGrid.updateRates("","","");
            alert(l('Channel Configuration Updated!'));
        },
        error: function (data, error) {
            console.log(data);
            $('.save_myallocator_mapping_button').val('Save').attr('disabled', false);
        }
	});
    return false;
});

$(".logout-myallocator").on("click", function() {
    $button = $(this);
    $button.text('Loading..').attr('disabled', true);
    var otaId = $button.data('ota_id');
    $.ajax({
        type: "POST",
        url: getBaseURL() + "settings/integrations/deconfigure_roomsy_channel_manager_AJAX",
        data: {
            ota_id: otaId
        },
        dataType: 'json',
        success: function (response) {
            location.reload();
        }
    });    
});
function displayRelatedRatePlansMyallcator () {
	$('tr.myallocator_room_types').each(function(){
		var room_type_id = $(this).find('select[name="roomsy_room_type"]').val();
		$(this).find('select[name="roomsy_rate_plan"]').find('option').hide();
		$(this).find('select[name="roomsy_rate_plan"]').find('option:first-child').show();
		$(this).find('select[name="roomsy_rate_plan"]').find('option[data-room_type_id="'+room_type_id+'"]').show();
	});
}
$('.myallocator_room_types select[name="roomsy_room_type"]').on('change', displayRelatedRatePlansMyallcator);

$('.manage-agoda-channel').on("click", function() {
    var otaId = $(this).data('ota_id');
    window.location.href = getBaseURL() + "settings/integrations/agoda/manage/"+otaId;
});

$(function(){
    if($(".integrations").length > 0 && $(".integrations").attr('integrations_enabled') != 1){
        //alert("OTA integration is available for our Premium subscribers. \n\nIf you are interested in integrating your OTA(Expedia, Booking.com etc.) rates and reservations with Minical contact us at support@minical.io");
    }
	
	displayRelatedRatePlansMyallcator();
});
