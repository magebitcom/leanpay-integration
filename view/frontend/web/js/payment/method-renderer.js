/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';

        rendererList.push(
            {
                type: 'leanpay',
                component: 'Leanpay_Payment/js/payment/method-renderer/leanpay'
            }
        );

        return Component.extend({});
    }
);
