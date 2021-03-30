
$(document).ready(function(){            
    innGrid.hasBookingPermission = $('input[name=flag]').val();      
});

var bookings = new Array();
var totalBalance = 0;

var segments = window.location.href.split( '/' );
var cus_id = segments[5];

function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    var expires = "expires="+d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

$(function() {	
    
    $(window).unload(function(){
        var queryStringLength = window.location.search.length; 
        if(queryStringLength > 0 && ($('.pagination-div').children().length > 0) && $("#check-all").prop('checked'))
        {
            setCookie('bookingHistoryAllChecked', true);
        }else{
            setCookie('bookingHistoryAllChecked', false);
        }
    });
    
    $(window).on('load', function(){
        var queryStringLength = window.location.search.length; 
        if(queryStringLength > 0 && ($('.pagination-div').children().length > 0))
        {
            var check_all = (getCookie('bookingHistoryAllChecked') == 'true') ? 1 : 0;
            $("#check-all").prop('checked', check_all);
            $(".booking-checkbox").each(function() { this.checked = check_all;});        
        }else{
            setCookie('bookingHistoryAllChecked', false);
        }
        
        
        var bookingHistoryCheckedBookings = getCookie('bookingHistoryCheckedBookings');
        bookingHistoryCheckedBookings = bookingHistoryCheckedBookings ? JSON.parse(bookingHistoryCheckedBookings) : {};
        
        if(bookingHistoryCheckedBookings.state == window.location.search && bookingHistoryCheckedBookings.customer == $("#customer_id").val()) 
        {
            $(".booking-checkbox").each(function() { 
                if($.inArray($(this).attr('name'), bookingHistoryCheckedBookings.bookings) > -1) 
                {
                    this.checked = true;
                }
            }); 
        }
        else
        {
            setCookie('bookingHistoryCheckedBookings', false);
        }
        
    });
    
	$(".customer-profile").on("click", function() {
		$.fn.openCustomerModal({
			customer_id: $(this).attr("id")
		});
	});
    
	// hide all the bookings that are not checked
	var booking_ids = new Array();

	$("input[name='payment_date']").datepicker({
									  dateFormat: "yy-mm-dd"
									});
	
	$(document).off('click', '.booking td:not(#td-check-box,.td-group-box, .statement-box)').on('click', '.booking td:not(#td-check-box,.td-group-box, .statement-box)', function () {	
        var booking = $(this).parent();
        if($(booking).hasClass('invoices') == false) {
            if(innGrid.hasBookingPermission == '1')
            {
                if (typeof $.fn.openBookingModal !== 'undefined' && $.isFunction($.fn.openBookingModal)) {
                    $.fn.openBookingModal({
                        id: booking.data("booking-id")
                    });
                } 
            }
        }
	});
	
	$("#add-payment-button").on('click', function(){
		var customerID = $("#customer_id").val();
		innGrid.openPaymentDialog(customerID);	
        setCookie('bookingHistoryCheckedBookings', false);
	});

	// if the checkbox in th is clicked, then either check or uncheck all checkboxes below
	$("#check-all").click(function() {
		var check_all = this.checked;
		$(".booking-checkbox").each(function() { this.checked = check_all;});
        setCookie('bookingHistoryAllChecked', check_all);
        setCookie('bookingHistoryCheckedBookings', false);
    });
	
	// if one of the booking checkbox is unchecked, then uncheck "check-all" checkbox as well.
	$(".booking-checkbox").click(function() {
        if (this.checked == false){
			$('#check-all').prop('checked', false);
            setCookie('bookingHistoryAllChecked', false);
        }
        
        if(this != $("#check-all")[0])
        {
            var bookingHistoryCheckedBookings = getCookie('bookingHistoryCheckedBookings');
            bookingHistoryCheckedBookings = bookingHistoryCheckedBookings ? JSON.parse(bookingHistoryCheckedBookings) : {};
            var oldCheckedBookings = [];
            if(bookingHistoryCheckedBookings.state == window.location.search && bookingHistoryCheckedBookings.customer == $("#customer_id").val()) 
            {
                oldCheckedBookings = bookingHistoryCheckedBookings.bookings;
                for(var key in oldCheckedBookings)
                {
                    if($('input[name='+oldCheckedBookings[key]+']').length > 0 && !$('input[name='+oldCheckedBookings[key]+']').prop('checked'))
                    {
                        oldCheckedBookings.splice(key, 1);
                    }
                }
            }
            else
            {
                oldCheckedBookings = [];
            }
            var checkedBookings = $('.booking-checkbox:checked').map(function(){return $(this).attr('name')});
            checkedBookings = $.unique(oldCheckedBookings.concat(checkedBookings.toArray()));
            var bookingHistoryCheckedBookings = {
                bookings: checkedBookings,
                state: window.location.search,
                customer: $("#customer_id").val()
            };
            setCookie('bookingHistoryCheckedBookings', JSON.stringify(bookingHistoryCheckedBookings));
        }
        // statement totals
		var charge_total = 0;
		var payment_total = 0;
		var balance_total = 0;

		// hide bookings that are not selected
		$(".booking").each(function() {
			if($(this).find(".booking-checkbox").prop('checked')) {
				booking_ids.push($(this).attr("id"));
				$(this).removeClass("hidden-print");
				
				charge_total += parseFloat($(this).find(".booking-charge").html().replace(/,/g, '').trim());
				payment_total += parseFloat($(this).find(".booking-payment").html().replace(/,/g, '').trim());
				balance_total += parseFloat($(this).find(".booking-balance").html().replace(/,/g, '').trim());
			}
			else
			{
				$(this).addClass("hidden-print");
			}
		});
		
		$("#charge-total").html(number_format(charge_total, 2, ".", ""));
		$("#payment-total").html(number_format(payment_total, 2, ".", ""));
		$("#balance-total").html(number_format(balance_total, 2, ".", ""));


	});
	
	// customer type has been changed
	$("#customer_type").on('change', function() {
		var customerType = $("#customer_type").val();
		$("#customer-type-image").attr("class", customerType);
	});
	
	$("input[name='start-date'], input[name='end-date'],input[name='statement_date']").datepicker(
		{
			dateFormat: 'yy-mm-dd'
		}
	);
	

	$("#print-statement-button").on('click', function (){
        $(this).attr('disabled', true);
        var checkAll  = $('#check-all').prop('checked');
        var queryStringLength = window.location.search.length; 
        
        var statementId = $("#statement_id").val();
        
        var bookingHistoryAllChecked = getCookie('bookingHistoryAllChecked');
        var bookingHistoryCheckedBookings = getCookie('bookingHistoryCheckedBookings');
        bookingHistoryCheckedBookings = bookingHistoryCheckedBookings ? JSON.parse(bookingHistoryCheckedBookings) : {};
        var filter;
        var statementColumnShow = "display:block;";
        
        if(statementId)
        {
            if(($('.pagination-div').children().length > 0)){
                filter = {
                    customer_id: $('#customer_id').val(),
                    statement_id : statementId
                };
                statementColumnShow = "display:none;";
            }
            else{
                window.print();
                $(this).attr('disabled', false);
            }
        }
        else{
            if(
                !bookingHistoryAllChecked &&
                bookingHistoryCheckedBookings.state == window.location.search && 
                bookingHistoryCheckedBookings.customer == $("#customer_id").val() &&   
                bookingHistoryCheckedBookings.bookings && bookingHistoryCheckedBookings.bookings.length > 0
            ) 
            {
                filter = {
                    checkedBookings: bookingHistoryCheckedBookings.bookings,
                    statement_date: $('input[name="statement_date"]').val()
                };
            }
            else
            {
                filter = {
                    customer_id: $('.token').attr('id'),
                    status: $('select[name="status"]').val(),
                    start_date: $('input[name="start-date"]').val(),
                    end_date: $('input[name="end-date"]').val(),
                    show_paid: $('select[name="show_paid"]').val(),
                    group: $('select[name="groups"]').val(),
                    statement_date: $('input[name="statement_date"]').val(),
                    linked_group_id: $('input[name="linked_group_id"]').val(),
                    in_statement: $('select[name="in_statement"]').val(),
                    staying_guest_name: $('input[name="staying_guest_name"]').val()
                };
            }
        }
        
        if( (filter.checkedBookings || (checkAll == true && queryStringLength > 0 && ($('.pagination-div').children().length > 0))) || (statementId && $('.pagination-div').children().length > 0))
        {
            $.ajax({
                url: getBaseURL()+'customer/get_customer_bookings_AJAX',
                type: 'post',
                data: filter,
                dataType: "json",   
                success: function(response){
                    $("#print-statement-button").attr('disabled', false);
                    if(response){
                            // dynamic table generate
                            $('.room-invoice').addClass('hidden-print');
                            $('body').append(
                                $('<table/>',{
                                    class: "print-all-bookings table table-hover"
                                }).append(
                                    $('<tr/>',{
                                }).append(
                                    $('<th/>',{
                                        text: l("ID", true)
                                    })
                                ).append(
                                    $('<th/>',{
                                        text: l("Room", true)
                                    })
                                ).append(
                                    $('<th/>',{
                                        text: l("Staying Guest(s)", true)
                                    })
                                ).append(
                                    $('<th/>',{
                                        text: l("Start Date", true)
                                    })
                                ).append(
                                    $('<th/>',{
                                        text: l("End Date", true)
                                    })
                                ).append(
                                    $('<th>',{
                                        class: "text-right",
                                        text: l("Sub-total", true)
                                    })
                                ).append(
                                    $('<th/>',{
                                        class: "text-right",
                                        text: l("Tax", true)
                                    })
                                ).append(
                                    $('<th/>',{
                                        class: "text-right",
                                        text: l("Charge", true)
                                    })
                                ).append(
                                    $('<th/>',{
                                        class: "text-right",
                                        text: l("Payment", true)
                                    })
                                ).append(
                                    $('<th/>',{
                                        class: "text-right",
                                        text: l("Balance", true)
                                    })
                                ).append(
                                    $('<th/>',{
                                        class: "text-right",
                                        text: l("Group ID", true),
                                        style: statementColumnShow
                                    })
                                ).append(
                                    $('<th/>',{
                                        class: "text-right",
                                        text: l("Statement", true)+"#",
                                        style: statementColumnShow
                                    })
                                )
                            ).append(
                                $('<tbody/>',{
                                       class: "print-bookings-body"
                                })
                            ).append(
                                $('<tfoot/>',{
                                       class: "print-bookings-foot"
                                })
                            )
                        );
                            
                        // table body
                        var tBody = $('.print-all-bookings .print-bookings-body');
                        
                        var subTotalSum = 0;
                        var chargeTotalSum = 0;
                        var paymentTotalSum = 0;
                        var balanceSum = 0;
                        var taxTotal = {};

                        $.each(response, function(index, bookingData){
                            var guestName = (bookingData.guest_name == null) ? " " : bookingData.guest_name; 
                            
                            bookingData.charge_total = parseFloat(bookingData.charge_total);
                            bookingData.payment_total = parseFloat(bookingData.payment_total);
                            
                            var subTotal = bookingData.charge_total;
                            var taxexType = '';

                            chargeTotalSum += bookingData.charge_total;
                            paymentTotalSum += bookingData.payment_total;
                            console.log(paymentTotalSum, bookingData.payment_total);
                            var balance = bookingData.charge_total - bookingData.payment_total;
                            balanceSum += balance;

                            if (bookingData.taxes) {
                                $.each(bookingData.taxes, function(tax_key, tax) {
                                    tax.tax_total = parseFloat(tax.tax_total);
                                    subTotal -= tax.tax_total;
                                    if (!taxTotal[tax.tax_type]){
                                        taxTotal[tax.tax_type] = 0;
                                    }
                                    var taxAmount = parseFloat(tax.tax_total);
                                    taxTotal[tax.tax_type] += taxAmount;

                                    taxexType += '<small> <div class="tax">'
                                        +'<span class="tax-type">'+tax.tax_type+' </span>'
                                        +'<span class="tax-amount">'+number_format(parseFloat(taxAmount), 2, ".", "")+ '</span>'
                                        +'</div> </small>';
                                });
                            }else{
                                taxexType = 0.0;
                            }
                            subTotalSum += subTotal;

                            if (bookingData.guest_count > 1){
                                guestName = guestName + " "+l('and', true)+" "+(bookingData.guest_count-1)+" "+l('other(s)', true);
                            }
                            booking_statements = [];
                            if(bookingData.booking_statements){
                                var booking_statements_arr = bookingData.booking_statements.split(",");
                                if(booking_statements_arr.length > 0) {
                                    for(var key in booking_statements_arr){
                                        var booking_statement = booking_statements_arr[key].split("_")[1];
                                        booking_statements.push(booking_statement);
                                    }
                                }
                            }
                            booking_statements = booking_statements.join();
                            
                            tBody.append($('<tr/>',{
                                    class: 'booking state'+bookingData.state,
                                    'data-booking-id': bookingData.booking_id,
                                    style: 'background-color: #'+bookingData.color
                                }).append(
                                    $('<td/>',{
                                       text: bookingData.booking_id
                                    })
                                ).append(
                                    $('<td/>',{
                                       text: bookingData.room_name
                                    })
                                ).append(
                                    $('<td/>',{
                                       text: guestName
                                    })
                                ).append(
                                    $('<td/>',{
                                        class: 'date-td',
                                        text: bookingData.charge_start_selling_date
                                    })
                                )
                                .append(
                                    $('<td/>',{
                                        class: 'date-td',
                                        text: bookingData.charge_end_selling_date
                                    })
                                ).append(
                                    $('<td/>',{
                                        class: "text-right sub-charge",
                                        text: number_format(parseFloat(subTotal), 2, ".", ""),
                                    })
                                ).append(
                                    $('<td/>',{
                                        class: "text-right td-tax",
                                        html: taxexType,
                                    })
                                ).append(
                                    $('<td/>',{
                                        class: "text-right booking-charge",
                                        text: number_format(parseFloat(bookingData.charge_total), 2, ".", ""),
                                    })
                                )
                                .append(
                                    $('<td/>',{
                                        class: "text-right booking-payment",
                                        text: number_format(parseFloat(bookingData.payment_total), 2, ".", ""),
                                    })
                                )
                                .append(
                                    $('<td/>',{
                                        class: "text-right booking-balance",
                                        text: number_format(parseFloat(balance), 2, ".", ""),
                                    })
                                ).append(
                                    $('<td/>',{
                                        class: "text-right group-id",
                                        style: statementColumnShow,
                                        text: (bookingData.is_group_booking) ? bookingData.is_group_booking : ""
                                    })
                                ).append(
                                    $('<td/>',{
                                        class: "text-right statement-number ",
                                        style: statementColumnShow,
                                        text: (booking_statements) ? booking_statements : ""
                                    })
                                )
                            ); 
                        });  
                        // table footer 
                        var tFoot = $('.print-all-bookings .print-bookings-foot');
                       
                        var tFootTaxes = '';
                        if (taxTotal){
                            $.each(taxTotal, function(index, taxVal){
                                tFootTaxes += '<small> <div class="tax">'
                                        +'<span class="tax-type">'+index+' </span>'
                                        +'<span class="tax-amount">'+number_format(parseFloat(taxVal), 2, ".", "")+'</span>'
                                        +'</div> </small>';
                            });
                        }else{
                            tFootTaxes = 0.0;
                        }
                        tFoot.append(
                            $('<tr/>',{
                                class: ""
                            }).append(
                                $('<td/>',{
                                    colspan: "5",
                                    class: 'text-right',
                                    text: l("Total")+":"
                                })
                            ).append(
                                $('<td/>',{
                                    class: "text-right",
                                    id: l("sub-total"),
                                    text: number_format(parseFloat(subTotalSum), 2, ".", "")
                                })
                            ).append(
                                $('<td/>',{
                                    class: "text-right",
                                    id: "tax-total",
                                    html: tFootTaxes
                                })
                            )
                            .append(
                                $('<td/>',{
                                    class: "text-right",
                                    id: "charge-total",
                                    text: number_format(parseFloat(chargeTotalSum), 2, ".", "")
                                })
                            ).append(
                                $('<td/>',{
                                    class: "text-right",
                                    id: "payment-total",
                                    text: number_format(parseFloat(paymentTotalSum), 2, ".", "")
                                })
                            ).append(
                                $('<td/>',{
                                    class: "text-right",
                                    id: "balance-total",
                                    text: number_format(parseFloat(balanceSum), 2, ".", "")
                                })
                            )
                        );  
                        window.print();
                        $('.print-all-bookings').html('').remove();
                    }
                }
            });
        }else{
            $('.room-invoice').removeClass('hidden-print');
            if (booking_ids.length < 1) {
                alert(l("Select at least one invoice to print", true));
                $("#print-statement-button").attr('disabled', false);
                return;
            }
            showTotalAmount();
            window.print();
            showTotalAmount('1');
            $("#print-statement-button").attr('disabled', false);
        }
	});
    
    $('#clear-filter-btn').on('click', function(){
       window.location.assign(getBaseURL() + 'customer/history/'+$("#customer_id").val());  
    });
    
    $("#create-invoice-button").on('click', function (){
        $(this).attr('disabled', true);
        var checkAll  = $('#check-all').prop('checked');
        var queryStringLength = window.location.search.length; 
        if(checkAll == true && queryStringLength > 0 && ($('.pagination-div').children().length > 0)){
          
            $.ajax({
               url: getBaseURL()+'customer_statements/customer/create_booking_statement_Ajax',
               type: 'post',
               data: {
                    customer_id: $('.token').attr('id'),
                    status: $('select[name="status"]').val(),
                    start_date: $('input[name="start-date"]').val(),
                    end_date: $('input[name="end-date"]').val(),
                    show_paid: $('select[name="show_paid"]').val(),
                    group: $('select[name="groups"]').val(),
                    statement_date: $('input[name="statement_date"]').val(),
                    linked_group_id: $('input[name="linked_group_id"]').val(),
                    in_statement: $('select[name="in_statement"]').val(),
                    staying_guest_name: $('input[name="staying_guest_name"]').val(),
                    booking_ids: ''
                },
                success: function(data){
                    $("#create-invoice-button").attr('disabled', false);
                    alert(l('Statement created successfully!', true));
                    window.location.assign(getBaseURL() + 'customer/statements/'+$("#customer_id").val());
                }
            });
        }else{       
            
            var bookingHistoryCheckedBookings = getCookie('bookingHistoryCheckedBookings');
            bookingHistoryCheckedBookings = bookingHistoryCheckedBookings ? JSON.parse(bookingHistoryCheckedBookings) : {};
            var booking_ids = newarr = Array();      
            if(
                    bookingHistoryCheckedBookings.state == window.location.search && 
                    bookingHistoryCheckedBookings.customer == $("#customer_id").val() &&
                    bookingHistoryCheckedBookings.bookings && bookingHistoryCheckedBookings.bookings.length > 0
            ) 
            {
                booking_ids = bookingHistoryCheckedBookings.bookings;
				var arr = booking_ids;
                $.each(arr, function(index,item){
                    if(searchForItem(newarr,item)<0){
                        newarr.push(item);
                    }
                });
				booking_ids = newarr;
            }
            else
            {
                $('.room-invoice').find(".booking").each(function(){
                    if($(this).find(".booking-checkbox").prop('checked') && $(this).attr("data-booking-id") !== 'undefined' ){
                        booking_ids.push($(this).attr("data-booking-id"));
                        $(this).removeClass('hidden-print')
                    }    
                });
            }
            console.log(booking_ids);

            if (booking_ids.length < 1) {
                alert(l("Select at least one booking.", true));
                $("#create-invoice-button").attr('disabled', false);
                return;
            }
            $.ajax({
                type: "POST",
                url: getBaseURL() + "customer_statements/customer/create_booking_statement_Ajax",
                data: {
                    booking_ids : booking_ids
                },
                success: function (data) 
                {
                    $("#create-invoice-button").attr('disabled', false);
                    alert(l('Statement created successfully!', true));
                    setCookie('bookingHistoryCheckedBookings', false);
                    window.location.assign(getBaseURL() + 'customer/statements/'+$("#customer_id").val());
                }
            });
        }
	});
	
	function searchForItem(array, item){
		var i, j, current;
		for(i = 0; i < array.length; ++i){
			if(item.length === array[i].length){
				current = array[i];
				for(j = 0; j < item.length && item[j] === current[j]; ++j);
				if(j === item.length)
					return i;
			}
		}
		return -1;
	}
	    
    // gateway button
    var $methods_list = $('select[name="payment_type_id"]');
    var gateway_button = $('input[name="use_gateway"]');
    var selected_gateway = $('input[name="use_gateway"]').data('gateway_name');

    var gatewayTypes = {
        'stripe': 'Stripe',
        'PayflowGateway': 'PayPal Payflow Pro',
        'FirstdataE4Gateway': 'FirstData Gateway e4(Payeezy)',
        'ChaseNetConnectGateway': 'Chase Payment Gateway',
        'AuthorizeNetGateway': 'Authorize.Net',
        'PayuGateway': 'Payu Gateway',
        'QuickbooksGateway': 'Quickbooks Gateway',
        'ElavonGateway': 'Elavon My Virtual Merchant',
        'MonerisGateway': 'Moneris eSelect Plus',
        'CieloGateway': 'Cielo Gateway'
    };

    selected_gateway = gatewayTypes[selected_gateway];
    
    $methods_list.prop('disabled', false);
    gateway_button.prop('checked',0);

    gateway_button.on('click',function(){
        $that = $(this);
        var checked = $that.prop('checked');
        $methods_list.prop('disabled', checked);
		var manualPaymentCapture = $("#manual_payment_capture").val();
        if(checked)
        {
			if(manualPaymentCapture == 1)
			{
				$('#auth_and_capture').removeClass('hidden');
				$('#authorize_only').removeClass('hidden');
				$('#add_payment_normal').addClass('hidden');
			}
			else{
				$('#addPaymentButton').removeClass('hidden');
				$('#add_payment_normal').addClass('hidden');
				$('#auth_and_capture').addClass('hidden');
				$('#authorize_only').addClass('hidden');
			}
            $methods_list
                .append(
                $('<option></option>',{
                    id : 'gateway_option'
                })
                    .val('gateway')
                    .html(selected_gateway)
            );
            $methods_list.val('gateway');
            
        }else{
			if(manualPaymentCapture == 1)
			{
				$('#auth_and_capture').addClass('hidden');
				$('#authorize_only').addClass('hidden');
				$('#add_payment_normal').removeClass('hidden');
			}
			else{
				$('#addPaymentButton').addClass('hidden');
				$('#add_payment_normal').removeClass('hidden');
				$('#auth_and_capture').removeClass('hidden');
				$('#authorize_only').removeClass('hidden');
			}
            $('#gateway_option').remove();
        }
    });
    
    var isUnderPaymentCondition = false;  
	$("#openPaymentModal").on('click', function (){
        $(this).attr('disabled', true);
        
        isUnderPaymentCondition = false;
        
        $("#addPaymentButton").prop('disabled', false);
        $("#overPaymentNotice").text("");
        
        totalBalance = 0;       
        bookings = [];
        var checkAll  = $('#check-all').prop('checked');
        var queryStringLength = window.location.search.length; 
        
        var statement_id = $('#statement_id').val();
         
        if(checkAll == true && queryStringLength > 0 && ($('.pagination-div').children().length > 0) || (statement_id && $('.pagination-div').children().length > 0 ))
        {
            if(statement_id)
            {
                filter = {
                    customer_id: $('#customer_id').val(),
                    statement_id : statement_id
                };
            }
            else
            {
                filter = {
                    customer_id: $('.token').attr('id'),
                    status: $('select[name="status"]').val(),
                    start_date: $('input[name="start-date"]').val(),
                    end_date: $('input[name="end-date"]').val(),
                    show_paid: $('select[name="show_paid"]').val(),
                    group: $('select[name="groups"]').val(),
                    statement_date: $('input[name="statement_date"]').val(),
                    linked_group_id: $('input[name="linked_group_id"]').val(),
                    in_statement: $('select[name="in_statement"]').val(),
                    staying_guest_name: $('input[name="staying_guest_name"]').val()
                };
            }
            $.ajax({
                url: getBaseURL()+'customer/get_customer_bookings_AJAX',
                type: 'post',
                data: filter,
                dataType: "json",
                success: function(data){
                    if(data){
                       $.each(data, function(index, value){
                            var balance = value.charge_total - value.payment_total;
                            if(statement_id)
                            {
                                totalBalance += balance;
                                bookings.push(
                                    {
                                        booking_id: value.booking_id,
                                        balance: balance
                                    }
                                );
                            }
                            else
                            {
                                if(balance > 0){
                                    totalBalance += balance;
                                    bookings.push(
                                        {
                                            booking_id: value.booking_id,
                                            balance: balance
                                        }
                                    );
                                }
                            }
                        });
                        if(bookings.length > 0)
                        {
                            $("#paymentNotice").html(l('This payment will be distributed among', true)+" "+bookings.length+" "+l('bookings that have outstanding balance'));
                            $("input[name='payment_amount']").val(number_format(totalBalance, 2, ".", ""));
                            $("textarea[name='description']").val(l('Part of')+" "+number_format(totalBalance, 2, ".", "")+" "+l('payment'));
                            $('#addPaymentsModal').modal('show');
                        }
                        else
                        {
                            alert(l("Please select at least one invoice with an outstanding balance", true));
                        }
                    }
                    $("#openPaymentModal").attr('disabled', false);
                }
            });         
        }
        else
        {

            $(".booking").each(function() {
                balance = parseFloat($(this).find(".booking-balance").html().replace(/,/g, '').trim());
                if(statement_id){
                    totalBalance += balance;
                    bookings.push(
                    {
                        booking_id: $(this).attr("data-booking-id"),
                        balance: balance
                    });
                }
                else if ($(this).find(".booking-checkbox").prop('checked') && balance > 0) {
                    totalBalance += balance;
                    bookings.push(
                        {
                            booking_id: $(this).attr("data-booking-id"),
                            balance: balance
                        });
                } 
            });
            $("#paymentNotice").html(l('This payment will be distributed among')+" "+bookings.length+" "+l('bookings that have outstanding balance'));

            if (bookings.length < 1) {
                alert(l("Please select at least one invoice with an outstanding balance", true));
                $("#openPaymentModal").attr('disabled', false);
                return;
            }
            else
            {
                $("input[name='payment_amount']").val(number_format(totalBalance, 2, ".", ""));
                $("textarea[name='description']").val(l('Part of')+" "+number_format(totalBalance, 2, ".", "")+" "+l('payment'));
                $('#addPaymentsModal').modal('show');
            }
            $("#openPaymentModal").attr('disabled', false);
        }
	});
	
    
	$(".addPaymentButton").on("click", function(e) {
        var totalPayment = number_format(totalBalance, 2, ".", "");
        var capture_payment_type = $(this).attr('id');
        
        var totalPaidAmount = parseFloat($("input[name='payment_amount']").val());
        $(this).prop('disabled', false);
      
        if(totalPaidAmount > totalPayment){
            isUnderPaymentCondition = false;
            $("#overPaymentNotice").text(l("The amount entered exceeds the amount owed", true));
            $("textarea[name='description']").val(l('Part of')+" "+totalPaidAmount+" "+l('payment'));
            $(this).prop('disabled', true);
            return;
        }
      
        
        if(totalPaidAmount < totalPayment){
            $("#overPaymentNotice").text("The amount entered is less than the amount owed (click 'Add Payment' again to add payment)");
            $("textarea[name='description']").val(l('Part of')+" "+totalPaidAmount+" "+l('payment'));
            

            if(!isUnderPaymentCondition)
            {
                isUnderPaymentCondition = true;
                e.preventDefault();
                return;
            }
        }
        
        if(totalPaidAmount == totalPayment){
            isUnderPaymentCondition = false;
            $("#overPaymentNotice").text("");
        }
        
        if(totalPaidAmount <= totalPayment){
            $(this).prop('disabled',true);
            $.post(getBaseURL() + 'customer/insert_payments_AJAX', {
                bookings: bookings,
                customer_id: cus_id,
                payment_type_id: $("select[name='payment_type_id']").val(),
                payment_date: $("input[name='payment_date']").val(),
                total_balance: totalPaidAmount,
                description: $("textarea[name='description']").val(),
                distribute_equal_amount: $("select[name='payment_distribution']").val(),
				capture_payment_type : capture_payment_type
            }, function (data) {
                var msg = "";
				// if(data){
    //                 var data = JSON.parse(data);
    //                 $.each(data, function(index, value){
    //                     msg += l('Booking', true)+" "+l('Id')+": "+ value['booking_id']+"   "+l('Error', true)+": "+value['error_msg']+"\n\n";
    //                 });
    //                 alert(msg);
    //             } 
    //             else{
                    window.location.reload();
                // }
            });
        }
    
    });  
    
    // edit statement modal open
    $('#openStatementModal').on("click", function(){
       $('#editStatementModal').modal('show');
       $('#editStatementButton').attr('disabled', false);
    });
    
    // update statement name 
    $("#editStatementButton").on("click", function(){
        $(this).attr('disabled', true);
    
        $.ajax({
            url: getBaseURL()+'customer/update_statement_AJAX',
                type: 'post',
                data: {
                    statement_id: $('#editStatementModal').attr('data-statement-id'),
                    statement_name : $('#statement-name').val()
                },
                success: function(data){
                    if(data != ''){
                        alert(data);
                        $('.token-label span:first-child').text($('#statement-name').val()+"(#"+$("#editStatementModal").attr('data-statement-no')+")");
                        $('#print-statement-name').html("<strong>"+l('Statement name', true)+": </strong> "+$('#statement-name').val());
                        $('#editStatementModal').modal('hide');
                    }
                }
        }); 
    });
    
    // delete statement
    $('#delete-statements-btn').on("click", function(){
        var statementID = $(this).attr("data-statement-id");
        $("#confirm_delete_dialog_statement")
		.html(l('Delete statement(s)', true)+'?')
		.dialog({
			title:l('Delete statement(s)', true),
			buttons: {
				"Confirm Delete":function() {	
                    $.ajax({
                        type: "POST",
                        url: getBaseURL() + "customer/delete_booking_statement_AJAX",
                        data: {
                            statement_id : statementID
                        },
                        success: function (data) 
                        {
                            if(data != '')
                            {
                                alert(data);
                                window.location.assign(getBaseURL() + 'customer/statements/'+$("#customer_id").val());
                            }
                        }
                    });
                    $(this).dialog("close");
                },
				"Cancel": function() {
					$(this).dialog("close");
				}
            }
        });    
        $("#confirm_delete_dialog_statement").dialog("open");
    });
    
    // print statement's booking
    $('#print-booking-statement-btn').on("click", function(){
        $(this).attr('disabled', true);
       
        if($('.pagination-div').children().length > 0){
             $.ajax({
                type: "POST",
                url: getBaseURL() + "customer/statement_detail",
                data: {
                    statement_id : $("#statement_id").val()
                },
                dataType: "JSON",
                success: function (data) 
                {
                    if(data != '')
                    {
                       console.log(data) 
                    }
                }
            });
        }else{
            window.print();
        }
        $(this).attr('disabled', false);
    });
    $("#search-guest-name").on("keyup", function(event) {
        $(this).parents().find('input[name="staying_guest_name"]').val($(this).val());  
        if (event.which == 13) {
            $('#click-statement') .trigger("click");
        }
    });
    $('#search-customer-btn').on("click", function(){
       $('#click-statement') .trigger("click");
    });
});
function showTotalAmount(data){
                var charge_total = 0;
                var sub_total = 0;
                var payment_total = 0;
                var balance_total = 0;
                var taxArray = {};	
                var totalTax = 0;

                $('.room-invoice').find(".booking").each(function() {
                    if(data === '1'){
                        $(this).removeClass("hidden-print");
                        $(this).find('.td-tax').find('.tax').each(function () {				
                            var taxType = $(this).find('.tax-type').html();				

                            if (typeof taxArray[taxType] === 'undefined') { //These checks could be wrong (type comparison issue)
                                taxArray[taxType] = 0;
                            }

                            var tax = Number($(this).find('.tax-amount').text().replace(/[^0-9\.-]+/g,""));
                            taxArray[taxType] += tax;
                            totalTax += tax;
                        });
                        sub_total += parseFloat($(this).find(".sub-charge").html().replace(/,/g, '').trim());
                        charge_total += parseFloat($(this).find(".booking-charge").html().replace(/,/g, '').trim());
                        payment_total += parseFloat($(this).find(".booking-payment").html().replace(/,/g, '').trim());
                        balance_total += parseFloat($(this).find(".booking-balance").html().replace(/,/g, '').trim());
                        $(this).addClass("hidden-print");
                    }else{
                        if($(this).find(".booking-checkbox").prop('checked')) {
                            $(this).removeClass("hidden-print");
                            $(this).find('.td-tax').find('.tax').each(function () {				
                                var taxType = $(this).find('.tax-type').html();				
                                if (typeof taxArray[taxType] === 'undefined') { //These checks could be wrong (type comparison issue)
                                    taxArray[taxType] = 0;
                                }
                                var tax = Number($(this).find('.tax-amount').text().replace(/[^0-9\.-]+/g,""));
                                taxArray[taxType] += tax;
                                totalTax += tax;
                            });
                            sub_total += parseFloat($(this).find(".sub-charge").html().replace(/,/g, '').trim());
                            charge_total += parseFloat($(this).find(".booking-charge").html().replace(/,/g, '').trim());
                            payment_total += parseFloat($(this).find(".booking-payment").html().replace(/,/g, '').trim());
                            balance_total += parseFloat($(this).find(".booking-balance").html().replace(/,/g, '').trim());
                        }
                        else
                        {
                            $(this).addClass("hidden-print");
                        }
                    }
                    
                    });
                $('#tax-total').html('');
                $.each(taxArray, function(key, value) {
                    $('.room-invoice').find('#tax-total')
                        .append('<div><span class="small">'+key+'</span><span>'+number_format(value, 2, ".", "")+'</span></div>');
                });
                $("#sub-total").html(number_format(sub_total, 2, ".", ""));
                $("#charge-total").html(number_format(charge_total, 2, ".", ""));
                $("#payment-total").html(number_format(payment_total, 2, ".", ""));
                $("#balance-total").html(number_format(balance_total, 2, ".", ""));
}

function createBookingGroup(obj){
    $.ajax({
            type: "POST",
            url: getBaseURL() + "booking/create_booking_group_Ajax",
            data: {
                booking_ids : $(obj).attr('booking_id'),
                group_id : $(obj).val()
            },
            success: function (data) 
            {
                 console.log(data);
            }
    });
}

function clearAllGroups(){
    var r = confirm(l("All groups will be cleared. Are you sure?", true));
    if (r == true) {
        $.ajax({
            type: "POST",
            url: getBaseURL() + "customer/clear_all_groups_Ajax",
            data: {
                customer_id : $("#customer_id").val()
            },
            success: function (data) 
            {
                window.location.reload();
            }
        });
    }
}

function filterStatementDate(){
    $('#click-statement').click();
}
