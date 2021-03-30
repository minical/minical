//populate room drop down list based on checkin,checkout and roomtype
innGrid.changeReport = function() {
	var state = $("select[name='state']").val();
	var from_date = $("input[name='from_date']").val();
    var to_date = $("input[name='to_date']").val();
	window.location.href = getBaseURL() + "reports/reservation/show_inhouse_report/"+from_date+"/"+to_date+"/"+state;
}

innGrid.columnSettings = function() {
    $('input.columns_checkbox').parents('li').each(function(){
        var c = $(this).find('.columns_checkbox').is(':checked');
        var name = $(this).attr('name');
        if(!c)
        {
            $('.inhouse_table').find('th.'+name).css('display', 'none');
            $('tbody').find('td.'+name).css('display', 'none');
        }
        else
        {
            $('.inhouse_table').find('th.'+name).css('display', 'table-cell');
            $('tbody').find('td.'+name).css('display', 'table-cell');
        }
    });
    $('.inhouse_table').removeClass('hidden');
}

$(function() {
	$('#printReportButton').click(function (){
		window.print();
	});
        
    innGrid.columnSettings();     
        
	//$("select[name='uidDDL']").change(changeUser);
	$("input[name='from_date'], select[name='state'], input[name='to_date']").on('change', innGrid.changeReport);
	$("input[name='from_date'], input[name='to_date']").datepicker({ dateFormat: 'yy-mm-dd' });
        
    $('.columns_checkbox').on('change', function(){
        
        innGrid.columnSettings();
        var columns = [];
        $('input.columns_checkbox:checked').each(function(){columns.push($(this).parents('li').attr('name'));});
        
        $.ajax({
			type: "POST",
			url: getBaseURL() + "reports/reservation/set_inhouse_column_settings_AJAX/",
			data: { columns: columns.join() },
			dataType: "json",
			success: function( data ) {
				
            }
        });
    }); 
});
