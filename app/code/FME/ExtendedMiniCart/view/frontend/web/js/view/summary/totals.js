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
    'Magento_Checkout/js/view/summary/abstract-total',
], function (Component) {
    'use strict';

    return Component.extend({
        /**
         * @return {*}
         */
        isDisplayed: function () {
            console.log(window.checkoutConfig.myCustomData);
            if (window.checkoutConfig.myCustomData == 1) {
                return true;
            } else {
                return this.isFullMode();
            }
        }

    });
});
