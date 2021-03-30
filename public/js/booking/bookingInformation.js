
var urlPath = window.location.pathname.split("/");
console.log(urlPath['1'], urlPath['2']);

if(urlPath['1'] == 'booking' && urlPath['2'] == 'show_booking_information')
{

    $("body").on('click','.modify_booking_btn' , function() {

        var booking_id = $('input[name="booking_id"]').val();
        var status = $(this).data('status');

        var checkInDate = $('#chk_in_dt').val();
        var checkOutDate = $('#chk_out_dt').val();
        var checkInTime = $('#chk_in_tm').val();
        var checkOutTime = $('#chk_out_tm').val();
        var startTime = moment().startOf('day');
        var endTime = moment().endOf('day');

        $('input[name=check_in_date]').val(checkInDate);
        $('input[name=check_out_date]').val(checkOutDate);
       
        $("input[name=check_in_date]").datepicker({
            dateFormat: ($('#companyDateFormat').val()).toLowerCase(),
            beforeShow: customRange
        });

        $("input[name=check_out_date]").datepicker({
            dateFormat: ($('#companyDateFormat').val()).toLowerCase(),
            beforeShow: customRange
        });

        if($('.enable_hourly_booking').val() == 1) {

            var timeOptions = [];
            var time = startTime;

            while (time <= endTime) {
                timeOptions.push('<option value="' + time.format('hh:mm A') + '" >' + time.format('hh:mm A') + '</option>');
                time = time.clone().add(30, 'm');
            }

            $(".check-in-time-wrapper").append(timeOptions);
            $(".check-out-time-wrapper").append(timeOptions);

            $(".check-in-time-wrapper").val(checkInTime);
            $(".check-out-time-wrapper").val(checkOutTime);
        } else {
            $(".check-in-time-wrapper").hide();
            $(".check-out-time-wrapper").hide();

            $(".check-in-date-wrapper").css('width','100%');
            $(".check-out-date-wrapper").css('width','100%');
        }

        $('#modify_booking_modal').modal('show');

    });
    
    $("body").on('click','.save_booking' , function() {
        var $btn = $(this);
        $btn.prop('disabled', true);
        $('.error-message').text('');

        var booking_id = $('input[name="booking_id"]').val();
        var room_id = $('input[name="room_id"]').val();
        
        var checkInDate = $('input[name="check_in_date"]').val();
        var checkInTIme = $('.check-in-time-wrapper').val();

        var checkOutDate = $('input[name="check_out_date"]').val();
        var checkOutTIme = $('.check-out-time-wrapper').val();

        var dateFormat = $('.date_format').val();

        checkInDate = innGrid._getBaseFormattedDate(checkInDate);//dateFormat == 'DD-MM-YY' ? moment(checkInDate, 'DD-MM-YYYY').format('YYYY-MM-DD') : checkInDate;
        checkOutDate = innGrid._getBaseFormattedDate(checkOutDate);

        var inDt = moment(moment(checkInDate).format('YYYY-MM-DD')+' '+moment(checkInTIme, 'hh:mm A').format('HH:mm:ss'));
        var outDt = moment(moment(checkOutDate).format('YYYY-MM-DD')+' '+moment(checkOutTIme, 'hh:mm A').format('HH:mm:ss'));
        var diff = outDt.diff(inDt, 'minutes');

        if(Math.sign(diff) == -1) {
            alert(l('Check-out date must be greater than or equal to Check-in date', true));
            $btn.prop('disabled', false);
            return false;
        }

        $.ajax({
            type: "POST",
            url: getBaseURL() + 'booking/check_overbooking_AJAX',
            dataType: 'json',
            data: {
                booking_id: booking_id,
                room_id: room_id,
                check_in_date: checkInDate,
                check_out_date: checkOutDate
            },
            success: function (response) {
                if(response.success)
                {
                    $('.error-message').text(l("Couldn't find any availability for the given date range", true));
                    $btn.removeAttr('disabled');
                }
                else
                {
                    var oldCheckInDate = $('#chk_in_dt').val();
                    var oldCheckOutDate = $('#chk_out_dt').val();

                    oldCheckInDate = dateFormat == 'DD-MM-YY' ? moment(oldCheckInDate, 'DD-MM-YYYY').format('YYYY-MM-DD') : oldCheckInDate;
                    oldCheckOutDate = dateFormat == 'DD-MM-YY' ? moment(oldCheckOutDate, 'DD-MM-YYYY').format('YYYY-MM-DD') : oldCheckOutDate;

                    var oldCheckInTime = $('#chk_in_tm').val();
                    var oldCheckOutTime = $('#chk_out_tm').val();

                    $.ajax({
                        type: "POST",
                        url: getBaseURL() + 'booking/guest_update_booking',
                        dataType: 'json',
                        data: {
                            booking_id: booking_id,
                            room_id: room_id,
                            check_in_date: checkInDate,
                            check_out_date: checkOutDate,
                            check_in_time: checkInTIme,
                            check_out_time: checkOutTIme,
                            old_check_in_date: oldCheckInDate,
                            old_check_out_date: oldCheckOutDate,
                            old_check_in_time: oldCheckInTime,
                            old_check_out_time: oldCheckOutTime
                        },
                        success: function (response) {
                            if(response.success)
                            {
                                location.reload();
                            }
                        }
                    });
                }
            }
        });
    
    });

    $("body").on('click','.cancel_booking' , function() {
        var answer = confirm(l("Are you sure you want to delete this booking?", true));
        if(answer == true)
        {
            var booking_id = $('input[name="booking_id"]').val();

            $.ajax({
                type: "POST",
                url: getBaseURL() + 'booking/guest_update_booking',
                dataType: 'json',
                data: {
                    booking_id: booking_id,
                    status: 'cancel'
                },
                success: function (response) {
                    if(response.success)
                    {
                        alert(response.message);
                        location.reload();
                    }
                }
            });
        }
    
    });

    function customRange(input) {
        var dateMin = null;
        var dateMax = null;

        if (input.name == "check_in_date") {
            if ($("[name='check_out_date']").val() != '') {
                dateMax = $("[name='check_out_date']").val();
            }
        } else if (input.name == "check_out_date") {
            if ($("[name='check_in_date']").val() != '') {
                dateMin = $("[name='check_in_date']").val();
            }
        }

        return {
            minDate: dateMin,
            maxDate: dateMax
        };
    }
}
