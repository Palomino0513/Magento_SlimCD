define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
  ], function (Component, rendererList) { 
         'use strict';
          rendererList.push({ 
              type: 'slimcdpayment', 
              component: 'Slimcd_Payment/js/view/payment/method-renderer/payment' });
            return Component.extend({});
      }
    );