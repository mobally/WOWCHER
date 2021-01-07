/**
 * Copyright © 2018 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'mage/translate',
	'mage/url',
    'Scommerce_GlobalSiteTag/js/list',
    'jquery/ui',
	'mage/cookies',
    'Magento_Catalog/js/catalog-add-to-cart'
], function($, $t, url, scList) {
    "use strict";

    $.widget('scommerce.catalogAddToCart', $.mage.catalogAddToCart, {
        gaAddToCart: function($){
			$.ajax({
                url: url.build('/gtag/index/addtocart'),
                type: 'get',
                dataType: 'json',
                success: function(products) {

					var items = [],
						ecommProdid = [],
						sendData = {};

                	$.each(products, function (i, product) {
						if (product != undefined) {

							var itemData = {
								"id": product.id,
								"name": product.name,
								"brand": product.brand,
								"category": product.category,
								"variant": product.variant,
								"quantity": product.qty,
								"price": product.price
							};

							//var productListData = $.mage.cookies.get('productListData-'+product.id);
                            var item = scList.findProductInStorage(product.id);

							if (item !== false) {
								var productListData = item;
								itemData["list_name"] = productListData['list'];
								itemData["list_position"] = productListData['position'];
								itemData["category"] = productListData['category'];
							}

							ecommProdid.push(product.id);
							items.push(itemData);
						}
					});

                	if (window.isEnhancedEcommerceEnabled === '1') {
						sendData["items"] = items;
					}

					if (window.isDynamicRemarketingEnabled === '1') {
                		if (window.isOtherSiteEnabled === '1') {
							sendData["dynx_itemid"] = ecommProdid;
						} else {
							sendData["ecomm_prodid"] = ecommProdid;
						}
					}

                	if (items.length && sendData !== {}) {
						gtag('event', 'add_to_cart', sendData);
					}

					$.ajax({
						url: url.build('/gtag/index/unsaddtocart'),
						type: 'get'
					});
                }
            });
			
        },
        ajaxSubmit: function(form) {

            var self = this;
            $(self.options.minicartSelector).trigger('contentLoading');
            self.disableAddToCartButton(form);

            $.ajax({
                url: form.attr('action'),
                data: form.serialize(),
                type: 'post',
                dataType: 'json',
                beforeSend: function() {
                    if (self.isLoaderEnabled()) {
                        $('body').trigger(self.options.processStart);
                    }
                },
                success: function(res) {
                    if (self.isLoaderEnabled()) {
                        $('body').trigger(self.options.processStop);
                    }

                    if (res.backUrl) {
                        window.location = res.backUrl;
                        return;
                    }
                    if (res.messages) {
                        $(self.options.messagesSelector).html(res.messages);
                    }
                    if (res.minicart) {
                        $(self.options.minicartSelector).replaceWith(res.minicart);
                        $(self.options.minicartSelector).trigger('contentUpdated');
                    }
                    if (res.product && res.product.statusText) {
                        $(self.options.productStatusSelector)
                            .removeClass('available')
                            .addClass('unavailable')
                            .find('span')
                            .html(res.product.statusText);
                    }
                    self.enableAddToCartButton(form);
                    self.gaAddToCart($);
                }

            });
        }
    });

    return $.scommerce.catalogAddToCart;
});