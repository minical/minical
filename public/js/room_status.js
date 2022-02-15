var selected_room_id = 0;

$(function(){
	var baseURL = getBaseURL();

	$(".room-notes-button").on("click", function() {
		// load room notes, then open modal

		selected_room_id = $(this).parents(".room_tr").attr("name");

		$.ajax({
			type: "POST",
			url: getBaseURL() + "room/get_notes_AJAX",
			data: { 
				room_id: selected_room_id
			},
			dataType: "json",
			success: function( data ) {
				$("#room-notes").val(data.notes);
				$('#room_notes_modal').modal('show');
			}
		});
			
	});

	$("#save-room-notes-button").on("click", function() {
		$.ajax({
			type: "POST",
			url: getBaseURL() + "room/update_notes_AJAX",
			data: { 
				room_id: selected_room_id,
				notes: $("#room-notes").val()	
			},
			dataType: "json",
			success: function( data ) {
				$('#room_notes_modal').modal('hide');
				window.location.reload();
			}
		});
	});

	$(document).on("click", ".room-instructions-button", function() {
		selected_room_id = $(this).parents(".room_tr").attr("name");

		$.ajax({
			type: "POST",
			url: getBaseURL() + "room/get_instructions_AJAX",
			data: { 
				room_id: selected_room_id
			},
			dataType: "json",
			success: function( data ) {
				$("#room-instructions").val(data.instructions);
				$('#room_instructions_modal').modal('show');
			}
		});
			
	});

	$(document).on("click", "#save-room-instructions-button", function() {
		$.ajax({
			type: "POST",
			url: getBaseURL() + "room/update_instructions_AJAX",
			data: { 
				room_id: selected_room_id,
				instructions: $("#room-instructions").val()	
			},
			dataType: "json",
			success: function( data ) {
				$('#room_instructions_modal').modal('hide');
				window.location.reload();
			}
		});
	});

	$('.button').button();	
	$("#booking_dialog").dialog({
		autoOpen: false,
		width: 650,
		autoReize: true,
		resizable: false,
	});
	
	$('.room_status').change(function() {
		$.post(baseURL + 'room/update_room_status', {
				room_id: $(this).parent().parent().attr('name').trim(),
				room_status: $(this).parent().parent().find('select option:selected').text().trim()
			}, function(results){
		}, 'json');
	});

	$('body').on('click', '.show_rating', function(){
		var roomRating = $(this).data('room_rating');
		var roomId = $(this).data('room_id');
		var htmlContent = "";
		var onlyRatingsContent = "";
		var noShowComment = false;
		$('.view-rating').html("");
		if(roomRating > 0)
		{
			$.ajax({
					url:baseURL + 'room/get_room_reviews',
					type:"POST",
					data: {room_id:roomId},
					dataType:"json",
					success: function(result){
						$.each(result, function(i,v){
							v.comment = v.comment != null ? v.comment : '';
							if(v.comment == '' && !noShowComment)
							{
								onlyRatingsContent = '<br/><h4>'+l("Ratings only", true)+':</h4>';
								noShowComment = true;
							}
							htmlContent = 	onlyRatingsContent+
												'<div class="box-review">'+
													'<div class="clearfix">'+
														'<div class="customer_detail bold">'+
															v.customer_name+
														'</div>'+
														'<div class="rating">'+parseFloat(v.rating).toFixed(1)+' / 5</div><br/>'+
														'<small class="review-time" style="color: gray;">'+v.created+', Booking: <a href="'+baseURL+'invoice/show_invoice/'+v.booking_id+'" target="_blank">#'+v.booking_id+'</a></small>'+
													'</div>'+
												'<div class="review">'+
													v.comment+
												'</div>'+
											'</div>';
							$('.view-rating').append(htmlContent);
							onlyRatingsContent = "";
						});
					},
			});
			$('#room_rating_modal').modal('show');
		}
		
	});


	$("#set_rooms_clean").on('click', function(e) {	
		if(!confirm(l('Set all rooms as clean?')))
			return;

		$.post(baseURL + 'room/set_rooms_clean', function(results) {	
			window.location.reload();
		});		
		return false; // prevent default click action from happening!
     
	});	


	
	$('#set_rooms_clean').click(function (){
		$("#clean_all_rooms_dialog" ).dialog('open');
	});
	
	// when booking is clicked, open booking edit dialog
	$(".room_info").click(function() {
		var bookingID = $(this).attr('name');
		innGrid.openDialog(baseURL + 'booking/edit_booking/' + bookingID + '/1');
	});
		
});
