define(
    [
        'jquery',
    ],
    function ($) {
        'use strict';

        $(document).on('ready', handleToolTip());

        function handleToolTip() {
            $('.price-installment_price').on('click', '.installment-info .installment-logo', function () {
                $('.installment-tooltip').toggleClass('hidden');
            });
        }
    }
);j
