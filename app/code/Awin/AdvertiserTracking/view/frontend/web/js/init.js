define([
    'jquery',
    'uiComponent',
    'mage/url'
],
    function ($, Component, url) {
        'use strict';

        return Component.extend({

            initialize: function (config, node) {
                url.setBaseUrl(BASE_URL);
                var settingsUrl = url.build('awin/settings');
                var awinPixelUrl = url.build('awin');
                $.getJSON(settingsUrl, function (data) {
                    if (data.advertiserId > 0) {
                        var getUrlParameter = function (name) {
                            name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
                            var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
                            var results = regex.exec(location.search);
                            return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
                        };

                        var awinPixel = document.createElement("img");
                        awinPixel.src = awinPixelUrl + "?awc=" + getUrlParameter("awc") + "&source=" + getUrlParameter("source");
                        
                        awinPixel.setAttribute('style', 'display: none;width:0px;height:0px;');
                        node.appendChild(awinPixel);

                        var awinMasterTag = document.createElement("script");
                        awinMasterTag.src = "https://www.dwin1.com/" + data.advertiserId + ".js";
                        awinMasterTag.setAttribute("defer", "defer");
                        node.appendChild(awinMasterTag);
                    }
                });
            }
        });
    });