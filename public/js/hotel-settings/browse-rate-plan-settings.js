
$(function() {

	$('.set-default-rate-plan').click(function () {
		$.post(getBaseURL() + 'settings/rates/set_default_rate_plan', {
			rate_plan_id: $(this).attr("id")
		},	function (){
			window.location.reload();
		});		
	});
	
	$('.delete-rate-plan-button').on('click' , function () {
		var that = this;
		var ratePlanID = $(this).parent().attr('id');
		var ratePlanName = $(this).parent().parent().find('.rate-plan-name').html();
		
		//Set custom buttons for delete dialog
		$("#confirm_delete_dialog")
		.html(l('Are you sure you want to delete ' + ratePlanName + '?'))
		.dialog({
			title: l('Delete Rate Plan'),
			buttons: {
				"Delete Rate Plan":function() {
					$.post(getBaseURL() + 'settings/rates/delete_rate_plan', {
						rate_plan_id: ratePlanID
						}, function (results) {
							console.log(results)
							$(that).parent().parent().remove();  //delete line of X button
						});
					$(this).dialog("close");
				},
				"Cancel": function() {
					$(this).dialog("close");
				}
			}
		});
		
		$("#confirm_delete_dialog").dialog("open");
	});
	
});