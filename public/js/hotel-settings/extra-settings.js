innGrid.saveAllExtraFields = function () { console.log('here');
    var updatedExtraFields = {};
    $(".extra-field-tr").each(function()
    {
        var extraFieldTr = $(this);
        var extraFieldId = extraFieldTr.attr('id');
        var extraFieldName = extraFieldTr.find('[name="name"]').val();
        // var extraType = extraFieldTr.find('[name="extra-type"]').val();
        var extraChargeTypeID = extraFieldTr.find('[name="extra-charge-type-id"]').val();
        var chargingScheme = extraFieldTr.find('[name="charging-scheme"]').val();
        var defaultRate = extraFieldTr.find('[name="default-rate"]').val();

        var extraType = 'item';
        if (chargingScheme == 'once_a_day_inclusive_end_date') {
            extraType = 'item';
            chargingScheme = 'once_a_day';
        }
        if (chargingScheme == 'once_a_day_exclusive_end_date') {
            extraType = 'rental';
            chargingScheme = 'once_a_day';
        }

        updatedExtraFields[extraFieldId] = {
            extra_id: extraFieldId,
            extra_name: extraFieldName,
            extra_type: extraType,
            extra_charge_type_id: extraChargeTypeID,
            charging_scheme: chargingScheme,
            default_rate: defaultRate,
            show_on_pos: (extraFieldTr.find('[name="show_on_pos"]').prop('checked')) ? 1 : 0
        };
    });

    var extrasData = updatedExtraFields;
    var data = {};
    var count = 0;
    var total = true;
    for(var key in extrasData) {
            
        if (count == 100) {
            // send ajax request with data

            $.post(getBaseURL() + 'settings/rates/update_extras', {
                updated_extras: data
            }, function (result) {
                if(result.success)
                {
                    alert(l('All Products saved'));
                }
                else
                {
                    alert(result.error);
                }
            }, 'json');
            
            console.log(data);
            
            data = {}; // reset data
            count = 0; // reset counter
            total = false;
        }
        
        data[key] = extrasData[key];
        count ++;
        
    }
    if (count > 0) {
        // send ajax request with data
        $.post(getBaseURL() + 'settings/rates/update_extras', {
            updated_extras: data
        }, function (result) {
            if(result.success)
            {
                if(total)
                    alert(l('All Products saved'));
            }
            else
            {
                alert(result.error);
            }
        }, 'json');
        
        console.log(data);
        
        data = {}; // reset data
        count = 0; // reset counter
    }
    
    //Populate updates to standard customer field information
    
}

$(function() {

    $('#add_extra').click(function () {

        $.post(getBaseURL() + 'settings/rates/create_extra', function (div){

            $('#extras-fields').append(div);
        });
    });


    $('#save-all-extras-button').on("click", function () {

        innGrid.ExtraFieldSavedCount = 0;
        innGrid.saveAllExtraFields();

    });

    $(document).on('click', '.delete-extra-button', function () {
        var extraID = $(this).parent().parent().attr('id')
        var that = this;
        //Set custom buttons for delete dialog
        var r = confirm(l('Are you sure you want to delete this product?'));
        if (r == true) {
            $.post(getBaseURL() + 'settings/rates/delete_extra_AJAX', {
            extra_id: extraID
            }, function (results) {
                if (results.isSuccess == true){
                        $(that).parent().parent().remove();  //delete line of X button
                        //alert(results.message);
                    }
                    else {
                        //alert(results.message);
                    }
                }, 'json');
        }
    });

});
    