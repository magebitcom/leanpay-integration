define(['jquery'], function ($) {
    'use strict';

    return function (widget) {
        $.widget('mage.configurable', widget, {

            /**
             * Hand the selected variant's badge to the price box, which renders it with the new price (LMM-144)
             *
             * @private
             */
            _reloadPrice: function () {
                var optionPrice = this.simpleProduct ? this.options.spConfig.optionPrices[this.simpleProduct] : null;

                this._getPriceBoxElement().data(
                    'leanpayVariantInstallment',
                    optionPrice && typeof optionPrice.instalment_html !== 'undefined' ? {
                        id: this.simpleProduct,
                        amount: Math.round(optionPrice.finalPrice.amount),
                        html: optionPrice.instalment_html
                    } : null
                );

                return this._super();
            }
        });

        return $.mage.configurable;
    };
});
