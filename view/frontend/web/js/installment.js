define(
    [
        'jquery',
        'Leanpay_Payment/js/vendor/jquery.ui.touch-punch.min'
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
                range: 'min',
                step: .0001,
                orientation: 'horizontal',
                animate: 'slow',
                max: data.max,
                create: function (event, ui) {
                    sliderUpdate();
                },
                slide: function (event, ui) {
                    sliderUpdate(Math.round(ui.value));
                },
                stop: function (event, ui) {
                    $(".installment-slider").slider('value', Math.round(ui.value));
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
                function (e) {
                    if (!e.target.classList.contains('ui-slider-handle')) {
                        $('.installment-tooltip').addClass('hidden');
                    }
                }
            );
        }
    }
);
