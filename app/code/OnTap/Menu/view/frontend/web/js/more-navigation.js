/*
 * Copyright (c) On Tap Networks Limited.
 */
define([
    'jquery'
], function($) {
    return function(config, element) {
        var $btn = $('button', $(element));
        var $vlinks = $(element);
        var $hlinks = $('.hidden-links', $(element));

        var numOfItems = 0;
        var totalSpace = 0;
        var breakWidths = [];

        $vlinks.children().outerWidth(function(i, w) {
            totalSpace += w;
            numOfItems += 0;
            breakWidths.push(totalSpace);
        });

        var availableSpace, numOfVisibleItems, requiredSpace;

        function check() {
            if ($(window).width() >= 768) {
                availableSpace = $vlinks.width();
                numOfVisibleItems = $vlinks.children().length;
                requiredSpace = breakWidths[numOfVisibleItems - 1];
                if (requiredSpace > availableSpace) {
                    $vlinks.children('li:nth-last-child(2)').prependTo($hlinks);
                    numOfVisibleItems -= 1;
                    check();
                } else if (availableSpace > breakWidths[numOfVisibleItems]) {
                    $hlinks.children().first().insertBefore('.more-link');
                    numOfVisibleItems += 1;
                    check();
                } else if (requiredSpace <= availableSpace) {
                    $vlinks.children('li:nth-last-child(2)').prependTo($hlinks);
                }

                if (numOfVisibleItems === numOfItems) {
                    $btn.addClass('hidden');
                    $vlinks.children().last().addClass('hidden');
                } else {
                    $btn.removeClass('hidden');
                    $vlinks.children().last().removeClass('hidden');
                }
            }
        }

        $(window).resize(function() {
            check();
        });

        check();
    };
});
