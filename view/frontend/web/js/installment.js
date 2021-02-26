define(
    [
        'jquery',
    ],
    function ($) {
        'use strict';

        $(document).on('ready', handleToolTip());

        function handleToolTip() {
            $('.price-installment_price').on(
                'mouseenter touchstart',
                '.installment-info .installment-logo-wrapper',
                function () {
                    $('.installment-tooltip').removeClass('hidden');
                }
            ).on(
                'mouseleave',
                '.installment-mouse',
                function () {
                    $('.installment-tooltip').addClass('hidden');
                }
            );
        }
    }
);
