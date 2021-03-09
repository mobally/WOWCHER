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
    'uiComponent',
    'jquery',
    'mage/url',
    'Magento_Customer/js/customer-data',
], function (Component, $, url, customerData) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'FME_ExtendedMiniCart/summary/item/details'
        },
        /**
         * @param {Object} quoteItem
         * @return {String}
         */
        getValue: function (quoteItem) {
            return quoteItem.name;
        },
        getDataPost: function(itemid, itemqty) {
            var data = {
                item_id:itemid,
                item_qty:itemqty + 1,
                'form_key': $.mage.cookies.get('form_key')
            }
            $.ajax({
                url: url.build('checkout/sidebar/updateItemQty'),
                data: data,
                type: 'post',
                dataType: 'json',
                context: this,
            }).done(function (response) {
                window.location.reload();
            })
            .fail(function (error) {
                console.log(JSON.stringify(error));
            });
        },
        getDataPostDecrease: function(itemid, itemqty) {
            var data = {
                item_id:itemid,
                item_qty:itemqty - 1,
                'form_key': $.mage.cookies.get('form_key')
            }
            $.ajax({
                url: url.build('checkout/sidebar/updateItemQty'),
                data: data,
                type: 'post',
                dataType: 'json',
                context: this,
            }).done(function (response) {
                window.location.reload();
            })
            .fail(function (error) {
                console.log(JSON.stringify(error));
            });
        },
        deleteItem: function(itemid) {
            var data = {
                item_id:itemid,
                'form_key': $.mage.cookies.get('form_key')
            }
            $.ajax({
                url: url.build('checkout/sidebar/removeItem'),
                data: data,
                type: 'post',
                dataType: 'json',
                context: this,
            }).done(function (response) {
                window.location.reload();
            })
            .fail(function (error) {
                console.log(JSON.stringify(error));
            });
        },
        getConfigDefault: function(){
            if (window.checkoutConfig.myCustomData == 1) {
                return window.checkoutConfig.myCustomData;
            } else {
                return 0;
            }
        },
        getCustomQtyUpdate: function(){
            //console.log(window.checkoutConfig.showCustomQtyUpdate);
            if (window.checkoutConfig.showCustomQtyUpdate == 1) {
                return window.checkoutConfig.showCustomQtyUpdate;
            } else {
                return 0;
            }
        }
    });
});
