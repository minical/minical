$(document).ready(function(){
	$('body').on('click', '.premium_extension', function(){
		$('#premium_extension').modal('show');
	});
});


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



var req;

$(document).on('keyup', '.search_ext', function() {
   		var item = $(this).val();
	    if (req) {
	        req.abort();
	    }
	    setTimeout(function() {
	        req =  $.ajax({
	        type: "POST",
	        url: getBaseURL() + 'extensions/search_extension',
	        data: { item: item },
	        success: function(results) {
	        	$(".extension_view").html(results);
	        }
	    });

	    },100);
	   
});

$(document).on('click', '.uninstall_extension', function(){
	var extension_name = $(this).data('ext_name');
	$.ajax({
            url    : getBaseURL() + 'extensions/uninstall_extension',
            method : 'post',
            dataType: 'json',
            data   : {
                extension_name: extension_name
            },
            success: function (resp) {
            	console.log('resp',resp);
            	var companyNameHtml = '';
            	if ($.isArray(resp.success)){

            		$('.company_names').html('');

            		companyNameHtml = '<table class="table table-hover">';

            		$.each(resp.success, function(i,v){
            			console.log('i',i);
            			console.log('v',v);
            			companyNameHtml += 	'<tr>'+
            									'<th>'+v.name+'</th>'+
            						// 			'<td>'+
            						// 				'<a href="'+getBaseURL()+'menu/select_hotel/'+v.company_id+'" class="btn btn-primary">Show</a>'+
        										// '</td>'+
    										'</tr>';
            		});

            		companyNameHtml += '</table>';
            		$('.company_names').append(companyNameHtml);

            		$('#active_modules_modal').modal('show');
				} else if (resp.success == true){
					var r  = confirm('Are you sure you want to uninstall this extension?');
    
				    if (r == true) {
				       $.ajax({
				            url    : getBaseURL() + 'extensions/uninstall_extension_process',
				            method : 'post',
				            dataType: 'json',
				            data   : {
				                extension_name: extension_name
				            },
				            success: function (resp) {
				            	if (resp.success == true){
				            		location.reload();
				            	}
				            }
				        });
				    } else {
				        event.preventDefault();
				    }
					
				}
            }
    });
});

$(document).on('click', '.install_extension', function(){
	var extension_name = $(this).data('ext_name');
	$.ajax({
            url    : getBaseURL() + 'extensions/install_extension',
            method : 'post',
            dataType: 'json',
            data   : {
                extension_name: extension_name
            },
            success: function (resp) {
            	if (resp.success == true){
					location.reload();
				}
				else {
					//alert(results.message);
				}
            }
    });
});

$(document).ready(function(){

   // alert(is_current_user_superadmin);
   // alert(innGrid.isPartnerOwner);
    if((is_current_user_superadmin!= 1 && innGrid.isPartnerOwner !=1) && 
        (innGrid.companyID !=3417 && innGrid.companyID !=3219 && innGrid.companyID !=3545)){
    $('.checbox-switch').each(function(){
            if ($(this).find('a[name="nestpay_integration"]').length > 0) {
                $(this).find('.extension-box').hide();
            }
        });
    }
});