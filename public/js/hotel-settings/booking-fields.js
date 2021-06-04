innGrid.saveAllBookingFields = function () {
    var updatedBookingFields = {};
    $(".booking-field-tr").each(function()
    {
        var bookingFieldTr = $(this);
        var bookingFieldId = bookingFieldTr.attr('id');
        var bookingFieldName = bookingFieldTr.find('[name="name"]').val();

        updatedBookingFields[bookingFieldId] = {
            id: bookingFieldId,
            name: bookingFieldName,
            show_on_booking_form: (bookingFieldTr.find('[name="show_on_booking_form"]').prop('checked'))?1:0,
            show_on_registration_card: (bookingFieldTr.find('[name="show_on_registration_card"]').prop('checked'))?1:0,
            show_on_in_house_report: (bookingFieldTr.find('[name="show_on_in_house_report"]').prop('checked'))?1:0,
            show_on_invoice: (bookingFieldTr.find('[name="show_on_invoice"]').prop('checked'))?1:0,
            is_required: (bookingFieldTr.find('[name="is_required"]').prop('checked')) ? 1 : 0
        };
    });
    //Populate updates to standard customer field information
    $.post(getBaseURL() + 'settings/reservations/update_booking_field', {
        updated_booking_fields: updatedBookingFields
    }, function (result) {
        //To Do: error checking.
        //This is incomplete because it doesn't check whether the update_rate posts are complete
        //To Do: complete checking with update-rates
        if(result.success)
        {
            alert(l('All Booking fields saved'));
        }
        else
        {
            alert(result.error);
        }
    }, 'json');
}

$(function() {

    $('#add_booking_field').click(function () {

        $.post(getBaseURL() + 'settings/reservations/get_new_booking_field_div', function (div){

            $('#booking-fields').append(div);
        });
    });


    $('#save-all-booking-fields-button').on("click", function () {

        innGrid.BookingFieldSavedCount = 0;
        innGrid.saveAllBookingFields();

    });

    $(document).on('click', '.delete-booking-field', function () {
        var that = this;
        console.log('delete');
        //Set custom buttons for delete dialog
        $("#confirm_delete_dialog")
            .html(l('Are you sure ?'))
            .dialog({
                title:(l('Delete Booking Field')),
                buttons: {
                    "Confirm Delete":function() {
                        $.post(getBaseURL() + 'settings/reservations/delete_booking_field', {
                            id: $(that).parent().parent().attr('id')
                        }, function (results) {
                            if (results.isSuccess == true){
                                $(that).parent().parent().remove();  //delete line of X button
                                //alert(results.message);
                            }
                            else {
                                //alert(results.message);
                            }
                        }, 'json');
                        $(this).dialog("close");
                    },
                    "Cancel": function() {
                        $(this).dialog("close");
                    }
                }
            });

        $("#confirm_delete_dialog").dialog("open");
    });

});