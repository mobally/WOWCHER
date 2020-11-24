/*
 * Copyright (c) On Tap Networks Limited.
 */
define([
    'jquery',
    'underscore',
    'Magento_Customer/js/customer-data'
], function ($, _, customerData) {
    'use strict';
    return function (config, element) {
        let afterLocationLoaded = function (locationData) {
            if (locationData.noticeHtml && !locationData.closed) {
                $(element).show();
                $('.content', $(element)).html(locationData.noticeHtml);

                $('[data-role=continue]', $(element)).on('click', function () {
                    window.location.href = $('[data-role=region-dropdown]').val();
                });

                $('[data-role=close]', $(element)).on('click', function () {
                    $(element).hide();
                    locationData.closed = true;
                    customerData.set('geoip', locationData);
                });
            }
        };

        let getParam = function (name) {
            let results = new RegExp('[\?&]' + name + '=([^&#]*)')
                .exec(window.location.search);
            return (results !== null) ? results[1] || 0 : false;
        };

        let geoIpData = customerData.get('geoip');

        customerData.getInitCustomerData().done(function () {
            if (_.isEmpty(geoIpData())) {
                let country = getParam('country');
                if (country) {
                    $.cookie('forceGeoIpCountry', country);
                } else {
                    $.cookie('forceGeoIpCountry', null, {expires: -1});
                }
                customerData.reload(['geoip'], true);
            } else {
                let myCountry = geoIpData().selectedCountry;
                if (myCountry !== config.siteRegion) {
                    customerData.reload(['geoip'], true);
                } else {
                    afterLocationLoaded(geoIpData());
                }
            }
        });

        geoIpData.subscribe(afterLocationLoaded);
    };
});
