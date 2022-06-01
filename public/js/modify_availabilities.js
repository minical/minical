

// Makes jquery datepicker to have limited range. (Greying out invalid selections)
innGrid.customRange = function(input) { 
	// disable the datepicker input field, so only calendar is allowed. 
	//This also prevents keyboard pop up in ipad
	//$(this).attr("disabled", true);

   // Split timestamp into [ Y, M, D, h, m, s ]
	var sd = $("#date_start").val().split(/[-]/);
	var cd = $("#date_start").val().split(/[-]/);
	
	// Apply each element to the Date function
	var current_selling_date = new Date(sd[0], sd[1]-1, sd[2]);
	var check_in_date = new Date(cd[0], cd[1]-1, cd[2]);
	var dateMin = current_selling_date;
	var dateMax = null;	
	
	if (input.id == "date_start" && $("#date_end").datepicker("getDate") != null)
	{
		dateMax = $("#date_end").datepicker("getDate");
		if (dateMin < current_selling_date)
		{
			dateMin = current_selling_date;
		}                       
	}
	else if (input.id == "date_end")
	{
		if ($("#date_start").datepicker("getDate") != null)
		{
			var tempDate = new Date($("#date_start").datepicker("getDate"));			
			dateMin = new Date(tempDate.getTime());
		}
	}

	return {
		minDate: dateMin, 
		maxDate: dateMax
	}; 

}

innGrid.setAvailabilities = function() {

	var otaAvailabilityArray = {};
	$(".ota-availability-data input").each(function() {
		var fieldname = $(this).attr("name");
		
		if ($(this).is(':checkbox'))
		{
			var value = $(this).is(':checked');
		}
		else
		{
			var value = $(this).val();
		}
		otaAvailabilityArray[fieldname] = value;
		$("#loading_avail_img").show();
	});
	
	
	$.post(getBaseURL() + 'room/modify_availabilities_POST',
		otaAvailabilityArray,
		function(data) {
			console.log('data',data[0]);
			if (data[0].status === 'error')
			{
				var errorHtml = '';
				$(data[0].message).each(function(i, v){
					console.log('v', v);
					errorHtml += v+"\n";
				});

				alert(errorHtml);
                
                $("#loading_avail_img").hide();
			}
			else
			{
                innGrid.updateAvailabilities($("#date_start").val(), $("#date_end").val(), otaAvailabilityArray['room_type_ids'], otaAvailabilityArray['channel_id']);
			}
		}, 'json'
	);
}

$(function() {

	$("#date_start").datepicker({
			dateFormat: 'yy-mm-dd',				
			beforeShow: innGrid.customRange
		});

	$("#date_end").datepicker({
		dateFormat: 'yy-mm-dd',				
		beforeShow: innGrid.customRange
	});


	$(".ota-availability-data input").addClass("modified");

	$("#allocate_availabilities_button").on('click', function() {		
		innGrid.setAvailabilities();

			
	});
	
	$("#close_button").on('click', function() {	
		var isInIframe = (window.location != window.parent.location) ? true : false;
		if(isInIframe)
			window.parent.jQuery('#edit_availabilities_dialog').dialog('close');
		else
			window.jQuery('#edit_availabilities_dialog').dialog('close');
	});
});

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
            var isInIframe = (window.location != window.parent.location) ? true : false;
			if(isInIframe)
				window.parent.jQuery('#edit_availabilities_dialog').dialog('close');
			else
				window.jQuery('#edit_availabilities_dialog').dialog('close');
	    },
        error: function(e){
            var isInIframe = (window.location != window.parent.location) ? true : false;
			if(isInIframe)
				window.parent.jQuery('#edit_availabilities_dialog').dialog('close');
			else
				window.jQuery('#edit_availabilities_dialog').dialog('close');
        }
	});
}
