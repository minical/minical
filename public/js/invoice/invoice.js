//Update total taxes, total charge, and total credit
innGrid.updateTotals = function() {
		
	var totalCharge = 0;
	var taxArray = {};	
	var totalTax = 0;
	
	var totalTaxes = $(''); //Jquery object	
	
	var totalCredit = 0;
	var balance = 0;
	
	//Get tax totals
	$('.td-tax').each(function() {
		$(this).find('.tax').each(function () {				
			var taxType = $(this).find('.tax-type').html();				
			
			if (typeof taxArray[taxType] == 'undefined') { //These checks could be wrong (type comparison issue)
				taxArray[taxType] = 0;
			}

            var realTax = Number($(this).find('.tax-amount').attr('data-real-taxes').replace(/[^0-9\.-]+/g,""));
  			taxArray[taxType] += realTax;
			totalTax += realTax;
		});
	});

	//Get cahrge totals
    var subtotal = 0;
    //$("span[name='amount']").each(function() {
    //    subtotal += Number($(this).text().trim().replace(/[^0-9\.-]+/g,""));
    //});
    
    $('.charge').each(function() {
	    totalCharge += Number($(this).attr('data-real-total-charge').replace(/[^0-9\.-]+/g,""));
	});
	subtotal = totalCharge - totalTax;
    // because Javascript is weird : 0.1 + 0.2 = 0.30000000000000004
    totalCharge = totalCharge.toFixed(12) 

	//Generate tax jquery object for appending to the #total-taxes td
	$.each(taxArray, function(key, value) {
		totalTaxes = $(totalTaxes)
					.add($('<div />', {
						html: $('<span />', {
									'class': 'small',
									html: key
								})
								.add($('<span />', {
										'class': 'small',
										html: number_format(value, 2, ".", "")
									}))
					}));
	});
	
	//Get payment(credit) total
	$(".payment").each(function () {
		totalCredit += Number($(this).text().replace(/[^0-9\.-]+/g,""));
	});
    
    amount_due = number_format((totalCharge - totalCredit).toFixed(12), 2, ".", "");
	totalCharge = number_format(totalCharge, 2, ".", "");
	totalCredit = number_format(totalCredit, 2, ".", "");
	
	$("#balance").val(amount_due);
	$("#total-taxes").html(totalTaxes);
	$("#subtotal").html(innGrid.addCommas(number_format(subtotal, 2, ".", "")));
	$("#total-charge").html(innGrid.addCommas(totalCharge));
	$("#payment_total").html(innGrid.addCommas(totalCredit));
	$("#amount_due").html(innGrid.addCommas(amount_due));

	// if all upcoming charges are paid or guest doesn't have any upcoming charges, and total accumulated charges are paid, print "PAID IN FULL"
	// In other words, if there are more charges to be made throughout this guest's stay, do not display PAID IN FULL even if the current balance is 0

	if (amount_due == 0)
	{
		$("#paid_in_full").html(l('paid_in_full'));
	}
	else if (amount_due == 0.01)
	{
		$("#amount_due").html('0.00');
		var html_content = l('paid_in_full') + "<br/><small style='font-size:48%'>("+l('you might notice a 0.01 difference, because of rounding decimals')+".)</small>";
		$("#paid_in_full").html(html_content);
	}
	else
	{
		$("#paid_in_full").html("");
	}	
}


/**
	CHARGE GROUPING STUFF!!
*/
	
// return date +1 day
innGrid.getDateDiff = function(date, dayDiff, monthDiff) {
	var dt = $.datepicker.parseDate('yy-mm-dd', date);
    if(dayDiff)
    {
      dt.setDate(dt.getDate() + dayDiff);
    }
    if(monthDiff)
    {
      dt.setMonth(dt.getMonth() + monthDiff);
    }
	return $.datepicker.formatDate('yy-mm-dd', dt);
}

innGrid.getChargeGroups = function() {
	
	chargeGroups = [];

	$(".charge_row").each(function() {
		charge = $(this);
		var chargeObject = {
			room_name: charge.find("span[name='room_name']").text().trim(),
			room_type_name: charge.find("span[name='room_type_name']").text().trim(),
			date: innGrid._getBaseFormattedDate(charge.find("span[name='selling-date']").text().trim()),
			description: charge.find("span[name='description']").text().trim(),
			customer: charge.find("span[name='customer']").attr("id").trim(),
			chargeType: charge.find("span[name='charge-type']").attr("id").trim(),
			amount: charge.find("span[name='amount']").text().trim(),
            pay_period: charge.data('pay_period'),
			booking_id: charge.find("span[name='booking_id']").text().trim(),
		};
    
		var chargeBelongsToAGroup = false, 
		chargePositionInGroup = false;
		
		$.each(chargeGroups, function(j, chargeGroup) {
			$.each(chargeGroup, function(k, groupedCharge) {
				var groupedChargeObject = {
					room_name: groupedCharge.find("span[name='room_name']").text().trim(),
					room_type_name: groupedCharge.find("span[name='room_type_name']").text().trim(),
					date: innGrid._getBaseFormattedDate(groupedCharge.find("span[name='selling-date']").text().trim()),
					pay_period: groupedCharge.find("span[name='description']").text().trim(),
					customer: groupedCharge.find("span[name='customer']").attr("id").trim(),
					chargeType: groupedCharge.find("span[name='charge-type']").attr("id").trim(),
					amount: groupedCharge.find("span[name='amount']").text().trim(),
                    pay_period: groupedCharge.attr('data-pay_period').trim(),
                    description: groupedCharge.find("span[name='description']").text().trim(),
					booking_id: groupedCharge.find("span[name='booking_id']").text().trim(),
				};

				if(chargeObject.date == groupedChargeObject.date) {
					chargeBelongsToAGroup = false;
					chargePositionInGroup = false;
					return false;
				}
				
				if (((groupedChargeObject.pay_period === '0' && innGrid.getDateDiff(chargeObject.date, 1, null) === groupedChargeObject.date) || 
                    (groupedChargeObject.pay_period === '1' && innGrid.getDateDiff(chargeObject.date, 7, null) === groupedChargeObject.date) || 
                    (groupedChargeObject.pay_period === '2' && innGrid.getDateDiff(chargeObject.date, null, 1) === groupedChargeObject.date) ) &&
					chargeObject.description === groupedChargeObject.description &&
					chargeObject.room_name === groupedChargeObject.room_name &&
					chargeObject.room_type_name === groupedChargeObject.room_type_name &&
					chargeObject.customer === groupedChargeObject.customer &&
					chargeObject.chargeType === groupedChargeObject.chargeType &&
					chargeObject.amount === groupedChargeObject.amount &&
					chargeObject.booking_id === groupedChargeObject.booking_id )
				{
					console.log("prior match found");
					// chargeGroup.unshift(charge);
					chargeBelongsToAGroup = true;
					chargePositionInGroup = 'unshift';
					// return false;
				}
				else if (((groupedChargeObject.pay_period === '0' && innGrid.getDateDiff(chargeObject.date, -1, null) === groupedChargeObject.date) || 
                        (groupedChargeObject.pay_period === '1' && innGrid.getDateDiff(chargeObject.date, -7, null) === groupedChargeObject.date) || 
                        (groupedChargeObject.pay_period === '2' && innGrid.getDateDiff(chargeObject.date, null, -1) === groupedChargeObject.date) ) &&
					chargeObject.description === groupedChargeObject.description &&
					chargeObject.room_name === groupedChargeObject.room_name &&
					chargeObject.room_type_name === groupedChargeObject.room_type_name &&
					chargeObject.customer === groupedChargeObject.customer &&
					chargeObject.chargeType === groupedChargeObject.chargeType &&
					chargeObject.booking_id === groupedChargeObject.booking_id &&
					chargeObject.amount === groupedChargeObject.amount )
				{
					// chargeGroup.push(charge);
					chargeBelongsToAGroup = true;
					chargePositionInGroup = 'push';
					//return false;
				}
			});
			if(chargeBelongsToAGroup){
				if(chargePositionInGroup == 'unshift') {
					chargeGroup.unshift(charge);
				} else {
					chargeGroup.push(charge);
				}
				return false;
			}
		});

		// if this charge doesn't belong in any groups, start a new group
		if (chargeBelongsToAGroup === false)
		{
			var newChargeGroup = [];
			newChargeGroup.push(charge);
			chargeGroups.push(newChargeGroup);
		}
		// if charge doesn't have prior charge, start a new group
			
		
	});
	return chargeGroups;
}



innGrid.renderChargeGroups = function (chargeGroups) {
	var chargeGroupID = 0;
	var roomTypeNames = [];

	$.each(chargeGroups, function() {
        var chargeGroup = $(this);
		var chargeCount = chargeGroup.length;

		var room_type_name = chargeGroup[0].find("span[name='room_type_name']").text().trim();

		//Initialize or increment the count for the room_type_name
        if (!roomTypeNames[room_type_name]) {
            roomTypeNames[room_type_name] = 1;
        } else {
            roomTypeNames[room_type_name]++;
        }
	});

	console.log('roomTypeNames',roomTypeNames);
  
    $.each(chargeGroups, function() {
        var chargeGroup = $(this);
		var chargeCount = chargeGroup.length;

		if (chargeCount < 2)
			return;

		var dateStart = innGrid._getBaseFormattedDate(chargeGroup[0].find("span[name='selling-date']").text().trim());
		var dateEnd = innGrid._getBaseFormattedDate(chargeGroup[chargeCount-1].find("span[name='selling-date']").text().trim());
		var amount = chargeGroup[0].find("span[name='amount']").text().trim();
		var customer = chargeGroup[0].find("span[name='customer']").text().trim();
		var chargeType = chargeGroup[0].find("span[name='charge-type']").text().trim();
		var description =  chargeGroup[0].find("span[name='description']").text().trim();
		var bookingId = chargeGroup[0].find("span[name='booking_id']").text().trim();
		var room_name = chargeGroup[0].find("span[name='room_name']").text().trim();
		var room_type_name = chargeGroup[0].find("span[name='room_type_name']").text().trim();
		var default_charge_name = $('.default_charge_name').val();

		var period = "day";
        if(description == 'Daily '+default_charge_name)
        {
            period = "day";
            dateEnd = innGrid.getDateDiff(dateEnd, 0, null);
        }
        else if(description == 'Weekly '+default_charge_name)
        {
            period = "week";
            dateEnd = innGrid.getDateDiff(dateEnd, 6, null);
        }
        else if(description == 'Monthly '+default_charge_name)
        {
            period = "month";
            dateEnd = innGrid.getDateDiff(dateEnd, -1, 1);
        }
            
		var totalCharge = 0;
		var taxTotal = 0;

        $.each(chargeGroup, function() {
            $(this).addClass("collapsable");
            $(this).attr("name", chargeGroupID);

			$(this).find(".tax-amount").each(function() {
            	if(!$(this).hasClass('hidden')) {
                    taxTotal += parseFloat($(this).attr('data-real-taxes').replace(/,/g, '').trim());
                }
            });
            $(this).find('.charge').each(function() {
                totalCharge += Number($(this).attr('data-real-total-charge').replace(/[^0-9\.-]+/g,""));
            });
        });
        
        var url = $(location).attr('href');
        var segments = url.split( '/' );
		var function_name = segments[4];
        var booking_id_td = '';
        var booking_id_data = {html: ''};
        if(function_name == 'show_master_invoice')
        {
            booking_id_td = '<td/>';
			var booking_id_data = {html: bookingId};
        }

        var room_id_td = '';
        var room_id_data = {html: ''};

        var room_type_id_td = '';
        var room_type_id_data = {html: ''};
        if(function_name == 'show_master_invoice')
        {
            room_id_td = '<td/>';
			// var room_id_data = {html: room_name};
			if(
				innGrid.isGroupBookingFeatures
			)
				var room_id_data = {html: '<b>'+ ' '+roomTypeNames[room_type_name]+'' +'</b>'};
			else
				var room_id_data = {html: room_name};

			//Initialize or increment the count for the room_type_name
	        // if (!roomTypeNames[room_type_name]) {
	        //     roomTypeNames[room_type_name] = 1;
	        // } else {
	        //     roomTypeNames[room_type_name]++;
	        // }

			room_type_id_td = '<td/>';
			var room_type_id_data = {html: '<b>'+room_type_name+'</b>'};

        }
			
		

        // console.log('roomTypeNames',roomTypeNames);

        if(
        	innGrid.isGroupBookingFeatures
		){

	        if (jQuery.inArray(room_type_name, roomTypeNames) === -1) {
			    roomTypeNames.push(room_type_name); // Add the room type if not already present

			    var expandableTR = $('<tr />', {
			        'class': 'expandable',
			        'name': chargeGroupID,
			        'data-toggle': "popover",
			        'data-content': "Click here to expand",
			        'data-trigger': "hover",
			        'data-placement': "bottom"
			    }).append(
			        $('<td />', {
			            html: innGrid._getLocalFormattedDate(dateStart) + " " + l('to') + " " + innGrid._getLocalFormattedDate(dateEnd)
			        }).add(booking_id_td,
			            booking_id_data
			        ).add(booking_id_td,
			            room_id_data,
			        ).add(room_type_id_td,
			            room_type_id_data,
			        ).add('<td />', {
			            html: description
			        }).add('<td />', {
			            html: customer
			        }).add('<td />', {
			            html: chargeType
			        }).add('<td />', {
			            'class': 'text-right',
			            html: amount + "/" + period + " " + l('for') + " " + chargeCount + " " + period + "s"
			        }).add('<td />', {
			            'class': 'text-right',
			            html: number_format(parseFloat(taxTotal), 2, ".", "").replace(/\B(?=(\d{3})+(?!\d))/g, ",")
			        }).add('<td />', {
			            'class': 'text-right',
			            html: number_format(parseFloat(totalCharge), 2, ".", "").replace(/\B(?=(\d{3})+(?!\d))/g, ",")
			        }).add('<td />', {
			            'class': 'delete-td'
			        })
			    );
			    chargeGroup[0].before(expandableTR);
			    chargeGroupID++;
			} else {

				var hiddenClass = '';

				if(
					function_name == 'show_master_invoice'
				) {
					hiddenClass = ' hidden';
				}

				var expandableTR = $('<tr />', {
			        'class': 'expandable'+hiddenClass,
			        'name': chargeGroupID,
			        'data-toggle': "popover",
			        'data-content': "Click here to expand",
			        'data-trigger': "hover",
			        'data-placement': "bottom"
			    }).append(
			        $('<td />', {
			            html: innGrid._getLocalFormattedDate(dateStart) + " " + l('to') + " " + innGrid._getLocalFormattedDate(dateEnd)
			        }).add(booking_id_td,
			            booking_id_data
			        ).add(booking_id_td,
			            room_id_data,
			        ).add(room_type_id_td,
			            room_type_id_data,
			        ).add('<td />', {
			            html: description
			        }).add('<td />', {
			            html: customer
			        }).add('<td />', {
			            html: chargeType
			        }).add('<td />', {
			            'class': 'text-right',
			            html: amount + "/" + period + " " + l('for') + " " + chargeCount + " " + period + "s"
			        }).add('<td />', {
			            'class': 'text-right',
			            html: number_format(parseFloat(taxTotal), 2, ".", "").replace(/\B(?=(\d{3})+(?!\d))/g, ",")
			        }).add('<td />', {
			            'class': 'text-right',
			            html: number_format(parseFloat(totalCharge), 2, ".", "").replace(/\B(?=(\d{3})+(?!\d))/g, ",")
			        }).add('<td />', {
			            'class': 'delete-td'
			        })
			    );
			    chargeGroup[0].before(expandableTR);
			    chargeGroupID++;
			}
		} else {
			var expandableTR = $('<tr />', {
			        'class': 'expandable',
			        'name': chargeGroupID,
			        'data-toggle': "popover",
			        'data-content': "Click here to expand",
			        'data-trigger': "hover",
			        'data-placement': "bottom"
			    }).append(
			        $('<td />', {
			            html: innGrid._getLocalFormattedDate(dateStart) + " " + l('to') + " " + innGrid._getLocalFormattedDate(dateEnd)
			        }).add(booking_id_td,
			            booking_id_data
			        ).add(booking_id_td,
			            room_id_data,
			        ).add(room_type_id_td,
			            room_type_id_data,
			        ).add('<td />', {
			            html: description
			        }).add('<td />', {
			            html: customer
			        }).add('<td />', {
			            html: chargeType
			        }).add('<td />', {
			            'class': 'text-right',
			            html: amount + "/" + period + " " + l('for') + " " + chargeCount + " " + period + "s"
			        }).add('<td />', {
			            'class': 'text-right',
			            html: number_format(parseFloat(taxTotal), 2, ".", "").replace(/\B(?=(\d{3})+(?!\d))/g, ",")
			        }).add('<td />', {
			            'class': 'text-right',
			            html: number_format(parseFloat(totalCharge), 2, ".", "").replace(/\B(?=(\d{3})+(?!\d))/g, ",")
			        }).add('<td />', {
			            'class': 'delete-td'
			        })
			    );
			    chargeGroup[0].before(expandableTR);
			    chargeGroupID++;
		}

        // console.log('roomTypeNames',roomTypeNames);

	})
	$(".expandable").popover();
	// if there's more than 2 of such, assign a new group number for those
}

innGrid.expand = function(expandable) {
	$('#room_name').html('<span alt="room" title="room">Room</span>');
	expandable.hide();
	$(".collapsable").each(function() {
		if ($(this).attr("name") === expandable.attr("name"))
			$(this).fadeIn(1500);
	})
}

innGrid.expandAll = function() {
	$('#room_name').html('<span alt="room" title="room">Room</span>');
	$(".collapsable").fadeIn(1500);
	$(".expandable").hide();
}

innGrid.collapseAll = function() {
	$('#room_name').html('<span alt="room" title="room">Room count</span>');
	$(".collapsable").hide();
	$(".expandable").fadeIn(1500);
}


innGrid.getNumberOfDays = function() {
	var that = this;
    var checkInDate = innGrid._getBaseFormattedDate($("#check-in-date").text());
    var checkOutDate = innGrid._getBaseFormattedDate($("#check-out-date").text());
    if (!checkInDate || !checkOutDate) {
        return;
    }

    // set number of days as check_out_date - check_in_date
    var cid = checkInDate.split(/[-]/);
    var cod = checkOutDate.split(/[-]/);

    // Apply each element to the Date function
    var check_in_date = new Date(cid[0], cid[1] - 1, cid[2]);
    var check_out_date = new Date(cod[0], cod[1] - 1, cod[2]);
    var oneDay = 24 * 60 * 60 * 1000; // hours*minutes*seconds*milliseconds
    var diffDays = Math.round(Math.abs((check_in_date.getTime() - check_out_date.getTime()) / (oneDay)))
    return diffDays;
}


/**
	ACTION STARTS HERE
*/

$(function() {

	$("#print-invoice-button").on('click', function (){
		window.print();
	});
	
	$(".charge_row").popover();
	
	// Group Charges (Collapse)

	

	// don't collapse the invoice if the customer is staying for over 90 days. otherwise Minical crashes.
	var getNumberOfDays = innGrid.getNumberOfDays();
	var chargeRowLength = (innGrid.isGroupBookingFeatures == true) ? 2000 : 60;
    if ($(".charge_row").length < chargeRowLength)
	{

        var chargeGroups = innGrid.getChargeGroups();
		innGrid.renderChargeGroups(chargeGroups);

        innGrid.collapseAll();	
        $(document).on("click", ".collapse-all", function(){
             innGrid.collapseAll();
        });

        $(document).on("click", ".expand-all", function(){
            innGrid.expandAll();
        });

        $(".expandable").on("click", function(){
            innGrid.expand($(this));
        });

    }
    else
    {
        $(".collapse-all, .expand-all").addClass("disabled")
    }

	

	
	// Update Totals
	innGrid.updateTotals();
	
	var foliosWidth = $('#folios').outerWidth();
	var foliosWrapperWidth = $('#folios').parent('.panel').outerWidth();
	
	if (foliosWidth > foliosWrapperWidth) {
		$('.invoice-arrow-icon.fa-angle-right').show();
	}
	
	$('.invoice-arrow-icon').on('click', function () {
		var margin = 0;
		var foliosWidth = $('#folios').outerWidth();
		var foliosWrapperWidth = $('#folios').parent('.panel').outerWidth();
		var foliosLeftScroll = parseInt($('#folios').css('margin-left'));
		
		if($(this).hasClass('fa-angle-right')) {
			foliosLeftScroll -= 200;
			foliosLeftScroll = (foliosLeftScroll < (foliosWrapperWidth - foliosWidth)) ? (foliosWrapperWidth - foliosWidth) : foliosLeftScroll;
			
			$('.invoice-arrow-icon.fa-angle-left').show();
			if (foliosLeftScroll === (foliosWrapperWidth - foliosWidth)) {
				$('.invoice-arrow-icon.fa-angle-right').hide();
			}
			
		} else {
			foliosLeftScroll += 200;
			foliosLeftScroll = (foliosLeftScroll > 0) ? 0 : foliosLeftScroll;
			
			$('.invoice-arrow-icon.fa-angle-right').show();
			if (foliosLeftScroll === 0) {
				$('.invoice-arrow-icon.fa-angle-left').hide();
			}
			
		}
		
		$('#folios').css('margin-left', foliosLeftScroll);
	});
	
});
