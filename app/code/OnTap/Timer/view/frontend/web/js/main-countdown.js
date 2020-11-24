/*
 * Copyright (c) On Tap Networks Limited.
 */
define([
    'jquery',
], function ($) {
    'use strict';

    return function (config, element) {
        $.fn.countdown = function() {
            setInterval(function() {
                var now = new Date();
                var hrs = 23-now.getHours();
                var mins = 59-now.getMinutes();
                var secs = 59-now.getSeconds();
                var str = '';

                str += '00' + ' : ' + display(hrs) + ' : ' + display(mins) + ' : '+ display(secs);
                $(element).context.innerHTML = str;
            }, 1000);
        };

       function display(val) {
           return val > 0 ? (val < 10 ? "0" : "") + val : "00";
       }

       $.fn.countdown();
    };
});
