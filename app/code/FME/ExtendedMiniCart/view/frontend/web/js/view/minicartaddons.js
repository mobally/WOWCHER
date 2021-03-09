/**
* FME Extensions
*
* NOTICE OF LICENSE 
*
* This source file is subject to the fmeextensions.com license that is
* available through the world-wide-web at this URL:
* https://www.fmeextensions.com/LICENSE.txt
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this extension to newer
* version in the future.
*
* @category FME
* @package FME_ExtendedMiniCart
* @copyright Copyright (c) 2019 FME (http://fmeextensions.com/)
* @license https://fmeextensions.com/LICENSE.txt
*/

define([
       'ko',
       'uiComponent',
       'Magento_Customer/js/customer-data',
       'Magento_Catalog/js/price-utils'
   ], function (ko, Component, customerData, priceUtils) {
       'use strict';
       var subtotalAmount;
       var maxPrice = 100;
       var percentage;

       return Component.extend({
           displaySubtotal: ko.observable(true),
           maxprice: '$' + maxPrice.toFixed(2),
           /**
            * @override
            */
           initialize: function () {
               this._super();
               this.cart = customerData.get('cart');
           },
           getTotalCartItems: function () {
               return customerData.get('cart')().summary_count;
           },
           getCartTotal: function () {
               return customerData.get('cart')().subtotal;
           },
           getCartItems: function () {
              return customerData.get('cart')().items;
           },
           getCustomReleated: function(){
            /*if (typeof customerData.get('cart')().items[0].showcustomreleated !== 'undefined') {
              if (customerData.get('cart')().items[0].showcustomreleated == 1) {
                return 1;
              } else {
                return 0;
              }
            } else {
              return 0;
            }*/
              if (typeof customerData.get('cart')().items !== 'undefined') {
                for (var i = 0; i < customerData.get('cart')().items.length; i++) {
                  if (typeof customerData.get('cart')().items[i].showcustomreleated !== 'undefined') {
                    if (customerData.get('cart')().items[i].showcustomreleated == 1) {
                        return 1;
                    } else {
                        return 0;
                    }
                  } else {
                    return 0;
                  }
                }
              } else {
                return 0;
              }
           },
           getCustomSummary: function(){
              //console.log(customerData.get('cart')().items);
              if (typeof customerData.get('cart')().items !== 'undefined') {
                for (var i = 0; i < customerData.get('cart')().items.length; i++) {
                  if (typeof customerData.get('cart')().items[i].showcustomsummary !== 'undefined') {
                    if (customerData.get('cart')().items[i].showcustomsummary == 1) {
                        return 1;
                    } else {
                        return 0;
                    }
                  } else {
                    return 0;
                  }
                }
              } else {
                return 0;
              }
           },
           getAmmount: function () {
              var price = 0.00;
              return priceUtils.formatPrice(price);
           },
           getpercentage: function () {
               subtotalAmount = customerData.get('cart')().subtotalAmount;
               if (subtotalAmount > maxPrice) {
                   subtotalAmount = maxPrice;
               }
               percentage = ((subtotalAmount * 100) / maxPrice);
               return percentage;
           }
           //console.log(enableMiniCart);
       });

   });