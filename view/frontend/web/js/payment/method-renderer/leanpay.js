/* global define */
define(
    [
        'jquery',
        'ko',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/quote'
    ],
    function ($, ko, Component, quote) {
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
                $.mage.redirect('/leanpay/checkout/placeorder');
            }
        });
    }
);
