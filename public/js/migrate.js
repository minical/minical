
innGrid.runQuery = function(index) {
	$.post( getBaseURL() + "auth/run_query/"+index, function (data) {
			// update default rate if new reservation or new check-in is being made
			$("#migration-results").append(data);
			$('html, body').animate({scrollTop: $(document).height()}, 'fast');
			if (data != "")
			{
				innGrid.runQuery(index+1);
				
			}
		}
	);
}

	
$(function() {
	innGrid.runQuery(0);
});