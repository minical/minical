
//If set to true makes script load bookings data every time scope on calendar is changed.
//Set reloadDataEveryRefetch to true if you implemented data saving.
var reloadDataEveryRefetch = true;

//Set to 1 is the check_out_date date is not shown as occuped, 0 - if is.		
var check_out_dateHack = 1;

var bookings = null;
var rooms = [];
var room_names = [];

var selectedRoomDiv, selectedRoomID, selectedRoomTypeAcronym;

//Occurs when an booking is clicked. Gets an event that represents the booking as parameter.
/*obsolete
innGrid.bookingClicked = function(event){
	innGrid.openBooking(event.booking_id);
}
*/

//Occurs when an booking is clicked. Gets an event that represents the booking as parameter.
innGrid.populateRoomEditModal = function(roomDiv){
	if (!roomDiv.hasClass("room-name"))
	{
		alert(l("there's no more rooms"));
		return;
	}

	selectedRoomDiv = roomDiv;
	selectedRoomID = selectedRoomDiv.attr("id");
					
	$.getJSON(getBaseURL() + 'room/get_room_AJAX/'+selectedRoomID, function(room) {

		$("#room-edit-modal").show();

		$("[name='room_edit_room_type_id'] option").each(function() {
	    	var option = $(this);
	    	if (room.room_type_id == option.val()) {
	    		option.prop("selected", true);
		    }
	    });

	    $("[name='room_edit_room_status'] option").each(function() {
	    	var option = $(this);
	    	if (room.status == option.val()) {
	    		option.prop("selected", true);
		    }
	    });

	    $("[name='room_edit_room_name']").val(room.room_name);
	    $("#room-edit-modal").modal('show');
		
	});
}


//Occurs when an booking is resized. Gets an event that represents the booking as parameter.
innGrid.bookingResized = function(event, dayDelta, minuteDelta, revertFunc) {
	var dateFrom1 = formatDate(addDays(cloneDate(event.start), -dayDelta),"yyyy-MM-dd");
	var dateTo1 = formatDate(addDays(cloneDate(event.end || event.start), -dayDelta),"yyyy-MM-dd");
	
	var dateFrom2 = formatDate(event.start,"yyyy-MM-dd");
	var dateTo2 = formatDate(addDays(cloneDate(event.end || event.start), 1),"yyyy-MM-dd");

	// used for update_calendar_booking. The calendar view and php's dates are 1 day off.
	var dateFrom1_actual = formatDate(addDays(cloneDate(event.start), 0),"yyyy-MM-dd");
	var dateTo1_actual = formatDate(addDays(cloneDate(event.end), 0),"yyyy-MM-dd");
	
	var room_id = rooms[event.room].room_id;
	var state = event.state;
	    var date = new Date(dateTo1);
        var newdate = new Date(date);

        newdate.setDate(newdate.getDate() + 1);
    
         var dd = newdate.getDate();
         var mm = newdate.getMonth() + 1;
         var y = newdate.getFullYear();
         if(mm < 10){
             mm = '0'+mm;
         }
         if(dd < 10){
             dd = '0'+dd;
         }
         var dateTo1 =y+'-'+mm+'-'+dd
	if (!confirm(l('Change check-out date from')+" " + dateTo1 +" "+l('to')+" " + dateTo2 + "?")){
		revertFunc();
	} 
	else {
		dateFrom1_actual = dateFrom1_actual + ' 00:00:00';
		dateTo1_actual = dateTo1_actual + ' 00:00:00';
		dateFrom2 = dateFrom2 + ' 00:00:00';
		dateTo2 = dateTo2 + ' 00:00:00';

		$.ajax({
            url: getBaseURL() + 'calendar/resize_booking_room_history',
            type: "POST",
            data: {
                booking_id: event.booking_id,
                room_id: room_id,
                state: state,
                start1: encodeURIComponent(dateFrom1_actual),
                end1: encodeURIComponent(dateTo1_actual),
                start2: encodeURIComponent(dateFrom2),
                end2: encodeURIComponent(dateTo2)
            },
            success: function(data){
                if (data.trim() != "success") {
                    alert(data);
                    info.revert();
                } else {
                    // reload booking_list_wrap and calendar
                    innGrid.reloadBookings();
                    innGrid.updateAvailabilities(
                        dateFrom2,
                        dateTo2
                    );
                }
            }
        });
	}
}

//Creates calendar
innGrid.buildCalendar = function(){

	rooms = Array.from(roomsBookingCalendar);
	roomIds = Object.assign({}, roomIdsBookingCalendar);

	var dateString = $("#sellingDate").val();		
	var date = new Date(dateString.replace(/-/g,'/'));
	var roomEditDialogTitle = l("Edit Room");
	var width = $("body").width();
	var daysBeforeToday = Math.round(width/400);
	var daysAfterToday = Math.round(width/70);
    
    var calendar_options = {
        monthFromBeginningOnly: true, //Set this true if you want calendar always start from the first day
		
		year: date.getFullYear(),
		month: date.getMonth(),
		date: date.getDate(),
		
		//defaultView: 'relative',
		defaultView: 'custom',
		selectable: true,
		selectHelper: true,
		showNumbers: false,
		
		//weekends: false,
		//height: 700,
		//contentHeight: 500,
		//aspectRatio: 2,
		//weekMode: 'variable',
		rowsHeight: '1.8em',
		
		header: {
			left: 'title',
			center: null,
			//center: 'month,agendaWeek,basicWeek,agendaDay,basicDay prevYear,nextYear',
			//right: 'month,relative today prev,next'
			right: 'prev,today,next'
		},
		
		buttonText: {
			prev: '&nbsp;&#9668;&nbsp;',
			next: '&nbsp;&#9658;&nbsp;',
			prevYear: '&nbsp;&lt;&lt;&nbsp;',
			nextYear: '&nbsp;&gt;&gt;&nbsp;',
			today: l('Today'),
			month: 'month',
			//week: 'week',
			relative: 'relative'
			//day: 'day'
		},
		
		editable: true,
		//disableDragging: true,
		//disableResizing: true,
		dragOpacity: .5,
		dragRevertDuration: 100,
		
		allDayText: 'ALLDAY',
		firstHour: 10,
		slotMinutes: 15,
		defaultEventMinutes: 45,
		allDayDefault: true,
		
		//Text format for columnn headers
		columnFormat: "dd<br>ddd",
        timeFormat: "h(:mm)[T]{ - h(:mm)T}",
		titleFormat: "yyyy-MM-dd { -yyyy-MM-dd }",
        
		//today is acquired from looking up DIV
		today: dateString.replace(/-/g,'/'),

		daysBeforeToday: sessionStorage.getItem("beforeDays") != null ? parseInt(sessionStorage.getItem("beforeDays")) : daysBeforeToday,
        daysAfterToday: sessionStorage.getItem("afterDays") != null ? parseInt(sessionStorage.getItem("afterDays")) : daysAfterToday,
	
		eventClick: innGrid.bookingClicked,
        
        //dayClick: innGrid.dayClicked,
		viewChanged: innGrid.viewChanged,
		eventResize: innGrid.bookingResized,
		eventDrop: occupacyMoved,
		eventSources: [getCalendarBookings],
		rooms: rooms,
	};
    if(innGrid.isDisplayTooltip)
    {
        calendar_options['eventMouseover'] = function (data, event, view) {
            var groupId = (data.id_group_booking != 'null') ? "<strong>"+l("group_id")+":</strong> "+data.id_group_booking : '';
            var groupName = (data.group_name != 'null') ? "<strong>"+l("group_name")+":</strong> "+data.group_name : '';
            //var totalAmount =  parseFloat(data.balance) + parseFloat(data.payment_total) ;
            
            // get booking sources text
            var sources = [];
            for(var i = 0; i < innGrid.bookingSources.length; i++)
            {
                sources.push({id: innGrid.bookingSources[i].id, name: innGrid.bookingSources[i].name});
            }
            
            var sourceVal = data.booking_source;
            var sourceText = '';
            $.each(sources, function (i, source) {
                if (source.id == sourceVal)
                {
                    sourceText = source.name;
                }
            });

            var notes = data.booking_notes.length > 120 ? data.booking_notes.substr(0, data.booking_notes.lastIndexOf(' ', 120)) + '...' : data.booking_notes;

            // calculate number of nights
            var checkInDate = moment(data.check_in_date).format('YYYY-MM-DD');
            var checkOutDate = moment(data.check_out_date).format('YYYY-MM-DD');
			var diffDays = moment(checkOutDate).diff(moment(checkInDate), 'days');

            // get staying customers list
            var staying_customers = data.staying_customers  && data.staying_customers != 'null' ? data.staying_customers : '';
            sourceText = sourceText && sourceText != 'null' ? sourceText : "";
            
            var resDetails = '<div class="tooltip-reservation" style="background-color: white;position:absolute;z-index:10001;padding:8px ; font-size: 12px; border: 2px solid rgba(0,0,0,0.5)">'+
                            '<p><span>'+groupId+'</span> <span>'+groupName+'</span></p>'+
                            '<p style="margin-bottom:8px;text-transform: capitalize;"><strong>' + data.booking_customer + staying_customers + '</strong></p>'+
                            '<p><strong>'+l("Room")+':</strong> '+data.room_name+'</p>'+
                            '<p><span><strong>'+l("Check in")+':</strong> '+checkInDate+'</span> <span><strong>'+l("Check out")+':</strong> '+checkOutDate+'</span></p>'+
                            '<p><strong>'+l("Number of nights")+':</strong> '+diffDays+'</p>'+
                            '<p style="margin-bottom:8px;"> <span><strong>'+l("Amount Due")+':</strong> '+parseFloat(data.balance)+'</span></p>'+
                            '<p><span><strong>'+l("Booking Source")+':</strong> '+sourceText+'</span></p>'+
                            '<p><div style="max-width: 300px;"><strong>'+l("Booking Notes")+':</strong> '+notes;+'</div></p>'+
                '</div>';
                
            $("body").append(resDetails);
            
            $(this).mouseover(function (e) {
                $(this).css('z-index', 10000);
                $('.tooltip-reservation').fadeIn('500');
                $('.tooltip-reservation').fadeTo('10', 1.9);
            }).mousemove(function (e) {
                $('.tooltip-reservation').css('top', e.pageY + 10);
                $('.tooltip-reservation').css('left', e.pageX + 20);
            });
            
        };
        calendar_options['eventMouseout'] = function (data, event, view) {
            $(this).css('z-index', 8);
            $('.tooltip-reservation').remove();
        };        
    }
    
    if(innGrid.hasBookingPermission == '1')    
    {
        calendar_options.select = function (startDate, endDate, allDay, jsEvent, mouseCoord, view) {
        	checkInDate = formatDate(startDate, 'yyyy-MM-dd');
			checkOutDate = formatDate(addDays(endDate, 1),"yyyy-MM-dd");

            var clicked = Math.abs(mouseCoord.down.x - mouseCoord.up.x) <= 2; // tolerance

            if (!clicked) {
                innGrid.createNewBooking(checkInDate, checkOutDate, rooms[view.lastSelectedRow]);
            }

		    $('#notification-drag-box').hide();
		    this.unselect()
		};
    }
    
    
	innGrid.calendar = $('#calendar').html("").fullCalendar(calendar_options);
    
    if(innGrid.hasBookingPermission == '0')
    {
        $('.create-new-booking').prop('disabled', true);
    }    
	// if the user has owner permission, then the user can edit Room name and Room type by clicking on Room name on calendar
	$("#RoomTable > tbody > tr").on("click", function() {
		var term = "unassigned";
		var room = $(this).find(".room-name");
		var roomID = $(this).find(".room-name").attr('id');
		if( roomID.indexOf( term ) != -1 )
		{}
		else
		{
			innGrid.populateRoomEditModal(room);
		}
	});

    $("#RoomTable").stickyTableHeaders();
	$("#CalendarTable").stickyTableHeaders();
    
    
	$('.btn_calendar_view[rel="popover"]').popover({ trigger: 'hover', container: 'body', html: true });
}

//Creates Overview calendar
innGrid.buildOverViewCalendar = function(){	
	
	rooms = Array.from(roomsOverviewCalendar);
	roomIds = Object.assign({}, roomIdsOverviewCalendar);

	rooms.push({name: 'Total', room_id: "total", room_type: "Total", room_type_id: "total", status: "Clean"});

	var dateString = $("#sellingDate").val();
	var date = new Date(dateString.replace(/-/g,'/'));
	var width = $("body").width();
	var daysBeforeToday = Math.round(width/400);
	var daysAfterToday = Math.round(width/70);
    
    var calendar_options = {
        monthFromBeginningOnly: true, //Set this true if you want calendar always start from the first day		
		year: date.getFullYear(),
		month: date.getMonth(),
		date: date.getDate(),		
		defaultView: 'custom',
		selectable: true,
		selectHelper: true,
		showNumbers: false,
		header: {
			left: 'title',
			center: null,
			right: 'prev,today,next'
		},
		
		buttonText: {
			prev: '&nbsp;&#9668;&nbsp;',
			next: '&nbsp;&#9658;&nbsp;',
			prevYear: '&nbsp;&lt;&lt;&nbsp;',
			nextYear: '&nbsp;&gt;&gt;&nbsp;',
			today: l('Today'),
			month: 'month',
			//week: 'week',
			relative: 'relative'
			//day: 'day'
		},
		
		rowsHeight: '1.8em',
		
		dragOpacity: .5,
		dragRevertDuration: 100,
		
		allDayText: 'ALLDAY',
		firstHour: 10,
		slotMinutes: 15,
		defaultEventMinutes: 45,
		allDayDefault: true,
		
		//Text format for columnn headers
		columnFormat: "dd<br>ddd",
        timeFormat: "h(:mm)[T]{ - h(:mm)T}",
		titleFormat: "yyyy-MM-dd { -yyyy-MM-dd }",
        
		//today is acquired from looking up DIV
		today: dateString.replace(/-/g,'/'),

		daysBeforeToday: daysBeforeToday,
		daysAfterToday: daysAfterToday,
	
		eventClick: innGrid.bookingClicked,
        
        dayClick: innGrid.dayClicked,
		viewChanged: innGrid.viewChanged,
		eventResize: innGrid.bookingResized,
		eventDrop: occupacyMoved,
		eventSources: [getCalendarOverviewData],
		rooms: rooms,
	};
	
//	calendar_options['eventMouseover'] = function (data, event, view) {
//		var tooltipData = $('#overview_calendar #CalendarTable tbody').find('tr:nth-child('+(parseInt(view.lastSelectedRow) + 1)+')').find('.fc-day-content > div').attr('availability');
//		console.log('over',tooltipData);
//		var totalAvailbility = data.total;
//		var sold = data.sold;
//		var availablity = data.title;
//		var status = (data.status == "1") ? "Open" : "Close";
//
//		var availabilityDetails = '<div class="tooltip-reservation" style="background-color: white;position:absolute;z-index:10001;padding:8px ; font-size: 12px; border: 2px solid rgba(0,0,0,0.5)">'+
//						'<p><strong>'+l("Total")+':</strong> '+totalAvailbility+'</p>'+
//						'<p><strong>'+l("Sold")+':</strong> '+sold+'</p>'+
//						'<p style="font-size: 15px;padding: 2px 0px;"><strong>'+l("Available")+': '+availablity+'</strong></p>'+
//						'<p><strong>'+l("Status")+':</strong> '+status+'</p>'+
//			'</div>';
//		$("body").append(availabilityDetails);
//
//		$(this).mouseover(function (e) {
//			$(this).css('z-index', 10000);
//			$('.tooltip-reservation').fadeIn('500');
//			$('.tooltip-reservation').fadeTo('10', 1.9);
//		}).mousemove(function (e) {
//			$('.tooltip-reservation').css('top', e.pageY + 10);
//			$('.tooltip-reservation').css('left', e.pageX + 20);
//		});
//	};
	
//	calendar_options['eventMouseout'] = function (data, event, view) {
//		$(this).css('z-index', 8);
//		$('.tooltip-reservation').remove();
//	};        
	
	calendar_options.select = function (startDate, endDate, allDay, jsEvent, mouseCoord, view) {
		var roomTypeID = $('#overview_calendar #RoomTable tbody').find('tr:nth-child('+(parseInt(view.lastSelectedRow) + 1)+')').find('.fc-day-content').data('room_type_id');
		checkInDate = formatDate(startDate, 'yyyy-MM-dd');
		checkOutDate = formatDate(addDays(endDate, 1),"yyyy-MM-dd");

		var clicked = Math.abs(mouseCoord.down.x - mouseCoord.up.x) <= 2; // tolerance

		if (!clicked) {
			innGrid.createNewBooking(checkInDate, checkOutDate, roomTypeID);
		}

		$('#notification-drag-box').hide();
		this.unselect()
	};
		
	innGrid.calendar = $('#overview_calendar').html("").fullCalendar(calendar_options);
	
	$('.btn_calendar_view[rel="popover"]').popover({ trigger: 'hover', container: 'body', html: true });
}

;(function ($, window, undefined) {
	'use strict';
    
	var name = 'stickyTableHeaders';
	var defaults = {
			fixedOffset: 0
		};

	function Plugin (el, options) {
		// To avoid scope issues, use 'base' instead of 'this'
		// to reference this class from internal events and functions.
		var base = this;

		// Access to jQuery and DOM versions of element
		base.$el = $(el);
		base.el = el;

		// Listen for destroyed, call teardown
		base.$el.bind('destroyed',
			$.proxy(base.teardown, base));

		// Cache DOM refs for performance reasons
		base.$window = $(window);
		base.$clonedHeader = null;
		base.$originalHeader = null;
        base.$fixedHeadersTable = null;

		// Keep track of state
		base.isSticky = false;
		base.leftOffset = null;
		base.topOffset = null;

		base.init = function () {
			base.options = $.extend({}, defaults, options);

			base.$el.each(function () {
				var $this = $(this);

				// remove padding on <table> to fix issue #7
				$this.css('padding', 0);

				base.$originalHeader = $('thead', this);

                base.$fixedHeadersTable = $("<table>");
                base.$fixedHeadersTable.addClass('fixedHeadersTable');
                base.$fixedHeadersTable.css({
                    position: 'fixed',
                    padding: 0,
                    zIndex: 10,
                    backgroundColor: 'white',
                });

                $this.before(base.$fixedHeadersTable);

				base.$printStyle = $('<style type="text/css" media="print">' + '.fixedHeadersTable { display:none !important; }' + '</style>');
				$('head').append(base.$printStyle);
			});

			base.toggleHeaders();
			base.bind();
		};

		base.destroy = function (){
			base.$el.unbind('destroyed', base.teardown);
			base.teardown();
		};

		base.teardown = function(){
			$.removeData(base.el, 'plugin_' + name);

			base.unbind();

            base.$fixedHeadersTable.remove();
			base.$printStyle.remove();

			base.el = null;
			base.$el = null;
		};

		base.bind = function(){
			base.$window.on('scroll.' + name, base.toggleHeaders);
			base.$window.on('resize.' + name, base.toggleHeaders);
			base.$window.on('resize.' + name, base.updateWidth);
		};

		base.unbind = function(){
			// unbind window events by specifying handle so we don't remove too much
			base.$window.off('.' + name, base.toggleHeaders);
			base.$window.off('.' + name, base.updateWidth);
			base.$el.off('.' + name);
			base.$el.find('*').off('.' + name);
		};

		base.toggleHeaders = function () {
			base.$el.each(function () {
				var $this = $(this);

				var newTopOffset = isNaN(base.options.fixedOffset) ?
					base.options.fixedOffset.height() : base.options.fixedOffset;

				var offset = $this.offset();
				var scrollTop = base.$window.scrollTop() + newTopOffset;
				var scrollLeft = base.$window.scrollLeft();

				if ((scrollTop > offset.top) && (scrollTop < offset.top + $this.height() - base.$originalHeader.height())) {
					var newLeft = offset.left - scrollLeft;
					if (base.isSticky && (newLeft === base.leftOffset) && (newTopOffset === base.topOffset)) {
						return;
					}

                    base.leftOffset = newLeft;
                    base.topOffset = newTopOffset;

                    base.$fixedHeadersTable.css({
                        left: newLeft,
                        top: newTopOffset,
                        width: $this.width()
                    });

                    base.$fixedHeadersTable.html(base.$originalHeader.clone());

                    base.$fixedHeadersTable.find("th").css({
                        borderBottomWidth: "1px"
                    });

                    base.$fixedHeadersTable.show();

					base.isSticky = true;
				}
				else if (base.isSticky) {
                    base.$fixedHeadersTable.hide();

					base.isSticky = false;
				}
			});
		};

		base.updateOptions = function(options) {
			base.options = $.extend({}, defaults, options);
			base.updateWidth();
			base.toggleHeaders();
		};

		// Run initializer
		base.init();
	}

	// A plugin wrapper around the constructor,
	// preventing against multiple instantiations
	$.fn[name] = function ( options ) {
		return this.each(function () {
			var instance = $.data(this, 'plugin_' + name);
			if (instance) {
				if (typeof options === "string") {
					instance[options].apply(instance);
				} else {
					instance.updateOptions(options);
				}
			} else if(options !== 'destroy') {
				$.data(this, 'plugin_' + name, new Plugin( this, options ));
			}
		});
	};

})(jQuery, window);

function UpdateTableHeaders() {
	$(".persist-area").each(function() {

	    var el         = $(this),
	    offset         = el.offset(),
	    scrollTop      = $(window).scrollTop(),
	    floatingHeader = $(".floatingHeader", this)

	    if ((scrollTop > offset.top) && (scrollTop < offset.top + el.height())) {
	        floatingHeader.css({
	            "visibility": "visible"
	        });
	    } else {
	        floatingHeader.css({
	            "visibility": "hidden"
	        });      
	    };
	});
}
//Gets rooms data from rooms.xml
//Lots of globals here. Needs to be fixed
innGrid.getRooms = function(callback){
    var order = "room_name";
    var room_type_id = $('[name=room-type]').val();
    var group_id = "";//$('[name=room-housekeeping]').val();
    var floor_id = $('[name=room-floor]').val();
    var location_id = $('[name=room-location]').val();
    var reservation_type = $('[name=reservation-type]').val();
    var booking_source = $('[name=booking-source]').val();

	var getRoomsSuccessFn = function(data){
		//Initialize Variables
		//For each Data
		var roomsArray = new Array();
		rooms = roomsOverviewCalendar = roomsBookingCalendar = new Array();
		roomIds = roomIdsOverviewCalendar = roomIdsBookingCalendar = {};

		$(data).each(function(index, roomObject){
			//rebuild objects in roomsArray but trim roomtype length
			//******************************
			roomsArray[index] = {};
			roomsArray[index].name = roomObject.room_name;
			roomsArray[index].status = roomObject.status;


			if (roomObject.acronym != null)
			{
				if (roomObject.acronym)
				{
					roomsArray[index].room_type = roomObject.acronym;
				}
				else if (roomObject.room_type !== undefined)
				{
					if (roomObject.room_type.length > 6) // trims room type to 6 characters prevent from being too long and messing up the columns
					{
						roomsArray[index].room_type = roomObject.room_type.substr(0,6); // trims room type to 6 characters
					}
					else
					{
						roomsArray[index].room_type = roomObject.room_type;
					}
				}
				else
				{
					roomsArray[index].room_type = '';
				}

			} else
			{
				roomsArray[index].room_type = "";
			}
			roomsArray[index].room_id = roomObject.room_id;
			roomsArray[index].room_type_id = roomObject.room_type_id;
			//******************************

			// update June 25, 2011. The code below ensures that
			// the bookings are aligned with the rooms accordingly on calendar display.
			if(roomIds[roomObject.room_id] === undefined){ //because variables are global this somehow works...

				roomIds[roomObject.room_id] = rooms.length; //why length?
				rooms[rooms.length] = roomObject.room_id;
			}

		});
		innGrid.ajaxCache = innGrid.ajaxCache || {};
		rooms = roomsArray; //Rebuild array of room objects

		roomsOverviewCalendar = Array.from(rooms); // clone array
		roomIdsOverviewCalendar = Object.assign({}, roomIds);

		// add unassigned room row for each room type
		var flags = [], room_types = [], l = rooms.length, i;
		for( i=0; i<l; i++) {
			if( flags[rooms[i].room_type]) continue;
			flags[rooms[i].room_type] = true;
			room_types.push(rooms[i]);
		}

		if(!innGrid.isShowUnassignedRooms)
		{
			var unassigned_room = {
				name: "",
				status: "",
				room_type: "",
				room_id: "unassigned",
				room_type_id: ""
			};
			roomIds[unassigned_room.room_id] = rooms.length;
			rooms.push(unassigned_room);

			$(room_types).each(function(i, room_type){
				unassigned_room = {
					name: "Unassigned",
					status: "Clean",
					room_type: room_type.room_type,
					room_id: "unassigned-"+room_type.room_type_id,
					room_type_id: room_type.room_type_id
				};
				roomIds[unassigned_room.room_id] = rooms.length;
				rooms.push(unassigned_room);
			});
		}

		roomsBookingCalendar = Array.from(rooms); // clone array
		roomIdsBookingCalendar = Object.assign({}, roomIds);

		if(callback !== undefined) {
			callback(bookings);
		}
	};

    if (innGrid.roomsWithoutFilters) {
		getRoomsSuccessFn(innGrid.roomsWithoutFilters);
		innGrid.roomsWithoutFilters = null;
		return;
	}

    $.ajax({
		type: "GET",
		url: getBaseURL() + 'booking/get_all_rooms_in_JSON'  + "?" + new Date().getTime(),  //To prevent IE caching
		dataType: "json",
		data: {
            order: order, 
            room_type_id: room_type_id, 
            group_id:group_id, 
            floor_id: floor_id, 
            location_id: location_id,
            reservation_type:reservation_type,
            booking_source:booking_source
        },
		success: getRoomsSuccessFn,
		error: innGrid.ajaxError
	 });
}

// EVENT HANDLERS ###################################################################

//Occures when an booking is moved. Gets an event that represents the booking as parameter.
function occupacyMoved(event,dayDelta,minuteDelta,allDay,revertFunc,ev,ui,roomDelta) {

	var dateFrom1 = formatDate(addDays(cloneDate(event.start), -dayDelta), "yyyy-MM-dd");
	var dateTo1 = formatDate(addDays(cloneDate(event.end || event.start), -dayDelta), "yyyy-MM-dd");
	var dateFrom2 = formatDate(event.start, "yyyy-MM-dd");
	var dateTo2 = formatDate(addDays(cloneDate(event.end || event.start), 1), "yyyy-MM-dd");

	var room1 = rooms[event.room - roomDelta].name;
	var room2 = rooms[event.room].name;
	var type1 = rooms[event.room - roomDelta].room_type;
	var type2 = rooms[event.room].room_type;
	var room_id = rooms[event.room].room_id;
	var state = event.state;

	// Generate alert string for moving booking blocks
	confirmation_string = "";
	if (type1 != type2) {
		confirmation_string = confirmation_string + "- "+l('Roomtype will be changed from')+" " + type1 + " "+l('to')+" " + type2 + "\n";
	}
	if (room1 != room2) {
		confirmation_string = confirmation_string + "- "+l('Room will be changed from')+" " + room1 + " "+l('to')+" " + room2 + "\n";
	}
	if (dateFrom1 != dateFrom2) {
		confirmation_string = confirmation_string + "- "+l('Check-in date will change from')+" " + dateFrom1 + " "+l('to')+" " + dateFrom2 + "\n";
	}
	confirmation_string = confirmation_string + l("Are you sure?");

	if (!confirm(confirmation_string)) {
		revertFunc();

	} else {
		dateFrom1 = dateFrom1 + ' 00:00:00';
		dateTo1 = dateTo1 + ' 00:00:00';
		dateFrom2 = dateFrom2 + ' 00:00:00';
		dateTo2 = dateTo2 + ' 00:00:00';

		$.ajax({
            url: getBaseURL() + 'calendar/move_booking_room_history',
            type: "POST",
            data: {
                booking_id: event.booking_id,
                room_id: room_id,
                state: state,
                start1: encodeURIComponent(dateFrom1),
                end1: encodeURIComponent(dateTo1),
                start2: encodeURIComponent(dateFrom2),
                end2: encodeURIComponent(dateTo2)
            },
            success: function(data){
                if (data.trim() != "success") {
                    alert(data);
                    revertFunc();
                } else {
                    // reload booking_list_wrap
					innGrid.reloadBookings();
					innGrid.updateAvailabilities(
						dateFrom2,
						dateTo2
					);
                }
            }
        });
	}

	
	
	//that's how we can get booking object from the event
	//console.log(convertToBooking(event).check_out_date);
}



//###################################################################################

//Get booking data
//Format is decided by Full Calendar format. See FullCalendar online documentation.
function getCalendarBookings(start, end, callback){
    var room_type_id = $('[name=room-type]').val();
    var group_id = $('[name=room-housekeeping]').val();
    var floor_id = $('[name=room-floor]').val();
    var location_id = $('[name=room-location]').val();
    var reservation_type = $('[name=reservation-type]').val();
    var booking_source = $('[name=booking-source]').val();
    var sellingdate = $("#sellingDate").val();
    sellingdate = new Date(sellingdate.replace(/-/g,'/'));

	var beforeTime = (new Date(sellingdate) - new Date(start));
    var beforeDays = Math.ceil(beforeTime / (1000 * 60 * 60 * 24));

    var afterTime = (new Date(end) - new Date(sellingdate));
    var afterDays = Math.ceil(afterTime / (1000 * 60 * 60 * 24));

    sessionStorage.setItem("beforeDays", beforeDays);
    sessionStorage.setItem("afterDays", afterDays);
	sessionStorage.setItem("currentCompanyId", $('#currentCompanyId').val());


	var param = 'start=' + encodeURIComponent(start.toUTCString()) + '&end=' + encodeURIComponent(addDays(end, 1).toUTCString())+'&room_type_id='+room_type_id+'&group_id='+group_id+'&floor_id='+floor_id+'&location_id='+location_id+'&reservation_type='+reservation_type+'&booking_source='+booking_source;
	if(bookings == null || reloadDataEveryRefetch) {
        if(typeof get_bookings_AJAX_request !== "undefined" && get_bookings_AJAX_request)
            get_bookings_AJAX_request.abort();
        
		get_bookings_AJAX_request = $.ajax({
		        type: "POST",
				url: getBaseURL() + 'booking/get_bookings_in_JSON',
				data: param,
				dataType: "json",
				success: function(bookingObjects){
					var bookingsArray = new Array();
                    $(bookingObjects).each(function(index, value){
					  	bookingsArray[index] = new Object();
					  	bookingsArray[index].booking_id = value.booking_id;
//					  	bookingsArray[index].room_id = value.room_id;
					  	bookingsArray[index].room_id = (value.room_id == 0 || value.room_id == null) ? 'unassigned-'+value.brh_room_type_id : value.room_id;
                        bookingsArray[index].room_type_id = value.room_type_id;
					  	bookingsArray[index].room_name = value.room_name;
						bookingsArray[index].warning_message = value.warning_message;
						bookingsArray[index].border_color = value.border_color;
						bookingsArray[index].color = value.color;
						bookingsArray[index].customer_type_id = value.customer_type_id;
						
                        numberOfStayingGuests = parseInt(value.guest_count);
						
                        if(value.is_group_booking != null) {
                            bookingsArray[index].group_booking = 'group-booking';
                        }
						if (value.state == 3) {// if the booking is Out of Order
							bookingsArray[index].customer_name = l('OUT OF ORDER');
						}
						// if there is more than one "staying" customer
						else if (numberOfStayingGuests > 1 )
						{
							bookingsArray[index].customer_name = value.customer_name + ", "+value.guest_name + " "+l('and')+" "+(numberOfStayingGuests-1) +" "+l('other(s)');
						}	
						else if (numberOfStayingGuests === 1)
						{
							bookingsArray[index].customer_name = value.customer_name + " "+l('with')+" "+value.guest_name;
						} 
						else
						{
							bookingsArray[index].customer_name = value.customer_name;
						}
                        
                        bookingsArray[index].staying_customers = value.staying_customers;
                        bookingsArray[index].booking_customer = value.customer_name;
                       
						bookingsArray[index].check_in_date = value.check_in_date;
					  	bookingsArray[index].check_out_date = value.check_out_date; 
					  	bookingsArray[index].state = value.state;
                        bookingsArray[index].booking_source = value.source;
                        bookingsArray[index].id_group_booking = value.is_group_booking;
                        bookingsArray[index].group_name = value.group_name;
                        bookingsArray[index].payment_total = value.payment_total;
                        bookingsArray[index].balance = value.balance;
                        bookingsArray[index].booking_notes = value.booking_notes;
				  	});
					var tmp = convertToEvents(bookingsArray);
					bookings = tmp.bookings;
					//rooms = tmp.rooms;
					//room_names = tmp.room_names;
					// console.log('bookings', bookings);
					if(callback !== undefined && !innGrid.isOverviewCalendar)
					{
                        callback(bookings);
					}
					
					//calendar.fullCalendar('refetchEvents');
					
			 	},
			 	error: innGrid.ajaxError
			 });
    } else {
		if (bookings!=null)
			alert(l("ocupancies is not null!"));
		if (!reloadDataEveryRefetch)
			alert(l("reload Data Every Refetch is false"));
		callback(bookings);
    }
}

//Converts booking entry (taken from XML) to event used by calendar
function convertToEvents(data){
	var bookings = new Array();
	var rooms = new Array();
	var room_names = new Array();
	
	for(var i in data){
		bookings[i] = new Object();
		bookings[i].booking_id = data[i].booking_id;
	 	bookings[i].title = ""+data[i].customer_name;
		bookings[i].color = ""+data[i].color;
        bookings[i].check_in_date = ""+data[i].check_in_date;
        bookings[i].check_out_date = ""+data[i].check_out_date;
        bookings[i].booking_source = ""+data[i].booking_source;
        bookings[i].id_group_booking = ""+data[i].id_group_booking;
        bookings[i].group_name = ""+data[i].group_name;
        bookings[i].payment_total = ""+data[i].payment_total;
        bookings[i].payment_total = ""+data[i].payment_total;
        bookings[i].balance = ""+data[i].balance;
        bookings[i].room_name = ""+data[i].room_name;
        bookings[i].booking_customer = ""+data[i].booking_customer;
        bookings[i].staying_customers = ""+data[i].staying_customers;
        bookings[i].customer_type_id = ""+data[i].customer_type_id;
        bookings[i].room_id = ""+data[i].room_id;
            var flag = '';
            if(data[i].customer_type_id == '-1')
            {
                flag = 'blacklist';
            }
            else if(data[i].customer_type_id == '-2')
            {
                flag = 'vip';
            }
		/*if(parseDate===undefined){
			bookings[i].start = new Date(data[i].check_in_date);
		  	bookings[i].end = new Date(data[i].check_out_date);
		}
		else{
			bookings[i].start = parseDate(data[i].check_in_date);
		  	bookings[i].end = addDays(cloneDate(parseDate(data[i].check_out_date)), -check_out_dateHack);
		}*/

		// change that was suggested by Sergei on Feb 26, 2012
		
		bookings[i].start = jQuery.fullCalendar.parseDate(data[i].check_in_date);
		bookings[i].end = addDays(jQuery.fullCalendar.parseDate(data[i].check_out_date), -check_out_dateHack);
	
	  	bookings[i].state = parseInt(data[i].state);
		bookings[i].className = flag+" fc-event"+bookings[i].state+" booking border-"+data[i].border_color+" "+data[i].group_booking; // For CSS and border color
        bookings[i].warning_message = data[i].warning_message;
		bookings[i].booking_notes = data[i].booking_notes;
						
		
		var rn = data[i].room_id;
		if(roomIds[rn] !== undefined)
			bookings[i].room = roomIds[rn];
		else{
			roomIds[rn] = rooms.length;
			bookings[i].room = roomIds[rn];
			rooms[rooms.length] = rn;
		}
	}
	
	var result = new Object();
	result.bookings = bookings;
	result.rooms = rooms;
	result.room_names = room_names;
	return result;
}

//###################################################################################
innGrid.ajaxError = function(XMLHttpRequest, textStatus, errorThrown){
	/*
	if(XMLHttpRequest.status==200)
		$('#errorDiv').html('200 AJAX error occured: '+textStatus);
	else
		$('#errorDiv').html('AJAX error occured: '+XMLHttpRequest.status+' '+XMLHttpRequest.statusText);
	*/
}
//###################################################################################

innGrid.saveRoom = function(roomID, proceedToNextRoom)
{
	$.ajax({
	        type: "POST",
			url: getBaseURL() + 'room/update_room_AJAX',
			data: {
				room_id: selectedRoomID,
				room_name: $("[name='room_edit_room_name']").val(),
				room_type_id: $("[name='room_edit_room_type_id']").val(),
				status: $("[name='room_edit_room_status']").val()
			},
			dataType: "json",
			success: function(data){
				if (proceedToNextRoom)
				{
					var nextRoom = selectedRoomDiv.parent().parent().parent().next().find(".room-name");
					innGrid.populateRoomEditModal(nextRoom);	
			    }
			    else
			    {
			    	$("#room-edit-modal").modal('hide');
			    }
			}
		});
}

// It all starts here!
$(function() {

	if(innGrid.isOverviewCalendar)
	{
		$('#overview_calendar').show();
		$('#calendar').hide();
		innGrid.getRooms(innGrid.buildOverViewCalendar);
	}
	else
	{
		$('#overview_calendar').hide();
		$('#calendar').show();
		innGrid.getRooms(innGrid.buildCalendar);
		
	}

    $("#room-edit-modal")
        .modal({show: false})
        .on('hide.bs.modal', function (e) {
			if(!(innGrid.calendar && innGrid.calendar.destroy)) {
				innGrid.reloadCalendar();
			}
        });
    // when user clicks on "create new room type" in room_edit modal.
    var previous;
    $(document).on('focus', "select[name='room_edit_room_type_id']", function () {
        previous = this.value;
    }).on("change", "select[name='room_edit_room_type_id']", function(x) {
        if ($(this).val() === 'create_new')
        {	
            var r = confirm(l("Proceed to Room Type Settings page?"));
            if (r == true) {
                window.location = getBaseURL() + 'settings/room_inventory/room_types';
            } else {
                $(this).val(previous);
            }
        }
    })
    $(document).on('click', '#room_edit_save', function (){
        innGrid.saveRoom(selectedRoomID, false);
    });

    $(document).on('click', '#room_edit_save_and_proceed', function (){
        innGrid.saveRoom(selectedRoomID, true);
    });
	
    $(document).on('mouseenter', '#overview_calendar .fc-day-content', function (){
		var totalAvailbility = $(this).find('div').data('total');
		var sold = $(this).find('div').data('sold');
		var availablity = $(this).find('div').data('availability');
		var status = ($(this).find('div').data('status') == "1") ? "Open" : "Close";
		
		var availabilityDetails = '<div class="tooltip-reservation" style="background-color: white;position:absolute;z-index:10001;padding:8px ; font-size: 12px; border: 2px solid rgba(0,0,0,0.5)">'+
						'<p><strong>'+l("Total")+':</strong> '+totalAvailbility+'</p>'+
						'<p><strong>'+l("Sold")+':</strong> '+sold+'</p>'+
						'<p style="font-size: 15px;padding: 2px 0px;"><strong>'+l("Available")+': '+availablity+'</strong></p>'+
						'<p><strong>'+l("Status")+':</strong> '+status+'</p>'+
			'</div>';
		if(totalAvailbility != undefined && sold != undefined && availablity != undefined)
			$("body").append(availabilityDetails);

		$(this).mouseover(function (e) {
			$(this).css('z-index', 10000);
			$('.tooltip-reservation').fadeIn('500');
			$('.tooltip-reservation').fadeTo('10', 1.9);
		}).mousemove(function (e) {
			$('.tooltip-reservation').css('top', e.pageY + 10);
			$('.tooltip-reservation').css('left', e.pageX + 20);
		}).mouseleave(function (e) {
			$(this).css('z-index', 8);
			$('.tooltip-reservation').remove();
		});
    });

});
function filter_data(){
    innGrid.reloadCalendar();
}
