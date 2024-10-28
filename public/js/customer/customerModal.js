/*  Plugin for Booking Modals
 *   It takes the element's id attr, and use it as bookingID
 */
var customerId;
var customer_pci_token = '';
(function($) {
    "use strict";

    innGrid.ajaxCache = innGrid.ajaxCache || {};

    var commonCustomerFields = [];

    if(!innGrid.ajaxCache.commonCustomerFields) {
        $.getJSON(getBaseURL() + 'booking/get_customer_data_on_pageload',
            function (data) {
                innGrid.ajaxCache.commonCustomerFields = data.common_customer_fields;
                console.log(innGrid.ajaxCache.commonCustomerFields);
                commonCustomerFields = data.common_customer_fields;
            }
        );
    }

    // dynamically load required js
    var scripts = [
        'js/jquery.payment.js'
    ];

    scripts.forEach(function(script) {
        $.getScript(getBaseURL() + script, function() {
            //console.log(script+" successfully loaded!");
        });

    });

    // initialize
    $("body").append(
        $("<div/>", {
            class: "modal fade",
            id: "customer-modal",
            "tabindex": "-1",
            "role": "dialog",
            "aria-hidden": true
        }).modal({
            show: false,
            backdrop: 'static'
        }).append(
            $("<div/>", {
                class: "modal-dialog"
            }).append(
                $("<div/>", {
                    class: "modal-content"
                })
            )
        )
    );

    $("#customer-modal")
    var CustomerModal = function(options) {

        var that = this;

        this.deferredCustomerTypes = $.Deferred();
        this.deferredCustomerFields = $.Deferred();

        var defaults = {
            customer_name: '',
            customer_id: '',
            onload: function() {},
            onclose: function() {}
        };

        options = $.extend({}, defaults, options);

        $.when(this.deferredCustomerFields, this.deferredCustomerTypes).done(function() {
            that._populateCustomerModal(that.customerData, options);
        });
        //if(!innGrid.ajaxCache.customerTypes)
        //{
        $.ajax({
            type: "POST",
            url: getBaseURL() + "customer/get_customer_types",
            dataType: "json",
            success: function(data) {
                that.customerTypes = data;
                innGrid.ajaxCache.customerTypes = data;

                if (options.customer_id) {
                    customerId = options.customer_id;
                    $.ajax({
                        type: "POST",
                        url: getBaseURL() + "customer/get_customer_AJAX",
                        data: {
                            customer_id: options.customer_id
                        },
                        dataType: "json",
                        success: function(data) {
                            that.customerData = data;
                            that.deferredCustomerTypes.resolve();
                        }
                    });
                } else {
                    data = {
                        customer_id: options.customer_id,
                        customer_name: options.customer_name
                    }
                    customerId = options.customer_id;
                    that.customerData = data;
                    that.deferredCustomerTypes.resolve();
                    options.onload();
                }
                // callback

            }
        });
        //      }
        //      else
        //      {
        //          that.customerTypes = innGrid.ajaxCache.customerTypes;
        //
        //          if (options.customer_id) {
        //              $.ajax({
        //                  type: "POST",
        //                  url: getBaseURL() + "customer/get_customer_AJAX",
        //                  data: {
        //                      customer_id: options.customer_id
        //                  },
        //                  dataType: "json",
        //                  success: function (data) {
        //                      that.customerData = data;
        //                      that.deferredCustomerTypes.resolve();
        //                  }
        //              });
        //          }
        //          else
        //          {
        //              innGrid.ajaxCache.customerTypes = {
        //                  customer_id: options.customer_id,
        //                  customer_name: options.customer_name
        //              }
        //              that.customerData = innGrid.ajaxCache.customerTypes;
        //              that.deferredCustomerTypes.resolve();
        //              options.onload();
        //          }
        //      }

        if (!innGrid.ajaxCache.customerFields) {
            $.ajax({
                type: "POST",
                url: getBaseURL() + "customer/get_customer_fields",
                dataType: "json",
                success: function(data) {
                    that.customerFields = data;
                    innGrid.ajaxCache.customerFields = data;
                    that.deferredCustomerFields.resolve();
                }
            });
        } else {
            that.customerFields = innGrid.ajaxCache.customerFields;
            that.deferredCustomerFields.resolve();
        }
        $("#customer-modal").on('hidden.bs.modal', function() {

            // remove customer tokens that has not been created
            $(".token").each(function() {
                if (!$(this).attr('id')) {
                    $(this).remove();
                }
            });

            // hack to prevent closing inner-modal removing modal-open class in body.
            // when modal-open class is removed from body, scrolling the customer-modal scrolls
            // background, instead of scrolling the modal

            if (($("#booking-modal").data('bs.modal') || {}).isShown)
                $("body").addClass("modal-open");
        })

    };

    CustomerModal.prototype = {
        _init: function(options) {
            $("#customer-modal").find(".modal-content").html("");
            $('#customer-modal').modal('show');
            $('#customer-modal').on('hidden.bs.modal', function(e) {
                options.onclose();
                $('#customer-modal').unbind('hidden.bs.modal');
            })
            if ($('input[name=check_in_date]').length > 0) {
                $('input[name=check_in_date]').datepicker('hide');
            }
            if ($('input[name=check_out_date]').length > 0) {
                $('input[name=check_out_date]').datepicker('hide');
            }

            setTimeout(function() {
                var event = new CustomEvent('post.open_customer_model', { "detail": { "customer_id": options.customer_id } });
                document.dispatchEvent(event);
            }, 300);

        },
        _populateCustomerModal: function(customer, options) {
            var that = this;

            commonCustomerFields = innGrid.ajaxCache.commonCustomerFields;

            that._init(options);

            if (customer.customer_id) // existing customer
            {
                // initializing cc_expiry month and year, to '', otherwise, expiry field shows NaN
                if (
                    (
                        typeof customer.cc_expiry_month === 'undefined' &&
                        typeof customer.cc_expiry_year === 'undefined'
                    ) ||
                    (
                        customer.cc_expiry_month === null &&
                        customer.cc_expiry_year === null
                    ) ||
                    (
                        customer.cc_expiry_month === '' &&
                        customer.cc_expiry_year === ''
                    )

                ) {
                    var cc_expiry = '';
                } else {
                    var cc_expiry = customer.cc_expiry_month + " / " + customer.cc_expiry_year;
                }
            }

            var $modal_content = $("#customer-modal").find(".modal-content")

            var $customer_form = $("<form/>", {
                    class: "modal-body form-horizontal",
                    id: "custom_form",
                    onsubmit: "return false",

                })
                .append(this._getHorizontalInput(l("Name", true), 'customer_name', customer.customer_name, (commonCustomerFields && commonCustomerFields[0] && commonCustomerFields[0]['show_on_customer_form'] == 0 ? "hidden customer_field_1" : "customer_field_1"), 1))
                .append(
                    $('<div/>', {
                        class: 'form-group form-group-sm ' + (commonCustomerFields && commonCustomerFields[1] && commonCustomerFields[1]['show_on_customer_form'] == 0 ? "hidden" : ""),
                    })
                    .append(
                        $("<label/>", {
                            class: 'col-sm-3 control-label ' + (commonCustomerFields && commonCustomerFields[1] && commonCustomerFields[1]['show_on_customer_form'] == 0 ? "hidden" : ""),
                            html: l("Customer Type")
                        })
                    )
                    .append(
                        $("<div/>", {
                            class: 'col-sm-9'
                        }).append(
                            this._getSelect("customer_type_id", that.customerTypes, (commonCustomerFields && commonCustomerFields[1] && commonCustomerFields[1]['show_on_customer_form'] == 0 ? "hidden customer_field_2" : "customer_field_2"))
                        )
                    )
                )
                .append(
                    $("<input/>", { // a workaround to disable autocomplete for email and cvv
                        class: "hidden", // browser check if email field is hidden than do not auto populate user and password field that is email and cvv.
                        name: 'email'
                    })
                )
                .append(this._getHorizontalInput(l("Email"), 'customer-email', customer.email,
                    (commonCustomerFields && commonCustomerFields[2] && commonCustomerFields[2]['show_on_customer_form'] == 0 ? "hidden customer_field_3" : "customer_field_3"),
                    (commonCustomerFields && commonCustomerFields[2] && commonCustomerFields[2]['is_required'])))
                .append(this._getHorizontalInput(l("Phone"), 'phone', customer.phone,
                    (commonCustomerFields && commonCustomerFields[3] && commonCustomerFields[3]['show_on_customer_form'] == 0 ? "hidden customer_field_4" : "customer_field_4"),
                    (commonCustomerFields && commonCustomerFields[3] && commonCustomerFields[3]['is_required'])))
                .append(this._getHorizontalInput(l("Phone 2"), 'phone2', customer.phone2,
                    (commonCustomerFields && commonCustomerFields[4] && commonCustomerFields[4]['show_on_customer_form'] == 0 ? "hidden customer_field_5" : "customer_field_5"),
                    (commonCustomerFields && commonCustomerFields[4] && commonCustomerFields[4]['is_required'])))
                .append(this._getHorizontalInput(l("Fax"), 'fax', customer.fax,
                    (commonCustomerFields && commonCustomerFields[5] && commonCustomerFields[5]['show_on_customer_form'] == 0 ? "hidden customer_field_6" : "customer_field_6"),
                    (commonCustomerFields && commonCustomerFields[5] && commonCustomerFields[5]['is_required'])))
                .append(this._getHorizontalInput(l("Address"), 'address', customer.address,
                    (commonCustomerFields && commonCustomerFields[6] && commonCustomerFields[6]['show_on_customer_form'] == 0 ? "hidden customer_field_7" : "customer_field_7"),
                    (commonCustomerFields && commonCustomerFields[6] && commonCustomerFields[6]['is_required'])))
                .append(this._getHorizontalInput(l("Address 2"), 'address2', customer.address2,
                    (commonCustomerFields && commonCustomerFields[7] && commonCustomerFields[7]['show_on_customer_form'] == 0 ? "hidden customer_field_8" : "customer_field_8"),
                    (commonCustomerFields && commonCustomerFields[7] && commonCustomerFields[7]['is_required'])))
                .append(this._getHorizontalInput(l("City"), 'city', customer.city,
                    (commonCustomerFields && commonCustomerFields[8] && commonCustomerFields[8]['show_on_customer_form'] == 0 ? "hidden customer_field_9" : "customer_field_9"),
                    (commonCustomerFields && commonCustomerFields[8] && commonCustomerFields[8]['is_required'])))
                .append(this._getHorizontalInput(l("Region"), 'region', customer.region,
                    (commonCustomerFields && commonCustomerFields[9] && commonCustomerFields[9]['show_on_customer_form'] == 0 ? "hidden customer_field_10" : "customer_field_10"),
                    (commonCustomerFields && commonCustomerFields[9] && commonCustomerFields[9]['is_required'])))
                .append(this._getHorizontalInput(l("Country"), 'country', customer.country,
                    (commonCustomerFields && commonCustomerFields[10] && commonCustomerFields[10]['show_on_customer_form'] == 0 ? "hidden customer_field_11" : "customer_field_11"),
                    (commonCustomerFields && commonCustomerFields[10] && commonCustomerFields[10]['is_required'])))
                .append(this._getHorizontalInput(l("Postal Code"), 'postal_code', customer.postal_code,
                    (commonCustomerFields && commonCustomerFields[11] && commonCustomerFields[11]['show_on_customer_form'] == 0 ? "hidden customer_field_12" : "customer_field_12"),
                    (commonCustomerFields && commonCustomerFields[11] && commonCustomerFields[11]['is_required'])));
         

            if (that.customerFields != undefined) {
                $.each(that.customerFields, function(key, value) {
                    var field = '';
                    if (customer.customer_fields != undefined) {
                        field = customer.customer_fields[value.id];
                    }
                    $customer_form.append(that._getHorizontalInput(value.name, "customer_field_" + value.id, field, '', value.is_required));
                });
            }

            // A WEIRD PATCH - do not remove
            // chrome autofills a text and password field with login details, so we need hidden fields so that they can contain autofilled data and these fields won't be used anywhere
            $customer_form.append(
                $("<input/>", { // a workaround to disable autocomplete for email and cvv
                    class: "form-control hidden-username",
                    name: "hidden-username",
                    style: "opacity: 0; width: 1px; height: 1px; margin: 0px; padding: 0px;",
                    type: 'text',
                })
            );


            // function getCookie(name) {
            //     const encodedName = encodeURIComponent(name) + '=';
            //     const cookieList = document.cookie.split(';');
            //     for (let i = 0; i < cookieList.length; i++) {
            //         let cookie = cookieList[i];
            //         while (cookie.charAt(0) === ' ') {
            //             cookie = cookie.substring(1);
            //         }
            //         if (cookie.indexOf(encodedName) === 0) {
            //             return decodeURIComponent(cookie.substring(encodedName.length, cookie.length));
            //         }
            //     }
            //     return '';
            // }

            // $customer_form.append(
            //         $("<input/>", {
            //             class: "form-control csrf_token",
            //             name: "csrf_token",
            //             style: "opacity: 0; width: 1px; height: 1px; margin: 0px; padding: 0px;",
            //             type: 'hidden',
            //             value: getCookie('csrf_token'),
            //         })
            // );


            if (isTokenizationEnabled == true) {
                console.log('customer-data',customer);
                console.log('Kovena',innGrid.isKovenaEnabled);
                var sensitiveCardNumber =
                    (((innGrid.isChannePCIEnabled || innGrid.isPCIBookingEnabled) && customer.token_source != 'kovena') && customer.customer_pci_token ? '<a style="position: absolute; right: 26px; top: 7px; z-index: 9999;" title = "Show Card Number" class="show_cc" data-cc_number_encrypted="' + customer.cc_number_encrypted + '" data-cc_number="' + customer.cc_number + '" data-customer_pci_token="' + customer.customer_pci_token + '" data-token_source="' + customer.token_source + '" data-cc_detail="card_number" href="javascript:"><i class="fa fa-eye" ></i></a><input type="hidden" class="customer_id" data-cc_token="' + customer.cc_tokenex_token + '" data-cc_cvc="' + customer.cc_cvc_encrypted + '" value="' + customer.customer_id + '"/>' : '');
                var sensitiveCardCVC = (customer.cc_cvc_encrypted ? '<a style="position: absolute; right: 26px; top: 7px; z-index: 9999;" title = "Show Card CVC" class="show_cc" data-cc_number_encrypted="' + customer.cc_number_encrypted + '" data-cc_number="' + customer.cc_number + '" data-cc_detail="card_cvc" href="javascript:"><i class="fa fa-eye" ></i></a>' : '');

                $customer_form.append(
                    $("<div/>", {
                        class: "form-group cc_field",
                        // style: "display: none;"
                    })
                    .append($("<label/>", {
                        for: "credit_card",
                        class: "col-sm-3 control-label",
                        text: l("Credit Card")
                    })).append(
                        $("<div/>", {
                            class: "col-sm-6"
                        }).append(
                            // $("<iframe/>", {
                            //     id: "credit_card_iframe",
                            //     style: "width: 100%;height: 35px;border: none;",
                            //     scrolling: "no",
                            //     frameborder: "0"
                            // })
                            $("<input/>", { // a workaround to disable autocomplete for email and cvv
                                class: "form-control cc_number",
                                name: "cc_number",
                                id: "cc_number",
                                type: 'text',
                                value: customer.cc_number
                            })
                        )
                        .append(sensitiveCardNumber)
                        .append($('<span/>', {
                                id: "masked-card-number-label",
                                style: "position: absolute;top: 0;left: 15px;background: white;max-width: 90%;padding: 8px;",
                                class: "masked-card-number-label form-control " + (customer.cc_number ? "" : "hidden"),
                                text: customer.cc_number
                            })
                            .on('click', function() {
                                $(this).hide();
                                $('#credit_card_iframe')[0].contentWindow.postMessage('focus', '*');
                            })
                        )
                        .append($('<img/>', {
                            id: "card-image",
                            style: "position: absolute; top: 3px; right: 18px; width: auto; height: 28px; padding: 0;"
                        }))
                        .append($('<img/>', {
                                id: "detokenize-card",
                                style: "cursor: pointer; position: absolute; top: 3px; right: 18px; width: auto; height: 28px; padding: 0;",
                                class: 'hidden'
                            })
                            .on('click', function() {
                                $.get(getBaseURL() + "customer/detokenize_card", { customer_id: customer.customer_id },
                                    function(data) {
                                        if (data) {
                                            $('#detokenize-card').hide();
                                            $('input[name="cc_number"]').val(data);
                                            that._updateCardImage();
                                        }
                                    }
                                );
                            })
                        )
                    )
                    .append(
                        $("<div/>", {
                            class: "col-sm-3"
                        }).append(
                            $("<input/>", {
                                class: "form-control",
                                name: "cc_expiry",
                                type: "text",
                                placeholder: "MM / YY",
                                value: cc_expiry,
                                maxlength: "7"
                            })
                            .payment('formatCardExpiry')
                        )
                    )
                ).append(
                    $("<div/>", {
                        class: "form-group cc_field",
                        // style: "display: none;"
                    }).append(
                        $("<label/>", {
                            for: "customer_notes",
                            class: "col-sm-3 control-label",
                            text: l("CVC")
                        })
                    ).append(
                        $("<div/>", {
                            class: "col-sm-3"
                        })
                        .append(
                            $("<input/>", { // a workaround to disable autocomplete for email and cvv
                                class: "hidden", // browser check if password field is hidden than don't auto populate user and password field that is email and cvv.
                                type: 'password',
                            })
                        )
                        .append(
                            $("<input/>", {
                                class: "form-control credit_card_cvc",
                                name: "cvc",
                                placeholder: '***',
                                type: 'password',
                                maxlength: 4,
                                // value: customer.cc_cvc_encrypted ? "***" : ""
                                value: customer.cc_number ? "***" : ""
                            })
                        ) //.append(sensitiveCardCVC)
                    )
                    // .append(
                    //     $("<div/>", {id: "cc_tokenization_status", class: 'col-sm-6'}).on("click", function () {
                    //         alert(l("The customer's credit card has been tokenized. You can charge the customer's credit card in the Invoice page using [Add Payment] button."));
                    //     })
                    // )
                );
            }
            $customer_form.append(
                $("<div/>", {
                    class: "form-group " + ((commonCustomerFields && commonCustomerFields[12] && commonCustomerFields[12]['show_on_customer_form'] == '0') ? "hidden" : ""),
                }).append(
                    $("<label/>", {
                        for: "customer_notes",
                        class: "col-sm-3 control-label " + ((commonCustomerFields && commonCustomerFields[12] && commonCustomerFields[12]['show_on_customer_form'] == '0') ? "hidden" : ""),
                        text: l("Notes")
                    })
                ).append(
                    $("<div/>", {
                        class: "col-sm-9"
                    }).append(
                        $("<textarea/>", {
                            class: "form-control restrict-cc-data " + ((commonCustomerFields && commonCustomerFields[12]['show_on_customer_form'] == '0') ? "hidden" : ""),
                            name: "customer_notes",
                            'data-label': 'customer notes',
                            rows: 3,
                            text: _.isNull(customer.customer_notes) ? '' : customer.customer_notes
                        })
                    )
                )
            );


            $modal_content.append(
                    $("<div/>", {
                        class: "modal-header"
                    })
                    .append("Customer Information ")
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
                .append($customer_form)
                $customer_form.append(
                    $("<div/>", {
                        class: "modal-footer"
                    }).append(
                        $("<button/>", {
                            type: "submit",
                            class: "btn btn-primary",
                            id: "button-update-customer",
                            text: (customer.customer_id) ? l("Update") : l("Create")
                        })
                        .on('click', function() {

                            if (typeof this.button_update_customer_lock !== "undefined" && this.button_update_customer_lock) {
                                return;
                            }
                            $('#button-update-customer').button_update_customer_lock = true;
                            setTimeout(function() {
                                $('#button-update-customer').button_update_customer_lock = false;
                            }, 500);
                            $(this).attr('disabled', true);

                            var errorMsg = '';

                            var customer_name = $.trim($("input[name='customer_name']").val());
                            if (customer_name == "") {
                                errorMsg += "\nCustomer Name is required";
                            }
                            var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                            var customer_email = $.trim($("input[name='customer-email']").val());
                            if (customer_email != '' && re.test(customer_email) == false) {
                                errorMsg += "\nInvalid Email Address";
                            }
                            var customer_phone = $.trim($("input[name='phone']").val());

                            if (customer_phone != '' && customer_phone.match(/^[\(\)\s\-\+\d]{10,17}$/) == null) {
                                errorMsg += "\nInvalid Phone Number";
                            }
                            var customer_phone2 = $.trim($("input[name='phone2']").val());
                            if (customer_phone2 != '' && customer_phone2.match(/^[\(\)\s\-\+\d]{10,17}$/) == null) {
                                errorMsg += "\nInvalid Phone 2 Number";
                            }
                            var customer_fax = $.trim($("input[name='fax']").val());
                            if (isNaN(customer_fax) == true) {
                                errorMsg += "\nInvalid Fax Number";
                            }

                            var cardno = $.trim($("input[name='cc_number']").val());

                            if (cardno.length != 0) {

                                var check_card = /^X.*.{1,15}$/;
                                var re16digit = /^(?:4[0-9]{12}(?:[0-9]{3})?|[25][1-7][0-9]{14}|6(?:011|5[0-9][0-9])[0-9]{12}|3[47][0-9]{13}|3(?:0[0-5]|[68][0-9])[0-9]{11}|(?:2131|1800|35\d{3})\d{11})$/;
                                if (check_card.test(cardno)) {

                                } else if (!re16digit.test(cardno)) {
                                    errorMsg += "\nPlease enter valid card number";
                                }
                            }

                            var cvc = $.trim($("input[name='cvc']").val());
                            if (
                                cardno !== '' &&
                                cvc == '' &&
                                innGrid.restrictCvcNotMandatory == 0
                            ) {
                                errorMsg += "\nPlease enter CVC code";
                            }
                            // console.log(commonCustomerFields);
                            if (commonCustomerFields && commonCustomerFields.length > 0) {
                                for (var key in commonCustomerFields) {
                                    if (commonCustomerFields[key].customer_field_id == '-1' || commonCustomerFields[key].is_required == '1') {
                                        if ($("input.customer_field_" + (Math.abs(commonCustomerFields[key].customer_field_id))).val() == '') {
                                            errorMsg += "\n" + ($("input.customer_field_" + (Math.abs(commonCustomerFields[key].customer_field_id))).data('label')) + " is required";
                                        }
                                    }
                                }
                            }
                            if (that.customerFields && that.customerFields.length > 0) {
                                for (var key in that.customerFields) {
                                    if (that.customerFields[key].is_required == '1') {
                                        if ($("input[name='customer_field_" + (that.customerFields[key].id) + "']").val() == '') {
                                            errorMsg += "\n" + (that.customerFields[key].name) + " is required";
                                        }
                                    }
                                }
                            }

                            if (isTokenizationEnabled == 1 && !$.payment.validateCardExpiry($("input[name='cc_expiry']").payment('cardExpiryVal')) &&
                                $("input[name='cc_expiry']").val() !== '') {
                                errorMsg += "\nInvalid Expiry Date";
                            }

                            if (errorMsg !== '') {
                                alert(errorMsg);
                                $(this).attr('disabled', false);
                                return;
                            }

                            var customerData = that._fetchCustomerData();

                            console.log('customerData',customerData);

                            var update_create_client = function(data) {
                                console.log('data',data);

                                clearTimeout(window.updateCreateClientTimeout);

                                data = _.isUndefined(data) ? null : data;
                                var token = null,
                                    cc_tokenex_token = null,
                                    cc_cvc_encrypted = null;
                                if (data && data.success) {
                                    console.log(data);
                                    customerData.cc_number = "XXXX XXXX XXXX " + data.lastFour;
                                    cc_tokenex_token = data.token;
                                    cc_cvc_encrypted = data.cc_cvc_encrypted;
                                }

                                if (data && data.length > 0 && $('#payment-form').html() == undefined  ){
                                    console.log('data',data);

                                    var cardData = data.split('_');
                                    if(cardData){
                                        console.log('customerData',customerData);
                                        console.log('cardData',cardData);
                                        customerData.cc_number = "XXXX XXXX XXXX " + cardData[1].substr(cardData[1].length - 4);
                                        customerData.cvc = cardData[3];
                                        customerData.cc_token = cardData[0];
                                        customerData.cc_expiry_month = cardData[2].substring(0, 2);
                                        customerData.cc_expiry_year = cardData[2].substring(4);
                                    }
                                }else if(data && data.length > 0 && innGrid.isCardknoxEnabled == 1 && $('#payment-form').html()){
                                    console.log('data',data);

                                    if (data) {
                                       customerData = data[0];
                                       customerData.cc_number =  data[0].cc_number;
                                        customerData.cvc =  data[0].cc_cvc_encrypted;
                                        customerData.cc_token = data[0].cc_token ?data[0].cc_token:null;
                                        customerData.cc_expiry_month = data[0].cc_expiry_month;
                                        customerData.cc_expiry_year = data[0].cc_expiry_year;
                                    }
                                }

                                console.log('customerData',customerData);

                                if (customer.customer_id) // new customer
                                {
                                    // customerData.csrf_token = $('.csrf_token').val();
                                    var customerData_str = JSON.stringify(customerData);
                                    console.log(customer.customer_id)
                                    // update customer
                                    $.ajax({
                                        type: "POST",
                                        url: getBaseURL() + "customer/update_customer_AJAX",
                                        data: {
                                            // customer_id: customer.customer_id,
                                            // customer_data: customerData,
                                            cc_tokenex_token: cc_tokenex_token,
                                            cc_cvc_encrypted: cc_cvc_encrypted,
                                            customer_id: btoa(customer.customer_id.toString()),
                                            // customer_data: btoa(JSON.stringify(customerData))
                                            customer_data: btoa(unescape(encodeURIComponent(customerData_str)))
                                        },
                                        dataType: "json",
                                        success: function(data) {
                                            if (data.error && data.error_msg) {
                                                console.log(data)
                                                alert(data.error_msg);
                                            } else {
                                                // update customer token's name
                                                $(document).find("#" + customer.customer_id + ".token").find(".token-label").text(customerData.customer_name);
                                                $("#customer-modal").modal('hide');
                                            }
                                            $('#button-update-customer').attr('disabled', false);
                                        }
                                    });
                                } else {
                                    // create new customer
                                    // customerData.csrf_token = $('.csrf_token').val();
                                    var customerData_str = JSON.stringify(customerData);

                                    $.ajax({
                                        type: "POST",
                                        url: getBaseURL() + "customer/create_customer_AJAX",
                                        data: {
                                            // customer_data: customerData,
                                            // customer_data: btoa(JSON.stringify(customerData)),
                                            customer_data: btoa(unescape(encodeURIComponent(customerData_str))),
                                            cc_tokenex_token: cc_tokenex_token,
                                            cc_cvc_encrypted: cc_cvc_encrypted
                                        },
                                        dataType: "json",
                                        success: function(data) {
                                            if (data.error && data.error_msg) {
                                                alert(data.error_msg);
                                            } else {

                                                $(".token").each(function() {
                                                    if (!$(this).attr('id')) {
                                                        var newCustomerToken = $(this);
                                                        newCustomerToken.find(".token-label").text(customerData.customer_name);
                                                        newCustomerToken.attr("id", data.customer_id);
                                                    }
                                                });
                                                // customerId = data.customer_id;
                                                // var event = new CustomEvent('post.create_user');
                                                // document.dispatchEvent(event);

                                                // a token that doesn't have id assigned yet
                                                $("#customer-modal").modal('hide');
                                            }
                                            $('#button-update-customer').attr('disabled', false);
                                        }
                                    });
                                }
                            };

                            innGrid.deferredCreditCardValidation = $.Deferred();

                            $.when(innGrid.deferredCreditCardValidation)
                                .then(function() {
                                    // user entered valid card number
                                    innGrid.deferredWaitForTokenization = $.Deferred();

                                    $('#credit_card_iframe')[0].contentWindow.postMessage('tokenize', '*');

                                    $.when(innGrid.deferredWaitForTokenization)
                                        .then(function(data) {
                                            update_create_client(data);
                                        })
                                        .fail(function(message) {
                                            alert(message);
                                            $('#button-update-customer').attr('disabled', false);
                                        });
                                })
                                .fail(function(validator) {
                                    if (validator == "required") {
                                        // user not entered card number
                                        update_create_client();
                                    } else if (validator == "invalid") {
                                        // user entered invalid card number
                                        errorMsg = "\nInvalid Credit Card Number";
                                        alert(errorMsg);
                                        $('#button-update-customer').attr('disabled', false);
                                        return;
                                    } else {
                                        alert(validator);
                                        $('#button-update-customer').attr('disabled', false);
                                        return;
                                    }
                                });
                            

                            if (typeof nexioGateway !== "undefined" && nexioGateway) {
                                var myIframe = window.document.getElementById('myIframe');
                                if(myIframe) {
                                    console.log('customer.customer_id',customerId);
                                    customerData['customer_id'] = customerId;
                                    var event = new CustomEvent('post.create_user', { detail: { "customer": customerData } });
                                    document.dispatchEvent(event);
                                } else {
                                    update_create_client();
                                }
                                
                            } 
                            else if (typeof kovenaGateway !== "undefined" && kovenaGateway && innGrid.isKovenaEnabled == 1) {
                                console.log('widget',widget);
                                widget.trigger('submit_form');
                                widget.load();

                                setTimeout(function(){
                                    customerData.kovena_vault_token = $("input[name='payment_source_token']").val();             
                                    console.log('Kovena vault token',customerData.kovena_vault_token);
                                    if(!customerData.kovena_vault_token && customerData.kovena_vault_token ==''){
                                        alert("one time token not found" );
                                        return false;
                                    } else {
                                                update_create_client();
                                    }
                                },4500);
                            }
                            // typeof cardknoxGateway !== "undefined" && cardknoxGateway &&
                            else if( innGrid.isCardknoxEnabled == 1 && $('#payment-form').html()) {
                                // if ($('#payment-form').html()) {

                                    $('#submit-btn').trigger('click');
                                    var imageUrl = getBaseURL() + 'images/loading.gif'
                                    $('<img class="loader-img" src="'+imageUrl+'" style=""/>').insertBefore($('#button-update-customer'));
                                    
                                    save_customer_cardknox_card(customerId);

                                    setTimeout(function(){

                                        let customerToken = $('#customer-token').text();
                                        let customerError = $('#customer-error').text();
                                        let customerCvvToken = $('#cvv-token').text();

                                        
                                        console.log(customerCvvToken)
                                        if (customerToken == ''|| customerToken == null|| customerToken == undefined) {
                                            console.log(customerError)
                                            alert(customerError);
                                            $('#customer-modal').find('.close').trigger('click');
                                            
                                        } else {
                                            
                                                let xName = $("input[name=customer_name]").val();
                                                let xMonth = document.getElementById("month").value;
                                                let xYear = document.getElementById("year").value;
                                            
                                                let card_number_token = document.querySelector("[data-ifields-id='card-number-token']").value;
                                                
                                                let last_four_card_number = card_number_token.substring(0, 17).substring(12, 16);

                                                console.log(card_number_token);

                                                $('#button-update-customer').removeProp('disabled');

                                                customerData.cardknox_token = customerToken;
                                                customerData.cardknox_cvv_token = customerCvvToken;
                                                customerData.customer_name = xName;
                                                // customerData.customer_id =  customerId ? customerId :'';
                                                customerData.cc_expiry_month = xMonth;
                                                customerData.cc_expiry_year = xYear;
                                                customerData.cc_number = "XXXX XXXX XXXX "  + last_four_card_number;
                                                console.log(customerData)
                                                update_create_client(customerData);
                                        }

                                    },12000);
                                    
                                // } 
                                
                                
                            }
                            else {

                                if($("#myIframe")[0] && $("#myIframe")[0].contentWindow){

                                    var contentWindow = $("#myIframe")[0].contentWindow;
                                    contentWindow.postMessage("submit", document.getElementById('myIframe').src);       

                                    setTimeout(function(){
                                        var cardData = $("#myIframe").contents().find("body").html();
                                        console.log(cardData);
                                        update_create_client(cardData);
                                    },2500);
                                } else {
                                    var cardData = [];
                                    update_create_client(cardData);
                                }
                            }

                        })
                    ).append(
                        $("<button/>", {
                            type: "button",
                            class: "btn btn-light",
                            "data-dismiss": "modal",
                            text: l("Close")
                        })
                    )
                );


            if (customer.customer_id) {
                $("#customer-modal").find(".modal-header").append(
                    $("<a/>", {
                        class: "btn btn-xs btn-light",
                        href: getBaseURL() + "customer/history/" + customer.customer_id,
                        text: l("History")
                    })
                );
            }

            // update field
            if (customer.customer_type_id !== undefined)
                $("[name='customer_type_id']").val(customer.customer_type_id)

            
            $("#customer-modal").find("form.modal-body").attr('autocomplete', 'none');
            $("#customer-modal").find(".modal-content").find('input.form-control').attr('autocomplete', 'none');
        },
       
        _fetchCustomerData: function() {

            var $customerModal = $("#customer-modal");

            // fetch general customer data
            var customerData = {
                customer_name: $.trim($customerModal.find("[name='customer_name']").val()),
                customer_type_id: $.trim($customerModal.find("[name='customer_type_id']").val()),
                email: $.trim($customerModal.find("[name='customer-email']").val()),
                phone: $.trim($customerModal.find("[name='phone']").val()),
                phone2: $.trim($customerModal.find("[name='phone2']").val()),
                fax: $.trim($customerModal.find("[name='fax']").val()),
                address: $.trim($customerModal.find("[name='address']").val()),
                address2: $.trim($customerModal.find("[name='address2']").val()),
                city: $.trim($customerModal.find("[name='city']").val()),
                region: $.trim($customerModal.find("[name='region']").val()),
                country: $.trim($customerModal.find("[name='country']").val()),
                postal_code: $.trim($customerModal.find("[name='postal_code']").val()),
                customer_notes: $.trim($customerModal.find("[name='customer_notes']").val()),
                cc_number: $.trim($customerModal.find("[name='cc_number']").val()),
                cvc: $.trim($customerModal.find("[name='cvc']").val())
            };
            if (isTokenizationEnabled == 1) {
                customerData['cc_expiry_month'] = $.trim($customerModal.find("[name='cc_expiry']").val().substring(0, 2));
                customerData['cc_expiry_year'] = $.trim($customerModal.find("[name='cc_expiry']").val().substring(5, 7))
            }
            // fetch custom customer field data
            // find input elements that contain 'customer_field_' string and fetch em
            var customer_fields = {};
            $("*[name*='customer_field_']").each(function() {
                var id = parseInt($(this).attr("name").replace("customer_field_", ""));
                customer_fields[id] = $.trim($(this).val());
            });
            customerData['customer_fields'] = customer_fields;
            //console.log(customerData);    
            return customerData;
        },
        _getSelect: function(name, options, customer_form) {
            var select = $("<select/>", {
                class: 'form-control ' + ((customer_form == '0') ? 'hidden' : ''),
                name: name
            })

            if (options != undefined) {
                options.forEach(function(data) {

                    var option = $('<option/>', {
                        value: data.id,
                        text: data.name
                    });

                    option.appendTo(select);
                });
            }




            return select;

        },
        _getHorizontalInput: function(label, name, value, element_class = '', is_required = false) {

            var countries_keys = Object.keys(COUNTRIES_OBJ)
            var countries_values = Object.values(COUNTRIES_OBJ)

            
              if(name=="country"){
                    return  $("<div/>", {
                        class: "form-group form-group-sm " + element_class,
                    }).append(
                        $("<label/>", {
                            for: name,
                            class: "col-sm-3 control-label " + element_class,
                            text: label
                        }).append(
                            $("<span/>", {
                                style: "color: red",
                                text: is_required == "1" ? "*" : ""
                            })
                        )
                    ).append(
                        $("<div/>", {
                            class: "col-sm-9"
                        }).append(
                            $("<input/>", {
                                class: "form-control restrict-cc-data " + element_class,
                                name: name,
                                type: 'text',
                                value: value,
                                length: 300,
                                'data-label': label
                            }).autocomplete({
                                    source:  countries_keys.concat(countries_values)
                            }) 
                        )
                       
                    )
                }else{
                    
                    return  $("<div/>", {
                        class: "form-group form-group-sm " + element_class,
                    }).append(
                        $("<label/>", {
                            for: name,
                            class: "col-sm-3 control-label " + element_class,
                            text: label
                        }).append(
                            $("<span/>", {
                                style: "color: red",
                                text: is_required == "1" ? "*" : ""
                            })
                        )
                    ).append(
                        $("<div/>", {
                            class: "col-sm-9"
                        }).append(
                            $("<input/>", {
                                class: "form-control restrict-cc-data " + element_class,
                                name: name,
                                type: 'text',
                                value: value,
                                length: 300,
                                'data-label': label
                            }) 
                        
                        )
                        
                    )
                }
            
    
        }


    }; // -- Prototype

    // eventually, add an option to enter check-in & check-out date.

    $.fn.openCustomerModal = function(options) {
        var body = $("body");
        // preventing against multiple instantiations

        $.data(body, 'customerModal',
            new CustomerModal(options)
        );
    }

    $('body').on('click', '.show_cc', function() {

        customer_pci_token = $(this).data('customer_pci_token');
        var token_source = $(this).data('token_source');

        if(token_source == 'pci_booking'){

            $.ajax({
                type: "POST",
                url: 'show_credit_card_data',
                dataType: "html",
                data: {card_token : customer_pci_token},
                success: function (response) {
                    console.log('response', response);

                    var iframe = document.createElement('iframe');
                    iframe.srcdoc = response;
                    iframe.height = '300px';
                    iframe.width = '100%';
                    iframe.style = 'border-style: none';

                    $('#display-cc-details').find('.modal-body').html(iframe);
                }
            });
                    $('#display-cc-details').modal('show');
            
        } else {

            var imageUrl = getBaseURL() + 'images/loading.gif'
            $('<img class="loader-img" src="'+imageUrl+'" style="width: 7%;margin: -25px -25px;float: right;"/>').insertAfter(this);

            if(
                innGrid.featureSettings.companySecurityStatus == 1 &&
                innGrid.featureSettings.SecurityData > 0
            ) {
                // Inject the content into the modal body first
                var twoFaContent = '<label for="otp" style="text-align: center;">Please enter the 2-factor authentication code from your Google Authenticator app to view credit card details</label>'+
                    '<div class="otp-input" style="display: flex;justify-content: space-between">'+
                        '<input type="text" maxlength="1" style="width: 50px;height: 50px;text-align: center;font-size: 24px;margin: 0 5px;border: 1px solid #ccc;border-radius: 5px;" required>'+
                        '<input type="text" maxlength="1" style="width: 50px;height: 50px;text-align: center;font-size: 24px;margin: 0 5px;border: 1px solid #ccc;border-radius: 5px;" required>'+
                        '<input type="text" maxlength="1" style="width: 50px;height: 50px;text-align: center;font-size: 24px;margin: 0 5px;border: 1px solid #ccc;border-radius: 5px;" required>'+
                        '<input type="text" maxlength="1" style="width: 50px;height: 50px;text-align: center;font-size: 24px;margin: 0 5px;border: 1px solid #ccc;border-radius: 5px;" required>'+
                        '<input type="text" maxlength="1" style="width: 50px;height: 50px;text-align: center;font-size: 24px;margin: 0 5px;border: 1px solid #ccc;border-radius: 5px;" required>'+
                        '<input type="text" maxlength="1" style="width: 50px;height: 50px;text-align: center;font-size: 24px;margin: 0 5px;border: 1px solid #ccc;border-radius: 5px;" required>'+
                    '</div>';

                $('#display-cc-details').find('.modal-body').html(twoFaContent);

                $('#display-cc-details').find('.modal-content').css({"width":"70%","margin":"0px 100px"});
                $('#display-cc-details').find('.modal-body').html(twoFaContent);
                $('#display-cc-details').find('.modal-footer').css("text-align", "center");
                $('#display-cc-details').find('.modal-footer').html('<a href="javascript:" class="verify_cc_otp btn btn-primary" style="margin: 14px 120px;">Verify</a>');

                // Now, reset the input values
                $('.otp-input input').val('');

                // Add event listeners for the newly injected input elements
                

                focusOnNextInput();

                $('#display-cc-details').modal('show');
            }

            if(
                innGrid.featureSettings.companySecurityStatus == 1 &&
                innGrid.featureSettings.SecurityData == 0
            ) {
                var twoFaContent = '<label for="otp" style="text-align: center;">2-Factor Authentication setup is required. Please visit this link and scan QR code for security '+
                        '<a href="' + getBaseURL() + 'account_settings/company_security">Click Here</a>'+
                    '</label>';

                $('#display-cc-details').find('.modal-content').css({"width":"70%","margin":"0px 100px"});
                $('#display-cc-details').find('.modal-body').html(twoFaContent);
                $('#display-cc-details').modal('show');
            }

            
            if(
                innGrid.featureSettings.companySecurityStatus == 0
            ) {
                var iframe = document.createElement('iframe');
                iframe.src = getBaseURL() + "customer/get_credit_card_number?customer_pci_token=" + customer_pci_token;
                iframe.height = '300px';
                iframe.width = '100%';
                iframe.style = 'border-style: none';

                console.log('iframe', iframe);

                $('#display-cc-details').find('.modal-body').html(iframe);

                setTimeout(function(){

                    console.log($('#display-cc-details').find('.modal-body').find('iframe').contents().find("body").html().trim());
                    var responseBody = $('#display-cc-details').find('.modal-body').find('iframe').contents().find("body").html().trim();
                    console.log('responseBody',responseBody);
                    if (responseBody == undefined || responseBody == 'card not found\n' || responseBody == 'card not found' || responseBody == null) {
                        console.log('in');
                        $("#display-cc-details").find('iframe').contents().find("body").html("Card details are no longer viewable");
                        $('#display-cc-details').modal('show');
                        $('.loader-img').hide();
                    } else {
                        $('#display-cc-details').modal('show');
                        $('.loader-img').hide();
                    }

                },3000);
            }

            

            
        }
    });


    $(document).ready(function() {

        $('body').on('click','.verify_cc_otp',function(){

                    let otp = '';
                    let inputs = document.querySelectorAll('.otp-input input'); // Query inputs again
                    inputs.forEach(input => {
                        otp += input.value;
                    });

                    console.log('inputs',otp);

                    $.ajax({
                        type: "POST",
                        url: getBaseURL() + 'settings/company_security/show_cc_verify_otp',
                        dataType: 'json',
                        data: {
                                otp: otp
                            },
                        success: function(resp){
                            console.log('resp',resp);
                            if(resp.success){
                                

                                var iframe = document.createElement('iframe');
                                iframe.src = getBaseURL() + "customer/get_credit_card_number?customer_pci_token=" + customer_pci_token;
                                iframe.height = '300px';
                                iframe.width = '100%';
                                iframe.style = 'border-style: none';

                                console.log('iframe', iframe);

                                $('#display-cc-details').find('.modal-content').removeAttr('style');
                                $('#display-cc-details').find('.modal-body').html(iframe);
                                $('#display-cc-details').find('.modal-footer').html('');

                                setTimeout(function(){

                                    console.log($('#display-cc-details').find('.modal-body').find('iframe').contents().find("body").html().trim());
                                    var responseBody = $('#display-cc-details').find('.modal-body').find('iframe').contents().find("body").html().trim();
                                    console.log('responseBody',responseBody);
                                    if (responseBody == undefined || responseBody == 'card not found\n' || responseBody == 'card not found' || responseBody == null) {
                                        console.log('in');
                                        $("#display-cc-details").find('iframe').contents().find("body").html("Card details are no longer viewable");
                                        $('#display-cc-details').modal('show');
                                        $('.loader-img').hide();
                                    } else {
                                        $('#display-cc-details').modal('show');
                                        $('.loader-img').hide();
                                    }

                                },3000);



                            } else {
                                alert(resp.error_msg);
                            }
                        }
                    });
                });

    });

    function focusOnNextInput(){
        const inputs = document.querySelectorAll('.otp-input input');

        inputs.forEach((input, index) => {
            // Handle input event
            input.addEventListener('input', (e) => {
                if (e.target.value.length === 1) {
                    // Only move to the next input if the current one has exactly 1 character
                    if (index < inputs.length - 1) {
                        inputs[index + 1].focus();
                    } else {
                        input.blur(); // Remove focus if it's the last input
                    }
                }
            });

            // Handle backspace event
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && e.target.value === '' && index > 0) {
                    inputs[index - 1].focus(); // Move to the previous input if backspace is pressed
                }
            });

            // Handle paste event to allow pasting into the inputs
            input.addEventListener('paste', (e) => {
                const paste = e.clipboardData.getData('text').slice(0, inputs.length); // Limit paste length to number of inputs
                paste.split('').forEach((char, i) => {
                    if (inputs[index + i]) {
                        inputs[index + i].value = char;
                        if (index + i + 1 < inputs.length) {
                            inputs[index + i + 1].focus(); // Move focus while pasting
                        }
                    }
                });
                e.preventDefault(); // Prevent default paste behavior
            });
        });
    }

})(jQuery, window, document);
