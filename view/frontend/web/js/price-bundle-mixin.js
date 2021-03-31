define(
    [
        'jquery',
        'underscore',
        'mage/template',
        'priceUtils',
        'priceBox',
        'mage/url'
    ],
    function ($, _, mageTemplate, utils, priceBox, url) {
        'use strict';

        return function (widget) {
            $.widget('mage.priceBundle', widget, {
                /**
                 * Update price box config with bundle option prices
                 * @private
                 */
                _updatePriceBox: function () {
                    var form = this.element,
                        options = $(this.options.productBundleSelector, form),
                        priceBox = $(this.options.priceBoxSelector, form),
                        self = this;

                    $(document).on('installment_price', function (e, data) {
                        if (data > 0) {
                            self.installmentPrice(data);
                        }
                    });

                    if (!this.options.isOptionsInitialized) {
                        if (priceBox.data('magePriceBox') &&
                            priceBox.priceBox('option') &&
                            priceBox.priceBox('option').priceConfig
                        ) {
                            if (priceBox.priceBox('option').priceConfig.optionTemplate) { //eslint-disable-line max-depth
                                this._setOption('optionTemplate', priceBox.priceBox('option').priceConfig.optionTemplate);
                            }
                            this._setOption('priceFormat', priceBox.priceBox('option').priceConfig.priceFormat);
                            priceBox.priceBox('setDefault', this.options.optionConfig.prices);
                            this.options.isOptionsInitialized = true;
                        }
                        this._applyOptionNodeFix(options);
                    }

                    return this;
                },

                installmentPrice: function (amount) {
                    var self = this;

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
                            }
                        },
                        cache: true,
                        dataType: 'html'
                    });
                }
            });
        };
    });
