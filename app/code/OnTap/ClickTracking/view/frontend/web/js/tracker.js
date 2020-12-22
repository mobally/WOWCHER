/*
 * Copyright (c) On Tap Networks Limited.
 */
define([
    'jquery',
    'mage/url',
	'mage/cookies'
], function ($, url,mage) {
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
		if($.cookie('gclidnew')) 
		{	
			if (window.location.href.indexOf("gclid") > -1) {
				let gclid = getParam('gclid');
				$.cookie('gclidnew', gclid);
			}
			else{
				$.cookie('gclidnew');
			}
		}else{
			$.cookie('gclidnew', gclid);
		}
		
		if($.cookie('msclkidnew')) 
		{		
			if (window.location.href.indexOf("msclkid") > -1) {
				let msclkid = getParam('msclkid');
				$.cookie('msclkidnew', msclkid);
			}
			else{
				$.cookie('msclkidnew');		
			}
		}else{
			$.cookie('msclkidnew', msclkid);
		}
		
		if($.cookie('itonew')) 
		{	
			if (window.location.href.indexOf("ito") > -1) {
				let ito = getParam('ito');
				$.cookie('itonew', ito);
			}
			else{
				$.cookie('itonew');
			}
		}else{
			$.cookie('itonew', ito);
		}
		
		//$.cookie('msclkidnew', msclkid,{ expires: 7, path: '/' });
		//$.cookie('itonew', ito,{ expires: 7, path: '/' });
       /* if (gclid || msclkid || ito) {
            $.ajax({
                url: url.build('tracker/track'),
                data: {
                    gclid: gclid,
                    msclkid: msclkid,
                    ito: ito
                }
            });
        }*/
    };
});
