//Add events to new reservation, new checkin, and new out of order and sorts them
$(function() {
	// create new booking
	$(document).on("click","#hide-button",  function() {
		if ($("#hide-button").text() == l("show"))
		{
			$(".booking_table").show();
			$("#hide-button").hide();
			$.get(getBaseURL() + 'user/set_reminder_as_visible',function (data) {			
				$("#hide-button").show();
				$("#hide-button").text(l("hide"));
			}, 'json');
			
		}
		else
		{
			$(".booking_table").hide();
			$("#hide-button").hide();
			$.get(getBaseURL() + 'user/set_reminder_as_hidden',function (data) {			
				$("#hide-button").show();
				$("#hide-button").text(l("show"));
			}, 'json');
			
		}
	});
        
        $('#add_payment_button').click(function(){
            var booking_csv = $('select[name=select-csv-export]').val();
            if (booking_csv == 'download-reservation-csv')
		{
			window.location = getBaseURL()+"booking/download_csv_export/0";
		} else if (booking_csv == 'download-inhouse-csv')
		{
			window.location = getBaseURL()+"booking/download_csv_export/1";
		}
        });
	

	$('[rel="popover"]').popover({ trigger: "hover" });
	
});