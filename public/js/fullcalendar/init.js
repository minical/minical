// It all starts here!
$(function () {
    $('#overview_calendar').hide();
    $('#calendar').show();
    innGrid.getRooms(innGrid.buildCalendar);

    $("#room-edit-modal")
        .modal({show: false})
        .on('hide.bs.modal', function (e) {
            innGrid.calendar.refetchResources();
        });
    $(document).on('click', '#room_edit_save', function () {
        innGrid.saveRoom(selectedRoomID, false);
    });

    $(document).on('click', '#room_edit_save_and_proceed', function () {
        innGrid.saveRoom(selectedRoomID, true);
    });
});

function filter_data() {
    innGrid.reloadCalendar();
}

//Gets rooms data from rooms.xml
//Lots of globals here. Needs to be fixed
innGrid.getRooms = function (callback) {
    var order = "room_name";
    var room_type_id = $('[name=room-type]').val();
    var group_id = "";//$('[name=room-housekeeping]').val();
    var floor_id = $('[name=room-floor]').val();
    var location_id = $('[name=room-location]').val();
    var reservation_type = $('[name=reservation-type]').val();
    var booking_source = $('[name=booking-source]').val();

    var getRoomsSuccessFn = function (data) {
        //Initialize Variables
        //For each Data
        var rooms = new Array();
        $(data).each(function (index, roomObject) {
            //rebuild objects in roomsArray but trim roomtype length
            //******************************
            rooms[index] = {};
            rooms[index].name = roomObject.room_name;
            rooms[index].status = roomObject.status;

            rooms[index].room_type = roomObject.acronym ? roomObject.acronym : (roomObject.room_type && roomObject.room_type.length > 6 ? roomObject.room_type.substr(0, 6) : roomObject.room_type);

            rooms[index].id = roomObject.room_id;
            rooms[index].room_type_id = roomObject.room_type_id;

            rooms[index].sort_order = roomObject.sort_order ? parseInt(roomObject.sort_order) : 0;

        });

        innGrid.ajaxCache = innGrid.ajaxCache || {};

        // add unassigned room row for each room type
        var flags = [], room_types = [], l = rooms.length, i;
        for (i = 0; i < l; i++) {
            if (flags[rooms[i].room_type]) continue;
            flags[rooms[i].room_type] = true;
            room_types.push(rooms[i]);
        }

        if (!innGrid.isShowUnassignedRooms) {
            var unassigned_room = {
                name: "",
                status: "",
                room_type: "",
                id: "unassigned",
                room_type_id: ""
            };

            rooms.push(unassigned_room);

            $(room_types).each(function (i, room_type) {
                unassigned_room = {
                    name: "Unassigned",
                    status: "Clean",
                    room_type: room_type.room_type,
                    id: "unassigned-" + room_type.room_type_id,
                    room_type_id: room_type.room_type_id
                };
                rooms.push(unassigned_room);
            });
        }

        if (callback !== undefined) {
            callback(rooms);
        }
    };

    if (innGrid.roomsWithoutFilters) {
        getRoomsSuccessFn(innGrid.roomsWithoutFilters);
        innGrid.roomsWithoutFilters = null;
        return;
    }

    $.ajax({
        type: "GET",
        url: getBaseURL() + 'booking/get_all_rooms_in_JSON' + "?" + new Date().getTime(),  //To prevent IE caching
        dataType: "json",
        data: {
            order: order,
            room_type_id: room_type_id,
            group_id: group_id,
            floor_id: floor_id,
            location_id: location_id,
            reservation_type: reservation_type,
            booking_source: booking_source
        },
        success: getRoomsSuccessFn,
        error: innGrid.ajaxError
    });
}

//Creates calendar
innGrid.buildCalendar = function (rooms) {

    $('#calendar').html('');

    innGrid.roomsResources = rooms;

    var sellingDate = moment($("#sellingDate").val()).toDate();

    var width = $("body").width();

    var daysBeforeToday = Math.round(width / 400);
    var daysAfterToday = Math.round(width / 60);

    console.log('bdays',sessionStorage.getItem("beforeDays"));
    console.log('adays',sessionStorage.getItem("afterDays"));

    var today = daysBeforeToday;
    daysBeforeToday = sessionStorage.getItem("beforeDays") != null ? parseInt(sessionStorage.getItem("beforeDays")) : daysBeforeToday;
    daysAfterToday = sessionStorage.getItem("afterDays") != null ? parseInt(sessionStorage.getItem("afterDays")) : daysAfterToday;

    if(innGrid.featureSettings.calendarDays) {
        var calendarDays = innGrid.featureSettings.calendarDays;
    } else {
        var calendarDays = daysBeforeToday + daysAfterToday;
    }

    console.log('calendarDays',calendarDays);
    var calStartDate = moment(sellingDate).subtract(daysBeforeToday, 'days').toDate();

    var calResourceAreaWidth = localStorage.getItem('resourceAreaWidth');
    var currentLanguageCode = 'en';
    
    if(innGrid.featureSettings.cuurentLanguage == 'korean') {
        currentLanguageCode = 'ko';
    } else if(innGrid.featureSettings.cuurentLanguage == 'portuguese') {
        currentLanguageCode = 'pt';
    } else if(innGrid.featureSettings.cuurentLanguage == 'spanish') {
        currentLanguageCode = 'es';
    } else if(innGrid.featureSettings.cuurentLanguage == 'vietnamese') {
        currentLanguageCode = 'vi';
    } else if(innGrid.featureSettings.cuurentLanguage == 'french') {
        currentLanguageCode = 'fr';
    }

    var calendar_options = {
        locale: currentLanguageCode,
        plugins: ['resourceTimeline', 'interaction'],
        resourceColumns: [
            {
                labelText: l(innGrid.featureSettings.defaultRoomSingular, true),
                field: 'name'
            },
            {
                labelText: l("Type", true),
                field: 'room_type'
            }
        ],
        resources: innGrid.roomsResources,
        resourceOrder: 'sort_order, name',

        selectable: true,
        selectHelper: true,
        handleWindowResize: true,

        resourceAreaWidth: calResourceAreaWidth ? calResourceAreaWidth + 'px' : '15%',
        scrollTime: '00:00',
        aspectRatio: 1,
        contentHeight: "auto",

        header: {
            left: (innGrid.enableHourlyBooking ? 'customCreateBooking, customDayView,customMonthView, prev,customToday,next, customStartDatePicker' : 'customCreateBooking, prev,customToday,next, customStartDatePicker'),
            right: null,
            center: null
        },
        buttonText: {
            prev: ' ◄ ',
            next: ' ► '
        },

        defaultView: 'customMonthView',
        views: {
            customMonthView: {
                type: 'resourceTimeline',
                slotDuration: '24:00:00',
                duration: {days: parseInt(calendarDays)},
                buttonText: l('Monthly View', true),
                columnHeader: false,
                slotLabelFormat: [
                    {month: 'long', year: 'numeric'}, // top level of text
                    {day: 'numeric'},
                    {weekday: 'short'},
                ],
            },
            customDayView: {
                type: 'resourceTimelineDay',
                buttonText: l('Hourly View', true),
                slotLabelFormat: [
                    {month: 'long', day: 'numeric', year: 'numeric'}, // top level of text
                    {
                        hour: 'numeric',
                        minute: '2-digit',
                        omitZeroMinute: true,
                        meridiem: 'short'
                    },
                ],
            }
        },
        customButtons: {
            // Add custom datepicker
            customToday: {
                text: l('Today', true),
                click: function (e) {
                    if (innGrid.calendar.view.type === "customMonthView") {
                        if (innGrid.calendar.view.activeStart < calStartDate && calStartDate < innGrid.calendar.view.activeEnd) {
                            // date is between start and end hence gotodate won't work, so move calendar to future date and switch back to new date
                            innGrid.calendar.gotoDate(innGrid.calendar.view.activeEnd);
                            innGrid.calendar.gotoDate(calStartDate);
                        } else if (innGrid.calendar.view.activeStart == calStartDate) {
                            // do nothing as date is already set
                        } else {
                            innGrid.calendar.gotoDate(calStartDate);
                        }
                    } else {
                        innGrid.calendar.gotoDate(sellingDate);
                    }
                }
            },
            customStartDatePicker: {
                text: l('Start date', true)+': ' + moment(calStartDate).format('YYYY-MM-DD') + ' ▼',
                click: function () {
                    if (!$("#hidden-start-date-picker").length) {
                        var $btnCustomStartDate = $('.fc-customStartDatePicker-button');
                        $btnCustomStartDate.after('<div style="width: 1px;height: 1px;opacity: 0;"><input type="text" id="hidden-start-date-picker" class="hidden-start-date-picker"/></div>');
                        var left = $btnCustomStartDate.offset().left,
                            top = $btnCustomStartDate.offset().top + $btnCustomStartDate.height() + 13;

                        $("#hidden-start-date-picker").datepicker({
                            dateFormat: 'yy-mm-dd',
                            onSelect: function (date, startDatePicker) {
                                date = new Date(date);
                                if (innGrid.calendar.view.type === "customMonthView" && innGrid.calendar.view.activeStart < date && date < innGrid.calendar.view.activeEnd) {
                                    // date is between start and end hence gotodate won't work, so move calendar to future date and switch back to new date
                                    innGrid.calendar.gotoDate(innGrid.calendar.view.activeEnd);
                                    innGrid.calendar.gotoDate(date);
                                } else if (innGrid.calendar.view.activeStart == date) {
                                    // do nothing as date is already set
                                } else {
                                    innGrid.calendar.gotoDate(date);
                                }
                            },
                            beforeShow: function (event, ui) {
                                setTimeout(function () {
                                    ui.dpDiv.css({left: left, top: top});
                                }, 5);
                            }
                        });
                        $("#hidden-start-date-picker").datepicker("setDate", calStartDate);
                    }
                    $("#hidden-start-date-picker").datepicker("show");
                }
            },
            customCreateBooking: {
                text: l('Create New Booking', true),
                click: function () {
                    if (typeof $(this).openBookingModal !== 'undefined' && $.isFunction($(this).openBookingModal)) {
                        $(this).openBookingModal();
                    }
                }
            }
        },
        viewSkeletonRender: function () {
            if (innGrid.calendar.view.type === "customMonthView") {
                innGrid.calendar.gotoDate(calStartDate);
            } else {
                innGrid.calendar.gotoDate(sellingDate);
            }

            setTimeout(function () {
                var date = moment(innGrid.calendar.getDate()).format('YYYY-MM-DD');
                $('.fc-customStartDatePicker-button').text(l('Start date', true)+': ' + date + ' ▼');
                $("#hidden-start-date-picker").val(date);

                addCalendarHeaderFilters();
            }, 500);

            if ($(".fc-customMonthView-view > table, .fc-customDayView-view > table").length) {
                $(".fc-customMonthView-view > table, .fc-customDayView-view > table").stickyTableHeaders();
            }
        },
        datesRender: function (renderInfo) {
            var date = moment(innGrid.calendar.getDate()).format('YYYY-MM-DD');
            $('.fc-customStartDatePicker-button').text(l('Start date', true)+': ' + date + ' ▼');
            $("#hidden-start-date-picker").val(date);

            addCalendarHeaderFilters();
        },
        dayRender: function (info) {
            if (innGrid.color && innGrid.color.length) {
                for (var i = 0; i < innGrid.color.length; i++) {
                    var date = moment(info.date).format('YYYY-MM-DD');
                    if (date >= innGrid.color[i]['start_date'] && date <= innGrid.color[i]['end_date']) {
                        if ($(info.el).hasClass("fc-today") && info.view.type === "customMonthView") {
                            var gradiant_color = 'linear-gradient(to left, #FFFF00,#FFFF00 50%,' + innGrid.color[i]['color_code'] + ' 50%)';
                            info.el.style.background = gradiant_color;
                            $('.fc-time-area.fc-widget-header').find('tr:not(:first-child)').find('th.fc-widget-header[data-date="' + date + '"]').css("background", gradiant_color);
                        } else {
                            info.el.style.backgroundColor = innGrid.color[i]['color_code'];
                            $('.fc-time-area.fc-widget-header').find('tr:not(:first-child)').find('th.fc-widget-header[data-date="' + date + '"]').css("background-color", innGrid.color[i]['color_code']);
                        }
                        break;
                    }
                }
            }
        },
        resourceRender: function (renderInfo) {
            renderInfo.el.style.backgroundColor = renderInfo.resource.extendedProps.status === "Dirty" ? "#f7e5e1" : (renderInfo.resource.extendedProps.status === "Inspected" ? "#24bb27" : "");

            var editIcon = (renderInfo.resource.id == '' || renderInfo.resource.id == 'unassigned' || renderInfo.resource.id == 'unassigned-' + renderInfo.resource.extendedProps.room_type_id) ? "" : "<div class='edit-icon'></div>";

            if (editIcon) {
                var $el = $(renderInfo.el);
                $el.append(editIcon);
                $el.on('click', function () {
                    innGrid.populateRoomEditModal($el.parent('tr'));
                });
            }
        },

        editable: true,
        dragOpacity: .5,
        dragRevertDuration: 100,
        selectMinDistance: 3,
        eventResizableFromStart: false,
        selectOverlap: true,

        defaultDate: calStartDate,
        now: sellingDate,

        eventClick: innGrid.bookingEventClicked,
        eventResize: innGrid.bookingResized,
        eventDrop: occupacyMoved,
        eventSources: [getCalendarBookings],
    };

    if (innGrid.isDisplayTooltip) {
        calendar_options['eventMouseEnter'] = function (info) {
            var groupId = (info.event.extendedProps.data.id_group_booking != 'null') ? "<strong>" + l("group_id") + ":</strong> " + info.event.extendedProps.data.id_group_booking : '';
            var groupName = (info.event.extendedProps.data.group_name != 'null') ? "<strong>" + l("group_name") + ":</strong> " + info.event.extendedProps.data.group_name : '';
           var totalAmount =  parseFloat(info.event.extendedProps.data.balance) + parseFloat(info.event.extendedProps.data.payment_total);
            totalAmount = totalAmount.toFixed(2);
            // get booking sources text
            var sources = [];
            for(var i = 0; i < innGrid.bookingSources.length; i++)
            {
                sources.push({id: innGrid.bookingSources[i].id, name: innGrid.bookingSources[i].name});
            }
            
            var sourceVal = info.event.extendedProps.data.booking_source;
            var sourceText = '';
            $.each(sources, function (i, source) {
                if (source.id == sourceVal || source.name == sourceVal)
                {
                    sourceText = source.name;
                }
            });

            var notes = info.event.extendedProps.data.booking_notes.length > 120 ? info.event.extendedProps.data.booking_notes.substr(0, info.event.extendedProps.data.booking_notes.lastIndexOf(' ', 120)) + '...' : info.event.extendedProps.data.booking_notes;

            // calculate number of nights
            // if(innGrid.enableHourlyBooking) {
            //     var checkInDate = moment(info.event.start).format('YYYY-MM-DD');
            //     var checkOutDate = moment(info.event.end || info.event.start).format('YYYY-MM-DD');
            // } else {
                var checkInDate = moment(info.event.start).format('YYYY-MM-DD');
                var checkOutDate = moment(info.event.end || info.event.start).format('YYYY-MM-DD');
            // }

            var diffDays = moment(checkOutDate).diff(moment(checkInDate), 'days');
            var warning_message = info.event.extendedProps.data.warning_message;

            // get staying customers list
            var staying_customers = info.event.extendedProps.data.staying_customers && info.event.extendedProps.data.staying_customers != 'null' ? info.event.extendedProps.data.staying_customers : '';
            sourceText = sourceText && sourceText != 'null' ? sourceText : "";

            var resDetails = '<div class="tooltip-reservation" style="display:none;background-color: white;position:absolute;z-index:10001;padding:8px ; font-size: 12px; border: 2px solid rgba(0,0,0,0.5)">' +
                '<p><span>' + groupId + '</span> <span>' + groupName + '</span></p>' +
                '<p style="margin-bottom:8px;text-transform: capitalize;"><strong>' + info.event.extendedProps.data.booking_customer + staying_customers + '</strong></p>' +
                '<p><strong>' + l("Room", true) + ':</strong> ' + info.event.extendedProps.data.room_name + '</p>' +
                '<p><span><strong>' + l("check_in", true) + ':</strong> ' + innGrid._getLocalFormattedDate(checkInDate) + '</span> <span><strong>' + l("check_out", true) + ':</strong> ' + innGrid._getLocalFormattedDate(checkOutDate) + '</span></p>' +
                '<p><strong>' + l("Number of nights", true) + ':</strong> ' + diffDays + '</p>' +
                '<p> <span><strong>' + l("Total Amount", true) + ':</strong> ' + parseFloat(totalAmount) + '</span></p>' +
                '<p> <span><strong>' + l("Balance Due", true) + ':</strong> ' + parseFloat(info.event.extendedProps.data.balance).toFixed(2) + '</span></p>' +
                '<p style="margin-bottom:8px;"> <span><strong>' + l("Paid Amount", true) + ':</strong> ' + parseFloat(info.event.extendedProps.data.payment_total).toFixed(2) + '</span></p>' +
                '<p><span><strong>' + l("Booking Source", true) + ':</strong> ' + sourceText + '</span></p>' +
                '<p><div style="max-width: 300px;"><strong>' + l("booking_notes", true) + ':</strong> ' + notes +
                (warning_message ? ('<p><div style="max-width: 300px;"><strong>' + l("Warning", true) + ':</strong> ' + warning_message) : '') +
                '</div></p>' +
            '</div>';

            $("body").append(resDetails);

            $('.tooltip-reservation').css('top', info.jsEvent.pageY + 10);
            $('.tooltip-reservation').css('left', info.jsEvent.pageX + 20);
            $('.tooltip-reservation').fadeIn('500');
            $('.tooltip-reservation').fadeTo('10', 1.9);

            $(info.el).mouseover(function (e) {
                $(this).css('z-index', 10000);
                $('.tooltip-reservation').fadeIn('500');
                $('.tooltip-reservation').fadeTo('10', 1.9);
            }).mousemove(function (e) {
                $('.tooltip-reservation').css('top', e.pageY + 10);
                $('.tooltip-reservation').css('left', e.pageX + 20);
            });

        };

        calendar_options['eventMouseLeave'] = function (info) {
            $(this).css('z-index', 8);
            $('.tooltip-reservation').remove();
        };
    }

    if (innGrid.hasBookingPermission == '1') {
        calendar_options.select = function (info) {

            innGrid.createNewBooking(moment(info.start).format('YYYY-MM-DD HH:mm:ss'), moment(info.end).format('YYYY-MM-DD HH:mm:ss'), {
                room_id: info.resource.id,
                room_type_id: info.resource.extendedProps.room_type_id
            });
            this.unselect()
        };
    }

    var calendarEl = document.getElementById('calendar');

    innGrid.calendar = new FullCalendar.Calendar(calendarEl, calendar_options);

    innGrid.calendar.render();

    if (innGrid.hasBookingPermission == '0') {
        $('.fc-customCreateBooking-button').prop('disabled', true);
    }
}

//Get booking data
//Format is decided by Full Calendar format. See FullCalendar online documentation.
function getCalendarBookings(data, successCallback, failureCallback) {
    var start = data.start;
    var end = data.end;
    var room_type_id = $('[name=room-type]').val();
    var group_id = $('[name=room-housekeeping]').val();
    var floor_id = $('[name=room-floor]').val();
    var location_id = $('[name=room-location]').val();
    var reservation_type = $('[name=reservation-type]').val();
    var booking_source = $('[name=booking-source]').val();
    var sellingdate = $("#sellingDate").val();
    sellingdate = new Date(sellingdate.replace(/-/g, '/'));

    var beforeTime = (new Date(sellingdate) - new Date(start));
    var beforeDays = Math.ceil(beforeTime / (1000 * 60 * 60 * 24));

    var afterTime = (new Date(end) - new Date(sellingdate));
    var afterDays = Math.ceil(afterTime / (1000 * 60 * 60 * 24));

    sessionStorage.setItem("beforeDays", beforeDays);
    sessionStorage.setItem("afterDays", afterDays);
    sessionStorage.setItem("currentCompanyId", $('#currentCompanyId').val());

    var param = 'start=' + encodeURIComponent(start.toUTCString()) + '&end=' + encodeURIComponent(moment(end).add(1, 'day').toDate().toUTCString()) + '&room_type_id=' + room_type_id + '&group_id=' + group_id + '&floor_id=' + floor_id + '&location_id=' + location_id + '&reservation_type=' + reservation_type + '&booking_source=' + booking_source;

    if (typeof get_bookings_AJAX_request !== "undefined" && get_bookings_AJAX_request)
        get_bookings_AJAX_request.abort();

    get_bookings_AJAX_request = $.ajax({
        type: "POST",
        url: getBaseURL() + 'booking/get_bookings_in_JSON',
        data: param,
        dataType: "json",
        success: function (bookingObjects) {
            var bookingsArray = new Array();
            $(bookingObjects).each(function (index, value) {
                bookingsArray[index] = new Object();
                bookingsArray[index].booking_id = value.booking_id;
                bookingsArray[index].room_id = (value.room_id == 0 || value.room_id == null) ? l('unassigned', true)+'-' + value.brh_room_type_id : value.room_id;
                bookingsArray[index].room_type_id = value.room_type_id;
                bookingsArray[index].room_name = value.room_name;
                bookingsArray[index].warning_message = value.warning_message;
                bookingsArray[index].border_color = value.border_color;
                bookingsArray[index].color = value.color;
                bookingsArray[index].customer_type_id = value.customer_type_id;

                numberOfStayingGuests = parseInt(value.guest_count);

                if (value.is_group_booking != null) {
                    bookingsArray[index].group_booking = 'group-booking';
                }
                if (value.state == 3) {// if the booking is Out of Order
                    bookingsArray[index].customer_name = l('OUT OF ORDER');
                }
                // if there is more than one "staying" customer
                else if (numberOfStayingGuests > 1) {
                    bookingsArray[index].customer_name = value.customer_name + ", " + value.guest_name + " "+l('and')+" " + (numberOfStayingGuests - 1) + " "+ l('other(s)');
                } else if (numberOfStayingGuests === 1) {
                    bookingsArray[index].customer_name = value.customer_name + " "+l('with')+" " + value.guest_name;
                } else {
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
            bookings = convertToEvents(bookingsArray);

            if (successCallback !== undefined && !innGrid.isOverviewCalendar) {
                successCallback(bookings);
            }
        },
        error: innGrid.ajaxError
    });
}

innGrid.ajaxError = function (XMLHttpRequest, textStatus, errorThrown) {
    /*
    if(XMLHttpRequest.status==200)
        $('#errorDiv').html('200 AJAX error occured: '+textStatus);
    else
        $('#errorDiv').html('AJAX error occured: '+XMLHttpRequest.status+' '+XMLHttpRequest.statusText);
    */
}

//Converts booking entry (taken from XML) to event used by calendar
function convertToEvents(data) {
    var bookings = new Array();

    for (var i in data) {
        var flag = '';
        if (data[i].customer_type_id == '-1') {
            flag = 'blacklist';
        } else if (data[i].customer_type_id == '-2') {
            flag = 'vip';
        }

        bookings[i] = new Object();
        bookings[i].id = data[i].booking_id;
        bookings[i].title = "" + data[i].customer_name;
        bookings[i].resourceId = "" + data[i].room_id;
        bookings[i].start = moment(data[i].check_in_date).format("YYYY-MM-DD HH:mm:ss");
        bookings[i].end = moment(data[i].check_out_date).format("YYYY-MM-DD HH:mm:ss");
        bookings[i].backgroundColor = (data[i].color && data[i].color != 'transparent') ? "#" + data[i].color : getdefaultStateBGColor(parseInt(data[i].state));
        bookings[i].borderColor = bookings[i].backgroundColor;
        bookings[i].textColor = getdefaultStateTextColor(parseInt(data[i].state));
        bookings[i].classNames = [flag, data[i].group_booking, "border-" + data[i].border_color]; // For CSS and border color

        bookings[i].data = new Object();
        bookings[i].data.booking_id = data[i].booking_id;
        bookings[i].data.color = "" + data[i].color;
        bookings[i].data.check_in_date = "" + data[i].check_in_date;
        bookings[i].data.check_out_date = "" + data[i].check_out_date;
        bookings[i].data.booking_source = "" + data[i].booking_source;
        bookings[i].data.id_group_booking = "" + data[i].id_group_booking;
        bookings[i].data.group_name = "" + data[i].group_name;
        bookings[i].data.payment_total = "" + data[i].payment_total;
        bookings[i].data.balance = "" + data[i].balance;
        bookings[i].data.room_name = "" + data[i].room_name;
        bookings[i].data.booking_customer = "" + data[i].booking_customer;
        bookings[i].data.staying_customers = "" + data[i].staying_customers;
        bookings[i].data.customer_type_id = "" + data[i].customer_type_id;
        bookings[i].data.room_id = "" + data[i].room_id;
        bookings[i].data.state = parseInt(data[i].state);
        bookings[i].data.warning_message = data[i].warning_message;
        bookings[i].data.booking_notes = data[i].booking_notes;
    }

    return bookings;
}

//Occures when an booking is moved. Gets an event that represents the booking as parameter.
function occupacyMoved(info) {

    var dateFrom1 = moment(info.oldEvent.start).format('YYYY-MM-DD HH:mm:ss');
    var dateTo1 = moment(info.oldEvent.end || info.oldEvent.start).format('YYYY-MM-DD HH:mm:ss');
    var dateFrom2 = moment(info.event.start).format('YYYY-MM-DD HH:mm:ss');
    var dateTo2 = moment(info.event.end || info.event.start).format('YYYY-MM-DD HH:mm:ss');

    var room1 = info.oldResource ? info.oldResource.extendedProps.name : '';
    var room2 = info.newResource ? info.newResource.extendedProps.name : '';
    var type1 = info.oldResource ? info.oldResource.extendedProps.room_type : '';
    var type2 = info.newResource ? info.newResource.extendedProps.room_type : '';
    var room_id = info.newResource ? info.newResource.id : info.event.extendedProps.data.room_id;
    var state = info.event.extendedProps.data.state;

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
        info.revert();
    } else {
        $.ajax({
            url: getBaseURL() + 'calendar/move_booking_room_history',
            type: "POST",
            data: {
                booking_id: info.event.id,
                room_id: room_id,
                state: state,
                start1: encodeURIComponent(dateFrom1),
                end1: encodeURIComponent(dateTo1),
                start2: encodeURIComponent(dateFrom2),
                end2: encodeURIComponent(dateTo2)
            },
            success: function(data){
                if (data.trim()  != "success") {
                    alert(data);
                    info.revert();
                } else {
                    
                    // reload booking_list_wrap and calendar
                    innGrid.reloadBookings();
                    if(dateFrom1 < dateFrom2){
                        innGrid.updateAvailabilities(
                            dateFrom1,
                            dateTo2
                        );
                    }else{
                        innGrid.updateAvailabilities(
                            dateFrom2,
                            dateTo1
                        );
                    }  

                }
            }
        });
    }
}

function getdefaultStateBGColor(state) {
    var defaultColors = {
        "0": "#389af0", // reservation
        "1": "#28a745", // in-house
        "2": "#E67D21", // checked-out
        "3": "#DDD", // out of order
        "4": "#389af0", // cancelled
        "5": "#e63600", // no show
        "6": "#FFF", // deleted
        "7": "#FFF" // unconfirmed reservation
    };
    return defaultColors[state] ? defaultColors[state] : '#389af0';
}

function getdefaultStateTextColor(state) {
    var defaultColors = {
        "3": "#000", // out of order
        "4": "#FFF", // cancelled
        "6": "#000", // deleted
        "7": "#000" // unconfirmed reservation
    };
    return defaultColors[state] ? defaultColors[state] : '#fff';
}


innGrid.bookingEventClicked = function (info) {
    if (typeof $.fn.openBookingModal !== 'undefined' && $.isFunction($.fn.openBookingModal)) {
        if (innGrid.hasBookingPermission == '1' || (typeof innGrid.hasBookingPermission === 'undefined')) {
            $.fn.openBookingModal({
                id: info.event.id,
                selected_room_id: info.event.extendedProps.data.room_id,
            });
        }
    }
}

//Occurs when an booking is resized. Gets an event that represents the booking as parameter.
innGrid.bookingResized = function (info) {
    var dateFrom1 = moment(info.prevEvent.start).format('YYYY-MM-DD HH:mm:ss');
    var dateTo1 = moment(info.prevEvent.end || info.prevEvent.start).format('YYYY-MM-DD HH:mm:ss');

    var dateFrom2 = moment(info.event.start).format('YYYY-MM-DD HH:mm:ss');
    var dateTo2 = moment(info.event.end || info.event.start).format('YYYY-MM-DD HH:mm:ss');

    var room_id = info.event.extendedProps.data.room_id;
    var state = info.event.extendedProps.data.state;

    if (!confirm(l('Change check-out date from')+" " + dateTo1 + " "+l('to')+" " + dateTo2 + "?")) {
        info.revert();
    } else {
        $.ajax({
            url: getBaseURL() + 'calendar/resize_booking_room_history',
            type: "POST",
            data: {
                booking_id: info.event.id,
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
                    info.revert();
                } else {
                   
                    // reload booking_list_wrap and calendar
                    innGrid.reloadBookings();
                    if(dateTo1 > dateTo2) {
                        innGrid.updateAvailabilities(
                            dateFrom2,
                            dateTo1
                        );
                    }else{
                         innGrid.updateAvailabilities(
                            dateFrom2,
                            dateTo2
                        );
                    }
                }
            }
        });
    }
}

;(function ($, window, undefined) {
    'use strict';

    var name = 'stickyTableHeaders';
    var defaults = {
        fixedOffset: 0
    };

    function Plugin(el, options) {
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

        base.destroy = function () {
            base.$el.unbind('destroyed', base.teardown);
            base.teardown();
        };

        base.teardown = function () {
            $.removeData(base.el, 'plugin_' + name);

            base.unbind();

            base.$fixedHeadersTable.remove();
            base.$printStyle.remove();

            base.el = null;
            base.$el = null;
        };

        base.bind = function () {
            base.$window.on('scroll.' + name, base.toggleHeaders);
            base.$window.on('resize.' + name, base.toggleHeaders);
            base.$window.on('resize.' + name, base.updateWidth);
        };

        base.unbind = function () {
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
                } else if (base.isSticky) {
                    base.$fixedHeadersTable.hide();

                    base.isSticky = false;
                }
            });
        };

        base.updateOptions = function (options) {
            base.options = $.extend({}, defaults, options);
            base.updateWidth();
            base.toggleHeaders();
        };

        // Run initializer
        base.init();
    }

    // A plugin wrapper around the constructor,
    // preventing against multiple instantiations
    $.fn[name] = function (options) {
        return this.each(function () {
            var instance = $.data(this, 'plugin_' + name);
            if (instance) {
                if (typeof options === "string") {
                    instance[options].apply(instance);
                } else {
                    instance.updateOptions(options);
                }
            } else if (options !== 'destroy') {
                $.data(this, 'plugin_' + name, new Plugin(this, options));
            }
        });
    };

})(jQuery, window);

//Occurs when an booking is clicked. Gets an event that represents the booking as parameter.
innGrid.populateRoomEditModal = function ($el) {
    // a global cache to be used later in save room func
    selectedRoomDiv = $el;
    selectedRoomID = $el.data('resource-id');
    if (selectedRoomID === "unassigned") {
        alert(l("there's no more rooms"));
        return;
    }

    $.getJSON(getBaseURL() + 'room/get_room_AJAX/' + selectedRoomID, function (room) {

        $("#room-edit-modal").show();

        $("[name='room_edit_room_type_id'] option").each(function () {
            var option = $(this);
            if (room.room_type_id == option.val()) {
                option.prop("selected", true);
            }
        });

        $("[name='room_edit_room_status'] option").each(function () {
            var option = $(this);
            if (room.status == option.val()) {
                option.prop("selected", true);
            }
        });

        $("[name='room_edit_room_name']").val(room.room_name);
        $("#room-edit-modal").modal('show');
    });
};

innGrid.saveRoom = function (roomID, proceedToNextRoom) {
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
        success: function (data) {
            for (key in innGrid.roomsResources) {
                if (innGrid.roomsResources[key]['id'] == selectedRoomID) {
                    innGrid.roomsResources[key]['status'] = $("[name='room_edit_room_status']").val();
                }
            }

            if (proceedToNextRoom) {
                var nextRoom = selectedRoomDiv.next('tr');
                innGrid.populateRoomEditModal(nextRoom);
            } else {
                $("#room-edit-modal").modal('hide');
            }
        }
    });
};

function addCalendarHeaderFilters() {
    // add header filters
    $('.fc-toolbar.fc-header-toolbar')
        .find('.fc-right')
        .append(
            $('<div/>', { class: 'form-inline m-041' })
                .append(
                    $("<form/>",
                        {
                            id: 'booking_search',
                            method: 'GET',
                            class: "input-group hidden-xs",
                            action: getBaseURL() + "booking/show_bookings/",
                            style: 'margin-left:10px'
                        }
                    )
                        .append("<input class='form-control' placeholder='" + l('search_bookings') + "' name='search_query' type='text' value=''>")
                        .append(
                            $("<span/>", {
                                class: "input-group-btn"
                            })
                                .append(
                                    $('<button/>', {class: 'btn btn-light', type: 'submit'})
                                        .append(
                                            $("<span/>", {
                                                class: "glyphicon glyphicon-search"
                                            })
                                        )
                                )
                        )
                )
                .append(
                    $("<button/>", {
                        href: '#',
                        class: 'btn btn-light filter-booking m-040',
                        style: 'margin-left:10px',
                        text: l('More Filters', true)
                    }).on('click', function () {
                        $('#filter-booking').slideToggle();
                    })
                )
        );

    $('.btn_calendar_view[rel="popover"]').popover({trigger: 'hover', container: 'body', html: true});
}

innGrid.cacheResourceAreaWidth = function (n) {
    localStorage.setItem('resourceAreaWidth', n);
}