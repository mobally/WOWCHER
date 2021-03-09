define(
    [
        'uiComponent',
        'jquery',
        'mage/url',
        'Magento_Customer/js/customer-data',
    ],
    function (Component, $, url, customerData) {
        "use strict";

        var quoteItemData = window.checkoutConfig.quoteItemData;
        return Component.extend({
            defaults: {
                template: 'Rvs_CheckoutAttributes/summary/item/details'
            },
            quoteItemData: quoteItemData,

            getValue: function(quoteItem) {
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
        },
            getLeadTime: function(quoteItem) {
                var item = this.getItemProduct(quoteItem.item_id);
                if(item.sku){ 
                    return item.sku; 
                }else{ 
                    return ''; 
                } 
            },
            
            getItemDue: function(quoteItem) {
            var itemProduct = this.getItemProduct(quoteItem.item_id);
            if(itemProduct.due){ 
                    return itemProduct.due; 
                }else{ 
                    return ''; 
                }             
            },

            getItemProduct: function(item_id) {
                var itemElement = null;
                _.each(this.quoteItemData, function(element, index) {
                    if (element.item_id == item_id) {
                        itemElement = element;
                    }
                });
                return itemElement;
            }
        });
    }
);
