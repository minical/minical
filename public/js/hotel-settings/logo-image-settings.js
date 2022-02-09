
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
			// location.reload(); // temporary fix to refresh cache of the recently uploaded image
		}
	});

}

innGrid.insertImage = function(imageGroupID, imageSrc) {
    var img = jQuery('<img/>', {
	    src: imageSrc ? imageSrc : $(".croppedImg").prop("src"),
	    class: 'thumbnail col-md-3 add-image',
	    // 'data-toggle': "modal",
	    // 'data-target': "#image_edit_modal"
	});
    if ($('.logo-image-thumbnails').length) {
		$('.logo-image-thumbnails').append(img);
	} else if($('#'+imageGroupID).find('img.thumbnail.add-image').length > 0) {
        img.insertAfter('#'+imageGroupID+' img.thumbnail.add-image:last');
    } else {
        img.appendTo('#'+imageGroupID);
    }
}

var imageDimensions = null;
var imgGroupID = null;

$(function (){

	$(document).on('click', '.add-image', function() {
		$('#image_edit_modal').modal('show');

		// A modal frame that will be used to crop & resize the uploaded image
		// This is a bit of a hack job. 
		var thumbnail = $(this); // thumbnail can also be the "Add Image" Button
		var imageGroupID = thumbnail.closest('.image-group').prop('id');

		imgGroupID = imageGroupID;

		$("#cropper_file_input, #cropper_canvas, #uploadbutton, #savebutton").remove(); // init/reset

		$.ajax({
			type: "POST",
			url: getBaseURL() + "image/get_dimensions_AJAX/",
			data: { 
				image_group_id: thumbnail.closest('.image-group').prop('id')
			},
			dataType: "json",
			success: function( dimensions ) {

				imageDimensions = dimensions;

				jQuery('<input/>', {
				    id: "cropper_file_input",
				    class: 'cropper_file_input',
					type: "file",
					style: 'display: none;'
				}).appendTo('.image-modal-footer');

				// create Upload image button
				$("#uploadbutton").remove(); // init/reset
				
				jQuery('<div/>', {
				    id: 'uploadbutton',
				    class: 'btn btn-primary',
				    type: 'button',
				}).prependTo('.image-modal-footer');

				// User is editing existing image
				if (thumbnail.prop('src') !== undefined)
				{
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
						var r = confirm(l("Are you use you want to delete this Image?"));
						if (r == true) {
						    innGrid.deleteImage(thumbnail);
						    $('#image_edit_modal').modal('hide'); // close bootstrap modal
						}
					});

					// Delete the old image once the new one is added
					// croppicOptions.onAfterImgCrop = function(data) {
					// 	innGrid.insertImage(imageGroupID);
					// 	innGrid.deleteImage(thumbnail);
					// 	$('#image_edit_modal').modal('toggle'); // close bootstrap modal
					//
					// };

					$('#uploadbutton').text('Change Image');

				} else // User clicked Add Image button
				{

					$('#image_edit_modal_body').html("Click on [Upload Image] button below to upload image!<br/>The image's dimensions has to be at least <strong>"+dimensions.width+"x"+dimensions.height+"</strong> and <strong>smaller than 1 MB in size</strong>");
					$('#uploadbutton').text('Upload Image');


					// croppicOptions.onAfterImgCrop = function(data) {
					// 	// croppic hack to fetch cropped image's filename
					// 	innGrid.insertImage(imageGroupID);
					// 	$('#image_edit_modal').modal('toggle'); // close bootstrap modal
					// 	//location.reload(); // temporary fix to refresh cache of the recently uploaded image
					// };
				}

			}
		});
	});

	$(document).on('change', '#cropper_file_input', function () {
		var file = document.getElementById("cropper_file_input").files[0];  // get a reference to the selected file

		var reader = new FileReader(); // create a file reader
		// set an onload function to show the image in cropper once it has been loaded
		reader.onload = function(event) {
			var data = event.target.result; // the "data url" of the image

			if (data) {

				$('#image_edit_modal_body').html ('');

				var ratio = parseFloat(imageDimensions.height) / parseFloat(imageDimensions.width);

				jQuery('<canvas/>', {
					id: "cropper_canvas",
					class: 'cropper_canvas'
				}).appendTo('#image_edit_modal_body');


				cropper.start(document.getElementById("cropper_canvas"), ratio);

				cropper.showImage(data); // hand this to cropper, it will be displayed

				setTimeout(function () {
					cropper.startCropping();
				}, 500)

				$("#uploadbutton").remove(); // init/reset
				$("#savebutton").remove(); // init/reset

				jQuery('<div/>', {
					id: 'savebutton',
					class: 'btn btn-primary',
					type: 'button',
					text: l('Crop and Save')
				}).prependTo('.image-modal-footer');
			}
		};

		reader.readAsDataURL(file); // this loads the file as a data url calling the function above once done
	});

	$(document).on('click', '#uploadbutton', function () {
		$('#cropper_file_input').trigger('click');
	});

	$(document).on('click', '#savebutton', function () {
		var ImageURL = cropper.getCroppedImageSrc();
		$('#image_edit_modal_body').html('Processing...');

		var block = ImageURL.split(";");
		// Get the content type of the image
		var contentType = block[0].split(":")[1];// In this case "image/gif"
		// get the real base64 content of the file
		var realData = block[1].split(",")[1];// In this case "R0lGODlhPQBEAPeoAJosM...."

		// Convert it to a blob to upload
		var blob = b64toBlob(realData, contentType);

		// Create a FormData and append the file with "image" as parameter name
		var formDataToUpload = new FormData();
		formDataToUpload.append("file", blob);

		var imageGroupID = $('.image-group').prop('id');
		imgGroupID = imageGroupID;

		$.ajax({
			url: getBaseURL() + "image/upload_to_s3/"+imgGroupID,
			data: formDataToUpload,// Add as Data the Previously create formData
			type:"POST",
			contentType:false,
			processData:false,
			cache:false,
			dataType:"json",
			success: function( data ) {

				if (data.status == "success") {
					innGrid.insertImage(imgGroupID, data.url);
				} else {
					alert(l('some error occurred!'));
				}

				$('#image_edit_modal').modal('hide'); // close bootstrap modal
				// location.reload(); // temporary fix to modal close issue
			}
		});
	});

});

/**
 * Convert a base64 string in a Blob according to the data and contentType.
 *
 * @param b64Data {String} Pure base64 string without contentType
 * @param contentType {String} the content type of the file i.e (image/jpeg - image/png - text/plain)
 * @param sliceSize {Int} SliceSize to process the byteCharacters
 * @see http://stackoverflow.com/questions/16245767/creating-a-blob-from-a-base64-string-in-javascript
 * @return Blob
 */
function b64toBlob(b64Data, contentType, sliceSize) {
	contentType = contentType || '';
	sliceSize = sliceSize || 512;

	var byteCharacters = atob(b64Data);
	var byteArrays = [];

	for (var offset = 0; offset < byteCharacters.length; offset += sliceSize) {
		var slice = byteCharacters.slice(offset, offset + sliceSize);

		var byteNumbers = new Array(slice.length);
		for (var i = 0; i < slice.length; i++) {
			byteNumbers[i] = slice.charCodeAt(i);
		}

		var byteArray = new Uint8Array(byteNumbers);

		byteArrays.push(byteArray);
	}

	var blob = new Blob(byteArrays, {type: contentType});
	return blob;
}