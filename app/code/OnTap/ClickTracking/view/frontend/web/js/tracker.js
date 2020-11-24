/*
 * Copyright (c) On Tap Networks Limited.
 */
define([
    'jquery',
    'mage/url'
], function ($, url) {
    'use strict';
    return function (config) {
        let getParam = function (name) {
            let results = new RegExp('[\?&]' + name + '=([^&#]*)')
                .exec(window.location.search);
            return (results !== null) ? results[1] || 0 : null;
        };

        let gclid = getParam('gclid');
        let msclkid = getParam('msclkid');
        let ito = getParam('ito');

        if (gclid || msclkid || ito) {
            $.ajax({
                url: url.build('tracker/track'),
                data: {
                    gclid: gclid,
                    msclkid: msclkid,
                    ito: ito
                }
            });
        }
    };
});
