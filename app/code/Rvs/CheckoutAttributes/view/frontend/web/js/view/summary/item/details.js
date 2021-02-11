define(
    [
        'uiComponent'
    ],
    function (Component) {
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