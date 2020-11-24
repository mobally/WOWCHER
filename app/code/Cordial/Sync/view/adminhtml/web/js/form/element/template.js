/**
 * Cordial/Magento Integration RFP
 *
 * @category    Cordial
 * @package     Cordial_Sync
 * @author      Cordial Team <info@cordial.com>
 * @copyright   Cordial (http://cordial.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'mage/storage'
], function (_, registry, Select, storage) {
    'use strict';
    return Select.extend({
        defaults: {
            skipValidation: false,
            imports: {
                update: '${ $.parentName }.store_id:value'
            }
        },

        /**
         * @param {String} value
         */
        update: function (value) {
            storage.post(
                this.storageConfig.refreshUrl,
                JSON.stringify({'store': value}),
                false
            );
        }
    });
});

