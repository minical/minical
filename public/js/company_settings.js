
// update Current Time based on timezone
innGrid.updateCurrentTime = function() {
	
	$.post( getBaseURL() + "company/get_current_time_in_JSON/", 
		{
			time_zone: $('select[name="time_zone"]').val() 
		}, function (data) {
			$("[name='current_time']").text(data);
		}, 'json'
	);
}

$(function() {
	innGrid.updateCurrentTime();
	$("select[name='time_zone']").change(innGrid.updateCurrentTime);
});