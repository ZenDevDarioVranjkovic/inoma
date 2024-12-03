/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

define([
    'jquery',
    'uiRegistry',
    'Magento_Ui/js/form/element/single-checkbox',
    'domReady!'
], function ($, uiRegistry, select) {
    'use strict';
    return select.extend({
        defaults: {
            customName: '${ $.parentName }.${ $.index }_input'
        },

        initialize: function () {
            this._super();
            var self = this;
           /* setTimeout(function () {
                self.fieldDepend(self.value())
            }, 2000);*/
        },

        onUpdate: function(value) {
            this.fieldDepend(value);
            return this._super();
        },

        fieldDepend: function(value) {
            if (value) {
                $('#autorp_rule_formrule_same_as_conditions_fieldset_').show();
            } else {
                $('#autorp_rule_formrule_same_as_conditions_fieldset_').hide();
            }
        },
    });
});
