/*  Plugin for Booking Modals
 *   It takes the element's id attr, and use it as bookingID
 */
(function ($) {
    // initialize
    
    $("body").append(
            $("<div/>", {
                class: "modal fade",
                id: "card-modal",
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

     $("#group-search-model").modal({show: false, backdrop: 'static', keyboard: false});
     
    // eventually, add an option to enter check-in & check-out date.
    var CardModal = function (options) {
        var that = this;
        this.deferredCustomerId = $.Deferred();
        
        var defaults = {
            customer_name: '',
            customer_id: '',
            onload: function () {
            },
            onclose: function () {
            }
        };

        options = $.extend({}, defaults, options);
        if(options.key_data == "delete" && options.customer_id){
            
            $.ajax({
                type: "POST",
                url: getBaseURL() + "customer/delete_customer_card_AJAX",
                data: {
                    customer_id: options.customer_id,
                    card_id: options.cus_card_id,
                    card_token: options.cus_card_token,
                    booking_id: options.booking_id
                },
                dataType: "json",
                success: function (del_data) {
                    if(del_data){
                       $("#card_div_b_"+options.cus_card_id).remove();
                       $("#card_div_sm_"+options.cus_card_id).remove();
                       $('#pay_details_tab').click();
                       $('#payment_details').click();
                    }
                }
            });
        }
        else if (options.key_data == "new" && options.customer_id) {
            $.ajax({
                type: "POST",
                url: getBaseURL() + "customer/get_customer_AJAX",
                data: {
                    customer_id: options.customer_id
                },
                dataType: "json",
                success: function (customer_data) {
                    if(customer_data != null){
                        var customer_card_data = {
                            customer_id: "",
                            customer_name: "",
                            cc_number: "",
                            cc_expiry_month: "",
                            cc_expiry_year: "",
                            cc_tokenex_token: "",
                            cc_cvc_encrypted: ""
                         };
                         var new_customer_data = {
                            customer_id: customer_data.customer_id,
                            customer_name: customer_data.customer_name,
                            card_name: "",
                            cc_number: "",
                            cc_expiry_month: "",
                            cc_expiry_year: "",
                            cc_tokenex_token: "",
                            cc_cvc_encrypted: ""
                         }
                        that._initializeCardModal(new_customer_data, customer_card_data, options.booking_id);
                    }
                }
             });
        }
        else if(options.key_data == "update" && options.customer_id){
               $.ajax({
                    type: "POST",
                    url: getBaseURL() + "customer/get_customer_card_AJAX_by_Id",
                    data: {
                        customer_id: options.customer_id,
                        customer_card_id: options.cus_card_id
                    },
                    dataType: "json",
                    success: function (customer_card_data) {
                        if(customer_card_data != null){
                        var customer_data = {
                            customer_id: "",
                            cc_number: "",
                            cc_expiry_month: "",
                            cc_expiry_year: "",
                            cc_tokenex_token: "",
                            cc_cvc_encrypted: ""
                         };
                            that._initializeCardModal(customer_data, customer_card_data[0], options.booking_id);
                            $("#card_name").attr('disabled', 'disabled');
                            $("#cc_number").attr('disabled', 'disabled');
                        }else{
                                $.ajax({
                                    type: "POST",
                                    url: getBaseURL() + "customer/get_customer_card_AJAX_by_Id_customer_table",
                                    data: {
                                        customer_id: options.customer_id,
                                        // customer_card_id: options.cus_card_id
                                    },
                                    dataType: "json",
                                    success: function (customer_card_data) {
                                        if(customer_card_data != null){
                                        var customer_data = {
                                            customer_id: "",
                                            cc_number: "",
                                            cc_expiry_month: "",
                                            cc_expiry_year: "",
                                            cc_tokenex_token: "",
                                            cc_cvc_encrypted: ""
                                        };
                                            that._initializeCardModal(customer_data, customer_card_data[0], options.booking_id);
                                        }
                                    }
                                 });
                        }
                    }
                });
        }
        //that._initializeCardModal(customer_array);
    };
    
    CardModal.prototype = {
        _init: function () {
          
        },
        _initializeCardModal: function (customer_data, customer_card_data, booking_id) {
            var that = this;
           // re-initialize by deleting the existing modal
           $("#card-modal").modal('show');
           $("#card-modal").find(".modal-content").html("");
           
           $("#card-modal").on('hidden.bs.modal', function () {
               // hack to prevent closing inner-modal removing modal-open class in body.
               // when modal-open class is removed from body, scrolling the customer-modal scrolls
               // background, instead of scrolling the modal

               if (($("#booking-modal").data('bs.modal') || {}).isShown)
                   $("body").addClass("modal-open");
           })
            this._populateCardModel(customer_data, customer_card_data, booking_id);
        },
        
        _populateCardModel: function(customer_data, customer_card_data, booking_id){
            var that = this;
            var logs = (customer_card_data.cc_number) ? customer_card_data : customer_data;
            if (logs.customer_id) // existing customer
            {
                // initializing cc_expiry month and year, to '', otherwise, expiry field shows NaN
                if (
                        (
                            typeof logs.cc_expiry_month === 'undefined' &&
                            typeof logs.cc_expiry_year === 'undefined'
                        ) ||
                        (
                            logs.cc_expiry_month === null &&
                            logs.cc_expiry_year === null
                            ) ||
                        (
                            logs.cc_expiry_month === '' &&
                            logs.cc_expiry_year === ''
                        )
                        ) {
                    var cc_expiry = '';
                }
                else {
                    var cc_expiry = logs.cc_expiry_month + " / " + logs.cc_expiry_year;
                }
            }
            var card_data = "";
            if(isTokenizationEnabled == true)
            {
                var sensitiveCardNumber = '';//logs.cc_number ? '<a style="position: absolute; right: 26px; top: 7px; z-index: 9999;" title = "Show Card Number" class="show_pay_details_cc" data-cc_detail="card_number" data-cc_number="'+logs.cc_number+'" href="javascript:"><i class="fa fa-eye" ></i></a><input type="hidden" id="customer_id" data-cc_token="'+logs.cc_tokenex_token+'" data-cc_cvc="'+logs.cc_cvc_encrypted+'" value="'+logs.customer_id+'"/>' : '';
                var sensitiveCardCVC = '';//logs.cc_cvc_encrypted ? '<a style="position: absolute; right: 120px; top: 7px; z-index: 9999;" title = "Show Card CVC" class="show_pay_details_cc" data-cc_detail="card_cvc" data-cc_number="'+logs.cc_number+'" href="javascript:"><i class="fa fa-eye" ></i></a>' : '';
                    card_data = 
                    $("<div/>", {
                        class: "form-group"
                    })
                    .append($("<label/>", {
                        for : "credit_card",
                        class: "col-sm-3 control-label credit_card_lable",
                        text: l("Credit Card")+" *"
                    })
                            ).append(
                    $("<div/>", {
                        class: "col-sm-6 iframe_div"
                    })
                                    .append(
                                        // $("<iframe/>", {
                                        //     id: "credit_card_iframe_card_modal",
                                        //     style: "width: 100%;height: 35px;border: none;",
                                        //     scrolling: "no",
                                        //     frameborder: "0"
                                        // })
                                        $("<input/>", { // a workaround to disable autocomplete for email and cvv
                                            class: "form-control cc_number",
                                            name: "cc_number",
                                            id: "cc_number",
                                            type: 'text',
                                            value: logs.cc_number
                                        })
                    ).append(sensitiveCardNumber)
                    .append($('<span/>', {
                        id: "masked-card-number-label",
                        style: "position: absolute;top: 0;left: 15px;background: white;max-width: 90%;padding: 8px;",
                        class: "credit_card_number form-control "+(logs.cc_number ? "" : "hidden"),
                        text: logs.cc_number
                    })
                        .on('click', function(){
                            $(this).hide();
                            $('#credit_card_iframe_card_modal')[0].contentWindow.postMessage('focus', '*');
                        })
                    )
                    .append($('<img/>', {
                        id: "card-image-card-modal",
                        style: "position: absolute; top: 3px; right: 18px; width: auto; height: 28px; padding: 0;"
                    }))
                    .append($('<img/>', {
                            id: "detokenize-card",
                            style: "cursor: pointer; position: absolute; top: 3px; right: 18px; width: auto; height: 28px; padding: 0;",
                            class: 'hidden'
                        })
                        .on('click', function(){
                            $.get(getBaseURL() + "customer/detokenize_card",
                                {customer_id: logs.customer_id}, 
                                function(data){
                                    if(data){
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
                                class: "form-control card_info card_exp",
                                name: "cc_expiry",
                                type: "text",
                                placeholder: "MM / YY",
                                value: cc_expiry,
                                autocomplete: false,
                                maxlength: "7"
                            })
                            .payment('formatCardExpiry')
                            )
                            )
                    .append(
                    $("<div/>", {
                        class: "form-group"
                    }).append(
                            $("<input/>", {
                                style: "opacity: 0; width: 1px; height: 1px; margin: 0px; padding: 0px;",
                                type: 'password',
                            })
                        ).append(
                            $("<div/>", {
                                class: "col-sm-6 card_cvc_div"
                            }).append(
                        $("<label/>", {
                            for : "cvc",
                            class: "col-sm-3 control-label cvc_label",
                            text: l('CVC')+" *"
                        })
                        )
                        .append(
                                $("<input/>", {
                                    class: "form-control cus_input card_info card_cvc_no",
                                    name: "cvc",
                                    id: 'cvc',
                                    placeholder: '***',
                                    type: 'password',
                                    maxlength: 4,
                                    autocomplete: false,
                                     value: logs.cc_cvc_encrypted ? "***" : ""
                                })
                            ).append(sensitiveCardCVC)
                        )
                    .append(
                        $("<div/>", {id: "cc_tokenization_status", class: 'col-sm-6 card_cvc_div'}).on("click", function () {
                            alert(l("The customer's credit card has been tokenized. You can charge the customer's credit card in the Invoice page using [Add Payment] button."));
                        })
                    )
                );
            }
            $("#card-modal").find(".modal-content").html(
                                    $("<div/>", {
                                        class: "modal-header"
                                    })
                                    ).append(
                                    $("<div/>", {
                                        class: "modal-body form-horizontal"
                                    }).append(
                                            $("<div/>", {
                                              class: "form-group"
                                    })
                                        .append(
                                        $("<lable/>", {
                                            class: "col-sm-4 guest_lable",
                                            html: l("Guest Name")
                                        })
                                     ).append(
                                        $("<input/>", {
                                            id: "Guest_name",
                                            class: "form-control guest_name col-sm-8",
                                            type: "text",
                                            value: logs.customer_name,
                                            disabled: 'disabled' 
                                        })
                                     ).append(
                                        $("<input/>", {
                                            id: "Guest_id",
                                            class: "form-control guest_id",
                                            type: "hidden",
                                            value: logs.customer_id
                                        })
                                     )).append(
                                            $("<div/>", {
                                                class: "form-group"
                                    })
                                        .append(
                                        $("<lable/>", {
                                            class: "col-sm-4 card_name_lable",
                                            html: l("Card Name")
                                        })
                                     ).append(
                                        $("<input/>", {
                                            id: "card_name",
                                            class: "form-control card_name col-sm-8",
                                            type: "text",
                                            value: logs.card_name
                                        })
                                     )).append(
                                     card_data
                                     )
                                    ).append(
                                    $("<div/>", {
                                        class: "modal-footer"
                                    }).append(
                                         $("<button/>", {
                                            type: "button",
                                            class: "btn save_card",
                                            id:"save_card",
                                            // disabled: "disabled",
                                            html: (customer_card_data.cc_number) ? l("Update") : l("Save")
                                        }).on("click", function () {
                                    var errorMsg = '';       
                                    // if (isTokenizationEnabled == 1 && !$.payment.validateCardExpiry($("input[name='cc_expiry']").payment('cardExpiryVal')) &&
                                    //     $("input[name='cc_expiry']").val() !== '') {
                                    //       errorMsg += "\nInvalid Expiry Date";
                                    // }

                                            // if (errorMsg !== '') {
                                            //     alert(errorMsg);
                                            //     $(this).attr('disabled', false);
                                            //     return;
                                            // }
                                     // $(this).attr('disabled', false);
                                    
                                    var customerData = that._fetchCustomerData();
                                    var update_create_client = function (data) {
                                    data = _.isUndefined(data) ? null : data;
                                    var token = null, cc_tokenex_token = null, cc_cvc_encrypted = null;
                                    if(data && data.success){
                                        customerData.cc_number = "XXXX XXXX XXXX "+data.lastFour;
                                        cc_tokenex_token = data.token;
                                        cc_cvc_encrypted = data.cc_cvc_encrypted;
                                    }
                                    if (logs.cc_number) // new customer
                                    {
                                        $.ajax({
                                            type: "POST",
                                            url: getBaseURL() + "customer/update_customer_card_AJAX",
                                            data: {
                                                card_id: logs.id,
                                                customer_id: logs.customer_id,
                                                customer_data: customerData,
                                                cc_tokenex_token: cc_tokenex_token,
                                                cc_cvc_encrypted: cc_cvc_encrypted,
                                                booking_id : booking_id
                                                
                                            },
                                            dataType: "json",
                                            success: function (data) {
                                                if (data.error && data.error_msg) {
                                                    alert(data.error_msg);
                                                }else {
                                                        $(".token").each(function () {
                                                        if (!$(this).attr('id')) {
                                                            var newCustomerToken = $(this);
                                                            newCustomerToken.find(".token-label").text(customerData.customer_name);
                                                            newCustomerToken.attr("id", data.customer_id);
                                                        }
                                                    });
                                                    // update customer token's name
                                                   // $(document).find("#" + customer.customer_id + ".token").find(".token-label").text(customerData.customer_name);
                                                    $("#card-modal").modal('hide');
                                                    $("#pay_details_tab").click();
                                                }
                                               /// $('#button-update-customer').attr('disabled', false);
                                            }
                                        });
                                    } else {
                                        $.ajax({
                                            type: "POST",
                                            url: getBaseURL() + "customer/insert_card_details",
                                            data: {
                                               
                                                customer_data: customerData,
                                                cc_tokenex_token: cc_tokenex_token,
                                                cc_cvc_encrypted: cc_cvc_encrypted,
                                                booking_id : booking_id
                                                
                                            },
                                            dataType: "json",
                                            success: function (data) {
                                                if (data.error && data.error_msg) {
                                                    alert(data.error_msg);
                                                } else {
                                                        $(".token").each(function () {
                                                        if (!$(this).attr('id')) {
                                                            var newCustomerToken = $(this);
                                                            newCustomerToken.find(".token-label").text(customerData.customer_name);
                                                            newCustomerToken.attr("id", data.customer_id);
                                                        }
                                                    });
                                                    // update customer token's name
                                                   // $(document).find("#" + customer.customer_id + ".token").find(".token-label").text(customerData.customer_name);
                                                    $("#card-modal").modal('hide');
                                                    $("#pay_details_tab").click();
                                                   
                                                   // $("#booking_detail").modal('show');
                                                }
                                               /// $('#button-update-customer').attr('disabled', false);
                                            }
                                        });
                                    }

                                    };   
                                innGrid.deferredCreditCardValidation = $.Deferred();

                                $.when(innGrid.deferredCreditCardValidation)
                                    .then(function(){
                                        // user entered valid card number
                                        innGrid.deferredWaitForTokenization = $.Deferred();
                                       
                                        $('#credit_card_iframe_card_modal')[0].contentWindow.postMessage('tokenize', '*');

                                        $.when(innGrid.deferredWaitForTokenization)
                                            .then(function (data) {
                                                update_create_client(data);
                                            })
                                            .fail(function (message) {
                                                alert(message);
                                                $('#button-update-customer').attr('disabled', false);
                                            });
                                    })
                                    .fail(function(validator){
                                        if(validator == "required")
                                        {
                                            // user not entered card number
                                            update_create_client();
                                            errorMsg = "\nCredit Card Number Can't be Emplty";
                                            alert(errorMsg);
                                            $('#button-update-customer').attr('disabled', false);
                                            return;
                                        }
                                        else if(validator == "invalid")
                                        {
                                            // user entered invalid card number
                                            errorMsg = "\nInvalid Credit Card Number";
                                            alert(errorMsg);
                                            $('#button-update-customer').attr('disabled', false);
                                            return;
                                        }
                                        else
                                        {
                                            alert(validator);
                                            $('#button-update-customer').attr('disabled', false);
                                            return;
                                        }
                                    });
                                            // if(isTokenizationEnabled == 1)
                                            // {
                                            //     // $('#credit_card_iframe_card_modal')[0].contentWindow.postMessage('validate', '*');
                                            // }
                                            // else
                                            // {

                                            update_create_client();

                                // }   
                                    })
                                     )
                                     .append(
                                         $("<button/>", {
                                            type: "button",
                                            class: "btn save_card",
                                            'data-dismiss': "modal",
                                            id:"closecard",
                                            html: l("Close")
                                        })
                                                )
                                    )
            // if(isTokenizationEnabled == 1) // global variable
            // {
            //     $.get(getBaseURL() + "customer/get_credit_card_frame",
            //         {customer_id: logs.customer_id}, 
            //         function(data){
            //             if(data){ console.log("tokan"); console.log(data);
            //                 data = JSON.parse(data);
            //                 if(typeof data.iframe_url !== "undefined"){
            //                     setTimeout(function(){
            //                         $('#credit_card_iframe_card_modal').attr('src', data.iframe_url);
            //                     },500); 
                                
            //                     if (window.addEventListener) {
            //                         addEventListener("message", that._iframe_listener_card, false);
            //                     } else {
            //                         attachEvent("onmessage", that._iframe_listener_card);
            //                     }
            //                 }
            //             }
            //         }
            //     );

            //     $.get(getBaseURL() + "settings/accounting/cc_tokenization_status",
            //         {customer_id: logs.customer_id}, 
            //         function(data){
            //             if(data){
            //                 data = JSON.parse(data);
            //                 if(logs.cc_tokenex_token){
            //                     $('#card-image-card-modal').hide();
            //                     $('#detokenize-card').attr('src', getBaseURL()+'images/cards/eye.png').show();
            //                     data.push('Tokenex');
            //                 }
            //                 if(data.length > 0){
            //                     $('#card-modal #cc_tokenization_status').html('<span class="btn btn-success" style="cursor:help;">'+l("Card Tokenized", true)+' ('+data.join(', ')+')</span>');
            //                 }
            //             }
            //         }
            //     );
            //   //  $("#save_card").prop('disabled', true);
            //     // disable create or update customer button utill iframe loads
               
            // }              
        },
        _iframe_listener_card: function(event){
            var button_text = $('#save_card').text();
            if(button_text == 'Update'){
                $("#save_card").prop('disabled', false);
                $(".card_info").on("keyup", function(){
                    if($('.card_exp').val() && $(".card_cvc_no").val()){
                         $("#save_card").prop('disabled', false);
                    }else{
                        $("#save_card").prop('disabled', true);
                    }
                });
            }else{
                $(".card_info").on("keyup", function(){
                    if($('.card_exp').val() && $(".card_cvc_no").val()){
                         $("#save_card").prop('disabled', false);
                    }else{
                         $("#save_card").prop('disabled', true);
                    }
                });
            }
             
            if (event.origin === 'https://htp.tokenex.com' || event.origin === 'https://test-htp.tokenex.com') {
                var message = JSON.parse(event.data);
                switch (message.event) {
                    case 'load':
                        //$('#save_card').attr('disabled', false);
                        break;
                    case 'focus':
                        $('#credit_card_iframe_card_modal')[0].contentWindow.postMessage('enablePrettyFormat', '*');
                        if(message.data.value)
                        {
                            $('#masked-card-number-label').hide();
                        }
                        break;
                    case 'cardTypeChange':
                        var supportedCardTypes = ["americanExpress", "diners", "discover", "jcb", "masterCard", "visa"];
                        if(message.data.possibleCardType && $.inArray(message.data.possibleCardType, supportedCardTypes) > -1)
                        {
                            $('#card-image-card-modal').attr('src', getBaseURL()+'images/cards/'+message.data.possibleCardType+'.jpg').show();
                        }else{
                            $('#card-image-card-modal').hide();
                        }
                        break;
                    case 'validation':
                        if(!message.data.isValid && message.data.validator == "required") 
                        {
                            $('#masked-card-number-label').show();
                        }
                        
                        if (!message.data.isValid) {
                            //field failed validation
                            if(message.data.validator == "invalid" && innGrid.deferredCreditCardValidation && 
                                    typeof innGrid.deferredCreditCardValidation.resolve === "function") 
                            {
                                innGrid.deferredCreditCardValidation.reject('invalid');
                            } 
                            else if(message.data.validator == "required" && innGrid.deferredCreditCardValidation && 
                                    typeof innGrid.deferredCreditCardValidation.resolve === "function") 
                            {
                                innGrid.deferredCreditCardValidation.reject('required');
                            }
                        } else {
                            //validation valid!
                            if(innGrid.deferredCreditCardValidation && typeof innGrid.deferredCreditCardValidation.resolve === "function") {
                                innGrid.deferredCreditCardValidation.resolve();
                            }
                        }
                        break;
                    case 'post':
                        if (!message.data.success) {
                            // use message.data.error                            
                            innGrid.deferredCreditCardValidation.reject(message.data.error);
                        } else {
                            //get token! message.data.token
                            var cvc = $('input[name="cvc"]').val();
                            if(cvc == "***") {
                                cvc = null; // cvc is already in db
                            }
                            if(cvc){
                                $.ajax({
                                    type: "POST",
                                    url: getBaseURL() + "customer/get_cc_cvc_encrypted",
                                    data: {
                                        token: message.data.token,
                                        cvc: cvc
                                    },
                                    dataType: "json",
                                    success: function (data) {
                                        if(data.success){
                                            message.data.cc_cvc_encrypted = data.cc_cvc_encrypted;
                                            innGrid.deferredWaitForTokenization.resolve(message.data);
                                        }else
                                            innGrid.deferredWaitForTokenization.resolve(message.data);
                                    },
                                    error: function(error){
                                        innGrid.deferredWaitForTokenization.resolve(message.data);
                                    }
                                });     
                            }else{
                                innGrid.deferredWaitForTokenization.resolve(message.data);
                            }
                        }
                        break;
                }
            }
        },
        _fetchCustomerData: function () {

            // fetch general customer data
            var $cardModal = $("#card-modal");
            var customerData = {
                customer_id:  $('#Guest_id').val(),
                customer_name:  $('#Guest_name').val(),
                card_name:  $('#card_name').val(),
                card_number: $('#cc_number').val(),
                cvc: $('#cvc').val()
            };
            if(isTokenizationEnabled == 1)
            {
                customerData['cc_expiry_month'] = $cardModal.find("[name='cc_expiry']").val().substring(0, 2);
                customerData['cc_expiry_year'] = $cardModal.find("[name='cc_expiry']").val().substring(5, 7)
            }

            return customerData;
        },
       
    };
    $.fn.openCardModal = function (options) {
        var body = $("body");
        // preventing against multiple instantiations
        // console.log('options', options);
        $.data(body, 'cardModal',
            new CardModal(options)
        );
    } 


// $('body').on('click', '.show_pay_details_cc', function(){
//
//     var cc_detail = $(this).data('cc_detail');
//     var cc_number = $(this).data('cc_number');
//     var cc_token = $('#customer_id').data('cc_token');
//     var cc_cvc = $('#customer_id').data('cc_cvc');
//
//     $.ajax({
//             type: "POST",
//             url: getBaseURL() + "customer/get_credit_card_number",
//             data: {
//                 cc_token: cc_token,
//                 cc_cvc: cc_cvc,
//                 cc_detail: cc_detail
//             },
//             dataType: "json",
//             success: function (resp) {
//                 if(resp.success == 1)
//                 {
//                     if(cc_detail == 'card_number') {
//                         $('.credit_card_number').text(resp.data);
//                         setTimeout( function(){
//                             $('.credit_card_number').text(cc_number);
//                         }, 10000);
//                     } else if(cc_detail == 'card_cvc') {
//                         $('.card_cvc_no').attr('type','text');
//                         $('.card_cvc_no').val(resp.cvc);
//                         setTimeout( function(){
//                             $('.card_cvc_no').attr('type','password');
//                             $('.card_cvc_no').val('***');
//                         }, 10000);
//                     }
//                 }
//             }
//         });
// });
//

})(jQuery, window, document);
