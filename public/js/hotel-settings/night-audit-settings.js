innGrid.openNightAuditPrompt = function() {
	var baseURL = getBaseURL();
	
	// get night audit's resulting date from server. (If current selling date is 2012-10-15, the resulting date will be 2012-10-15 unless the night auditing multiple days
	$.post(baseURL + 'menu/get_night_audit_resulting_date', function(str){
		$("#night-audit-resulting-date").text(str);
		
	}, 'json');		
	$("#dialogNightAudit").dialog("open");
	$("#submitNightAuditButton").button({disabled: false });
}

innGrid.runNightAudit = function() {
	var baseURL = getBaseURL();
	$("#submitNightAuditButton").button({disabled: true });
	$("#dialogNightAudit").dialog("close");
	$("#dialogProcessingRequest").dialog("open");
	$.get(baseURL + "menu/run_night_audit", {},
		function(theXML) {
			alert(theXML);
			window.location.href = baseURL + "booking";
		}
	);
	

}


$(function() {

	$(".nightAuditButton").button();
	

	$("input[name='selling_date']").datepicker({
		  dateFormat: 'yy-mm-dd',
	});

	$("input[name='selling_date']").on("change", function() {
		alert(l("Property shouldn't run Night Audit Automatically while manipulating the selling date. Disabling Auto-Night Audit below."));
		$("input[name='night_audit_auto_run_is_enabled']").attr("checked", false);
	});

	$("#dialogNightAudit, #dialogUndoNightAudit, #dialogProcessingRequest").dialog({
			autoOpen: false,
			resizable: false,
			modal: true,
			movable: true,
			width: 450,
			height: 300
	});
	
	$('#run-night-audit-button').click(function(e) {
		e.preventDefault(); 
		innGrid.openNightAuditPrompt();
	});

	$("#cancelNightAuditButton").click(function() {
		$("#dialogNightAudit").dialog("close");
	});
	
	$("#submitNightAuditButton").click(function() {
		innGrid.runNightAudit();
	});



});