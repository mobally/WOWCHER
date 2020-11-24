/*
 * Copyright (c) On Tap Networks Limited.
 */

define([
    'uiComponent',
    'jquery',
    'ko'
], function (Component, $, ko) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'OnTap_ConfigurableGroupedProduct/configurable-ui',
            rowLabel: 'Default 1',
            rowOptions: [],
            _columnOptions: [],
            columnLabel: 'Default 2',
            selectedColumnValue: '',
            selectedRowValue: '',
            columnOptions: {},
            qtyFieldName: 'noop',
            hasTwoOptions: false
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super()
                .observe(['selectedRowValue', 'selectedColumnValue', '_columnOptions', 'qtyFieldName']);

            this.selectedRowValue.subscribe(function (value) {
                if (value === undefined) {
                    return;
                }
                if (this.hasTwoOptions) {
                    this.populateColumnOptions(value);
                } else {
                    this.qtyFieldName('super_group[' + value + ']');
                }
            }.bind(this));

            this.selectedColumnValue.subscribe(function (value) {
                this.qtyFieldName('super_group[' + value + ']');
            }.bind(this));

            return this;
        },

        /**
         * Select the items (columns) from row
         * @param rowId
         */
        populateColumnOptions: function (rowId) {
            this._columnOptions(this.columnOptions[rowId]['items']);
        },

        /**
         * KO callback to disable items
         * @param option
         * @param item
         */
        setOptionDisable: function (option, item) {
            ko.applyBindingsToNode(option, {disable: option.value === '0'}, item);
        }
    });
});
