//populate room drop down list based on checkin,checkout and roomtype
innGrid.changeGroupReport = function() {console.log('yes');
	var group_id = $("input[name='group_id']").val();
	var from_date = $("input[name='from_date']").val();        
    var to_date = $("input[name='to_date']").val();
    console.log(from_date);
        console.log(to_date);
	window.location.href = getBaseURL() + "reports/reservation/group_report/"+from_date+"/"+to_date+"/"+group_id;
}

$(function() {
	$('#printReportButton').click(function (){
		window.print();
	});

	//$("select[name='uidDDL']").change(changeUser);
	$("input[name='from_date'], input[name='group_id'], input[name='to_date']").on('change', innGrid.changeGroupReport);
        $("#generateReport").on('click', innGrid.changeGroupReport);
	$("input[name='from_date'], input[name='to_date']").datepicker({ dateFormat: 'yy-mm-dd' });
   
});
