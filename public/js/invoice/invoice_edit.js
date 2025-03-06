var totalBalance = 0;
var unpaidBookings = new Array();

//Tabs to next field when enter is pressed in field..
//Need to look into this to make sure its doing what I think its doing..
innGrid.checkForEnter = function(event) {
    var locationFound = false;
    if (event.keyCode === 13) {
        var obj = this;

        $(".input-field").each(function() {
            if (this === obj) {
                locationFound = true;
            } else {
                if (locationFound) {
                    $(this).focus();
                    event.preventDefault();
                    return false;
                }
            }
        });
    }
}

var booking_state_val = $('#booking_state').val();
    if(booking_state_val == 2 && innGrid.restrictEditAfterCheckout == 1){
     $('.editable_tr td').removeClass('editable_td');
    }
//Adds charge or payment to invoice
innGrid.addNewCharge = function() {
    // Make sure a charge type or payment type has been entered.
    if ($('#row-type-input').val() === '0') {
        alert(l('Please select Charge Type', true));
        $('#row-type-input').focus();
        return;
    }

    var customerName = $('#row-customer-input option:selected').text();
    if ($('#row-customer-input').val() === '0') {
        customerName = '';
    }

    var rowTR = $('<tr />', {
        'class': 'new-charge editable_tr bg-warning'
    });


    var sellingDate = innGrid._getLocalFormattedDate($('#sellingDate').val());

    //Create row's TDs
    var rowTDs =
        $('<td />', {
            'class': 'editable_td',
            html: $('<span />', {
                'name': 'selling-date',
                html: sellingDate
            })
        })
        .add('<td />', {
            'class': 'editable_td',
            html: $('<span />', {
                'name': 'description',
                html: $('#row-description-input').val()
            })
        })
        .add('<td />', {
            'class': 'editable_td',
            html: $('<span />', {
                'id': $('#row-customer-input').val(),
                'name': 'customer',
                html: customerName
            })
        })
        .add('<td />', {
            'class': 'editable_td',
            html: $('<span />', {
                'id': $('#row-type-input').val(),
                'name': 'charge-type',
                html: $('#row-type-input option:selected').text()
            })
        })
        .add('<td />', {
            'class': 'editable_td text-right',
            html: $('<span />', {
                'name': 'amount',
                html: number_format(parseFloat($('#row-rate-input').val() ? $('#row-rate-input').val() : 0), 2, ".", "")
            })
        })
        .add('<td />', {
            'class': 'text-right',
            html: $('<span />', {
                'class': 'td-tax small'
            })
        })
        .add('<td />', {
            'class': 'text-right',
            html: $('<span />', {
                'class': 'charge',
                "data-real-total-charge": ""
            })
        })
        .add('<td />', {
            'class': 'text-right',
            html: $('<i />', {
                'class': 'x-button'
            })
        });

    var selectedChargeType = $('#row-type-input option:selected').attr('is_room_charge_type');
    if (selectedChargeType == "1") {
        rowTR.append($('<td />', {
            'class': 'editable_td hidden pay-period-col',
            html: $('<span />', {
                id: $('#pay-period-td select').val(),
                name: 'pay-period',
                html: $('#pay-period-td select option:selected').text()
            })
        }));
    }

    $(rowTDs).appendTo(rowTR);

    $("#charge-table > tbody:last").append(rowTR);
    innGrid.updateRowTaxChargeCreditInformation($("#charge-table > tbody > tr:last")); // Generate and display tax, credit, charge information for that row

    //Clear input values for the next entry
    $('#row-description-input').val('');
    $('#row-rate-input').val('');
    $('#row-type-input').val('0');
    $('#row-type-input option[value="0"]').show();
    $('#row-description-input').focus();
    $('#pay-period-td').addClass("hidden");
    // this is necessary, because "fade" class doesn't prevent users from clicking on the invisible button
    $("#button-save-invoice").fadeIn();
}

// When charge or payments changes, update the row's tax, charge & credit
// parameter: a tr jquery object from a table
innGrid.updateRowTaxChargeCreditInformation = function(row) {
    var chargeTypeID = row.find("span[name='charge-type']").attr("id");
    var amount = Number(row.find("span[name='amount']").text().replace(/[^0-9\.-]+/g, ""));
    var totalTax = 0;
    var realTotalTaxes = 0;
    $.ajax({
        type: "POST",
        url: getBaseURL() + 'invoice/get_taxes_AJAX',
        data: { charge_type_id: chargeTypeID, amount: amount },
        dataType: "json",
        success: function(data) {
            var tax_rate;
            var tax;
            var realTaxes;
            row.find('.td-tax').html(''); //Clear existing taxes before generating new ones
            if (data !== '' && data !== null && data.length > 0) { //these checks could be wrong (type comparison issue)
                var taxes = $('');
                for (var index in data) {
                    tax_rate = Number(data[index].tax_rate.replace(/[^0-9\.-]+/g, ""));
                    if (data[index].is_percentage == 1) {
                        realTaxes = parseFloat(amount * tax_rate * 0.01);
                        tax = number_format(parseFloat(amount * tax_rate * 0.01), 2, ".", "");
                    } else {
                        realTaxes = parseFloat(amount * tax_rate * 0.01);
                        tax = number_format(parseFloat(tax_rate), 2, ".", "");
                    }
                    taxes = $(taxes)
                        .add('<div />', {
                            'class': 'tax',
                            html: $('<span />', {
                                    id: data[index].tax_type_id,
                                    'class': 'tax-type' + (data[index].is_tax_inclusive == 1 ? ' hidden' : ''),
                                    html: data[index].tax_type + " "
                                })
                                .add($('<span />', {
                                    'class': 'tax-amount ' + (data[index].is_tax_inclusive == 1 ? ' hidden' : ''),
                                    html: tax,
                                    "data-real-taxes": realTaxes
                                }))
                        });
                    //totalTax += parseFloat(tax);
                    realTotalTaxes += parseFloat(realTaxes);
                }
            }
            row.find('.td-tax').append(taxes);
            if (data && data[index] && data[index].is_tax_inclusive == 1) {
                row.find('.charge').html(number_format(parseFloat(amount), 2, ".", ""));
                row.find('.charge').attr("data-real-total-charge", (number_format(parseFloat(amount), 2, ".", "")));
            } else {
                row.find('.charge').html(number_format(parseFloat(amount + realTotalTaxes), 2, ".", ""));
                row.find('.charge').attr("data-real-total-charge", (number_format(parseFloat(amount + realTotalTaxes), 2, ".", "")));
            }
            row.find('.credit').html('');
            innGrid.updateTotals();
        }
    });

}

innGrid.deleteChargeRow = function(xButton) {
    var tr = xButton.parents('tr');
    var chargeID = tr.attr('id');

    $.ajax({
        beforeSend: function(request) {
            if (!confirm(l('Delete this charge permanently?', true))) {
                return false;
            }
        },
        type: "POST",
        url: getBaseURL() + "invoice/delete_charge_JSON/",
        data: "charge_id=" + chargeID,
        success: function(data) {
            // insufficient access
            if (data == "You don't have permission to access this functionality.") {
                alert(data);
                return;
            }
            if (data !== '') {
                alert(l("insufficient access", true));
                return;
            }
            // success
            tr.remove();
            innGrid.updateTotals();
        }
    });
}

innGrid.deletePaymentRow = function(item, e) {
    e.preventDefault();
    var tr = item.closest('tr');
    var paymentID = tr.attr('id');
    var amountTotal = tr.find('.payment').text();
    var amountStatus = l('full', true);
    var refundHeading = l('Full Refund', true);
    var remainingAmount = (tr.data('remaining-amount') != '') ? tr.data('remaining-amount') : null;
    var payType = $(tr).data('pay-type');
    if (remainingAmount != null) {
        amountTotal = remainingAmount;
        amountStatus = l('remaining', true);
        refundHeading = l('Remaining Refund', true);
    }
    if ($(tr).data('is_gateway') == 1) {
        $('#refund-payment-modal').modal('show');
        if (payType != 'Authorized') {
            payType = '';
        }
        initializeRefundModal(paymentID, refundHeading, amountStatus, amountTotal, remainingAmount, payType);
    } else {
        $('#refund-payment-modal').modal('hide');
        var message = l('Delete this payment permanently?', true);
        $.ajax({
            beforeSend: function(request) {
                if (!confirm(message)) {
                    return false;
                }
            },
            type: "POST",
            url: getBaseURL() + "invoice/delete_payment_JSON/",
            data: "payment_id=" + paymentID,
            success: function(data) {
                // insufficient access
                if (data.trim() !== '') {
                    alert(l("You do not have permission to delete a payment. Contact your administrator if you need access", true));
                    return;
                }
                tr.remove();
                innGrid.updateTotals();
            }
        });
    }
};


innGrid.voidPaymentRow = function(item, e) {
    e.preventDefault();
    var tr = item.closest('tr');
    var paymentID = tr.attr('id');
    var amountTotal = tr.find('.payment').text();
    var amountStatus = l('full', true);
    var refundHeading = l('Full Refund', true);
    var remainingAmount = (tr.data('remaining-amount') != '') ? tr.data('remaining-amount') : null;
    var payType = $(tr).data('pay-type');
    if (remainingAmount != null) {
        amountTotal = remainingAmount;
        amountStatus = l('remaining', true);
        refundHeading = l('Remaining Refund', true);
    }

    var message = l('Are you sure that you want to void this payment?', true);
    $.ajax({
        beforeSend: function(request) {
            if (!confirm(message)) {
                return false;
            }
        },
        type: "POST",
        url: getBaseURL() + "invoice/void_payment/",
        dataType: "JSON",
        data: "payment_id=" + paymentID,
        success: function(data) {
            console.log(data.success);
            if (data.success == true) {
                location.reload();
            }
            innGrid.updateTotals();
        }
    });
};


innGrid.chargeCapturePaymentRow = function(item, e) {
    e.preventDefault();
    var tr = item.closest('tr');
    var paymentID = tr.attr('id');
    var amountTotal = tr.find('.payment').text();
    var amountStatus = l('full', true);
    var refundHeading = l('Full Capture', true);
    var capturePaymentType = $('.capture-payment-modal').data("capture-payment-type");
    var captureAuthorizeId = $('.capture-payment-modal').data("capture-authorize-id");
    var customerId = $('.capture-payment-modal').data("customer-id");
    var bookingId = $('.capture-payment-modal').data("booking-id");
    var remainingAmount = (tr.data('remaining-amount') != '') ? tr.data('remaining-amount') : null;
    if (remainingAmount != null) {
        amountTotal = remainingAmount;
        amountStatus = l('remaining', true);
        refundHeading = l('Remaining Capture', true);
    }
    if ($(tr).data('is_gateway') == 1) {
        $('#charge-capture-modal').modal('show');
        initializeChargeCaptureModal(paymentID, refundHeading, amountStatus, amountTotal, remainingAmount, capturePaymentType, captureAuthorizeId, customerId, bookingId);
    } else {
        $('#charge-capture-modal').modal('hide');
        //        var message = 'Delete this payment permanently?';
        //        $.ajax({
        //            beforeSend: function (request) {
        //                if (!confirm(message))
        //                {
        //                    return false;
        //                }
        //            },
        //            type: "POST",
        //            url    : getBaseURL() + "invoice/delete_payment_JSON/",
        //            data: "payment_id=" + paymentID,
        //            success: function( data ) {               
        //                // insufficient access
        //                if (data !== '') {
        //                    alert("You do not have permission to delete a payment. Contact your administrator if you need access");
        //                    return;
        //                }
        //                tr.remove();
        //                innGrid.updateTotals();
        //            }
        //        });
    }
};

innGrid.addNewFolio = function(event) {
    var folioCount = $('#folios ul li').length;
    if (folioCount > 9) {
        $('#alert-modal').find('.alert').removeClass('alert-success').addClass('alert-danger');
        $('#alert-modal h3').text(l('You cannot add more than 10 folios', true));
        $("#alert-modal").modal('show');
        setTimeout(function() { $("#alert-modal").modal('hide'); }, 3000);
        return false;
    }
    var bookingId = $("#booking_id").val();
    var customerId = $("#customer_id").val();
    var newFolioCount = folioCount + 1;
    var folioName = l('Folio', true) + ' #' + newFolioCount;
    $.ajax({
        type: "POST",
        url: getBaseURL() + 'invoice/add_folio_AJAX',
        data: { "booking_id": bookingId, "customer_id": customerId, "folio_name": folioName },
        dataType: "json",
        success: function(data) {
            var folioId = data.folio_id;
            var firstFolioId = data.first_folio_id;

            if (firstFolioId) {
                $('#folio-name-').parents('li').data('folio-id', firstFolioId);
                $('#folio-name-').attr('id', 'folio-name-' + firstFolioId);
                if (!$('#current_folio_id').val()) {
                    $('#current_folio_id').val(firstFolioId)
                }
            }

            $("#folios ul").append(
                $('<li/>', { 'data-folio-id': folioId })
                .append(
                    $('<div/>')
                    .append($('<a/>', { 'href': getBaseURL() + 'invoice/show_invoice/' + bookingId + '/' + folioId })
                        .append(
                            $('<div/>', {
                                text: folioName
                            })
                        )
                    )
                    .append(
                        $('<a/>', {
                            'data-toggle': 'popover',
                            text: folioName,
                            class: 'folio-name',
                            id: 'folio-name-' + folioId
                        })
                        .popover({
                            html: true,
                            trigger: 'manual',
                            placement: 'right',
                            content: function() {
                                return $('#popover-update-folio-name').html();
                            }
                        })
                    )
                )
                .append($('<span/>', { 'class': 'remove-folio' })
                    .append($('<i/>', { 'class': 'fa fa-close' }))
                )
            );
        },
        error: function(e) {
            alert(l("You don't have permission to access this functionality.", true));
        }
    });
};

innGrid.removeFolio = function() {
    var result = confirm(l("Do you really want to delete this folio?", true));
    if (result) {
        var folioId = $('#folios').find('ul').find('li.active').attr('data-folio-id');
        var bookingId = $("#booking_id").val();
        var customerId = $("#customer_id").val();
        $.ajax({
            type: "POST",
            url: getBaseURL() + 'invoice/remove_folio_AJAX',
            data: { "booking_id": bookingId, "customer_id": customerId, "folio_id": folioId },
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    $("#alert-modal").find('.alert').removeClass('alert-danger').addClass('alert-success');
                    $("#alert-modal h3").text(l('Folio deleted successfully.', true));
                    $("#alert-modal").modal('show');
                    setTimeout(function() { $("#alert-modal").modal('hide'); }, 3000);
                    window.location = getBaseURL() + 'invoice/show_invoice' + '/' + bookingId;
                } else {
                    $("#alert-modal").find('.alert').removeClass('alert-success').addClass('alert-danger');
                    $("#alert-modal h3").text(l('You cannot delete an invoice with charges or payments on it.', true));
                    $("#alert-modal").modal('show');
                    setTimeout(function() { $("#alert-modal").modal('hide'); }, 3000);
                }
            },
            error: function(e) {
                alert(l("You don't have permission to access this functionality.", true));
            }
        });
    }
}

function initializeRefundModal(paymentID, refundHeading, amountStatus, amountTotal, remainingAmount, payType) {

    var modelBody = $("#refund-payment-modal").find(".modal-content");
    var partialAttr = '<input type="radio" name="refund_type" value="partial" style="margin-right: 4px;">';
    var partialMessage = '';
    if (payType == 'Authorized') {
        partialMessage = l('You can not partially refund a pre-authorized amount. Instead, capture the charge for an amount less than the original amount.', true);
        partialAttr = '<input type="radio" name="refund_type" value="partial" style="margin-right: 4px;" disabled="">';
    }

    modelBody.html('');
    modelBody.append(
        $('<div/>', {
            class: "modal-header panel-header"
        }).append(
            $('<button/>', {
                type: 'button',
                class: "close",
                'data-dismiss': "modal",
                'aria-label': "Close"
            }).append(
                $('<span/>', {
                    'aria-hidden': "true",
                    text: 'X'
                })
            )
        ).append(
            $('<h4/>', {
                class: "modal-title text-center",
                text: (payType == 'Authorized') ? l('Refund Pre-Authorization', true) : l('Refund Payment', true)
            })
        )
    ).append(
        $('<div/>', {
            class: "modal-body"
        }).append(
            $('<div/>', {
                class: "panel panel-default"
            }).append(
                $('<label/>', {
                    class: "panel-body"
                }).append(
                    $('<input/>', {
                        type: "radio",
                        name: "refund_type",
                        value: "full",
                        style: "margin-right: 4px;",
                        checked: "checked"
                    })
                ).append(
                    $('<strong/>', {
                        html: refundHeading + '<br/>'
                    })
                ).append(
                    $('<em/>', {
                        style: "padding-left: 19px;font-weight: normal;",
                        html: l('Refund the', true) + ' ' + amountStatus + ' ' + l('amount', true) + ' (' + amountTotal + ')'
                    })
                )
            )
        ).append(
            $('<div/>', {
                class: "panel panel-default",
                style: (payType == 'Authorized') ? 'color:#ccc' : ''
            }).append(
                $('<label/>', {
                    class: "panel-body"
                }).append(
                    partialAttr
                ).append(
                    $('<strong/>', {
                        html: l('Partial Refund', true) + '<br/>'
                    })
                ).append(
                    $('<em/>', {
                        style: "padding-left: 19px;font-weight: normal;",
                        html: l('Refund a partial amount', true)
                    })
                )
            ).append(
                $('<div/>', {
                    id: 'partial-amount-div',
                    class: "hidden",
                    style: 'padding: 15px 12px 15px;'
                }).append(
                    $('<label/>', {
                        class: "control-label col-sm-4",
                        text: l("Partial Amount", true)
                    })
                ).append(
                    $('<div/>', {
                        class: "col-sm-5"
                    }).append(
                        $('<input/>', {
                            type: "number",
                            min: 0,
                            oninput: "validity.valid||(value='0');",
                            step: "any",
                            name: "partial-amount",
                            class: "form-control"
                        })
                    )
                ).append(
                    $('<div/>', {
                        class: 'clearfix'
                    })
                )
            )
        ).append(
            partialMessage
        )
    ).append(
        $('<div/>', {
            class: 'modal-footer'
        })
        .append(
            $('<button/>', {
                type: 'button',
                class: "btn btn-success",
                id: 'refund-payment-btn',
                text: l('Refund', true)
            }).on("click", function() {

                $(this).text('Processing. . .');
                $(this).attr('disabled', true);

                var amount = '';
                var paymentType = '';
                if ($('#refund-payment-modal input[type="radio"]:checked').val() == 'partial') {
                    amount = $('#partial-amount-div input').val();
                    var msg = (amount == '') ? l('partial amount can not be empty', true) : l('partial amount can not be more than full amount', true);
                    paymentType = "partial";
                } else if (remainingAmount != null) {
                    amount = remainingAmount;
                    paymentType = "remaining";
                } else {
                    paymentType = "full";
                }
                $.ajax({
                    beforeSend: function(request) {
                        if (msg) {
                            if (parseFloat(amount) > amountTotal || amount == '') {
                                $('#refund-payment-btn').attr('disabled', false);
                                alert(msg)
                                return false;
                            }
                        }
                    },
                    type: "POST",
                    url: getBaseURL() + "invoice/refund_payment_JSON",
                    data: { payment_id: paymentID, amount: amount, payment_type: paymentType, folio_id: $('#current_folio_id').val() },
                    success: function(data) {
                        console.log('refund_payment_JSON', data);
                        if (data == "You don't have permission to access this functionality.") {
                            alert(data);
                            return;
                        }
                        if (data !== '') {
                            data = JSON.parse(data);
                            if (typeof data.success === "undefined" || !data.success) {
                                //insufficient access
                                $('#refund-payment-btn').attr('disabled', false);

                                var error_html = "";
                                // console.log(jQuery.isArray( data.message ));
                                if (jQuery.isArray(data.message)) {
                                    $.each(data.message, function(i, v) {
                                        error_html += v.description + '\n';
                                    });
                                    console.log(error_html);
                                    $('#display-errors').find('.modal-body').html(error_html.replace(/\n/g, '<br/>'));
                                    $('#refund-payment-modal').modal('hide');
                                    $('#display-errors').modal('show');

                                    $('#display-errors').on('hidden.bs.modal', function() {
                                        $('#refund-payment-modal').modal('show');
                                    });
                                    // alert(error_html);
                                } else if (data.message) {
                                    alert(data.message);
                                    location.reload();
                                } else {
                                    alert(l("insufficient access", true));
                                }
                                return;
                            }
                        }
                        // success
                        innGrid.updateTotals();
                        location.reload();
                    }
                });
            })
        )
        .append(
            $('<button/>', {
                type: 'button',
                class: "btn btn-default",
                id: 'button-cancel',
                text: l('Cancel', true)
            }).on('click', function() {
                $('#refund-payment-btn').attr('disabled', false);
                $('#refund-payment-modal').modal('hide');
            })
        )
    );
    modelBody.find('input[type="radio"]').on('click', function() {
        if ($(this).val() == 'partial') {
            $('#partial-amount-div').removeClass('hidden');
        } else {
            $('#partial-amount-div').addClass('hidden');
        }
    });
}

function initializeChargeCaptureModal(paymentID, refundHeading, amountStatus, amountTotal, remainingAmount, capturePaymentType, captureAuthorizeId, customerId, bookingId) {

    var modelBody = $("#charge-capture-modal").find(".modal-content");
    modelBody.html('');
    modelBody.append(
        $('<div/>', {
            class: "modal-header panel-header"
        }).append(
            $('<button/>', {
                type: 'button',
                class: "close",
                'data-dismiss': "modal",
                'aria-label': "Close"
            }).append(
                $('<span/>', {
                    'aria-hidden': "true",
                    text: 'X'
                })
            )
        ).append(
            $('<h4/>', {
                class: "modal-title text-center",
                text: l('Capture Payment', true)
            })
        )
    ).append(
        $('<div/>', {
            class: "modal-body"
        }).append(
            $('<div/>', {
                class: "panel panel-default"
            }).append(
                $('<label/>', {
                    class: "panel-body"
                }).append(
                    $('<input/>', {
                        type: "radio",
                        name: "charge_type",
                        value: "full",
                        style: "margin-right: 4px;",
                        checked: "checked"
                    })
                ).append(
                    $('<strong/>', {
                        html: refundHeading + '<br/>'
                    })
                ).append(
                    $('<em/>', {
                        style: "padding-left: 19px;font-weight: normal;",
                        html: l('Capture the', true) + ' ' + amountStatus + ' ' + l('amount', true) + ' (' + amountTotal + ')'
                    })
                )
            )
        ).append(
            $('<div/>', {
                class: "panel panel-default"
            }).append(
                $('<label/>', {
                    class: "panel-body"
                }).append(
                    $('<input/>', {
                        type: "radio",
                        name: "charge_type",
                        value: "partial",
                        style: "margin-right: 4px;",
                    })
                ).append(
                    $('<strong/>', {
                        html: l('Partial Capture', true) + '<br/>'
                    })
                ).append(
                    $('<em/>', {
                        style: "padding-left: 19px;font-weight: normal;",
                        html: l('Capture a partial amount', true)
                    })
                )
            ).append(
                $('<div/>', {
                    id: 'partial-charge-amount-div',
                    class: "hidden",
                    style: 'padding: 15px 12px 15px;'
                }).append(
                    $('<label/>', {
                        class: "control-label col-sm-4",
                        text: l("Partial Amount", true)
                    })
                ).append(
                    $('<div/>', {
                        class: "col-sm-5"
                    }).append(
                        $('<input/>', {
                            type: "number",
                            min: 0,
                            oninput: "validity.valid||(value='0');",
                            step: "any",
                            name: "partial-amount",
                            class: "form-control"
                        })
                    )
                ).append(
                    $('<div/>', {
                        class: 'clearfix'
                    })
                )
            )
        )
    ).append(
        $('<div/>', {
            class: 'modal-footer'
        })
        .append(
            $('<button/>', {
                type: 'button',
                class: "btn btn-success capture-payment-button",
                id: 'charge-capture-payment-btn',
                text: l('Capture', true)
            }).on("click", function() {

                $(this).text('Processing. . .');
                $(this).attr('disabled', true);
                var amount = '';
                var paymentType = '';
                if ($('#charge-capture-modal input[type="radio"]:checked').val() == 'partial') {
                    amount = $('#partial-charge-amount-div input').val();
                    var msg = (amount == '') ? l('partial amount can not be empty', true) : l('partial amount can not be more than full amount', true);
                    paymentType = amountStatus = "partial";
                } else if (remainingAmount != null) {
                    amount = remainingAmount;
                    paymentType = "remaining";
                } else {
                    paymentType = "full";
                    amount = amountTotal;
                }
                $.ajax({
                    beforeSend: function(request) {
                        if (msg) {
                            if (parseFloat(amount) > amountTotal || amount == '') {
                                alert("if");
                                $('#charge-capture-payment-btn').attr('disabled', false);
                                alert(msg)
                                return false;
                            }
                        }
                    },
                    type: "POST",
                    url: getBaseURL() + "invoice/get_payment_capture",
                    dataType: 'JSON',
                    data: {
                        payment_id: paymentID,
                        auth_id: captureAuthorizeId,
                        amount: amount,
                        payment_type: capturePaymentType,
                        customer_id: customerId,
                        booking_id: bookingId,
                        amount_status: amountStatus,
                        remaining_amount: remainingAmount,
                        capture_heandling: refundHeading
                    },
                    success: function(response) {
                        if (!response.success) {
                            alert(response.message);
                        }
                        location.reload();
                    }
                });


            })
        )
        .append(
            $('<button/>', {
                type: 'button',
                class: "btn btn-default",
                id: 'button-cancel-capture',
                text: l('Cancel', true)
            }).on('click', function() {
                $('#charge-capture-payment-btn').attr('disabled', false);
                $('#charge-capture-modal').modal('hide');
            })
        )
    );
    modelBody.find('input[type="radio"]').on('click', function() {
        if ($(this).val() == 'partial') {
            $('#partial-charge-amount-div').removeClass('hidden');
        } else {
            $('#partial-charge-amount-div').addClass('hidden');
        }
    });
}


//Populates the drop down list
//by generating the option list and appending it to the input
//previouslySelectedValue is the html or text, 
//used to reselect the previous selected value while the drop down list is being generated. it may be undefined
innGrid.showChargeTypesDDL = function(field) {

    var originalValue = field.attr("title");

    var select = $('<select />', {
        'class': 'edit-state input-field form-control'
    });

    $('<option />', {
        value: 0,
        html: l('Select a type', true) + '...'
    }).appendTo(select);

    $.getJSON(getBaseURL() + "invoice/get_charge_types_in_JSON/",
        function(charge_types) {
            for (var i in charge_types) {
                var option = $('<option />', {
                    'name': 'charge-type',
                    value: charge_types[i]['id'],
                    html: charge_types[i]['name']
                });

                if (charge_types[i]['name'] === String(originalValue)) {
                    option.attr("selected", "selected");
                }

                option.appendTo(select);

            }

            field.html(select);
            select.focus();
        });
}


//Populates the drop down list
//by generating the option list and appending it to the input
//previouslySelectedValue is the html or text, 
//used to reselect the previous selected value while the drop down list is being generated. it may be undefined
innGrid.showCustomerDDL = function(field) {

    var originalValue = field.attr("title");

    var select = $('<select />', {
        'class': 'edit-state input-field form-control'
    });

    $('<option />', {
        value: 0,
        html: l('Not assigned', true)
    }).appendTo(select);

    $.ajax({
        type: "POST",
        url: getBaseURL() + "invoice/get_customers_in_JSON/",
        data: {
            booking_id: $('#booking_id').val()
        },
        dataType: "json",
        success: function(customers) {
            for (var i in customers) {
                var option = $('<option />', {
                    'name': 'customer',
                    value: customers[i]['customer_id'],
                    html: customers[i]['customer_name']
                });

                if (customers[i]['customer_name'] === String(originalValue)) {
                    option.attr("selected", "selected");
                }

                option.appendTo(select);

            }

            field.html(select);
            select.focus();
        }
    });
}

// edit field! field is span inside .editable td
innGrid.editField = function(span) {
    var originalValue = $.trim($(span).html());
    $(span).attr("title", originalValue); // temporarily store original value in title attribute

    var fieldType = span.attr('name');

    //Generate the replacement input based on the type of span
    if (fieldType === 'description' ||
        fieldType === 'selling-date' ||
        fieldType === 'amount'
    ) {
        var newValueInput = $('<input />', {
            'class': 'edit-state input-field form-control',
            type: 'text',
            value: originalValue
        });

        if (fieldType === 'amount') {
            newValueInput.addClass("text-right");
        }

        span.html(newValueInput);
        span.find('.edit-state').focus();

    } else if (fieldType === 'charge-type') {
        innGrid.showChargeTypesDDL(span);
    } else if (fieldType === 'customer') {
        innGrid.showCustomerDDL(span);
    }

    $("select.edit-state").change(function() {
        $(this).blur();
    });

    $("#button-save-invoice").fadeIn();
}

// Called when user presses enter or clicks away from editing a field (this function is called after innGrid.editField())
// If value has been changed, "modified-field" class is appended
// Coupled with innGrid.editField
//Some odd reason chrome calls this twice when you use the enter button
innGrid.blurInputField = function(input) {
    var fieldSpan = input.parent();
    var fieldTD = input.parent().parent();
    var fieldTR = fieldTD.parent('tr');
    var fieldType;

    if (fieldSpan.hasClass('modified-field')) {
        var fieldSpanCopy = $(fieldSpan).clone();
        fieldSpanCopy.removeClass('modified-field');
        fieldType = fieldSpanCopy.attr('name');
    } else {
        fieldType = fieldSpan.attr('name');
    }

    var originalValue = fieldSpan.attr("title");
    var newValue;
    var newFieldType;

    fieldTD.addClass('editable_td'); // Allow the td to be edited if clicked again   

    if (fieldType === 'description' ||
        fieldType === 'selling-date' ||
        fieldType === 'amount') {
        newValue = input.val();

        //Input validation for amount
        if (fieldType === 'amount' && isNaN(newValue)) {
            fieldSpan.html(originalValue);
            alert(l('Please enter a valid number.', true));
        } else {
            // add modified-field class so when save invoice is executed this change is saved.
            // do not add modified-field class if the TR already is new-payment or new-charge
            if (originalValue !== newValue && !(fieldTR.hasClass("new-payment") || fieldTR.hasClass("new-charge"))) {
                fieldSpan.addClass("modified-field");
                fieldTR.addClass("modified-row");
            }
            fieldSpan.html(newValue);
        }

    } else if (fieldType === 'charge-type') {
        // get the field type of newly selected option (whether it's a charge or a payment)
        newFieldType = "charge-type";
        newChargeTypeID = input.children().filter(":selected").val();
        newValue = input.children().filter(":selected").text();

        //If user hasn't selected a type yet. restore original value
        if (newValue === 'Select a type...' || input.val() === '0') {
            fieldSpan.html(originalValue);
            return;
        }

        fieldSpan.attr("id", newChargeTypeID);
        fieldSpan.attr("name", newFieldType);
        fieldSpan.html(newValue);

        //If change has been made, apply "modified-field" and "modified-row" classes
        // do not add "modified-row" class if the TR already is a new payment/charge
        if (originalValue !== newValue) {
            fieldTR.addClass("modified-row");
            fieldSpan.addClass("modified-field");
        }
    } else if (fieldType === 'customer') {
        // get the field type of newly selected option (whether it's a charge or a payment)
        newFieldType = "customer";
        newCustomerID = input.children().filter(":selected").val();
        newValue = input.children().filter(":selected").text();

        //If user hasn't selected a type yet. restore original value
        if (newValue === 'Select a type...' || input.val() === '0') {
            fieldSpan.html(originalValue);
            return;
        }

        fieldSpan.attr("id", newCustomerID);
        fieldSpan.attr("customer_name", newFieldType);
        fieldSpan.html(newValue);

        //If change has been made, apply "modified-field" and "modified-row" classes
        // do not add "modified-row" class if the TR already is a new payment/charge
        if (originalValue !== newValue) {
            fieldTR.addClass("modified-row");
            fieldSpan.addClass("modified-field");
        }
    }

    fieldSpan.addClass("text-danger");
    innGrid.updateRowTaxChargeCreditInformation(fieldTR);
}


/**
    SAVING INVOICE!
*/


innGrid.save = function() {
    //Prevent saving multiple times if its clicked multiple times in succession

    var bookingID = $('#booking_id').val();
    var folio_id = $('#current_folio_id').val();
    if (folio_id == '') {
        folio_id = 0;
    }
    //Store NEW charges
    var newCharges = new Array();
    $('.new-charge').each(function() {
        var sellingDate = innGrid._getBaseFormattedDate($(this).find("[name='selling-date']").text());
        var description = $(this).find("[name='description']").text();
        var chargeTypeID = $(this).find("[name='charge-type']").attr("id");
        var customerID = $(this).find("[name='customer']").attr("id");
        var amount = $(this).find("[name='amount']").text();
        var payPeriod = $(this).find(".pay-period-col span").attr("id");
        var isRoomChargeType = $('#charge-table tfoot #row-type-input').find('option[value=' + chargeTypeID + ']').attr('is_room_charge_type');

        newCharges.push({
            'selling_date': sellingDate,
            'description': description,
            'customer_id': customerID,
            'charge_type_id': chargeTypeID,
            'amount': amount,
            'booking_id': bookingID,
            'pay_period': payPeriod,
            'isRoomChargeType': isRoomChargeType
        });
    });

    //Store changes in EXISTING charges
    var chargeChanges = new Array();
    $(".modified-row").each(function() {
        var row = {};

        $(this).find('.modified-field').each(function() {
            if ($(this).attr("name") === ('selling-date')) {
                row['selling_date'] = $(this).text();
            } else if ($(this).attr("name") === ('description')) {
                row['description'] = $(this).text();
            } else if ($(this).attr("name") === ('customer')) {
                row['customer_id'] = $(this).attr("id");
            } else if ($(this).attr("name") === ('charge-type')) {
                row['charge_type_id'] = $(this).attr("id");
            } else if ($(this).attr("name") === ('amount')) {
                row['amount'] = $(this).text();
            }
        });

        row['charge_id'] = $(this).attr("id");
        chargeChanges.push(row);

    });

    $.post(getBaseURL() + 'invoice/save_invoice', {
        'booking_id': bookingID,
        'charges': newCharges,
        'charge_changes': chargeChanges,
        'folio_id': folio_id
    }, function(str) {
        if (str == "You don't have permission to access this functionality.") {
            alert(str);
        }
        window.location.reload();
    });
}

// populate Payment Amount and Payment Description based on pay_for selection ("this invoice only" or "all bookings belonging to this customer")
innGrid.populatePaymentInformation = function() {
    var payFor = $("[name='pay_for']").val();
    if (payFor == "this_invoice_only") {
        $("input[name='payment_amount']").prop("readonly", false);
        $("input[name='payment_amount']").val(number_format(parseFloat($("#amount_due").text().replace(/,/g, '')), 2, ".", ""));
        $("textarea[name='description']").val("");
    } else if (payFor == 'all_bookings') {
        $("input[name='payment_amount']").prop("readonly", true);
        $("input[name='payment_amount']").val(number_format(totalBalance, 2, ".", ""));
        $("textarea[name='description']").val(l('Part of', true) + " " + number_format(totalBalance, 2, ".", "") + " " + l('payment', true));
    }
}

/*open move charge or payment modal*/
$("#move-charge-payment").prop("disabled", true);
innGrid.moveToFolioModal = function() {
    $('#move-charge-payment-modal').modal('show');
    var folio_id = $("#current_folio_id").val();
    var booking_id = $("#booking_id").val();
    var customer_id = $("#customer_id").val();
    var charge_id = $(this).parents('tr').attr('charge_id');
    var payment_id = $(this).parents('tr').attr('payment_id');
    $.ajax({
        type: "POST",
        url: getBaseURL() + 'invoice/get_folios_JSON',
        data: { "folio_id": folio_id, "booking_id": booking_id, "customer_id": customer_id },
        dataType: "json",
        success: function(folios) {
            if (folios.length > 0) {
                $("#move-charge-payment").prop("disabled", false);
            }
            $('#move-charge-payment-modal').find("input[name=charge_id]").val(charge_id);
            $('#move-charge-payment-modal').find("input[name=payment_id]").val(payment_id);
            $("#folios-list").html('');
            for (var i = 0; i < folios.length; i++) {
                $("#folios-list").append(
                    $("<option/>", {
                        value: folios[i].id,
                        html: folios[i].folio_name
                    })
                );
            }
        }
    });
}

innGrid.moveChargePayment = function(event) {
    var folio_id_from = $("#current_folio_id").val();
    var folio_id_to = $("#folios-list").val();
    var booking_id = $("#booking_id").val();
    var customer_id = $("#customer_id").val();
    var charge_id = $('#move-charge-payment-modal').find("input[name=charge_id]").val();
    var payment_id = $('#move-charge-payment-modal').find("input[name=payment_id]").val();
    $.ajax({
        type: "POST",
        url: getBaseURL() + 'invoice/move_charge_payment',
        data: { "booking_id": booking_id, "customer_id": customer_id, "folio_id_from": folio_id_from, "folio_id_to": folio_id_to, "charge_id": charge_id, "payment_id": payment_id },
        dataType: "json",
        success: function(data) {
            if (data == true) {
                $('#move-charge-payment-modal').modal('hide');
                $('#' + charge_id).remove();
                $('#' + payment_id).remove();
                $('#alert-modal').find('.alert').removeClass('alert-danger').addClass('alert-success');
                $('#alert-modal h3').text(l('Saved', true) + '!');
                $("#alert-modal").modal('show');
                setTimeout(function() {
                    $("#alert-modal").modal('hide');
                }, 3000);

            }
        },
        error: function(e) {
            alert(l("You don't have permission to access this functionality.", true));
        }
    });
}

/**
    ACTION STARTS HERE
*/


$(function() {


    // initialize refund payment model
    $('body').append($('<div/>', {
        class: "modal fade",
        id: "refund-payment-modal",
        tabindex: "-1",
        role: "dialog",
        "aria-hidden": "true"
    }).append(
        $("<div/>", {
            class: "modal-dialog"
        }).append(
            $("<div/>", {
                class: "modal-content"
            }).html("")
        )
    ));
    $('body').append($('<div/>', {
        class: "modal fade",
        id: "invoice-logs-modal",
        tabindex: "-1",
        role: "dialog",
        "aria-hidden": "true"
    }).append(
        $("<div/>", {
            class: "modal-dialog"
        }).append(
            $("<div/>", {
                class: "modal-content"
            }).html("")
        )
    ));

    $('body').append($('<div/>', {
        class: "modal fade",
        id: "charge-capture-modal",
        tabindex: "-1",
        role: "dialog",
        "aria-hidden": "true"
    }).append(
        $("<div/>", {
            class: "modal-dialog"
        }).append(
            $("<div/>", {
                class: "modal-content"
            }).html("")
        )
    ));

    $("#refund-payment-modal").modal({ show: false, backdrop: 'static', keyboard: false });
    $("#charge-capture-modal").modal({ show: false, backdrop: 'static', keyboard: false });


    $(".open-booking-button").on('click', function() {
        if (typeof $(this).openBookingModal !== 'undefined' && $.isFunction($(this).openBookingModal)) {
            $(this).openBookingModal({
                id: $(this).data("booking-id")
            });
        }
    });

    $(".open-invoice-logs-button").on('click', function() {
        var that = this;
        $.ajax({
            type: "POST",
            url: getBaseURL() + "invoice/get_invoice_history_AJAX",
            data: {
                booking_id: $(this).data("booking-id")
            },
            dataType: "json",
            success: function(logs) {
                if (logs.length > 0) {
                    _populateHistoryModal(logs);
                } else {
                    alert(l("No Records Found", true));
                }
            }
        });
    });

    $(document).on('hide.bs.modal', "#booking-modal", function(e) {
        // hack to prevent closing inner-modal removing modal-open class in body.
        // when modal-open class is removed from body, scrolling the booking-modal scrolls
        // background, instead of scrolling the modal
        window.location.reload();
    });


    $(document).on("click", ".editable_td", function() {
        var td = $(this).removeClass("editable_td"); // prevent clicking editable-td while editing          
        innGrid.editField(td.find('span'));
    });

    //User finishes editing by blur
    $(document).on("blur", ".edit-state", function() {
        innGrid.blurInputField($(this));
    });

    //If user presses "enter" while editing, blur the input
    $(document).on("keypress", ".edit-state", function(e) {
        if (e.which == 13) {
            $(this).blur();
        }
    });

    // $(document).on("click", ".charge_row .x-button", function() {
    //     innGrid.deleteChargeRow($(this));
    // });

    $('.delete_charge').on("click", function() {
        innGrid.deleteChargeRow($(this));
    });

    // $(document).on("click", ".payment_row .x-button, .delete-payment", function (e) {
    //     innGrid.deletePaymentRow($(this), e);
    // });

    $('.delete-payment').on("click", function(e) {
        innGrid.deletePaymentRow($(this), e);
    });

    $('.void-payment').on("click", function(e) {
        innGrid.voidPaymentRow($(this), e);
    });

    $(document).on("click", ".payment_row .x-button, .capture-payment-modal", function(e) {
        innGrid.chargeCapturePaymentRow($(this), e);
    });

    $(document).on({
        mouseenter: function() {
            $(this).find(".delete-menu").show();
        },
        mouseleave: function() {
            $(this).find(".delete-menu").hide();
        }
    }, ".payment_row");


    // x button is clicked on new charge or new payment row
    $(document).on("click", ".new-charge > td > .x-button", function() {
        $(this).parent().parent("tr").remove();
        innGrid.updateTotals();
    });

    //Adds charge/payment at pre-save area
    $("#button-add-charge").on("click", innGrid.addNewCharge);

    $('#row-type-input').on('change', function() {
        var selectedChargeType = $('#row-type-input option:selected').attr('is_room_charge_type');
        if (selectedChargeType == "1") {
            $('#pay-period-td').removeClass("hidden");
        } else {
            $('#pay-period-td').addClass("hidden");
        }
    });

    $("#button-save-invoice").on("click", function() {
        $(this).prop("disabled", true);
        innGrid.saveButtonClicked = true;
        innGrid.save();
    });

    $("#email-invoice-button").click(function() {
        // check if email exists
        if ($("#customer-email").length == 0) {
            alert(l("Please enter the customer's email address first.", true));
        } else if ($("#company-email").length == 0) {
            alert(l("Please setup your company's email first", true));
        } else {
            var confirmation = confirm(l("Are you sure you want to send this invoice via E-mail?", true));
            if (confirmation == true) {
                if ($("#booking_id").val() == '') {
                    $.post(getBaseURL() + 'invoice/email_master_invoice/' + $("#booking_id_for_group_confirmation_email").val() + '/' + $("#group_id").val(), function(response) {
                        alert(response);
                    });
                } else {
                    var folioId = $('#current_folio_id').val();
                    var url = getBaseURL() + 'invoice/email_invoice/' + $("#booking_id").val();
                    url = folioId ? url + '/' + folioId : url;
                    $.post(url, {}, function(data) {

                        alert(data);
                        // reload, because the invoice log will be updated on the bottom of the page   
                    });
                }
            }
        }
    });

    $("#feedback-email-button").click(function() {
        // check if email exists
        if ($("#customer-email").length == 0) {
            alert(l("Please enter the customer's email address first.", true));
        } else if ($("#company-email").length == 0) {
            alert(l("Please setup your company's email first", true));
        } else {
            var confirmation = confirm(l("Are you sure you want to send feedback E-mail?", true));
            if (confirmation == true) {
                if ($("#booking_id").val() != '') {
                    var folioId = $('#current_folio_id').val();
                    var url = getBaseURL() + 'invoice/email_feedback/' + $("#booking_id").val();
                    url = folioId ? url + '/' + folioId : url;
                    $.post(url, {}, function(data) {
                        alert(data);
                    });
                }
            }
        }
    });

    // Once a Type is selected, remove "Select Type..." selection
    $("#charge-type-input").change(function() {
        $("#charge-type-input option[value='0']").hide();
    });

    //For enter key to act as tab
    $(".input-field").keydown(innGrid.checkForEnter);

    //Warns about changes that haven't been saved when trying to leave the page.
    $(window).bind('beforeunload', function() {
        if ($('.modified-row, .new-charge, .new-payment').length && !innGrid.saveButtonClicked) {
            //Some odd reason this isn't displaying the proper message. Jquery doesn't have supported documentation on this event.
            return l('You have unsaved changes. Are you sure you want to leave the page?', true);
        };
    });

    // Payment
    $("input[name='payment_date']").datepicker({
        dateFormat: ($('#companyDateFormat').val()).toLowerCase()
    });

    $(".payment-modal-button").on("click", function() {
        $("[name='payment_amount']").val(number_format(parseFloat($("#amount_due").text().replace(/,/g, '')), 2, ".", ""));
    });

    // figure out if there's multiple invoices for this customer
    // calculates: number of unpaid bookings and a combined balance owed.
    $.ajax({
        type: "POST",
        url: getBaseURL() + 'customer/get_all_unpaid_bookings_AJAX',
        data: {
            customer_id: $("[name='customer_id']").val()
        },
        dataType: "json",
        success: function(data) {

            // if there's only 1 invoice for this customer, then do not show the option for adding payment for multiple invoices
            if (data.length <= 1) {
                $("#option-to-add-multiple-payments").hide();
            } else {
                //console.log(data.length);
                $("#option-to-add-multiple-payments").show();
                $("[name='pay_for'] option[name='all_bookings_option']").text(l('All', true) + " " + data.length + " " + l('bookings made by this customer', true));
            }
            totalBalance = 0;

            $.each(data, function(index, value) {
                totalBalance = totalBalance + parseFloat(value.balance);
                unpaidBookings.push({
                    booking_id: value.booking_id,
                    balance: value.balance
                });
            });
        }

    });

    innGrid.populatePaymentInformation();
    $("select[name='pay_for']").on("change", function() {
        innGrid.populatePaymentInformation();
    });

    // // gateway button
    // var $methods_list = $('select[name="payment_type_id"]');
    // var gateway_button = $('input[name="use_gateway"]');
    // var selected_gateway = $('input[name="use_gateway"]').data('gateway_name');

    // var gatewayTypes = {
    //     'PayflowGateway': 'PayPal Payflow Pro',
    //     'FirstdataE4Gateway': 'FirstData Gateway e4(Payeezy)',
    //     'ChaseNetConnectGateway': 'Chase Payment Gateway',
    //     'AuthorizeNetGateway': 'Authorize.Net',
    //     'PayuGateway': 'Payu Gateway',
    //     'QuickbooksGateway': 'Quickbooks Gateway',
    //     'ElavonGateway': 'Elavon My Virtual Merchant',
    //     'MonerisGateway': 'Moneris eSelect Plus',
    //     'CieloGateway': 'Cielo Gateway',
    //     'SquareGateway': 'Square Gateway'
    // };
    // selected_gateway = gatewayTypes[selected_gateway];

    // $methods_list.prop('disabled', false);
    // gateway_button.prop('checked',0);

    // gateway_button.on('click',function(){
    //     $that = $(this);

    //     var checked = $that.prop('checked');
    //     $methods_list.prop('disabled', checked);
    //     var manualPaymentCapture = $("#manual_payment_capture").val();
    //     if(checked)
    //     {
    //         if(manualPaymentCapture == 1)
    //         {
    //             $('#auth_and_capture').removeClass('hidden');
    //             $('#authorize_only').removeClass('hidden');
    //             $('#add_payment_normal').addClass('hidden');
    //         }
    //         else{
    //             $('#add_payment_button').removeClass('hidden');
    //             $('#add_payment_normal').addClass('hidden');
    //             $('#auth_and_capture').addClass('hidden');
    //             $('#authorize_only').addClass('hidden');
    //         }
    //         $methods_list
    //             .append(
    //             $('<option></option>',{
    //                 id : 'gateway_option'
    //             })
    //                 .val('gateway')
    //                 .html(selected_gateway)
    //         );
    //         $methods_list.val('gateway');

    //         var available_gateway = $('.paid-by-customers').children('option:selected').data('available-gateway');

    //         // false by default. until we find proper solution to store cvc
    //         if(false && available_gateway == 'tokenex')
    //         {
    //             $that.parents('#use-gateway-div').append(
    //                 $('<div/>',{
    //                     class: 'col-sm-10',
    //                     id: 'cvc-field'
    //                 }).append(
    //                      $("<label/>", {
    //                         for : "cvc",
    //                         class: "col-sm-3 control-label",
    //                         text: l("CVC", true)
    //                     })
    //                 ).append(
    //                     $("<div/>", {
    //                         class: "col-sm-9"
    //                     }).append(
    //                     $("<input/>", {
    //                         class: "form-control",
    //                         name: "cvc",
    //                         placeholder: '***',
    //                         type: 'password',
    //                         maxlength: 4,
    //                         autocomplete: false,
    //                         required: "required"
    //                     })
    //                     )
    //                 )
    //             );
    //         }
    //     }else{
    //         if(manualPaymentCapture == 1)
    //         {
    //             $('#auth_and_capture').addClass('hidden');
    //             $('#authorize_only').addClass('hidden');
    //             $('#add_payment_normal').removeClass('hidden');
    //         }
    //         else{
    //             $('#add_payment_button').addClass('hidden');
    //             $('#add_payment_normal').removeClass('hidden');
    //             $('#auth_and_capture').removeClass('hidden');
    //             $('#authorize_only').removeClass('hidden');
    //         }
    //         $('#gateway_option').remove();
    //         $('#cvc-field').remove();
    //     }
    // });

    // // show "Use Payment Gateway" option 
    // $('.paid-by-customers').on('change', function(){
    //     var isGatewayAvailable = $(this).find('option:selected').attr('is-gateway-available');
    //     if(isGatewayAvailable == 'true'){
    //         $('.use-payment-gateway-btn').show();
    //         $('input[name="use_gateway"]').prop('checked', false);
    //         $('#cvc-field').remove();
    //         //$('select[name = "payment_type_id"]').attr('disabled');
    //         $checked = $('input[name="use_gateway"]').prop('checked');
    //         if($checked){
    //             $('select[name = "payment_type_id"]')
    //                     .append('<option id="gateway_option" value="gateway">'+selected_gateway+'</option>')
    //         }
    //     }
    //     else
    //     {
    //         $('.use-payment-gateway-btn').hide();
    //         $('select[name = "payment_type_id"]').removeAttr('disabled');
    //         $('#gateway_option').remove();
    //         $('input[name="use_gateway"]').prop('checked', 0);
    //     }
    // });
    // if( $('.paid-by-customers option:selected').attr('is-gateway-available') == 'true'){
    //     $('.use-payment-gateway-btn').show();
    // }

    // FF cache... 
    $add_payment_button = $(".add_payment_button");
    $add_payment_button.prop("disabled", false);

    $add_payment_button.on("click", function() {
        var that = this;
        $(that).html("Processing. . .");
        $(that).prop("disabled", true);

        var capture_payment_type = $(that).attr('id');

        var payFor = $("[name='pay_for']").val();
        var d = $.Deferred();
        var is_group_invoice = $('#is_group_invoice').val();
        if (is_group_invoice && is_group_invoice != undefined) {
            d.resolve();
        } else if ($("select[name='payment_type_id']").val() == 'gateway') {
            $.ajax({
                url: getBaseURL() + 'invoice/is_payment_available',
                method: 'post',
                dataType: 'json',
                data: {
                    booking_id: $("#booking_id").val(),
                    customer_id: $("[name='customer_id']").val()
                },
                success: function(available) {
                    console.log('available',available);

                    if(parseInt(available) != 1) {

                        var error_html = available.error.type + '\n\nPlease check the logs in your Stripe account from here\n' + "<a href='"+available.error.request_log_url+"' target='_blank'>"+available.error.request_log_url+"</a>" ;
                        // alert(available.error);
                        $('#display-errors').find('.modal-body').html(error_html.replace(/\n/g, '<br/>'));
                        $('#display-errors').modal('show');
                        available = 0;
                    }

                    parseInt(available) == 1 ? d.resolve() : d.reject();
                }
            });
        } else {
            d.resolve();
        }


        $.when(d.promise())
            .then(function() {
                // validation
                if (
                    parseFloat($("input[name='payment_amount']").val()) == 0
                ) {
                    alert(l('Amount cannot be', true) + ' 0');
                    $(that).prop("disabled", false);
                    return;
                }

                if (
                    parseFloat($("input[name='payment_amount']").val()) < 0 &&
                    $("select[name='payment_type_id']").val() == 'gateway'
                ) {
                    alert(l('Amount cannot be less than', true) + ' 0');
                    $(that).prop("disabled", false);
                    return;
                }

                if (
                    parseFloat($("input[name='payment_amount']").val()) <= 0.5 &&
                    $("select[name='payment_type_id']").val() == 'gateway'
                ) {
                    //itodo coupling
                    alert(l('Amount for', true) + ' ' + selected_gateway + ' ' + l('must be more than', true) + ' 0.5$');
                    $(that).prop("disabled", false);
                    return;
                }

                if ($("select[name='payment_type_id']").val() == null) {
                    alert(l('please select payment method', true));
                    $(that).prop("disabled", false);
                    return;
                }

                if ($("input[name='cvc']").val() == '') {
                    alert(l('please enter cvc', true));
                    $(that).prop("disabled", false);
                    return;
                }

                // if paying for this invoice only
                if (is_group_invoice && is_group_invoice != undefined) {
                    $.post(getBaseURL() + 'invoice/insert_payment_AJAX', {
                        group_id: $("#group_id").val(),
                        payment_type_id: $("select[name='payment_type_id']").val(),
                        customer_id: $("select[name='customer_id']").val(),
                        payment_amount: $("input[name='payment_amount']").val(),
                        payment_date: innGrid._getBaseFormattedDate($("input[name='payment_date']").val()),
                        payment_distribution: $("select[name='payment_distribution']").val(),
                        description: $("textarea[name='description']").val(),
                        cvc: $("input[name='cvc']").val(),
                        folio_id: $('#current_folio_id').val(),
                        selected_gateway: $('input[name="' + innGrid.featureSettings.selectedPaymentGateway + '_use_gateway"]').data('gateway_name'),
                        use_gateway: $('input[name="' + innGrid.featureSettings.selectedPaymentGateway + '_use_gateway"]').prop('checked') ? 1 : 0,
                        capture_payment_type: capture_payment_type
                    }, function(data) {
                        data = JSON.parse(data);
                        if (data.success) {
                            window.location.reload();
                        } else {
                            alert(data.message);
                            $(that).prop("disabled", false);
                        }
                    });
                }
                if (payFor == "this_invoice_only") {
                    $.post(getBaseURL() + 'invoice/insert_payment_AJAX', {
                        booking_id: $("#booking_id").val(),
                        payment_type_id: $("select[name='payment_type_id']").val(),
                        customer_id: $("select[name='customer_id']").val(),
                        payment_amount: $("input[name='payment_amount']").val(),
                        payment_date: innGrid._getBaseFormattedDate($("input[name='payment_date']").val()),
                        description: $("textarea[name='description']").val(),
                        cvc: $("input[name='cvc']").val(),
                        folio_id: $('#current_folio_id').val(),
                        selected_gateway: $('input[name="' + innGrid.featureSettings.selectedPaymentGateway + '_use_gateway"]').data('gateway_name'),
                        capture_payment_type: capture_payment_type
                    }, function(data) {
                        console.log('expire ', data);
                        if (data == "You don't have permission to access this functionality.") {
                            alert(data);
                            $(that).prop("disabled", false);
                            return;
                        }

                        data = JSON.parse(data);
                        if (data.success) {
                            if(innGrid.isEasyposFisicalEnabled = 1){
                                var folioId = $('#current_folio_id').val();

                                var url = getBaseURL() + 'ep_download_invoice_print/'+$("#booking_id").val();

                                url = folioId ? url + '-' + folioId : url;

                                $.ajax({
                                type   : "POST",
                                url    : url,
                                dataType: "json",
                                success: function (response) {
                                console.log('response',response);

                                if(response.status){

                                var url = getBaseURL()+'application/extensions/easypos_fisical_integration/fiscal_invoices/'+response.openurl;

                                // Create a link element
                                var link = document.createElement('a');
                                link.href = url;
                                // var match = response.openurl;
                                link.download = response.openurl; // Change the filename as needed
                                console.log('link',link);
                                // Programmatically click the link to trigger the download
                                link.click();
                                // Clean up
                                // document.body.removeChild(link);
                                }else{
                                alert(l('Some error occured! Please try again.'));
                                }

                                }

                                });
                            }
                            window.location.reload();
                        } else if (data.expire) {
                            window.location.href = getBaseURL() + 'settings/integrations/payment_gateways';
                        } else {
                            var error_html = "";
                            // console.log(jQuery.isArray( data.message ));
                            if (jQuery.isArray(data.message)) {
                                $.each(data.message, function(i, v) {
                                    error_html += v.description + '\n';
                                });
                                console.log(error_html);
                                $('#display-errors').find('.modal-body').html(error_html.replace(/\n/g, '<br/>'));
                                $('#display-errors').modal('show');
                                // alert(error_html);
                            } else {
                                alert(data.message ? data.message : data);
                                location.reload();
                            }


                            $(that).prop("disabled", false);
                        }
                    });
                } else if (payFor == 'all_bookings') {
                    $.post(getBaseURL() + 'customer/insert_payments_AJAX', {
                        bookings: unpaidBookings,
                        payment_type_id: $("select[name='payment_type_id']").val(),
                        customer_id: $("select[name='customer_id']").val(),
                        payment_date: innGrid._getBaseFormattedDate($("input[name='payment_date']").val()),
                        total_balance: totalBalance,
                        description: $("textarea[name='description']").val(),
                        cvc: $("input[name='cvc']").val(),
                        folio_id: $('#current_folio_id').val()
                    }, function(data) {
                        data = JSON.parse(data);
                        if (data.success) {
                            window.location.reload();
                        } else {
                            alert(data.message);
                            $(that).prop("disabled", false);
                        }
                    });
                }
            })
            .fail(function() {
                if($('input[name="current_payment_gateway"]').val() != 'stripe-integration') {
                    alert(l('Gateway transaction unavailable', true));
                    location.reload();
                }
            });

    });

    $('#add-new-folio').on("click", innGrid.addNewFolio);
    $('#folios').on("click", 'ul li a', innGrid.getFolioData);
    $('.remove-folio').on("click", innGrid.removeFolio);

    $(document).on("click", ".btn-update-folio-name", function() {
        var folioName = $(this).parent().parent().find('.updated-folio-name').val();
        var folioId = $(this).parent().parent().find('.update-folio-id').val();
        var bookingId = $("#booking_id").val();
        var customerId = $("#customer_id").val();
        if (folioName == '') {
            folioName = l('Add name', true);
        }
        $.ajax({
            type: "POST",
            url: getBaseURL() + 'invoice/update_folio_AJAX',
            data: { "folio_name": folioName, "folio_id": folioId, "booking_id": bookingId, "customer_id": customerId },
            dataType: "json",
            success: function(data) {
                if (data) {
                    var folioId = data.folio_id;
                    var firstFolioId = data.first_folio_id;
                    if (firstFolioId) {
                        $('#folio-name-').parents('li').data('folio-id', firstFolioId);
                        $('#folio-name-').attr('id', 'folio-name-' + firstFolioId);
                        if (!$('#current_folio_id').val()) {
                            $('#current_folio_id').val(firstFolioId)
                        }
                    }

                    $("#folio-name-" + folioId).html(folioName);
                    $('[data-toggle="popover"]').popover('hide');
                }
            },
            error: function(e) {
                alert(l("You don't have permission to access this functionality.", true));
            }
        });
    });

    $('#folios').on('click', '.folio-name', function(e) {
        e.preventDefault();
        $('.popover.in').prev('.folio-name').popover('hide');
        $(this).popover('show');

        var folio_id = $(this).parent().parent().data('folio-id');
        var folio_name = $(this).text().trim();
        if (folio_name == 'Expedia EVC') {
            $('[data-toggle="popover"]').popover('hide');
        } else {
            $('.popover.in').find(".update-folio-id").val(folio_id);
            $('.popover.in').find(".updated-folio-name").val(folio_name);
        }
        return false;
    });

    $(document).on('click', function(e) {
        if ($('.popover.in').length !== 0 &&
            $(e.target).data('toggle') !== 'popover' &&
            !$(e.target).is('.popover.in') &&
            $(e.target).parents('.popover.in').length === 0
        ) {
            $('[data-toggle="popover"]').popover('hide');
        }
    });

    $('#move-charge-payment').on("click", innGrid.moveChargePayment);

    $('.folios_modal').on("click", innGrid.moveToFolioModal);

    $('.folio-name').popover({
        html: true,
        trigger: 'manual',
        placement: 'right',
        content: function() {
            return $('#popover-update-folio-name').html();
        }
    });

});

function _populateHistoryModal(logs) {
    var $model = $("#invoice-logs-modal").find(".modal-content");
    $model
        .html("")
        .append(
            $("<div/>", {
                class: "modal-header"
            })
            .append(l("Invoice History", true))
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
                    text: l("Close", true)
                })
            )
        );
    var log_amount = '';
    logs.forEach(function(log) {

        if (((log.log).indexOf("Folio Added") != -1) || ((log.log).indexOf("Folio Updated") != -1) || ((log.log).indexOf("Folio Deleted") != -1)) {
            log_amount = '';
        } else {
            log_amount = " ( " + log.new_amount + " ) ";
        }
        $model.find(".modal-body").append(
            $("<div/>", {
                class: "panel panel-default"
            }).append(
                $("<div/>", {
                    class: "panel-body",
                    html: log.date_time + " "+l('by')+" " + (log.user_id == '0' && !log.first_name && !log.last_name && !log.email ? l('System') : (log.user_id == '-1' ? l('Guest') : (log.first_name + " " + log.last_name))) + " - " + log.log + log_amount
                })
            )
        )
    });
    $("#invoice-logs-modal").modal('show');
}

var extraCharges = new Array();
var prevExtraIDArray = new Array();
calculatePOSTotal();

$(document).on('click', '.remove_extra_charge', function() {
    var extraID = $(this).attr('id');

    extraCharges = $.grep(extraCharges, function(e) {
        return e.extra_id != extraID;
    });

    prevExtraIDArray = jQuery.grep(prevExtraIDArray, function(value) {
        return value != extraID;
    });

    $('.extra_div_' + extraID).fadeOut().remove();

    if (extraCharges.length == 0) {
        $('.cart-is-empty').removeClass('hidden');
    }
    calculatePOSTotal();
});

$(document).on('click', '#save-extra-charges', function() {

    $(this).text('Loading');
    var bookingID = $('.inv_booking_id').val();
    var folio_id = $('#current_folio_id').val();

    $.post(getBaseURL() + 'invoice/save_invoice', {
        'booking_id': bookingID,
        'charges': extraCharges,
        'folio_id': folio_id,
        'is_extra_pos': true
    }, function(str) {
        if (str == "You don't have permission to access this functionality.") {
            alert(str);
        }
        window.location.reload();
    });
    calculatePOSTotal();
});


function posItem(extraID, extraName, extraRate, extraChargeTypeID, extraChargeTypeName, itemRate) {
    var extraRow = {};
    var htmlContent = '';
    var qty = $('.extra_qty_' + extraID).val();
    extraRow['description'] = extraName;
    extraRow['amount'] = extraRate;
    extraRow['charge_type_id'] = extraChargeTypeID;
    extraRow['extra_id'] = extraID;
    extraRow['qty'] = qty;

    if (jQuery.inArray(extraID, prevExtraIDArray) == -1) {
        extraCharges.push(extraRow);
        prevExtraIDArray.push(extraID);

        htmlContent += '<div class="col-sm-12 name-rate-div extra_div_' + extraID + '" style="padding-bottom: 10px; border-bottom: 1px solid #eee;box-shadow: 5px 5px 10px #f9f9f9;">' +
            '<h4 class="extra_name_' + extraID + '" data-extra_name="' + extraName + '">' +
            extraName +
            '<a style="color: #f16f3c; margin-left: 15px; cursor: pointer;" class="pull-right remove_extra_charge" id="' + extraID + '"><i class="fa fa-times"></i></a>' +
            '<div class="pull-right form-inline">' +
            '<small>' + l('Qty', true) + ': </small>' +
            '<div class="input-group">' +
            '<input style="width: 50px;height: 30px;" size="1" type="number" name="extra_qty" min="1" value="1" class="form-control extra_qty_' + extraID + '">' +
            '<div class="input-group-btn">' +
            '<button style="padding: 4px 6px;" type="button" class="btn btn-default qty_plus" id="' + extraID + '">' +
            '<i class="fa fa-plus"></i>' +
            '</button>' +
            '<button style="padding: 4px 6px;" type="button" class="btn btn-default qty_minus" id="' + extraID + '">' +
            '<i class="fa fa-minus"></i>' +
            '</button>' +
            '</div>' +
            '</div>&nbsp;' +
            '</div>' +
            '</h4>' +
            '<div class="charge-div" style="padding-top: 10px;">' +
            '<div class="pull-left extra_charge_type_' + extraID + '" id="' + extraChargeTypeID + '" data-charge_name="' + extraChargeTypeName + '">' +
            '<small>' + extraChargeTypeName + '</small>' +
            '</div>' +
            '<div style="margin-right: 35px;" class="pull-right extra_rate_' + extraID + '">' + extraRate + '</div>' +
            '</div>' +
            '</div>';
    } else {
        qty++;
        $('.extra_qty_' + extraID).val(qty);
        $.each(extraCharges, function(key, value) {
            if (value.extra_id == extraID) {
                extraCharges[key]["amount"] = parseFloat(extraCharges[key]["amount"]) + parseFloat(itemRate);
                extraCharges[key]["qty"] = qty;
                return false;
            }
        });
    }

    $('#show-extra-package').append(htmlContent);
    $('.cart-is-empty').addClass('hidden');
    calculatePOSTotal();
}

$(document).on('click', '.qty_plus', function() {

    var extraID = $(this).attr('id');
    var qty = $(this).parents('.input-group').find('.extra_qty_' + extraID).val();

    var extraChargeTypeID = $(this).parents('.name-rate-div').find('.charge-div').find('.extra_charge_type_' + extraID).attr('id');
    var extraChargeTypeName = $(this).parents('.charge-div').find('.extra_charge_type_' + extraID).data('charge_name');
    var extraName = $(this).parents('.name-rate-div').find('.extra_name_' + extraID).data('extra_name');
    var extraRate = $(this).parents('.name-rate-div').find('.extra_rate_' + extraID).text();

    posItem(extraID, extraName, extraRate, extraChargeTypeID, extraChargeTypeName, extraRate);
    $(this).parents('.input-group').find('.extra_qty_' + extraID).val(parseInt(qty) + 1);
    calculatePOSTotal();
});

$(document).on('click', '.qty_minus', function() {

    var extraID = $(this).attr('id');
    var qty = $(this).parents('.input-group').find('.extra_qty_' + extraID).val();
    if (qty > 1) {
        newQty = parseInt(qty) - 1;
        $(this).parents('.input-group').find('.extra_qty_' + extraID).val(newQty);
        extraRate = $(this).parents('.name-rate-div').find('.extra_rate_' + extraID).text();
        // Remove single qty amount
        $.each(extraCharges, function(key, value) {
            if (value.extra_id == extraID) {
                extraCharges[key]["amount"] = parseFloat(extraCharges[key]["amount"]) - parseFloat(extraRate);
                extraCharges[key]["qty"] = newQty;
                return false;
            }
        });
    }
    calculatePOSTotal();
});

function calculatePOSTotal() {
    var total = 0;
    $.each(extraCharges, function(key, value) {
        total += parseFloat(value.amount);
    });
    $('.pos-total').text(number_format(total, 2));
}

