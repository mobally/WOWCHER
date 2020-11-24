/*
 * Copyright (c) On Tap Networks Limited.
 */
define ([
    'jquery',
    'accordion'
    ], function ($) {

    return function(config, element) {
        $('.panel.header > .header.links').clone().appendTo('.my-account-content', $(element));
    };
});
