/*  Plugin for Booking Modals
 *   It takes the element's id attr, and use it as bookingID
 */
var _createBookingLock = false;
var bookingDetails = [];
var bookingCustomerTypeID = '';


var width = $("body").width();
setCookie('width',width);

var bookingModalInvoker = function ($) {
    "use strict";

    var defaults = {};
    var defaultRoomCharge = {};

    // dynamically load required css
    var csses = [
        'css/bootstrap-colorselector.css',
        'css/bootstrap-tokenfield.min.css'
    ];

    csses.forEach(function (css) {
        if (document.createStyleSheet) {
            document.createStyleSheet(getBaseURL() + css);
        } else {
            $('<link rel="stylesheet" type="text/css" href="' + getBaseURL() + css + '" />').appendTo('head');
        }
    });

    // Ability for show decimal places
    var show_decimal = true;
    var add_remaining_daily_charges = true;
    var invoiceGroupId = '';
    var invoice_group_id = '';
//    var parent_rate_plan_id = false;

    var startTime = moment().startOf('day');
    var endTime = moment().endOf('day');

    var timeOptions = [];
    var time = startTime;

    while (time <= endTime) {
        timeOptions.push('<option value="' + time.format('hh:mm A') + '">' + time.format('hh:mm A') + '</option>');
        time = time.clone().add(30, 'm');
    }

    innGrid.ajaxCache = innGrid.ajaxCache || {};

    // dynamically load required js
    var scripts = [
        'js/booking/jquery.ui.autocomplete.scroll.min.js',
        'js/customer/customerModal.js',
        'js/bootstrap-tokenfield.js',
        'js/bootstrap-colorselector.js',
        'js/jquery.payment.js',
        'js/card_detail/cardModal.js'

        /*
                 ,'js/booking/chargeTypeSelect.js',
                 'js/booking/roomTypeSelect.js'
                 */

    ];

    scripts.forEach(function (script) {
        $.getScript(getBaseURL() + script, function () {
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
    $("#booking-modal").modal({show: false, backdrop: 'static', keyboard: true});
    $("#group-search-model").modal({show: false, backdrop: 'static', keyboard: false});

    var BookingModal = function (options) {

        var that = this;

        this.deferredChargeWithDDL = $.Deferred();
        this.deferredRoomDDL = $.Deferred();
        this.deferredExistingCustomerConfirmation = $.Deferred();
        this.deferredBookingSource = $.Deferred();
        this.isCreatingCustomer = false;
        this.companyBookingSources = null;
        this.customBookingFields = null;
        this.deferredBookingFields = $.Deferred();
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
        this.rateInclusiveTax = null;

        this.ratePlanCache = {};
        this.roomTypesCache = {};
        this.roomsCache = {};

        if (this.options.isAddGroupBooking) {
            this.groupInfo = this.options.isAddGroupBooking;
        }


        if (!this.options.id) // new booking
        {
            this._populateNewBookingModal();
        } else //edit existing booking!
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
                {id: 1, name: '1 '+l('adult')},
                {id: 2, name: '2 '+l('adults')},
                {id: 3, name: '3 '+l('adults')},
                {id: 4, name: '4 '+l('adults')},
                {id: 5, name: '5 '+l('adults')},
                {id: 6, name: '6 '+l('adults')},
                {id: 7, name: '7 '+l('adults')},
                {id: 8, name: '8 '+l('adults')},
                {id: 9, name: '9 '+l('adults')},
                {id: 10, name: '10 '+l('adults')},
                {id: 11, name: '11 '+l('adults')},
                {id: 12, name: '12 '+l('adults')},
                {id: 13, name: '13 '+l('adults')},
                {id: 14, name: '14 '+l('adults')}
            ];

            var adultOption = [];

            for (var i = 15; i <= 100; i++) {
                adultOption.push({id:i, name: i+' adults'});
            }

            this.adultsCount = this.adultsCount.concat(adultOption);

            this.childrenCount = [
                {id: 0, name: 'no '+l('children')},
                {id: 1, name: '1 '+l('child')},
                {id: 2, name: '2 '+l('children')},
                {id: 3, name: '3 '+l('children')},
                {id: 4, name: '4 '+l('children')},
                {id: 5, name: '5 '+l('children')},
                {id: 6, name: '6 '+l('children')},
                {id: 7, name: '7 '+l('children')},
                {id: 8, name: '8 '+l('children')},
                {id: 9, name: '9 '+l('children')},
                {id: 10, name: '10 '+l('children')},
                {id: 11, name: '11 '+l('children')},
                {id: 12, name: '12 '+l('children')},
                {id: 13, name: '13 '+l('children')},
                {id: 14, name: '14 '+l('children')}
            ];

            var childOption = [];

            for (var i = 15; i <= 99; i++) {
                childOption.push({id:i, name: i+' children'});
            }

            this.childrenCount = this.childrenCount.concat(childOption);

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
                {id: '0', name: l('Nightly')},
                {id: '1', name: l('Weekly')},
                {id: '2', name: l('Monthly')},
                {id: '3', name: l('One Time')}
            ];

            setTimeout(function(){
                invoice_group_id = $('#group_id').val();
                console.log('invoice_group_id',invoice_group_id);
                $('.group_invoice').attr('href', getBaseURL() + "invoice/show_master_invoice/" + invoice_group_id);
            },2000);

            // array of different option buttons
            this.$allActions = {
                editHousekeepingNotes: $("<li/>").append(
                    $("<a/>", {
                        href: '#',
                        text: l("Edit Housekeeping Notes")
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

                sendCancellationEmail: $("<li/>").append(
                    $("<a/>", {
                        href: '#',
                        class: 'send-cancellation-email',
                        text: l("Send Cancellation Email")
                    })
                ),

                sendConfirmationEmail: $("<li/>").append(
                    $("<a/>", {
                        href: "#",
                        class: 'send_confirmation_email',
                        text: l('send_email_confirmation')
                    })
                ),

                sendGroupConfirmationEmail: $("<li/>").append(
                    $("<a/>", {
                        href: "#",
                        class: 'send_group_confirmation_email',
                        text: l("Send group confirmation email")
                    }).on('click', function (e) {
//                    e.preventDefault(); // prevent # scrolling to the top
//                    $.post(getBaseURL() + 'booking/send_booking_confirmation_email/' + that.booking.booking_id, function (response) {
//                        alert(response);
//                    });
                    })
                ),
                openGroupInvoice: $("<li/>").append(
                    $("<a/>", {
                        href: getBaseURL() + "invoice/show_master_invoice/" + invoice_group_id,
                        class: 'group_invoice',
                        text: l('Group Invoice')
                    }).on('click', function (e) {
                        
                    })
                ),
                showHistory: $("<li/>").append(
                    $("<a/>", {
                        href: "#",
                        text: l("Show History")
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
                        text: l("Add Product")
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
                            success: function (response) {
                                that._editBookingExtra(response.booking_extra_id);

                                // update booking balance
                                if (response && $.isNumeric(response.balance)) {
                                    $('.booking_balance').html(number_format(response.balance, 2, ".", ""));
                                }

                                // if this is the first extra to this booking,
                                // create extra container (panel). otherwise, append extra to existing
                                extraData.booking_extra_id = response.booking_extra_id;
                                if ($("#extra-container").length) {
                                    extraData.charging_scheme = $("#inner-modal").find("[name='extra_id'] option:selected").attr('data-charging-scheme');
                                    $("#extra-container").append(that._getBookingExtraDiv(extraData));
                                } else {
                                    if (!that.booking.extras) {
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
                        text: l("Add Room Block")
                    })
                ),
                setCutOffDate: $("<li/>").append(
                    $("<a/>", {
                        href: "#",
                        text: l("Set Cut-off date")
                    })
                ),
                createDuplicate: $("<li/>").append(
                    $("<a/>", {
                        href: "#",
                        class: 'create_duplicate',
                        text: l('create_duplicate_booking')
                    })
                ),
                deleteBooking: $("<li/>").append(
                    $("<a/>", {
                        href: "#",
                        class: 'delete_booking',
                        text: l('delete_booking')
                    }).on('click', function (e) {

                        //e.preventDefault(); // prevent # scrolling to the top
                        //that._deleteBooking();
                    })
                ),
                divider: $("<li/>", {
                    class: "divider"
                }),
                
                editFixRatePlan: $("<li/>").append(
                    $("<a/>", {
                        href: "#",
                        class: 'edit_fix_rate_plan',
                        text: l('Edit Fixed Rate Plan')
                    }).on('click', function (e) {})
                ),
                editRatePerPerson: $("<li/>").append(
                    $("<a/>", {
                        href: "#",
                        class: 'edit_rate_per_person',
                        text: l('Edit Rate per Person')
                    }).on('click', function (e) {})
                ),
                dividerNew: $("<li/>", {
                    class: "divider"
                })

            };

            this._initializeBookingModal();


            $(document).off('click', '.create_duplicate').on('click', '.create_duplicate', function (e) {
                e.preventDefault();
                var text = $(this).text();
                that._createDuplicate(text, true, that.booking.booking_id);
            })

            $(document).off('click', '.delete_booking').on('click', '.delete_booking', function (e) {
                e.preventDefault();
                that._deleteBooking();
            })

            $(document).off('click', '.send_confirmation_email').on('click', '.send_confirmation_email', function (e) {
                e.preventDefault();
                $.post(getBaseURL() + 'booking/send_booking_confirmation_email/' + that.booking.booking_id, function (response) {
                    alert(response);
                });
            });

            $(document).off('click', '.send-cancellation-email').on('click', '.send-cancellation-email', function (e) {
                e.preventDefault();
                $.post(getBaseURL() + "booking/send_booking_cancellation_email_AJAX/" + that.booking.booking_id, function (response) {
                    alert(response);
                });
            });

            $(document).off('click', '.send_group_confirmation_email').on('click', '.send_group_confirmation_email', function (e) {
                e.preventDefault();
                $.post(getBaseURL() + 'booking/send_group_booking_confirmation_email/' + that.booking.booking_id + '/' + that.groupInfo.group_id, function (response) {
                    alert(response);
                });
            });

            $(document).on('click', '.add-daily-charge', function (e) {
                if ($("input[name='add-daily-charge']:checked").val() == 1) {
                    $('#residual_rate_div').removeClass('hidden');
                } else {
                    $('#residual_rate_div').addClass('hidden');
                }
            });

            $(document).on('click', '#add_save_daily_charge_button', function (e) {
                e.preventDefault();
                var daily_charge_value = $("input[name='add-daily-charge']:checked").val();
                var residual_rate = $("input[name='residual_rate']").val();
                $('.daily_charge_msg').show();
                $("#add-daily-charges-modal").modal('hide');
                add_remaining_daily_charges = (daily_charge_value == 1) ? true : false;
                that.booking.add_daily_charge = (daily_charge_value == 1) ? 1 : 0;
                that.booking.residual_rate = residual_rate ? residual_rate : 0;
                var roomTypeDIV = $("body #booking-modal .modal-body").find(".room-type");
                that._updateRate(roomTypeDIV);
            });
            $(document).on('hidden.bs.modal', "#add-daily-charges-modal", function () {
                if (($("#booking-modal").data('bs.modal') || {}).isShown)
                    $("body").addClass("modal-open");
            });

        },
        _initializeBookingModal: function () {
            // re-initialize by deleting the existing modal

            this.$panel = $("<div/>", {
                //class: "box-body"
            }).append(
                $("<div/>", {
                    class: "panel-body",
                    style: 'padding:0px!important;'
                })
            );

            this.$rateInfo = $("<span/>", {
                class: 'rate-info',
                hidden: true
            })

                .append(
                    $('<div/>', {
                        class: 'form-group col-sm-3 width-fix-wep'
                    })
                        .append(
                            $('<label/>')
                                .append(
                                    $('<small/>', {
                                        html: "&nbsp;"
                                    })
                                )
                        )
                        .append(
                            this._getSelect('adult_count', this.adultsCount, 'adult_count')
                        )
                )
                .append(
                    $('<div/>', {
                        class: 'form-group col-sm-3',
                        style: 'padding: 0;margin-left: 0px;'
                    })
                        .append(
                            $('<label/>', {
                                for: "and"
                            })
                                .append(
                                    $('<small/>', {
                                        html: '&nbsp;'
                                    })
                                )
                        )
                        .append(
                            this._getSelect('children_count', this.childrenCount)
                        )
                )
                .append(
                    $("<span/>", {
                        class: 'charge-with-div form-group col-sm-6'
                    })
                        .append(
                            $('<label/>')
                                .append(
                                    $('<small/>', {
                                        text: l('charge_type')
                                    })
                                )
                        )
                )
                .append(
                    $("<div/>", {
                        class: ' col-sm-4 form-group rate-block',
                        style: 'padding-right: 0px;'
                    })
                        .append(
                            $('<label/>')
                                .append(
                                    $('<small/>', {
                                        text: l('rate')
                                    })
                                )
                        )
                        .append(
                            $('<div/>', {
                                class: 'input-group',
                                style: "padding: 1px"
                            })
                                .append(
                                    $("<span/>", {
                                        class: 'input-group-addon edit-rate-btn'
                                    })
                                        .append(
                                            $("<i/>", {
                                                class: 'fa fa-pencil-square-o',
                                                'aria-hidden': true
                                            })
                                        )
                                )
                                .append(
                                    $("<input/>", {
                                        name: 'rate',
                                        class: 'form-control',
                                        placeholder: "Rate",
                                        value: 0,
                                        type: 'number'
                                    })
                                )
                                .append(
                                    $('<span/>', {
                                        class: 'input-group-addon rate-including-tax hidden',
                                        style: 'padding: 2px;'
                                    })
                                )
                        )
                )
                .append(
                    $('<div/>', {
                        class: 'form-group col-sm-2 pay-period-block',
                        style: 'padding-left: 4px;margin-top: 25px;padding-right: 0px;'
                    })
                        .append(this._getSelect('pay_period', this.payPeriods, 'pay_period'))
                        .css('padding', '0px')
                );


            // because it's a $(document) call, it's in the constructor
            // .off exists to prevent from event firing multiple times
            $(document).off('click', '.token').on('click', '.token', function () {
                if (($("#booking-modal").data('bs.modal') || {}).isShown) {
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
            var modalHeader = $("#booking-modal .modal-header");

            var state = this.booking.state;

            if (this.groupInfo != null) {
                modelBodyClass = (that.booking.booking_id) ? 'col-lg-7 booking-modal-body' : 'col-lg-9 booking-modal-body';
            } else {
                modelBodyClass = (that.booking.booking_id) ? 'col-lg-9 booking-modal-body' : 'col-lg-12 booking-modal-body';
            }

            this.$modalBody = $("<div/>", {
                class: "modal-body content " + modelBodyClass
            }).html(this._getBookingTypePanel());
            //.append(this._getSingleBookingPanel())
            //.append(this._getCustomerAndNotesPanel())
            //.append(this._getExtraPanel());

            //that._setHeight('booking_detail');


            var roomTypeDIV = this.$modalBody.find('#booking_detail.room-type');
            roomTypeDIV.find("[name='check_in_date'], [name='check_out_date'], [name='number_of_days']")
                .on('change', function () {
                    that._updateNumberOfDays();
                    that._updateRate(roomTypeDIV);
                    //that._updateRoomTypeDDL();
                });

            roomTypeDIV.find("[name='adult_count'], [name='children_count'], [name='rate']")
                .on('change', function () {
                    that.booking.state == 3 ? '' : that._validateCapacity();
                    that._updateRate(roomTypeDIV);
                });

            if (that.booking.source != 2 && that.booking.source != 3 && that.booking.source != 4 && that.booking.source != 8) {
                roomTypeDIV.find('.edit-rate-btn').on('click', function () {
                    that._initializeRateModal();
                });
            } else {
                roomTypeDIV.find('.edit-rate-btn').css('cursor', 'not-allowed');
            }

            // if walk-in, disable check-in date
            if ($("[name='state']").val() === '1' && that.booking.booking_id === undefined) {
                block.find("[name='check_in_date']").val(innGrid._getLocalFormattedDate($("#sellingDate").val()));
                block.find("[name='check_in_date']").prop("disabled", true);
            }

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
            if (this.groupInfo != null) {
                $('.modal-dialog.modal-lg').css('width', '1110px');
                this.$modalBody.after(
                    $("<div/>", {
                        class: "col-lg-3",
                        //style: "padding: 0px 15px 0px 0px;"
                    }).append(
                        $("<div/>", {
                            class: "panel panel-default"
                        }).append(
                            $("<div/>", {
                                class: 'panel-heading '
                            }).append(
                                $("<h3/>", {
                                    text: l('Rooms'),
                                    class: "panel-title bold pull-left",
                                    style: "padding-top: 8px;"
                                })
                            )
                                .append(
                                    $("<div/>", {
                                        class: 'pull-right btn-group'
                                    }).append(
                                        $("<button/>", {
                                            html: l('Add a room'),
                                            class: 'btn btn-light btn-sm',
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
                                            class: "btn btn-light  btn-sm  dropdown-toggle",
                                            "data-toggle": "dropdown",
                                            "aria-expanded": false,
                                            text: l("Action")+" ",
                                        })
                                        // .append(
                                        //     $("<span/>", {
                                        //         class: "caret"
                                        //     })
                                        // )
                                    ).append(
                                        $("<ul/>", {
                                            class: "dropdown-menu other-actions",
                                            role: "menu",
                                            style:"min-width:200px"
                                        })
                                            .append(
                                                $("<li/>").append(
                                                    $("<a/>", {
                                                        href: "#",
                                                        //class: 'send_confirmation_email',
                                                        text: l('send_email_confirmation')
                                                    }).on("click", function (e) {
                                                        that._cancelDeleteGroupBookingRoom('Email');
                                                    })
                                                )
                                            )
                                            .append(
                                                $("<li/>", {}).append(
                                                    $("<a/>", {
                                                        href: '#',
                                                        text: l("Cancelled (Hide)")
                                                    }).on("click", function (e) {
                                                        e.preventDefault();
                                                        that._cancelDeleteGroupBookingRoom('Cancel');
                                                    })
                                                )
                                            )
                                            .append(
                                                $("<li/>", {}).append(
                                                    $("<a/>", {
                                                        href: '#',
                                                        text: l("Delete")
                                                    }).on("click", function (e) {
                                                        e.preventDefault();
                                                        that._cancelDeleteGroupBookingRoom('Delete');
                                                    })
                                                )
                                            ).append(
                                                $("<li/>", {}).append(
                                                    $("<a/>", {
                                                        href: '#',
                                                        text: l("Check-in")
                                                    }).on("click", function (e) {
                                                        e.preventDefault();
                                                        that._cancelDeleteGroupBookingRoom('Checkin');
                                                    })
                                                )
                                            ).append(
                                                $("<li/>", {}).append(
                                                    $("<a/>", {
                                                        href: '#',
                                                        text: l("Check-out")
                                                    }).on("click", function (e) {
                                                        e.preventDefault();
                                                        that._cancelDeleteGroupBookingRoom('Checkout');
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

                this.$modalBody.before(
                    $("<div/>", {
                        class: "col-lg-2 sidebar-wrapper " + (that.booking.booking_id ? '' : 'hidden'),
                        style: "padding: 0px 15px 0px 0px;"
                    }).append(
                        $("<ul/>", {
                            class: "nav nav-tabs tabs-left left-sidebar",
                        }).append(
                            $("<li/>", {
                                class: 'active booking_detail'
                            }).append(
                                $("<a/>", {
                                    'href': '#booking_detail',
                                    'id': 'booking_detail_tab',
                                    'data-toggle': "tab",
                                    'text': l('booking_detail')
                                }).on('click', function (e) {
                                    that.$modalBody = $("<div/>", {
                                        class: "modal-body content " + modelBodyClass
                                    }).html(that._getBookingTypePanel());
                                    setTimeout(function () {
                                        that._setHeight('booking_detail');
                                    }, 1000);
                                })
                            )
                        )
                            // .append(
                            //     $("<li/>",
                            //         {
                            //             class: 'registration_card_tab ' + (this.booking.booking_id ? '' : 'hidden')
                            //         }
                            //     ).append(
                            //         $("<a/>", {
                            //             'href': '#registration_card',
                            //             'data-toggle': "tab",
                            //             'text': l('registration_card')
                            //         }).on('click', function (e) {
                            //             if (that.booking.state !== undefined) {
                            //                 that._populateRegistrationCardDDL();
                            //                 setTimeout(function () {
                            //                     that._setHeight('registration_card');
                            //                 }, 3000);
                            //             }
                            //         })
                            //     )
                            // )
                            .append(
                                $("<li/>", {
                                    // style: isTokenizationEnabled == 1 && innGrid.featureSettings.selectedPaymentGateway ? "" : "display: none;"
                                }).append(
                                    $("<a/>", {
                                        'href': '#payment_details',
                                        'id': 'pay_details_tab',
                                        'data-toggle': "tab",
                                        'text': l('Payment Details')
                                    }).on('click', function (e) {
                                        if (that.booking.state !== undefined) {
                                            if (that.booking.booking_customer_id) {
                                                var customer_array = new Array();
                                                $.ajax({
                                                    type: "POST",
                                                    url: getBaseURL() + "customer/get_customer_card_data",
                                                    data: {
                                                        booking_id: that.booking.booking_id
                                                    },
                                                    dataType: "json",
                                                    success: function (new_customer_data) {
                                                        if (new_customer_data != null) {
                                                            // new_customer_data.push( {'booking_id': that.booking.booking_id} );
                                                            $(".is_primary_check").prop('disabled', false);
                                                            that._populatePaymentCard(new_customer_data, that.booking.booking_id);
                                                        }
                                                    },
                                                    error: function (e) {

                                                    }
                                                });
                                            }
                                        }
                                        setTimeout(function () {
                                            that._setHeight('payment_details');
                                        }, 1000);
                                    })
                                )
                            )
                            .append(
                                $("<li/>").append(
                                    $("<a/>", {
                                        'href': '#housekeeping',
                                        'id': 'housekeeping_tab',
                                        'data-toggle': "tab",
                                        'text': l('housekeeping')
                                    }).on('click', function (e) {
                                        if (that.booking.state !== undefined) {
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
                                        }
                                        setTimeout(function () {
                                            that._setHeight('housekeeping');
                                        }, 1000);
                                    })
                                )
                            )
                            .append(
                                $("<li/>",{
                                    class: "history_tab"
                                }).append(
                                    $("<a/>", {
                                        'href': '#history',
                                        'id': 'history_tab',
                                        'data-toggle': "tab",
                                        'text': l('history')
                                    }).on('click', function (e) {
                                        if (that.booking.state !== undefined) {
                                            $("#history").find(".content").html('');
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
                                        }
                                        setTimeout(function () {
                                            that._setHeight('history');
                                        }, 1000);
                                    })
                                )
                            )
                            .append(
                                $("<li/>").append(
                                    $("<a/>", {
                                        'href': '#extras',
                                        'id': 'extras_tab',
                                        'data-toggle': "tab",
                                        'text': l('Products')
                                    })
                                        .append(
                                            $("<span/>", {
                                                'class': 'extras_count',
                                                'text': " ( " + that.booking.extras_count + " )"
                                            })
                                        )
                                        .on('click', function (e) {
                                            setTimeout(function () {
                                                that._setHeight('extras');
                                            }, 1000);
                                        })
                                )
                            )
                            
                    )
                );

            } else {
                this.$modalBody.before(
                    $("<div/>", {
                        class: "col-lg-3 sidebar-wrapper " + (that.booking.booking_id ? '' : 'hidden'),
                        style: "padding: 0px 15px 25px 0px;"
                    }).append(
                        $("<ul/>", {
                            class: "nav nav-tabs tabs-left left-sidebar left-sidebar-fix-wep",
                        }).append(
                            $("<li/>", {
                                class: 'active'
                            }).append(
                                $("<a/>", {
                                    'href': '#booking_detail',
                                    'id': 'booking_detail_tab',
                                    'data-toggle': "tab",
                                    'text': l('booking_detail')
                                }).on('click', function (e) {
                                    that.$modalBody = $("<div/>", {
                                        class: "modal-body content " + modelBodyClass
                                    }).html(that._getBookingTypePanel());
                                    setTimeout(function () {
                                        that._setHeight('booking_detail');
                                    }, 1000);
                                })
                            )
                        )
                            // .append(
                            //     $("<li/>",
                            //         {
                            //             class: 'registration_card_tab ' + (this.booking.booking_id ? '' : 'hidden')
                            //         }
                            //     ).append(
                            //         $("<a/>", {
                            //             'href': '#registration_card',
                            //             'data-toggle': "tab",
                            //             'text': l('registration_card')
                            //         }).on('click', function (e) {
                            //             if (that.booking.state !== undefined) {
                            //                 that._populateRegistrationCardDDL();
                            //                 setTimeout(function () {
                            //                     that._setHeight('registration_card');
                            //                 }, 3000);
                            //             }
                            //         })
                            //     )
                            // )

                            .append(
                                $("<li/>", {
                                    // style: isTokenizationEnabled == 1 && innGrid.featureSettings.selectedPaymentGateway ? "" : "display: none;"
                                }).append(
                                    $("<a/>", {
                                        'href': '#payment_details',
                                        'id': 'pay_details_tab',
                                        'data-toggle': "tab",
                                        'text': l('Payment Details')
                                    }).on('click', function (e) {
                                        if (that.booking.state !== undefined) {
                                            var customer_array = new Array();
                                            if (that.booking.booking_id) {
                                                $.ajax({
                                                    type: "POST",
                                                    url: getBaseURL() + "customer/get_customer_card_data",
                                                    data: {
                                                        booking_id: that.booking.booking_id
                                                    },
                                                    dataType: "json",
                                                    success: function (new_customer_data) {
                                                        if (new_customer_data != null) {
                                                            // new_customer_data.push( {'booking_id': that.booking.booking_id} );
                                                            that._populatePaymentCard(new_customer_data, that.booking.booking_id);
                                                        }
                                                    },
                                                    error: function (e) {

                                                    }
                                                });
                                            }


                                        }
                                        setTimeout(function () {
                                            that._setHeight('payment_details');
                                        }, 1000);
                                    })
                                )
                            )
                            .append(
                                $("<li/>", {
                                    style: isTokenizationEnabled == 1 && innGrid.featureSettings.selectedPaymentGateway ? "" : "display: none;"
                                })
                            )
                            .append(
                                $("<li/>").append(
                                    $("<a/>", {
                                        'href': '#housekeeping',
                                        'id': 'housekeeping_tab',
                                        'data-toggle': "tab",
                                        'text': l('housekeeping')
                                    }).on('click', function (e) {
                                        if (that.booking.state !== undefined) {
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
                                        }
                                        setTimeout(function () {
                                            that._setHeight('housekeeping');
                                        }, 1000);
                                    })
                                )
                            )
                            .append(
                                $("<li/>",{
                                    class: "history_tab"
                                }
                                ).append(
                                    $("<a/>", {
                                        'href': '#history',
                                        'id': 'history_tab',
                                        'data-toggle': "tab",
                                        'text': l('history')
                                    }).on('click', function (e) {
                                        if (that.booking.state !== undefined) {
                                            $("#history").find(".content").html('');
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
                                        }
                                        setTimeout(function () {
                                            that._setHeight('history');
                                        }, 1000);
                                    })
                                )
                                
                            ).append(
                                $("<li/>").append(
                                    $("<a/>", {
                                        'href': '#extras',
                                        'id': 'extras_tab',
                                        'data-toggle': "tab",
                                        'text': l('Products')
                                    })
                                        .append(
                                            $("<span/>", {
                                                'class': 'extras_count',
                                                'text': " ( " + that.booking.extras_count + " )"
                                            })
                                        )
                                        .on('click', function (e) {
                                            setTimeout(function () {
                                                that._setHeight('extras');
                                            }, 1000);
                                        })
                                )
                            )
                            
                    )
                );
            }

            this._getLinkedGroupBookingRoomList();
            this._updateNumberOfDays();

            $("[name='booking_notes']").val(that.booking.booking_notes);
            $("[name='adult_count']").val(that.booking.adult_count);
            $("[name='children_count']").val(that.booking.children_count);
            $("[name='pay_period']").val(that.booking.pay_period);
            $("[name='rate']").val(that.booking.rate);

            this._updateRoomTypeDDL();
            this._updatePayPeriodDropdown();
            that._updateModalContent();
            this._bookingSource();
            this._bookingFields();
            this._showDecimal();
            this._dailyCharge();

            if (that.booking.state !== '3') {
                that._tokenizeCustomerField(that.booking.staying_customers);
                that._tokenizeBookedByField(that.booking.booked_by);
            }

            // disable checkin, checkout dates for ota bookings
            if (that.booking.source == 2 || that.booking.source == 3) {
                $('[name="check_in_date"]').parent()
                    .attr('data-toggle', "popover")
                    .popover({
                        content: l("Warning: You should not modify this value. Instead, the guest should change it through OTA (e.g. Booking.com)"),
                        placement: 'bottom',
                        trigger: "hover"
                    });
                $('[name="check_out_date"]').parent()
                    .attr('data-toggle', "popover")
                    .popover({
                        content: l("Warning: You should not modify this value. Instead, the guest should change it through OTA (e.g. Booking.com)"),
                        placement: 'bottom',
                        trigger: "hover"
                    });
            }
            that.$modalBody.find("[name='check_in_date'], [name='check_out_date'], [name='rate'], [name='pay_period']").on('change', function () {
                that._displayRateInfo();
            });


            // this is necessary to display booking notes for out of order in bookingModal
            $('.booking-save-btn, #button-check-out').prop('disabled', true);
            $.when(this.deferredRoomDDL, this.deferredChargeWithDDL).done(function () {
                $('.booking-save-btn, #button-check-out').prop('disabled', false);
            });

            $.when(this.deferredBookingSource).done(function () {
                if (that.companyBookingSources.length > 0) {
                    for (var key in that.companyBookingSources) {
                        var source = that.companyBookingSources[key];
                        // remove seasonal.io source
                        if (source['id'] == 15) {
                            if (that.booking.booking_id && that.booking.source == 15) {
                                $('select[name="source"]').append('<option value="' + source['id'] + '">' + l(source['name']) + '</option>');
                            }
                        } else {
                            $('select[name="source"]').append('<option value="' + source['id'] + '">' + l(source['name']) + '</option>');
                        }
                    }
                }
                if (that.booking.source) {
                    $('select[name="source"]').val(that.booking.source);
                }
                var height = $('.content').height() + 50;
                $('.left-sidebar').css('height', height);
            });

            $.when(this.deferredBookingFields).done(function () {
                if (that.customBookingFields) {
                    $.each(that.customBookingFields, function (key, value) {
                        var val = that.booking.custom_booking_fields && that.booking.custom_booking_fields[value.id] ? that.booking.custom_booking_fields[value.id] : "";
                        that._getCustomBookingFieldInput(value.name, "custom_booking_field[]", val, value.id, value.is_required).insertAfter('.booking_notes');
                    });
                }
            });

            if (this.booking.booking_id === undefined) {
                if (that.groupInfo == null) {
                    $("#booking-modal .modal-header").append(
                        $("<div/>", {
                            class: "btn-group pull-right btn-fix-wep",
                            'data-toggle': "buttons",
                            style: "margin-right: 20px;"
                        })
                            .append(
                                $("<label/>", {
                                    class: "btn btn-light active booking_form_type",
                                    text: l("Single")
                                })
                                    .append(
                                        $("<input/>", {
                                            type: "radio",
                                            checked: true,
                                            class: 'single-radio-button',
                                            name: 'booking-type-radio',
                                            value: 'single'
                                        })
                                    ).on("click", function () {
                                    that.selectedGroupType = 'single';
                                    $('.content').find('#booking_detail').replaceWith(that._getBookingTypePanel());
                                    $.when(that._getBookingTypePanel()).done(function () {
                                        that._updateNumberOfDays();
                                        that._updateRoomGroupList();
                                        that._updateRoomTypeDDL();
                                        that._updateColorSelector(that._getDefaultColor());

                                        $('.content').find('#booking_detail').find("[name='adult_count'], [name='children_count'], [name='rate']")
                                            .on('change', function () {
                                                that.booking.state == 3 ? '' : that._validateCapacity();
                                                that._updateRate(that.$modalBody.find('#booking_detail.room-type'));
                                            });

                                        if (that.companyBookingSources.length > 0) {
                                            for (var key in that.companyBookingSources) {
                                                var source = that.companyBookingSources[key];
                                                $('select[name="source"]').append('<option value="' + source['id'] + '">' + source['name'] + '</option>');
                                            }
                                        }
                                        if (that.booking.source) {
                                            $('select[name="source"]').val(that.booking.source);
                                        }

                                        if (that.booking.state !== '3') {
                                            that._tokenizeCustomerField(that.booking.staying_customers);
                                        }

                                        setTimeout(function () {
                                            that._setHeight('booking_detail');
                                        }, 1000);
                                    })
                                })
                            )
                            //                                    .append(
                            //                                            $("<label/>", {
                            //                                                class: "btn btn-light",
                            //                                                text: "Multiple"
                            //                                            })
                            //                                            .append(
                            //                                                    $("<input/>", {
                            //                                                        type: "radio"
                            //                                                    })
                            //                                                    ).on("click", function () {
                            //                                                        that.selectedGroupType = 'group';
                            //                                                        $('.panel-booking').replaceWith(that._getGroupBookingPanel());
                            //                                                        $.when(that._getGroupBookingPanel()).done(function () {
                            //                                                            that._updateNumberOfDays();
                            //                                                            that._updateRoomGroupList();
                            //                                                        })
                            //                                                    })
                            //                                            )
                            .append(
                                $("<label/>", {
                                    class: "btn btn-light booking_form_type",
                                    text: l("Group")
                                })
                                    .append(
                                        $("<input/>", {
                                            type: "radio",
                                            class: 'group-radio-button',
                                            name: 'booking-type-radio',
                                            value: 'group'

                                        })
                                    ).on("click", function () {
                                    if ($('#companyFeatureLimit').val() == 1 && $('#companySubscriptionLevel').val() == STARTER && $('#companySubscriptionState').val() != 'trialing') {
                                        $("#access-restriction-message").modal("show");
                                        $('#access-restriction-message .restriction_message').html('This feature is not active for your current subscription. \n\nPlease upgrade your subscrition to use this feature.');
                                        return false;
                                    }
                                    that.selectedGroupType = 'linked_group';
                                    $('.panel-booking').replaceWith(that._getGroupBookingPanel());
                                    $.when(that._getGroupBookingPanel()).done(function () {
                                        that._updateNumberOfDays();
                                        that._updateRoomGroupList();
                                        $('.content').find('#booking_detail').find('.rate-extra-info-div').html("").remove();
                                    })
                                })
                            )
                    )

                }
            } else {
                //$('div.booked-by-block .tokenfield input.token-input').css('display', 'none');
            }

            var event = new CustomEvent('post.open_booking_modal', { "detail" : {"reservation_id" : that.booking.booking_id,"count": that.booking.extras_count} });
            // Dispatch/Trigger/Fire the event
            document.dispatchEvent(event);

        },
        _updateModalContent: function () {

            this._updateModalHeader();
            this._updateBookingType();
            this._updateColorSelector(this._getDefaultColor());
            this._updateModalFooter();

        },
        _displayRateInfo: function (rateArray = null, taxArray = null, roomTypeDIV = null) {
            var that = this;
            var $parentEle = roomTypeDIV ? roomTypeDIV : this.$modalBody;
            var avgRate = 0;
            var rateCount = 0;
            var totalPreTaxRate = 0, totalInclusivePreTaxRate = 0;
            var totalRate = 0;
            var rateWithTax = 0;
            var numberOfDays = 0;
            var rateNoTax = 0, rateInclusiveTax = 0;
            var payPeriod = 0;

            if (rateArray && taxArray) {
                for (var key in rateArray) {
                    var rate = parseFloat(rateArray[key]['rate']);
                    var taxedRate = rate * (1 + parseFloat(taxArray.percentage)) + parseFloat(taxArray.flat_rate);
                    var inclusiveTaxedRate = rate * (parseFloat(taxArray.inclusive_tax_percentage)) + parseFloat(taxArray.inclusive_tax_flat_rate);

                    rateCount++;
                    avgRate += rate;
                    totalPreTaxRate += rate;
                    totalRate += taxedRate;
                    totalInclusivePreTaxRate += inclusiveTaxedRate;
                }
                avgRate = avgRate / rateCount;
            } else {
                rateWithTax = parseFloat(this.rateWithTax);

                numberOfDays = parseInt(this.$modalBody.find('input[name="number_of_days"]').val());
                rateNoTax = parseFloat($parentEle.find('input[name="rate"]').val());
                payPeriod = $parentEle.find('select[name="pay_period"]').val();
                avgRate = rateNoTax;
                totalPreTaxRate = numberOfDays * rateNoTax;
                totalRate = numberOfDays * rateWithTax;

                rateInclusiveTax = parseFloat(this.rateInclusiveTax);
                totalInclusivePreTaxRate = numberOfDays * rateInclusiveTax;

                if (payPeriod == 1) // weekly
                {
                    avgRate = (rateNoTax / 7);
                    totalPreTaxRate = (Math.floor(numberOfDays / 7) * rateNoTax) + (add_remaining_daily_charges ? ((rateNoTax / 7) * (numberOfDays % 7)) : 0);
                    totalInclusivePreTaxRate = (Math.floor(numberOfDays / 7) * rateInclusiveTax) + (add_remaining_daily_charges ? ((rateInclusiveTax / 7) * (numberOfDays % 7)) : 0);
                    totalRate = (Math.floor(numberOfDays / 7) * rateWithTax) + (add_remaining_daily_charges ? ((rateWithTax / 7) * (numberOfDays % 7)) : 0);
                } else if (payPeriod == 2) // monthly
                {
                    var checkInDate = new Date(innGrid._getBaseFormattedDate($parentEle.find('input[name="check_in_date"]').val()));
                    var checkOutDate = new Date(innGrid._getBaseFormattedDate($parentEle.find('input[name="check_out_date"]').val()));
                    if (!checkInDate || !checkOutDate) {
                        return;
                    }
                    totalRate = totalPreTaxRate = totalInclusivePreTaxRate = 0;
                    var lastPeriodDate = new Date(checkInDate.getTime());
                    var date = new Date(checkInDate.getTime());

                    date.setMonth(date.getMonth() + 1);
                    for (date = date; date <= checkOutDate; date.setMonth(date.getMonth() + 1)) {
                        lastPeriodDate = new Date(date.getTime());
                        totalPreTaxRate += rateNoTax;
                        totalRate += rateWithTax;
                        totalInclusivePreTaxRate += rateInclusiveTax;
                    }

                    var dayDiff = parseInt((checkOutDate - lastPeriodDate) / (1000 * 60 * 60 * 24));
                    if (dayDiff > 0 && add_remaining_daily_charges) {
                        totalPreTaxRate += ((rateNoTax / 30) * dayDiff);
                        totalRate += ((rateWithTax / 30) * dayDiff);
                        totalInclusivePreTaxRate += ((rateInclusiveTax / 30) * dayDiff);
                    }
                    avgRate = (totalPreTaxRate / numberOfDays);
                }
            }
            totalPreTaxRate -= totalInclusivePreTaxRate;

            // console.log(number_format(number_format(totalRate, 3, ".", ""), 2, ".", ""));
            totalRate = number_format(number_format(totalRate, 3, ".", ""), 2, ".", "");

            if (avgRate >= 0) {
                if (payPeriod != 3) // one time charge
                {
                    var avgGroupRateBlock = $("<div/>", {
                        class: "col-sm-12 form-group rate-extra-parent-div"
                    }).append(
                        $("<div/>", {
                            class: "clearfix rate-extra-info-div",
                        })
                            .append(
                                $("<div/>", {
                                    class: "col-sm-6 width-fix-wep",
                                    //style: "padding-left: 0px;"
                                }).append(
                                    $("<label/>", {
                                        class: "",
                                    })
                                        .append(
                                            $("<small/>", {
                                                class: "",
                                                text: l('average_rate'),
                                                style: "text-align: right;"
                                            })
                                        )
                                ).append(
                                    $("<span/>", {
                                        class: "input-group-addon",
                                        html: number_format(avgRate, 2, ".", ""),
                                        style: "line-height: 20px;border:  1px solid #ccc;border-radius: 4px;text-align: left;"
                                    })
                                )
                            )
                            .append(
                                $("<div/>", {
                                    class: "col-sm-6 per-txt-fix",
                                    style: "padding: 0;"
                                }).append(
                                    $("<label/>", {
                                        class: "",
                                        style: "padding-left: 15px;"
                                    })
                                        .append(
                                            $("<small/>", {
                                                text: l('total_pre_tax')
                                            })
                                        )
                                )
                                    .append(
                                        $("<span/>", {
                                            class: "col-sm-12",
                                            style: "padding-right: 0px;"
                                        })
                                            .append(
                                                $("<span/>", {
                                                    class: "input-group-addon",
                                                    text: number_format(totalPreTaxRate, 2, ".", ""),
                                                    style: "line-height: 20px;border: 1px solid #ccc;"
                                                })
                                            ).append(
                                            $("<span/>", {
                                                class: "input-group-addon",
                                                html: "("+l('with tax')+": " + number_format(totalRate, 2, ".", "") + ")"
                                            })
                                        )
                                    )
                            )
                    );

                    var avgSingleRateBlock = $("<div/>", {
                        class: "clearfix rate-extra-info-div",
                        style: "margin-bottom: 20px;"
                    }).append(
                        $("<div/>", {
                            class: "col-sm-6",
                            style: "padding-left: 0px;"
                        }).append(
                            $("<label/>", {
                                class: "",
                            })
                                .append(
                                    $("<small/>", {
                                        class: "",
                                        text: l('average_rate'),
                                        style: "text-align: right;"
                                    })
                                )
                        ).append(
                            $("<span/>", {
                                class: "input-group-addon",
                                //html: number_format(avgRate, 2, ".", ""),
                                html: ((show_decimal) ? ((parseFloat(avgRate)).toLocaleString('en', {
                                    style: 'decimal',
                                    maximumFractionDigits: 2,
                                    minimumFractionDigits: 2
                                })) : ((parseInt(avgRate)).toLocaleString())),
                                style: "line-height: 20px;border:  1px solid #ccc;border-radius: 4px;text-align: left;"
                            })
                        )
                    ).append(
                        $("<div/>", {
                            class: "col-sm-6",
                            style: "padding: 0;"
                        }).append(
                            $("<label/>", {
                                class: "",
                                style: "padding-left: 15px;"
                            })
                                .append(
                                    $("<small/>", {
                                        text: l('total_pre_tax')
                                    })
                                )
                        )
                            .append(
                                $("<span/>", {
                                    class: "col-sm-12",
                                    style: "padding-right: 0px;"
                                })
                                    .append(
                                        $("<span/>", {
                                            class: "input-group-addon",
                                            //text: number_format(totalPreTaxRate, 2, ".", ""),
                                            text: ((show_decimal) ? ((parseFloat(totalPreTaxRate)).toLocaleString('en', {
                                                style: 'decimal',
                                                maximumFractionDigits: 2,
                                                minimumFractionDigits: 2
                                            })) : ((parseInt(totalPreTaxRate)).toLocaleString())),
                                            style: "line-height: 20px;border: 1px solid #ccc;"
                                        })
                                    ).append(
                                    $("<span/>", {
                                        class: "input-group-addon",
                                        //html: "(with tax: "+number_format(totalRate, 2, ".", "")+")"
                                        html: "("+l('with tax')+": " + ((show_decimal) ? ((parseFloat(totalRate)).toLocaleString('en', {
                                            style: 'decimal',
                                            maximumFractionDigits: 2,
                                            minimumFractionDigits: 2
                                        })) : ((parseInt(totalRate)).toLocaleString())) + ")"
                                    })
                                )
                            )
                    );

                    if ($('input[name=booking-type-radio]:checked').val() == 'group') {
                        $parentEle.find('.rate-extra-parent-div').html('').remove();
                        $parentEle.append(avgGroupRateBlock);
                    } else {
                        $parentEle.find('.rate-extra-info-div').html('').remove();
                        this.$modalBody.find('.room-type').find('.booking_notes').prepend(avgSingleRateBlock);
                    }
                } else {
                    this.$modalBody.find('.rate-extra-info-div').remove();
                }
            }
        },
        _populateNewBookingModal: function () {
            var that = this;
            $("#booking-modal").find(".modal-content").html("");

            // get extras for new booking
            if (!innGrid.ajaxCache.extras) {
                $.getJSON(getBaseURL() + 'extra/get_all_extras_JSON',
                    function (data) {
                        that.extras = data;
                        innGrid.ajaxCache.extras = data;
                    }
                );
            } else {
                that.extras = innGrid.ajaxCache.extras;
            }
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

                    bookingDetails['booking'] = data.booking; // set globaly booking data
                    bookingDetails['extras'] = data.extras; // set globaly booking data
                    bookingDetails['group_info'] = data.group_info; // set globaly booking data

                    that.booking = data.booking;
                    that.extras = data.extras; // get all extras types that belong to company
                    that.groupInfo = data.group_info;

                    var ratePlanKey = that.booking.current_room_type_id + '-' + that.booking.rate_plan_id;
                    that.ratePlanCache[ratePlanKey] = data.rate_plan;

                    var roomTypeKey = innGrid._getBaseFormattedDate(that.booking.check_in_date) + '-' + innGrid._getBaseFormattedDate(that.booking.check_out_date);
                    that.roomTypesCache[roomTypeKey] = data.available_room_types;

                    var roomKey = that.booking.check_in_date + '-' + that.booking.check_out_date + '-' + that.booking.current_room_type_id + '-' + that.booking.booking_id + '-' + that.booking.current_room_id;
                    that.roomsCache[roomKey] = data.available_rooms;

                    that.booking.staying_customers.unshift(that.booking.paying_customer);
                    that._constructModalComponents();
                    $('#booking_detail').find('.token-input').css('width', '0px');
                    $('#booking_detail').find('.token-input').css('min-width', '0px');

                    if (data.booking.pay_period == 1 || data.booking.pay_period == 2) {
                        $('.add-daily-charges-div').show();
                        $('.add-daily-charges-div').parents('.pay-period-block').removeClass('col-sm-2').addClass('col-sm-3').prev().removeClass('col-sm-4').addClass('col-sm-3');
                        $('.add-daily-charges-div').parent('div').removeClass('form-group').addClass('input-group');
                    }

                    if (data.booking.state == 3) {
                        $('.guest_fields_row').addClass('hidden');
                    }

                    if (data.booking.state == 0 && !data.allow_state_change) {
                        $('select[name="state"]').attr("disabled","true");
                        $('#button-check-in').prop("disabled", true);
                    }
                    //alert(data.allow_change_state);
                    if (data.allow_change_state == 0) {
                        var d = new Date();
                        var month = d.getMonth()+1;
                    var day = d.getDate();

                    var output = d.getFullYear() + '-' +
                    ((''+month).length < 2 ? '0' : '') + month + '-' +
                    ((''+day).length < 2 ? '0' : '') + day;
                        if(data.booking.check_out_date < output){
                        $('select[name="state"]').attr("disabled","true");
                        }
                    }

                    if(that.groupInfo && that.groupInfo.total_room_count && that.groupInfo.total_room_count != 0){
                        console.log('rm', that.groupInfo.total_room_count);
                        console.log('guest', that.groupInfo.total_guest_count);
                        $('.total_rm_count').text(that.groupInfo.total_room_count);
                        $('.total_customer_count').text(that.groupInfo.total_guest_count);
                    } else {
                        $('.total_rm_count').parent('span').hide();
                        $('.total_customer_count').parent('span').hide();
                        $('.total_group_booking_counts').hide();
                    }
                },
                error: function () {
                    console.log("booking not accesible for this company/user");
                    //that._closeBookingModal();
                }

            }); // -- ajax call
        },
        _getBookingTypePanel: function () {
            var that = this;
            var state = this.booking.state;

            var check_in_date = innGrid._getLocalFormattedDate(that.booking.check_in_date ? that.booking.check_in_date : $("#sellingDate").val());
            var check_out_date = that.booking.check_out_date ? innGrid._getLocalFormattedDate(that.booking.check_out_date) : '';
            var check_in_time = moment(that.booking.check_in_date ? that.booking.check_in_date : $("#sellingDate").val()).format('hh:mm A');
            var check_out_time = that.booking.check_out_date ? moment(that.booking.check_out_date).format('hh:mm A') : '12:00 AM';

            if (!that.booking.booking_id && innGrid.calendar.view && innGrid.calendar.view.type === "customMonthView") {
                check_in_time = $('#CheckInTime').val();
                check_in_time = check_in_time ? check_in_time : '12:00 AM';
                check_out_time = $('#CheckOutTime').val();
                check_out_time = check_out_time ? check_out_time : '12:00 AM';
            }

            var panel = this.$panel.clone();

            var sourceSelectDiv = $("<select/>", {
                class: "form-control select-fix-wep",
                name: "source"
            });

            var today = new Date();
            var year = today.getFullYear();
            var month = ("0" + (today.getMonth() + 1)).slice(-2);
            var day = ("0" + today.getDate()).slice(-2);
            today = year + '-' + month + '-' + day;
            $.each(this.sources, function (i, source) {
                var option = $("<option/>", {
                    value: source.id,
                    text: source.name
                });

                if (source.id == that.booking.source) {
                    option.prop("selected", "selected");
                }

                sourceSelectDiv.append(option);
            });

            if (that.booking.source === '15') {
                sourceSelectDiv.attr("disabled", "disabled");
            }

            if (that.groupInfo != null && that.booking.state == 4) {
                that.disableRoomBlock = 'cursor:not-allowed;background:#f2f2f2;pointer-events:none;';
            }

            var bookingSourceClass = 'col-sm-12';
            var bookedByClass = 'hidden';
            if (that.groupInfo != null) {
                bookingSourceClass = 'col-sm-6';
                bookedByClass = '';
            }
            var disableDates = false;
            if (state == '5') {
                disableDates = true;
            }

            panel.find(".panel-body")
                .append(
                    $("<div/>", {
                        class: "tab-content tab-gap-wep",
                        //style: "padding: 0px 15px 0px 0px;"
                    })
                        .append(
                            $("<div/>", {
                                'id': 'booking_detail',
                                'class': "tab-pane active room-type"
                            }).append(
                                $("<div/>", {
                                    class: "col-sm-12 booking-buttons"
                                })
                            )
                                .append(
                                    $("<div/>", {
                                        class: "form-group col-sm-4"
                                    })
                                        .append(
                                            $("<label/>")
                                                .append(
                                                    $("<small/>", {
                                                        text: l('booking_type')
                                                    })
                                                )
                                        )
                                        .append(this._getBookingTypes(state))
                                )
                                .append(
                                    $("<div/>", {
                                        class: "form-group col-sm-2"
                                    })
                                        .append(
                                            $("<label/>", {
                                                html: "&nbsp;"
                                            })
                                        )
                                        .append(this._getColorPicker())
                                )
                                .append(
                                    $("<div/>", {
                                        class: "form-group col-sm-6",
                                        style: "padding: 0"
                                    }).append(
                                        $("<div/>", {
                                            class: "form-group booking-source-block " + bookingSourceClass
                                        })
                                            .append(
                                                $("<label/>", {
                                                    for: "booking_source",
                                                })
                                                    .append(
                                                        $("<small/>", {
                                                            for: "state",
                                                            text: l('booking_source')
                                                        })
                                                    )
                                            )
                                            .append(sourceSelectDiv)
                                    )
                                        .append(
                                            $("<div/>", {
                                                class: "form-group col-sm-6 booked-by-block " + bookedByClass,
                                                style: "padding: 0 15px 0 0px;"
                                            }).append(
                                                $("<label/>", {
                                                    for: "booked_by",
                                                })
                                                    .append(
                                                        $("<small/>", {
                                                            for: "booked_by",
                                                            text: l('Booked By')
                                                        })
                                                    )
                                            )
                                                .append(
                                                    $("<input/>", {
                                                        class: "form-control booked_by",
                                                        //name: "customers",
                                                        booked_by: '1',
                                                        rows: 1
                                                    })
                                                )
                                        )
                                )
                                .append(
                                    $("<div/>", {
                                        class: "guest_fields_row",
                                        style: "display: table;width: 100%;"
                                    })
                                        .append(
                                            $("<div/>", {
                                                class: "form-group col-sm-6 guest-block"
                                            })
                                                .append(
                                                    $("<label/>", {
                                                        for: "guests",
                                                    })
                                                        .append(
                                                            $("<small/>", {
                                                                text: l('guest')
                                                            })
                                                        )
                                                )
                                                .append(
                                                    $("<input/>", {
                                                        class: "form-control",
                                                        name: "customers",
                                                        rows: 1
                                                    })
                                                )
                                        )
                                        .append(
                                            $("<div/>", {
                                                class: "adult-child-block"
                                            })
                                                .append(
                                                    $("<div/>", {
                                                        class: "form-group col-sm-3 adult-count"
                                                    })
                                                        .append(
                                                            $("<label/>", {
                                                                html: "&nbsp;"
                                                            })
                                                        )
                                                        .append(this._getSelect('adult_count', this.adultsCount))
                                                )
                                                .append(
                                                    $("<div/>", {
                                                        class: "form-group col-sm-3 children-count"
                                                    })
                                                        .append(
                                                            $("<label/>", {
                                                                html: "&nbsp;"
                                                            })
                                                        )
                                                        .append(this._getSelect('children_count', this.childrenCount))
                                                )
                                        )
                                )
                                .append(
                                    $("<div/>", {
                                        class: "panel-booking clearfix",
                                        style: that.disableRoomBlock
                                    })
                                        //check -in start
                                        .append(
                                            $("<div/>", {
                                                class: "form-group col-sm-6"
                                            })
                                                .append(
                                                    $("<label/>", {
                                                        for: "checkin-date",
                                                    })
                                                        .append(
                                                            $("<small/>", {
                                                                text: l('check_in_date')
                                                            })
                                                        )
                                                )
                                                .append($('<span/>', {style: "color:red;", text: "*"}))
                                                .append(
                                                    $('<div/>', {class: innGrid.enableHourlyBooking ? 'hourly-booking-enabled' : ''})
                                                        .append(
                                                            $("<input/>", {
                                                                name: 'check_in_date',
                                                                class: 'form-control check-in-date-wrapper',
                                                                placeholder: "Check-in Date",
                                                                value: check_in_date,
                                                                disabled: disableDates
                                                            }).datepicker({
                                                                dateFormat: ($('#companyDateFormat').val()).toLowerCase(),
                                                                beforeShow: that._customRange
                                                            }).on('change', function () {
                                                                var checkInDate = innGrid._getBaseFormattedDate($("input[name=check_in_date]").val());
                                                                var checkOutDate = innGrid._getBaseFormattedDate($("input[name=check_out_date]").val());
                                                                var roomId = $(".room option:selected").val();
                                                                var bookingId = that.booking.booking_id;

                                                                $.ajax({
                                                                    type: "POST",
                                                                    url: getBaseURL() + 'booking/check_overbooking_AJAX',
                                                                    dataType: 'json',
                                                                    data: {
                                                                        check_in_date: checkInDate,
                                                                        check_out_date: checkOutDate,
                                                                        room_id: roomId,
                                                                        booking_id: bookingId
                                                                    },
                                                                    success: function (response) {
                                                                        if (response.success) {
                                                                            $('#reservation-message').find('.confirm-customer[flag=cancel]').html(l("Revert Dates")).removeClass('hidden');
                                                                            $('#reservation-message').find('.confirm-customer[flag=ok]').html(l("Keep The New Dates"));
                                                                            $('#reservation-message').modal("show");
                                                                            $('#reservation-message .message').html(l("This room is not available for the entire stay."));
                                                                            $('.confirm-customer').on('click', function () {
                                                                                var flag = $(this).attr('flag');
                                                                                if (flag == 'cancel') {
                                                                                    $("input[name='check_in_date']").val(innGrid._getLocalFormattedDate(that.booking.check_in_date));
                                                                                    that._updateNumberOfDays();
                                                                                }
                                                                                $('#reservation-message').modal('hide');
                                                                                $('#reservation-message').on('hidden.bs.modal', function () {
                                                                                    $('body').addClass('modal-open');
                                                                                });
                                                                                $('#reservation-message').find('.confirm-customer[flag=cancel]').html(l("Cancel")).addClass('hidden');
                                                                                $('#reservation-message').find('.confirm-customer[flag=ok]').html(l("OK"));
                                                                                return false;
                                                                            });
                                                                        }
                                                                    }
                                                                });

                                                                if (that.groupInfo != null && that.booking.booking_id != null) {
                                                                    if (that.saveAllGroupDate == null) {
                                                                        that._confirmationGroupDateModel(this);
                                                                    }
                                                                }
                                                                setTimeout(function () {
                                                                    $(document).find("[name='check_out_date']").focus();
                                                                }, 200);
                                                                that._updateNumberOfDays();
                                                                that._updateRoomTypeDDL();
                                                            })
                                                        )
                                                        .append(
                                                            $("<select/>", {
                                                                name: 'check_in_time',
                                                                class: 'form-control check-in-time-wrapper',
                                                                disabled: disableDates
                                                            })
                                                                .append(timeOptions)
                                                                .val(check_in_time)
                                                                .on('change', function () {
                                                                    var checkInDate = innGrid._getBaseFormattedDate($("input[name=check_in_date]").val());
                                                                    var checkOutDate = innGrid._getBaseFormattedDate($("input[name=check_out_date]").val());
                                                                    var roomId = $(".room option:selected").val();
                                                                    var bookingId = that.booking.booking_id;

                                                                    $.ajax({
                                                                        type: "POST",
                                                                        url: getBaseURL() + 'booking/check_overbooking_AJAX',
                                                                        dataType: 'json',
                                                                        data: {
                                                                            check_in_date: checkInDate,
                                                                            check_out_date: checkOutDate,
                                                                            room_id: roomId,
                                                                            booking_id: bookingId
                                                                        },
                                                                        success: function (response) {
                                                                            if (response.success) {
                                                                                $('#reservation-message').modal("show");
                                                                                $('#reservation-message .message').html(l("This room is not available for the entire stay."));
                                                                                $('.confirm-customer').on('click', function () {
                                                                                    $('#reservation-message').modal('hide');
                                                                                    $("input[name='check_in_date']").val(innGrid._getLocalFormattedDate(that.booking.check_in_date));
                                                                                    $('#reservation-message').on('hidden.bs.modal', function () {
                                                                                        $('body').addClass('modal-open');
                                                                                    });
                                                                                    return false;
                                                                                });
                                                                            }
                                                                        }
                                                                    });

                                                                    if (that.groupInfo != null && that.booking.booking_id != null) {
                                                                        if (that.saveAllGroupDate == null) {
                                                                            that._confirmationGroupDateModel(this);
                                                                        }
                                                                    }
                                                                    setTimeout(function () {
                                                                        $(document).find("[name='check_out_date']").focus();
                                                                    }, 200);
                                                                    that._updateNumberOfDays();
                                                                    that._updateRoomTypeDDL();
                                                                })
                                                        )
                                                )
                                        ) //check -in end
                                        .append(
                                            $("<div/>", {
                                                class: "form-group col-sm-6"
                                            })
                                                .append(
                                                    $("<label/>")
                                                        .append(
                                                            $("<small/>", {
                                                                for: "state",
                                                                text: l('check_out_date')
                                                            })
                                                        )
                                                )
                                                .append($('<span/>', {style: "color:red;", text: "*"}))
                                                .append(
                                                    $("<input/>", {
                                                        type: 'hidden',
                                                        name: 'old_check_out_date',
                                                        id: 'old_check_out_date',
                                                        value: check_out_date
                                                    })
                                                )
                                                .append(
                                                    $('<div/>', {class: innGrid.enableHourlyBooking ? 'hourly-booking-enabled' : ''})
                                                        .append(
                                                            $("<input/>", {
                                                                name: 'check_out_date',
                                                                class: 'form-control check-out-date-wrapper',
                                                                placeholder: l("Check-out Date"),
                                                                value: check_out_date,
                                                                disabled: disableDates
                                                            }).datepicker({
                                                                dateFormat: ($('#companyDateFormat').val()).toLowerCase(),
                                                                beforeShow: that._customRange
                                                            }).on('change', function () {

                                                                var checkOutDate = innGrid._getBaseFormattedDate($("input[name=check_out_date]").val());
                                                                var checkInDate = innGrid._getBaseFormattedDate($("input[name=check_in_date]").val());
                                                                var roomId = $(".room option:selected").val();
                                                                var bookingId = that.booking.booking_id;

                                                                $.ajax({
                                                                    type: "POST",
                                                                    url: getBaseURL() + 'booking/check_overbooking_AJAX',
                                                                    data: {
                                                                        check_in_date: checkInDate,
                                                                        check_out_date: checkOutDate,
                                                                        room_id: roomId,
                                                                        booking_id: bookingId
                                                                    },
                                                                    dataType: 'json',
                                                                    success: function (response) {
                                                                        if (response.success) {
                                                                            $('#reservation-message').find('.confirm-customer[flag=cancel]').html(l("Revert Dates")).removeClass('hidden');
                                                                            $('#reservation-message').find('.confirm-customer[flag=ok]').html(l("Keep The New Dates"));
                                                                            $('#reservation-message').modal("show");
                                                                            $('#reservation-message .message').html(l("This room is not available for the entire stay."));
                                                                            $('.confirm-customer').on('click', function () {
                                                                                var flag = $(this).attr('flag');
                                                                                if (flag == 'cancel') {
                                                                                    $("input[name='check_out_date']").val(innGrid._getLocalFormattedDate(that.booking.check_out_date));
                                                                                    that._updateNumberOfDays();
                                                                                }
                                                                                $('#reservation-message').modal('hide');
                                                                                $('#reservation-message').on('hidden.bs.modal', function () {
                                                                                    $('body').addClass('modal-open');
                                                                                });
                                                                                $('#reservation-message').find('.confirm-customer[flag=cancel]').html(l("Cancel")).addClass('hidden');
                                                                                $('#reservation-message').find('.confirm-customer[flag=ok]').html(l("OK"));
                                                                                return false;
                                                                            });
                                                                        }
                                                                    }
                                                                });

                                                                if (that.groupInfo != null) {
                                                                    if (that.saveAllGroupDate == null && that.booking.booking_id != null) {
                                                                        that._confirmationGroupDateModel(this);
                                                                    }
                                                                }

                                                                if (new Date(innGrid._getBaseFormattedDate(this.value)) < new Date(innGrid._getBaseFormattedDate($("[name='check_in_date']").val()))) {
                                                                    $('#reservation-message .message').html(l("Check-out-date can't be less than Check-in-date"));
                                                                    $('#reservation-message').modal("show");
                                                                    $('.confirm-customer').on('click', function () {
                                                                        $('#reservation-message').modal('hide');
                                                                        return false;
                                                                    });
                                                                    //alert("Check-out-date can't be less than Check-in-date");
                                                                    $("[name='check_out_date']").val($("[name='check_in_date']").val());
                                                                    $("[name='check_out_date']").focus();
                                                                }
                                                                that._updateNumberOfDays();
                                                                that._updateRoomTypeDDL();
                                                            })
                                                        )
                                                        .append(
                                                            $("<select/>", {
                                                                name: 'check_out_time',
                                                                class: 'form-control check-out-time-wrapper',
                                                                disabled: disableDates
                                                            })
                                                                .append(timeOptions)
                                                                .val(check_out_time)
                                                                .on('change', function () {
                                                                    var checkOutDate = innGrid._getBaseFormattedDate($("input[name=check_out_date]").val());
                                                                    var checkInDate = innGrid._getBaseFormattedDate($("input[name=check_in_date]").val());
                                                                    var roomId = $(".room option:selected").val();
                                                                    var bookingId = that.booking.booking_id;

                                                                    $.ajax({
                                                                        type: "POST",
                                                                        url: getBaseURL() + 'booking/check_overbooking_AJAX',
                                                                        data: {
                                                                            check_in_date: checkInDate,
                                                                            check_out_date: checkOutDate,
                                                                            room_id: roomId,
                                                                            booking_id: bookingId
                                                                        },
                                                                        dataType: 'json',
                                                                        success: function (response) {
                                                                            if (response.success) {
                                                                                $('#reservation-message').modal("show");
                                                                                $('#reservation-message .message').html(l("This room is not available for the entire stay."));
                                                                                $('.confirm-customer').on('click', function () {
                                                                                    $('#reservation-message').modal('hide');
                                                                                    $("input[name='check_out_date']").val(innGrid._getLocalFormattedDate(that.booking.check_out_date));
                                                                                    $('#reservation-message').on('hidden.bs.modal', function () {
                                                                                        $('body').addClass('modal-open');
                                                                                    });
                                                                                    return false;
                                                                                });
                                                                            }
                                                                        }
                                                                    });

                                                                    if (that.groupInfo != null) {
                                                                        if (that.saveAllGroupDate == null && that.booking.booking_id != null) {
                                                                            that._confirmationGroupDateModel(this);
                                                                        }
                                                                    }

                                                                    if (new Date(innGrid._getBaseFormattedDate(this.value)) < new Date(innGrid._getBaseFormattedDate($("[name='check_in_date']").val()))) {
                                                                        $('#reservation-message .message').html(l("Check-out-date can't be less than Check-in-date"));
                                                                        $('#reservation-message').modal("show");
                                                                        $('.confirm-customer').on('click', function () {
                                                                            $('#reservation-message').modal('hide');
                                                                            return false;
                                                                        });
                                                                        //alert("Check-out-date can't be less than Check-in-date");
                                                                        $("[name='check_out_date']").val($("[name='check_in_date']").val());
                                                                        $("[name='check_out_date']").focus();
                                                                    }
                                                                    that._updateNumberOfDays();
                                                                    that._updateRoomTypeDDL();
                                                                })
                                                        )
                                                )
                                        )//check -out end
                                        .append(
                                            $("<div/>", {
                                                class: "room-section " + (that.booking.booking_id ? '' : 'hidden')
                                            })
                                                .append(
                                                    $("<div/>", {
                                                        class: "form-group col-sm-6"
                                                    })
                                                        .append(
                                                            $("<label/>", {
                                                                for: "room-type",
                                                            })
                                                                .append(
                                                                    $("<small/>", {
                                                                        for: "state",
                                                                        text: l(innGrid.featureSettings.defaultRoomType)
                                                                    })
                                                                )
                                                        ).append(
                                                        $("<div/>", {
                                                            class: 'room-type-ddl-span'
                                                        })
                                                    )
                                                )// room type end
                                                .append(
                                                    $("<div/>", {
                                                        class: "form-group col-sm-3"
                                                    })
                                                        .append(
                                                            $("<label/>", {
                                                                for: "room",
                                                            })
                                                                .append(
                                                                    $("<small/>", {
                                                                        for: "state",
                                                                        text: l(innGrid.featureSettings.defaultRoomSingular)
                                                                    })
                                                                )
                                                        )
                                                        .append($('<span/>', {style: "color:red;", text: " * "}))
                                                        .append(
                                                            $("<span/>", {
                                                                class: 'room-ddl-span'
                                                            })
                                                        )
                                                )// room end
                                                .append(
                                                    $("<div/>", {
                                                        class: "form-group col-sm-3"
                                                    })
                                                        .append(
                                                            $("<label/>", {
                                                                for: "no-of-days",
                                                            })
                                                                .append(
                                                                    $("<small/>", {
                                                                        for: "state",
                                                                        text: l('no_of_days')
                                                                    })
                                                                )
                                                        )
                                                        .append(
                                                            $("<input/>", {
                                                                name: 'number_of_days',
                                                                class: 'form-control',
                                                                maxlength: '3'
                                                            }).on("change", function () {
                                                                // set number of days as check_out_date - check_in_date
                                                                var cid = innGrid._getBaseFormattedDate($("[name='check_in_date']").val()).split(/[-]/);
                                                                var number_of_days = $("[name='number_of_days']").val();
                                                                if (number_of_days == "") {
                                                                    $("[name='check_out_date']").val("");
                                                                } else {
                                                                    number_of_days = parseInt(number_of_days);
                                                                    // Apply each element to the Date function
                                                                    var check_out_date = new Date(cid[0], cid[1] - 1, cid[2]);
                                                                    check_out_date.setDate(check_out_date.getDate() + number_of_days);
                                                                    var year = check_out_date.getFullYear();
                                                                    var month = ("0" + (check_out_date.getMonth() + 1)).slice(-2);
                                                                    var day = ("0" + check_out_date.getDate()).slice(-2);
                                                                    $("[name='check_out_date']").val(innGrid._getLocalFormattedDate(year + '-' + month + '-' + day));
                                                                }
                                                                that._updatePayPeriodDropdown();
                                                            })
                                                        )
                                                )// no-of days end
                                                .append(
                                                    $("<div/>", {
                                                        class: "form-group col-sm-4"
                                                    })
                                                        .append(
                                                            $("<label/>", {
                                                                for: "charge-type",
                                                            })
                                                                .append(
                                                                    $("<small/>", {
                                                                        text: l('charge_type')
                                                                    })
                                                                )
                                                        )
                                                        .append(
                                                            $("<span/>", {
                                                                class: 'charge-with-div'
                                                            })
                                                        )
                                                )// charge type end
                                                .append(
                                                    $("<div/>", {
                                                        class: "form-group col-sm-2 pay-period-block",
                                                        style: "margin-top: 25px; padding-left: 4px;"
                                                    })
                                                        .append(
                                                            $("<div/>", {
                                                                class: "form-group"
                                                            })
                                                                .append(
                                                                    this._getSelect('pay_period', this.payPeriods, 'pay_period').on('change', function () {
                                                                        that._showDailyChargeSetting();
                                                                    })
                                                                )//pay period ends
                                                                .append(
                                                                    $("<span/>", {
                                                                        class: 'input-group-addon add-daily-charges-div',
                                                                        style: "display:none;"
                                                                    })
                                                                        .append(
                                                                            $("<i/>", {
                                                                                class: 'fa fa-cog',
                                                                                'aria-hidden': true
                                                                            })
                                                                        ).on('click', function () {
                                                                        $('.daily_charge_msg').hide();
                                                                        if (that.booking.add_daily_charge != undefined && that.booking.add_daily_charge != 1) {
                                                                            add_remaining_daily_charges = false;
                                                                            $('.add-daily-charge').prop('checked', false);
                                                                            $("input[name='residual_rate']").val(0);
                                                                            $('#residual_rate_div').addClass('hidden');
                                                                        } else {
                                                                            add_remaining_daily_charges = true;
                                                                            $('.add-daily-charge').prop('checked', true);
                                                                            $("input[name='residual_rate']").val(that.booking.residual_rate);
                                                                            $('#residual_rate_div').removeClass('hidden');
                                                                        }
                                                                        $('#add-daily-charges-modal').modal('show');
                                                                    })
                                                                )
                                                        )
                                                )
                                                .append(
                                                    $("<div/>", {
                                                        class: "form-group col-sm-6 rate-block",
                                                        //style: "padding-right: 0px;"
                                                    })
                                                        .append(
                                                            $("<label/>", {
                                                                for: "rate",
                                                            })
                                                                .append(
                                                                    $("<small/>", {
                                                                        text: l('rate')
                                                                    })
                                                                )
                                                        )
                                                        .append(
                                                            $("<div/>", {
                                                                class: "input-group",
                                                                style: "padding: 1px"
                                                            })
                                                                .append(
                                                                    $("<span/>", {
                                                                        class: 'input-group-addon edit-rate-btn',
                                                                        //style: 'padding: 2px 15px;',
                                                                        //text: 'Edit'
                                                                    })
                                                                        .append(
                                                                            $("<i/>", {
                                                                                class: 'fa fa-pencil-square-o',
                                                                                'aria-hidden': true
                                                                            })
                                                                        )
                                                                )
                                                                .append(
                                                                    $("<input/>", {
                                                                        name: 'rate',
                                                                        class: 'form-control',
                                                                        placeholder: "Rate",
                                                                        value: 0,
                                                                        type: 'number',
                                                                    })
                                                                )
                                                                .append(
                                                                    $('<span/>', {
                                                                        class: 'input-group-addon rate-including-tax hidden',
                                                                        style: "padding: 2px;"
                                                                    })
                                                                )
                                                        )
                                                )// rate end


                                        )
                                )
                                .append(
                                    $("<div/>", {
                                        class: "form-group col-sm-12 booking_notes"
                                    })
                                        .append(
                                            $("<label/>", {
                                                for: "state",
                                            })
                                                .append(
                                                    $("<small/>", {
                                                        text: l('booking_notes')
                                                    })
                                                )
                                        )
                                        .append(
                                            $("<textarea/>", {
                                                class: "form-control restrict-cc-data",
                                                name: "booking_notes",
                                                'data-label': 'booking notes',
                                                rows: 4,
                                                placeholder: l("Max. 2000 characters"),
                                                maxlength: 2000,
                                                text: that.booking.booking_notes
                                            }).on('keyup', function () {
                                                if (this.value.length >= 2000) {
                                                    $('.booking_notes_error').show();
                                                } else {
                                                    $('.booking_notes_error').hide();
                                                }
                                            })
                                        ).append(
                                        $("<div/>", {
                                            class: "form-group col-sm-12 booking_notes_error",
                                            style: "color:red; top:10px; margin:0px -14px; display:none;",
                                            text: l('Note: Exceeds maximum character limit of')+" 2000"
                                        })
                                    )
                                )
                        )
                        .append(
                            $("<div/>", {
                                'id': 'rate_schedule',
                                'class': "tab-pane"
                            }).append(
                                $("<table/>", {
                                    'class': "table"
                                }).append(
                                    $("<tr/>", {}).append(
                                        $("<th/>", {
                                            'text': l('date')
                                        })
                                    ).append(
                                        $("<th/>", {
                                            'text': l('room_details')
                                        })
                                    ).append(
                                        $("<th/>", {
                                            'text': l('rate')
                                        })
                                    )
                                )
                                    .append(
                                        $("<input/>", {
                                            'type': "hidden",
                                            'name': "check_in_date",
                                            'value': that.booking.check_in_date
                                        })
                                    ).append(
                                    $("<input/>", {
                                        'type': "hidden",
                                        'name': "check_out_date",
                                        'value': that.booking.check_out_date
                                    })
                                )
                            )
                        )
                        .append(
                            $("<div/>", {
                                'id': 'registration_card',
                                'class': "tab-pane",
                            })
                        )
                        .append(
                            $("<div/>", {
                                'id': 'payment_reminder',
                                'class': "tab-pane",
                            })
                        )
                        .append(
                            $("<div/>", {
                                'id': 'housekeeping',
                                'class': "tab-pane"
                            }).append(
                                $("<div/>", {
                                    class: "modal-body"
                                })
                                    .append(
                                        $("<div/>", {
                                            class: "form-group"
                                        }).append(
                                            $("<label/>", {
                                                for: "customer_notes",
                                                class: "control-label",
                                            }).append(
                                                $("<small/>", {
                                                    text: l('notes')
                                                })
                                            )
                                        ).append(
                                            $("<textarea/>", {
                                                class: "form-control notes",
                                                name: "housekeeping_notes",
                                                rows: 3,
                                                //text: housekeeping_notes
                                            })
                                        )
                                    )
                            )
                        )
                        .append(
                            $("<div/>", {
                                'id': 'payment_details',
                                'class': "tab-pane"
                            })

                                .append(
                                    $("<div/>", {
                                        class: "payment-modal modal-body"
                                    })
                                )
                                .append(
                                    $("<div/>", {
                                        class: "modal-inner-footer",
                                        id: "modal-inner-footer-new"
                                    })
                                )
                        )
                        .append(
                            $("<div/>", {
                                'id': 'history',
                                'class': "tab-pane tab-gap-wep"
                            }).append(
                                $("<div/>", {
                                    class: "content"
                                })
                            )
                        )
                        .append(
                            $("<div/>", {
                                'id': 'extras',
                                'class': "tab-pane"
                            })
                                .append(
                                    $("<div/>", {
                                        style: 'text-align:right;'
                                    })
                                        .append(
                                            $('<button/>', {
                                                class: 'btn btn-primary btn-sm',
                                                text: l('Add Product'),
                                                style: 'margin: 0px 0px 10px 0px;'
                                            }).on('click', function (e) {
                                                e.preventDefault(); // prevent # scrolling to the top

                                                if (!that.extras || !that.extras[0]) {
                                                    alert(l('Please add the products first under Settings')+ " > "+l('Rates')+" > "+l('Products'));
                                                    return;
                                                }

                                                that._initializeInnerModal();

                                                var extraData = {
                                                    extra_id: that.extras && that.extras[0] ? that.extras[0].extra_id : null,
                                                    extra_name: that.extras && that.extras[0] ? that.extras[0].extra_name : null,
                                                    start_date: that.booking.check_in_date,
                                                    end_date: that.booking.check_out_date,
                                                    quantity: 1,
                                                    rate: that.extras && that.extras[0] ? that.extras[0].rate : null
                                                };

                                                $.ajax({
                                                    type: "POST",
                                                    url: getBaseURL() + "extra/create_booking_extra_AJAX",
                                                    data: {
                                                        booking_id: that.booking.booking_id,
                                                        extra_data: extraData
                                                    },
                                                    dataType: "json",
                                                    success: function (response) {
                                                        that._editBookingExtra(response.booking_extra_id);

                                                        // update booking balance
                                                        if (response && $.isNumeric(response.balance)) {
                                                            $('.booking_balance').html(number_format(response.balance, 2, ".", ""));
                                                        }

                                                        var current_count = $('.extra_len').val();
                                                        var new_count = (parseInt(current_count) + 1);

                                                        $('.left-sidebar').find('.extras_count').html(" (" + new_count + ")");
                                                        $('.extra_len').val(new_count);
                                                        // if this is the first extra to this booking,
                                                        // create extra container (panel). otherwise, append extra to existing
                                                        extraData.booking_extra_id = response.booking_extra_id;
                                                        if ($("#extra-container").length) {
                                                            extraData.charging_scheme = $("#inner-modal").find("[name='extra_id'] option:selected").attr('data-charging-scheme');
                                                            $("#extra-container").append(that._getBookingExtraDiv(extraData));
                                                        } else {
                                                            if (!that.booking.extras) {
                                                                that.booking.extras = [];
                                                            }
                                                            that.booking.extras.push(extraData);
                                                            var extraPanel = that._getExtraPanel();
                                                            $("#booking-modal").find(".modal-body").find(".panel-body").find("#extras").append(extraPanel);
                                                        }
                                                    }
                                                })
                                            })
                                        )
                                )
                                .append(this._getExtraPanel())
                        )
                )


            panel.find('[name="check_in_date"]').attr('autocomplete', 'off');
            panel.find('[name="check_out_date"]').attr('autocomplete', 'off');

            return panel;
        },

        _dailyCharge: function () {
            var that = this;
            add_remaining_daily_charges = (that.booking.add_daily_charge == 0 ? false : true);
        },

        _showDailyChargeSetting: function () {
            var that = this;
            var pay_period = $('.pay_period').val();

            if (pay_period == 1 || pay_period == 2) {
                $('.add-daily-charges-div').show();
                $('.add-daily-charges-div').parents('.pay-period-block').removeClass('col-sm-2').addClass('col-sm-3').prev().removeClass('col-sm-4').addClass('col-sm-3');
                $('.add-daily-charges-div').parent('div').removeClass('form-group').addClass('input-group');
            } else {
                $('.add-daily-charges-div').hide();
                $('.add-daily-charges-div').parents('.pay-period-block').removeClass('col-sm-3').addClass('col-sm-2').prev().removeClass('col-sm-3').addClass('col-sm-4');
                $('.add-daily-charges-div').parent('div').removeClass('input-group').addClass('form-group');
            }
        },

        _bookingSource: function () {
            var that = this;
            if (!innGrid.ajaxCache.companyBookingSources) {
                $.post(getBaseURL() + "booking/get_booking_source_AJAX/",
                    function (data) {
                        if (data) {
                            that.companyBookingSources = jQuery.parseJSON(data);
                            innGrid.ajaxCache.companyBookingSources = that.companyBookingSources;
                            that.deferredBookingSource.resolve();
                        }
                    }
                );
            } else {
                that.companyBookingSources = innGrid.ajaxCache.companyBookingSources;
                that.deferredBookingSource.resolve();
            }
        },
        _bookingFields: function () {
            var that = this;
            if (!innGrid.ajaxCache.customBookingFields) {

                $.ajax({
                    type: "POST",
                    url: getBaseURL() + "booking/get_booking_fields",
                    dataType: "json",
                    success: function (data) {
                        that.customBookingFields = data;
                        innGrid.ajaxCache.customBookingFields = data;
                        that.deferredBookingFields.resolve();
                    }
                });
            } else {
                that.customBookingFields = innGrid.ajaxCache.customBookingFields;
                that.deferredBookingFields.resolve();
            }
        },
        _showDecimal: function () {
            if (innGrid.hideDecimalPlaces) {
                show_decimal = innGrid.hideDecimalPlaces != 0 ? false : true;
            }
            if (innGrid.makeGuestFieldMandatory && innGrid.makeGuestFieldMandatory == 1) {
                $('label[for="guests"]').append('<span class="guest_mandatory" style="color:red;">*</span>');
            }
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
                            roomsList.append(
                                $("<div/>", {
                                    style: 'padding:12px 14px 10px 14px;display:inline-block;'
                                }).append(
                                    $("<input/>", {
                                        type: 'checkbox',
                                        class: 'all-cancelled-room-checkbox',
                                    
                                    })
                                ).append(
                                        $("<span/>", {
                                            style: 'padding:10px;',
                                            html: '<strong>'+l("Select All Rooms")+'</strong> '
                                        })
                                    )
                                );
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
                                        'data-room_type_id': objVal.room_type_id,
                                        'data-check_in_date': objVal.check_in_date,
                                        'data-check_out_date': objVal.check_out_date,
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
                                                html: '<strong>'+l("Room")+'</strong> ' + (objVal.room_name ? objVal.room_name : 'Not Assigned')
                                            })
                                        ).append(
                                            $("<p/>", {
                                                style: 'margin-bottom:2px',
                                                html: objVal.customer_name
                                            })
                                        ).append(
                                            $("<p/>", {
                                                style: 'margin-bottom:2px',
                                                html: (innGrid.enableHourlyBooking ? moment(objVal.check_in_date).format('YYYY-MM-DD hh:mm A') : moment(objVal.check_in_date).format('YYYY-MM-DD')) + ' to ' + (innGrid.enableHourlyBooking ? moment(objVal.check_out_date).format('YYYY-MM-DD hh:mm A') : moment(objVal.check_out_date).format('YYYY-MM-DD'))
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
                            for: "customers",
                            class: "col-sm-2 control-label",
                            text: l("Customers")
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

                if (source.id == that.booking.source) {
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
                            for: "booking-notes",
                            class: "col-sm-2 control-label",
                            text: l("Notes")
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
                            for: "source",
                            class: "col-sm-2 control-label",
                            text: l("Source")
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
                        .append(l("Product Information"))
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
                        .append(this._getExtraSelect(l("Prodcut"), 'extra_id', that.extras, extra.extra_id))
                        .append(this._getHorizontalInput(l("Start Date"), 'start_date', extra.start_date))
                        .append(this._getHorizontalInput(l("End Date"), 'end_date', extra.end_date))
                        .append(this._getHorizontalInput(l("Quantity"), 'quantity', extra.quantity))
                        .append(this._getHorizontalInput(l("Rate"), 'rate', extra.rate))
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
                                text: l("Update")
                            }).on('click', function () {

                                var extraData = that._fetchExtraData();
                                $.ajax({
                                    type: "POST",
                                    url: getBaseURL() + "extra/update_booking_extra_AJAX",
                                    data: {
                                        booking_extra_id: extra.booking_extra_id,
                                        booking_extra_data: extraData,
                                        booking_id: that.booking.booking_id
                                    },
                                    dataType: "json",
                                    success: function (data) {
                                        // hack to properly generate extra div. it needs extra_name nad scheme
                                        extraData.extra_name = $("#inner-modal").find("[name='extra_id'] option:selected").text();
                                        extraData.booking_extra_id = extra.booking_extra_id;
                                        extraData.charging_scheme = $("#inner-modal").find("[name='extra_id'] option:selected").attr('data-charging-scheme');
                                        $(".extra#" + extra.booking_extra_id).replaceWith(that._getBookingExtraDiv(extraData));
                                        $("#inner-modal").modal('hide');

                                        // update booking balance
                                        if (data && $.isNumeric(data.balance)) {
                                            $('.booking_balance').html(number_format(data.balance, 2, ".", ""));
                                        }
                                    }
                                })
                            })
                        )
                        .append(
                            $("<button/>", {
                                type: "button",
                                class: "btn btn-light",
                                "data-dismiss": "modal",
                                text: l("Close")
                            })
                        )
                );


            var chargeScheme = $("#inner-modal [name='extra_id'] option:selected").attr('data-charging-scheme');
            if (chargeScheme == 'on_start_date') {
                $("#inner-modal").find('.block_end_date').remove();
                $("#inner-modal").find('.block_start_date').addClass('hidden');
            }

            $("#inner-modal").find("[name='start_date']").datepicker({
                dateFormat: ($('#companyDateFormat').val()).toLowerCase(),
                beforeShow: that._customRange
            });

            $("#inner-modal").find("[name='end_date']").datepicker({
                dateFormat: ($('#companyDateFormat').val()).toLowerCase(),
                beforeShow: that._customRange
            });

            $("#inner-modal").find("[name='extra_id']").on('change', function () {
                var extraVal = $(this).val();

                for (var key in that.extras) {
                    if (extraVal == that.extras[key].extra_id) {
                        $("#inner-modal").find('input[name="rate"]').val(that.extras[key].rate);
                    }
                }

                var chargeScheme = $(this).find('option[value="' + extraVal + '"]').attr('data-charging-scheme');
                if (chargeScheme == "on_start_date") {
                    $("#inner-modal").find('.block_end_date').remove();
                    $("#inner-modal").find('.block_start_date').addClass('hidden');
                } else {
                    $("#inner-modal").find('.modal-body').append(that._getHorizontalInput(l("End Date"), 'end_date', extra.end_date));
                    $("#inner-modal").find('.block_start_date').removeClass('hidden');
                }
            });

        },
        _populateRateModel: function () {
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
                                text: l("ok"),
                                style: "width: 70px;"
                            }).on('click', function () {

                                var modalBody = $(this).parents('body').find('#booking-modal .modal-body');

                                $(this).prop('disabled', true);

                                var rate_plan_id = that.newRatePlanId;

                                if (!rate_plan_id && modalBody.find('select.charge-with option:selected').hasClass('rate-plan') === true) {
                                    rate_plan_id = modalBody.find('select.charge-with option:selected').val()
                                }

                                var rateArray = {
                                    start_date: innGrid._getBaseFormattedDate(modalBody.find('[name="check_in_date"]').val()),
                                    end_date: innGrid._getBaseFormattedDate(modalBody.find('[name="check_out_date"]').val()),
                                    rate_plan_id: rate_plan_id,
                                    room_type_id: modalBody.find('[name="room_type_id"] option:selected').val(),
                                    charge_type_id: (rate_plan_id ? null : modalBody.find('select.charge-with option:selected').val()),
                                    booking_id: that.booking.booking_id
                                };

                                var ratesAr = [];
                                $('.rate-row-td td').each(function () {

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
                                        if (data.status == "success") {
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
                                class: "btn btn-light",
                                "data-dismiss": "modal",
                                text: l("Close")
                            })
                        )
                );
            that._getCustomRates();

        },
        _getCustomRates: function (newRatePlanId) {

            var that = this;

            var ratePlanId = (newRatePlanId != null) ? newRatePlanId : this.$modalBody.find('select.charge-with').val();

            var editRateRowTh = $('#rate-edit-table .rate-row-th');
            var editRateRowTd = $('#rate-edit-table .rate-row-td');
            var ratePlanName = this.$modalBody.find('.charge-with option:selected').text();

            var rateValue = 0;
            if (ratePlanName !== 'Custom Rate Plan') {
                rateValue = this.$modalBody.find('[name="rate"]').val(); // set default rate value
            }

            editRateRowTh.html("");
            editRateRowTd.html("");

            $.post(getBaseURL() + 'settings/rates/get_cusom_rates_AJAX', {
                    start_date: innGrid._getBaseFormattedDate(this.$modalBody.find('[name="check_in_date"]').val()),
                    end_date: innGrid._getBaseFormattedDate(this.$modalBody.find('[name="check_out_date"]').val()),
                    rate_plan_id: ratePlanId,
                    rate_plan_name: ratePlanName
                }, function (data) {
                    var data = JSON.parse(data);
                    if (data[0].rate_plan_name) {
                        $("#selected-rate-plan").html(data[0].rate_plan_name);

                        var chargeDropdown = $("body #booking-modal .modal-body").find("select.charge-with");
                        var prevoiusRatePlanId = chargeDropdown.val();

                        chargeDropdown.find("option.custom-rate-plan").remove() // remove previous custom rate plan

                        //if (data[0].rate_plan_name == "Custom Rate Plan") // remove for nestpay custom same rate name
                            chargeDropdown.find("optgroup[label='Rate Plans (Pre-set)']").append('<option selected class="rate-plan custom-rate-plan" value="' + data[0].rate_plan_id + '">' + data[0].rate_plan_name + '</option>');
                        $("body #booking-modal .modal-body").find("input[name='rate']").val(data[0].base_rate);
                    }

                    var monthName = {
                        0: l('Jan'),
                        1: l('Feb'),
                        2: l('Mar'),
                        3: l('Apr'),
                        4: l('May'),
                        5: l('Jun'),
                        6: l('Jul'),
                        7: l('Aug'),
                        8: l('Sep'),
                        9: l('Oct'),
                        10: l('Nov'),
                        11: l('Dec')
                    };
                    var adultCount = $("body #booking-modal .modal-body").find('select[name="adult_count"]').val();

                    $.each(data, function (index, value) {
                        var dayOfWeek = '';

                        switch (value.day_of_week) {
                            case '0':
                                dayOfWeek = l("Mo");
                                break;
                            case '1':
                                dayOfWeek = l("Tu");
                                break;
                            case '2':
                                dayOfWeek = l("We");
                                break;
                            case '3':
                                dayOfWeek = l("Th");
                                break;
                            case '4':
                                dayOfWeek = l("Fr");
                                break;
                            case '5':
                                dayOfWeek = l("Sa");
                                break;
                            case '6':
                                dayOfWeek = l("Su");
                                break;
                        }
                        var d = (value.date);
                        var split = d.split('-');

                        if (value['adult_' + adultCount + '_rate'] != null) // set custom rate value
                            rateValue = value['adult_' + adultCount + '_rate'];
                        else if (value.base_rate != null) // set custom rate value
                            rateValue = value.base_rate;
                        else
                            rateValue = $("body #booking-modal .modal-body").find('input[name="rate"]').val();

                        editRateRowTh.append(
                            $("<th/>", {
                                html: monthName[split[1] - 1] + '<br>' + split[2] + '<br>' + dayOfWeek,
                            })
                        );

                        var selling_date = moment($("#sellingDate").val() + ' 00:00:00').format('YYYY-MM-DD');
                        var checkInDate = moment(value.date).format('YYYY-MM-DD');
                        var disabled = false;
                        var style = '';
                        if (selling_date > checkInDate) {
                            disabled = true;
                            style = 'cursor: not-allowed;';
                        }

                        editRateRowTd.append(
                            $("<td/>", {
                                "data-attr-date": value.date,
                                "data-attr-day": dayOfWeek
                            }).append(
                                $("<input/>", {
                                    style: "width: 40px;padding: 2px;border: none;" + style,
                                    value: rateValue,
                                    class: "adult-rates",
                                    name: "rate_" + dayOfWeek,
                                    disabled: disabled
                                })
                            )
                        );

                    });
                    var roomTypeDIV = $("body #booking-modal .modal-body").find(".room-type");
                    that._updateRate(roomTypeDIV);
                }
            );
        },
        _populatePaymentCard: function (logs, booking_id) {
            var customer_array = new Array();
            for (var index = 0; index <= logs.length; index++) {

                if (Array.isArray(logs[index])) {
                    for (var index1 = 0; index1 <= logs[index].length; index1++) {
                        if (typeof (logs[index][index1]) == 'object') {
                            customer_array.push(logs[index][index1]);
                        }
                    }
                }
            }
            var that = this;
            $("#payment_details").find(".payment-modal").html(
                $("<div/>", {
                    class: "row cardpanel"
                })
            )
            var count = 1;
            customer_array.forEach(function (log) {

                var cus_id = $('#hidden_customer_iden' + log.customer_id).val();
                var cus_name = "";
                var new_card_button = "";
                var card_details_part_one = "";
                var card_details_part_sec = "";
                var guest_name_heading = "";
                var is_primary_button = "";
                var error_part = "";

                if (cus_id != log.customer_id) {
                    cus_name = log.customer_name;
                    count = count + 1;
                    guest_name_heading = $("<div/>", {
                        class: "card_heading_div col-md-12",
                        id: "card_div_" + log.customer_id
                    }).append(
                        $("<span/>", {
                            class: "card_guest_span col-md-12",
                            id: "card_span_" + log.customer_id,
                            html: cus_name
                        })
                    ).append($("<input/>", {
                        type: "hidden",
                        id: "hidden_customer_iden" + log.customer_id,
                        value: log.customer_id
                    }))
                        .append(
                            $("<div/>", {
                                class: "add_card"
                            }).append(
                                $("<button/>", {
                                    type: "button",
                                    class: "btn add_card",
                                    id: "add_card",
                                    html: l("Add Card")
                                })
                            ).on("click", function (e) {
                                $(document).openCardModal({
                                    customer_id: log.customer_id,
                                    key_data: "new",
                                    booking_id: booking_id
                                });
                            })
                        )
                }
                if (cus_id != log.customer_id) {
                    error_part = $("<div/>", {
                        class: "error_div_" + log.customer_id
                    })
                }
                if (log.is_card_deleted == 0 && log.is_primary) {
                    if (log.is_primary == 1) {
                        var is_primary_button = $("<label/>", {
                            class: "switch"
                        }).append(
                            $("<input/>", {
                                type: "checkbox",
                                class: "is_primary_check",
                                "checked": "checked",
                                id: "is_primary" + log.id
                            })
                        ).append(
                            $("<span/>", {
                                class: "slider round"
                            }).append(
                                $("<span/>", {
                                    class: "p-label",
                                    html: l("Primary")
                                })
                            )
                        );
                    } else {
                        var is_primary_button = $("<label/>", {
                            class: "switch"
                        }).append(
                            $("<input/>", {
                                type: "checkbox",
                                class: "is_primary_check",
                                id: "is_primary" + log.id

                            })
                        ).append(
                            $("<span/>", {
                                class: "slider round"
                            }).append(
                                $("<span/>", {
                                    class: "p-label",
                                    html: l("Primary")
                                })
                            )
                        );
                    }
                }
                if (log.is_card_deleted == 0) {
                    card_details_part_one = $("<div/>", {
                        class: "card_div col-md-7 card_div_b_" + log.customer_id,
                        id: "card_div_b_" + log.id
                    }).append(
                        $("<input/>", {
                            type: "hidden",
                            id: "hidden_cus_id",
                            value: log.customer_id
                        })).append(
                        $("<table/>", {
                            class: "guest_card",
                        }).append(
                            $("<tr/>", {}).append(
                                $("<td/>", {
                                    html: log.cc_number
                                })
                            ).append(
                                $("<td/>", {
                                    html: log.card_name
                                })
                            )
                        ).append(
                            $("<tr/>", {}).append(
                                $("<td/>", {
                                    html: log.cc_expiry_month + '/' + log.cc_expiry_year

                                })
                            ).append(
                                $("<td/>", {
                                    id: "table_td_" + log.id
                                }).append(
                                    //on off
                                    is_primary_button
                                )
                            )
                        )
                    )

                }
                if (log.is_card_deleted == 0) {
                    card_details_part_sec = $("<div/>", {
                        class: "remo_div col-md-5",
                        id: "card_div_sm_" + log.id
                    }).append(
                        $("<button/>", {
                            type: "button",
                            class: "close card-close fa fa-close",
                            id: "card_remove_" + log.cc_number

                        })
                    ).on("click", ".card-close", function () {
                        if (confirm("Are you sure to delete this card ?")) {
                            $(document).openCardModal({
                                customer_id: log.customer_id,
                                cus_card_id: log.id,
                                cus_card_token: log.cc_tokenex_token,
                                key_data: "delete",
                                booking_id: booking_id
                            });
                        }
                        return false;

                    })
                        .append(
                            $("<button/>", {
                                type: "button",
                                class: "close card-edit fa fa-edit",
                                id: "card_update"
                            })
                        ).on("click", ".card-edit", function () {

                            $(document).openCardModal({
                                customer_id: log.customer_id,
                                cus_card_id: log.id,
                                key_data: "update",
                                booking_id: booking_id
                            });
                        })
                }
                $(".payment-modal").find(".row").append(
                    guest_name_heading
                )
                if (log.cc_number) {
                    $(".payment-modal").find(".row").append(
                        guest_name_heading
                    ).append(
                        card_details_part_one
                    )
                        .append(
                            card_details_part_sec
                        )
                }
                $(".payment-modal").find(".row").append(
                                 error_part
                )
                $("#remove_card").on("click", function () {
                    $(document).openCardModal({
                        customer_id: log.customer_id,
                        key_data: "delete",
                    });
                });


                $("#is_primary" + log.id).on("click", function () {
                    $(".is_primary_check").prop('disabled', true);
                    if ($("#is_primary" + log.id).prop('checked') == true) {
                        $.ajax({
                            type: "POST",
                            url: getBaseURL() + "customer/update_customer_card_is_primary",
                            data: {
                                customer_id: log.customer_id,
                                card_id: log.id,
                                active: "active"
                            },
                            dataType: "json",
                            success: function (is_primary_date) {
                                if (is_primary_date != null) {
                                    $('#pay_details_tab').click();
                                }
                            }
                        });
                    } else {
                        $.ajax({
                            type: "POST",
                            url: getBaseURL() + "customer/update_customer_card_is_primary",
                            data: {
                                customer_id: log.customer_id,
                                card_id: log.id,
                                active: "deactive"
                            },
                            dataType: "json",
                            success: function (is_primary_date) {
                                if (is_primary_date != null) {
                                    $('#pay_details_tab').click();
                                }
                            }
                        });
                    }
                });
            });
            customer_array.forEach(function (log) {
                if ($('.card_div_b_' + log.customer_id).length == 0) {
                    for (var i = 0; i <= count; i++) {
                        $(".payment-modal").find(".error_div_" + log.customer_id).html(
                            $("<span/>", {
                                class: "card_guest_span col-md-12",
                                id: "card_span_error",
                                html: l("No Card Activated Yet!")
                            })
                        )
                    }
                }
            });


        },
        _populateHousekeepingNotesModal: function (housekeeping_notes) {
            var that = this;

            $('#housekeeping').find(".notes").html(housekeeping_notes);

        },
        _populateHistoryModal: function (logs) {
            var that = this;

            // construct header
            //$("#inner-modal").find(".modal-content")


            logs.forEach(function (log) {

                $("#history").find(".content").append(
                    $("<div/>", {
                        class: "panel panel-default"
                    }).append(
                        $("<div/>", {
                            class: "panel-body",
                            html: log.date_time + " "+l('by')+" " + (log.user_id == '0' && !log.first_name && !log.last_name && !log.email ? l('System') : (log.user_id == '-1' ? l('Guest') : (log.first_name + " " + log.last_name))) + " - " + log.log
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
                        .append("<b>\"" + item.customer_name + "\" "+l('already exists in the system')+"</b>")
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
                                text: l("Yes. It's a Returning Customer")
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
                                class: "btn btn-light",
                                text: l("No, It's a different customer with a same name")
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
            } else if (input.name == "check_out_date") {
                if ($("[name='check_in_date']").val() != '') {
                    var dateMin = $("[name='check_in_date']").val();
                }
            }

            return {
                minDate: dateMin,
                maxDate: dateMax
            };

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
            } else {
                checkGroup = true;
                activeGroup = 'active';
            }

            var checkInDt = innGrid._getLocalFormattedDate(that.booking.check_in_date ? that.booking.check_in_date : $("#sellingDate").val());
            var checkOutDt = that.booking.check_out_date ? innGrid._getLocalFormattedDate(that.booking.check_out_date) : '';
            var check_in_time = moment(that.booking.check_in_date ? that.booking.check_in_date : $("#sellingDate").val()).format('hh:mm A');
            var check_out_time = that.booking.check_out_date ? moment(that.booking.check_out_date).format('hh:mm A') : '12:00 AM';

            if (!that.booking.booking_id && innGrid.calendar.view && innGrid.calendar.view.type === "customMonthView") {
                check_in_time = $('#CheckInTime').val();
                check_in_time = check_in_time ? check_in_time : '12:00 AM';
                check_out_time = $('#CheckOutTime').val();
                check_out_time = check_out_time ? check_out_time : '12:00 AM';
            }

            // prepare block template
            var block = $('<div/>', {
                class: 'panel-booking clearfix'
            })
                //.append(
                //                            $('<div/>', {
                //                                class: 'panel-body',
                //                            })
                .append(
                    $("<div/>", {
                        class: "form-group col-sm-6"
                    })
                        .append(
                            $("<label/>", {
                                for: "checkin-date"
                            })
                                .append(
                                    $("<small/>", {
                                        text: l('check_in_date')
                                    })
                                )
                        )
                        .append($('<span/>', {style: "color:red;", text: "*"}))
                        .append(
                            $('<div/>', {class: innGrid.enableHourlyBooking ? 'hourly-booking-enabled' : ''})
                                .append(
                                    $("<input/>", {
                                        name: 'check_in_date',
                                        class: 'form-control check-in-date-wrapper',
                                        placeholder: l("Check-in Date"),
                                        value: checkInDt,
                                    }).datepicker({
                                        dateFormat: ($('#companyDateFormat').val()).toLowerCase(),
                                        beforeShow: that._customRange
                                    })
                                        .on('change', function () {
                                            if (that.groupInfo != null && that.booking.booking_id != null) {
                                                if (that.saveAllGroupDate == null) {
                                                    that._confirmationGroupDateModel(this);
                                                }
                                            }
                                            setTimeout(function () {
                                                $(document).find("[name='check_out_date']").focus();
                                            }, 200);
                                            that._updateNumberOfDays();
                                            that._updateRoomTypeDDL();
                                        })
                                )
                                .append(
                                    $("<input/>", {
                                        name: 'check_in_time',
                                        class: 'form-control check-in-time-wrapper',
                                    })
                                        .append(timeOptions)
                                        .val(check_in_time)
                                        .on('change', function () {
                                            if (that.groupInfo != null && that.booking.booking_id != null) {
                                                if (that.saveAllGroupDate == null) {
                                                    that._confirmationGroupDateModel(this);
                                                }
                                            }
                                            setTimeout(function () {
                                                $(document).find("[name='check_out_date']").focus();
                                            }, 200);
                                            that._updateNumberOfDays();
                                            that._updateRoomTypeDDL();
                                        })
                                )
                        )
                ) //check -in end
                .append(
                    $("<div/>", {
                        class: "form-group col-sm-6",
                        style: "display: -webkit-inline-box;padding-left: 15px;"
                    })
                        .append(
                            $("<label/>")
                                .append(
                                    $("<small/>", {
                                        text: l('check_out_date')
                                    })
                                )
                        )
                        .append($('<span/>', {style: "color:red;", text: "*"}))
                        .append(
                            $("<div/>", {
                                class: "form-group input-group" + (innGrid.enableHourlyBooking ? ' hourly-booking-enabled' : ''),
                                style: "display: -webkit-inline-box;width: 100% ;"
                            })
                                .append(
                                    $("<input/>", {
                                        name: 'check_out_date',
                                        class: 'form-control check-out-date-wrapper fix-wep',
                                        placeholder: l("Check-out Date"),
                                        style: "border-bottom-left-radius: 4px;border-top-left-radius: 4px;",
                                        value: checkOutDt
                                    }).datepicker({
                                        dateFormat: ($('#companyDateFormat').val()).toLowerCase(),
                                        beforeShow: that._customRange
                                    })
                                        .on('change', function () {
                                            if (that.groupInfo != null) {
                                                if (that.saveAllGroupDate == null && that.booking.booking_id != null) {
                                                    that._confirmationGroupDateModel(this);
                                                }
                                            }
                                            if (new Date(innGrid._getBaseFormattedDate(this.value)) < new Date(innGrid._getBaseFormattedDate($("[name='check_in_date']").val()))) {
                                                alert(l("Check-out-date can't be less than Check-in-date"));
                                                $("[name='check_out_date']").val($("[name='check_in_date']").val());
                                                $("[name='check_out_date']").focus();
                                            }
                                            that._updateNumberOfDays();
                                            that._updateRoomTypeDDL();
                                        })
                                )
                                .append(
                                    $("<input/>", {
                                        name: 'check_out_time',
                                        class: 'form-control check-out-time-wrapper',
                                    })
                                        .append(timeOptions)
                                        .val(check_out_time)
                                        .on('change', function () {
                                            if (that.groupInfo != null) {
                                                if (that.saveAllGroupDate == null && that.booking.booking_id != null) {
                                                    that._confirmationGroupDateModel(this);
                                                }
                                            }
                                            if (new Date(innGrid._getBaseFormattedDate(this.value)) < new Date(innGrid._getBaseFormattedDate($("[name='check_in_date']").val()))) {
                                                alert(l("Check-out-date can't be less than Check-in-date",true));
                                                $("[name='check_out_date']").val($("[name='check_in_date']").val());
                                                $("[name='check_out_date']").focus();
                                            }
                                            that._updateNumberOfDays();
                                            that._updateRoomTypeDDL();
                                        })
                                )
                                .append(
                                    $("<div/>", {
                                        class: 'input-group',
                                        style: "width: 23%;border-radius: 3px;border-right: 1px solid #ccc;"
                                    })
                                        .append(
                                            $("<span/>", {
                                                class: "input-group-addon",
                                                style: "padding: 3px;"
                                            }).append(
                                                $("<input/>", {
                                                    name: 'number_of_days',
                                                    maxlength: '3',
                                                    style: 'padding: 3px;width: 30px; '
                                                }).on("change", function () {
                                                    // set number of days as check_out_date - check_in_date
                                                    var cid = innGrid._getBaseFormattedDate($("[name='check_in_date']").val()).split(/[-]/);
                                                    if ($("[name='check_in_date']").val()) {
                                                        var number_of_days = that.$modalBody.find("[name='number_of_days']").val();
                                                        if (number_of_days == "") {
                                                            that.$modalBody.find("[name='check_out_date']").val("");
                                                        } else {
                                                            number_of_days = parseInt(number_of_days);
                                                            // Apply each element to the Date function
                                                            var check_out_date = new Date(cid[0], cid[1] - 1, cid[2]);
                                                            check_out_date.setDate(check_out_date.getDate() + number_of_days);
                                                            var year = check_out_date.getFullYear();
                                                            var month = ("0" + (check_out_date.getMonth() + 1)).slice(-2);
                                                            var day = ("0" + check_out_date.getDate()).slice(-2);
                                                            that.$modalBody.find("[name='check_out_date']").val(innGrid._getLocalFormattedDate(year + "-" + month + "-" + day));
                                                        }
                                                    }
                                                    that._updatePayPeriodDropdown();
                                                })
                                            ).append(" nights")
                                        )
                                )
                        )
                )//check -out end

                .append(
                    $('<div/>', {
                        id: 'room-type-list'
                    })
                )
            //);
            var panelBody = that.$modalBody.find('.panel-booking');
            panelBody.find('.group-name-div').remove();
            if (that.selectedGroupType == 'linked_group') {
                var groupNameBlock = (
                    $('<div/>', {
                        class: 'form-group col-sm-6 group-name-div'
                    }).append(
                        $('<label/>')
                            .append(
                                $('<small/>', {
                                    text: l('group_name')
                                })
                            )
                    )
                        .append(
                            $('<div/>').append(
                                $('<input/>', {
                                    class: 'group-name form-control',
                                    type: 'text',
                                    value: '',
                                    name: 'group_name',
                                    placeholder: l('Recommended: Give the group a name')
                                })
                            )
                        )
                );

                $('.booked-by-block').removeClass('hidden');
                $('.booking-source-block').removeClass('col-sm-12').addClass('col-sm-6');
                //$('.booking-source-block').removeClass('col-sm-12').addClass('col-sm-6');
                $('.adult-child-block').replaceWith(groupNameBlock);
                $('.group-name-div').removeClass('hidden');
                $('.guest-block').removeClass('col-sm-12').addClass('col-sm-6');
            } else {
                $('.group-name-div').addClass('hidden');
                $('.adult-child-block').addClass('hidden');
                $('.guest-block').removeClass('col-sm-6').addClass('col-sm-12');
            }
            block.find("[name='check_in_date'], [name='check_out_date'], [name='number_of_days']")
                .on('change', function () {
                    that._updateNumberOfDays();
                    that._updateRoomGroupList();
                    that._updateRate();
                });

            if (that.booking.source != 2 && that.booking.source != 3 && that.booking.source != 4 && that.booking.source != 8) {
                block.find('.edit-rate-btn').on('click', function () {
                    that._initializeRateModal();
                });
            } else {
                roomTypeDIV.find('.edit-rate-btn').css('cursor', 'not-allowed')
            }


            // if walk-in, disable check-in date
            if ($("[name='state']").val() === '1' && that.booking.booking_id === undefined) {
                block.find("[name='check_in_date']").val(innGrid._getLocalFormattedDate($("#sellingDate").val()));
                block.find("[name='check_in_date']").prop("disabled", true);
            }
            $('.content').find('#booking_detail').find('.rate-extra-info-div').html("").remove();

            block.find('[name="check_in_date"]').attr('autocomplete', 'off');
            block.find('[name="check_out_date"]').attr('autocomplete', 'off');

            return block;
        },
        _updateRoomGroupList: function () {
            var that = this;

            if (!$("[name='check_in_date']").val() || !$("[name='check_out_date']").val()) {
                return;
            }

            var roomTypeList = $('<div/>', {
                class: 'rooms form-group col-lg-12'
            });

            var checkInDate = moment(innGrid._getBaseFormattedDate($("input[name='check_in_date']").val()) + ' ' + that.convertTimeFormat($("[name='check_in_time']").val())).format('YYYY-MM-DD HH:mm:ss');
            var checkOutDate = moment(innGrid._getBaseFormattedDate($("input[name='check_out_date']").val()) + ' ' + that.convertTimeFormat($("[name='check_out_time']").val())).format('YYYY-MM-DD HH:mm:ss');

            // $.getJSON(getBaseURL() + 'booking/get_available_room_types_in_JSON/' + encodeURIComponent(checkInDate) + '/' + encodeURIComponent(checkOutDate),
            $.post(getBaseURL() + 'booking/get_available_room_types_in_JSON', {
                check_in_date: encodeURIComponent(checkInDate),
                check_out_date: encodeURIComponent(checkOutDate),
                isAJAX : true
            },
            function (roomTypes) {

                    if (roomTypes !== '' && roomTypes !== null && roomTypes.length > 0) {
                        roomTypes.forEach(
                            function (roomType) {
                                if (roomType.availability > 0) {
                                    var numberOfRoomsSelect = $("<select/>", {

                                        class: 'form-control room_count',
                                        name: 'room_count',
                                        id: roomType.id

                                    });

                                    for (var i = 0; i <= roomType.availability; i++) {
                                        if (i === 0)
                                            var text = l("no room");
                                        else if (i === 1)
                                            var text = "1 "+l('room');
                                        else
                                            var text = i + " " + l('rooms');
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
                                                        class: "col-sm-12 form-group"
                                                    })
                                                        .append("<strong>" + roomType.name + "</strong> (" + roomType.acronym + ") <small>" + roomType.availability + " "+l('available')+"</small>")
                                                        .append(
                                                            $("<div/>")
                                                                .append(
                                                                    $('<select/>', {
                                                                        name: 'room_type_id',
                                                                        class: 'form-control room_type_id',
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
                                                        class: "col-sm-12 form-group"
                                                    })
                                                        .append(
                                                            $("<div/>", {
                                                                class: "col-sm-6 form-group width-fix-wep"
                                                            })
                                                                .append(
                                                                    $('<label/>', {
                                                                        for: "book"
                                                                    })
                                                                        .append(
                                                                            $('<small/>', {
                                                                                for: "book",
                                                                                text: l('no_of_rooms')
                                                                            })
                                                                        )
                                                                        .append($('<span/>', {
                                                                            style: "color:red;",
                                                                            text: "*"
                                                                        }))
                                                                )
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
                                                alert(l('Maximum capacity for room') + " " +
                                                    selected_room_type +
                                                    " "+l('is')+" \n"+l('Maximun adults')+" " + max_adults +
                                                    " \n"+l('Maximun children')+" " + max_children);


                                                if (adult_count > max_adults)
                                                    $(this).closest(".capicity-block").find("[name='adult_count']").val(max_adults);
                                                if (children_count > max_children)
                                                    $(this).closest(".capicity-block").find("[name='children_count']").val(max_children);
                                            }
                                            that.booking.state == 3 ? '' : that._validateCapacity();
                                            that._updateRate($(this).parents('.room-type.panel-body'));
                                        });

                                    roomTypeList.append(roomGroup);
                                }
                            }
                        );

                        //roomTypeList.append("10 rooms selected. Set cut-off date to: <select class='form-control'><option>never</option></select>");

                    }
                },'json'
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
                );

            var extraContainer = $('<div/>', {
                class: 'col-sm-12',
                id: 'extra-container'
            });

            extras.forEach(function (bookingExtra) {
                extraContainer.append(that._getBookingExtraDiv(bookingExtra));
            });

            block.find(".panel-body").append(extraContainer);

            return block;
        },
        _getBookingExtraDiv: function (bookingExtra) {

            var that = this;

            var extraDiv = $("<div/>", {
                class: 'row extra',
                id: bookingExtra.booking_extra_id,
                style: "margin-bottom:10px;"
            })
            extraDiv.append(bookingExtra.quantity)
                .append(" " + bookingExtra.extra_name);

            if (bookingExtra.charging_scheme != 'on_start_date') {
                extraDiv.append(" "+l('between')+" ")
                    .append(bookingExtra.start_date)
                    .append(" "+l('and')+" ")
                    .append(bookingExtra.end_date);
            } else {
                extraDiv.append(' '+l("on date")+' ')
                    .append(bookingExtra.start_date);
            }
            extraDiv.append(l('at rate')+" : ")
                .append(bookingExtra.rate)
                .append(
                    $("<button/>", {
                        class: 'btn btn-light pull-right btn-xs',
                        type: 'button',
                        html: "<span class='glyphicon glyphicon-remove' aria-hidden='true'></span>",
                        style: "margin-left:10px;"
                    }).on('click', function () {

                        var extra = $(this).parent();
                        that._deleteExtra(extra.attr('id'));
                    })
                )
                .append(
                    $("<button/>", {
                        class: 'booking-extra btn btn-light pull-right btn-xs',
                        id: bookingExtra.booking_extra_id,
                        type: 'button',
                        html: "<span class='glyphicon glyphicon-pencil' aria-hidden='true'></span> "+l('Edit')
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
                {value: 7, name: l('unconfirmed') + ' ' + l('reservation')},
                {value: 0, name: l('reservation')},
                {value: 1, name: l('checked_in')},
                {value: 2, name: l('checked_out')},
                {value: 4, name: l('cancelled') + ' (' + l('hide') + ')'},
                {value: 5, name: l('No-show')}
            ];

            if (state === undefined) {

                // if user is dragging mouse
                if (that.booking.check_in_date == $("#sellingDate").val() ||
                    that.booking.current_room_id === undefined) {
                    options[2].name = l('Walk-in');
                }

                options.push({value: 3, name: l('Out of Order')});
                state = 0;
            } else if (state == '6') {
                // deleted
                var options = [
                    {value: 6, name: l('Deleted')}
                ];
            } else if (state == '3') {
                // out of order
                var options = [
                    {value: 3, name: l('Out of Order')}
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

                $('.guest_mandatory').show();
                $('.guest_fields_row').removeClass('hidden');

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
                        $('.guest_mandatory').hide();
                        $('.guest_fields_row').addClass('hidden');
                        break;
                    case '4': // No Show
                        var balance = parseInt(that.booking.balance_without_forecast);
                        if (balance != '0' && !innGrid.featureSettings.bookingCancelledWithBalance) {
                            // balance is 0  when
                            //alert('You are unable to cancel a reservation with a balance on the invoice');
                            $('#reservation-message')
                                .modal('show')
                                .on('hidden.bs.modal', function () {
                                    if (($("#booking-modal").data('bs.modal') || {}).isShown)
                                        $("body").addClass("modal-open");
                                });
                            $('#reservation-message .message').html(l('You are unable to cancel a reservation with a balance on the invoice'));
                            $('.confirm-customer').on('click', function () {
                                    $('#reservation-message').modal('hide');
                                    return false;
                            });
                        }
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

            if (state !== undefined) {
                // If the booking is not out of order
                if (state !== '3') {
                    modalHeader.prepend(
                        $("<span/>", {
                            class: "h4",
                            html: l('Edit') + ' ' + l(innGrid.featureSettings.defaultRoomSingular) + " (" + l('id') + ": " + this.booking.booking_id + ") " + l('balance') + ": <a class='booking_balance' href='" + getBaseURL() + "invoice/show_invoice/" + this.booking.booking_id + "'>" + number_format(this.booking.balance, 2, ".", "") + "</a> "
                        })
                    ).prepend(
                        $("<input/>", {
                            type: "hidden",
                            id: "booking_id",
                            value: this.booking.booking_id
                        })
                    )
                } else {
                    modalHeader.prepend(
                        $("<span/>", {
                            class: "h4",
                            html: l("Edit Out-of-order")
                        })
                    )
                }
            } else {
                modalHeader.prepend(
                    $("<span/>", {
                        class: "h4 heading-fix-wep",
                        html: l("Create new booking"),
                         style: "float: left;"
                    })
                );

                if(innGrid.isGroupBookingFeatures == true){
                    modalHeader.append(
                        $("<span/>", {
                            class: "h4 heading-fix-wep total_counts",
                            html: l("Total Rooms") + ": <span class='total_room_count' >" + 0 + "</span> ",
                            style: "margin: 0 30px; display: none;"
                        })
                    );

                    modalHeader.append(
                        $("<span/>", {
                            class: "h4 heading-fix-wep total_counts",
                            html: l("Total Guest") + ": <span class='total_guest_count' >" + 0 + "</span> ",
                            style: "display: none;"
                        })
                    );

                    modalHeader.prepend(
                        $("<button/>", {
                            class: "btn-light heading-fix-wep fixed_rate_group",
                            html: l("Fixed Rate Plan"),
                            id: "fixed_rate_group",
                            style: "margin-left:5px; margin-top: 5px; display:none;"
                        })
                    );

                    modalHeader.prepend(
                        $("<button/>", {
                            class: "btn-light heading-fix-wep per_person_group ",
                            html: l("Rate per Person"),
                            id: "per_person_group",
                            style: "margin-left:5px; margin-top: 5px; display:none;"
                        })
                    );
                    modalHeader.prepend(
                        $("<input/>", {
                            type: "hidden",
                            id: "current_rate_plan_type"
                        })
                    );

                    modalHeader.prepend(
                        $("<input/>", {
                            type: "hidden",
                            id: "current_rate_plan_amount"
                        })
                    );

                    modalHeader.prepend(
                        $("<input/>", {
                            type: "hidden",
                            id: "currentRatePlanID"
                        })
                    );   
                }
                //$('.left-sidebar').find("li#registration_card").hide();
            }
            if (this.groupInfo != null) {
                modalHeader.append(
                    $("<span/>", {
                        class: "h4",
                        style: "padding-left: 20px",
                        html: l("Group")+" "+l("Id")+": " + this.groupInfo.group_id
                    }).append(
                        $("<span/>", {
                            class: "h4",
                            style: "padding-left: 20px",
                            html: l("Group")+" "+l("Name")+": " + this.groupInfo.group_name + " "
                        })
                    ).append(
                        $("<input/>", {
                            type: "hidden",
                            id: "group_id",
                            value: this.groupInfo.group_id
                        })
                    )
                );

                if(innGrid.isGroupBookingFeatures == true){
                    modalHeader.append(
                        $("<br/>", {
                            class: "total_group_booking_counts"
                        })
                    );
                    modalHeader.append(
                        $("<br/>", {
                            class: "total_group_booking_counts"
                        })
                    );

                    modalHeader.append(
                        $("<span/>", {
                            class: "h4 heading-fix-wep",
                            html: l("Total Rooms") + ": <span class='total_rm_count' >" + 0 + "</span> ",
                            style: ""
                        })
                    );

                    modalHeader.append(
                        $("<span/>", {
                            class: "h4 heading-fix-wep",
                            html: l("Total Guest") + ": <span class='total_customer_count' >" + 0 + "</span> ",
                            style: "margin: 0 30px;"
                        })
                    );
                }

                invoiceGroupId = this.groupInfo.group_id;
            } else {
                invoiceGroupId = '';
            }

            return modalHeader;
        },
        _updateModalFooter: function () {

            var that = this;
            var state = this.booking.state;
            var modalFooter = $("#booking-modal").find(".modal-footer").html("");
            var todays = ($("#sellingDate").val() + ' 00:00:00');
            var checkInDate = (this.booking.check_in_date);
            var checkOutDate = (this.booking.check_out_date);
            var disableClass = '';
            if (moment(checkInDate).format('YYYY-MM-DD') > moment(todays).format('YYYY-MM-DD')) {
                disableClass = 'disabled';
            }
            $('input[name=check_in_date], input[name=check_out_date]').prop('disabled', false);

            // reservation
            if (state == 0) {
                modalFooter
                    .append(
                        $("<button/>", {
                            type: "button",
                            class: "btn btn-success " + disableClass,
                            id: "button-check-in",
                            text: l("Check in")
                        }).on('click', function () {

                            if (moment(checkInDate).format('YYYY-MM-DD') <= moment(todays).format('YYYY-MM-DD')) {
                                that.button = $(this);
                                that.button.prop('disabled', true);
                                var bookingData = that._fetchBookingData();
                                if (!bookingData['rooms'][0]['room_id']) {
                                    $('#reservation-message')
                                        .modal('show')
                                        .on('hidden.bs.modal', function () {
                                            if (($("#booking-modal").data('bs.modal') || {}).isShown)
                                                $("body").addClass("modal-open");
                                        });
                                    $('#reservation-message .message').html(l('You must assign a room before checking the guest in'));
                                    that.button.prop('disabled', false);
                                    $('.confirm-customer').on('click', function () {
                                        $('#reservation-message').modal('hide');
                                        return false;
                                    });
                                } else {
                                    bookingData.booking.state = 1;
                                    that._updateBooking(bookingData, l("Successfully checked-in"));
                                    that._makeRoomDirty(bookingData['rooms'][0]['room_id']);
                                }

                            }

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
                            class: "btn btn-warning " + disableClass,
                            id: "button-check-out",
                            text: l("Check out")
                        }).on('click', function () {

                            // Restrict check out if customer has a balance.
                            var bookingData = that._fetchBookingData();
                            var balance = parseInt(that.booking.balance_without_forecast);
                            if (balance != '0' && that.booking.restrict_checkout_with_balance == 1 && !innGrid.enableHourlyBooking) {
                                //alert('This reservation cannot be checked out as the stay has not started yet');
                                $('#reservation-message')
                                    .modal('show')
                                    .on('hidden.bs.modal', function () {
                                        if (($("#booking-modal").data('bs.modal') || {}).isShown)
                                            $("body").addClass("modal-open");
                                    });
                                $('#reservation-message .message').html(l('You are unable to checkout with a balance on the invoice'));
                                $('.confirm-customer').on('click', function () {
                                    $('#reservation-message').modal('hide');
                                    return false;
                                });
                            } else {

                                if (moment(checkInDate).format('YYYY-MM-DD') <= moment(todays).format('YYYY-MM-DD')) {
                                    that.button = $(this);
                                    that.button.prop('disabled', true);

                                    bookingData.booking.state = 2;
                                    var selling_date = $("#sellingDate").val() + ' 00:00:00';
                                    var action = null;
                                    if (moment(selling_date).format('YYYY-MM-DD') < moment(bookingData.rooms[0].check_out_date).format('YYYY-MM-DD')) {

                                        bookingData.rooms[0].check_out_date = moment(innGrid._getBaseFormattedDate(selling_date) + ' ' + that.convertTimeFormat($("[name='check_out_time']").val())).format('YYYY-MM-DD HH:mm:ss');

                                        action = 'early-check-out';
                                    }

                                    // update availabilities of the dates after the update

                                    var bookingUpdatedData = [];
                                    bookingUpdatedData.check_in_date = checkInDate;
                                    bookingUpdatedData.check_out_date = checkOutDate;
                                    bookingUpdatedData.room_type_id = that.booking.current_room_type_id;

                                    var bookingUpdatedEvent = new CustomEvent('booking_updated', { "detail" : {"reservation_id" : that.booking.booking_id, "booking_data" : bookingUpdatedData} });
                                    document.dispatchEvent(bookingUpdatedEvent);

                                    that._updateBooking(bookingData, l("Successfully checked-out"), action);
                                    if(innGrid.companyID == '2637') {
                                        that._makeRoomDirty(bookingData['rooms'][0]['room_id']);
                                    }
                                }

                            }
                        })
                    )
            }
            // Checked out
            if (state == 2) {
                // Restrict booking dates modification if customer has been checked out.
                if (that.booking.restrict_booking_dates_modification == 1) {
                    $('input[name=check_in_date], input[name=check_out_date]').prop('disabled', 'disabled');
                }
            }

            // out of order
            if (state == 3) {
                modalFooter
                    .append(
                        $("<button/>", {
                            type: "button",
                            class: "btn btn-danger",
                            id: "button-delete-out-of-order",
                            text: l("Delete")
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
                            text: l("Confirm Reservation")
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
                        text: l("Create")
                    }).on('click', function () {
                        that.button = $(this);
                        that.button.prop('disabled', true);
                        var selling_date = moment($("#sellingDate").val() + ' 00:00:00').format('YYYY-MM-DD');
                        var checkInDate = moment(that.booking.check_in_date).format('YYYY-MM-DD');
                        if (checkInDate < selling_date) {
                            //alert('Charges will not be posted on a reservation created for a date in the past');
                            $('#reservation-message')
                                .modal('show')
                                .on('hidden.bs.modal', function () {
                                    if (($("#booking-modal").data('bs.modal') || {}).isShown)
                                        $("body").addClass("modal-open");
                                });
                            $('#reservation-message .modal-lg').removeClass('modal-lg').addClass('modal-sm');
                            $('#reservation-message .message-heading').text(l('Notice'));
                            $('#reservation-message .message').html(l('Charges will only be posted between current date and check-out date.'));
                            $('.confirm-customer').on('click', function () {
                                $('#reservation-message').modal('hide');
                                setTimeout(function () {
                                    $('#reservation-message .modal-sm').removeClass('modal-sm').addClass('modal-lg');
                                    $('#reservation-message .message-heading').text(l('Message'));
                                }, 1000);
                                return false;
                            });
                        }
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
                        } else {
                            that._createBooking();
                        }
                    })
                );
            } else {
                modalFooter.append(
                    $("<button/>", {
                        type: "button",
                        class: "btn btn-light booking-save-btn",
                        text: l("Save")
                    }).on('click', function () {
                        that.button = $(this);
                        //that.button.prop('disabled', true);
                        var todays = moment($("#sellingDate").val() + ' 00:00:00').format('YYYY-MM-DD');
                        var checkInDate = moment(that.booking.check_in_date).format('YYYY-MM-DD');
                        var balance = parseInt(that.booking.balance_without_forecast);
                        var reservationType = $('#booking_detail').find('select[name=state]').val();
                        var balance1 = parseInt(that.booking.balance);

                        if (checkInDate > todays && reservationType == '1') {
                            //alert('Guest cannot be checked in as the arrival is for a date in the future');
                            $('#reservation-message')
                                .modal('show')
                                .on('hidden.bs.modal', function () {
                                    if (($("#booking-modal").data('bs.modal') || {}).isShown)
                                        $("body").addClass("modal-open");
                                });
                            $('#reservation-message .message').html(l('Guest cannot be checked in as the arrival is for a date in the future'));
                            $('.confirm-customer').on('click', function () {
                                var flag = $(this).attr('flag');
                                if (flag == 'cancel') {
                                    $('#reservation-message').modal('hide');
                                    return false;
                                } else {
                                    $('#reservation-message').modal('hide');
                                    return false;
                                }
                            });
                        } else if (checkInDate > todays && reservationType == '2') {
                            //alert('This reservation cannot be checked out as the stay has not started yet');
                            $('#reservation-message')
                                .modal('show')
                                .on('hidden.bs.modal', function () {
                                    if (($("#booking-modal").data('bs.modal') || {}).isShown)
                                        $("body").addClass("modal-open");
                                });
                            $('#reservation-message .message').html(l('This reservation cannot be checked out as the stay has not started yet'));
                            $('.confirm-customer').on('click', function () {
                                var flag = $(this).attr('flag');
                                if (flag == 'cancel') {
                                    $('#reservation-message').modal('hide');
                                    return false;
                                } else {
                                    $('#reservation-message').modal('hide');
                                    return false;
                                }
                            });
                        } else if (reservationType == '4' && balance != 0 && !innGrid.featureSettings.bookingCancelledWithBalance) {
                            //alert('This reservation cannot be checked out as the stay has not started yet');
                            $('#reservation-message')
                                .modal('show')
                                .on('hidden.bs.modal', function () {
                                    if (($("#booking-modal").data('bs.modal') || {}).isShown)
                                        $("body").addClass("modal-open");
                                });
                            $('#reservation-message .message').html(l('You are unable to cancel a reservation with a balance on the invoice'));
                            $('.confirm-customer').on('click', function () {
                                $('#reservation-message').modal('hide');
                                return false;
                            });
                        } else if (reservationType == '4' && balance != 0 && innGrid.featureSettings.bookingCancelledWithBalance) {
                            $('#cancel-reservation')
                                .modal('show')
                                .on('hidden.bs.modal', function () {
                                    if (($("#booking-modal").data('bs.modal') || {}).isShown)
                                        $("body").addClass("modal-open");
                                });
                            $('#cancel-reservation .message').html(l('Are you sure you want to cancel a reservation with a balance on the invoice?'));
                            $('.confirm-customer').on('click', function () {
                                var flag = $(this).attr('flag');
                                if (flag == 'yes') {

                                    $('#cancel-reservation').modal('hide');

                                    var activeTab = $('.left-sidebar').find('li.active').find('a').html();
                                    if (activeTab == l('Housekeeping')) {
                                        $.ajax({
                                            type: "POST",
                                            url: getBaseURL() + "booking/update_housekeeping_notes_AJAX",
                                            data: {
                                                booking_id: that.booking.booking_id,
                                                housekeeping_notes: $("#housekeeping").find("[name='housekeeping_notes']").val()
                                            },
                                            dataType: "json",
                                            success: function (data) {

                                            }
                                        })
                                    }
                                    if (that.saveAllGroupDate == true || that.saveAllGroupDate == false) {
                                        var bookingData = that._fetchBookingData();
                                        that._getAllGroupRoomBookingIds(bookingData);
                                    } else {
                                        var bookingData = that._fetchBookingData();
                                        that._updateBooking(bookingData);
                                    }
                                    that.saveAllGroupDate = null;
                                    that._showAlert(l("Saved"));
                                } else {
                                    $('#cancel-reservation').modal('hide');
                                    return false;
                                }
                            });
                        } else {

                            that._showAlert(l("Saved"));
                            var activeTab = $('.left-sidebar').find('li.active').find('a').html();
                            if (activeTab == l('Housekeeping')) {
                                $.ajax({
                                    type: "POST",
                                    url: getBaseURL() + "booking/update_housekeeping_notes_AJAX",
                                    data: {
                                        booking_id: that.booking.booking_id,
                                        housekeeping_notes: $("#housekeeping").find("[name='housekeeping_notes']").val()
                                    },
                                    dataType: "json",
                                    success: function (data) {

                                    }
                                })
                            }
                            if (that.saveAllGroupDate == true || that.saveAllGroupDate == false) {
                                var bookingData = that._fetchBookingData();
                                that._getAllGroupRoomBookingIds(bookingData);
                            } else {
                                var bookingData = that._fetchBookingData();
                                that._updateBooking(bookingData);
                            }
                            that.saveAllGroupDate = null;
                        }
                    })
                );
            }

            return modalFooter.append(
                $("<button/>", {
                    type: "button",
                    class: "btn btn-light",
                    "data-dismiss": "modal",
                    text: l("Close")
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
            var booked_by_id = '';

            if (that.selectedGroupType == 'linked_group') {
                isGroupBooking = true;
                groupName = this.$modalBody.find('input[name="group_name"]').val();
            }
            $("div.guest-block .tokenfield div.token").each(function () {

//                if($('.tokenfield input.booked_by').attr('booked_by') == '1')
//                {
//                    booked_by_id = $(this).attr("id") ? $(this).attr("id") : null;
//                }
                var customerOptions = {
                    customer_id: $(this).attr("id") ? $(this).attr("id") : null,
                    customer_name: $(this).find(".token-label").text()
                };

                // customer name is passed, in case it's a new customer
                // (without customer id) that needs to be created
                if (customerOrder === 0) {
                    payingCustomer = customerOptions
                } else {
                    stayingCustomers.push(customerOptions);
                }
                customerOrder++;

            });

            $("div.booked-by-block .tokenfield div.token").each(function () {
                booked_by_id = $(this).attr("id") ? $(this).attr("id") : null;
            });

            var rooms = [];
            if ($('input[name=booking-type-radio]:checked').val() == 'group') {
                var roomsBlock = $('#room-type-list').find('.room-type');
            } else {
                var roomsBlock = $('.room-type');
            }
            roomsBlock.each(function () {
                var useRatePlan = undefined;
                var ratePlanID = undefined;
                var chargeTypeID = undefined;

                if ($(this).find(".charge-with option:selected").hasClass('rate-plan') === true) {
                    // if user is using rate_plan, assign rate_plan_id
                    useRatePlan = 1;
                    ratePlanID = $(this).find(".charge-with").val();
                } else {
                    // if booking is not using rate plan, assign charge_type_id
                    useRatePlan = 0;
                    chargeTypeID = $(this).find(".charge-with").val();
                }

                rooms.push({
                    check_in_date: innGrid.enableHourlyBooking == 1 ? moment(innGrid._getBaseFormattedDate($("[name='check_in_date']").val()) + ' ' + that.convertTimeFormat($("[name='check_in_time']").val())).format('YYYY-MM-DD HH:mm:ss') : moment(innGrid._getBaseFormattedDate($("[name='check_in_date']").val()) + ' ' + '00:00:00').format('YYYY-MM-DD HH:mm:ss'),
                    check_out_date: innGrid.enableHourlyBooking == 1 ? moment(innGrid._getBaseFormattedDate($("[name='check_out_date']").val()) + ' ' + that.convertTimeFormat($("[name='check_out_time']").val())).format('YYYY-MM-DD HH:mm:ss') : moment(innGrid._getBaseFormattedDate($("[name='check_out_date']").val()) + ' ' + '00:00:00').format('YYYY-MM-DD HH:mm:ss'),
                    // for single booking
                    room_id: $(this).find("[name='room_id']").val(),
                    // for group booking
                    room_type_id: $(this).find("[name='room_type_id'] option:selected").val(),//$(this).find("[name='room_type_id']").val(),
                    room_count: $(this).find("[name='room_count']").val(),
                    rate: $(this).find("[name='rate']").val() ? $(this).find("[name='rate']").val() : 0,
                    use_rate_plan: useRatePlan,
                    rate_plan_id: ratePlanID,
                    charge_type_id: chargeTypeID,
                    pay_period: $(this).find("[name='pay_period']").val(),
                    adult_count: $(this).find("[name='adult_count'] option:selected").val(),
                    children_count: $(this).find("[name='children_count'] option:selected").val()
                });

            });

            var updateBookingData = {
                state: $('#booking-modal select[name="state"]').val(),
                rate: $("[name='rate']").val() ? $("[name='rate']").val() : 0,
                pay_period: $("[name='pay_period'] option:selected").val(),
                adult_count: $("[name='adult_count'] option:selected").val(),
                children_count: $("[name='children_count'] option:selected").val(),
                booking_notes: $("[name='booking_notes']").val(),
                source: $("[name='source'] option:selected").val(),
                booked_by: booked_by_id,
                add_daily_charge: (that.booking.add_daily_charge == 0 ? 0 : 1),
                residual_rate: (that.booking.residual_rate ? that.booking.residual_rate : 0),
                color: $("[name='color']").val()
            };
            var bookingData = {
                booking: updateBookingData,
                rooms: rooms,
                customers: {
                    paying_customer: payingCustomer,
                    staying_customers: stayingCustomers
                },
                isGroupBooking: isGroupBooking,
                groupName: groupName,
                guests: $('input[name=customers]').val() ? $('input[name=customers]').val() : ''
            };

            var booking_fields = [];

            $('input[name="custom_booking_field[]"]').each(function (i, v) {
                booking_fields.push({
                    id: $(this).attr('id'),
                    value: $(this).val()
                });
            });
            bookingData['custom_booking_fields'] = booking_fields;

            return bookingData;
        },
        convertTimeFormat (time) {
            // console.log('time', time);
            time = time ? String(time) : "12:00 AM";
            var hours = Number(time.match(/^(\d+)/)[1]);
            var minutes = Number(time.match(/:(\d+)/)[1]);
            var AMPM = time.match(/\s(.*)$/)[1];
            if(AMPM == "PM" && hours<12) hours = hours+12;
            if(AMPM == "AM" && hours==12) hours = hours-12;
            var sHours = hours.toString();
            var sMinutes = minutes.toString();
            if(hours<10) sHours = "0" + sHours;
            if(minutes<10) sMinutes = "0" + sMinutes;
            return (sHours + ":" + sMinutes);
        },
        _showAlert: function (msg) {
            if (msg) {
                $(".alert-booking-created").text(msg).show(0, function () {
                    $(this).stop().fadeOut(3000);
                });
            }
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
                        "data-color": "#" + color,
                    })
                );
            });

            return select;
        },
        _createBooking: function (text, is_duplicate = false, old_booking_id = null) {


            var that = this;
            var existGroupId = null;
            var data = this._fetchBookingData();
            var roomTypeAvailability = this.$modalBody.find('select[name="room_type_id"]').find('option:selected').data('room_type_availability');
            if ($('.btn-group input[name=booking-type-radio]:checked').val() == 'single' && (roomTypeAvailability == 0 || roomTypeAvailability == null)) {
                $('#reservation-message .message').html(l('There is no availability for the selected Room Type!'));
                $('#reservation-message').modal('show');
                $('.confirm-customer').on('click', function () {
                    $('#reservation-message').modal('hide');
                    return false;
                });
                that.button.prop('disabled', false);
                that.booking = {};
                return;
            }

            if (that.groupInfo != null)
                existGroupId = that.groupInfo.group_id


            if (typeof _createBookingLock !== "undefined" && _createBookingLock) {
                // booking creation already in progress
                return;
            }

            _createBookingLock = true;

            $.ajax({
                type: "POST",
                url: getBaseURL() + "booking/create_booking_AJAX",
                data: {
                    data: data,
                    existing_group_id: existGroupId,
                    is_duplicate: is_duplicate,
                    old_booking_id: old_booking_id
                },
                dataType: "json",
                success: function (response) {

                    // release the lock
                    _createBookingLock = false;

                    if (response.overbooking_status) {
                        $('#reservation-message .message').html(l("The selected room is no longer available. Please select a different room"));
                        $('#reservation-message')
                            .modal('show')
                            .on('hidden.bs.modal', function () {
                                if (($("#booking-modal").data('bs.modal') || {}).isShown)
                                    $("body").addClass("modal-open");
                            });
                        $('.confirm-customer').on('click', function () {
                            $('#reservation-message').modal('hide');
                            return false;
                        });
                        that.button.prop('disabled', false);
                        that.booking = {};
                        return;
                    }

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
                            errorMsg += error + "<br/>";
                        });
                        $('#reservation-message .message').html(errorMsg);
                        $('#reservation-message')
                            .modal('show')
                            .on('hidden.bs.modal', function () {
                                if (($("#booking-modal").data('bs.modal') || {}).isShown)
                                    $("body").addClass("modal-open");
                            });
                        $('.confirm-customer').on('click', function () {
                            $('#reservation-message').modal('hide');
                            return false;
                        });
                        that.button.prop('disabled', false);
                        that.booking = {};
                        return;
                    } else {
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
                            } else {
                                that.booking.check_in_date = data.rooms[0].check_in_date;
                                that.booking.check_out_date = data.rooms[0].check_out_date;
                            }

                            that._showAlert(l("Successfully created"));

                            that._updateModalContent();
                            that._getLinkedGroupBookingRoomList();

                            if(response[0] && response[0].rate_plan_id){
                                var option = $("<option/>", {
                                    value: response[0].rate_plan_id,
                                    parent_rate_plan_id: data.rooms[0].rate_plan_id,
                                    text: response[0].rate_plan_name,
                                    class: 'rate-plan'
                                });

                                $('.charge-with optgroup:nth-child(2)').append(option);
                                option.prop("selected", true);
                            }
                            

                            if (that.booking.pay_period == 1 || that.booking.pay_period == 2) {
                                $('.add-daily-charges-div').show();
                                $('.add-daily-charges-div').parents('.pay-period-block').removeClass('col-sm-2').addClass('col-sm-3').prev().removeClass('col-sm-4').addClass('col-sm-3');
                                $('.add-daily-charges-div').parent('div').removeClass('form-group').addClass('input-group');
                            } else {
                                $('.add-daily-charges-div').hide();
                                $('.add-daily-charges-div').parents('.pay-period-block').removeClass('col-sm-3').addClass('col-sm-2').prev().removeClass('col-sm-3').addClass('col-sm-4');
                                $('.add-daily-charges-div').parent('div').removeClass('input-group').addClass('form-group');
                            }

                            // Create the event
                            var event = new CustomEvent('post.open_booking_modal', { "detail" : {"reservation_id" : that.booking.booking_id, "booking_data" : that.booking} });
                            var bookingCreatedEvent = new CustomEvent('booking_created', { "detail" : {"reservation_id" : that.booking.booking_id, "booking_data" : that.booking, "booking_room_data" : data.rooms[0]} });

                            // Dispatch/Trigger/Fire the event
                            document.dispatchEvent(event);
                            document.dispatchEvent(bookingCreatedEvent);

                            if ($("#current-page").val() === 'show_reservation_report_cm') {
                                setTimeout(function () {
                                    location.reload();
                                }, 500);
                            }
                        } else {

                            that.booking = data.booking;
                            console.log('response',response);

                            // Create the event
                            var event = new CustomEvent('post.open_booking_modal', { "detail" : {"reservation_id" : that.booking.booking_id, "booking_data" : that.booking} });
                            var bookingCreatedEvent = new CustomEvent('booking_created', { "detail" : {"booking_data" : that.booking, "booking_room_data" : response} });

                            // Dispatch/Trigger/Fire the event
                            document.dispatchEvent(event);
                            document.dispatchEvent(bookingCreatedEvent);
                                
                            that._closeBookingModal();
                        }

                    }

                    if (innGrid.reloadBookings) innGrid.reloadBookings();

                    // mixpanel tracking
                    mixpanel.track("Booking created");

                    // Intercom tracking
                    var metadata = {
                        booking_id: that.booking.booking_id ? that.booking.booking_id : null
                    };
                    // Intercom('trackEvent', 'booking-created', metadata);

                    $('.registration_card_tab').removeClass('hidden');
                    $('.sidebar-wrapper').removeClass('hidden');
                    $('.left-sidebar').find('.extras_count').html(" ( 0 )");
                    if (text != 'Create duplicate booking') {
                        if ($('.booking-modal-body').hasClass('col-lg-12')) {
                            $('.booking-modal-body').removeClass('col-lg-12').addClass('col-lg-9');
                            $('#booking_detail').find('.token-input').css('width', '0px');
                        } else if ($('.booking-modal-body').hasClass('col-lg-9')) {
                            $('.booking-modal-body').removeClass('col-lg-9').addClass('col-lg-7');
                            $('#booking_detail').find('.token-input').css('width', '0px');
                        }
                    }
                },
                error: function () {
                    // release the lock
                    _createBookingLock = false;
                }
            });
        },
        _closeBookingModal: function () {
            this.closeModal.resolve();
        },
        _makeRoomDirty: function (roomID) {
            var that = this;

            if(innGrid.companyID != '2637') {
                $.ajax({
                    type: "POST",
                    url: getBaseURL() + "room/update_room_status",
                    data: {
                        room_id: roomID,
                        room_status: 'Dirty'
                    },
                    dataType: "json",
                    success: function (data) {
                        if(!(innGrid.calendar && innGrid.calendar.destroy)) {
                            innGrid.reloadCalendar();
                        }
                    }
                });
            }
        },
        _updateBooking: function (data, msg, action = null) {

            var that = this;

            if (!data['booking']['color']) {
                var selectedColor = $('[name=color]').data('selected-color');
                var selectedState = $('[name=color]').data('selected-state');
                if (selectedColor && selectedState == data['booking']['state']) {
                    data['booking']['color'] = selectedColor;
                }
            }

            // merge in the changes to booking
            $.each(data.booking, function (key, value) {
                that.booking[key] = data.booking[key];
            });

            data.number_of_days = $("[name='number_of_days']").val();

            $.ajax({
                type: "POST",
                url: getBaseURL() + "booking/update_booking_AJAX",
                data: {
                    booking_id: this.booking.booking_id,
                    data: data
                },
                dataType: "json",
                success: function (response) {
                    if (response.response == 'failure') {
                        $('#reservation-message').modal('show');
                        $('#reservation-message .message').html(response.message);
                        $('.confirm-customer').on('click', function () {
                            $('#reservation-message').modal('hide');
                            $('#reservation-message').on('hidden.bs.modal', function () {
                                $('body').addClass('modal-open');
                            });
                            return false;
                        });
                    }
                    if (response.errors !== undefined) {
                        var errorMsg = "";
                        response.errors.forEach(function (error) {
                            errorMsg += error + "\n";
                        });
                        $('#reservation-message .message').html(errorMsg);
                        $('#reservation-message').modal('show');
                        $('.confirm-customer').on('click', function () {
                            $('#reservation-message').modal('hide');
                            return false;
                        });
                        that.button.prop('disabled', false);
                    } else {

                        //that._closeBookingModal();
                        //that._init();
                        that._showAlert(msg);
                        //that._populateEditBookingModal();
                        that._updateModalContent();
                        that._getLinkedGroupBookingRoomList();

                        // update booking balance
                        if (response && $.isNumeric(response.balance)) {
                            $('.booking_balance').html(number_format(response.balance, 2, ".", ""));
                        }

                        // update availabilities of the dates after the update

                        var bookingUpdatedEvent = new CustomEvent('booking_updated', { "detail" : {"reservation_id" : that.booking.booking_id, "booking_data" : data} });
                        document.dispatchEvent(bookingUpdatedEvent);

                        if (action === "early-check-out") {
                            // update checkout date in modal in case of early checkout
                            $('[name="check_out_date"]').val(innGrid._getLocalFormattedDate(data.rooms[0].check_out_date));
                        }
                        if (data.booking.state != 4 && that.groupInfo != null) {
                            that.disableRoomBlock = '';
                            that.pointerNone = '';
                            that.$modalBody.find('.panel-booking').attr('style', that.disableRoomBlock);
                            that.$modalBody.find('.panel-booking .form-inline').attr('style', that.pointerNone);

                        }
                        if ($("#current-page").val() === 'show_reservation_report_cm') {
                            setTimeout(function () {
                                location.reload();
                            }, 500);
                        }

                        if (data.booking.state == 2 && innGrid.companyID == '2637'){
                            that._makeRoomDirty(data['rooms'][0]['room_id']);
                        }
                    }

                    if (innGrid.reloadBookings) innGrid.reloadBookings();
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
                    if (response.response == 'failure') {
                        $('#reservation-message').modal('show');
                        $('#reservation-message .message').html(response.message);
                        $('.confirm-customer').on('click', function () {
                            $('#reservation-message').modal('hide');
                            $('#reservation-message').on('hidden.bs.modal', function () {
                                $('body').addClass('modal-open');
                            });
                            return false;
                        });
                    }
                    if (response.errors !== undefined) {
                        var errorMsg = "";
                        response.errors.forEach(function (error) {
                            errorMsg += error + "\n";
                        });
//                        alert(errorMsg);
                        $('#reservation-message .message').html(errorMsg);
                        $('#reservation-message').modal('show');
                        $('.confirm-customer').on('click', function () {
                            $('#reservation-message').modal('hide');
                            return false;
                        });
                    } else {

                        that._showAlert(msg);

                        // update booking balance
                        if (response && $.isNumeric(response.balance)) {
                            $('.booking_balance').html(number_format(response.balance, 2, ".", ""));
                        }

                        // update availabilities of the dates after the update

                        var bookingUpdatedEvent = new CustomEvent('booking_updated', { "detail" : {"reservation_id" : bookingId, "booking_data" : data} });
                        document.dispatchEvent(bookingUpdatedEvent);

                        that._getLinkedGroupBookingRoomList();
                        if (bookingId == that.booking.booking_id && cancelledTrue != '') {
                            that.$modalBody.find("[name='state']").val('4');
                            that.disableRoomBlock = 'cursor:not-allowed;background:#f2f2f2'; // disable room block section that is cancelled
                            that.pointerNone = 'pointer-events:none';
                            that.$modalBody.find('.panel-booking').attr('style', that.disableRoomBlock);
                            that.$modalBody.find('.panel-booking .form-inline').attr('style', that.pointerNone);
                        }
                    }

                    if (innGrid.reloadBookings) innGrid.reloadBookings();
                }
            });
        },
        _createDuplicate: function (text, is_duplicate = false, booking_id = null) {
            var answer = confirm(l("Make a duplicate (this action may cause over booking!)"));
            if (answer == true) {
                this._createBooking(text, is_duplicate, booking_id);
            }
        },
        _deleteBooking: function (isGroupBookingDel, groupBookingId, key = 0) {
            var that = this;
            var answer = key === 0 ? confirm(l("Are you sure you want to delete this booking?")) : true;
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
                    dataType: "json",
                    data: {
                        booking_id: bookingId
                    },
                    success: function (data) {
                        // data = (data == "") ? data : JSON.parse(data);
                        if (data.response == "success") // if successful, delete_booking_AJAX returns empty page
                        {
                            var bookingDeletedEvent = new CustomEvent('booking_deleted', { "detail" : {"reservation_id" : that.booking.booking_id, "booking_data" : that.booking} });
                            document.dispatchEvent(bookingDeletedEvent);

                            if (isGroupBookingDel == true) {
                                that._getLinkedGroupBookingRoomList();
                                that._closeBookingModal();
                            } else {
                                that._closeBookingModal();
                            }
                        } else if (data.response == 'failure') {
                            $('#reservation-message').modal('show');
                            $('#reservation-message .message').html(data.message);
                            $('.confirm-customer').on('click', function () {
                                $('#reservation-message').modal('hide');
                                $('#reservation-message').on('hidden.bs.modal', function () {
                                    $('body').addClass('modal-open');
                                });
                                return false;
                            });
                        } else {
                            alert(l("You do not have permission to delete booking"));
                        }

                        if (innGrid.reloadBookings) innGrid.reloadBookings();
                    }
                });

            }
            return answer;
        },
        _chekinBooking :function(isGroupBookingDel, groupBookingId, key = 0){
            var that = this;
            var answer = key === 0 ? confirm(l("Are you sure you want to checkin this booking?")) : true;
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
                    url: getBaseURL() + "booking/checkin_booking_AJAX",
                    dataType: "json",
                    data: {
                        booking_id: bookingId
                    },
                    success: function (data) {
                        // data = (data == "") ? data : JSON.parse(data);
                        if (data.response == "success") // if successful, delete_booking_AJAX returns empty page
                        {
                            if (isGroupBookingDel == true) {
                                that._getLinkedGroupBookingRoomList();
                                that._closeBookingModal();
                            } else {
                                that._closeBookingModal();
                            }
                        } 
                         else {
                            alert(l("Guest cannot be checked in as the arrival is for a date in the future"));
                        }

                        if (innGrid.reloadBookings) innGrid.reloadBookings();
                    }
                });

            }
            return answer;

        },
        _chekoutBooking :function(isGroupBookingDel, groupBookingId, key = 0){
            var that = this;
            var answer = key === 0 ? confirm(l("Are you sure you want to checkout this booking?")) : true;
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
                    url: getBaseURL() + "booking/checkout_booking_AJAX",
                    dataType: "json",
                    data: {
                        booking_id: bookingId
                    },
                    success: function (data) {
                        // data = (data == "") ? data : JSON.parse(data);
                        if (data.response == "success") // if successful, delete_booking_AJAX returns empty page
                        {
                            if (isGroupBookingDel == true) {
                                that._getLinkedGroupBookingRoomList();
                                that._closeBookingModal();
                            } else {
                                that._closeBookingModal();
                            }
                        } 
                         else {
                            alert(l("please confirm with the guest checkout date is not arrival is for a date in the future"));
                        }

                        if (innGrid.reloadBookings) innGrid.reloadBookings();
                    }
                });

            }
            return answer;

        },
        _deleteExtra: function (bookingExtraID) {
            var that = this;
            var answer = confirm(l("Are you sure you want to delete this prodcut?"));
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

                        var current_count = $('.extra_len').val();
                        var new_count = (parseInt(current_count) - 1);

                        $('.left-sidebar').find('.extras_count').html(" (" + new_count + ")");
                        $('.extra_len').val(new_count);

                        $.each(that.booking.extras, function (i, value) { // unset booking extra id that is deleted
                            if (value.booking_extra_id == bookingExtraID) {
                                that.booking.extras.splice(i, 1);
                            }
                        });

                        // update booking balance
                        if (data && $.isNumeric(data.balance)) {
                            $('.booking_balance').html(number_format(data.balance, 2, ".", ""));
                        }

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
            var invoice_group_id = $('#group_id').val();
            if (state === undefined) {
                $("[name='state']").val(0); // assume new booking is reservation
                return;
            } else {
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
                        //this.$allActions.editHousekeepingNotes
                    ];

                    if (that.groupInfo !== null) {
                        $actions.push(this.$allActions.openGroupInvoice);
                        $actions.push(this.$allActions.sendGroupConfirmationEmail);
                    } else {
                        $actions.push(this.$allActions.sendConfirmationEmail);
                    }

                    //allow users to add extras only if the company has the extras set up
                    if (that.extras !== null)
                        //$actions.push(this.$allActions.addExtra);

                        //$actions.push(this.$allActions.showHistory);
                        if (state != '2')
                            $actions.push(this.$allActions.createDuplicate);
                    $actions.push(this.$allActions.divider);
                    $actions.push(this.$allActions.deleteBooking);
                    if(
                        (
                            innGrid.isGroupBookingFeatures == true
                        )
                        && 
                        invoice_group_id != undefined
                    )
                    {
                        $actions.push(this.$allActions.dividerNew);
                        $actions.push(this.$allActions.editFixRatePlan);
                        $actions.push(this.$allActions.editRatePerPerson);
                    }


                    break;
                case 3: // out of order
                case 4: // cancelled
                    $actions = [
                        this.$allActions.showInvoice,
                        this.$allActions.sendCancellationEmail,
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

                    //allow users to add extras only if the company has the extras set up
                    if (that.extras !== null)
                        //$actions.push(this.$allActions.addExtra);

                        //$actions.push(this.$allActions.showHistory);
                        if (state != '2')
                            $actions.push(this.$allActions.createDuplicate);
                    $actions.push(this.$allActions.divider);
                    $actions.push(this.$allActions.deleteBooking);

                    break;
                case 6: // deleted
                    $actions = [
                        this.$allActions.showHistory
                    ];
                    break;

            }

            var actionsUL = $("<ul/>", {
                class: "dropdown-menu pull-right other-actions",
                role: "menu",
                style:"min-width:230px"
            });

            $.each($actions, function (name, action) {
                actionsUL.append(action);
            });

            var groupInvoiceUL = $("<ul/>", {
                class: "dropdown-menu pull-right other-actions",
                role: "menu"
              
            });


            var bookingButtons = this.$modalBody.find(".booking-buttons");
            var modalHeader = $("#booking-modal .modal-header");
            if (invoiceGroupId != '') {
                modalHeader.append(
                    $("<div/>", {
                        class: "btn-group pull-right",
                        role: "group",
                        
                    })
                        .append(
                            $("<button/>", {
                                type: "button",
                                class: "btn btn-light dropdown-toggle",
                                "data-toggle": "dropdown",
                                "aria-expanded": false,
                                text: l('more') + " "
                            })
                                // .append(
                                //     $("<span/>", {
                                //         class: "caret"
                                //     })
                                // )
                        )
                        .append(actionsUL)
                );
                modalHeader.append(
                    $("<div/>", {
                        class: "btn-group pull-right",
                        role: "group",
                       
                    })
                        .append(
                            $("<a/>", {
                                class: "btn btn-light",
                                href: getBaseURL() + "invoice/show_invoice/" + that.booking.booking_id,
                                text: l('open_invoice'),    
                                style: "margin-right: 10px;border-radius: 3px;"
                            })
                        )
                        // .append(
                        //     $("<button/>", {
                        //         type: "button",
                        //         class: "btn btn-light dropdown-toggle",
                        //         "data-toggle": "dropdown",
                        //         "aria-expanded": false,
                        //         "style": "margin-right: 10px;"
                        //     })
                        //         // .append(
                        //         //     $("<span/>", {
                        //         //         class: "caret"
                        //         //     })
                        //         // )
                        // )
                        // .append(groupInvoiceUL
                        //     .append(
                        //         $("<li/>", {}).append(
                        //             $("<a/>", {
                        //                 href: getBaseURL() + "invoice/show_master_invoice/" + invoiceGroupId,
                        //                 text: l('Group Invoice')
                        //             })
                        //         )
                        //     )
                        // )

                );
                

            } else {
                modalHeader.append(
                    $("<div/>", {
                        class: "btn-group pull-right",
                        role: "group"
                        
                    })
                        .append(
                            $("<a/>", {
                                class: "btn btn-light m-2",
                                href: getBaseURL() + "invoice/show_invoice/" + that.booking.booking_id,
                                text: l('open_invoice')
                            })
                        )
                        .append(
                            $("<button/>", {
                                type: "button",
                                class: "btn btn-light dropdown-toggle m-2",
                                "data-toggle": "dropdown",
                                "aria-expanded": false,
                                text: l('more') + " "
                            })
                                // .append(
                                //     $("<span/>", {
                                //         class: "caret"
                                //     })
                                // )
                        )
                        .append(actionsUL)
                );
            }
        },
        _updateColorSelector: function (color) {
            // console.log('color', color);
            $("[name='color']").colorselector("setBackgroundColor", color);
            $("[name='color']").colorselector("setColor", "#" + color);

            $('[name=color]').data('selected-color', "");
            $('[name=color]').data('selected-state', "");
            if (!this.booking.color) {
                $("[name='color']").val("");
            } else {
                if ($.inArray("#" + color, Object.values(this.defaultColors)) == -1 && $.inArray(color, this.customColors) == -1) {
                    $('[name=color]').data('selected-color', color);
                    $('[name=color]').data('selected-state', this.booking.state);
                }
            }

            $(".dropdown-colorselector").find(".use-default-button-div").html("");
            var that = this;
            $(".dropdown-colorselector").find(".dropdown-menu").append(
                $("<div/>", {
                    class: 'use-default-button-div'
                }).append(
                    $("<button/>", {
                        class: "use-default-color-button color-btn form-control",
                        text: l("Use Default"),
                        "data-color": 'transparent',
                        "data-value": 'transparent',
                        "style": "background-color: " + this.defaultColors[this.booking.state]
                    }).on("click", function () {
                        $("[name='color']").val("");
                        $('[name=color]').data('selected-color', "");
                        $('[name=color]').data('selected-state', "");
                        $("[name='color']").colorselector("setBackgroundColor", that.defaultColors[that.booking.state]);
                    })
                )
            );
        },
        _getDefaultColor: function () {

            if (this.booking.color) {
                color = this.booking.color;
            } else if (this.booking.state) {
                color = this.defaultColors[this.booking.state];
            } else {
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
                delimiter: ['|'],
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
                                            customer_type_id: item.customer_type_id,
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

                            if (existing_customer && existing_customer.customer_notes) {
                                $(".blacklist_customer").html(existing_customer && existing_customer.customer_notes ? existing_customer.customer_notes : '');
                                $(".blacklist_customer").removeClass('hidden');
                            } else {
                                $(".blacklist_customer").addClass('hidden');
                            }

                            if (!customer_selected_from_autocomplete) {
                                that.deferredExistingCustomerConfirmation = $.Deferred();
                                if (existing_customer) {
                                    that._initializeInnerModal();
                                    that._populateExistingCustomerConfirmationModal(token, existing_customer);
                                } else {
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

                                                setTimeout(function(){
                                                    bookingCustomerTypeID = $('select[name="customer_type_id"]').val();
                                                    console.log('bookingCustomerTypeID',bookingCustomerTypeID);
                                                    handleCustomerTypeChange(bookingCustomerTypeID);
                                                }, 1500);
                                            },
                                            onclose: function (e) {
                                                if (typeof token.attr("id") !== "undefined") {
                                                    if (that.deferredCreatingCustomer) {
                                                        that.deferredCreatingCustomer.resolve();
                                                    }
                                                } else {
                                                    if (that.deferredCreatingCustomer) {
                                                        that.deferredCreatingCustomer.reject();
                                                    }
                                                }
                                                that.isCreatingCustomer = false;
                                                tokenField.tokenfield("enable");
                                            }
                                        }
                                    );
                                } else if (typeof token.attr("id") !== "undefined") {
                                    if (that.deferredCreatingCustomer) {
                                        that.deferredCreatingCustomer.resolve();
                                    }
                                    that.isCreatingCustomer = false;
                                } else {
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
                var customer_type_id = item.customer_type_id;
                var background_color = '';
                var blacklist = '';
                if (customer_type_id == '-1') {
                    background_color = "background-color: #FF0000";
                    blacklist = true;
                } else if (customer_type_id == '-2') {
                    background_color = "background-color: #DAA520";
                }
                return $("<li>", {
                    style: background_color
                })
                    .data("ui-autocomplete-item", item)
                    .append(
                        $("<a/>").append(
                            $("<div/>", {
                                text: item.value,
                                style: background_color
                            }).append(
                                $("<div/>", {
                                    class: "small",
                                    text: ((item.email) ? item.email : '') + ((item.phone) ? " - " + item.phone : '') + ((item.city) ? " - " + item.city : '') + ((item.country) ? " - " + item.country : '')
                                })
                            )
                        ).on('click', function () {
                            if (blacklist) {
                                $('#confirm-blacklist-customer')
                                    .modal('show')
                                    .on('hidden.bs.modal', function () {
                                        if (($("#booking-modal").data('bs.modal') || {}).isShown)
                                            $("body").addClass("modal-open");
                                    });
                                $('.confirm-customer').on('click', function () {
                                    var flag = $(this).attr('flag');
                                    if (flag == 'cancel') {
                                        var id = item.id;
                                        $('#' + id).remove();
                                        $('#confirm-blacklist-customer').modal('hide');
                                        return false;
                                    } else {
                                        $('#confirm-blacklist-customer').modal('hide');
                                        return false;
                                    }
                                });
                            }
                        })
                    )
                    .appendTo(ul);
            };

            that.$modalBody.find(".tokenfield").sortable();
        },
        _tokenizeBookedByField: function (customers) {
            var that = this;

            var tokenField = this.$modalBody.find(".booked_by");
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
                    var token = $(".booked-by-block .tokenfield .token:last");
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
                            if (!customer_selected_from_autocomplete) {
                                that.deferredExistingCustomerConfirmation = $.Deferred();
                                if (existing_customer) {
                                    that._initializeInnerModal();
                                    that._populateExistingCustomerConfirmationModal(token, existing_customer);
                                } else {
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
                                                } else {
                                                    if (that.deferredCreatingCustomer) {
                                                        that.deferredCreatingCustomer.reject();
                                                    }
                                                }
                                                that.isCreatingCustomer = false;
                                                tokenField.tokenfield("enable");
                                            }
                                        }
                                    );
                                } else if (typeof token.attr("id") !== "undefined") {
                                    if (that.deferredCreatingCustomer) {
                                        that.deferredCreatingCustomer.resolve();
                                    }
                                    that.isCreatingCustomer = false;
                                } else {
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

            if (this.booking.current_room_type_id == null) {
                this.booking.current_room_type_id = this.$modalBody.find("[name='room_type_id']").val();
            }

            if (roomTypeDIV == undefined) {
                var roomTypeDIV = this.$modalBody.find(".room-type");
                //roomTypeDIV.attr("id", this.booking.current_room_type_id);
            }

            if (
                that.$modalBody.find("[name='check_in_date']").val() === '' ||
                that.$modalBody.find("[name='check_out_date']").val() === ''
            ) {
                return;
            }

            var checkInDate = innGrid.enableHourlyBooking == 1 ? moment(innGrid._getBaseFormattedDate(that.$modalBody.find("input[name='check_in_date']").val()) + ' ' + that.convertTimeFormat(that.$modalBody.find("[name='check_in_time']").val())).format('YYYY-MM-DD HH:mm:ss') : moment(innGrid._getBaseFormattedDate(that.$modalBody.find("input[name='check_in_date']").val()) + ' ' + '00:00:00').format('YYYY-MM-DD HH:mm:ss');
            var checkOutDate = innGrid.enableHourlyBooking == 1 ? moment(innGrid._getBaseFormattedDate(that.$modalBody.find("input[name='check_out_date']").val()) + ' ' + that.convertTimeFormat(that.$modalBody.find("[name='check_out_time']").val())).format('YYYY-MM-DD HH:mm:ss') : moment(innGrid._getBaseFormattedDate(that.$modalBody.find("input[name='check_out_date']").val()) + ' ' + '00:00:00').format('YYYY-MM-DD HH:mm:ss');

            var roomTypeDDL = $("<select/>", {
                title: 'Room Type',
                name: 'room_type_id',
                class: 'form-control',
                //style: 'max-width: 320px;'
            }).on('change', function () {
                
                var maxAdult = roomTypeDIV.find('select[name="room_type_id"] option:selected').attr('data-max_adults');
                var maxChildren = roomTypeDIV.find('select[name="room_type_id"] option:selected').attr('data-max_children');

                if($('select[name=adult_count]').val() > maxAdult){
                    alert(maxAdult + (maxAdult > 1 ? 'Adults are' : ' Adult is') + l(' not compatible, please update max adults in selected room type', true));
                    $('select[name=adult_count]').val(1);
                }

                if($('select[name=children_count]').val() > maxChildren){
                    alert(maxChildren + (maxChildren > 1 ? ' Children are' : ' Child is') + l(' not compatible, please update max children in selected room type'));
                    $('select[name=children_count]').val(0);
                }
                
                
                that._updateAccommodationDDL(roomTypeDIV);
                that._updateRoomDDL(roomTypeDIV);
                that._updateChargeWithDDL(roomTypeDIV);
            });

            var get_available_room_types_callback = function (data) {
                if (data !== '' && data !== null && data.length > 0) {
                    for (var i in data) {
                        var option = $("<option/>", {
                            value: data[i].id,
                            text: data[i].name + " (" + data[i].availability + ")",
                            'data-max_occupancy': data[i].max_occupancy,
                            'data-min_occupancy': data[i].min_occupancy,
                            'data-max_adults': data[i].max_adults,
                            'data-max_children': data[i].max_children,
                            'data-room_type_availability': data[i].availability
                        });

                        if (data[i].availability < 1) {
                            option.prop("disabled", true);
                        }

                        if (that.booking.booking_blocks && that.booking.booking_blocks[0].room_type_id === data[i].id) {
                            option.prop("selected", true);
                        }

                        defaultRoomCharge[data[i].id] = data[i].default_room_charge;

                        roomTypeDDL.append(option);
                    }
                }

                that.$modalBody.find(".room-type-ddl-span").html(
                    $('<div/>')
                        .append(roomTypeDDL)
                );

                // if current room type is already set (opening an existing booking), select that room type
                if (that.booking.current_room_type_id)
                    that.$modalBody.find("[name='room_type_id']").val(that.booking.current_room_type_id);

                that._updateChargeWithDDL(roomTypeDIV);
                that._updateRoomDDL(roomTypeDIV);
                that._updateAccommodationDDL(roomTypeDIV);
            };

            var roomTypeKey = checkInDate + '-' + checkOutDate;
            if (typeof that.roomTypesCache[roomTypeKey] !== "undefined" && that.roomTypesCache[roomTypeKey]) {

                get_available_room_types_callback(that.roomTypesCache[roomTypeKey]);

            } else {

                $.post(getBaseURL() + 'booking/get_available_room_types_in_JSON', {
                    check_in_date: encodeURIComponent(checkInDate),
                    check_out_date: encodeURIComponent(checkOutDate),
                    isAJAX : true
                }, get_available_room_types_callback, 'json');

                // $.getJSON(getBaseURL() + 'booking/get_available_room_types_in_JSON' ,
                //     get_available_room_types_callback
                // );
            }


            $('.room-section').removeClass('hidden');
        },
        _updateAccommodationDDL: function (roomTypeDIV) {
            var max_occupancy = roomTypeDIV.find('select[name="room_type_id"] option:selected').attr('data-max_occupancy');
            var min_occupancy = roomTypeDIV.find('select[name="room_type_id"] option:selected').attr('data-min_occupancy');
            var max_adult = roomTypeDIV.find('select[name="room_type_id"] option:selected').attr('data-max_adults');
            var max_child = roomTypeDIV.find('select[name="room_type_id"] option:selected').attr('data-max_children');
            roomTypeDIV.find('select[name=adult_count]').find('option').each(function () {
                if (Number($(this).val()) > max_adult) {
                    $(this).prop('disabled', true).hide();
                } else {
                    $(this).prop('disabled', false).show();
                }
            });
            roomTypeDIV.find('select[name=children_count]').find('option').each(function () {
                if (Number($(this).val()) > max_child) {
                    $(this).prop('disabled', true).hide();
                } else {
                    $(this).prop('disabled', false).show();
                }
            });
        },
        //Populate room drop down list based on checkin, checkout, and roomtype
        _updateRoomDDL: function (roomTypeDIV) {

            var that = this;
            var curr_room_id = that.booking && that.booking.current_room_id ? that.booking.current_room_id : null;
            curr_room_id = curr_room_id ? curr_room_id : (this.options && this.options.selected_room_id ? this.options.selected_room_id : $("[data-booking-id=" + that.booking.booking_id + "]").attr("data-room-id"));

            var checkInDate = moment(innGrid._getBaseFormattedDate(this.$modalBody.find("input[name='check_in_date']").val()) + ' ' + that.convertTimeFormat(this.$modalBody.find("[name='check_in_time']").val())).format('YYYY-MM-DD HH:mm:ss');
            var checkOutDate = moment(innGrid._getBaseFormattedDate(this.$modalBody.find("input[name='check_out_date']").val()) + ' ' + that.convertTimeFormat(this.$modalBody.find("[name='check_out_time']").val())).format('YYYY-MM-DD HH:mm:ss');

            var currentSellingDate = $("#sellingDate").val();
            var roomTypeID = roomTypeDIV.find("[name='room_type_id'] option:selected").val();
            var booking_blocks = that.booking.booking_blocks;

            if (booking_blocks && booking_blocks.length > 0) {
                $.each(booking_blocks, function (i, val) {
                    if (parseInt(curr_room_id) == parseInt(val.room_id)) {
                        checkInDate = val.check_in_date;
                        checkOutDate = val.check_out_date;
                    }
                });
            }
            var select = $("<select/>", {
                title: 'Room(s)',
                name: 'room_id',
                class: 'form-control room',
                style: 'max-width: 320px;'
            });

            var get_available_rooms_callback = function (data) {

                if (data !== '' && data !== null && data.length > 0) {
                    // remember the room type of currently viewing list of rooms.
                    // this prevents room type going resetting to the first one in the list whenever checkin/checkout dates change
                    if (!innGrid.isShowUnassignedRooms && !isShowUnassignedRooms) {
                        var option = $("<option/>", {
                            value: '0',
                            text: l('Not Assigned')
                        });

                        select.append(option);
                    } else if (that.booking.booking_id) {
                        var option = $("<option/>", {
                            value: '',
                            text: ''
                        });
                        option.prop("selected", "selected");
                        option.prop("disabled", "disabled");
                        select.append(option);
                    }

                    for (var i in data) {
                        option = $("<option/>", {
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


                roomTypeDIV.find(".room-ddl-span").html(
                    $('<span/>', {
                        class: 'form-group'
                    })
                        //.prepend("in room ")
                        //.append($('<span/>', {style: "color:red;", text: "* "}))
                        .append(select)
                );

                that.deferredRoomDDL.resolve();

                // don't show rate-info for out-of-orders
                if (that.$modalBody.find("[name='state']").val() !== '3')
                    that.$modalBody.find(".rate-info").fadeIn();
            };

            var roomKey = checkInDate + '-' + checkOutDate + '-' + roomTypeID + '-' + that.booking.booking_id + '-' + curr_room_id;
            if (typeof that.roomsCache[roomKey] !== "undefined" && that.roomsCache[roomKey]) {

                get_available_rooms_callback(that.roomsCache[roomKey]);

            } else {
                $.ajax({
                    type: "POST",
                    url: getBaseURL() + 'booking/get_available_rooms_in_AJAX/',
                    data: {
                        check_in_date: checkInDate,
                        check_out_date: checkOutDate,
                        room_type_id: roomTypeID,
                        room_id: curr_room_id,
                        booking_id: that.booking.booking_id
                    },
                    dataType: "json",
                    success: get_available_rooms_callback
                });
            }

            var height = $('.content').height() + 50;
            $('.left-sidebar').css('height', height);
        },
        // Populate Rate Schedule block
        _populateRateScheduleDDL: function () {
            $('#rate_schedule').find('.table').find('.table_data').html('');
            var that = this;
            var checkIn = that.booking.check_in_date;
            var checkOut = that.booking.check_out_date;

            var start = new Date(checkIn);
            var end = new Date(checkOut);
            var diff = new Date(end - start);
            var days = diff / 1000 / 60 / 60 / 24;

            for (i = 0; i <= days; i++) {
                $('#rate_schedule').find('.table').append(
                    $("<tr/>", {
                        class: "table_data"
                    }).append(
                        $("<td/>", {
                            style: 'width:185px;',
                            'text': checkIn.split(' ')[0]
                        })
                    ).append(
                        $("<td/>", {
                            style: 'width:310px;'
                        }).append(
                            $("<div/>", {
                                class: 'room-span col-sm-7',
                                style: 'padding:0'
                            })
                        )
                    ).append(
                        $("<td/>", {
                            style: 'width:170px;'
                        }).append(
                            $("<div/>", {
                                class: "form-group col-sm-6",
                                style: 'padding:0'
                            })
                                .append(
                                    $("<input/>", {
                                        class: 'form-control',
                                        value: '$45.00'
                                    })
                                )
                        )
                    )
                );
                var date = new Date(checkIn);
                var d = new Date(date.setDate(date.getDate() + 1));
                var yyyy = d.getFullYear().toString();
                var mm = (d.getMonth() + 1).toString(); // getMonth() is zero-based
                var dd = d.getDate().toString();
                checkIn = yyyy + "-" + (mm[1] ? mm : "0" + mm[0]) + "-" + (dd[1] ? dd : "0" + dd[0]);
            }
            var rooms = $('.modal-body').find('.room-ddl-span').html();
            var roomSelected = $('.modal-body').find('select[name=room_id]').val();
            $('#rate_schedule').find('.room-span').append(rooms);
            $('#rate_schedule').find('select[name=room_id]').val(roomSelected);
            $('#rate_schedule').find('select[name=room_id]').attr('disabled', true);
        },
        // Populate registration card
        _populateRegistrationCardDDL: function () {
            var that = this;
            var show_rate = that.booking.show_rate_on_registration_card;
            var show_logo = that.booking.show_logo_on_registration_card;

            $.ajax({
                type: "POST",
                url: getBaseURL() + 'booking/get_registration_card_info_AJAX/',
                data: {
                    booking_id: that.booking.booking_id
                },
                dataType: "json",
                success: function (data) {
                    var customer_fields = data['customer_fields'];
                    var booking_fields = data['booking_fields'];
                    var check_in_policies = data['check_in_policies'];
                    var policies = (check_in_policies) ? (check_in_policies.replace(/\n/g, '')) : '';
                    var staying_customers = data['booking']['staying_customers'];
                    var comapany_logo = '';
                    if (show_logo == 1 && data['company_logo'] != "undefined" && data['company_logo'] != null) {
                        comapany_logo = '<img class="img" src="https://'+getenv("AWS_S3_BUCKET")+'.s3.amazonaws.com/' + data['company']['company_id'] + '/' + data['company_logo'] + '" id="company-logo-image"/><br/>';
                    }

                    $('#registration_card').html('');
                    $('#registration_card').append(
                        $("<div/>", {
                            style: 'text-align:right;'
                        })
                            .append(
                                $('<button/>', {
                                    class: 'btn btn-primary btn-sm',
                                    text: l('print'),
                                    style: 'margin: 0px 0px 10px 0px;'
                                }).on('click', function (e) {
                                    $('.full_registration').printThis();
                                })
                            )
                    )
                    $('#registration_card').append(
                        $("<div/>", {
                            class: "row full_registration"
                        })
                            .append(
                                $("<div/>", {
                                    class: 'panel panel-default'
                                })
                                    .append(
                                        $("<div/>", {
                                            class: 'panel-header h3 text-center',
                                            html: comapany_logo
                                        })
                                            .append(
                                                $("<h3/>", {
                                                    class: 'h3',
                                                    text: data['company']['name'] + ' ' + l('Registration Card'),
                                                    style: 'margin-bottom: 0'
                                                })
                                            )
                                            .append(
                                                $("<small/>", {
                                                    class: 'panel-header h3 text-center',
                                                    text: l('Confirmation Number')+': ' + data['booking']['booking_id']
                                                })
                                            )
                                    )
                                    .append(
                                        $("<div/>", {
                                            class: 'panel-body'
                                        })
                                            .append(
                                                $("<div/>", {
                                                    class: 'col-xs-6'
                                                })
                                                    .append(
                                                        $("<strong/>", {
                                                            text: l('Customer information')
                                                        })
                                                    )
                                                    .append(
                                                        $("<table/>", {
                                                            class: 'col-xs-12'
                                                        })
                                                            .append(
                                                                $("<ul/>", {
                                                                    class: 'list-unstyled customer_info'
                                                                })
                                                                    .append(
                                                                        $("<li/>", {
                                                                            text: l('Customer Name')+': ' + (typeof (data['customer']) != "undefined" && data['customer'] !== null) ? data['customer']['customer_name'] : ''
                                                                        })
                                                                    )
                                                                    .append(
                                                                        $("<li/>", {
                                                                            text: l('Phone')+': ' + (typeof (data['customer']) != "undefined" && data['customer'] !== null) ? data['customer']['phone'] : ''
                                                                        })
                                                                    )
                                                                    .append(
                                                                        $("<li/>", {
                                                                            text: l('Address')+': ' + (typeof (data['customer']) != "undefined" && data['customer'] !== null) ? data['customer']['address'] : ''
                                                                        })
                                                                    )
                                                                    .append(
                                                                        $("<li/>", {
                                                                            text: l('Email')+': ' + (typeof (data['customer']) != "undefined" && data['customer'] !== null) ? data['customer']['email'] : ''
                                                                        })
                                                                    )
                                                            )
                                                    )
                                            )
                                            .append(
                                                $("<div/>", {
                                                    class: 'col-xs-6 text-right'
                                                })
                                                    .append(
                                                        $("<strong/>", {
                                                            text: l('Booking information')
                                                        })
                                                    )
                                                    .append(
                                                        $("<table/>", {
                                                            class: 'col-xs-12'
                                                        })
                                                            .append(
                                                                $("<ul/>", {
                                                                    class: 'list-unstyled booking_info'
                                                                })
                                                                    .append(
                                                                        $("<li/>", {
                                                                            text: $('#RoomSingular').val() + ':' + data['booking']['room_name']
                                                                        })
                                                                    )
                                                                    .append(
                                                                        $("<li/>", {
                                                                            text: $('#RoomType').val() + ':' + data['booking']['room_type_name']
                                                                        })
                                                                    )
                                                                    .append(
                                                                        $("<li/>", {
                                                                            text: l('Check-in Date')+': ' + data['booking']['check_in_date'].split(' ')[0]
                                                                        })
                                                                    )
                                                                    .append(
                                                                        $("<li/>", {
                                                                            text: l('Check-out Date')+': ' + data['booking']['check_out_date'].split(' ')[0]
                                                                        })
                                                                    )
                                                                    .append(
                                                                        $("<li/>", {
                                                                            text: l('Number of Adults')+': ' + data['booking']['adult_count']
                                                                        })
                                                                    )
                                                                    .append(
                                                                        $("<li/>", {
                                                                            text: l('Number of Children')+': ' + data['booking']['children_count']
                                                                        })
                                                                    )
                                                                    .append(
                                                                        $("<li/>", {
                                                                            text: (show_rate == '1') ? (l('Rate')+'*: ' + data['booking']['rate']) : ''
                                                                        })
                                                                    )
                                                                    .append(
                                                                        $("<li/>", {
                                                                            text: (show_rate == '1') ? (l('Total')+'*: ' + data['booking']['charge_total']) : ''
                                                                        })
                                                                    )
                                                            )
                                                    )
                                            )
                                            .append(
                                                $("<div/>", {
                                                    class: 'jumbotron registration-policies',
                                                    html: policies
                                                })
                                                    /*
                                                .append(
                                                    $("<div/>", {
                                                        class: 'jumbotron rate_text',
                                                    text: (show_rate == '1') ? ("The rate displayed is the rate for check-in date only. Additional taxes or extra fees may apply.Rates may change during guest's stay due to seasonal rate changes.For a detailed information on rates, please ask receptionist for an invoice.") : ''
                                                    })
                                                )
                        */
                                                    .append(
                                                        $("<div/>", {
                                                            class: '',
                                                            style: 'padding-top: 20px;',
                                                            text: l('Signature(s)')+": _______________________________"
                                                        })
                                                    )
                                                    .append(
                                                        $("<div/>", {
                                                            class: '',
                                                            style: 'padding-top: 20px;',
                                                            text: l('Date')+": _______________________________"
                                                        })
                                                    )
                                            )
                                    )
                            )
                    );
                    var customers = [];
                    for (var key in staying_customers) {
                        customers.push(staying_customers[key]['customer_name']);
                    }

                    if (customers) {
                        $('.customer_info').append(
                            $("<li/>", {
                                text: l('Other guests')+": " + customers.toString()
                            })
                        )
                    }

                    for (var key in customer_fields) {
                        if (data['customer'] && data['customer']['customer_fields']) {
                            var value = data['customer']['customer_fields'][customer_fields[key]['id']] != null ? data['customer']['customer_fields'][customer_fields[key]['id']] : '';
                            $('.customer_info').append(
                                $("<li/>", {
                                    text: customer_fields[key]['name'] + ': ' + value
                                })
                            )
                        }
                    }

                    for (var key in booking_fields) {
                        var value = booking_fields[key]['value'];
                        if (value) {
                            $('.booking_info').append(
                                $("<li/>", {
                                    text: booking_fields[key]['name'] + ': ' + value
                                })
                            )
                        }
                    }

                    if (show_rate == '0') {
                        $('.rate_text').css('display', 'none');
                    }
                }
            });

        },
        // Populate payment_card
        //  _populatePaymentCardDDL: function () {

        //                 $('#payment_details').html('');
        //                 $('#payment_details').append(
        //                                         $("<div/>", {
        //                                             style: 'text-align:right;'
        //                                         })
        //                                         .append(
        //                                             $('<button/>', {
        //                                                 class: 'btn btn-primary btn-sm',
        //                                                 text: l('print'),
        //                                                 style: 'margin: 0px 0px 10px 0px;'
        //                                             }).on('click', function (e) {
        //                                                 $('.full_registration').printThis();
        //                                             })
        //                                         )
        //                                     )

        // }, 
        // set height of right sidebar
        _setHeight: function (tab) {
            var height = $('#' + tab).height() + 100;
            if ($('#' + tab).height() < 320) {
                $('.left-sidebar').css('height', '350');
            } else {
                $('.left-sidebar').css('height', height);
            }
        },
        _validateCapacity: function () {
            var adult_count = this.$modalBody.find('[name="adult_count"] option:selected').val();
            var children_count = this.$modalBody.find('[name="children_count"] option:selected').val();
            var $selected_room_type = this.$modalBody.find("[name='room_type_id'] option:selected");
            var max_adults = $selected_room_type.data('max_adults');
            var max_children = $selected_room_type.data('max_children');
            var max_occupancy = $selected_room_type.data('max_occupancy');
            var total = parseInt(adult_count) + parseInt(children_count);
            var min_occupancy = $selected_room_type.data('min_occupancy');
            var that = this;
            if (adult_count > max_adults || children_count > max_children) {
                $('#reservation-message').modal('show');
                $('#reservation-message .message').html(l('Maximum occupancy required for this room type is')+" \n "+l('Maximun adults')+" " + max_adults + " \n"+l('Maximun children')+" " + max_children);
                $('.confirm-customer').on('click', function () {
                    $('#reservation-message').modal('hide');
                    $('#reservation-message').on('hidden.bs.modal', function () {
                        $('body').addClass('modal-open');
                    });
                    return false;
                });
                if (adult_count > max_adults)
                    this.$modalBody.find('[name="adult_count"]').val(max_adults);
                if (children_count > max_children)
                    this.$modalBody.find('[name="children_count"]').val(max_children);
            } else if (max_occupancy && total > max_occupancy) {
                $('#reservation-message').modal('show');
                $('#reservation-message .message').html(l('Maximum occupancy required for this room type is')+" " + max_occupancy);
                $('.confirm-customer').on('click', function () {
                    $('#reservation-message').modal('hide');
                    $('#reservation-message').on('hidden.bs.modal', function () {
                        that.$modalBody.find('[name="adult_count"]').val(1);
                        that.$modalBody.find('[name="children_count"]').val(0);
                        $('body').addClass('modal-open');
                    });

                    return false;
                });
            } else if (min_occupancy && total < min_occupancy) {
                $('#reservation-message').modal('show');
                $('#reservation-message .message').html(l('Minimum occupancy required for this room type is')+" " + min_occupancy);
                $('.confirm-customer').on('click', function () {
                    $('#reservation-message').modal('hide');
                    $('#reservation-message').on('hidden.bs.modal', function () {
                        $('body').addClass('modal-open');
                    });
                    return false;
                });
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
                        charge_type_id: roomTypeDIV.find(".charge-with").val(),
                        rate: rate
                    },
                    function (tax) {

                        var taxedRate = rate * (1 + parseFloat(tax.percentage)) + parseFloat(tax.flat_rate);
                        //var rateInclusiveTax = rate * (parseFloat(tax.inclusive_tax_percentage)) + parseFloat(tax.inclusive_tax_flat_rate);
                        var rateInclusiveTax = rate * (1 - (1 / (1 + parseFloat(tax.inclusive_tax_percentage)))) + parseFloat(tax.inclusive_tax_flat_rate);
                        that.rateWithTax = taxedRate;
                        that.rateInclusiveTax = rateInclusiveTax;

                        // console.log(number_format(number_format(taxedRate, 3, ".", ""), 2, ".", ""));
                        taxedRate = number_format(number_format(taxedRate, 3, ".", ""), 2, ".", "");
                        // taxedRate = parseFloat((Math.round(taxedRate * 1000) / 1000).toFixed(2));
                        // console.log(taxedRate);
                        //taxedRate = Math.round(taxedRate * 100) / 100;

                        var rateIncludingTaxDiv = roomTypeDIV.find('.rate-including-tax');
                        rateIncludingTaxDiv.text("("+l('with tax')+": " + number_format(taxedRate, 2, ".", "") + ")");

                        if (tax.percentage != 0 || (tax.flat_rate != 0 && rate != '')) {
                            rateIncludingTaxDiv.removeClass("hidden");
                            //roomTypeDIV.find('.rate-block').removeClass('col-sm-3').addClass('col-sm-4');
                            //roomTypeDIV.find('.pay-period-block').removeClass('col-sm-3').addClass('col-sm-2');
                            //$('input[name=rate]').css('padding', '0px');
                            //roomTypeDIV.find('.rate-block').css('padding-right', '0px');
                            //roomTypeDIV.find('input[name=pay_period]').css('padding', '2px');
                            //roomTypeDIV.find('.pay-period-block').css('padding-left', '4px');
                            roomTypeDIV.find('input[name=rate]').css('border-top-right-radius', '0px');
                            roomTypeDIV.find('input[name=rate]').css('border-bottom-right-radius', '0px');
                        } else {
                            rateIncludingTaxDiv.addClass("hidden");
                            //roomTypeDIV.find('.rate-block').removeClass('col-sm-4').addClass('col-sm-3');
                            //roomTypeDIV.find('.pay-period-block').removeClass('col-sm-2').addClass('col-sm-3');
                            roomTypeDIV.find('input[name=rate]').css('padding', '');
                            //roomTypeDIV.find('.rate-block').css('padding-right', '');
                            //roomTypeDIV.find('input[name=pay_period]').css('padding', '');


                            roomTypeDIV.find('input[name=rate]').css('border-top-right-radius', '4px');
                            roomTypeDIV.find('input[name=rate]').css('border-bottom-right-radius', '4px');
                        }

                        that._displayRateInfo(null, null, roomTypeDIV);

                        var height = $('.content').height() + 50;
                        $('.left-sidebar').css('height', height);

                    }, 'json'
                );

            } else if (
                roomTypeDIV.find('[name="adult_count"]').val() != "" &&
                roomTypeDIV.find('[name="children_count"]').val() != "" &&
                roomTypeDIV.find('[name="check_in_date"]').val() != "" &&
                roomTypeDIV.find('[name="check_out_date"]').val() != ""
            ) {
                var old_check_out_date = innGrid._getBaseFormattedDate($('#old_check_out_date').val());
                old_check_out_date = moment(old_check_out_date).format('YYYY-MM-DD');

                if(
                    (innGrid.isGroupBookingFeatures != true)
                ){
                    roomTypeDIV.find("[name='rate']").attr("disabled", true);
                }
                roomTypeDIV.find("[name='pay_period']").attr("disabled", true).val(0);

                var current_rate_plan_id = roomTypeDIV.find('.charge-with').val();
                var ratePlanID = current_rate_plan_id;
                if (old_check_out_date < moment(innGrid._getBaseFormattedDate(roomTypeDIV.find('[name="check_out_date"]').val())).format('YYYY-MM-DD')) {
                    $.ajax({
                        type: "POST",
                        url: getBaseURL() + 'rate_plan/get_parent_rate_plan_id',
                        data: {rate_plan_id: current_rate_plan_id},
                        dataType: 'json',
                        success: function (response) {
                            if (response.success) {
                                var ratePlanID = response.id;
                                if (ratePlanID)
                                    rateArrayJSON(ratePlanID);
                                else
                                    rateArrayJSON(current_rate_plan_id);
                            }
                        }
                    });
                } else {
                    rateArrayJSON(ratePlanID);
                }

                function rateArrayJSON(ratePlanID) {
                    $.post(getBaseURL() + "rate_plan/get_rate_array_JSON", {
                            date_start: innGrid._getBaseFormattedDate($('[name="check_in_date"]').val()),
                            date_end: innGrid._getBaseFormattedDate($('[name="check_out_date"]').val()),
                            rate_plan_id: ratePlanID,
                            adult_count: roomTypeDIV.find('[name="adult_count"]').val(),
                            children_count: roomTypeDIV.find('[name="children_count"]').val()
                        }, function (data) {
                            if (data[0] !== undefined) {
                                var rate = data[0].rate;
                                rate = ((show_decimal) ? parseFloat(rate).toFixed(2) : parseInt(rate));

                                console.log('rate',rate);
                                console.log('rate11',roomTypeDIV.find("[name='rate']").val());

                                var rate_val = rate;
                                if(innGrid.isGroupBookingFeatures == true){

                                    var oldRatePlanId = $('#currentRatePlanID').val();

                                    if(roomTypeDIV.find("[name='rate']").val() == 0){
                                        rate_val = rate;
                                    } 
                                    else if(oldRatePlanId != ratePlanID){
                                        rate_val = rate;
                                    } else if(roomTypeDIV.find("[name='rate']").val() != rate) {
                                        rate_val = rate;
                                    } else {
                                        rate_val = roomTypeDIV.find("[name='rate']").val();
                                    }

                                    $('#currentRatePlanID').val(ratePlanID);

                                    data[0].rate = rate_val;
                                }

                                if($('#current_rate_plan_type').val() !== 'per_person_type'){
                                roomTypeDIV.find("[name='rate']").val(rate);
                                } 
                                if(innGrid.isGroupBookingFeatures == true){
                                    roomTypeDIV.find("[name='rate']").val(rate_val);
                                }

                                $.post(getBaseURL() + "rate_plan/get_tax_amount_from_rate_plan_JSON/",
                                    {
                                        rate_plan_id: roomTypeDIV.find('.charge-with option:selected').val(),
                                        rate: rate
                                    },
                                    function (tax) {

                                        if(innGrid.isGroupBookingFeatures == true)
                                            var taxedRate = rate_val * (1 + parseFloat(tax.percentage)) + parseFloat(tax.flat_rate);
                                        else
                                        var taxedRate = rate * (1 + parseFloat(tax.percentage)) + parseFloat(tax.flat_rate);
                                        //taxedRate = Math.round(taxedRate * 100) / 100;
                                        var rateIncludingTaxDiv = roomTypeDIV.find('.rate-including-tax');

                                        rateIncludingTaxDiv.text("("+l('with tax')+": " + number_format(taxedRate, 2, ".", "") + ")");

                                        if (tax.percentage != 0) {
                                            rateIncludingTaxDiv.removeClass("hidden");
                                        } else {
                                            rateIncludingTaxDiv.addClass("hidden");
                                        }
                                        that.rateWithTax = taxedRate;
                                        if(innGrid.isGroupBookingFeatures == true)
                                            that.rateInclusiveTax = rate_val * (parseFloat(tax.inclusive_tax_percentage)) + parseFloat(tax.inclusive_tax_flat_rate);
                                        else
                                        that.rateInclusiveTax = rate * (parseFloat(tax.inclusive_tax_percentage)) + parseFloat(tax.inclusive_tax_flat_rate);
                                        that._displayRateInfo(data, tax);
                                    }, 'json'
                                );
                            }
                        }, 'json'
                    );
                }
            }
            this._updatePayPeriodDropdown();
        },
        _updateNumberOfDays: function () {
            var that = this;
            var checkInDate = innGrid._getBaseFormattedDate(this.$modalBody.find("[name='check_in_date']").val());
            var checkOutDate = innGrid._getBaseFormattedDate(this.$modalBody.find("[name='check_out_date']").val());
            if (!checkInDate || !checkOutDate) {
                return;
            }

            // Apply each element to the Date function
            var check_in_date = new Date(checkInDate);
            var check_out_date = new Date(checkOutDate);
            var oneDay = 24 * 60 * 60 * 1000; // hours*minutes*seconds*milliseconds
            var diffDays = Math.round(Math.abs((check_in_date.getTime() - check_out_date.getTime()) / (oneDay)))
            
            if($.isNumeric(diffDays))
                this.$modalBody.find("[name='number_of_days']").val(diffDays);
            else
                this.$modalBody.find("[name='number_of_days']").val(0);

            that._updatePayPeriodDropdown();
        },
        _updateChargeWithDDL: function (roomTypeDIV) {

            var that = this;

            var roomTypeID = roomTypeDIV.find("[name='room_type_id']").val();
            roomTypeID = roomTypeID ? roomTypeID : that.booking.current_room_type_id;

            var select = $("<select/>", {
                class: 'form-control charge-with form-group',
                //style: 'max-width: 300px;'
                room_type_id: roomTypeID
            })

            var chargeTypeOptionGroup = $("<optgroup/>", {
                label: l("Charge Types (Manual)")
            });

            if (!innGrid.ajaxCache.chargeTypes) {
                $.getJSON(getBaseURL() + 'booking/get_charge_types_in_JSON',
                    function (data) {
                        innGrid.ajaxCache.chargeTypes = data;
                        that._getRatePlans(that, roomTypeDIV, roomTypeID, innGrid.ajaxCache.chargeTypes, select, chargeTypeOptionGroup);
                    }
                );
            } else {
                that._getRatePlans(that, roomTypeDIV, roomTypeID, innGrid.ajaxCache.chargeTypes, select, chargeTypeOptionGroup);
            }

        },
        _getRatePlans: function (that, roomTypeDIV, roomTypeID, data, select, chargeTypeOptionGroup) {

            var get_rate_plans_callback = function (ratePlan) {

                var selectedChargeType = null;

                if (data !== '' && data !== null && data.length > 0) {

                    for (var i in data) {
                        var option = $("<option/>", {
                            value: data[i].id,
                            text: data[i].name,
                            data_rp_name: data[i].name,
                        });

                        if (that.booking.charge_type_id == data[i].id &&
                            that.booking.use_rate_plan == 0) {
                            option.prop("selected", true);
                            selectedChargeType = true;
                        }
                        else if (!selectedChargeType && defaultRoomCharge[roomTypeID] == data[i].id) {
                            option.prop("selected", true);
                            selectedChargeType = true;
                        }

                        chargeTypeOptionGroup.append(option);
                    }
                }

                select.append(chargeTypeOptionGroup);

                if (ratePlan !== '' && ratePlan !== null && ratePlan.length > 0) {
                    // Set Rate Plan DDL
                    var ratePlanOptionGroup = $("<optgroup/>", {
                        label: l("Rate Plans (Pre-set)")
                    });

                    for (var i in ratePlan) {
                        var option = $("<option/>", {
                            value: ratePlan[i].rate_plan_id,
                            parent_rate_plan_id: ratePlan[i].parent_rate_plan_id,
                            text: ratePlan[i].rate_plan_name,
                            data_rp_name: ratePlan[i].rate_plan_name,
                            class: 'rate-plan'
                        });
                        if (that.booking.rate_plan_id == ratePlan[i].rate_plan_id &&
                            that.booking.use_rate_plan == 1) {
                            option.prop("selected", true);
                            selectedChargeType = true;
                        } else if (!selectedChargeType && defaultRoomCharge[roomTypeID] == ratePlan[i].rate_plan_id) {
                            option.prop("selected", true);
                            selectedChargeType = true;
                        }
                        ratePlanOptionGroup.append(option);
                    }
                }

                select.append(ratePlanOptionGroup)
                    .on('change', function () {
                        that._updateRate($(this).closest('.room-type'));
                    });

                if (innGrid.featureSettings.allow_free_bookings) {
                    select.append(
                        $("<option/>", {
                            value: '',
                            selected: (!selectedChargeType ? 'selected' : false),
                            text: l("None (FREE)")
                        })
                    );
                }
                else if (select.find(":selected").val() === undefined) {
                    // If nothing's been selected (This ususally occurs if previously selected rate plan has been deleted)
                    select.append(
                        $("<option/>", {
                            selected: 'selected',
                            text: l("NOT SELECTED")
                        })
                    );
                }
                that.deferredChargeWithDDL.resolve();
                roomTypeDIV.find(".charge-with-div").find('.charge-with').remove();
                roomTypeDIV.find(".charge-with-div").append(select);
                that._updateRate(roomTypeDIV);

            };

            var ratePlanKey = roomTypeID + '-' + that.booking.rate_plan_id;
            if (typeof that.ratePlanCache[ratePlanKey] !== "undefined" && that.ratePlanCache[ratePlanKey]) {

                get_rate_plans_callback(that.ratePlanCache[ratePlanKey]);

            } else {

                $.post(getBaseURL() + 'booking/get_rate_plans_JSON/', {
                    room_type_id: roomTypeID,
                    previous_rate_plan_id: that.booking.rate_plan_id
                }, get_rate_plans_callback, 'json');
            }

        },
        _getSelect: function (name, options, class_name) {

            var select = $("<select/>", {
                class: 'form-control ' + class_name,
                name: name,
                style: (name == 'pay_period' ? 'padding:0px 4px;' : '')
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
                    "data-charging-scheme": data.charging_scheme
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
                    for: name,
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
                class: "form-group form-group-sm block_" + name
            }).append(
                $("<label/>", {
                    for: name,
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
        _getCustomBookingFieldInput: function (label, name, value, id, is_required) {

            var is_required_mark = "";
            if (is_required == 1) {
                is_required_mark = '<span class="custom_booking_field_required" style="color:red;">*</span>';
            }

            return $("<div/>", {
                class: "form-group col-sm-6"
            }).append(
                $("<label/>", {
                    for: name,
                    class: "control-label"
                }).append($("<small/>", {
                    text: label
                })).append(is_required_mark)
            ).append(
                $("<span/>")
                    .append(
                        $("<input/>", {
                            class: "form-control custom-booking-field restrict-cc-data",
                            name: name,
                            value: value,
                            'data-label': label,
                            id: id,
                            autocomplete: false
                        })
                    )
            )
        },
        _confirmationGroupDateModel: function (obj) {
            var that = this;

            $('#reservation-message').find('.confirm-customer[flag=cancel]').html(l("Apply to this booking only")).removeClass('hidden btn-danger').addClass('btn-success');
            $('#reservation-message').find('.confirm-customer[flag=ok]').html(l("Yes, change all bookings")).removeClass('btn-success').addClass('btn-warning');;
            $('#reservation-message').modal("show");
            $('#reservation-message .message-heading').text('Warning');
            $('#reservation-message .message').html(l("Would you like to apply the date change to ALL bookings that belong to this group?"));
            $('.confirm-customer').on('click', function () {

                var flag = $(this).attr('flag');
                if (flag == 'cancel') {
                    that.saveAllGroupDate = false;
                } else {
                    that.saveAllGroupDate = true;
                }

                $('#reservation-message').modal('hide');
                $('#reservation-message').on('hidden.bs.modal', function () {
                    $('body').addClass('modal-open');
                });
                $('#reservation-message').find('.confirm-customer[flag=cancel]').html(l("Cancel")).addClass('hidden');
                $('#reservation-message').find('.confirm-customer[flag=ok]').html(l("OK"));
                return false;
            });
        },
        _getAllGroupRoomBookingIds: function (data) {
            var that = this;
            // var bookingId = '';
            //var roomId ='';
            var roomBookingArr = [];
            var checkInDate = innGrid._getBaseFormattedDate($('#booking_detail').find('input[name="check_in_date"]').val());
            var checkOutDate = innGrid._getBaseFormattedDate($('#booking_detail').find('input[name="check_out_date"]').val());

            if (that.saveAllGroupDate == true) {
                var roomList = $('.room-lists .room-list-info');
                roomList.each(function () {
                    if ($(this).attr('data-booking-cancelled') == 'false') {
                        roomBookingArr.push({'bookingId': $(this).attr('id'), 'roomId': $(this).attr('data-room-id')});
                    }
                });
            } else if (that.saveAllGroupDate == false) {
                roomBookingArr.push({bookingId: that.booking.booking_id, roomId: that.booking.current_room_id});
            }
            var bookingData = {
                booking: {
                    new_check_in_date: moment(innGrid._getBaseFormattedDate($('#booking_detail').find('input[name="check_in_date"]').val()) + ' ' + that.convertTimeFormat($('#booking_detail').find("[name='check_in_time']").val())).format('YYYY-MM-DD HH:mm:ss'),
                    check_out_date: moment(innGrid._getBaseFormattedDate($('#booking_detail').find("[name='check_out_date']").val()) + ' ' + that.convertTimeFormat($('#booking_detail').find("[name='check_out_time']").val())).format('YYYY-MM-DD HH:mm:ss'),
                    room_booking_ar: roomBookingArr,
                    update_date: true,
                    state: $('#booking-modal select[name="state"]').val(),
                    rooms: data.rooms
                }
            };
            that._updateGroupBooking(bookingData, null, that.booking.booking_id);
        },
        _cancelDeleteGroupBookingRoom: function (action) {
            var that = this;
            var booking_ids = [];
            var booking_room_ids = [];
            var booking_check_ins = [];
            var booking_check_outs = [];
            $('.room-lists').find(".room-list-info").each(function () {
                if ($(this).find(".cancelled-room-checkbox").prop('checked') && $(this).attr("id") !== 'undefined') {
                    booking_ids.push($(this).attr("id"));
                    booking_room_ids[$(this).attr("id")] = $(this).data("room_type_id");
                    booking_check_ins[$(this).attr("id")] = $(this).data("check_in_date");
                    booking_check_outs[$(this).attr("id")] = $(this).data("check_out_date");
                }
            });
            if (booking_ids.length < 1) {
                alert(l("Select at least one room"));
                return;
            }

            if (action == 'Email') {
                $.ajax({
                    type: "POST",
                    url: getBaseURL() + "booking/send_multiple_booking_confirmation_email",
                    data: {
                        booking_ids: booking_ids,
                        group_id: that.groupInfo.group_id
                    },
                    success: function (response) {
                        alert(response);
                    }
                });
            }

            var booking_blocks = [];
            $.each(booking_ids, function (key, value) {
                if (action == 'Cancel') {
                    var bookingId = value;
                    booking_blocks.push({room_type_id: booking_room_ids[bookingId], check_in_date: booking_check_ins[bookingId], check_out_date: booking_check_outs[bookingId]});
                    var booking_data = [];
                    booking_data[0] = booking_blocks[key];
                    var bookingData = {booking: {state: 4}, rooms: booking_data}
                    that._updateGroupBooking(bookingData, l('Booking is cancelled'), bookingId);
                }
                if (action == 'Delete') {
                    var groupBookingId = value;
                    var isGroupBookingDel = true;
                    return that._deleteBooking(isGroupBookingDel, groupBookingId, key);
                }
                if (action == 'Checkin') {
                    var groupBookingId = value;
                    var isGroupBookingDel = true;
                    return that._chekinBooking(isGroupBookingDel, groupBookingId, key);
                }
                if (action == 'Checkout') {
                    var groupBookingId = value;
                    var isGroupBookingDel = true;
                    return that._chekoutBooking(isGroupBookingDel, groupBookingId, key);
                }
            });
        }
    }; // -- Prototype

    // eventually, add an option to enter check-in & check-out date.

    $.fn.openBookingModal = function (options) {
        var body = $("body");
        // preventing against multiple instantiations
        if ((options && options.roomTypeID && jQuery.type(options.roomTypeID) != 'object') || (options && options.selected_room_id) || (options && options.id) || (options == undefined)) {
            $.data(body, 'bookingModal', new BookingModal(options));
        }

        // // Create the event
        // var event = new CustomEvent('open_booking_modal', { "detail" : {"reservation_id" : options.id} });

        // // Dispatch/Trigger/Fire the event
        // document.dispatchEvent(event);
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
                            text: l("Group Manager")
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
                                class: "btn btn-light show-all-groups",
                                type: "button",
                                text: l('Show All Groups')
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
                                class: "btn btn-light new-search",
                                type: "button",
                                text: l('New Search')
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
                                class: "btn btn-light close-wep",
                                'data-dismiss': "modal",
                                type: "button",
                                text: l('Close')
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
                                    for: "group-name",
                                    class: "col-sm-3 control-label"
                                })
                                    .append(
                                        $('<small/>', {
                                            text: l('Group Name')
                                        })
                                    )
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
                                    for: "group-id",
                                    class: "col-sm-3 control-label",
                                    text: l("Group")+" "+l("Id")
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
                                    for: "customer-name",
                                    class: "col-sm-3 control-label",
                                    text: l("Customer")
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
                                        class: "btn btn-light",
                                        text: l("Find Group")
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

            if (showAllGroups != true) {
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
                                    $('<thead/>', {}).append(
                                        $('<tr/>', {}).append(
                                            $('<th/>', {
                                                text: l('Group Name')
                                            })
                                        ).append(
                                            $('<th/>', {
                                                text: l('Customer')
                                            })
                                        ).append(
                                            $('<th/>', {
                                                text: l('Phone')
                                            })
                                        ).append(
                                            $('<th/>', {
                                                text: l('First Check In Date')
                                            })
                                        ).append(
                                            $('<th/>', {
                                                text: l('Last Check Out Date')
                                            })
                                        )
                                    )
                                ).append(
                                    $('<tbody/>', {})
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

    $.fn.openSearchGroupModel = function () {
        var body = $("body");

        // preventing against multiple instantiations
        $.data(body, 'searchGroupModel', new SearchGroupModel());
    }

    // advance caching - speedup booking modal to prefetch data
    var preFetchData = function () {

        innGrid.ajaxCache.companyBookingSources = innGrid.bookingSources;

        // prefetch charge types
        if (!innGrid.ajaxCache.commonCustomerFields || !innGrid.ajaxCache.chargeTypes) {
            $.getJSON(getBaseURL() + 'booking/get_customer_data_on_pageload',
                function (data) {
                    innGrid.ajaxCache.commonCustomerFields = data.common_customer_fields;
                    innGrid.ajaxCache.chargeTypes = data.room_charge_types;
                }
            );
        }
    }

    // prefetch data after 4s of page load
    setTimeout(preFetchData, 1000);
};

setTimeout(function () {
    bookingModalInvoker(jQuery, window, document);
}, 500);


function restrictCreditCardData(cc_number, fieldName) {

    var ccNum = cc_number;
    var visaRegEx = /^(?:4[0-9]{12}(?:[0-9]{3})?)$/;
    var mastercardRegEx = /^(?:5[1-5][0-9]{14})$/;
    var amexpRegEx = /^(?:3[47][0-9]{13})$/;
    var discovRegEx = /^(?:6(?:011|5[0-9][0-9])[0-9]{12})$/;
    var isValid = false;

    if (visaRegEx.test(ccNum)) {
        isValid = true;
    } else if(mastercardRegEx.test(ccNum)) {
        isValid = true;
    } else if(amexpRegEx.test(ccNum)) {
        isValid = true;
    } else if(discovRegEx.test(ccNum)) {
        isValid = true;
    }

    if(isValid) {
        $('#reservation-message .message-heading').text('Warning');
        $('#reservation-message .message').html('<p>'+l('Our system has detected "raw" credit card numbers in the')+' '+fieldName+' '+l('field')+'. '+l('This is not secure and it is putting your company at risk')+'. ' +
            '<br><br>'+l('To securely store credit card data, please setup Payment Gateway Integration')+'. '+l('We recommend using')+' <a href="https://supportroomsy.groovehq.com/help/how-to-get-integrated-with-stripe" target="_blank">Stripe</a>.' +
            '<br/><br>'+l('Please contact us at')+' <a href="mailto:support@minical.io" target="_blank">support@minical.io</a> '+l('if you have any questions')+'.</p>');
        $('#reservation-message')
            .modal('show')
            .on('hidden.bs.modal', function () {
                if (($("#booking-modal").data('bs.modal') || {}).isShown)
                    $("body").addClass("modal-open");
                $('#reservation-message .message-heading').text('Message');
            });
        $('.confirm-customer').on('click', function () {
                $('#reservation-message').modal('hide');
                return false;
        });
    }
    return isValid;
}

$(document).on('blur', '.restrict-cc-data', function() {
    innGrid.restrictedCreditCardData = localStorage.getItem('restrictedCreditCardData');
    if (innGrid.restrictedCreditCardData) {
        return;
    }
    var fieldName = $(this).data('label');
    var str = $(this).val();
    var digits = str ? str.replace(/\ /g, '').match(/\d+/g) : null;
    if (digits && digits.length > 0) {
        digits.forEach(function(digit) {
            innGrid.restrictedCreditCardData = restrictCreditCardData(digit, fieldName);
            localStorage.setItem('restrictedCreditCardData', innGrid.restrictedCreditCardData);
        });
    }
});


$(document).on('click','.booking_form_type', function(){
    var type = $(this).text();
    if(type == 'Group'){
        $('.fixed_rate_group').show();
        $('.per_person_group').show();
        $('.total_counts').show();
    } else {
        
        $('.fixed_rate_group').hide();
        $('.per_person_group').hide();

        $('.total_room_count').text(0);
        $('.total_guest_count').text(0);
        $('.total_counts').hide();
    }
});

if(innGrid.isGroupBookingFeatures == true){
    // Create an object to store room counts by room type
    var roomCounts = {};
    var adultCounts = {};

    // Update the total room count on input blur
    $(document).on('blur', '.room_count', function() {

        var roomTypeID = $(this).attr('id'); // Get the room type ID

        roomCounts[roomTypeID] = 0;

        var adultCount = $(this).closest('.room-type').find('.adult_count').val();
        console.log('adultCount',adultCount);

        var newRoomCount = parseInt($(this).val()); // Get the new room count value

        // If the new room count is not a number, set it to 0
        if (isNaN(newRoomCount)) {
            newRoomCount = 0;
        }

        // Update the room count for the specific room type
        roomCounts[roomTypeID] = newRoomCount;

        // Recalculate the total room count
        var totalRoomCount = 0;
        for (var id in roomCounts) {
            totalRoomCount += roomCounts[id];
        }

        var totalAdultCount = newRoomCount * adultCount;

        var oldGuestCount = $('.total_guest_count').text();
        console.log('oldGuestCount',oldGuestCount);
        totalAdultCount = parseInt(totalAdultCount) + parseInt(oldGuestCount);

        // Update the displayed total room count
        $('.total_guest_count').text(totalAdultCount);

        // Update the displayed total room count
        $('.total_room_count').text(totalRoomCount);

        console.log('RoomTypeID:', roomTypeID, 'New Room Count:', newRoomCount, 'total Room Count:', totalRoomCount);
    });

    $(document).on('blur', '.adult_count', function() {

        var roomTypeID = $(this).closest('.room-type').find('.room_type_id').val(); // Get the room type ID
        
        adultCounts[roomTypeID] = 0;

        var roomCount = $(this).closest('.room-type').find('.room_count').val();
        console.log('roomCount',roomCount);

        var newAdultCount = parseInt($(this).val()); // Get the new room count value

        // If the new room count is not a number, set it to 0
        if (isNaN(newAdultCount)) {
            newAdultCount = 0;
        }

        // Update the room count for the specific room type
        adultCounts[roomTypeID] = newAdultCount;

        // Recalculate the total room count
        var totalAdultCount = 0;
        for (var id in adultCounts) {
            totalAdultCount += adultCounts[id];
            
        }

        totalAdultCount = newAdultCount * roomCount;

        var oldGuestCount = $('.total_guest_count').text();
        console.log('oldGuestCount',oldGuestCount);
        oldGuestCount = parseInt(oldGuestCount) - parseInt(roomCount);
        totalAdultCount = parseInt(totalAdultCount) + parseInt(oldGuestCount);

        // Update the displayed total room count
        $('.total_guest_count').text(totalAdultCount);

        console.log('RoomTypeID:', roomTypeID, 'New Adult Count:', newAdultCount);
        
    });

}

function handleCustomerTypeChange(bookingCustomerTypeID) {

    var type = $('.booking_form_type.active').text();
    console.log('type', type);

    if(type == 'Single'){
        $.ajax({
            url: getBaseURL() + 'get_select_rate_plan',
            type: "POST",
            dataType: "json",
            data: {
                customer_type_id: bookingCustomerTypeID
            },
            dataType: "json",
            success: function(resp) {
                if(resp.success){
                    var room_type_id = resp.room_type_id;
                    $("select[name='room_type_id']").val(room_type_id);
                    $("select[name='room_type_id']").trigger('change');
                    $("select[name='room_type_id']").prop('disabled', true);

                    var rate_plan_id = resp.rate_plan_id;
                    setTimeout(function() {
                        $('select.charge-with').val(rate_plan_id);
                        $('select.charge-with').prop('disabled', true);
                    }, 1000);

                    setTimeout(function() {
                        $("select.charge-with").trigger('change');
                    }, 1000);

                } else {
                    $("select[name='room_type_id']").prop('disabled', false);
                    $('select.charge-with').prop('disabled', false);

                }
            }
        });
    }
}

$(document).on('click', '.all-cancelled-room-checkbox', function() {
        var checked = $(this).prop('checked');
        $('.cancelled-room-checkbox').each(function() {
            $(this).prop('checked', checked);
        })
});