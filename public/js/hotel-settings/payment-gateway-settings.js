var settings;
innGrid.ajaxCache = innGrid.ajaxCache || {};

innGrid._getStripeForm = function() {
    return $("<div/>", {})
        .append(innGrid._getHorizontalInput("Publishable Key", "stripe_publishable_key", settings.stripe.stripe_publishable_key))
        .append(innGrid._getHorizontalInput("Secret Key", "stripe_secret_key", settings.stripe.stripe_secret_key));
};
innGrid._getPaymentGatewayForm = function() {
    return $("<div/>", {})
        .append(innGrid._getHorizontalInput("Login", "gateway_login", settings.payment_gateway.gateway_login))
        .append(innGrid._getHorizontalInput("Password", "gateway_password", settings.payment_gateway.gateway_password));
};

innGrid._getChaseNetConnectGatewayForm = function() {
    return $("<div/>", {})
        .append(innGrid._getHorizontalInput("Login", "gateway_login", settings.payment_gateway.gateway_login))
        .append(innGrid._getHorizontalInput("Password", "gateway_password", settings.payment_gateway.gateway_password))
        .append(innGrid._getHorizontalInput("MID (12-digit ID)", "gateway_mid", settings.payment_gateway.gateway_mid))
        .append(innGrid._getHorizontalInput("TID (1 to 3 digit ID)", "gateway_tid", settings.payment_gateway.gateway_tid))
        .append(innGrid._getHorizontalInput("CID (4-digit ID)", "gateway_cid", settings.payment_gateway.gateway_cid));
};

innGrid._getFirstDataE4GatewayForm = function() {
    return $("<div/>", {})
        .append(innGrid._getHorizontalInput("Login (Gateway ID)", "gateway_login", settings.payment_gateway.gateway_login))
        .append(innGrid._getHorizontalInput("Password", "gateway_password", settings.payment_gateway.gateway_password))
};

innGrid._getAuthorizeNetGatewayForm = function() {
    return $("<div/>", {})
        .append(innGrid._getHorizontalInput("Login (API Login)", "gateway_login", settings.payment_gateway.gateway_login))
        .append(innGrid._getHorizontalInput("Password (Transaction Key)", "gateway_password", settings.payment_gateway.gateway_password))
};

innGrid._getPayuGatewayForm = function() {
    return $("<div/>", {})
        .append(innGrid._getHorizontalInput(l("Login"), "gateway_login", settings.payment_gateway.gateway_login))
        .append(innGrid._getHorizontalInput(l("Password"), "gateway_password", settings.payment_gateway.gateway_password))
        .append(innGrid._getHorizontalInput(l("App Id"), "gateway_app_id", settings.payment_gateway.gateway_app_id))
        .append(innGrid._getHorizontalInput(l("Private Key"), "gateway_private_key", settings.payment_gateway.gateway_private_key))
        .append(innGrid._getHorizontalInput(l("Public Key"), "gateway_public_key", settings.payment_gateway.gateway_public_key))
};

innGrid._getQuickbooksGatewayForm = function() {
    return $("<div/>", {})
};

innGrid._getElavonGatewayForm = function() {
    return $("<div/>", {})
        .append(innGrid._getHorizontalInput(l("SSL Merchant ID"), "gateway_login", settings.payment_gateway.gateway_login))
        .append(innGrid._getHorizontalInput(l("User ID"), "gateway_user", settings.payment_gateway.gateway_user))
        .append(innGrid._getHorizontalInput(l("PIN"), "gateway_password", settings.payment_gateway.gateway_password))
};

innGrid._getMonerisGatewayForm = function() {
    return $("<div/>", {})
        .append(innGrid._getHorizontalInput(l("Login") + "  (" + l("Store ID") + ")", "gateway_login", settings.payment_gateway.gateway_login))
        .append(innGrid._getHorizontalInput(l("Password") + " (" + l("API Token") +")", "gateway_password", settings.payment_gateway.gateway_password))
};

innGrid._getCieloGatewayForm = function() {
    return $("<div/>", {})
        .append(innGrid._getHorizontalInput(l("Merchant ID"), "gateway_merchant_id", settings.payment_gateway && settings.payment_gateway.gateway_merchant_id ? settings.payment_gateway.gateway_merchant_id : ''))
        .append(innGrid._getHorizontalInput(l("Merchant Key"), "gateway_merchant_key", settings.payment_gateway && settings.payment_gateway.gateway_merchant_key ? settings.payment_gateway.gateway_merchant_key : ''))
};

innGrid._getHorizontalInput = function (label, name, value)
{
    if( 
            name == 'gateway_password' || 
            name == 'gateway_private_key' || 
            name == 'gateway_public_key' || 
            name == 'stripe_publishable_key' || 
            name == 'stripe_secret_key' || 
            name == 'gateway_mid' || 
            name == 'gateway_tid' || 
            name == 'gateway_cid' || 
            name == 'gateway_merchant_id' || 
            name == 'gateway_merchant_key'
        )
    {
        var sensitiveData = '<a title = "Show '+label+'" class="show_password" href="javascript:"><i class="fa fa-eye" ></i></a>';
    }
        
    return $("<div/>", {
        class: "form-group form-group-sm show_field"
    }).append(
        $("<label/>", {
            for: name,
            class: "col-sm-3 control-label",
            text: label
        })
    ).append(
        sensitiveData
    ).append(
        $("<div/>", {
            class: "col-sm-8"
        }).append(
            $("<input/>", {
                class: "form-control sensitive_field",
                name: name,
                value: value,
                type: (name == 'gateway_login' || name == 'gateway_app_id' || name == 'gateway_user') ? "text" : "password"
            })
        )
    )
};

$('body').on('click', '.show_password', function(){
    $(this).parents('.show_field').find('.sensitive_field').attr('type','text');
    var thats = $(this);
    setTimeout( function(){
        thats.parents('.show_field').find('.sensitive_field').attr('type','password'); 
    }, 3000);
});

innGrid._updatePaymentGatewayForm = function(selected_payment_gateway) {
    if (selected_payment_gateway === '') {
        $("#form-div").html('');
        $("#update-button").text(l("Update", true));
    }
    else if (selected_payment_gateway === 'stripe') {
        $("#form-div").html(innGrid._getStripeForm());
        $("#update-button").text(l("Update", true));
    }
    else if (selected_payment_gateway === 'PayflowGateway') {
        $("#form-div").html(innGrid._getPaymentGatewayForm());
        $("#update-button").text(l("Update", true));
    }
    else if (selected_payment_gateway === 'FirstdataE4Gateway') {
        $("#form-div").html(innGrid._getFirstDataE4GatewayForm());
        $("#update-button").text(l("Update", true));
    }
    else if (selected_payment_gateway === 'ChaseNetConnectGateway') {
        $("#form-div").html(innGrid._getChaseNetConnectGatewayForm());
        $("#update-button").text(l("Update", true));
    }
    else if (selected_payment_gateway === 'AuthorizeNetGateway') {
        $("#form-div").html(innGrid._getAuthorizeNetGatewayForm());
        $("#update-button").text(l("Update", true));
    }
    else if (selected_payment_gateway === 'PayuGateway') {
        $("#form-div").html(innGrid._getPayuGatewayForm());
        $("#update-button").text(l("Update", true));
    }
    else if (selected_payment_gateway === 'QuickbooksGateway') {
        $("#form-div").html(innGrid._getQuickbooksGatewayForm());
        $("#update-button").text(l("Login with your QuickBooks Payments Account", true));
    }
    else if (selected_payment_gateway === 'ElavonGateway') {
        $("#form-div").html(innGrid._getElavonGatewayForm());
        $("#update-button").text(l("Update", true));
    }
    else if (selected_payment_gateway === 'MonerisGateway') {
        $("#form-div").html(innGrid._getMonerisGatewayForm());
        $("#update-button").text(l("Update", true));
    }
    else if (selected_payment_gateway === 'CieloGateway') {
        $("#form-div").html(innGrid._getCieloGatewayForm());
        $("#update-button").text(l("Update", true));
    }
};

$(function (){

    var gatewayTypes = {
        'None selected': '',
        'Stripe': 'stripe',
        'PayPal Payflow Pro': 'PayflowGateway',
        'FirstData Gateway e4(Payeezy)': 'FirstdataE4Gateway',
        'Chase Payment Gateway': 'ChaseNetConnectGateway',
        'Authorize.Net': 'AuthorizeNetGateway',
        'Payu Gateway': 'PayuGateway',
        'Quickbooks Gateway': 'QuickbooksGateway',
        'Elavon My Virtual Merchant': 'ElavonGateway',
        'Moneris eSelect Plus': 'MonerisGateway'
        ,'Cielo Gateway': 'CieloGateway'
    };
    
    // load saved payment gateway settings data
    if(!innGrid.ajaxCache.paymentGatewaySettings)
    {
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: getBaseURL() + 'settings/accounting/get_payment_gateway_settings/',
            success: function( data ) {
                settings = data;
                innGrid.ajaxCache.paymentGatewaySettings = data;
                for (var key in gatewayTypes) {
                    var option = $("<option/>", {
                                    value: gatewayTypes[key],
                                    text: key
                                });

                    $("[name='payment_gateway']").append(option);

                    if (data.selected_payment_gateway == gatewayTypes[key]) {
                        option.prop('selected', true);
                    }
                }

                $gateway = $("select[name='payment_gateway']");
                $gateway.change(function () {
                    var selected_payment_gateway = $gateway.val();
                    innGrid._updatePaymentGatewayForm(selected_payment_gateway);
                });

                $gateway.trigger("change");
            }

        });
    }
    else
    {
        settings = data = innGrid.ajaxCache.paymentGatewaySettings;
        for (var key in gatewayTypes) {
            var option = $("<option/>", {
                            value: gatewayTypes[key],
                            text: key
                        });

            $("[name='payment_gateway']").append(option);

            if (data.selected_payment_gateway == gatewayTypes[key]) {
                option.prop('selected', true);
            }
        }

        $gateway = $("select[name='payment_gateway']");
        $gateway.change(function () {
            var selected_payment_gateway = $gateway.val();
            innGrid._updatePaymentGatewayForm(selected_payment_gateway);
        });

        $gateway.trigger("change");
    }
    
    $("#update-button").on("click", function () {
        var valid = false;
        var selected_payment_gateway = $("select[name='payment_gateway']").val();
        var fields = {};
        fields['selected_payment_gateway'] = selected_payment_gateway;
        //for each vars, push
        $("#form-div input").each(function() {
            fields[$(this).attr("name")] = $(this).val();
        });

        switch (selected_payment_gateway) {
            default:
                valid = true;
                break;
            case 'stripe':
                if (fields['stripe_publishable_key'] != '' && fields['stripe_secret_key'] != '') {
                    valid = true;
                } else {
                    alert(l('Please fill all fields'));
                }
                break;
            case 'PayflowGateway':
                if (fields['gateway_login'] != '' && fields['gateway_password'] != '') {
                    valid = true;
                } else {
                    alert(l('Please fill all fields'));
                }
                break;
            case 'FirstdataE4Gateway':
                if (fields['gateway_login'] != '' && fields['gateway_password'] != '') {
                    valid = true;
                } else {
                    alert(l('Please fill all fields'));
                }
                break;
            case 'ChaseNetConnectGateway':
                if (fields['gateway_login'] != '' && fields['gateway_password'] != '' && fields['gateway_mid'] != '' && fields['gateway_tid'] != '' && fields['gateway_cid'] != '') {
                    valid = true;
                } else {
                    alert(l('Please fill all fields'));
                }
                break;
            case 'AuthorizeNetGateway':
                if (fields['gateway_login'] != '' && fields['gateway_password'] != '') {
                    valid = true;
                } else {
                    alert(l('Please fill all fields'));
                }
            case 'PayuGateway':
                if (fields['gateway_login'] != '' && fields['gateway_password'] != '' && fields['gateway_private_key'] != '' && fields['gateway_public_key'] != '' && fields['gateway_app_id'] != '') {
                    valid = true;
                } else {
                    alert(l('Please fill all fields'));
                }
                break;
            case 'ElavonGateway':
                if (fields['gateway_login'] != '' && fields['gateway_password'] != '' && fields['gateway_user'] != '') {
                    valid = true;
                } else {
                    alert(l('Please fill all fields'));
                }
                break;
            case 'MonerisGateway':
                if (fields['gateway_login'] != '' && fields['gateway_password'] != '') {
                    valid = true;
                } else {
                    alert(l('Please fill all fields'));
                }
                break;
            case 'CieloGateway':
                if (fields['gateway_merchant_id'] != '' && fields['gateway_merchant_key'] != '') {
                    valid = true;
                } else {
                    alert(l('Please fill all fields'));
                }
                break;
        }

        if (valid) {
            $.ajax({
                type    : "POST",
                dataType: 'json',
                url     : getBaseURL() + 'settings/accounting/update_payment_gateway_settings/',
                data: fields,
                success: function( data ) {
                    if(data.authorizationCodeUrl)
                    {
                        var url = data.authorizationCodeUrl.replace(/\"/g, "");
                        url = url.replace(/\/+/g, '');
                        window.location.href = url;
                    }
                    else
                        alert(l("Settings updated."));
                }
            });
        }


    }); //--update on click


});
    