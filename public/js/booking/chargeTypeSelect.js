// jQuery Plugin Boilerplate
// A boilerplate for jumpstarting jQuery plugins development
// version 1.1, May 14th, 2011
// by Stefan Gabos

(function($) {

    $.chargeTypeSelect = function(element, options) {

        var defaults = {
            foo: 'bar',
            onFoo: function() {}
        }

        var chargeTypeSelect = this;

        chargeTypeSelect.settings = {
        	checkInDate: undefined,
			checkOutDate: undefined,
			roomTypeID: undefined
        }

        var $element = $(element),
             element = element;

        chargeTypeSelect.init = function() {
            plugin.settings = $.extend({}, defaults, options);
            // code goes here
        }

        chargeTypeSelect.setVariables = function(options) {

        }

        chargeTypeSelect.addChargeWithDDL = function() {
            console.log(this);
            var that = this;

            var select = $("<select/>", {
                            class: 'form-control',
                            id: 'charge-with',
                            style: 'max-width: 300px;'
                        })

            var chargeTypeOptionGroup = $("<optgroup/>", {
                label: l("Charge Types", true)
            });

            //alert('arrive setChargeTypeDDL');
            $.getJSON(getBaseURL() + 'booking/get_charge_types_in_JSON',
                function(data){
                    if (data !== '' && data !== null && data.length > 0) {
                        
                        for (var i in data) {
                            var option = $("<option/>", {
                                                value: data[i].id,
                                                text: data[i].name,
                                            });

                            if (that.booking.charge_type_id == data[i].id && 
                                that.booking.use_rate_plan == 0) 
                            {
                                option.prop("selected", true);
                            }

                            chargeTypeOptionGroup.append(option);
                        }
                    }
                    
                    select.html(chargeTypeOptionGroup);

                    $.post(getBaseURL() + 'booking/get_rate_plans_JSON/', {
                        room_type_id: $("[name='room_type_id").val(),
                        previous_rate_plan_id: that.booking.rate_plan_id
                        },  function(data)

                        {

                            if (data !== '' && data !== null && data.length > 0) {
                                // Set Rate Plan DDL
                                var ratePlanOptionGroup = $("<optgroup/>", {
                                    label: l("Rate Plans", true)
                                });

                                for (var i in data) {
                                    var option = $("<option/>", {
                                                        value: data[i].rate_plan_id,
                                                        text: data[i].rate_plan_name,
                                                        class: 'rate-plan'
                                                    });
                                    if (that.booking.rate_plan_id == data[i].rate_plan_id && 
                                        that.booking.use_rate_plan == 1) 
                                    {
                                        option.prop("selected", true);
                                    }

                                    ratePlanOptionGroup.append(option);
                                }
                            }

                            select.append(ratePlanOptionGroup)
                                .on('change', function() {
                                    that._updateRate(roomTypeDIV);
                                });
                                
                            // If nothing's been selected (This ususally occurs if previously selected rate plan has been deleted)
                            if(select.find(":selected").val() === undefined) {
                                select.append(
                                    $("<option/>", {
                                        selected: 'selected',
                                        text: l("NOT SELECTED", true)
                                    })
                                );
                            }

                            $("#charge-with-div").append(select);
                            that._updateRate(roomTypeDIV);

                        }, 'json'
                    );

                }
            );
        }

        var foo_private_method = function() {
            // code goes here
        }

        chargeTypeSelect.init();

    }

    $.fn.chargeTypeSelect = function(options) {

        return this.each(function() {
            if (undefined == $(this).data('chargeTypeSelect')) {
                var plugin = new $.chargeTypeSelect(this, options);
                $(this).data('chargeTypeSelect', plugin);
            }
        });

    }

})(jQuery);