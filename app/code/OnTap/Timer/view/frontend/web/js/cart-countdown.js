/*
 * Copyright (c) On Tap Networks Limited.
 */

define([
    'jquery',
    'Magento_Ui/js/lib/core/storage/local'
], function ($, storage) {
    'use strict';
    return function (config, element) {
        const step = 1000;
        const wallSeconds = 60;
        let minutes = 4;
        let seconds = 0;
        let counter = 60;
        let storedTime = storage.get('cartTimer');
        if (storedTime !== undefined) {
            minutes = storedTime[0];
            counter = storedTime[1];
        }
        let updateTimer = function () {
            counter = (counter === 1) ? wallSeconds : counter - 1;
            seconds = counter % wallSeconds;
            if (seconds === 0) {
                minutes = minutes - 1;
            }
            if (minutes < 0) {
                minutes = 4;
            }
            $(element).html(
                pad(minutes, 2) + ':' + pad(seconds, 2)
            );
            storage.set('cartTimer', [minutes, counter]);
        }
        let pad = function (str, max) {
            str = str.toString();
            return str.length < max ? pad("0" + str, max) : str;
        }
        setInterval(function () {
            updateTimer();
        }, step);
    };
});
