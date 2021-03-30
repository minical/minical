// ical claendar js
$(function(){
   
    $('#dateStart, #dateEnd').on('click', function(){
       $(this).datepicker({
           dateFormat:"yy-mm-dd"
       });
    });
   
   
    $('select[name="send-status"]').on('change', function()
    {
        if($(this).val() == 1)
        {
            $(this).parents('tr').find('.show-url-td input').removeClass('hidden');
        }
        else
        {
            $(this).parents('tr').find('.show-url-td input').addClass('hidden'); 
        }
    });
    
    $('.save_ical_mapping_button').on('click', function(){
        var allRows = getAllMappingRoomRows();
        $(this).prop('disabled', true);
        $.ajax({
            type   : "POST",
            url    : getBaseURL() + "settings/integrations/save_ical_mapping_AJAX",
            data   : {mapping_ical_data: allRows},
            dataType: "json",
            success: function (response) 
            {
                if(response ==  '')
                {
                    alert(l('Data is successfully saved'));
                    window.location.reload();
                }
            }
        });
        
    });
    
});

function getAllMappingRoomRows()
{
    var icalData = [];
    $('.minical-rooms-tr').each(function(){
        var sendStatus = $(this).find('select[name="send-status"]').val();
        if(sendStatus == 1 || $(this).hasClass('rooms-mapped-tr'))
        { //get those rows value that send status is 1 and that are already mapped with ical
            var importUrl = $(this).find("input[name='import-url']").val();
            icalData.push({
                minical_room_id: $(this).attr('data-room-id'),
                send_status: sendStatus,
                import_url: importUrl
            });  
        }
       
    });
    return icalData;
}
