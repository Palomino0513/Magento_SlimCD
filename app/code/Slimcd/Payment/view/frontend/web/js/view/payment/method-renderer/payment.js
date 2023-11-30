/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    "Magento_Checkout/js/view/payment/default",
    "jquery",
    "Magento_Checkout/js/checkout-data",
    "mage/url",
    "Magento_Checkout/js/model/url-builder",
], function (
    Component,
    $,    
    checkoutData,
    url,
    urlBuilder,
) {
    "use strict";
    return Component.extend({
        defaults: {
            redirectAfterPlaceOrder: false,
            template: "Slimcd_Payment/payment/payment",
            checkconvience: setTimeout(function () {
                checkForSurchargeConvience();
            }, 1000),
        },
        getCode: function () {
            return "slimcdpayment";
        },
        isActive: function () {
            return true;
        },
        getdescription: function () {
            return window.checkoutConfig.payment.slimcdpayment.description;
        },
        getcheckcode: function () {
            return window.checkoutConfig.payment.slimcdpayment.slimcd_check
                .form_name_for_check;
        },
        isslimcheck: function () {
            if (
                window.checkoutConfig.payment.slimcdpayment.slimcd_check
                    .form_name_for_check != null
            )
                return "block";
            else return "none";
        },
        isslimdis: function () {
            if (
                window.checkoutConfig.payment.slimcdpayment.slimcd_check
                    .form_name_for_check != null
            )
                return "none";
            else return "block";
        },
        getcardcode: function () {
            return window.checkoutConfig.payment.slimcdpayment.slimcd_card
                .form_name_for_card;
        },
        getcardid: function () {
            return "slimcdcardid";
        },
        getcheckid: function () {
            return "slimcdcheckid";
        },
        getcardvalue: function () {
            return "Credit Card";
        },
        getcheckvalue: function () {
            return "Checking Account";
        },
        afterPlaceOrder: function () {
            var checkform =  window.checkoutConfig.payment.slimcdpayment.slimcd_card.form_name_for_card;
            var receiptlabel = $("#fee_title").val();
            var customer_note_slim = $("#customer_note_slim").val();
            if ( $('input[name="payment_slimoption"]:checked').val() == "slimcdcheck") {
                checkform = window.checkoutConfig.payment.slimcdpayment.slimcd_check.form_name_for_check;
            }
            window.location.replace(url.build('payment/index/orderplace?seloption='+checkform+"&receiptlabel="+receiptlabel+"&customernoteslim="+customer_note_slim));
			},
            
        selectPaymentOption: function () {
            if (
                $('input[name="payment_slimoption"]:checked').val() ==
                "slimcdcard"
            ) {
                $("#slimcdcardid").attr("checked", true);
                $("#slimcdcheckid").attr("checked", false);
                if ($("#sur_fee").val() > "0") {
                    $("#desc_slimoption").text(
                        window.checkoutConfig.payment.slimcdpayment.slimcd_card.card_sur_dis
                    );
                } else if ($("#sur_fee").val() == "") {
                    $("#desc_slimoption").text(
                        window.checkoutConfig.payment.slimcdpayment.slimcd_card.card_fee_dis
                    );
                }
            } else {
                $("#slimcdcardid").attr("checked", false);
                $("#slimcdcheckid").attr("checked", true);
                if ($("#sur_fee").val() == "") {
                    $("#desc_slimoption").text(
                        window.checkoutConfig.payment.slimcdpayment.slimcd_check.check_fee_dis
                    );
                }
                else{
                    $("#desc_slimoption").text('');
                } 
            }
        },
        selectPaymentOptiondescription: function () {
            return '';//window.checkoutConfig.payment.slimcdpayment.slimcd_card.card_fee_dis;
        },
        isPlaceOrderActionAllowed: function () {

            var configData = window.checkoutConfig.payment.slimcdpayment;
            var checkform = configData.slimcd_card.form_name_for_card;
            if ( $('input[name="payment_slimoption"]:checked').val() == "slimcdcheck") {
                checkform = configData.slimcd_check.form_name_for_check;
            }
            if(checkform == null || checkform == ""){
                //alert(checkform);
                $("#error_slimcd").text("Please Check the Configuration");
                return false;
            }
            else if (configData.slimcd_API.api_username == "" || configData.slimcd_API.api_username == null) {
                $("#error_slimcd").text("Please Check the Configuration");
                return false;
            }
            else if (window.checkoutConfig.totalsData.base_currency_code != "USD" && window.checkoutConfig.totalsData.base_currency_code != "CAD") {
                $("#error_slimcd").text("Payment allowed currency USD and CAD only");
                return false;
            } else {
                return true;
            }
        },

        // placeOrder: function (data, event) {
        //     if (event) {
        //         event.preventDefault();
        //     }
        //     placeOrder = placeOrderAction(
        //         this.getData(),
        //         this.messageContainer
        //     );
        // },
    });
    function checkForSurchargeConvience() {
        var configData = window.checkoutConfig.payment.slimcdpayment;
        var sendInfo = {
            // 'username' : 'RGWA7QCR',//configData.slimcd_API.api_username,
            //'password' : ''//configData.slimcd_API.password,
            username: configData.slimcd_API.api_username,
            password: configData.slimcd_API.password,
        };
        
        $.ajax({
            type: "POST",
            url:"https://stats.slimcd.com/soft/json/jsonscript.asp?service=GetUserClientSite3",
            dataType: "json",
            data: sendInfo,
            success: function (msg) {
                if (msg) {                    
                    var returnedData = msg.reply;
                    if (returnedData.response == "Success") {
                        if (returnedData.datablock.SiteList.Site
                                .surcharge_percentage > 0
                        ) {
                            $("#desc_slimoption").text(window.checkoutConfig.payment.slimcdpayment.slimcd_card.card_sur_dis);
                            $("#desc_check").text(window.checkoutConfig.payment.slimcdpayment.slimcd_card.card_sur_dis);
                            $("#fee_title").val('Surcharge');
                            $('#sur_fee').val(returnedData.datablock.SiteList.Site.surcharge_percentage);
                            return returnedData.datablock.SiteList.Site.conveniencefee_receiptlabel;
                        } else if (returnedData.datablock.SiteList.Site.conveniencefee_enabled == "True"
                        ) {
                            $("#fee_title").val(returnedData.datablock.SiteList.Site.conveniencefee_receiptlabel);
                            $("#desc_check").text(window.checkoutConfig.payment.slimcdpayment.slimcd_card.card_fee_dis);
                            $("#desc_slimoption").text(window.checkoutConfig.payment.slimcdpayment.slimcd_card.card_fee_dis);
                            $('#sur_fee').val('');
                            return returnedData.datablock.SiteList.Site.conveniencefee_receiptlabel;
                        } else {
                            $("#desc_slimoption").css("display", "none");
                            return false;
                        }
                    } else
                        messageList.addErrorMessage({
                            message: $t(returnedData.description),
                        });
                    return false;
                } else {
                    messageList.addErrorMessage({
                        message: $t(msg.reply.description),
                    });
                }
            },
        });
    }    
});
