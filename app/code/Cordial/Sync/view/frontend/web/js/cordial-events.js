define(['uiComponent', 'Magento_Customer/js/customer-data', 'ko'], function(Component, customerData, ko) {

    return Component.extend({
        events: {},

        initialize: function () {
            this._super();
            this.events = customerData.get('cordial');
        }
    });
});