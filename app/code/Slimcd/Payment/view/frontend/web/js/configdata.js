define([
    'Magento_Checkout/js/view/payment/default',
    'jquery',    
    'mage/url',
], function ($,url) {
    'use strict';
    
        var serviceUrl = url.build('payment/index/storeconfig');
        $.ajax({
            url: serviceUrl,
            type: 'GET',
            success: function (response) {  
                if(response.success) {
                    return response.data;
                } else {
                    return '';
                }
            },
        })
    
})