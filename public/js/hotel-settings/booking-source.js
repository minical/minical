innGrid.saveAllBookingSource = function () {
	var updatedBookingSources = {};
    var sort = 0;
    $(".booking-source-tr").each(function()
	{
		var bookingSourceTr = $(this);
		var bookingSourceId = bookingSourceTr.attr('id');
		var bookingSourceName = bookingSourceTr.find('[name="booking-source-name"]').val();
		var bookingCommissionRate = bookingSourceTr.find('[name="commission_rate"]').val();
//        var bookingSourceSortOrder = bookingSourceTr.find('[name="booking-source-sort-order"]').val();
        var bookingSourceSortOrder = sort;
        
		var hidden = '0';
        if(bookingSourceTr.find('[name="booking-source-hidden"]').is(":checked"))
        {
            hidden = '1';
        }
		if (bookingSourceName == 'New Booking Source') {
		
            alert(l('Please fill in booking source name'));
            bookingSourceTr.find('[name="booking-source-name"]').focus();
            return false;
        }

        updatedBookingSources[bookingSourceId] = {
            id: bookingSourceId,
            name: bookingSourceName,
            commission_rate: bookingCommissionRate,
            is_hidden: hidden,
            sort_order: bookingSourceSortOrder
        };
        sort++;
        // alert(bookingSourceName);
    });
    console.log(updatedBookingSources);


    $.post(getBaseURL() + 'settings/reservations/update_booking_sources', {
            updated_booking_sources: updatedBookingSources

        }, function (result) {

            if(result.success)
            {

                alert(l('All booking sources saved'));
            }
            else
            {
                alert(result.error);
            }
    }, 'json');
}

$(function() {

	$('#add-booking-source-button').click(function () {
		$.post(getBaseURL() + 'settings/reservations/get_new_booking_source_div', function (div){
			console.log(div);
			$('#booking-source').append(div);
		});		
	});
	

	$('#save-all-booking-source-button').on("click", function () {	
		innGrid.bookingSourceSavedCount = 0;		
		innGrid.saveAllBookingSource();
	});

	$(document).on('click', '.delete-booking-source-button', function () {		
		var that = this;
		//Set custom buttons for delete dialog
		$("#confirm_delete_dialog")
		.html(l('Are you sure ?'))
		.dialog({
			title:(l('Delete booking Source')),
			buttons: {				
				"Confirm Delete":function() {					
					$.post(getBaseURL() + 'settings/reservations/delete_booking_source', {
						id: $(that).parent().parent().attr('id')
						}, function (results) {							
							if (results.isSuccess == true){
									$(that).parent().parent().remove();  //delete line of X button
                            }
                            else {
									//alert(results.message);
								}
							}, 'json');
					$(this).dialog("close");
				},
				"Cancel": function() {
					$(this).dialog("close");
				}
			}
		});
		
		$("#confirm_delete_dialog").dialog("open");
	});
	
    $(document).on('click', '.hide-booking-source-button', function () {
		var that = this;
        var hidden = '0';
        if($(that).is(":checked"))
        {
            hidden = '1';
        }
        var bookingSourceTr = $(this).parents('tr.booking-source-tr');
        var bookingSourceId = bookingSourceTr.attr('id');
        var bookingSourceName = bookingSourceTr.find('[name="booking-source-name"]').val();
        var bookingSourceSortOrder = bookingSourceTr.find('[name="booking-source-sort-order"]').val();
        var bookingCommissionRate = bookingSourceTr.find('[name="commission_rate"]').val();
		//Set custom buttons for delete dialog
		$("#confirm_delete_dialog")
		.html(l('Are you sure ?'))
		.dialog({
			title: l('Hide booking Source'),
			buttons: {				
				"Confirm":function() {					
                    $.post(getBaseURL() + 'settings/reservations/update_booking_sources', {
                            updated_booking_sources: {
                                bookingSourceId: {
                                    id: bookingSourceId,
                                    name: bookingSourceName,
                                    commission_rate: bookingCommissionRate,
                                    is_hidden: hidden,
                                    sort_order: bookingSourceSortOrder
                                }
                            }
                        }, function (result) {
                            if(!result.success)
                            {
                                alert(result.error);
                            }
                    }, 'json');
					$(this).dialog("close");
				},
				"Cancel": function() {
					$(this).dialog("close");
				}
			}
		});
		
		$("#confirm_delete_dialog").dialog("open");
	});
    
    $( "#sortable" ).sortable();  
});
