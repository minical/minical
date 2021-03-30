//populate room drop down list based on checkin,checkout and roomtype
innGrid.changeReport = function() {
	var state = $("select[name='state']").val();
	var from_date = $("input[name='from_date']").val();
	var to_date = $("input[name='to_date']").val();
	var booking_source = $("select[name='booking_source']").val();
	
    if(booking_source == undefined)
    {
        window.location.href = getBaseURL() +"reports/reservation/show_reservation_report/"+from_date+"/"+to_date+"/"+state;
    }else{
        window.location.href = getBaseURL() +"reports/reservation/show_reservation_report_cm/"+from_date+"/"+to_date+"/"+state+"/"+booking_source;
    }
	
}
$(document).ready(function(){            
    innGrid.hasBookingPermission = $('input[name=flag]').val();      
});

innGrid.columnSettings = function() {
    $('input.columns_checkbox').parents('li').each(function(){
        var c = $(this).find('.columns_checkbox').is(':checked');
        var name = $(this).attr('name');
        if(!c)
        {
            $('.reservation_table').find('th.'+name).css('display', 'none');
            $('tbody').find('td.'+name).css('display', 'none');
        }
        else
        {
            $('.reservation_table').find('th.'+name).css('display', 'table-cell');
            $('tbody').find('td.'+name).css('display', 'table-cell');
        }
    });
    $('.reservation_table').removeClass('hidden');
}

$(function() {
	// for beta method
	$('#printReportButton').click(function (){
		window.print();
	});

	//$("select[name='uidDDL']").change(changeUser);
	$("input[name='from_date'], input[name='to_date'], select[name='state'], select[name='booking_source']").on('change', innGrid.changeReport);
	$("input[name='from_date'],input[name='to_date']").datepicker({ dateFormat: 'yy-mm-dd' });
    
    
    $(document).off('click', '.booking td:not(#td-check-box,.td-group-box, .statement-box)').on('click', '.booking td:not(#td-check-box,.td-group-box, .statement-box)', function () {	
        var booking = $(this).parent();
        if(innGrid.hasBookingPermission == '1')
        {
            if (typeof $.fn.openBookingModal !== 'undefined' && $.isFunction($.fn.openBookingModal)) {
               $.fn.openBookingModal({
                    id: booking.data("booking-id")
                });
           }
        }
	});

    innGrid.columnSettings(); 

    $('.columns_checkbox').on('change', function(){
        
        innGrid.columnSettings();
        var columns = [];
        $('input.columns_checkbox:checked').each(function(){columns.push($(this).parents('li').attr('name'));});
        
        $.ajax({
            type: "POST",
            url: getBaseURL() + "reports/reservation/set_reservation_column_settings_AJAX/",
            data: { columns: columns.join() },
            dataType: "json",
            success: function( data ) {
                
            }
        });
    });

});
$('body').on('click','.create-new-booking',function(){
    if (typeof $.fn.openBookingModal !== 'undefined' && $.isFunction($.fn.openBookingModal)) {
        $.fn.openBookingModal();
    }
});