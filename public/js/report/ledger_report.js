innGrid.drawGoogleChart = function(chartData) {
	// Some raw data (not necessarily accurate)
	// Create and populate the data table.
	var data = google.visualization.arrayToDataTable(chartData);

	// Create and draw the visualization.
	var ac = new google.visualization.ComboChart(document.getElementById('visualization'));
	ac.draw(data, {
	  title : '',
	  vAxis: {title: l('Charges')+" / "+l('Payments')},
	  hAxis: {title: l("Date")},
	  seriesType: "line",
	  series: {5: {type: "line"}}
	});


}

$("#dateStart").datepicker({
    dateFormat: 'yy-mm-dd',
    onClose: function() { 
        $("#dateEnd").datepicker(                             
            "change",
            { minDate: new Date($('#dateStart').val()) }
        );
    }
});
$("#dateEnd").datepicker({                                  
    dateFormat: 'yy-mm-dd',
    onClose: function() {
        $("#dateStart").datepicker(
            "change",
            { maxDate: new Date($('#dateEnd').val()) }
        );
    }
});

innGrid.getDate = function(dateString){
	var dateArray = dateString.split("-");
	return new Date(dateArray[0], dateArray[1] - 1, dateArray[2]);
}

innGrid.getWeekday = function(dateString) {
	var weekdayNumber = innGrid.getDate(dateString).getDay();
	switch(weekdayNumber){
		case 0: return "Sunday";
		case 1: return "Monday";
		case 2: return "Tuesday";
		case 3: return "Wednesday";
		case 4: return "Thursday";
		case 5: return "Friday";
		case 6: return "Saturday";
	}
					
}

innGrid.renderReport = function(dateStart, dateEnd, groupBy, customerTypeId) {
	
	if (dateStart != "" && dateEnd != "")
	{
		
		$.ajax({
			type: "POST",
			url: getBaseURL() + "reports/ledger/get_ledger_report_AJAX/",
			data: { 
				dateStart: dateStart,
				dateEnd: dateEnd,
				groupBy: groupBy,
                customerTypeId: customerTypeId
			},
			dataType: "json",
			success: function( data ) {
				$("#report-content").html("");
	
				// for Google Chart
				var chartData = [
						[
							{label: l('Date'), type: 'string'},
						    {label: l('Charges'), type: 'number'},
						    {label: l('Payment'), type: 'number'},
					    ]
				    ];
				
				// render the report table's body
				$.each(data, function(index, value) {

					if ($("#groupBy").val() === "daily")
					{
						var dateLabel = "<a href='"+getBaseURL()+"reports/ledger/show_daily_report/"+index+"'>"+index+" "+innGrid.getWeekday(index)+"</a>";
					}
					else if ($("#groupBy").val() === "monthly")
					{
						var dateLabel = index;
					}
					

					var chargeTotal = parseFloat(value.charge_total).toFixed(2);
					var roomChargeTotal = parseFloat(value.room_charge_total).toFixed(2);
					var paymentTotal = parseFloat(value.payment_total).toFixed(2);
					var balance = parseFloat(value.charge_total - value.payment_total).toFixed(2);

					// grey out the future charges/payments
					if (innGrid.getDate(index) >= innGrid.getDate($("#sellingDate").val()))
						var future = "bg-warning futureRow";
					else
						var future = "";
						

					var billable_bookings_col = "";

					if(innGrid.companyID == 2242){
						billable_bookings_col = '<td class="text-center"><span class="booking-count">'+value.charges_booking_count+'</span></td>';
					}
                                        
					var row = $("<tr>", {
					    "class": "salesRow " + future,   
					    "data-toggle": 'popover',
					    "data-content": l('This is a forecast based on existing & upcoming bookings.'),
					    "data-trigger": 'hover',
					    "data-placement": 'bottom'
					}).append(
						$("<td>", {
							html: dateLabel
						})	
					).append(
						$("<td>", {
							"class": "text-center",
							html: "<span class='booking-count'>"+value.booking_count+"</span> (<span class='occupancy-rate'>"+parseFloat(value.occupancy_rate*100).toFixed(1)+"</span>%)"
						})
					).append(
						billable_bookings_col
					).append(
						$("<td>", {
							"class": "text-right revPAR",
							text: value.revPAR.toFixed(2)
						})
					).append(
						$("<td>", {
							"class": "text-right ADR",
							text: isNaN(roomChargeTotal/value.booking_count) || roomChargeTotal/value.booking_count == 'Infinity' ? '0.00' : (roomChargeTotal/value.booking_count).toFixed(2)
							//value.ADR.toFixed(2)
						})
					).append(
						$("<td>", {
							"class": "text-right room-charge-total",
							text: roomChargeTotal
						})
					).append(
						$("<td>", {
							"class": "text-right charge-total",
							text: chargeTotal
						})
					).append(
						$("<td>", {
							"class": "text-right payment-total",
							text: paymentTotal
						})
					).append(
						$("<td>", {
							"class": "text-right balance",
							text: balance
						})
					);

					$("#report-content").append(row);

					chartData.push([index, chargeTotal, paymentTotal]);

				});

				$(".futureRow").popover();
	
				innGrid.drawGoogleChart(chartData);

				// occupancy total
				var bookingCountTotal = 0;
				$(".booking-count").each(function (data) {
					bookingCountTotal += parseFloat($(this).html().replace(/,/g, ''));
				});
				$("#monthly-booking-count-total").html(bookingCountTotal);


				// occupancy total
				var occupancyRateTotal = 0;
				var count = 0;
				$(".occupancy-rate").each(function (data) {
					occupancyRateTotal += parseFloat($(this).html().replace(/,/g, ''));
					count++;
				});
				$("#monthly-occupancy-rate-total").html((occupancyRateTotal/count).toFixed(2));

				// revPar average
				var revPARTotal = 0;
				var count = 0;
				$(".revPAR").each(function (data) {
					revPARTotal += parseFloat($(this).html().replace(/,/g, ''));
					count++;
				});
				$("#monthly-revPAR-average").html((revPARTotal/count).toFixed(2));

				// get charge total
				var roomChargeTotal = 0;
				$(".room-charge-total").each(function (data) {
					roomChargeTotal += parseFloat($(this).html().replace(/,/g, ''));
				});
				$("#monthly-room-charge-total").html(innGrid.addCommas(roomChargeTotal.toFixed(2)));

				$("#monthly-ADR-average").html((roomChargeTotal/bookingCountTotal).toFixed(2));

				// get charge total
				var chargeTotal = 0;
				$(".charge-total").each(function (data) {
					chargeTotal += parseFloat($(this).html().replace(/,/g, ''));
				});

				$("#monthly-charge-total").html(innGrid.addCommas(chargeTotal.toFixed(2)));

				// get payment total
				var paymentTotal = 0;
				$(".payment-total").each(function (data) {
					paymentTotal += parseFloat($(this).html().replace(/,/g, ''));
				});

				$("#monthly-payment-total").html(innGrid.addCommas(paymentTotal.toFixed(2)));
				

				// calculate and post balances
				var balance_total = 0;
				$(".balance").each(function (data) {
					var chargeTotal = parseFloat($(this).parent().find('.charge-total').html().replace(/,/g, '')).toFixed(2);
					var paymentTotal = parseFloat($(this).parent().find('.payment-total').html().replace(/,/g, '')).toFixed(2);
					var balance = (chargeTotal - paymentTotal).toFixed(2);
					balance_total += parseFloat(balance);
					$(this).html(innGrid.addCommas(balance));
				});

				$("#monthly-balance-total").html(innGrid.addCommas(balance_total.toFixed(2)));

			}
		});
	}


}


$(function() {

	$('#printReportButton').click(function (){
        window.print();
	});
    
    $('#generateSalesReport').click(function (){
		var dateStart = $("input[name='date_start']").val();	
        var dateEnd = $("input[name='date_end']").val();	
        var reportType = $("select[name='report_type']").val();	
        var roomType = $("select[name='room_type']").val();
        
        
        window.location.href = getBaseURL() +"reports/show_sales_summary_report/"+dateStart+"/"+dateEnd+"/"+reportType+"/"+roomType;
	});
        
    $('#generateDepositReport').click(function (){
		var dateStart = $("input[name='date_start']").val();	
        var dateEnd = $("input[name='date_end']").val();
        var depositType = $("select[name='depositType']").val();
        
        window.location.href = getBaseURL() +"reports/advanced_deposits/"+dateStart+"/"+dateEnd+"/"+depositType;
    });

    $('#generateFolioAuditReport').click(function (){
		$('.monthselectpicker').css('display', 'none');
        var startDate = $("#dateStart").val(); 
	    var endDate = $("#dateEnd").val();
		if(startDate == '' || endDate == '') {
		    alert(l("Start date or End date cannot be blank."));
		    return false;
	    }
		else {
            window.location.href = base_url + "/reports/show_folio_audit_trail_report/" + startDate+'--'+endDate;
		}
    });

	$("#dateStart, #dateEnd, input[name='date_start'], input[name='date_end']").datepicker({ dateFormat: 'yy-mm-dd' });

	var dateStart = $("#dateStart").val();
	var dateEnd = $("#dateEnd").val();
	var groupBy = $("#groupBy").val();
	var customerTypeId = $('select[name="customer_type_id"]').val();
    if ($("#report-content").length) {
		innGrid.renderReport(dateStart, dateEnd, groupBy, customerTypeId);
	}

	$("#generateReport").on('click', function() {	
		var dateStart = $("#dateStart").val();
		var dateEnd = $("#dateEnd").val();
		var groupBy = $("#groupBy").val();
		var customerTypeId = $('select[name="customer_type_id"]').val();
        var customerType = $('select option[value="'+customerTypeId+'"]').text();
        
        $('#selected-customer-type').text(l('Customer Type')+": "+customerType);
		innGrid.renderReport(dateStart, dateEnd, groupBy, customerTypeId);
	});
    
    $("#downloadReport").on('click', function() {	
		var dateStart = $("#dateStart").val();
		var dateEnd = $("#dateEnd").val();
		var groupBy = $("#groupBy").val();
		var customerTypeId = $('select[name="customer_type_id"]').val();
        var customerType = $('select option[value="'+customerTypeId+'"]').text();
        
        $('#selected-customer-type').text(l('Customer Type')+": "+customerType);
        window.location.href = getBaseURL() + "reports/ledger/download_summary_csv_export/"+dateStart+"/"+dateEnd+"/"+groupBy+"/"+customerTypeId;
		innGrid.renderReport(dateStart, dateEnd, groupBy, customerTypeId);
	});

	$("#dateStart, #dateEnd").on("change", function () {
		$("#dateStartPrint").text($("#dateStart").val());
		$("#dateEndPrint").text($("#dateEnd").val());
	});

	$('#report_type').change(function () {
		var report_type = $(this).val();
		if(report_type=='room_type'){
			$('#room_type').show();
		}
		else{
			$('#room_type').hide();
		}
	});
	
	$('.show_daily_account_report').click(function(){ 
		var startDate = $("#dateStart").val(); 
	    var endDate = $("#dateEnd").val();
        if(startDate == '' || endDate == '') {
		    alert(l("Start date or End date cannot be blank."));
		    return false;
	    }
		else{
			window.location.assign(getBaseURL() +"reports/daily_account_summary/" + startDate+'--'+endDate);
		}
	});
});