$(function(){
	var baseURL = getBaseURL();

	$('.button').button();

	$('.room_status').change(function() {
		$.post(baseURL + 'room/update_room_status', {
				room_id: $(this).parent().parent().attr('name').trim(),
				room_status: $(this).parent().parent().find('select option:selected').text().trim()
			}, function(results){
		}, 'json');
	});

	$('.room_score').change(function() {
		var room_score = $(this).parent().parent().find('.room_score').val().trim();
		if(room_score >= 0 && room_score <=10)
		{
			$.post(baseURL + 'room/update_room_score', {
					room_id: $(this).parent().parent().attr('name').trim(),
					room_score: room_score
				}, function(results){
					if(results.success)
					{
						//alert('Room Score saved');
					}
			}, 'json');
		}
		else
		{
			alert(l('please enter score b/w 0 to 10'));
			$(this).parent().parent().find('.room_score').val(0);
		}
	});

	setTimeout(function(){
		$('.table-rating tbody tr').each(function(i,v){
			var width = $(this).find('.star-rating').val()*16;
			if(width)
			{
				$(this).find('.stars').find('.rateit-selected').css('width',width+'px');
			}
		});
	},2000);
	
	$("#clean_all_rooms_dialog").dialog({
		autoOpen: false,
		title: l('Confirmation'),					
		resizable: false,				
		modal: false,
		buttons: [
		{
			text: "Ok",
			click: function() {
				$.post(baseURL + 'room/set_rooms_clean', function(results) {			
					alert(l('All rooms set clean'));
					window.location = baseURL + "room/";
				});		
				$(this).dialog("close"); 
			}
		},
		{
			text: l("Cancel"),
			click: function() { $(this).dialog("close"); }
		} ]
	});		
	
	$('#set_rooms_clean').click(function (){
		$("#clean_all_rooms_dialog" ).dialog('open');
	});
	
	// when 'view notes' is clicked, open a dialog for viewing notes
	$(".room_notes").click(function() {
		var roomID = $(this).attr('name');
		
		var url = baseURL + 'room/view_room_note_form/' + roomID + '/1';
		// open the booking dialog once the content is loaded
		$('#room_notes').html($('<iframe />', {
			'id': 'room-note-dialog-iframe',
			'scrolling': 'no',
			'frameborder': 0,
			'width': 460,
			'height': 330,
			"src": url
		}));
	});
	
	
});