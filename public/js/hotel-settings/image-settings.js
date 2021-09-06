
innGrid.deleteImage = function(thumbnail) {
	
	// loading animation
	$("#image_edit_modal_body").append('<div class="loader bubblingG"><span id="bubblingG_1"></span><span id="bubblingG_2"></span><span id="bubblingG_3"></span></div>');
	
	$.ajax({
		type: "POST",
		url: getBaseURL() + "image/delete_file_AJAX/",
		data: { 
			src: thumbnail.prop('src'),
			image_group_id: thumbnail.closest('.image-group').prop('id')
		},
		dataType: "json",
		success: function( data ) {
			thumbnail.remove();
			//location.reload(); // temporary fix to refresh cache of the recently uploaded image
		}
	});

}

innGrid.insertImage = function(imageGroupID) {
    var img = jQuery('<img/>', {
	    src: $(".croppedImg").prop("src"),
	    class: 'thumbnail col-md-3 add-image',
	    'data-toggle': "modal",
	    'data-target': "#image_edit_modal"
	});
    if($('#'+imageGroupID).find('img.thumbnail.add-image').length > 0) {
        img.insertAfter('#'+imageGroupID+' img.thumbnail.add-image:last');
    } else {
        img.appendTo('#'+imageGroupID);
    }
}

$(function (){

	$(document).on('click', '.add-image', function() {

		$('#image_edit_modal').modal('hide');

		// A modal frame that will be used to crop & resize the uploaded image
		// This is a bit of a hack job. 
		var thumbnail = $(this); // thumbnail can also be the "Add Image" Button
		var imageGroupID = thumbnail.closest('.image-group').prop('id');
		console.log(imageGroupID);
		$("#croppic_div").remove(); // init/reset

		$.ajax({
			type: "POST",
			url: getBaseURL() + "image/get_dimensions_AJAX/",
			data: { 
				image_group_id: thumbnail.closest('.image-group').prop('id')
			},
			dataType: "json",
			success: function( data ) {

				//console.log(data);
				jQuery('<div/>', {
				    id: "croppic_div",
				    class: 'center-block croppic_div',
				    width: data.width,
				    height: data.height,
				    position: 'relative'
				}).appendTo('.image-modal-footer');

				var croppicOptions = {
					imageGroupID: imageGroupID,
					uploadUrl: getBaseURL() + 'image/save_to_file',
					cropUrl: getBaseURL() + 'image/crop_to_file',
					customUploadButtonId:'uploadbutton',
					imgEyecandy: false,
					modal:true,
					onBeforeImgUpload: function()
					{
						// Show loader animation
						$("#image_edit_modal_body").append('<div class="loader bubblingG"><span id="bubblingG_1"></span><span id="bubblingG_2"></span><span id="bubblingG_3"></span></div>');
					},
					onAfterImgUpload: 	function()
					{
						// remove loader animation
						$(".bubblingG").remove(); // init/reset
					},
					onBeforeImgCrop: function()
					{
						// Show loader animation
						$(".cropImgWrapper").append('<div class="loader bubblingG"><span id="bubblingG_1"></span><span id="bubblingG_2"></span><span id="bubblingG_3"></span></div>');
					},
					onError: function(errormsg){ 
                        alert(l('Some error occured. Please try again.'));
                        $(".bubblingG").remove(); // init/reset
						console.log('onError:'+errormsg);
					}
				};


				// create Upload image button
				$("#uploadbutton").remove(); // init/reset
				
				jQuery('<div/>', {
				    id: 'uploadbutton',
				    class: 'btn btn-primary',
				    type: 'button'
				}).prependTo('.image-modal-footer');


				// User is editing existing image
				if (thumbnail.prop('src') !== undefined)
				{
					$('#edit-rate-plan').modal('hide');
					$('#room_type_model').modal('hide');
					// $('#addnew_room_type_model').modal('hide');
					
					$('#image_edit_modal_body').html('<img src="'+thumbnail.prop('src')+'" class="img-responsive">');	

					// Add delete button
					$("#delete_button").remove(); // init/reset
					jQuery('<div/>', {
					    id: 'delete_button',
					    class: 'btn btn-danger',
					    type: 'button',
					    text: l('Delete Image')
					}).prependTo('.image-modal-footer');
					
					$("#delete_button").on("click", function () {
						var r = confirm("Are you use you want to delete this Image?");
						if (r == true) {
						    innGrid.deleteImage(thumbnail);
							$('#image_edit_modal').modal('hide'); // close bootstrap modal
							$('#image_edit_modal').on('hidden.bs.modal', function () {
	                            $('body').addClass('modal-open');
	                        });
							$('#edit-rate-plan').modal('show');
							$('#room_type_model').modal('show');
							// $('#addnew_room_type_model').modal('show');
						}
					});

					// Delete the old image once the new one is added
					croppicOptions.onAfterImgCrop = function(data) {
						innGrid.insertImage(imageGroupID);
						innGrid.deleteImage(thumbnail);
						$('#image_edit_modal').modal('hide'); // close bootstrap modal
						
						$('#image_edit_modal').on('hidden.bs.modal', function () {
                            $('body').addClass('modal-open');
                        });
						$('#edit-rate-plan').modal('show');
						$('#room_type_model').modal('show');
						// $('#addnew_room_type_model').modal('show');
						
					};

					$('#uploadbutton').text('Change Image');
					$('#edit-rate-plan').on('hidden.bs.modal', function () {
	                            $('body').addClass('modal-open');
							});
					$('#room_type_model').on('hidden.bs.modal', function () {
	                            $('body').addClass('modal-open');
							});
							// $('#addnew_room_type_model').on('hidden.bs.modal', function () {
	                        //     $('body').addClass('modal-open');
	                        // });
				} else // User clicked Add Image button
				{
					$('#edit-rate-plan').modal('hide');
					$('#room_type_model').modal('hide');
					// $('#addnew_room_type_model').modal('hide');
					$('#image_edit_modal_body').html("Click on [Upload Image] button below to upload image!<br/>The image's dimensions has to be at least <strong>"+data.width+"x"+data.height+"</strong> and <strong>smaller than 1 MB in size</strong>");
					$('#uploadbutton').text('Upload Image');
					croppicOptions.onAfterImgCrop = function(data) {
						// croppic hack to fetch cropped image's filename
						innGrid.insertImage(imageGroupID);

						$('#image_edit_modal').modal('hide'); // close bootstrap modal
						$('#image_edit_modal').on('hidden.bs.modal', function () {
                            $('body').addClass('modal-open');
                        });
						$('#edit-rate-plan').modal('show');
						$('#room_type_model').modal('show');
						// $('#addnew_room_type_model').modal('show');

						
						//location.reload(); // temporary fix to refresh cache of the recently uploaded image
					};
				}


				var temp = new Croppic("croppic_div", croppicOptions);

				$('.modal-backdrop').remove();
				$('#image_edit_modal').modal('show');
			}
		});
	});	

});