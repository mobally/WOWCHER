/*
 * Copyright (c) On Tap Networks Limited.
 */
define ([
    'jquery',
    'accordion'
    ], function ($) {

    return function(config, element) {
        var slider = $('#main-navigation-slider', $(element));
        var rightNav = $('.navigation-arrow-right', $(element));
        var leftNav = $('.navigation-arrow-left', $(element));

        slider.scroll(function() {
            var $width = slider.outerWidth()
            var $scrollWidth = slider[0].scrollWidth;
            var $scrollLeft = slider.scrollLeft();

            if ($scrollWidth - $width === $scrollLeft) {
                rightNav.hide()
            } else if ($scrollLeft === 0) {
                leftNav.hide();
            } else {
                leftNav.show();
                rightNav.show();
            }
        });
    };
});
