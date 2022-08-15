define(
    [
        'jquery',
        'jquery-ui-modules/slider',
        'Leanpay_Payment/js/vendor/jquery.ui.touch-punch.min'
    ],
    function ($) {
        'use strict';

        $(document).on('ready', handleToolTip());
        $(document).on('ready', handleSlider());
        $(document).on('installmentSlider', function () {
            handleSlider()
        });

        $(document).on('installmentReInit', function () {
            handleToolTipCheckout();
            handleSlider();
        });

        function handleSlider()
        {
            if ($('.installment-slider-data').length > 0) {
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

                function sliderUpdate(id = 0)
                {
                    var data = JSON.parse($('.installment-slider-data').html()).data[id];
                    var currency = JSON.parse($('.installment-slider-data').html()).currency
                    $('.term-html .installment_period').html(data.installment_period + ' x');
                    $('.term-html .installment_amount').html(data.installment_amount + currency);
                    $('.installment-slider-term .total')
                        .html((data.installment_period * data.installment_amount).toFixed(2) + currency);
                }
            }
        }

        function handleToolTipCheckout()
        {
            $('.checkout-index-index').on(
                'mouseenter',
                '.installment-mouse',
                function () {
                    $('.installment-tooltip').removeClass('hidden');
                }
            ).on(
                'mouseleave',
                '.installment-mouse',
                function (e) {
                    if (
                        !e.target.classList.contains('ui-slider-handle') &&
                        !e.target.classList.contains('installment-slider') &&
                        !e.target.classList.contains('ui-slider-range')
                    ) {
                        $('.installment-tooltip').addClass('hidden');
                    }
                }
            );
        }

        function handleToolTip()
        {
            $('.price-installment_price').on(
                'mouseenter',
                '.installment-mouse',
                function () {
                    $('.installment-tooltip').removeClass('hidden');
                }
            ).on(
                'mouseleave',
                '.installment-mouse',
                function (e) {
                    if (
                        !e.target.classList.contains('ui-slider-handle') &&
                        !e.target.classList.contains('installment-slider') &&
                        !e.target.classList.contains('ui-slider-range')
                    ) {
                        $('.installment-tooltip').addClass('hidden');
                    }
                }
            );
        }
    }
);
