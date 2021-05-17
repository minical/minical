/*  Plugin for Booking Modals
 *   It takes the element's id attr, and use it as bookingID
 */
(function ($) {
    "use strict";

    var defaults = {};

    // dynamically load required css
    var csses = [
        'css/bootstrap-colorselector.css',
        'css/bootstrap-tokenfield.min.css'
    ];

    csses.forEach(function (css) {
        if (document.createStyleSheet) {
            document.createStyleSheet(getBaseURL() + css);
        }
        else {
            $('<link rel="stylesheet" type="text/css" href="' + getBaseURL() + css + '" />').appendTo('head');
        }
    });

    // dynamically load required js
    var scripts = [
        'js/booking/jquery.ui.autocomplete.scroll.min.js',
        'js/customer/customerModal.js',
        'js/bootstrap-tokenfield.js',
        'js/bootstrap-colorselector.js',
        'js/jquery.payment.js'

                /*
                 ,'js/booking/chargeTypeSelect.js',
                 'js/booking/roomTypeSelect.js'
                 */

    ];

    scripts.forEach(function (script) {
        $.getScript(getBaseURL() + script, function () {
            //console.log(script+" successfully loaded!");
        });

    });

    // initialize booking modal
    $("body").append(
            $("<div/>", {
                class: "modal fade",
                id: "booking-modal",
                "tabindex": "-1",
                "role": "dialog",
                "aria-hidden": true
            }).append(
            $("<div/>", {
                class: "alert-booking-created h1 text-center alert-success"
            }).hide()
            ).append(
            $("<div/>", {
                class: "modal-dialog modal-lg"
            }).append(
            $("<div/>", {
                class: "modal-content"
            }).html("")
            )
            )
            ).append(
            $("<div/>", {
                class: "modal fade",
                id: "inner-modal",
                "tabindex": "-1",
                "role": "dialog",
                "aria-hidden": true
            }).append(
            $("<div/>", {
                class: "modal-dialog"
            }).append(
            $("<div/>", {
                class: "modal-content"
            }).html("")
            )
            )
            ).append(
            $("<div/>", {
                class: "modal fade",
                id: "edit-rate-modal",
                "tabindex": "-1",
                "role": "dialog",
                "aria-hidden": true
            }).append(
            $("<div/>", {
                class: "modal-dialog",
                style: "width: 700px;"
            }).append(
            $("<div/>", {
                class: "modal-content"
            })
            )
            )
            ).append(
            $("<div/>", {
                class: "modal fade",
                id: "group-search-model",
                "tabindex": "-1",
                "role": "dialog",
                "aria-hidden": true
            }).append(
            $("<div/>", {
                class: "modal-dialog",
                style: "width: 700px;"
            }).append(
            $("<div/>", {
                class: "modal-content"
            }).html("")
            )
            )
            );

    $("#inner-modal").modal({show: false, backdrop: 'static', keyboard: false});
    $("#booking-modal").modal({show: false, backdrop: 'static', keyboard: false});
    $("#group-search-model").modal({show: false, backdrop: 'static', keyboard: false});
    $("#edit-rate-modal").modal({show: false, backdrop: 'static', keyboard: false});

    var BookingModal = function (options) {

        var that = this;

        this.deferredChargeWithDDL = $.Deferred();
        this.deferredRoomDDL = $.Deferred();
        this.deferredExistingCustomerConfirmation = $.Deferred();
        this.deferredBookingSource = $.Deferred();
        this.isCreatingCustomer = false;
        this.companyBookingSources = null;
        this.options = $.extend({}, defaults, options);
        this._init();

        this.selectedGroupType = 'single';

        // new booking
        this.booking = {
            state: undefined,
            color: undefined,
            check_in_date: this.options.checkInDate,
            check_out_date: this.options.checkOutDate,
            current_room_id: this.options.roomID,
            current_room_type_id: this.options.roomTypeID,
            adult_count: 1,
            children_count: 0,
            balance: 0,
            pay_period: 0,
            //staying_customers: this.options.stayingCustomers,

        };
        this.groupInfo = null;
        this.saveAllGroupDate = null;
      
        this.disableRoomBlock = '';
        this.pointerNone = '';
        
        this.rateWithTax = null;

        if (this.options.isAddGroupBooking) {
            this.groupInfo = this.options.isAddGroupBooking;
        }


        if (!this.options.id) // new booking
        {
            this._populateNewBookingModal();
        }
        else //edit existing booking!
        {
            this._populateEditBookingModal();
        }


    };

    BookingModal.prototype = {
        _init: function () {

            var that = this;

            $('#booking-modal').modal('show');
            
            // remove reservation tooltip if still showing
            $('.tooltip-reservation').remove();

            this.closeModal = $.Deferred();
            $.when(this.closeModal.promise()).done(function (script) {
                $("#booking-modal").modal('hide');
            });

            this.adultsCount = [
                {id: 1, name: '1 adult'},
                {id: 2, name: '2 adults'},
                {id: 3, name: '3 adults'},
                {id: 4, name: '4 adults'},
                {id: 5, name: '5 adults'},
                {id: 6, name: '6 adults'},
                {id: 7, name: '7 adults'},
                {id: 8, name: '8 adults'},
                {id: 9, name: '9 adults'},
                {id: 10, name: '10 adults'},
                {id: 11, name: '11 adults'},
                {id: 12, name: '12 adults'},
                {id: 13, name: '13 adults'},
                {id: 14, name: '14 adults'}
            ];

            this.childrenCount = [
                {id: 0, name: 'no children'},
                {id: 1, name: '1 child'},
                {id: 2, name: '2 children'},
                {id: 3, name: '3 children'},
                {id: 4, name: '4 children'},
                {id: 5, name: '5 children'},
                {id: 6, name: '6 children'},
                {id: 7, name: '7 children'},
                {id: 8, name: '8 children'},
                {id: 9, name: '9 children'},
                {id: 10, name: '10 children'},
                {id: 11, name: '11 children'},
                {id: 12, name: '12 children'},
                {id: 13, name: '13 children'},
                {id: 14, name: '14 children'}
            ];
            
            this.sources = [];

            this.customColors = [
                "98D6D6",
                "93AEFF",
                "ADA2F5",
                "CFCACA",
                "FFFF96",
                "EAA8FF",
                "FAB9D9",
                "DEBEA2",
                "FC9974",
                "E3A1AE",
                "FAC88C",
                "D99EC3"
            ];

            this.defaultColors = {
                "0": "#CEE7FF", // reservation
                "1": "#BBFC3C", // in-house
                "2": "#FAC854", // checked-out
                "3": "#DDD", // out of order
                "4": "#FFF", // cancelled
                "5": "#e63600", // no show
                "6": "#FFF", // deleted
                "7": "#FFF" // unconfirmed reservation
            };

            this.payPeriods = [
                {id: '0', name: 'Nightly'},
                {id: '1', name: 'Weekly'},
                {id: '2', name: 'Monthly'},
                {id: '3', name: 'One Time'}
            ];

            // array of different option buttons
            this.$allActions = {
                editHousekeepingNotes: $("<li/>").append(
                        $("<a/>", {
                            href: '#',
                            text: "Edit Housekeeping Notes"
                        }).on('click', function (e) {
                    e.preventDefault(); // prevent # scrolling to the top

                    that._initializeInnerModal();

                    $.ajax({
                        type: "POST",
                        url: getBaseURL() + "booking/get_housekeeping_notes_AJAX",
                        data: {
                            booking_id: that.booking.booking_id
                        },
                        dataType: "json",
                        success: function (data) {
                            that._populateHousekeepingNotesModal(data);
                        }
                    })
                })
                        ),
                sendConfirmationEmail: $("<li/>").append(
                        $("<a/>", {
                            href: "#",
                            text: "Send Confirmation Email"
                        }).on('click', function (e) {
                    e.preventDefault(); // prevent # scrolling to the top
                    $.post(getBaseURL() + 'booking/send_booking_confirmation_email/' + that.booking.booking_id, function (response) {
                        alert(response);
                    });
                })
                        ),
                showHistory: $("<li/>").append(
                        $("<a/>", {
                            href: "#",
                            text: "Show History"
                        }).on('click', function (e) {
                    e.preventDefault(); // prevent # scrolling to the top

                    that._initializeInnerModal();

                    $.ajax({
                        type: "POST",
                        url: getBaseURL() + "booking/get_history_AJAX",
                        data: {
                            booking_id: that.booking.booking_id
                        },
                        dataType: "json",
                        success: function (data) {
                            that._populateHistoryModal(data);
                        }
                    });
                })
                        ),
                addExtra: $("<li/>").append(
                        $("<a/>", {
                            href: "#",
                            text: "Add Extra"
                        }).on('click', function (e) {
                    e.preventDefault(); // prevent # scrolling to the top

                    that._initializeInnerModal();

                    var extraData = {
                        extra_id: that.extras[0].extra_id,
                        extra_name: that.extras[0].extra_name,
                        start_date: that.booking.check_in_date,
                        end_date: that.booking.check_out_date,
                        quantity: 1,
                        rate: that.extras[0].rate
                    };

                    $.ajax({
                        type: "POST",
                        url: getBaseURL() + "extra/create_booking_extra_AJAX",
                        data: {
                            booking_id: that.booking.booking_id,
                            extra_data: extraData
                        },
                        dataType: "json",
                        success: function (bookingExtraID) {
                            that._editBookingExtra(bookingExtraID);

                            // if this is the first extra to this booking,
                            // create extra container (panel). otherwise, append extra to existing
                            extraData.booking_extra_id = bookingExtraID;
                            if ($("#extra-container").length) {
                                extraData.charging_scheme =  $("#inner-modal").find("[name='extra_id'] option:selected").attr('data-charging-scheme');
                                $("#extra-container").append(that._getBookingExtraDiv(extraData));
                            }
                            else {
                                if(!that.booking.extras){
                                    that.booking.extras = [];
                                }
                                that.booking.extras.push(extraData);
                                var extraPanel = that._getExtraPanel();
                                $("#booking-modal").find(".modal-body").append(extraPanel);
                            }
                        }
                    })
                })
                        ),
                addRoomBlock: $("<li/>").append(
                        $("<a/>", {
                            href: "#",
                            text: "Add Room Block"
                        })
                        ),
                setCutOffDate: $("<li/>").append(
                        $("<a/>", {
                            href: "#",
                            text: "Set Cut-off date"
                        })
                        ),
                createDuplicate: $("<li/>").append(
                        $("<a/>", {
                            href: "#",
                            text: "Create a duplicate"
                        }).on('click', function (e) {
                    e.preventDefault(); // prevent # scrolling to the top
                    that._createDuplicate();
                })
                        ),
                deleteBooking: $("<li/>").append(
                        $("<a/>", {
                            href: "#",
                            text: "Delete"
                        }).on('click', function (e) {

                    e.preventDefault(); // prevent # scrolling to the top
                    that._deleteBooking();
                })
                        ),
                divider: $("<li/>", {
                    class: "divider"
                })

            };

            this._initializeBookingModal();

        },
        _initializeBookingModal: function () {
            // re-initialize by deleting the existing modal

            this.$panel = $("<div/>", {
                class: "panel panel-default"
            }).append(
                    $("<div/>", {
                        class: "panel-body form-horizontal"
                    })
                    );

            this.$rateInfo = $("<span/>", {
                class: 'rate-info',
                hidden: true
            })
                    .append(" accommodating ")
                    .append(
                            $('<div/>', {
                                class: 'form-group',
                                style: 'margin: 0px 7px 7px 7px;'
                            }).append(
                            this._getSelect('adult_count', this.adultsCount)
                            )
                            )
                    .append(" and ")
                    .append(
                            $('<div/>', {
                                class: 'form-group',
                                style: 'margin: 0px 7px 7px 7px;'
                            }).append(
                            this._getSelect('children_count', this.childrenCount)
                            )
                            )
                    .append("<br/>charging with ")
                    .append(
                            $("<span/>", {
                                class: 'charge-with-div'
                            })
                            )
                    .append(" with rate: ")
                    .append(
                            $('<div/>', {
                                class: 'input-group',
                                style: 'margin: 0px 7px 7px 7px;'
                            })
                            .append(
                                    $("<button/>", {
                                        class: 'form-control edit-rate-btn btn btn-default',
                                        text: 'Edit',
                                        style:"width:auto;"
                                    })
                            )
                            .append(
                                    $("<input/>", {
                                        name: 'rate',
                                        class: 'form-control',
                                        placeholder: "Rate",
                                        value: 0,
                                        type: 'number',
                                        style: 'max-width: 125px;'
                                    })
                                    )
                            .append(
                                    $('<span/>', {
                                        class: 'input-group-addon rate-including-tax hidden'
                                    })
                                    )
                            )
                    .append(
                            $('<div/>', {
                                class: 'form-group',
                                style: 'margin: 0px 7px 7px 7px;'
                            })
                            .append(this._getSelect('pay_period', this.payPeriods))
                            );


            // because it's a $(document) call, it's in the constructor
            // .off exists to prevent from event firing multiple times
            $(document).off('click', '.token').on('click', '.token', function () {
                if (($("#booking-modal").data('bs.modal') || {}).isShown)
                {
                    $(document).openCustomerModal({
                        customer_id: $(this).attr("id"),
                        customer_name: $(this).find(".token-label").text()
                    });
                }

            });

        },
        _initializeInnerModal: function () {
            // re-initialize by deleting the existing modal
            $("#inner-modal").modal('show');
            $("#inner-modal").find(".modal-content").html("");

            $("#inner-modal").on('hidden.bs.modal', function () {
                // hack to prevent closing inner-modal removing modal-open class in body.
                // when modal-open class is removed from body, scrolling the customer-modal scrolls
                // background, instead of scrolling the modal

                if (($("#booking-modal").data('bs.modal') || {}).isShown)
                    $("body").addClass("modal-open");
            })

        },  
        _initializeRateModal: function () {
            var that = this;
            // re-initialize by deleting the existing modal
            $("#edit-rate-modal").modal('show');
            $("#edit-rate-modal").find(".modal-content").html("");
            
            $("#edit-rate-modal").on('hidden.bs.modal', function () {
                // hack to prevent closing inner-modal removing modal-open class in body.
                // when modal-open class is removed from body, scrolling the customer-modal scrolls
                // background, instead of scrolling the modal

                if (($("#booking-modal").data('bs.modal') || {}).isShown)
                    $("body").addClass("modal-open");
            })
            this._populateRateModel();
            
        },
        _constructModalComponents: function () {
            var that = this;

            var modelBodyClass = '';

            if (this.groupInfo != null) {
                modelBodyClass = 'col-lg-9';
            }

            this.$modalBody = $("<div/>", {
                class: "modal-body " + modelBodyClass
            }).html(this._getBookingTypePanel())
                    .append(this._getSingleBookingPanel())
                    .append(this._getCustomerAndNotesPanel())
                    .append(this._getExtraPanel());

            $("#booking-modal").find(".modal-content")
                    .append(
                            $("<div/>", {
                                class: "modal-header"
                            }
                            )
                            )
                    .append(this.$modalBody)
                    .append(
                            $("<div/>", {
                                class: "modal-footer",
                                style: 'clear: both'
                            }
                            )
                            );
            $('.modal-dialog.modal-lg').removeAttr('style');
            if (this.groupInfo != null)
            {
                $('.modal-dialog.modal-lg').css('width', '1110px');
                this.$modalBody.after(
                        $("<div/>", {
                            class: "col-lg-3",
                            style: "padding: 15px 15px 0px 0px;"
                        }).append(
                        $("<div/>", {
                            class: "panel panel-default"
                        }).append(
                        $("<div/>", {
                            class: 'panel-heading '
                        }).append(
                        $("<h3/>", {
                            text: 'Rooms',
                            class: "panel-title bold pull-left",
                            style: "padding-top: 8px;"
                        })
                        )
                        .append(
                                $("<div/>", {
                                    class: 'pull-right btn-group'
                                }).append(
                                $("<button/>", {
                                    html: 'Add a room',
                                    class: 'btn btn-default btn-sm',
                                })
                                .prepend($('<i/>', {class: "fa fa plus"}))
                                .on('click', function () {
                                    that.options.id = null;
                                    that.options.checkInDate = that.booking.check_in_date;
                                    that.options.checkOutDate = that.booking.check_out_date;
                                    that.options.isAddGroupBooking = that.groupInfo;
                                    // that.options.stayingCustomers = that.booking.staying_customers;
                                    $.fn.openBookingModal(that.options);

                                })
                                ).append(
                                $("<button/>", {
                                    type: "button",
                                    class: "btn btn-default  btn-sm  dropdown-toggle",
                                    "data-toggle": "dropdown",
                                    "aria-expanded": false,
                                    text: "Action ",
                                }).append(
                                $("<span/>", {
                                    class: "caret"
                                })
                                )
                                ).append(
                                $("<ul/>", {
                                    class: "dropdown-menu other-actions",
                                    role: "menu"
                                }).append(
                                $("<li/>", {
                                }).append(
                                $("<a/>", {
                                    href: '#',
                                    text: "Cancelled (Hide)"
                                }).on("click", function (e) {
                            e.preventDefault();
                            that._cancelDeleteGroupBookingRoom('Cancel');
                        })
                                )
                                )
                                .append(
                                        $("<li/>", {
                                        }).append(
                                        $("<a/>", {
                                            href: '#',
                                            text: "Delete"
                                        }).on("click", function (e) {
                                    e.preventDefault();
                                    that._cancelDeleteGroupBookingRoom('Delete');
                                })
                                        )
                                        )
                                )
                                )
                        .append($('<div/>', {class: "clearfix"}))
                        )
                        .append(
                                $("<div/>", {
                                    class: "room-lists",
                                    style: 'clear: both;height: 477px;overflow: auto;'
                                })
                                )

                        )
                        );
            }

            this._getLinkedGroupBookingRoomList();

            this._updateNumberOfDays();
            this._updateRoomTypeDDL();
            this._updatePayPeriodDropdown();
            that._updateModalContent();
            this._bookingSource();

            if (that.booking.state !== '3') {
                that._tokenizeCustomerField(that.booking.staying_customers);
            }

            // disable checkin, checkout dates for ota bookings
            if (that.booking.source == 2 || that.booking.source == 3) {
                $('[name="check_in_date"]').parent()
                        .attr('data-toggle', "popover")
                        .popover({
                            content: "Warning: You should not modify this value. Instead, the guest should change it through OTA (e.g. Booking.com)",
                            placement: 'bottom',
                            trigger: "hover"
                        });
                $('[name="check_out_date"]').parent()
                        .attr('data-toggle', "popover")
                        .popover({
                            content: "Warning: You should not modify this value. Instead, the guest should change it through OTA (e.g. Booking.com)",
                            placement: 'bottom',
                            trigger: "hover"
                        });
            }
            that.$modalBody.find("[name='check_in_date'], [name='check_out_date'], [name='rate'], [name='pay_period']").on('change', function () {
                that._displayRateInfo();
            });

            // this is necessary to display booking notes for out of order in bookingModal

            $.when(this.deferredRoomDDL, this.deferredChargeWithDDL).done(function () {
                $("[name='booking_notes']").val(that.booking.booking_notes);
                $("[name='adult_count']").val(that.booking.adult_count);
                $("[name='children_count']").val(that.booking.children_count);
                $("[name='rate']").val(that.booking.rate);
                $("[name='pay_period']").val(that.booking.pay_period);
            });
            $.when(this.deferredBookingSource).done(function () {
                if (that.companyBookingSources.length > 0) {
                    for (var key in that.companyBookingSources) {
                        var source = that.companyBookingSources[key];
                        $('select[name="source"]').append('<option value="' + source['id'] + '">' + source['name'] + '</option>');
                    }
                }
                if (that.booking.source) {
                    $('select[name="source"]').val(that.booking.source);
                }
            });
            
        },
        _updateModalContent: function () {

            this._updateModalHeader();
            this._updateBookingType();
            this._updateColorSelector(this._getDefaultColor());
            this._updateModalFooter();
            
        },
        _displayRateInfo: function (rateArray = null, taxArray = null) {
            var that = this;
            
            var avgRate = 0;
            var rateCount = 0;
            var totalPreTaxRate = 0;
            var totalRate = 0;
            var rateWithTax = 0;
            var numberOfDays = 0;
            var rateNoTax = 0;
            var payPeriod = 0;
            
            if(rateArray && taxArray)
            {
                for(var key in rateArray)
                {
                    var rate = parseFloat(rateArray[key]['rate']);
                    var taxedRate = rate * (1 + parseFloat(taxArray.percentage)) + parseFloat(taxArray.flat_rate);
                                    
                    rateCount++;
                    avgRate += rate;
                    totalPreTaxRate += rate;
                    totalRate += taxedRate;
                }
                avgRate = avgRate / rateCount;
            }
            else
            {
                rateWithTax = parseFloat(this.rateWithTax);
                numberOfDays = parseInt(this.$modalBody.find('input[name="number_of_days"]').val());
                rateNoTax = parseFloat(this.$modalBody.find('input[name="rate"]').val());
                payPeriod = this.$modalBody.find('select[name="pay_period"]').val();

                avgRate = rateNoTax;
                totalPreTaxRate = numberOfDays * rateNoTax;
                totalRate = numberOfDays * rateWithTax;

                if(payPeriod == 1) // weekly
                {
                    avgRate = (rateNoTax / 7);
                    totalPreTaxRate = (Math.floor(numberOfDays / 7) *  rateNoTax) + ((rateNoTax / 7) * (numberOfDays % 7));
                    totalRate = (Math.floor(numberOfDays / 7) *  rateWithTax) + ((rateWithTax / 7) * (numberOfDays % 7));
                }
                else if(payPeriod == 2) // monthly
                {
                    var checkInDate = new Date(this.$modalBody.find('input[name="check_in_date"]').val());
                    var checkOutDate = new Date(this.$modalBody.find('input[name="check_out_date"]').val());
                    if(!checkInDate || !checkOutDate){
                        return;
                    }
                    totalRate = totalPreTaxRate = 0;
                    var lastPeriodDate = new Date(checkInDate.getTime());
                    var date = new Date(checkInDate.getTime());
                    
                    date.setMonth(date.getMonth() + 1);
                    for(date = date; date <= checkOutDate; date.setMonth(date.getMonth() + 1))
                    {
                        lastPeriodDate = new Date(date.getTime());
                        totalPreTaxRate += rateNoTax;
                        totalRate += rateWithTax;
                    }
                    
                    var dayDiff = parseInt((checkOutDate - lastPeriodDate) / (1000 * 60 * 60 * 24));  
                    if(dayDiff > 0)
                    {
                        totalPreTaxRate += ((rateNoTax / 30) * dayDiff);
                        totalRate += ((rateWithTax / 30) * dayDiff);
                    }
                    avgRate = (totalPreTaxRate / numberOfDays);
                }
            }
            
            if(avgRate > 0)
            {
                if(payPeriod != 3) // one time charge
                {
                    this.$modalBody.find('.rate-extra-info-div').html("").remove();
                    this.$modalBody.find('.room-type').append(
                        $("<div/>",{
                            class: "clearfix rate-extra-info-div"
                        }).append(
                            $("<div/>",{
                                class: "col-sm-4",
                                style: "padding: 0;"
                            }).append(
                                $("<span/>",{
                                    class: "col-sm-6",
                                    text: "average rate",
                                    style: "text-align: right; line-height: 30px;"
                                })
                            ).append(
                                $("<span/>",{
                                    class: "input-group-addon",
                                    html: number_format(avgRate, 2, ".", ""),
                                    style: "line-height: 20px;border:  1px solid #ccc;border-radius: 4px;text-align: left;"
                                })
                            )
                        ).append(
                            $("<div/>",{
                                class: "col-sm-6",
                                style: "padding: 0;"
                            }).append(
                                $("<span/>",{
                                    class: "col-sm-3",
                                    text: "total(pre-tax)",
                                    style: "line-height: 34px; padding-right: 0; text-align: right;"
                                })
                            )
                            .append(
                                $("<span/>",{
                                    class: "col-sm-8 input-group"
                                })
                                .append(
                                    $("<span/>",{
                                        class: "input-group-addon",
                                        text: number_format(totalPreTaxRate, 2, ".", ""),
                                        style: "line-height: 20px;border: 1px solid #ccc;"
                                    })
                                ).append(
                                    $("<span/>",{
                                        class: "input-group-addon",
                                        html: "(with tax: "+number_format(totalRate, 2, ".", "")+")"
                                    })
                                )
                            )
                        )
                    ); 
                }
                else
                {
                    this.$modalBody.find('.rate-extra-info-div').remove();
                }
            }
        },
        _populateNewBookingModal: function () {
            var that = this;
            $("#booking-modal").find(".modal-content").html("");
            
            // get extras for new booking
            $.getJSON(getBaseURL() + 'extra/get_all_extras_JSON',
                function (data) {
                    that.extras = data;
                }
            );
    
            this._constructModalComponents();

        },
        _populateEditBookingModal: function () {

            var that = this;
            $("#booking-modal").find(".modal-content").html("");

            $.ajax({
                type: "POST",
                url: getBaseURL() + "booking/get_booking_AJAX",
                data: {
                    booking_id: this.options.id
                },
                dataType: "json",
                success: function (data) {
                    that.booking = data.booking;
                    that.extras = data.extras; // get all extras types that belong to company
                    that.groupInfo = data.group_info;
                    that.booking.staying_customers.unshift(that.booking.paying_customer);
                    that._constructModalComponents();
                },
                error: function () {
                    console.log("booking not accesible for this company/user");
                    //that._closeBookingModal();
                }

            }); // -- ajax call
        },
        _getBookingTypePanel: function () {
            var state = this.booking.state;

            var panel = this.$panel.clone();

            panel.find(".panel-body")
                    .append(
                            $("<label/>", {
                                for : "state",
                                class: "col-sm-2 control-label",
                                text: "Type"
                            })
                            )
                    .append(
                            $("<div/>", {
                                class: "col-sm-4"
                            }).append(this._getBookingTypes(state))
                            )
                    .append(
                            $("<div/>", {
                                class: "col-sm-1"
                            }).html(this._getColorPicker())
                            )
                    .append(
                            $("<div/>", {
                                class: "col-sm-5 booking-buttons"
                            })

                            );

            return panel;
        },
        _bookingSource: function () {
            var that = this;
            $.post(getBaseURL() + "booking/get_booking_source_AJAX/",
                    function (data) {
                        if(data){
                            that.companyBookingSources = jQuery.parseJSON(data);
                            that.deferredBookingSource.resolve();
                        }
                    }
            );
        },
        _getLinkedGroupBookingRoomList: function () {
            var that = this;
            var groupId = '';
            var groupBookingRL = '';

            if (that.groupInfo != null) {
                groupId = that.groupInfo.group_id;
                $.ajax({
                    type: 'POST',
                    url: getBaseURL() + "booking/get_booking_linked_group_room_list_AJAX/",
                    data: {'group_id': groupId},
                    dataType: 'json',
                    success: function (data) {
                        groupBookingRL = data.booked_rooms_list;

                        if (groupBookingRL.length > 0) {
                            var roomsList = $('.modal-content').find('.room-lists');
                            roomsList.html('');
                            var activeRoomBg = '';
                            var cRoomColor = '';
                            var showCheckBox = '';
                            $.each(groupBookingRL, function (key, objVal) {
                                if (that.booking.booking_id == objVal.booking_id)
                                    activeRoomBg = '#f2f2f2';
                                else
                                    activeRoomBg = 'transparent';

                                if (objVal.room_cancelled == true) {
                                    cRoomColor = 'red';
                                    showCheckBox = 'none';
                                } else {
                                    cRoomColor = '#000';
                                    showCheckBox = 'inline-block';
                                }

                                roomsList.append(
                                        $("<div/>", {
                                            class: "room-list-info",
                                            style: 'display:block;border-top: 1px solid #ddd;padding: 5px 15px; background:' + activeRoomBg + ';color:' + cRoomColor,
                                            id: objVal.booking_id,
                                            'data-room-id': objVal.room_id,
                                            'data-booking-cancelled': objVal.room_cancelled
                                        }).append(
                                        $("<div/>", {
                                            style: 'padding:0 12px 0 0;display:' + showCheckBox
                                        }).append(
                                        $("<input/>", {
                                            type: 'checkbox',
                                            class: 'cancelled-room-checkbox'
                                        })
                                        )
                                        ).append(
                                        $("<div/>", {
                                            style: 'display: inline-block;vertical-align: top;cursor:pointer;'
                                        }).append(
                                        $("<p/>", {
                                            class: "room-name",
                                            style: 'margin-bottom:2px',
                                            html: '<strong>Room</strong> ' + objVal.room_name
                                        })
                                        ).append(
                                        $("<p/>", {
                                            style: 'margin-bottom:2px',
                                            html: objVal.customer_name
                                        })
                                        ).append(
                                        $("<p/>", {
                                            style: 'margin-bottom:2px',
                                            html: objVal.check_in_date + ' to ' + objVal.check_out_date
                                        })
                                        ).on('click', function () {
                                    var options = {};
                                    options.id = objVal.booking_id;
                                    var body = $("body"); // call booking model 
                                    $.data(body, 'bookingModal', new BookingModal(options));
                                })
                                        )
                                        )

                            });
                        }
                    }
                });
            }
        },
        _getCustomerAndNotesPanel: function () {

            var that = this;
            var state = this.booking.state;

            var panel = this.$panel.clone();

            panel.find(".panel-body").addClass("form-horizontal")

            // don't display customer field for out of order
            if (state !== '3') {

                panel.find(".panel-body").append(
                        $("<div/>", {
                            class: "form-group customer-info"
                        }).append(
                        $("<label/>", {
                            for : "customers",
                            class: "col-sm-2 control-label",
                            text: "Customers"
                        })
                        ).append(
                        $("<div/>", {
                            class: "col-sm-10"
                        }).append(
                        $("<input/>", {
                            class: "form-control",
                            name: "customers",
                            rows: 1
                        })
                        )
                        )
                        );
            }

            var sourceSelectDiv = $("<select/>", {
                class: "form-control",
                name: "source"
            });

            $.each(this.sources, function (i, source) {
                var option = $("<option/>", {
                    value: source.id,
                    text: source.name
                });

                if (source.id == that.booking.source)
                {
                    option.prop("selected", "selected");
                }

                sourceSelectDiv.append(option);
            });

            panel.find(".panel-body")
                    .append(
                            $("<div/>", {
                                class: "form-group"
                            }).append(
                            $("<label/>", {
                                for : "booking-notes",
                                class: "col-sm-2 control-label",
                                text: "Notes"
                            })
                            ).append(
                            $("<div/>", {
                                class: "col-sm-10"
                            }).append(
                            $("<textarea/>", {
                                class: "form-control",
                                name: "booking_notes",
                                rows: 4,
                                text: that.booking.booking_notes
                            })
                            )
                            )
                            )
                    .append(
                            $("<div/>", {
                                class: "form-group"
                            }).append(
                            $("<label/>", {
                                for : "source",
                                class: "col-sm-2 control-label",
                                text: "Source"
                            })
                            ).append($("<div/>", {
                        class: "col-sm-10"
                    }).append(sourceSelectDiv)
                            )
                            );

            return panel;

        },
        _populateExtraModal: function (extra) {
            var that = this;

            // construct header
            $("#inner-modal").find(".modal-content")
                    .append(
                            $("<div/>", {
                                class: "modal-header"
                            })
                            .append("Extra Information")
                            .append(
                                    $("<button/>", {
                                        class: "close",
                                        "data-dismiss": "modal",
                                        "aria-label": "Close"
                                    }).append(
                                    $("<span/>", {
                                        "aria-hidden": "true",
                                        html: "&times;"
                                    })
                                    )
                                    )
                            )
                    .append(
                            $("<div/>", {
                                class: "modal-body form-horizontal"
                            })
                            .append(this._getExtraSelect("Extra", 'extra_id', that.extras, extra.extra_id))
                            .append(this._getHorizontalInput("Start Date", 'start_date', extra.start_date))
                            .append(this._getHorizontalInput("End Date", 'end_date', extra.end_date))
                            .append(this._getHorizontalInput("Quantity", 'quantity', extra.quantity))
                            .append(this._getHorizontalInput("Rate", 'rate', extra.rate))
                            )
                    .append(
                            $("<div/>", {
                                class: "modal-footer"
                            })
                            .append(
                                    $("<button/>", {
                                        type: "button",
                                        class: "btn btn-success",
                                        id: "button-update-extra",
                                        text: "Update"
                                    }).on('click', function () {

                                var extraData = that._fetchExtraData();
                                $.ajax({
                                    type: "POST",
                                    url: getBaseURL() + "extra/update_booking_extra_AJAX",
                                    data: {
                                        booking_extra_id: extra.booking_extra_id,
                                        booking_extra_data: extraData
                                    },
                                    dataType: "json",
                                    success: function (data) {

                                        // hack to properly generate extra div. it needs extra_name nad scheme
                                        extraData.extra_name = $("#inner-modal").find("[name='extra_id'] option:selected").text();
                                        extraData.booking_extra_id = extra.booking_extra_id;
                                        extraData.charging_scheme = $("#inner-modal").find("[name='extra_id'] option:selected").attr('data-charging-scheme');
                                        $(".extra#" + extra.booking_extra_id).replaceWith(that._getBookingExtraDiv(extraData));
                                        $("#inner-modal").modal('hide');
                                    }
                                })
                            })
                                    )
                            .append(
                                    $("<button/>", {
                                        type: "button",
                                        class: "btn btn-default",
                                        "data-dismiss": "modal",
                                        text: "Close"
                                    })
                                    )
                            );
                    
                     
            var chargeScheme = $("#inner-modal [name='extra_id'] option:selected").attr('data-charging-scheme');
            if( chargeScheme == 'on_start_date')
            {
                $("#inner-modal").find('.block_end_date').remove();
                $("#inner-modal").find('.block_start_date').addClass('hidden');
            }
           
            $("#inner-modal").find("[name='start_date']").datepicker({
                dateFormat: 'yy-mm-dd',
                beforeShow: that._customRange
            });

            $("#inner-modal").find("[name='end_date']").datepicker({
                dateFormat: 'yy-mm-dd',
                beforeShow: that._customRange
            });
            
            $("#inner-modal").find("[name='extra_id']").on('change', function(){
                var extraVal = $(this).val();
                
                for(var key in that.extras){
                    if(extraVal == that.extras[key].extra_id) {
                        $("#inner-modal").find('input[name="rate"]').val(that.extras[key].rate);
                    }
                }
                
                var chargeScheme = $(this).find('option[value="'+extraVal+'"]').attr('data-charging-scheme');
                if(chargeScheme == "on_start_date")
                {
                   $("#inner-modal").find('.block_end_date').remove();
                   $("#inner-modal").find('.block_start_date').addClass('hidden');
                }
                else
                {
                    $("#inner-modal").find('.modal-body').append(that._getHorizontalInput("End Date", 'end_date', extra.end_date));
                    $("#inner-modal").find('.block_start_date').removeClass('hidden');
                }
            });
           
        },
        _populateRateModel: function(){
            var that = this;
           
            // construct modal header
            $("#edit-rate-modal").find(".modal-content")
                    .append(
                            $("<div/>", {
                                class: "modal-header"
                            })
                        .append(
                                $("<button/>", {
                                    class: "close",
                                    "data-dismiss": "modal",
                                    "aria-label": "Close"
                                }).append(
                                $("<span/>", {
                                    "aria-hidden": "true",
                                    html: "&times;"
                                })
                                )
                            )
                        )
                    .append( // construct modal body 
                        $("<div/>", {
                            class: "modal-body form-horizontal"
                        }).append(
                            $("<div/>", {
                                class: "panel panel-default"
                            }).append(
                                $("<div/>", {
                                    class: "panel-body text-center",
                                }).append(
                                    $("<span/>", {
                                        class: "h4",
                                        id: "selected-rate-plan",
                                        html: this.$modalBody.find('select.charge-with option:selected').text()
                                    })
                                )
                            )
                        ).append(
                            $("<div/>", {
                                class: "panel panel-default"
                            }).append(
                                $("<div/>", {
                                    class: "panel-body text-center",
                                }).append(
                                    $("<div/>", {
                                        style: "overflow-x: auto;"
                                    }).append(
                                        $("<table/>", {
                                            class: "table table-bordered text-right",
                                            id: "rate-edit-table",
                                            style: "width:auto;margin:0 auto;"                                         
                                        }).append(
                                            $("<tr/>", {
                                                class: "rate-row-th"
                                            })
                                        ).append(
                                            $("<tr/>", {
                                                class: "rate-row-td"                                          
                                            })
                                        )
                                    )
                                )
                            )
                        )
                    )
                    .append(      //  construct modal footer 
                            $("<div/>", {
                                class: "modal-footer"
                            })
                            .append(
                                $("<button/>", {
                                    type: "button",
                                    class: "btn btn-success",
                                    id: "update-rate-btn",
                                    text: "ok",
                                    style: "width: 70px;"
                                }).on('click', function () {
                                    
                                    var modalBody = $(this).parents('body').find('#booking-modal .modal-body');
                                    
                                    $(this).prop('disabled', true);
                                    
                                    var rateArray = {
                                        start_date: modalBody.find('[name="check_in_date"]').val(),
                                        end_date: modalBody.find('[name="check_out_date"]').val(),
                                        rate_plan_id: that.newRatePlanId,
                                        room_type_id: modalBody.find('[name="room_type_id"] option:selected').val(),
                                        booking_id: that.booking.booking_id
                                    };

                                    var ratesAr = [];
                                    $('.rate-row-td td').each(function(){

                                        ratesAr.push({
                                            date: $(this).attr('data-attr-date'),
                                            day: $(this).attr('data-attr-day'),
                                            rate: $(this).find("input").val()
                                        });

                                    });

                                    rateArray['rates'] = ratesAr;

                                    $.post(getBaseURL() + 'settings/rates/create_custom_rate_AJAX', 
                                        rateArray,
                                        function (data) {
                                            data = JSON.parse(data);
                                            if(data.status == "success"){
                                                that.newRatePlanId = data.new_rate_plan_id;
                                                alert(l('rates have been updated successfully'));
                                                $('#update-rate-btn').prop('disabled', false);
                                                that._getCustomRates(data.new_rate_plan_id);
                                            }
                                        }
                                    )
                                })
                            )
                            .append(
                                    $("<button/>", {
                                        type: "button",
                                        class: "btn btn-default",
                                        "data-dismiss": "modal",
                                        text: "Close"
                                    })
                                )
                            );
            that._getCustomRates();
              
        },
        _getCustomRates: function(newRatePlanId){
         
            var that = this;
           
            var ratePlanId = (newRatePlanId != null) ? newRatePlanId : this.$modalBody.find('select.charge-with').val();

            var editRateRowTh = $('#rate-edit-table .rate-row-th');
            var editRateRowTd = $('#rate-edit-table .rate-row-td');
            var ratePlanName = this.$modalBody.find('.charge-with option:selected').text();
                       
            var rateValue = 0;
            if(ratePlanName !== 'Custom Rate Plan')
            {
                rateValue = this.$modalBody.find('[name="rate"]').val(); // set default rate value
            }
                         
            editRateRowTh.html("");
            editRateRowTd.html("");
           
            $.post(getBaseURL() + 'settings/rates/get_cusom_rates_AJAX', {
                    start_date: this.$modalBody.find('[name="check_in_date"]').val(),
                    end_date: this.$modalBody.find('[name="check_out_date"]').val(),
                    rate_plan_id: ratePlanId,
                    rate_plan_name: ratePlanName
                }, function (data) {
                    var data = JSON.parse(data);
                   
                    if(data[0].rate_plan_name){
                        $("#selected-rate-plan").html(data[0].rate_plan_name);
                        
                        var chargeDropdown =  $("body #booking-modal .modal-body").find("select.charge-with");
                        var prevoiusRatePlanId = chargeDropdown.val();
                        
                        chargeDropdown.find("option.custom-rate-plan").remove() // remove previous custom rate plan                    
                        
                        if(data[0].rate_plan_name == "Custom Rate Plan")
                            chargeDropdown.find("optgroup[label='Rate Plans (Pre-set)']").append('<option selected class="rate-plan custom-rate-plan" value="'+data[0].rate_plan_id+'">'+data[0].rate_plan_name+'</option>');
                        $("body #booking-modal .modal-body").find("input[name='rate']").val(data[0].base_rate);
                    }
                    
                    var monthName = {0:'Jan', 1:'Feb', 2:'Mar', 3:'Apr', 4:'May', 5:'Jun', 6:'Jul', 7:'Aug', 8:'Sep', 9:'Oct', 10:'Nov', 11:'Dec'};
                    $.each(data, function(index, value)
                    {
                        var dayOfWeek = '';

                        switch (value.day_of_week)
                        {
                            case '0': dayOfWeek = "Mo"; break;
                            case '1': dayOfWeek = "Tu"; break;
                            case '2': dayOfWeek = "We"; break;
                            case '3': dayOfWeek = "Th"; break;
                            case '4': dayOfWeek = "Fr"; break;
                            case '5': dayOfWeek = "Sa"; break;
                            case '6': dayOfWeek = "Su"; break;
                        }
                        var d = new Date(value.date);

                        if(value.base_rate != null) // set custom rate value 
                            rateValue = value.base_rate;
                        
                        editRateRowTh.append(
                            $("<th/>",{
                                html: monthName[d.getMonth()]+'<br>'+d.getDate()+'<br>'+dayOfWeek,
                            })
                        ); 

                        editRateRowTd.append(
                                $("<td/>",{
                                    "data-attr-date": value.date,
                                    "data-attr-day": dayOfWeek
                            }).append(
                                $("<input/>", {
                                   style: "width: 40px;padding: 2px;border: none;" ,
                                   value: rateValue,
                                   class: "adult-rates",
                                   name: "rate_"+dayOfWeek
                                })
                            )
                        );  

                    });
                    var roomTypeDIV = $("body #booking-modal .modal-body").find(".room-type");
                    that._updateRate(roomTypeDIV);
                }
            );
        },
        _populateHousekeepingNotesModal: function (housekeeping_notes) {
            var that = this;

            // construct header
            $("#inner-modal").find(".modal-content")
                    .append(
                            $("<div/>", {
                                class: "modal-header"
                            })
                            .append("Housekeeping Notes")
                            .append(
                                    $("<button/>", {
                                        class: "close",
                                        "data-dismiss": "modal",
                                        "aria-label": "Close"
                                    }).append(
                                    $("<span/>", {
                                        "aria-hidden": "true",
                                        html: "&times;"
                                    })
                                    )
                                    )
                            )
                    .append(
                            $("<div/>", {
                                class: "modal-body"
                            })
                            .append(
                                    $("<div/>", {
                                        class: "form-group"
                                    }).append(
                                    $("<label/>", {
                                        for : "customer_notes",
                                        class: "control-label",
                                        text: "Notes"
                                    })
                                    ).append(
                                    $("<textarea/>", {
                                        class: "form-control",
                                        name: "housekeeping_notes",
                                        rows: 3,
                                        text: housekeeping_notes
                                    })
                                    )
                                    )
                            )
                    .append(
                            $("<div/>", {
                                class: "modal-footer"
                            })
                            .append(
                                    $("<button/>", {
                                        type: "button",
                                        class: "btn btn-success",
                                        text: "Update"
                                    }).on('click', function () {

                                $.ajax({
                                    type: "POST",
                                    url: getBaseURL() + "booking/update_housekeeping_notes_AJAX",
                                    data: {
                                        booking_id: that.booking.booking_id,
                                        housekeeping_notes: $("#inner-modal").find("[name='housekeeping_notes']").val()
                                    },
                                    dataType: "json",
                                    success: function (data) {

                                        $("#inner-modal").modal('hide');
                                    }
                                })
                            })
                                    )
                            .append(
                                    $("<button/>", {
                                        type: "button",
                                        class: "btn btn-default",
                                        "data-dismiss": "modal",
                                        text: "Close"
                                    })
                                    )
                            )

        },
        _populateHistoryModal: function (logs) {
            var that = this;

            // construct header
            $("#inner-modal").find(".modal-content")
                    .append(
                            $("<div/>", {
                                class: "modal-header"
                            })
                            .append("Booking History")
                            .append(
                                    $("<button/>", {
                                        class: "close",
                                        "data-dismiss": "modal",
                                        "aria-label": "Close"
                                    }).append(
                                    $("<span/>", {
                                        "aria-hidden": "true",
                                        html: "&times;"
                                    })
                                    )
                                    )
                            )
                    .append(
                            $("<div/>", {
                                class: "modal-body"
                            })
                            )
                    .append(
                            $("<div/>", {
                                class: "modal-footer"
                            })
                            .append(
                                    $("<button/>", {
                                        type: "button",
                                        class: "btn btn-default",
                                        "data-dismiss": "modal",
                                        text: "Close"
                                    })
                                    )
                            );

            logs.forEach(function (log) {
                $("#inner-modal").find(".modal-body").append(
                        $("<div/>", {
                            class: "panel panel-default"
                        }).append(
                        $("<div/>", {
                            class: "panel-body",
                            html: log.date_time + " by " + log.first_name + " " + log.last_name + " - " + log.log
                        })
                        )
                        )
            });

        },
        _populateExistingCustomerConfirmationModal: function (token, item) {
            var that = this;

            var existingCustomerDiv = $("<div/>", {
                text: "Did you mean: " + item.customer_name
            }).append(
                    $("<div/>", {
                        class: "small",
                        text: ((item.email) ? item.email : '') + ((item.phone) ? " - " + item.phone : '') + ((item.city) ? " - " + item.city : '') + ((item.country) ? " - " + item.country : '')
                    })
                    );

            // construct header
            $("#inner-modal").find(".modal-content")
                    .append(
                            $("<div/>", {
                                class: "modal-header"
                            })
                            .append("<b>\"" + item.customer_name + "\" already exists in the system</b>")
                            .append(
                                    $("<button/>", {
                                        class: "close",
                                        "data-dismiss": "modal",
                                        "aria-label": "Close"
                                    }).append(
                                    $("<span/>", {
                                        "aria-hidden": "true",
                                        html: "&times;"
                                    })
                                    ).on("click", function () {
                                token.remove();
                            })
                                    )
                            )
                    .append(
                            $("<div/>", {
                                class: "modal-body",
                                html: existingCustomerDiv
                            })
                            )
                    .append(
                            $("<div/>", {
                                class: "modal-footer"
                            })
                            .append(
                                    $("<button/>", {
                                        type: "button",
                                        class: "btn btn-success",
                                        text: "Yes. It's a Returning Customer"
                                    }).on("click", function () {
                                token.find(".token-label").text(item.customer_name); // to ensure capitalization is correctly reflected
                                token.attr("id", item.customer_id);
                                that.deferredExistingCustomerConfirmation.resolve();
                                $("#inner-modal").modal('hide');
                            })
                                    )
                            .append(
                                    $("<button/>", {
                                        type: "button",
                                        class: "btn btn-default",
                                        text: "No, It's a different customer with a same name"
                                    }).on("click", function () {
                                that.deferredExistingCustomerConfirmation.resolve();
                                $("#inner-modal").modal('hide');
                            })
                                    )
                            );
        },
        _fetchExtraData: function () {
            var $inner_modal = $("#inner-modal");
            return {
                extra_id: $inner_modal.find("[name='extra_id']").val(),
                start_date: $inner_modal.find("[name='start_date']").val(),
                end_date: $inner_modal.find("[name='end_date']").val(),
                quantity: $inner_modal.find("[name='quantity']").val(),
                rate: parseFloat($inner_modal.find("[name='rate']").val()).toFixed(2),
            };
        },
        // Makes jquery datepicker to have limited range. (Greying out invalid selections)
        _customRange: function (input) {
            // disable the datepicker input field, so only calendar is allowed. 
            //This also prevents keyboard pop up in ipad
            //$(this).attr("disabled", true);

            var dateMin = null;
            var dateMax = null;

            if (input.name == "check_in_date") {
                if ($("[name='check_out_date']").val() != '') {
                    dateMax = $("[name='check_out_date']").val();
                }
            }
            else if (input.name == "check_out_date") {
                if ($("[name='check_in_date']").val() != '') {
                    var dateMin = $("[name='check_in_date']").val();
                }
            }

            return {
                minDate: dateMin,
                maxDate: dateMax
            };

        },
        _getSingleBookingPanel: function () {

            var that = this;
            // prepare block template

            if (that.groupInfo != null && that.booking.state == 4) {
                that.disableRoomBlock = 'cursor:not-allowed;background:#f2f2f2';
                that.pointerNone = 'pointer-events:none';
            }
            var block = $('<div/>', {
                class: 'panel panel-default panel-booking',
                style: that.disableRoomBlock
            })
                    .append(
                            $('<div/>', {
                                class: 'panel-body form-inline',
                                style: that.pointerNone
                            })
                            .append(
                                    $('<label/>', {
                                        class: 'col-sm-2 text-right',
                                        html: 'Room Block'
                                    })
                                    )
                            .append(
                                    $('<div/>', {
                                        class: 'col-sm-10 text-left group-booking-button-div'
                                    })
                                    )
                            //.append(that.$deleteBookingBlockButton) // only for new Bookings
                            .append(
                                    $('<div/>', {
                                        class: 'row col-sm-12 text-center room-type single-booking',
                                        style: 'margin-top: 10px;'
                                    })
                                    )
                            );

            // display group booking button in new bookings only
            // do not display in edit booking mode
            if (this.booking.booking_id === undefined) {
                if (that.groupInfo == null) {
                    block.find('.group-booking-button-div')
                            .append(
                                    $("<div/>", {
                                        class: "btn-group",
                                        'data-toggle': "buttons"
                                    })
                                    .append(
                                            $("<label/>", {
                                                class: "btn btn-default btn-xs active",
                                                text: "Single"
                                            })
                                            .append(
                                                    $("<input/>", {
                                                        type: "radio",
                                                        checked: true
                                                    })
                                                    )
                                            )
                                    .append(
                                            $("<label/>", {
                                                class: "btn btn-default btn-xs",
                                                text: "Multiple"
                                            })
                                            .append(
                                                    $("<input/>", {
                                                        type: "radio"
                                                    })
                                                    ).on("click", function () {
                                        that.selectedGroupType = 'group';
                                        $('.panel-booking').replaceWith(that._getGroupBookingPanel());
                                        $.when(that._getGroupBookingPanel()).done(function () {
                                            that._updateNumberOfDays();
                                            that._updateRoomGroupList();
                                        })
                                    })
                                            ).append(
                                    $("<label/>", {
                                        class: "btn btn-default btn-xs",
                                        text: "Group"
                                    })
                                    .append(
                                            $("<input/>", {
                                                type: "radio"

                                            })
                                            ).on("click", function () {
                                that.selectedGroupType = 'linked_group';
                                $('.panel-booking').replaceWith(that._getGroupBookingPanel());
                                $.when(that._getGroupBookingPanel()).done(function () {
                                    that._updateNumberOfDays();
                                    that._updateRoomGroupList();

                                })
                            })
                                    )
                                    )

                }
            }

            var roomTypeDIV = block.find(".room-type");
            roomTypeDIV.append("From ")
                    .append($('<span/>', {style: "color:red;", text: "*"}))
                    .append(
                            $('<div/>', {
                                class: 'form-group'
                            })
                            .append(
                                    $("<input/>", {
                                        name: 'check_in_date',
                                        class: 'form-control',
                                        placeholder: "Check-in Date",
                                        value: that.booking.check_in_date,
                                        style: 'max-width: 125px;margin: 0px 7px 7px 7px;'
                                    }).datepicker({
                                dateFormat: 'yy-mm-dd',
                                beforeShow: that._customRange
                            }).on('change', function () {
                                if (that.groupInfo != null &&  that.booking.booking_id != null) {
                                    if (that.saveAllGroupDate == null) {
                                        that._confirmationGroupDateModel(this);
                                    }
                                }
                                setTimeout(function () {
                                    $(document).find("[name='check_out_date']").focus();
                                }, 200);
                            })
                                    )
                            )
                    .append(" to ")
                    .append($('<span/>', {style: "color:red;", text: "*"}))
                    .append(
                            $('<div/>', {
                                class: 'input-group',
                                style: 'margin: 0px 7px 7px 7px;'
                            })
                            .append(
                                    $("<input/>", {
                                        name: 'check_out_date',
                                        class: 'form-control',
                                        placeholder: "Check-out Date",
                                        value: that.booking.check_out_date,
                                        style: 'max-width: 125px;'
                                    }).datepicker({
                                dateFormat: 'yy-mm-dd',
                                beforeShow: that._customRange
                            }).on('change', function () {
                                if (that.groupInfo != null) {
                                    if (that.saveAllGroupDate == null && that.booking.booking_id != null) {
                                        that._confirmationGroupDateModel(this);
                                    }
                                }
                                if (new Date(this.value) < new Date($("[name='check_in_date']").val())) {
                                    alert(l("Check-out-date can't be less than Check-in-date"));
                                    $("[name='check_out_date']").val($("[name='check_in_date']").val());
                                    $("[name='check_out_date']").focus();
                                }
                            })
                                    )
                            .append(
                                    $("<span/>", {
                                        class: "input-group-addon",
                                        style: "padding: 3px;"
                                    }).append(
                                    $("<input/>", {
                                        name: 'number_of_days',
                                        maxlength: '3',
                                        style: 'padding: 2px; width: 30px;'
                                    }).on("change", function () {
                                // set number of days as check_out_date - check_in_date
                                var cid = $("[name='check_in_date']").val().split(/[-]/);
                                var number_of_days = that.$modalBody.find("[name='number_of_days']").val();
                                if (number_of_days == "")
                                {
                                    that.$modalBody.find("[name='check_out_date']").val("");
                                }
                                else
                                {
                                    number_of_days = parseInt(number_of_days);
                                    // Apply each element to the Date function
                                    var check_in_date = new Date(cid[0], cid[1] - 1, cid[2]);
                                    var check_out_date = new Date(check_in_date);
                                    check_out_date.setDate(check_out_date.getDate() + number_of_days);
                                    var year = check_out_date.getFullYear();
                                    var month = ("0" + (check_out_date.getMonth() + 1)).slice(-2);
                                    var day = ("0" + check_out_date.getDate()).slice(-2);
                                    that.$modalBody.find("[name='check_out_date']").val(year + "-" + month + "-" + day);
                                }
                                that._updatePayPeriodDropdown();
                            })
                                    ).append(" nights")
                                    )
                            ).append(
                    $("<span/>", {
                        class: 'room-type-ddl-span'
                    })
                    ).append(
                    $("<span/>", {
                        class: 'room-ddl-span'
                    })
                    ).append(that.$rateInfo.clone());

            roomTypeDIV.find("[name='check_in_date'], [name='check_out_date'], [name='number_of_days']")
                    .on('change', function () {
                        that._updateNumberOfDays();
                        that._updateRoomTypeDDL();
                });

            roomTypeDIV.find("[name='adult_count'], [name='children_count'], [name='rate']")
                    .on('change', function () {
                        that._validateCapacity();
                        that._updateRate($(this).closest('.room-type'));
                });

            // if walk-in, disable check-in date
            if ($("[name='state']").val() === '1' && that.booking.booking_id === undefined) {
                block.find("[name='check_in_date']").val($("#sellingDate").val());
                block.find("[name='check_in_date']").prop("disabled", true);
            }
            
            if (that.booking.source != 2 && that.booking.source != 3 && that.booking.source != 4 && that.booking.source != 8) {
                roomTypeDIV.find('.edit-rate-btn').on('click', function(){
                    that._initializeRateModal();
                });
            }
            else
            {
                roomTypeDIV.find('.edit-rate-btn').css('cursor', 'not-allowed')
            }
            
            
            return block;
        },
        _updatePayPeriodDropdown: function () {
            var that = this;
            var number_of_days = parseInt(that.$modalBody.find("[name='number_of_days']").val());
            if (number_of_days >= 7) {
                that.$modalBody.find('[name="pay_period"] option[value=1]').attr('disabled', false).show();
            } else {
                that.$modalBody.find('[name="pay_period"] option[value=1]').attr('disabled', true).hide();
            }
        },
        _getGroupBookingPanel: function () {

            var that = this;
            var checkLinkGroup = false;
            var activeLinkGroup = '';
            var checkGroup = false;
            var activeGroup = '';
            if (that.selectedGroupType == 'linked_group') {
                checkLinkGroup = true;
                activeLinkGroup = 'active';
            }
            else {
                checkGroup = true;
                activeGroup = 'active';
            }

            // prepare block template
            var block = $('<div/>', {
                class: 'panel panel-default panel-booking'
            })
                    .append(
                            $('<div/>', {
                                class: 'panel-body form-inline',
                            })
                            .append(
                                    $('<label/>', {
                                        class: 'col-sm-2 text-right',
                                        html: 'Room Block'
                                    })
                                    )
                            .append(
                                    $('<div/>', {
                                        class: 'col-sm-10 text-left'
                                    })
                                    .append(
                                            $("<div/>", {
                                                class: "btn-group",
                                                'data-toggle': "buttons"
                                            })
                                            .append(
                                                    $("<label/>", {
                                                        class: "btn btn-default btn-xs",
                                                        text: "Single"
                                                    })
                                                    .append(
                                                            $("<input/>", {
                                                                type: "radio"
                                                            })
                                                            )
                                                    .on("click", function () {
                                                        that.selectedGroupType = 'single';
                                                        $('.panel-booking').replaceWith(that._getSingleBookingPanel());
                                                        $.when(that._getGroupBookingPanel()).done(function () {
                                                            that._updateNumberOfDays();
                                                            that._updateRoomTypeDDL();
                                                        });
                                                    })
                                                    )
                                            .append(
                                                    $("<label/>", {
                                                        class: "btn btn-default btn-xs " + activeGroup,
                                                        text: "Multiple"
                                                    })
                                                    .append(
                                                            $("<input/>", {
                                                                type: "radio",
                                                                checked: checkGroup
                                                            })
                                                            ).on("click", function () {
                                                that.selectedGroupType = 'group';
                                                $('.panel-booking').replaceWith(that._getGroupBookingPanel());
                                                $.when(that._getGroupBookingPanel()).done(function () {
                                                    that._updateNumberOfDays();
                                                    that._updateRoomTypeDDL();
                                                    that._updateRoomGroupList();
                                                });
                                            })
                                                    )
                                            .append(
                                                    $("<label/>", {
                                                        class: "btn btn-default btn-xs " + activeLinkGroup,
                                                        text: "Group"
                                                    })
                                                    .append(
                                                            $("<input/>", {
                                                                type: "radio",
                                                                checked: checkLinkGroup
                                                            })
                                                            ).on("click", function () {
                                                that.selectedGroupType = 'linked_group';
                                                $('.panel-booking').replaceWith(that._getGroupBookingPanel());
                                                $.when(that._getGroupBookingPanel()).done(function () {
                                                    that._updateNumberOfDays();
                                                    that._updateRoomTypeDDL();
                                                    that._updateRoomGroupList();
                                                });
                                            })
                                                    )
                                            )
                                    )
                            .append(
                                    $("<div/>", {
                                        class: "row"
                                    })

                                    .append(
                                            $('<div/>', {
                                                class: 'row col-sm-12 text-center',
                                                style: 'margin-top: 10px'
                                            })
                                            .append("From ")
                                            .append($('<span/>', {style: "color:red;", text: "*"}))
                                            .append(
                                                    $('<div/>', {
                                                        class: 'form-group'
                                                    })
                                                    .append(
                                                            $("<input/>", {
                                                                name: 'check_in_date',
                                                                class: 'form-control',
                                                                placeholder: "Check-in Date",
                                                                value: that.booking.check_in_date,
                                                                style: 'max-width: 125px;margin: 0px 7px 7px 7px;'
                                                            }).datepicker({
                                                        dateFormat: 'yy-mm-dd',
                                                        beforeShow: that._customRange
                                                    }).on('change', function () {

                                                        setTimeout(function () {
                                                            $("[name='check_out_date']").focus();
                                                        }, 200);

                                                    })
                                                            )
                                                    )
                                            .append(" to ")
                                            .append($('<span/>', {style: "color:red;", text: "*"}))
                                            .append(
                                                    $('<div/>', {
                                                        class: 'input-group',
                                                        style: 'margin: 0px 7px 7px 7px;'
                                                    })
                                                    .append(
                                                            $("<input/>", {
                                                                name: 'check_out_date',
                                                                class: 'form-control',
                                                                placeholder: "Check-out Date",
                                                                value: that.booking.check_out_date,
                                                                style: 'max-width: 125px;'
                                                            }).datepicker({
                                                        dateFormat: 'yy-mm-dd',
                                                        beforeShow: that._customRange
                                                    }).on('change', function () {

                                                        if (new Date(this.value) < new Date($("[name='check_in_date']").val())) {
                                                            alert(l("Check-out-date can't be less than Check-in-date"));
                                                            $("[name='check_out_date']").val($("[name='check_in_date']").val());
                                                            $("[name='check_out_date']").focus();
                                                        }

                                                    })
                                                            )
                                                    .append(
                                                            $("<span/>", {
                                                                class: "input-group-addon",
                                                                style: "padding: 3px;"
                                                            }).append(
                                                            $("<input/>", {
                                                                name: 'number_of_days',
                                                                maxlength: '3',
                                                                style: 'padding: 2px; width: 30px;'
                                                            }).on("change", function () {
                                                        // set number of days as check_out_date - check_in_date
                                                        var cid = $("[name='check_in_date']").val().split(/[-]/);
                                                        var number_of_days = $("[name='number_of_days']").val();
                                                        if (number_of_days == "")
                                                        {
                                                            $("[name='check_out_date']").val("");
                                                        }
                                                        else
                                                        {
                                                            number_of_days = parseInt(number_of_days);
                                                            // Apply each element to the Date function
                                                            var check_in_date = new Date(cid[0], cid[1] - 1, cid[2]);
                                                            var check_out_date = new Date(check_in_date);
                                                            check_out_date.setDate(check_out_date.getDate() + number_of_days);

                                                            var year = check_out_date.getFullYear();
                                                            var month = ("0" + (check_out_date.getMonth() + 1)).slice(-2);
                                                            var day = ("0" + check_out_date.getDate()).slice(-2);
                                                            $("[name='check_out_date']").val(year + "-" + month + "-" + day);
                                                        }
                                                        that._updatePayPeriodDropdown();
                                                    })
                                                            ).append(" days")
                                                            )
                                                    )
                                            )
                                    )

                            .append(
                                    $('<div/>', {
                                        id: 'room-type-list'
                                    })
                                    )
                            );
            var panelBody = that.$modalBody.find('.form-inline');
            panelBody.remove('.group-name-div');
            if (that.selectedGroupType == 'linked_group') {
                panelBody.append(
                        $('<div/>', {
                            class: 'form-group col-sm-12 group-name-div',
                            style: 'margin: 0px 7px 7px 7px;'
                        }).append(
                        $('<label/>', {
                            class: 'col-sm-2 text-right',
                            text: 'Group Name'
                        })
                        )
                        .append(
                                $('<div/>', {
                                    class: 'col-sm-10 text-left'
                                }).append(
                                $('<input/>', {
                                    class: 'group-name form-control',
                                    type: 'text',
                                    value: '',
                                    name: 'group_name'
                                })
                                ).append(
                                $('<span/>', {
                                    class: 'bold',
                                    style: 'padding-left: 20px;',
                                    text: 'Recommended: Give the group a name',
                                })
                                )

                                )
                        );
            }
            block.find("[name='check_in_date'], [name='check_out_date'], [name='number_of_days']")
                    .on('change', function () {
                        that._updateNumberOfDays();
                        that._updateRoomGroupList();
                    });

            // if walk-in, disable check-in date
            if ($("[name='state']").val() === '1' && that.booking.booking_id === undefined) {
                block.find("[name='check_in_date']").val($("#sellingDate").val());
                block.find("[name='check_in_date']").prop("disabled", true);
            }
            
            if (that.booking.source != 2 && that.booking.source != 3 && that.booking.source != 4 && that.booking.source != 8) {
                block.find('.edit-rate-btn').on('click', function(){
                    that._initializeRateModal();
                });
            }
            else
            {
                roomTypeDIV.find('.edit-rate-btn').css('cursor', 'not-allowed')
            }  
            
            
            return block;
        },
        _updateRoomGroupList: function () {
            var that = this;

            if (!$("[name='check_in_date']").val() || !$("[name='check_out_date']").val())
            {
                return;
            }

            var roomTypeList = $('<div/>', {
                class: 'rooms'
            });
            var checkInDate = $('input[name="check_in_date"]').val();
            var checkOutDate = $('input[name="check_out_date"]').val();

            $.getJSON(getBaseURL() + 'booking/get_available_room_types_in_JSON/' + checkInDate + '/' + checkOutDate,
                    function (roomTypes) {

                        if (roomTypes !== '' && roomTypes !== null && roomTypes.length > 0) {
                            roomTypes.forEach(
                                    function (roomType) {
                                        if (roomType.availability > 0) {
                                            var numberOfRoomsSelect = $("<select/>", {
                                                class: 'form-control',
                                                name: 'room_count'

                                            });

                                            for (var i = 0; i <= roomType.availability; i++) {
                                                if (i === 0)
                                                    var text = "no room";
                                                else if (i === 1)
                                                    var text = "1 room";
                                                else
                                                    var text = i + " rooms";
                                                numberOfRoomsSelect.append(
                                                        $("<option/>", {
                                                            value: i,
                                                            text: text
                                                        })
                                                        );
                                            }

                                            var roomGroup = $('<div/>', {
                                                class: 'panel panel-default extra-block capicity-block',
                                                max_adults: roomType.max_adults,
                                                max_children: roomType.max_children
                                            })
                                                    .append(
                                                            $('<div/>', {
                                                                class: 'panel-body room-type'
                                                            })
                                                            .append(
                                                                    $("<div/>", {
                                                                        class: "col-sm-3"
                                                                    })
                                                                    .append("<strong>" + roomType.name + "</strong> (" + roomType.acronym + ")<br/><small>" + roomType.availability + " available</small>")
                                                                    .append(
                                                                            $("<div/>", {
                                                                                class: "form-group"
                                                                            })
                                                                            .append(
                                                                                    $('<select/>', {
                                                                                        name: 'room_type_id',
                                                                                        class: 'form-control',
                                                                                        disabled: true,
                                                                                        style: 'display: none;'
                                                                                    })
                                                                                    .append(
                                                                                            $('<option/>', {
                                                                                                text: roomType.name,
                                                                                                value: roomType.id,
                                                                                                selected: true
                                                                                            })
                                                                                            )
                                                                                    )
                                                                            )
                                                                    )
                                                            .append(
                                                                    $("<div/>", {
                                                                        class: "col-sm-9"
                                                                    })
                                                                    .append("Book ")
                                                                    .append($('<span/>', {style: "color:red;", text: "*"}))
                                                                    .append(
                                                                            $("<div/>", {
                                                                                class: "form-group"
                                                                            })
                                                                            .append(numberOfRoomsSelect)
                                                                            )
                                                                    .append(that.$rateInfo.clone())
                                                                    )
                                                            );
                                            that._updateChargeWithDDL(roomGroup.find(".room-type"));
                                            that._updateRoomDDL(roomGroup);

                                            roomGroup.find("[name='adult_count'], [name='children_count'], [name='rate']")
                                                    .on('change', function () {
                                                        var max_adults = $(this).closest('.capicity-block').attr('max_adults');
                                                        var max_children = $(this).closest('.capicity-block').attr('max_children');
                                                        var adult_count = $(this).closest(".capicity-block").find("[name='adult_count']").val();
                                                        var children_count = $(this).closest(".capicity-block").find("[name='children_count']").val();
                                                        max_adults = Number(max_adults);
                                                        max_children = Number(max_children);
                                                        adult_count = Number(adult_count);
                                                        children_count = Number(children_count);
                                                        var selected_room_type = $(this).closest(".capicity-block").find("[name=room_type_id]").text();
                                                        if (adult_count > max_adults || children_count > max_children) {
                                                            alert(l("Maximum capacity for room ") +
                                                                    selected_room_type +
                                                                    l(" is \nMaximun adults ") + max_adults +
                                                                    l(" \nMaximun children ") + max_children);
                                                            if (adult_count > max_adults)
                                                                $(this).closest(".capicity-block").find("[name='adult_count']").val(max_adults);
                                                            if (children_count > max_children)
                                                                $(this).closest(".capicity-block").find("[name='children_count']").val(max_children);
                                                        }
                                                        that._validateCapacity();
                                                        that._updateRate($(this).parents('.panel-body.room-type'));
                                                    });

                                            roomTypeList.append(roomGroup);
                                        }
                                    }
                            );

                            //roomTypeList.append("10 rooms selected. Set cut-off date to: <select class='form-control'><option>never</option></select>");    

                        }
                    }
            );

            $("#room-type-list").html(roomTypeList);

        },
        _getExtraPanel: function () {
            var that = this;

            var extras = this.booking.extras;

            if (extras === undefined)
                return '';

            if (extras.length === 0)
                return '';

            // prepare block template
            var block = $('<div/>', {
                class: 'panel panel-default extra-block'
            })
                    .append(
                            $('<div/>', {
                                class: 'panel-body'
                            })
                            .append(
                                    $('<label/>', {
                                        class: 'col-sm-2 text-right',
                                        html: 'Extras'
                                    })
                                    )
                            );

            var extraContainer = $('<div/>', {
                class: 'col-sm-10',
                id: 'extra-container'
            });

            extras.forEach(function (bookingExtra) {
                extraContainer.append(that._getBookingExtraDiv(bookingExtra));
            });

            block.find(".panel-body").append(extraContainer);

            return block
        },
        _getBookingExtraDiv: function (bookingExtra) {

            var that = this;

            var extraDiv = $("<div/>", {
                class: 'row extra',
                id: bookingExtra.booking_extra_id
            })
            extraDiv.append(bookingExtra.quantity)
                    .append(" " + bookingExtra.extra_name);
            console.log('get'+bookingExtra.charging_scheme);
            if(bookingExtra.charging_scheme != 'on_start_date')
            {
                extraDiv.append(" between ")
                        .append(bookingExtra.start_date)
                        .append(" and ")
                        .append(bookingExtra.end_date);
            }
            else{
                extraDiv.append(' on date ')
                        .append(bookingExtra.start_date);
            }
            extraDiv.append(" at rate: ")
                .append(bookingExtra.rate)
                .append(
                        $("<button/>", {
                                class: 'btn btn-default pull-right btn-xs',
                                type: 'button',
                                html: "<span class='glyphicon glyphicon-remove' aria-hidden='true'></span>"
                            }).on('click', function () {

                        var extra = $(this).parent();
                        that._deleteExtra(extra.attr('id'));
                    })
                            )
                    .append(
                            $("<button/>", {
                                class: 'booking-extra btn btn-default pull-right btn-xs',
                                id: bookingExtra.booking_extra_id,
                                type: 'button',
                                html: "<span class='glyphicon glyphicon-pencil' aria-hidden='true'></span> Edit"
                            })
                            .on('click', function () {
                                that._editBookingExtra($(this).parent().attr("id"));

                            })
                            )
            return extraDiv;
        },
        _editBookingExtra: function (bookingExtraID) {
            var that = this;

            that._initializeInnerModal();

            $.ajax({
                type: "POST",
                url: getBaseURL() + "extra/get_booking_extra_AJAX",
                data: {
                    booking_extra_id: bookingExtraID
                },
                dataType: "json",
                success: function (data) {
                    that._populateExtraModal(data);
                }
            });
        },
        _getBookingTypes: function (state) {

            var that = this;

            var select = $("<select/>", {
                class: 'form-control',
                name: 'state'
            })
            // new booking
            var options = [
                {value: 7, name: 'Unconfirmed Reservation'},
                {value: 0, name: 'Reservation'},
                {value: 1, name: 'Checked-in'},
                {value: 2, name: 'Checked-out'},
                {value: 4, name: 'Cancelled (Hide)'},
                {value: 5, name: 'No show'}
            ];

            if (state === undefined) {

                // if user is dragging mouse
                if (that.booking.check_in_date == $("#sellingDate").val() ||
                        that.booking.current_room_id === undefined) {
                    options[2].name = 'Walk-in';
                }
                
                options.push({value: 3, name: 'Out of Order'});
                state = 0;
            } else if (state == '6') {
                // deleted
                var options = [
                    {value: 6, name: 'Deleted'}
                ];
            } else if (state == '3') {
                // out of order
                var options = [
                    {value: 3, name: 'Out of Order'}
                ];


            }

            options.forEach(function (data) {
                var option = $('<option/>', {
                    value: data.value,
                    text: data.name
                });

                if (data.value == state) {
                    option.prop('selected', true);
                }

                select.append(option);
            });

            return select.on('change', function () {

                var newState = $(this).val();
                var roomDiv = that.$modalBody.find("[name='room_id']");
                var roomCount = that.$modalBody.find("[name='room_count']");
                var rateInfo = that.$modalBody.find(".rate-info");
                var customerInfo = that.$modalBody.find(".customer-info");

                that.booking.state = newState;
                that._updateColorSelector(that._getDefaultColor());

                switch (newState) {
                    case '0': // reservation
                        // make sure room information is already visible (this means check-in & check-out dates are selected)
                        if (roomDiv.is(":visible") || roomCount.is(":visible"))
                            rateInfo.fadeIn();
                        customerInfo.fadeIn();
                        break;
                    case '1': // walk-in
                        // make sure room information is already visible (this means check-in & check-out dates are selected)
                        if (roomDiv.is(":visible") || roomCount.is(":visible"))
                            rateInfo.fadeIn();
                        customerInfo.fadeIn();
                        break;

                    case '3': // out-of-order
                        rateInfo.fadeOut();
                        customerInfo.fadeOut();
                        break;
                }
            });
        },
        _updateModalHeader: function () {
            var that = this;

            var state = this.booking.state;

            var modalHeader = $("#booking-modal .modal-header");

            modalHeader.html(
                    $("<button/>", {
                        class: "close",
                        "data-dismiss": "modal",
                        "aria-label": "Close"
                    }).append(
                    $("<span/>", {
                        "aria-hidden": "true",
                        html: "&times;"
                    })
                    )
                    );

            if (state !== undefined)
            {
                // If the booking is not out of order
                if (state !== '3') {
                    modalHeader.prepend(
                            $("<span/>", {
                                class: "h4",
                                html: "Edit Room (ID: " + this.booking.booking_id + ") Balance: <a href='" + getBaseURL() + "invoice/show_invoice/" + this.booking.booking_id + "'>" + number_format(this.booking.balance, 2, ".", "") + "</a>"
                            })
                            )
                }
                else {
                    modalHeader.prepend(
                            $("<span/>", {
                                class: "h4",
                                html: "Edit Out-of-order"
                            })
                            )
                }
            }
            else
            {
                modalHeader.prepend(
                        $("<span/>", {
                            class: "h4",
                            html: "Create new booking"
                        })
                        );
            }
            if (this.groupInfo != null) {
                modalHeader.append(
                        $("<span/>", {
                            class: "h4",
                            style: "padding-left: 20px",
                            html: "Group Id: " + this.groupInfo.group_id
                        }).append(
                        $("<span/>", {
                            class: "h4",
                            style: "padding-left: 20px",
                            html: "Group Name: " + this.groupInfo.group_name
                        })
                        )
                        );

            }
            this._updateColorSelector(this._getDefaultColor());

            return modalHeader;
        },
        _updateModalFooter: function () {

            var that = this;
            var state = this.booking.state;
            var modalFooter = $("#booking-modal").find(".modal-footer").html("");

            // reservation
            if (state == 0) {
                modalFooter
                        .append(
                                $("<button/>", {
                                    type: "button",
                                    class: "btn btn-success",
                                    id: "button-check-in",
                                    text: "Check in"
                                }).on('click', function () {
                            that.button = $(this);
                            that.button.prop('disabled', true);

                            var bookingData = that._fetchBookingData();
                            bookingData.booking.state = 1;
                            that._updateBooking(bookingData, "Successfully checked-in");
                            that._makeRoomDirty(bookingData['rooms'][0]['room_id']);
                            //that._populateEditBookingModal();
                        })
                                );
            }

            // checked-in
            if (state == 1) {
                modalFooter
                        .append(
                                $("<button/>", {
                                    type: "button",
                                    class: "btn btn-warning",
                                    id: "button-check-out",
                                    text: "Check out"
                                }).on('click', function () {
                            that.button = $(this);
                            that.button.prop('disabled', true);

                            var bookingData = that._fetchBookingData();
                            bookingData.booking.state = 2;
                            that._updateBooking(bookingData, "Successfully checked-out");
                        })
                                )
            }

            // out of order
            if (state == 3) {
                modalFooter
                        .append(
                                $("<button/>", {
                                    type: "button",
                                    class: "btn btn-danger",
                                    id: "button-delete-out-of-order",
                                    text: "Delete"
                                })
                                .on('click', function () {
                                    that.button = $(this);
                                    that.button.prop('disabled', true);
                                    that._deleteBooking();
                                }
                                )
                                )
            }


            // Unconfirmed Reservation
            if (state == 7) {
                modalFooter
                        .append(
                                $("<button/>", {
                                    type: "button",
                                    class: "btn btn-info",
                                    id: "button-confirm-reservation",
                                    text: "Confirm Reservation"
                                })
                                .on('click', function () {
                                    that.button = $(this);
                                    that.button.prop('disabled', true);

                                    var bookingData = that._fetchBookingData();
                                    that.booking.state = '0';
                                    bookingData.booking.state = that.booking.state;

                                    that._updateBooking(bookingData);
                                })
                                )
            }

            if (state === undefined) {
                modalFooter.append(
                        $("<button/>", {
                            type: "button",
                            class: "btn btn-success booking-create",
                            text: "Create"
                        }).on('click', function () {
                    that.button = $(this);
                    that.button.prop('disabled', true);
                    if (that.isCreatingCustomer) {
                        that.deferredCreatingCustomer = $.Deferred();
                        $.when(that.deferredCreatingCustomer)
                                .done(function () {
                                    that._createBooking();
                                    that.button.prop('disabled', false);
                                    that.deferredCreatingCustomer = null;
                                    that.isCreatingCustomer = false;
                                })
                                .fail(function () {
                                    that.button.prop('disabled', false);
                                    that.deferredCreatingCustomer = null;
                                    that.isCreatingCustomer = false;
                                });
                    }
                    else
                    {
                        that._createBooking();
                    }

                })
                        );
            }
            else {
                modalFooter.append(
                        $("<button/>", {
                            type: "button",
                            class: "btn btn-default ",
                            text: "Save"
                        }).on('click', function () {
                    that.button = $(this);
                    //that.button.prop('disabled', true);
                    that._showAlert("Saved");

                    if (that.saveAllGroupDate == true || that.saveAllGroupDate == false) {
                        that._getAllGroupRoomBookingIds();
                    } else {
                        var bookingData = that._fetchBookingData();
                        that._updateBooking(bookingData);
                    }
                    that.saveAllGroupDate = null;
                })
                        );
            }

            return modalFooter.append(
                    $("<button/>", {
                        type: "button",
                        class: "btn btn-default",
                        "data-dismiss": "modal",
                        text: "Close"
                    })
                    );
        },
        _fetchBookingData: function () {
            var that = this;
            var payingCustomer = '';
            var stayingCustomers = [];
            var customerOrder = 0;
            var isGroupBooking = null;
            var groupName = '';
           
            if (that.selectedGroupType == 'linked_group') {
                isGroupBooking = true;
                groupName = this.$modalBody.find('input[name="group_name"]').val();
            }
            $(".tokenfield div.token").each(function () {

                var customerOptions = {
                    customer_id: $(this).attr("id") ? $(this).attr("id") : null,
                    customer_name: $(this).find(".token-label").text()
                };

                // customer name is passed, in case it's a new customer
                // (without customer id) that needs to be created
                if (customerOrder === 0) {
                    payingCustomer = customerOptions
                }
                else {
                    stayingCustomers.push(customerOptions);
                }
                customerOrder++;
            });

            var rooms = [];
            $('.room-type').each(function () {

                var useRatePlan = undefined;
                var ratePlanID = undefined;
                var chargeTypeID = undefined;

                if ($(this).find(".charge-with option:selected").hasClass('rate-plan') === true) {
                    // if user is using rate_plan, assign rate_plan_id
                    useRatePlan = 1;
                    ratePlanID = $(this).find(".charge-with").val();
                }
                else {
                    // if booking is not using rate plan, assign charge_type_id
                    useRatePlan = 0;
                    chargeTypeID = $(this).find(".charge-with").val();
                }

                rooms.push({
                    check_in_date: $("[name='check_in_date']").val(),
                    check_out_date: $("[name='check_out_date']").val(),
                    // for single booking
                    room_id: $(this).find("[name='room_id']").val(),
                    // for group booking
                    room_type_id: $(this).find("[name='room_type_id']").val(),
                    room_count: $(this).find("[name='room_count']").val(),
                    rate: $(this).find("[name='rate']").val() ? $(this).find("[name='rate']").val() : 0,
                    use_rate_plan: useRatePlan,
                    rate_plan_id: ratePlanID,
                    charge_type_id: chargeTypeID,
                    pay_period: $(this).find("[name='pay_period']").val()
                });

            });

            var bookingData = {
                booking: {
                    state: this.$modalBody.find("[name='state'] option:selected").val(),
                    color: this.$modalBody.find("[name='color']").val(),
                    rate: this.$modalBody.find("[name='rate']").val() ? this.$modalBody.find("[name='rate']").val() : 0,
                    pay_period: this.$modalBody.find("[name='pay_period']").val(),
                    adult_count: this.$modalBody.find("[name='adult_count']").val(),
                    children_count: this.$modalBody.find("[name='children_count']").val(),
                    booking_notes: this.$modalBody.find("[name='booking_notes']").val(),
                    source: this.$modalBody.find("[name='source']").val()
                },
                rooms: rooms,
                customers: {
                    paying_customer: payingCustomer,
                    staying_customers: stayingCustomers
                },
                isGroupBooking: isGroupBooking,
                groupName: groupName
            };
            return bookingData;
        },
        _showAlert: function (msg) {
            $(".alert-booking-created").text(msg).show(0, function () {
                $(this).stop().fadeOut(3000);
            })


        },
        _getColorPicker: function () {
            var select = $("<select/>", {
                class: "form-control",
                name: 'color'
            });

            this.customColors.forEach(function (color) {
                select.append(
                        $("<option/>", {
                            value: color,
                            "data-color": "#" + color
                        })
                        );
            });

            return select;
        },
        _createBooking: function () {
            var that = this;
            var existGroupId = null;
            var data = this._fetchBookingData();

            if (that.groupInfo != null)
                existGroupId = that.groupInfo.group_id

            $.ajax({
                type: "POST",
                url: getBaseURL() + "booking/create_booking_AJAX",
                data: {
                    data: data,
                    existing_group_id: existGroupId
                },
                dataType: "json",
                success: function (response) {

                    // if booking(s) are walk-in's, make the rooms dirty
                    if (data.booking.state === '1') {
                        var rooms = data.rooms;

                        rooms.forEach(function (room) {
                            that._makeRoomDirty(room.room_id);
                        });

                    }
                    ;

                    // error handling happens here
                    if (response.errors !== undefined) {
                        var errorMsg = "";
                        response.errors.forEach(function (error) {
                            errorMsg += error + "\n";
                        });
                        alert(errorMsg);
                        that.button.prop('disabled', false);
                    }
                    else {
                        //
                        // open "edit booking modal" if single booking is created
                        // if we just created multiple bookings, then don't open "edit booking modal"
                        if (response.length === 1) {
                            that.booking = data.booking;
                            that.booking.booking_id = response[0].booking_id;
                            that.booking.balance = response[0].balance;
                            
                            //that._initializeBookingModal();

                            if (that.options.isAddGroupBooking != null) {
                                that.booking.check_in_date = that.options.checkInDate;
                                that.booking.check_out_date = that.options.checkOutDate;
                                that.booking.staying_customers = that.options.stayingCustomers;
                            }
                            else
                            {
                                that.booking.check_in_date = data.rooms[0].check_in_date;
                                that.booking.check_out_date = data.rooms[0].check_out_date;
                            }
                           
                            that._showAlert("Successfully created");

                            that._updateModalContent();
                            that._getLinkedGroupBookingRoomList();
                        }
                        else
                        {
                            that._closeBookingModal();
                        }

                    }
                    innGrid.updateAvailabilities(
                            data.rooms[0].check_in_date,
                            data.rooms[0].check_out_date
                            );

                    mixpanel.track("Booking created");

                }
            });
        },
        _closeBookingModal: function () {
            this.closeModal.resolve();
        },
        _makeRoomDirty: function (roomID) {
            var that = this;

            $.ajax({
                type: "POST",
                url: getBaseURL() + "room/update_room_status",
                data: {
                    room_id: roomID,
                    room_status: 'Dirty'
                },
                dataType: "json",
                success: function (data) {
                    innGrid.reloadCalendar();
                }
            });
        },
        _updateBooking: function (data, msg) {
            var that = this;

            // merge in the changes to booking
            $.each(data.booking, function (key, value) {
                that.booking[key] = data.booking[key];
            });

            // update availabilities of the dates prior to update
            innGrid.updateAvailabilities(
                    this.booking.check_in_date,
                    this.booking.check_out_date
                    );

            $.ajax({
                type: "POST",
                url: getBaseURL() + "booking/update_booking_AJAX",
                data: {
                    booking_id: this.booking.booking_id,
                    data: data
                },
                dataType: "json",
                success: function (response) {
                    if (response.errors !== undefined) {
                        var errorMsg = "";
                        response.errors.forEach(function (error) {
                            errorMsg += error + "\n";
                        });
                        alert(errorMsg);
                        that.button.prop('disabled', false);
                    }
                    else {

                        //that._closeBookingModal();
                        //that._init();
                        that._showAlert(msg);
                        //that._populateEditBookingModal();
                        that._updateModalContent();
                        that._getLinkedGroupBookingRoomList();
                        // update availabilities of the dates after the update

                        innGrid.updateAvailabilities(
                                data.rooms[0].check_in_date,
                                data.rooms[0].check_out_date
                                );
                        if (data.booking.state != 4 && that.groupInfo != null) {
                            that.disableRoomBlock = '';
                            that.pointerNone = '';
                            that.$modalBody.find('.panel-booking').attr('style', that.disableRoomBlock);
                            that.$modalBody.find('.panel-booking .form-inline').attr('style', that.pointerNone);

                        }
                    }
                }
            });
        },
        _updateGroupBooking: function (data, msg, bookingId) {
            var that = this;
            if (data.booking.update_date == true) {
                var cancelledTrue = '';
            } else {
                var cancelledTrue = 'cancelled';
            }
            $.ajax({
                type: "POST",
                url: getBaseURL() + "booking/update_booking_AJAX",
                data: {
                    booking_id: bookingId,
                    data: data,
                    group_booking_cancellation: cancelledTrue
                },
                dataType: "json",
                success: function (response) {
                    if (response.errors !== undefined) {
                        var errorMsg = "";
                        response.errors.forEach(function (error) {
                            errorMsg += error + "\n";
                        });
                        alert(errorMsg);
                    }
                    else {

                        that._showAlert(msg);

                        innGrid.updateAvailabilities(
                                that.booking.check_in_date,
                                that.booking.check_out_date
                                );

                        that._getLinkedGroupBookingRoomList();
                        if (bookingId == that.booking.booking_id && cancelledTrue != '') {
                            that.$modalBody.find("[name='state']").val('4');
                            that.disableRoomBlock = 'cursor:not-allowed;background:#f2f2f2'; // disable room block section that is cancelled
                            that.pointerNone = 'pointer-events:none';
                            that.$modalBody.find('.panel-booking').attr('style', that.disableRoomBlock);
                            that.$modalBody.find('.panel-booking .form-inline').attr('style', that.pointerNone);
                        }

                    }
                }
            });
        },
        _createDuplicate: function () {
            var answer = confirm("Make a duplicate (this action may cause over booking!)");
            if (answer == true) {
                this._createBooking();
            }
        },
        _deleteBooking: function (isGroupBookingDel, groupBookingId ) {
            var that = this;
            var answer = confirm("Are you sure you want to delete this booking?");
            if (groupBookingId != null) {
                var bookingId = groupBookingId;
            } else {
                var bookingId = that.booking.booking_id;
            }

            if (answer == true) {
                this.button = $(this);
                this.button.prop('disabled', true);

                $.ajax({
                    type: "POST",
                    url: getBaseURL() + "booking/delete_booking_AJAX",
                    data: {
                        booking_id: bookingId
                    },
                    success: function (data) {
                        if (data == "") // if successful, delete_booking_AJAX returns empty page
                        {
                            innGrid.updateAvailabilities(
                                    that.booking.check_in_date,
                                    that.booking.check_out_date
                                    );

                            if (isGroupBookingDel == true) {
                                that._getLinkedGroupBookingRoomList();
                            }
                            else {
                                that._closeBookingModal();
                            }
                        }
                        else
                        {
                            alert(l("You do not have permission to delete booking"));
                        }

                    }
                });

            }
        },
        _deleteExtra: function (bookingExtraID) {
            var that = this;
            var answer = confirm("Are you sure you want to delete this extra?");
            if (answer == true) {

                $.ajax({
                    type: "POST",
                    url: getBaseURL() + "booking/delete_booking_extra_AJAX",
                    data: {
                        booking_extra_id: bookingExtraID
                    },
                    dataType: "json",
                    success: function (data) {
                        $(".extra#" + bookingExtraID).remove();
                        
                        $.each(that.booking.extras, function(i, value){ // unset booking extra id that is deleted
                            if (value.booking_extra_id == bookingExtraID){
                                that.booking.extras.splice(i, 1);
                            }
                        });
                        
                        // delete panel if there's no extra
                        if ($(".extra").length === 0) {
                            $(".extra-block").remove();
                        }

                    }
                });
            }
        },
        _convertToCurrency: function (num) {
            num = (isNaN(num) || num === '' || num === null) ? 0.00 : num;
            return parseFloat(num).toFixed(2);
        },
        _updateBookingType: function () {
            var that = this;

            var state = this.booking.state;

            if (state === undefined)
            {
                $("[name='state']").val(0); // assume new booking is reservation
                return;
            }
            else
            {
                $("[name='state']").val(state); // set booking type drop down
            }

            var $actions = {};

            switch (parseInt(state)) {
                case 0: // reservation
                case 1: // inhouse
                case 2: // check-out
                case 7: // unconfirmed reservation
                    $actions = [
                        this.$allActions.showInvoice,
                        this.$allActions.sendConfirmationEmail,
                        this.$allActions.editHousekeepingNotes
                    ];

                    //allow users to add extras only if the company has the extras set up
                    if (that.extras !== null)
                        $actions.push(this.$allActions.addExtra);

                    $actions.push(this.$allActions.showHistory);
                    if (state != '2')
                        $actions.push(this.$allActions.createDuplicate);
                    $actions.push(this.$allActions.divider);
                    $actions.push(this.$allActions.deleteBooking);


                    break;
                case 3: // out of order
                case 4: // cancelled
                    $actions = [
                        this.$allActions.showInvoice,
                        this.$allActions.editHousekeepingNotes,
                        this.$allActions.showHistory,
                        this.$allActions.divider,
                        this.$allActions.deleteBooking
                    ];
                    break;
                case 5: // no show
                    $actions = [
                        this.$allActions.showHistory,
                        this.$allActions.divider,
                        this.$allActions.deleteBooking
                    ];
                    break;
                case 6: // deleted
                    $actions = [
                        this.$allActions.showHistory
                    ];
                    break;

            }

            var actionsUL = $("<ul/>", {
                class: "dropdown-menu pull-right other-actions",
                role: "menu"
            });

            $.each($actions, function (name, action) {
                actionsUL.append(action);
            });

            var bookingButtons = this.$modalBody.find(".booking-buttons");

            bookingButtons.html(
                    $("<div/>", {
                        class: "btn-group pull-right",
                        role: "group"
                    })
                    .append(
                            $("<a/>", {
                                class: "btn btn-default",
                                href: getBaseURL() + "invoice/show_invoice/" + that.booking.booking_id,
                                text: "Open Invoice"
                            })
                            )
                    .append(
                            $("<a/>", {
                                class: "btn btn-default",
                                href: getBaseURL() + "booking/print_registration_card/" + this.booking.booking_id,
                                text: "Print Reg Card",
                                target: "_blank"
                            })
                            )
                    .append(
                            $("<button/>", {
                                type: "button",
                                class: "btn btn-default dropdown-toggle",
                                "data-toggle": "dropdown",
                                "aria-expanded": false,
                                text: "More "
                            })
                            .append(
                                    $("<span/>", {
                                        class: "caret"
                                    })
                                    )
                            )
                    .append(actionsUL)
                    );

        },
        _updateColorSelector: function (color) {

            $("[name='color']").colorselector("setBackgroundColor", color);
            $("[name='color']").colorselector("setColor", "#" + color);

            $(".dropdown-colorselector").find(".use-default-button-div").html("");
            $(".dropdown-colorselector").find(".dropdown-menu").append(
                    $("<div/>", {
                        class: 'use-default-button-div'
                    }).append(
                    $("<button/>", {
                        class: "use-default-color-button color-btn form-control",
                        text: "Use Default",
                        "data-color": 'transparent',
                        "data-value": 'transparent',
                        "style": "background-color: " + this.defaultColors[this.booking.state]
                    }).on("click", function () {
                $("[name='color']").val("");
                $("[name='color']").colorselector("setBackgroundColor", this.defaultColors[this.booking.state]);
            })
                    )
                    );
        },
        _getDefaultColor: function () {

            if (this.booking.color)
            {
                color = this.booking.color;
            }
            else if (this.booking.state)
            {
                color = this.defaultColors[this.booking.state];
            }
            else
            {
                var color = this.defaultColors[0]; // assume it's a reservation
            }

            return color;

        },
        _tokenizeCustomerField: function (customers) {
            var that = this;
            // Customers Token

            var tokenField = this.$modalBody.find("[name='customers']");
            var customer_selected_from_autocomplete = false;

            tokenField.tokenfield({
                createTokensOnBlur: true,
                autocomplete: {
                    source: function (request, response) {

                        $.ajax({
                            type: "GET",
                            url: getBaseURL() + "customer/get_customers_AJAX/",
                            data: {
                                query: request.term
                            },
                            dataType: "json",
                            success: function (data) {
                                response(
                                        $.map(data, function (item) {
                                            return {
                                                value: item.customer_name,
                                                id: item.customer_id,
                                                type: item.customer_type,
                                                phone: item.phone,
                                                email: item.email,
                                                city: item.city,
                                                country: item.country
                                            }
                                        })
                                        );
                            }
                        });
                    },
                    select: function (e, ui) {
                        customer_selected_from_autocomplete = true;
                        if (that.deferredCreatingCustomer) {
                            that.deferredCreatingCustomer.resolve();
                        }
                        that.isCreatingCustomer = false;
                    },
                    maxShowItems: 5,
                    minLength: 3,
                    delay: 250
                },
                tokens: customers
            }) // -- tokenfield
                    .on('tokenfield:createdtoken', function (e) {
                        // check if there's a customer with a same name already in the system
                        var token = $(".tokenfield .token:last");
                        var name = token.find(".token-label").text();

                        that.isCreatingCustomer = true;
                        $.ajax({
                            type: "GET",
                            url: getBaseURL() + "customer/get_customer_by_name",
                            data: {
                                name: name
                            },
                            dataType: "json",
                            success: function (existing_customer) {
                                if (!customer_selected_from_autocomplete)
                                {
                                    that.deferredExistingCustomerConfirmation = $.Deferred();
                                    if (existing_customer)
                                    {
                                        that._initializeInnerModal();
                                        that._populateExistingCustomerConfirmationModal(token, existing_customer);
                                    }
                                    else
                                    {
                                        that.deferredExistingCustomerConfirmation.resolve();
                                    }
                                }

                                $.when(that.deferredExistingCustomerConfirmation).done(function () {
                                    // open customerModal if bookingModal is still open
                                    if (($("#booking-modal").data('bs.modal') || {}).isShown &&
                                            token.attr("id") == undefined) // don't open customerModal when selecting autocomplete
                                    {
                                        $(document).openCustomerModal(
                                                {
                                                    customer_id: token.attr("id"),
                                                    customer_name: name,
                                                    onload: function () {
                                                        that.$modalBody.find("[name='check_in_date'], [name='check_out_date']").datepicker("hide");
                                                        $("select").blur();
                                                        tokenField.tokenfield("disable");
                                                    },
                                                    onclose: function (e) {
                                                        if (typeof token.attr("id") !== "undefined") {
                                                            if (that.deferredCreatingCustomer) {
                                                                that.deferredCreatingCustomer.resolve();
                                                            }
                                                        }
                                                        else
                                                        {
                                                            if (that.deferredCreatingCustomer) {
                                                                that.deferredCreatingCustomer.reject();
                                                            }
                                                        }
                                                        that.isCreatingCustomer = false;
                                                        tokenField.tokenfield("enable");
                                                    }
                                                }
                                        );
                                    }
                                    else if (typeof token.attr("id") !== "undefined")
                                    {
                                        if (that.deferredCreatingCustomer) {
                                            that.deferredCreatingCustomer.resolve();
                                        }
                                        that.isCreatingCustomer = false;
                                    }
                                    else
                                    {
                                        if (that.deferredCreatingCustomer) {
                                            that.deferredCreatingCustomer.reject();
                                        }
                                        that.isCreatingCustomer = false;
                                    }
                                });

                                customer_selected_from_autocomplete = false;
                            }
                        });


                    });

            //custom autocomplete template
            that.$modalBody.find(".ui-autocomplete-input")
                    .data("ui-autocomplete")._renderItem = function (ul, item) {

                return $("<li>")
                        .data("ui-autocomplete-item", item)
                        .append(
                                $("<a/>").append(
                                $("<div/>", {
                                    text: item.value
                                }).append(
                                $("<div/>", {
                                    class: "small",
                                    text: ((item.email) ? item.email : '') + ((item.phone) ? " - " + item.phone : '') + ((item.city) ? " - " + item.city : '') + ((item.country) ? " - " + item.country : '')
                                })
                                )
                                )
                                )
                        .appendTo(ul);
            };

            that.$modalBody.find(".tokenfield").sortable();
        },
        //Populate room drop down list based on checkin, checkout, and roomtype
        _updateRoomTypeDDL: function (roomTypeDIV) {
            var that = this;

            if (this.booking.current_room_type_id == null)
            {
                this.booking.current_room_type_id = this.$modalBody.find("[name='room_type_id']").val();
            }

            if (roomTypeDIV == undefined)
            {
                var roomTypeDIV = this.$modalBody.find(".room-type");
                roomTypeDIV.attr("id", this.booking.current_room_type_id);
            }

            if (
                    that.$modalBody.find("[name='check_in_date']").val() === '' ||
                    that.$modalBody.find("[name='check_out_date']").val() === ''
                    )
            {
                return;
            }

            var checkInDate = that.$modalBody.find('input[name="check_in_date"]').val();
            var checkOutDate = that.$modalBody.find('input[name="check_out_date"]').val();

            var roomTypeDDL = $("<select/>", {
                title: 'Room Type',
                name: 'room_type_id',
                class: 'form-control',
                style: 'max-width: 320px;'
            }).on('change', function () {
                $('select[name=children_count]').val(0);
                $('select[name=adult_count]').val(1);
                that._updateAccommodationDDL(roomTypeDIV);
                that._updateRoomDDL(roomTypeDIV);
                that._updateChargeWithDDL(roomTypeDIV);
            });

            $.getJSON(getBaseURL() + 'booking/get_available_room_types_in_JSON/' + checkInDate + '/' + checkOutDate,
                    function (data) {
                        if (data !== '' && data !== null && data.length > 0) {
                            for (var i in data) {
                                var option = $("<option/>", {
                                    value: data[i].id,
                                    text: data[i].name + " (" + data[i].availability + ")",
                                    'data-max_adults': data[i].max_adults,
                                    'data-max_children': data[i].max_children
                                });

                                if (data[i].availability < 1) {
                                    option.prop("disabled", true);
                                }

                                roomTypeDDL.append(option);
                            }
                        }

                        that.$modalBody.find(".room-type-ddl-span").html(
                                $('<div/>', {
                                    class: 'form-group'
                                })
                                .append(roomTypeDDL)
                                ).prepend(" in ");

                        // if current room type is already set (opening an existing booking), select that room type
                        if (that.booking.current_room_type_id)
                            that.$modalBody.find("[name='room_type_id']").val(that.booking.current_room_type_id);
                        that._updateAccommodationDDL(roomTypeDIV);
                        that._updateRoomDDL(roomTypeDIV);
                        that._updateChargeWithDDL(roomTypeDIV);
                    }
            );
        },
        _updateAccommodationDDL: function (roomTypeDIV) {
            var max_adult = roomTypeDIV.find('select[name="room_type_id"] option:selected').attr('data-max_adults');
            var max_child = roomTypeDIV.find('select[name="room_type_id"] option:selected').attr('data-max_children');
            roomTypeDIV.find('select[name=adult_count]').find('option').each(function () {
                if (Number($(this).val()) > max_adult)
                {
                    $(this).prop('disabled', true).hide();
                }
                else
                {
                    $(this).prop('disabled', false).show();
                }
            });
            roomTypeDIV.find('select[name=children_count]').find('option').each(function () {
                if (Number($(this).val()) > max_child)
                {
                    $(this).prop('disabled', true).hide();
                }
                else
                {
                    $(this).prop('disabled', false).show();
                }
            });
        },
        //Populate room drop down list based on checkin, checkout, and roomtype
        _updateRoomDDL: function (roomTypeDIV) {

            var that = this;

            var checkInDate = this.$modalBody.find('input[name="check_in_date"]').val();
            var checkOutDate = this.$modalBody.find('input[name="check_out_date"]').val();
            var currentSellingDate = $("#sellingDate").val();
            var roomTypeID = roomTypeDIV.find("[name='room_type_id'] option:selected").val();

            var select = $("<select/>", {
                title: 'Room(s)',
                name: 'room_id',
                class: 'form-control room',
                style: 'max-width: 320px;'
            });

            $.ajax({
                type: "POST",
                url: getBaseURL() + 'booking/get_available_rooms_in_AJAX/',
                data: {
                    check_in_date: checkInDate,
                    check_out_date: checkOutDate,
                    room_type_id: roomTypeID,
                    booking_id: that.booking.booking_id
                },
                dataType: "json",
                success: function (data) {

                    if (data !== '' && data !== null && data.length > 0) {
                        // remember the room type of currently viewing list of rooms.
                        // this prevents room type going resetting to the first one in the list whenever checkin/checkout dates change
                        for (var i in data) {
                            var option = $("<option/>", {
                                value: data[i].room_id,
                                text: data[i].room_name + ' (' + data[i].status + ')'
                            });

                            //Keep the same room selected if it is still on the list                    
                            if (that.booking.current_room_id === data[i].room_id) {
                                option.prop("selected", true);
                            }
                            select.append(option);
                        }
                    }

                    that.deferredRoomDDL.resolve();

                    roomTypeDIV.find(".room-ddl-span").html(
                            $('<span/>', {
                                class: 'form-group'
                            })
                            .prepend("in room ")
                            .append($('<span/>', {style: "color:red;", text: "* "}))
                            .append(select)
                            );

                    // don't show rate-info for out-of-orders
                    if (that.$modalBody.find("[name='state']").val() !== '3')
                        that.$modalBody.find(".rate-info").fadeIn();
                }
            });
        },
        _validateCapacity: function () {
            var adult_count = this.$modalBody.find('[name="adult_count"]').val();
            var children_count = this.$modalBody.find('[name="children_count"]').val();
            var $selected_room_type = this.$modalBody.find('[name="room_type_id"] option:selected');
            var max_adults = $selected_room_type.data('max_adults');
            var max_children = $selected_room_type.data('max_children');
            if (adult_count > max_adults || children_count > max_children) {
                alert(l("Maximum capacity for room ") +
                        $selected_room_type.text() +
                        l(" is \nMaximun adults ") + max_adults +
                        l(" \nMaximun children ") + max_children);
                if (adult_count > max_adults)
                    this.$modalBody.find('[name="adult_count"]').val(max_adults);
                if (children_count > max_children)
                    this.$modalBody.find('[name="children_count"]').val(max_children);
            }
        },
        // returns array of [default_rate, default_rate_info]
        _updateRate: function (roomTypeDIV) {
            var that = this;
            if (roomTypeDIV.find(".charge-with option:selected").hasClass('rate-plan') === false) {
                roomTypeDIV.find("[name='rate']").attr("disabled", false);
                roomTypeDIV.find("[name='pay_period']").attr("disabled", false);
                var rate = roomTypeDIV.find("input[name='rate']").val();

                $.post(getBaseURL() + "rate_plan/get_tax_amount_from_room_charge_JSON/",
                        {
                            charge_type_id: roomTypeDIV.find(".charge-with").val()
                        },
                function (tax) {
                    var taxedRate = rate * (1 + parseFloat(tax.percentage)) + parseFloat(tax.flat_rate);
                    that.rateWithTax = taxedRate; // set real taxed rate 
                    
                    //taxedRate = Math.round(taxedRate * 100) / 100;

                    var rateIncludingTaxDiv = roomTypeDIV.find('.rate-including-tax');
                    rateIncludingTaxDiv.text("(with tax: " + taxedRate.toFixed(2) + ")");

                    if (tax.percentage != 0 || (tax.flat_rate != 0 && rate != ''))
                    {
                        rateIncludingTaxDiv.removeClass("hidden");
                    }
                    else
                    {
                        rateIncludingTaxDiv.addClass("hidden");
                    }
                   
                    that._displayRateInfo();
                }, 'json'
                        );

            }
            else if (
                    roomTypeDIV.find('[name="adult_count"]').val() != "" &&
                    roomTypeDIV.find('[name="children_count"]').val() != "" &&
                    roomTypeDIV.find('[name="check_in_date"]').val() != "" &&
                    roomTypeDIV.find('[name="check_out_date"]').val() != ""
                    ) {
                roomTypeDIV.find("[name='rate']").attr("disabled", true);
                roomTypeDIV.find("[name='pay_period']").attr("disabled", true).val(0);
                
                $.post(getBaseURL() + "rate_plan/get_rate_array_JSON", {
                    date_start: $('[name="check_in_date"]').val(),
                    date_end: $('[name="check_out_date"]').val(),
                    rate_plan_id: roomTypeDIV.find('.charge-with').val(),
                    adult_count: roomTypeDIV.find('[name="adult_count"]').val(),
                    children_count: roomTypeDIV.find('[name="children_count"]').val()
                }, function (data) {
                    if (data[0] !== undefined) {
                        var rate = data[0].rate;
                        roomTypeDIV.find("[name='rate']").val(parseFloat(rate).toFixed(2));
                        $.post(getBaseURL() + "rate_plan/get_tax_amount_from_rate_plan_JSON/",
                                {
                                    rate_plan_id: roomTypeDIV.find('.charge-with option:selected').val()
                                },
                        function (tax) {
                            var taxedRate = rate * (1 + parseFloat(tax.percentage)) + parseFloat(tax.flat_rate);
                            that.rateWithTax = taxedRate;
                            //taxedRate = Math.round(taxedRate * 100) / 100;
                            var rateIncludingTaxDiv = roomTypeDIV.find('.rate-including-tax');

                            rateIncludingTaxDiv.text("(with tax: " + number_format(taxedRate, 2, ".", "") + ")");

                            if (tax.percentage != 0)
                            {
                                rateIncludingTaxDiv.removeClass("hidden");
                            }
                            else
                            {
                                rateIncludingTaxDiv.addClass("hidden");
                            }
                            
                            that._displayRateInfo(data, tax);
                        }, 'json'
                                );
                    }
                }, 'json'
                        );
              
            }
            this._updatePayPeriodDropdown();
        },
        _updateNumberOfDays: function () {
            var that = this;
            var checkInDate = this.$modalBody.find("[name='check_in_date']").val();
            var checkOutDate = this.$modalBody.find("[name='check_out_date']").val();
            if (!checkInDate || !checkOutDate) {
                return;
            }

            // set number of days as check_out_date - check_in_date
            var cid = checkInDate.split(/[-]/);
            var cod = checkOutDate.split(/[-]/);

            // Apply each element to the Date function
            var check_in_date = new Date(cid[0], cid[1] - 1, cid[2]);
            var check_out_date = new Date(cod[0], cod[1] - 1, cod[2]);
            var oneDay = 24 * 60 * 60 * 1000; // hours*minutes*seconds*milliseconds
            var diffDays = Math.round(Math.abs((check_in_date.getTime() - check_out_date.getTime()) / (oneDay)))
            this.$modalBody.find("[name='number_of_days']").val(diffDays);

            that._updatePayPeriodDropdown();
        },
        _updateChargeWithDDL: function (roomTypeDIV) {

            var that = this;

            var roomTypeID = roomTypeDIV.find("[name='room_type_id']").val();
            roomTypeID = roomTypeID ? roomTypeID : that.booking.current_room_type_id;

            var select = $("<select/>", {
                class: 'form-control charge-with form-group',
                style: 'max-width: 300px;width: 150px;'
            })

            var chargeTypeOptionGroup = $("<optgroup/>", {
                label: "Charge Types (Manual)"
            });

            //alert('arrive setChargeTypeDDL');
            $.getJSON(getBaseURL() + 'booking/get_charge_types_in_JSON',
                    function (data) {

                        $.post(getBaseURL() + 'booking/get_rate_plans_JSON/', {
                            room_type_id: roomTypeID,
                            previous_rate_plan_id: that.booking.rate_plan_id
                        }, function (ratePlan) {


                            if (data !== '' && data !== null && data.length > 0) {

                                for (var i in data) {
                                    var option = $("<option/>", {
                                        value: data[i].id,
                                        text: data[i].name,
                                    });

                                    if (that.booking.charge_type_id == data[i].id &&
                                            that.booking.use_rate_plan == 0) {
                                        option.prop("selected", true);
                                    }

                                    chargeTypeOptionGroup.append(option);
                                }
                            }

                            select.append(chargeTypeOptionGroup);

                            if (ratePlan !== '' && ratePlan !== null && ratePlan.length > 0) {
                                // Set Rate Plan DDL
                                var ratePlanOptionGroup = $("<optgroup/>", {
                                    label: "Rate Plans (Pre-set)"
                                });

                                for (var i in ratePlan) {
                                    var option = $("<option/>", {
                                        value: ratePlan[i].rate_plan_id,
                                        text: ratePlan[i].rate_plan_name,
                                        class: 'rate-plan'
                                    });
                                    if (that.booking.rate_plan_id == ratePlan[i].rate_plan_id &&
                                            that.booking.use_rate_plan == 1) {
                                        option.prop("selected", true);
                                    }

                                    ratePlanOptionGroup.append(option);
                                }
                            }

                            select.append(ratePlanOptionGroup)
                                    .on('change', function () {
                                        that._updateRate(roomTypeDIV);
                                    });



                            // If nothing's been selected (This ususally occurs if previously selected rate plan has been deleted)
                            if (select.find(":selected").val() === undefined) {
                                select.append(
                                        $("<option/>", {
                                            selected: 'selected',
                                            text: "NOT SELECTED"
                                        })
                                        );
                            }

                            that.deferredChargeWithDDL.resolve();
                            roomTypeDIV.find(".charge-with-div").html(select);
                            that._updateRate(roomTypeDIV);

                        }, 'json'
                                );
                    }
            );
        },
        _getSelect: function (name, options) {

            var select = $("<select/>", {
                class: 'form-control',
                name: name,
                style: (name == 'pay_period') ? 'max-width: 125px;' : ''
            })

            options.forEach(function (data) {
                var option = $('<option/>', {
                    value: data.id,
                    text: data.name
                });

                option.appendTo(select);
            });


            return select;

        },
        _getExtraSelect: function (label, name, options, selectedOptionID) {

            var select = $("<select/>", {
                class: 'form-control',
                name: name
            })

            options.forEach(function (data) {
                var option = $('<option/>', {
                    value: data.extra_id,
                    text: data.extra_name,
                    "data-charging-scheme" : data.charging_scheme
                });

                if (data.extra_id == selectedOptionID) {
                    option.prop('selected', true);
                }

                option.appendTo(select);
            });

            return $("<div/>", {
                class: "form-group form-group-sm charging-scheme-block"
            }).append(
                    $("<label/>", {
                        for : name,
                        class: "col-sm-3 control-label",
                        text: label
                    })
                    ).append(
                    $("<div/>", {
                        class: "col-sm-9"
                    }).append(select)
                    )
        },
        _getHorizontalInput: function (label, name, value) {
            return $("<div/>", {
                class: "form-group form-group-sm block_"+name
            }).append(
                    $("<label/>", {
                        for : name,
                        class: "col-sm-3 control-label",
                        text: label
                    })
                    ).append(
                    $("<div/>", {
                        class: "col-sm-9"
                    }).append(
                    $("<input/>", {
                        class: "form-control",
                        name: name,
                        value: value,
                        autocomplete: false
                    })
                    )
                    )
        },
        _confirmationGroupDateModel: function (obj) {
            var that = this;
            var confirmVal = confirm('Apply to All reservations in the group');
            if (confirmVal == true) {
                that.saveAllGroupDate = true;
            } else {
                that.saveAllGroupDate = false;
            }
        },
        _getAllGroupRoomBookingIds: function () {
            var that = this;
            // var bookingId = '';
            //var roomId ='';
            var roomBookingArr = [];
            var checkInDate = $('input[name="check_in_date"]').val();
            var checkOutDate = $('input[name="check_out_date"]').val();

            if (that.saveAllGroupDate == true) {
                var roomList = $('.room-lists .room-list-info');
                roomList.each(function () {
                    if($(this).attr('data-booking-cancelled') == 'false'){
                        roomBookingArr.push({'bookingId': $(this).attr('id'), 'roomId': $(this).attr('data-room-id')});
                    }
                });
            } else if (that.saveAllGroupDate == false) {
                roomBookingArr.push({bookingId: that.booking.booking_id, roomId: that.booking.current_room_id});
            }
            var bookingData = {booking: {new_check_in_date: checkInDate, check_out_date: checkOutDate, room_booking_ar: roomBookingArr, update_date: true}};
            that._updateGroupBooking(bookingData);
        },
        _cancelDeleteGroupBookingRoom: function (action) {
            var that = this;
            var booking_ids = [];
            $('.room-lists').find(".room-list-info").each(function () {
                if ($(this).find(".cancelled-room-checkbox").prop('checked') && $(this).attr("id") !== 'undefined') {
                    booking_ids.push($(this).attr("id"));
                }
            });
            if (booking_ids.length < 1) {
                alert(l("Select at least one room"));
                return;
            }

            $.each(booking_ids, function (key, value) {
                if (action == 'Cancel') {
                    var bookingId = value;
                    var bookingData = {booking: {state: 4}}
                    that._updateGroupBooking(bookingData, 'Booking is cancelled', bookingId);
                }
                if (action == 'Delete') {
                    var groupBookingId = value;
                    var isGroupBookingDel = true;
                    that._deleteBooking(isGroupBookingDel, groupBookingId);
                }
            });
        }


    }; // -- Prototype

    // eventually, add an option to enter check-in & check-out date.

    $.fn.openBookingModal = function (options) {
        var body = $("body");

        // preventing against multiple instantiations
        $.data(body, 'bookingModal', new BookingModal(options));
    }

    // group manager search 
    var SearchGroupModel = function () {
        var that = this;
        this.searchGroupsInfo = null;

        this._init();

        this._populateNewSearchGroupModel();
    };

    SearchGroupModel.prototype = {
        _init: function () {
            var that = this;
            $('#group-search-model').modal('show');

            this.closeGroupModal = $.Deferred();
            $.when(this.closeGroupModal.promise()).done(function (script) {
                $("#group-search-model").modal('hide');
            });
        },
        _constructGroupModalComponents: function () {
            var that = this;
            this.$modalGroupBody = $("<div/>", {
                class: "modal-body "
            });
            $("#group-search-model").find(".modal-content")
                    .append(
                            $("<div/>", {
                                class: "modal-header"
                            }).append(
                            $("<span/>", {
                                class: "bold",
                                text: "Group Manager"
                            })
                            ).append(
                            $("<button/>", {
                                class: "close",
                                "data-dismiss": "modal",
                                "aria-label": "Close"
                            }).append(
                            $("<span/>", {
                                "aria-hidden": "true",
                                html: "&times;"
                            })
                            )
                            )
                            )
                    .append(this.$modalGroupBody).append(this._getSearchGroupPanel())
                    .append(
                            $("<div/>", {
                                class: "modal-footer",
                            }).append(
                            $("<div/>", {
                                class: "col-sm-10 text-left",
                            }).append(
                            $("<div/>", {
                                class: "btn btn-default show-all-groups",
                                type: "button",
                                text: 'Show All Groups'
                            }).on('click', function () {
                        that.$modalGroupBody.find('input[name = "group-name"]').val("");
                        that.$modalGroupBody.find('input[name = "group-id"]').val("");
                        that.$modalGroupBody.find('input[name = "customer-name"]').val("");
                        that.$modalGroupBody.find('.show-groups-list').remove();
                        var showAllGroups = true;
                        that._addGroupListDivWithModelBody();
                        that._getSearchGroupFieldsValue(showAllGroups);
                    })
                            ).append(
                            $("<div/>", {
                                class: "btn btn-default new-search",
                                type: "button",
                                text: 'New Search'
                            }).on('click', function () {
                        that.$modalGroupBody.find('input[name = "group-name"]').val("");
                        that.$modalGroupBody.find('input[name = "group-id"]').val("");
                        that.$modalGroupBody.find('input[name = "customer-name"]').val("");
                        that.$modalGroupBody.find('.show-groups-list').remove();
                    })
                            )
                            ).append(
                            $("<div/>", {
                                class: "col-sm-2",
                            }).append(
                            $("<div/>", {
                                class: "btn btn-default",
                                'data-dismiss': "modal",
                                type: "button",
                                text: 'Close'
                            })
                            )
                            )
                            );

        },
        _getSearchGroupPanel: function () {

            var that = this;
            that.$modalGroupBody.append(
                    $("<div/>", {
                        class: "panel panel-default"
                    })
                    .append(
                            $("<div/>", {
                                class: "panel-body form-horizontal"
                            }).append(
                            $("<div/>", {
                                class: "form-group",
                            }).append(
                            $("<label/>", {
                                for : "group-name",
                                class: "col-sm-3 control-label",
                                text: "Group Name"
                            })
                            ).append(
                            $("<div/>", {
                                class: "col-sm-9"
                            }).append(
                            $("<input/>", {
                                class: "form-control",
                                name: "group-name",
                                value: ''
                            })
                            )
                            )
                            ).append(
                            $("<div/>", {
                                class: "form-group",
                            }).append(
                            $("<label/>", {
                                for : "group-id",
                                class: "col-sm-3 control-label",
                                text: "Group Id"
                            })
                            ).append(
                            $("<div/>", {
                                class: "col-sm-9"
                            }).append(
                            $("<input/>", {
                                class: "form-control",
                                name: "group-id",
                                value: ''
                            })
                            )
                            )
                            ).append(
                            $("<div/>", {
                                class: "form-group",
                            }).append(
                            $("<label/>", {
                                for : "customer-name",
                                class: "col-sm-3 control-label",
                                text: "Customer"
                            })
                            ).append(
                            $("<div/>", {
                                class: "col-sm-9"
                            }).append(
                            $("<input/>", {
                                class: "form-control",
                                name: "customer-name",
                                value: ''
                            })
                            )
                            )
                            ).append(
                            $("<div/>", {
                                class: "form-group",
                            }).append(
                            $("<div/>", {
                                class: "col-sm-12 text-center",
                            }).append(
                            $("<button/>", {
                                type: "button",
                                class: "btn btn-default",
                                text: "Find Group"
                            }).on("click", function () {
                        that._addGroupListDivWithModelBody();
                        that._getSearchGroupFieldsValue();
                    })
                            )
                            )
                            )
                            )
                    );
        },
        _populateNewSearchGroupModel: function () {
            var that = this;
            $("#group-search-model").find(".modal-content").html("");
            this._constructGroupModalComponents();

        },
        _addGroupListDivWithModelBody: function () {
            var that = this;
            that.$modalGroupBody.find('.show-groups-list').remove();
            that.$modalGroupBody.append(
                    $("<div/>", {
                        class: "show-groups-list panel panel-default",
                        style: 'overflow: auto;'
                    })
                    )
        },
        _getSearchGroupFieldsValue: function (showAllGroups) {
            var that = this;

            if(showAllGroups != true){
                var groupName = '';
                var groupId = '';
                var customerName = '';
            }
            var groupName = that.$modalGroupBody.find('input[name = "group-name"]').val();
            var groupId = that.$modalGroupBody.find('input[name = "group-id"]').val();
            var customerName = that.$modalGroupBody.find('input[name = "customer-name"]').val();

            $.ajax({
                url: getBaseURL() + 'booking/search_linked_groups',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    group_name: groupName,
                    group_id: groupId,
                    customer_name: customerName
                },
                success: function (response) {
                    that.deferredSearchGroups = $.Deferred();
                    if (response.success == true) {
                        that.searchGroupsInfo = response.group_info;
                        that.deferredSearchGroups.resolve();
                    } else if (response.success == false) {
                        that.searchGroupsInfo = response.message;
                        that.deferredSearchGroups.reject();
                    }

                    $.when(that.deferredSearchGroups)
                            .done(function () {
                                var groupsList = that.$modalGroupBody.find('.show-groups-list');
                                groupsList.html("");
                                groupsList.append(
                                        $('<table/>', {
                                            class: "groups-info table"
                                        }).append(
                                        $('<thead/>', {
                                        }).append(
                                        $('<tr/>', {
                                        }).append(
                                        $('<th/>', {
                                            text: 'Group Name'
                                        })
                                        ).append(
                                        $('<th/>', {
                                            text: 'Customer'
                                        })
                                        ).append(
                                        $('<th/>', {
                                            text: 'Phone'
                                        })
                                        ).append(
                                        $('<th/>', {
                                            text: 'First Check In Date'
                                        })
                                        ).append(
                                        $('<th/>', {
                                            text: 'Last Check Out Date'
                                        })
                                        )
                                        )
                                        ).append(
                                        $('<tbody/>', {
                                        })
                                        )

                                        );
                                if (that.searchGroupsInfo.length > 0) {
                                    var tbody = $('#group-search-model').find('.groups-info tbody');
                                    for (var i = 0; i < that.searchGroupsInfo.length; i++) {
                                        tbody.append(
                                                $('<tr/>', {
                                                    id: "group-id-" + that.searchGroupsInfo[i].id,
                                                    style: 'cursor:pointer',
                                                    'data-booking-id': that.searchGroupsInfo[i].booking_id
                                                }).append(
                                                $('<td/>', {
                                                    text: that.searchGroupsInfo[i].name
                                                })
                                                ).append(
                                                $('<td/>', {
                                                    text: that.searchGroupsInfo[i].customer_name
                                                })
                                                ).append(
                                                $('<td/>', {
                                                    text: that.searchGroupsInfo[i].phone
                                                })
                                                ).append(
                                                $('<td/>', {
                                                    text: that.searchGroupsInfo[i].check_in_date
                                                })
                                                ).append(
                                                $('<td/>', {
                                                    text: that.searchGroupsInfo[i].check_out_date
                                                })
                                                ).on("click", function () {
                                            var bookingId = $(this).attr('data-booking-id');
                                            var options = {};
                                            options.id = bookingId;
                                            var body = $("body"); // call booking model 
                                            $.data(body, 'bookingModal', new BookingModal(options));
                                            $('#booking-modal').css('z-index', '1061');
                                            $('#customer-modal').css('z-index', '1062');
                                        })
                                                );
                                    }
                                    if (groupsList.height() > '140') {
                                        groupsList.css('height', '140');
                                    }
                                }
                            })
                            .fail(function () {
                                var groupsList = that.$modalGroupBody.find('.show-groups-list');
                                groupsList.append('<h3 class="text-center">' + that.searchGroupsInfo + '</h3>');
                            });

                }
            });

        },
        _closeSearchGroupModal: function () {
            this.closeGroupModal.resolve();
        }
    }

    $.fn.openSearchGroupModel = function() {
        var body = $("body");

        // preventing against multiple instantiations
        $.data(body, 'searchGroupModel', new SearchGroupModel());
    }

})(jQuery, window, document);