$(document).ready(function(){

    

    $('body').on('click','.settings-segment',function(){
     
        var name = $('input[name="name"]').val();
        var description = $('input[name="description"]').val();
        
        var segment_id = $('input[name="segment_id"]').val();
        
        if(name == ''){
            alert(l('Please enter Name', true));
        }
          else {
            $.ajax({
                type    : "POST",
                dataType: 'json',
                url     : getBaseURL() + 'settings/marketing_segments/save_segment',
                data: {
					name : name,
					description : description,
					segment_id : segment_id,
				},
                success: function( data ) {
                    if(data.success){
						alert(l('Marketing Segment save successfully!'));
                        window.location.href = getBaseURL() + 'settings/marketing_segments'
                    } else {
                        alert(data.msg);
                    }
                }
            });
        }
    });

   
    
});

function EnableDisable (segment_id, dstatus){
	let text = "Are you sure you want to "+dstatus;
   if (confirm(text) == true) {
    $.ajax({
                type    : "POST",
                dataType: 'json',
                url     : getBaseURL() + 'settings/marketing_segments/status_segment',
                data: {
					is_disable : dstatus,
					segment_id : segment_id,
				},
                success: function( data ) {
                    if(data.success){
						alert(l('This Segment '+dstatus+' successfully!'));
                        window.location.href = getBaseURL() + 'settings/marketing_segments'
                    } else {
                        alert(data.msg);
                    }
                }
            });
  } else {
    text = "You canceled!";
  }
}

