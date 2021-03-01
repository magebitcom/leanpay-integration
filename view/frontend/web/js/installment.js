define(
    [
        'jquery',
    ],
    function ($) {
        'use strict';

        $(document).on('ready', handleToolTip());
        $(document).on('ready', handleSlider());
        $(document).on('installmentSlider', function () {
            handleSlider()
        });

        function handleSlider() {
            var data = JSON.parse($('.installment-slider-data').html())

            $('.installment-slider').slider({
                animate: true,
                range: 'min',
                max: data.max,
                create: function (event, ui) {
                    sliderUpdate();
                },
                slide: function (event, ui) {
                    sliderUpdate(ui.value);
                }
            });

            function sliderUpdate(id = 0) {
                var data = JSON.parse($('.installment-slider-data').html()).data[id];
                $('.term-html .installment_period').html(data.installment_period + ' x');
                $('.term-html .installment_amount').html(data.installment_amount + '€');
                $('.installment-slider-term .total')
                    .html((data.installment_period * data.installment_amount).toFixed(2) + '€');
            }
        }

        function handleToolTip() {
            $('.price-installment_price').on(
                'mouseenter touchstart',
                '.installment-info .installment-logo',
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
