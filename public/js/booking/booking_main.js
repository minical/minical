innGrid.reloadBookings = function () {
	if(innGrid.isOverviewCalendar) {
		if (window.$('#overview_calendar').is('*')) {
			window.$('#overview_calendar').fullCalendar( 'refetchEvents' );
		}
	} else {
		if (window.$('#calendar').is('*')) {
			if(innGrid.calendar && innGrid.calendar.refetchEvents) {
				innGrid.calendar.refetchEvents();
			} else {
				window.$('#calendar').fullCalendar( 'refetchEvents' );
			}
		}
	}

	// Reload booking list
	if (window.$('#booking_list_wrap').is('*')) {
		window.$('#booking_list_wrap').load(window.parent.getBaseURL() + 'booking/show_booking_list');
	}
}

innGrid.reloadCalendar = function () {
	// reload calendar
	// if calendar exists (It doesn't exist in Show All... page)
	if(innGrid.isOverviewCalendar) {
		if (window.$('#overview_calendar').is('*')) {
			//window.$('#overview_calendar').fullCalendar( 'refetchEvents' );
			innGrid.getRooms(innGrid.buildOverViewCalendar);
		}
	} else {
		if (window.$('#calendar').is('*')) {
			if(innGrid.calendar && innGrid.calendar.destroy) {
				innGrid.calendar.destroy();
				$('#calendar').text(l('Loading', true)+'...');
				innGrid.getRooms(innGrid.buildCalendar);
			} else {
				innGrid.getRooms(innGrid.buildCalendar);
			}
		}
	}
}



/**
 * A wizard that helps create a new booking
 *
 * @param startDate
 * @param endDate
 * @param room
 */
innGrid.createNewBooking = function(startDate, endDate, room){
	if (typeof $.fn.openBookingModal !== 'undefined' && $.isFunction($.fn.openBookingModal)) {
		$("body").openBookingModal({
			checkInDate : startDate,
			checkOutDate: endDate,
			roomID      : room && room.room_id ? room.room_id : '',
			roomTypeID  : room && room.room_type_id ? room.room_type_id : room
		});
	}
};

$(function() {
	mixpanel.people.set({
		"$company_name": $(".navbar-brand").html(),
		"$user_email": $("#user_email").html(),
		"$last_login": new Date()
	});

	mixpanel.register({

	});

	mixpanel.identify($("#user_id").val());

	$(document).on('click', '.invoice-button', function (e) {
		e.stopPropagation();
        if(innGrid.hasBookingPermission == '1' || (typeof innGrid.hasBookingPermission === 'undefined'))
        {
            window.location = getBaseURL()+"invoice/show_invoice/"+e.target.title+"#add-row-table";
        }
	});
    
	$(document).on('click', '.booking', function (e) {        
        if(innGrid.hasBookingPermission == '1' || (typeof innGrid.hasBookingPermission === 'undefined'))
        {
            location.hash = this.title; // get the clicked link id
            if (typeof $.fn.openBookingModal !== 'undefined' && $.isFunction($.fn.openBookingModal)) {
	            $.fn.openBookingModal({
	                id: $(this).data("booking-id"),
					selected_room_id: $(this).data("room-id"),
	            });
	        }
        }
	});

	
	$(document).on('hide.bs.modal', "#booking-modal", function (e) {
		// hack to prevent closing inner-modal removing modal-open class in body.
		// when modal-open class is removed from body, scrolling the booking-modal scrolls
		// background, instead of scrolling the modal
		if(innGrid.calendar && innGrid.calendar.refetchEvents) {
			// do nothing if it's new calendar
		} else {
			innGrid.reloadBookings();
			window.location.hash = '';
		}

	});

	

});

//Adding script for booking color change on hover
$(document).on("mouseenter", ".booking_tr", function() {
	var hoverID= $(this).attr("data-booking-id");
	$(".fc-event").removeClass("hover-bg");
	$(".fc-event").each(function(){
		var bookingID= $(this).attr("data-booking-id");
		if(hoverID==bookingID){
			$(this).children("a").addClass("hover-bg");
		}
	});
});

$(document).on("mouseleave", ".booking_tr", function() {
	$(".fc-event>a").removeClass("hover-bg");
});

$('body').on('click', '.btn_calendar_view_bookings', function () {
	$('#overview_calendar').hide();
	$('#calendar').show();
	innGrid.isOverviewCalendar = false;
	innGrid.buildCalendar();
	$('.btn_calendar_view').removeClass('active');
	$('.btn_calendar_view.btn_calendar_view_bookings').addClass('active');

	window.history.pushState({calendar: 'booking-calendar-state'}, "", getBaseURL()+'booking')
});

$('body').on('click', '.btn_calendar_view_overview', function () {
	$('#overview_calendar').show();
	$('#calendar').hide();
	innGrid.isOverviewCalendar = true;
	innGrid.buildOverViewCalendar();
	$('.btn_calendar_view').removeClass('active');
	$('.btn_calendar_view.btn_calendar_view_overview').addClass('active');

	window.history.pushState({calendar: 'booking-calendar-state'},"",getBaseURL()+'booking/overview')
});

window.onpopstate = function(e){
	if(e.state && e.state.calendar === 'booking-calendar-state') { // state data available
		window.location.reload();
	}
}

function getCalendarOverviewData (start, end, callback) {
    var channel = -1;

	// copy dates so we don't alter them
	
	var start_date = start.getFullYear()+'-'+("0" + (start.getMonth() + 1)).slice(-2)+'-'+("0" + (start.getDate())).slice(-2);
    var end_date = end.getFullYear()+'-'+("0" + (end.getMonth() + 1)).slice(-2)+'-'+("0" + (end.getDate())).slice(-2);
    
	
	var param = {
        channel: channel,
        start_date: start_date,
        end_date: end_date,
		filter_can_be_sold_online: false
    };
	
	if(typeof get_bookings_overview_AJAX_request !== "undefined" && get_bookings_overview_AJAX_request)
		get_bookings_overview_AJAX_request.abort();

	get_bookings_overview_AJAX_request = $.ajax({
		type: "GET",
		url: getBaseURL()+'room/get_room_type_availability_AJAX',
		data: param,
		dataType: "json",
		success: function(availabilityObjects){

			var bookings = new Array();
			var total = {};

			availabilityObjects['total'] = {
				acronym: "Total",
				availability: [],
				id: "total",
				name: "Total",
			};

			for (var room_type_id in availabilityObjects) {
				var availabilities = availabilityObjects[room_type_id].availability;
				availabilityObjects['total'].availability = availabilities;
				var room_type_index = $('.fc-day-content[data-room_type_id="'+(room_type_id)+'"]').parents('tr').index();
				for (var i in availabilities) {
					var availability = availabilities[i];
					
					for (var date = new Date(availability.date_start + " 00:00:00"); date < new Date(availability.date_end + " 00:00:00"); date = new Date(date.getTime()+(24*60*60*1000))) {
						
						var date_str = date.getFullYear()+'-'+("0" + (date.getMonth() + 1)).slice(-2)+'-'+("0" + (date.getDate())).slice(-2);

						if (room_type_id == 'total') {
							availability.availability = total['td.cell-'+date_str].availability;
							availability.inventory_sold = total['td.cell-'+date_str].inventory_sold;
							availability.max_availability = total['td.cell-'+date_str].max_availability;
							availability.status = total['td.cell-'+date_str].status;
						} else {
							total['td.cell-'+date_str] = total['td.cell-'+date_str] || {
								'availability': 0,
								'inventory_sold': 0,
								'max_availability': 0,
								'status': 1
							};
							total['td.cell-'+date_str].availability += parseInt(availability.availability);
							total['td.cell-'+date_str].inventory_sold += parseInt(availability.inventory_sold);
							total['td.cell-'+date_str].max_availability += parseInt(availability.max_availability);
						}

						var availabilityData = {
							'availability': availability.availability,
							'sold': availability.inventory_sold,
							'total': availability.max_availability,
							'status': availability.closeout_status
						};

						$('#overview_calendar').find('#CalendarTable')
                                .find('tr.fc-week'+room_type_index)
                                .find('td.cell-'+date_str+'-'+room_type_index)
                                .find('.fc-day-content > div')
								.text(availability.availability)
								.data(availabilityData);

					   if (availability.availability == 0) {
							$('#overview_calendar').find('#CalendarTable')
									.find('tr.fc-week'+room_type_index)
									.find('td.cell-'+date_str+'-'+room_type_index)
									.addClass('cell-zero-availability');
					   } else {
							$('#overview_calendar').find('#CalendarTable')
									.find('tr.fc-week'+room_type_index)
									.find('td.cell-'+date_str+'-'+room_type_index)
									.removeClass('cell-zero-availability');
					   }
					}
				}
				
				if (availability && availability.availability && availability.max_availability && date) {
					
					var date_str = date.getFullYear()+'-'+("0" + (date.getMonth() + 1)).slice(-2)+'-'+("0" + (date.getDate())).slice(-2);

					if (room_type_id == 'total') {
						availability.availability = total['td.cell-'+date_str].availability;
						availability.inventory_sold = total['td.cell-'+date_str].inventory_sold;
						availability.max_availability = total['td.cell-'+date_str].max_availability;
						availability.status = total['td.cell-'+date_str].status;
					} else {
						total['td.cell-'+date_str] = total['td.cell-'+date_str] || {
							'availability': 0,
							'inventory_sold': 0,
							'max_availability': 0,
							'status': 1
						};
						total['td.cell-'+date_str].availability += parseInt(availability.availability);
						total['td.cell-'+date_str].inventory_sold += parseInt(availability.inventory_sold);
						total['td.cell-'+date_str].max_availability += parseInt(availability.max_availability);
					}

					var availabilityData = {
						'availability': availability.availability,
						'sold': availability.inventory_sold,
						'total': availability.max_availability,
						'status': availability.closeout_status
					};

					$('#overview_calendar').find('#CalendarTable')
                                .find('tr.fc-week'+room_type_index)
                                .find('td.cell-'+date_str+'-'+room_type_index)
                                .find('.fc-day-content > div').text(availability.availability)
								.attr(availabilityData);
					
					if (availability.availability == 0) {
						$('#overview_calendar').find('#CalendarTable')
								.find('tr.fc-week'+room_type_index)
								.find('td.cell-'+date_str+'-'+room_type_index)
								.addClass('cell-zero-availability');
					} else {
						$('#overview_calendar').find('#CalendarTable')
								.find('tr.fc-week'+room_type_index)
								.find('td.cell-'+date_str+'-'+room_type_index)
								.removeClass('cell-zero-availability');
					}
					
				}
			}
			bookings.push({total});
			if(callback !== undefined && innGrid.isOverviewCalendar)
			{
				callback(bookings);
			}
		},
		error: innGrid.ajaxError
	 });

};
// console.log('key',innGrid.companyAPIKey);
window.parent.postMessage({
	'minical-api-key': innGrid.companyAPIKey,
	'minical-company-id': innGrid.companyID
},"*");
