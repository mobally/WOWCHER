/**
 * Copyright © 2013-2018 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global confirm:true*/

define([
    'jquery'
], function ($) {

    var findProductInList = function (id, list) {
        for (var i in list) {
            if (list[i]["id"] == id) {
                return list[i];
            }
        }
        return false;
    };

	return {
		findProductInStorage: function(id) {
            var tmpList = JSON.parse(localStorage.getItem("sc-gtag-data"));
            if (tmpList != null) {
                var tmp = tmpList["product_list"];
                return findProductInList(id, tmp);
			}
            return false;
		},

        mergeProductLists: function (newListData) {
            var tmpList = JSON.parse(localStorage.getItem("sc-gtag-data"));
            if (tmpList == null) {
                localStorage.setItem("sc-gtag-data", JSON.stringify({"product_list": newListData}));
            } else {
                var tmp = tmpList["product_list"];
                var resultList = tmp;
                for (var i in newListData) {
                    if (resultList.length >= 100) {
                        resultList.pop();
                    }
                    var item = findProductInList(newListData[i]["id"], tmp);
                    if (item !== false) {
                        item = newListData[i];
                    } else {
                        resultList.push(newListData[i]);
                    }
                }
                localStorage.setItem("sc-gtag-data", JSON.stringify({"product_list": resultList}));
            }
        }
    }
});
