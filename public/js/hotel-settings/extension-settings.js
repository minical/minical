
$(document).on('click', '.extension-status-button', function () {
	var extensionName = $(this).attr('name');
    // var extensionStatus = $(this).data('status');

    if ($(this).prop("checked")) {
      
         var extension_action = extensionStatus = 0;
    } else {
    
    	 var extension_action = extensionStatus = 1;
    }

    var extension_action = extensionStatus == 1 ? "deactivate" : "activate";
	
	//var r = confirm(l('Are you sure you want to '+extension_action+' this extension?'));
	//if (r == true) {
	    $.post(getBaseURL() + 'extensions/change_extension_status', {
		extension_name: extensionName,
        extension_status: extensionStatus
		}, function (results) {
			if (results.success == true){
					location.reload();
				}
				else {
					//alert(results.message);
				}
			}, 'json');
	//}
});

function getval(sel)
{
    var status = sel.value;

    if(status == "all"){
    	location.reload();

    } else {
    	$.post(getBaseURL() + 'extensions/get_filter_extension', {
        extension_status: status
		}, function (results) {
			if (results){
				$(".extension_view").html(results);
			}
		});
    }
}

function getcat(category){
	var cat = category.value

	if(cat == "all"){
    	location.reload();

    } else {

	$.post(getBaseURL() + 'extensions/get_category_extension', {
        extension_category: cat
		}, function (results) {
			if (results){
				$(".extension_view").html(results);
			}
		});
  	}
}


$("body").on("click",".favourite-button", function() {
    
    var extensionName = $(this).attr('name');
    var extensionStatus = $(this).data('value');
	
    $.post(getBaseURL() + 'extensions/change_favourite_status', {
		extension_name: extensionName,
    	extension_status: extensionStatus
	}, function (results) {
		if (results.success == true){
				location.reload();
			}
			else {
				
			}
		}, 'json');

    });


$(document).on('keyup', '.search_ext', function() {
    var item = $(this).val();
   
    $.ajax({
        type: "POST",
        url: getBaseURL() + 'extensions/search_extension',
        data: { item: item },
        success: function(results) {
        	$(".extension_view").html(results);
        }
    });
});