/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'jquery',
    'Magento_Catalog/js/price-utils',
    'underscore',
    'mage/template',
    'jquery-ui-modules/widget',
    'mage/url'
], function ($, utils, _, mageTemplate, jqueryWidget, url) {
    'use strict';

    var globalOptions = {
        productId: null,
        priceConfig: null,
        prices: {},
        priceTemplate: '<span class="price"><%- data.formatted %></span>'
    };

    return function (widget) {
        $.widget('mage.priceBox', widget, {
            _ensureInstallmentCache: function ()
            {
                if (typeof this.installmentCache === 'undefined') {
                    this.installmentCache = {};
                }
            },
            installmentPrice: function (amount)
            {
                var self = this;
                self._ensureInstallmentCache();

                var intAmount = Math.round(amount);

                // Prefer pre-rendered map from priceConfig when available
                if (self.options.priceConfig && self.options.priceConfig.installmentHtmlMap) {
                    var map = self.options.priceConfig.installmentHtmlMap;
                    if (typeof map[intAmount] !== 'undefined') {
                        var wrapper = $('.price-installment_price');
                        wrapper.html(map[intAmount]);
                        wrapper.trigger('contentUpdated');
                        $(document).trigger('installmentSlider');
                        self.installmentCache[intAmount] = map[intAmount];
                        return;
                    }
                }

                if (self.installmentCache[intAmount]) {
                    var cachedHtml = self.installmentCache[intAmount];
                    var wrapper = $('.price-installment_price');
                    wrapper.html(cachedHtml);
                    wrapper.trigger('contentUpdated');
                    $(document).trigger('installmentSlider');
                    return;
                }

                self.options.ajax = $.ajax({
                    type: 'get',
                    url: url.build('/leanpay/installment/index/'),
                    data: {"amount": amount},
                    beforeSend: function () {
                        if (typeof self.options.ajax !== 'undefined') {
                            self.options.ajax.abort();
                        }
                    },
                    success: function (response) {
                        response = JSON.parse(response);
                        if (typeof response.installment_html !== 'undefined') {
                            var currentHtml = $('.price-installment_price .installment-wrapper');
                            var newHtml = response.installment_html;
                            var wrapper = $('.price-installment_price');

                            if (currentHtml !== newHtml) {
                                wrapper.html(newHtml);
                                wrapper.trigger('contentUpdated');
                                $(document).trigger('installmentSlider');
                            }

                            self.installmentCache[intAmount] = newHtml;
                        }
                    },
                    cache: true,
                    dataType: 'html'
                });
            },
            reloadPrice: function reDrawPrices()
            {
                var priceFormat = (this.options.priceConfig && this.options.priceConfig.priceFormat) || {},
                    priceTemplate = mageTemplate(this.options.priceTemplate);

                _.each(this.cache.displayPrices, function (price, priceCode) {
                    price.final = _.reduce(price.adjustments, function (memo, amount) {
                        return memo + amount;
                    }, price.amount);

                    price.formatted = utils.formatPrice(price.final, priceFormat);

                    $(document).trigger('installment_price', [price.final]);

                    if (priceCode === 'finalPrice' && price.final > 0) {
                        this.installmentPrice(price.final);
                    }

                    $('[data-price-type="' + priceCode + '"]', this.element).html(priceTemplate({
                        data: price
                    }));
                }, this);
            },
        });
    }
});
