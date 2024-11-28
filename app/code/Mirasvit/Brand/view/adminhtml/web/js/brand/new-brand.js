define([
    'Magento_Ui/js/form/element/ui-select'
], function (Select) {
    'use strict';

    return Select.extend({
        setParsed: function (data) {
            if (!data.success) {
                window.location.reload();
                return;
            }

            var option = this.parseData(data);

            if (data.error) {
                return this;
            }

            var options = this.options();
            options.push(option);
            this.cacheOptions.plain.push(option);
            this.options(options);
            this.setOption(option, options);
            this.set('newOption', option);
        },

        parseData: function (data) {
            return {
                value: data.option['value'],
                label: data.option['label'],
                level: 1,
                path: "",
            };
        }
    });
});
