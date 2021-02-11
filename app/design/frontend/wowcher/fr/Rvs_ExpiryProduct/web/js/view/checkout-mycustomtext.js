/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */ 
define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/checkout-data',
    'Magento_Customer/js/customer-data',
    'mage/translate'],
function($, ko, Component, quote, checkoutData, customerData, $t){
     return Component.extend({
          defaults: {
            template: 'Rvs_ExpiryProduct/sales/checkout/mycustomtext'
        },
        getCustomText: function (){         
            var customText = $t('Le prix inclus la TVA et droits de douanes');
            return customText;
        }
     });
});
