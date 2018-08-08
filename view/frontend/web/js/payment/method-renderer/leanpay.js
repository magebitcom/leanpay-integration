/* global define */
define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default'
    ],
    function ($, Component) {
        'use strict';

        return Component.extend({
            defaults: {
                redirectAfterPlaceOrder: false,
                template: 'Leanpay_Payment/payment/leanpay'
            },

            /**
             * Get value of instruction field.
             * @returns {String}
             */
            getInstructions: function () {
                return window.leanpayConfig.instructions;
            },

            getLogo: function () {
                return window.leanpayConfig.logo
                    ;
            },

            afterPlaceOrder: function () {
                $.mage.redirect('/leanpay/checkout/placeorder');
            }
        });
    }
);
