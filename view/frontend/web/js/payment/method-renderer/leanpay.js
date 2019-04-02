/* global define */
define(
    [
        'jquery',
        'ko',
        'mage/url',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/quote'
    ],
    function ($, ko, url, Component, errorProcessor, fullScreenLoader, quote) {
        'use strict';

        return Component.extend({
            isEnable: ko.observable(true),

            defaults: {
                redirectAfterPlaceOrder: false,
                template: 'Leanpay_Payment/payment/leanpay',
            },

            initialize: function() {
                this._super();
                var self = this;

                quote.billingAddress.subscribe(function (newAddress) {
                    if (!newAddress) {
                        self.isEnable(false);
                        return;
                    }

                    if (window.leanpayConfig.allowspecific === '1') {
                        self.isEnable(window.leanpayConfig.countries.includes(newAddress.countryId));

                        return;
                    }

                    self.isEnable(true);
                });
            },

            /**
             * Get value of instruction field.
             * @returns {String}
             */
            getInstructions: function () {
                return window.leanpayConfig.instructions;
            },

            getLogo: function () {
                return window.leanpayConfig.logo;
            },

            afterPlaceOrder: function () {
                //$.mage.redirect('/leanpay/checkout/placeorder');
                var leanpay_checkout_redirect = url.build('/leanpay/checkout/placeorder'); //your custom controller url
                $.post(leanpay_checkout_redirect, 'json')
                    .done(function (response) {
                        $.mage.dataPost().postData(response);
                    })
                    .fail(function (response) {
                        errorProcessor.process(response, this.messageContainer);
                    })
                    .always(function () {
                        fullScreenLoader.stopLoader();
                    });
            }
        });
    }
);
