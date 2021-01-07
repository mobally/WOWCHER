/**
 * Copyright © 2013-2018 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global confirm:true*/
define([
    'jquery',
    'Magento_Customer/js/model/authentication-popup',
    'Magento_Customer/js/customer-data',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/confirm',
    'mage/url',
	'jquery/ui',
    'mage/decorate',
    'mage/collapsible',
    'mage/cookies',
	'Magento_Checkout/js/sidebar'
], function ($, authenticationPopup, customerData, alert, confirm, url) {

    $.widget('scommerce.sidebar', $.mage.sidebar, {
        _gaRemoveFromCart: function($){
			var list = $.mage.cookies.get('productlist');
        	$.ajax({
                url: url.build('gtag/index/removefromcart'),
                type: 'get',
                dataType: 'json',
                success: function(product) {
					if (product != undefined) {
						if (list == undefined){
							list = 'Category - '+ product.category
						}
						var sendData = {};

						if (window.isEnhancedEcommerceEnabled === '1') {
							sendData['items'] = [
								{
									"id": product.id,
									"name": product.name,
									"list_name": list,
									"brand": product.brand,
									"category": product.category,
									"variant": product.variant,
									//"list_position": 1,
									"quantity": product.qty,
									"price": product.price
								}
							];
						}

						if (window.isDynamicRemarketingEnabled === '1') {
							if (window.isOtherSiteEnabled === '1') {
								sendData['dynx_itemid'] = product.id;
								sendData['dynx_totalvalue'] = product.price;
							} else {
								sendData['ecomm_prodid'] = product.id;
								sendData['ecomm_totalvalue'] = product.price;
							}
						}

						if (sendData !== {}) {
							gtag('event', 'remove_from_cart', sendData);
						}
					}
					$.ajax({
						url: url.build('gtag/index/unsremovefromcart'),
						type: 'get',
						success: function(response) {
						}
					});
                }
            });			
        },
        /**
         * Update content after item remove
         *
         * @param elem
         * @param response
         * @private
         */
        _removeItemAfter: function(elem, response) {
            this._gaRemoveFromCart($);
        },
		
		/**
         * Calculate height of minicart list
         *
         * @private
		 */
        _calcHeight: function () {
            var self = this,
                height = 0,
                counter = this.options.minicart.maxItemsVisible,
                target = $(this.options.minicart.list),
                outerHeight;

            self.scrollHeight = 0;
            target.children().each(function () {

                if ($(this).find('.options').length > 0) {
                    $(this).collapsible();
                }
                outerHeight = $(this).outerHeight();

                if (counter-- > 0) {
                    height += outerHeight;
                }
                self.scrollHeight += outerHeight;
            });
			if (height>0){
				target.parent().height(height);
			}
        }
    });

    return $.scommerce.sidebar;
});
