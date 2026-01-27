define(
    [
        'jquery',
        'jquery-ui-modules/slider',
        'mage/touch-slider'
    ],
    function ($) {
        'use strict';

        function isTouchDevice() {
            return 'ontouchstart' in window || navigator.maxTouchPoints > 0;
        }

        function isMobileViewport() {
            return window.innerWidth <= 767;
        }

        function shouldUseTouchSlider() {
            return isTouchDevice() || isMobileViewport();
        }

        $(document).on('ready', handleToolTip());
        $(document).on('ready', handleSlider());
        $(document).on('installmentSlider', function () {
            handleSlider()
        });

        // Initialize checkout handlers on page load
        if ($('body').hasClass('checkout-index-index')) {
            handleToolTipCheckout();
        }

        $(document).on('installmentReInit', function () {
            handleToolTipCheckout();
            handleSlider();
        });

        // Reinitialize slider on resize if viewport crosses mobile breakpoint
        var resizeTimer;
        $(window).on('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                var $slider = $('.installment-slider');
                if ($slider.length && ($slider.data('ui-slider') || $slider.data('touchSlider'))) {
                    // Destroy existing slider and reinitialize
                    if ($slider.data('touchSlider')) {
                        $slider.touchSlider('destroy');
                    } else if ($slider.data('ui-slider')) {
                        $slider.slider('destroy');
                    }
                    handleSlider();
                }
            }, 250);
        });

        function handleSlider()
        {
            if ($('.installment-slider-data').length > 0) {
                var data = JSON.parse($('.installment-slider-data').html());
                var $slider = $('.installment-slider');
                var $sliderTerm = $slider.closest('.installment-slider-term');

                function ensureSliderDecorations(installmentData)
                {
                    if (!$sliderTerm.length) {
                        return;
                    }

                    if (!$sliderTerm.find('.installment-slider-scale').length) {
                        $sliderTerm.prepend('<div class="installment-slider-scale" aria-hidden="true"></div>');
                    }
                    if (!$sliderTerm.find('.installment-slider-selected').length) {
                        $sliderTerm.append('<div class="installment-slider-selected" aria-hidden="true"></div>');
                        var monthLabel = $sliderTerm.data('month-label') || 'mes.';
                        var $unit = $('<div>', {
                            'class': 'installment-slider-selected-unit',
                            'aria-hidden': 'true',
                            'text': monthLabel
                        });
                        $sliderTerm.append($unit);
                    }

                    var $scale = $sliderTerm.find('.installment-slider-scale');
                    if ($scale.children().length) {
                        return; // already built
                    }

                    // Build ticks from unique available periods (prefer 12,24,... if present)
                    var periods = [];
                    for (var i = 0; i < installmentData.data.length; i++) {
                        var p = parseInt(installmentData.data[i].installment_period, 10);
                        if (!isNaN(p) && periods.indexOf(p) === -1) {
                            periods.push(p);
                        }
                    }
                    periods.sort(function (a, b) { return a - b; });

                    // If we have a lot of periods, keep it readable by sampling up to 8 ticks
                    if (periods.length > 8) {
                        var sampled = [];
                        var step = Math.ceil(periods.length / 8);
                        for (var j = 0; j < periods.length; j += step) {
                            sampled.push(periods[j]);
                        }
                        if (sampled[sampled.length - 1] !== periods[periods.length - 1]) {
                            sampled.push(periods[periods.length - 1]);
                        }
                        periods = sampled;
                    }

                    // Map period -> slider index
                    var maxIndex = installmentData.max || 0;
                    // Special case: only 1 option (maxIndex === 0). Still render a single tick label.
                    if (maxIndex <= 0) {
                        var onlyPeriod = null;
                        if (installmentData.data && installmentData.data.length) {
                            onlyPeriod = parseInt(installmentData.data[0].installment_period, 10);
                        }
                        if (!isNaN(onlyPeriod) && onlyPeriod !== null) {
                            $scale.append(
                                '<div class="installment-slider-tick active" data-index="0" style="left:0%">' +
                                '  <span class="installment-slider-tick-label">' + onlyPeriod + '</span>' +
                                '  <span class="installment-slider-tick-dot"></span>' +
                                '</div>'
                            );
                        }
                        return;
                    }
                    periods.forEach(function (period) {
                        var idx = -1;
                        for (var k = 0; k < installmentData.data.length; k++) {
                            if (parseInt(installmentData.data[k].installment_period, 10) === period) {
                                idx = k;
                                break;
                            }
                        }
                        if (idx < 0 || maxIndex <= 0) {
                            return;
                        }
                        var left = (idx / maxIndex) * 100;
                        $scale.append(
                            '<div class="installment-slider-tick" data-index="' + idx + '" style="left:' + left + '%">' +
                            '  <span class="installment-slider-tick-label">' + period + '</span>' +
                            '  <span class="installment-slider-tick-dot"></span>' +
                            '</div>'
                        );
                    });
                }

                function updateSliderDecorations(id, installmentData)
                {
                    if (!$sliderTerm.length) {
                        return;
                    }
                    var maxIndex = installmentData.max || 0;
                    if (maxIndex <= 0) {
                        // Only one option: keep the (single) tick active and position unit at start.
                        $sliderTerm.find('.installment-slider-tick').addClass('active');
                        var $unitSingle = $sliderTerm.find('.installment-slider-selected-unit');
                        if ($unitSingle.length) {
                            $unitSingle.css({ 'left': '0px' });
                        }
                        return;
                    }
                    var clamped = Math.max(0, Math.min(maxIndex, id));
                    var left = (clamped / maxIndex) * 100;

                    var $activeTick = null;
                    $sliderTerm.find('.installment-slider-tick').each(function () {
                        var $tick = $(this);
                        var idx = parseInt($tick.data('index'), 10);
                        if (!isNaN(idx) && idx <= clamped) {
                            $tick.addClass('active');
                            // Track the last active tick (the one under the handle)
                            if (idx === clamped) {
                                $activeTick = $tick;
                            }
                        } else {
                            $tick.removeClass('active');
                        }
                    });

                    // Position the unit label below the active tick
                    var $unit = $sliderTerm.find('.installment-slider-selected-unit');
                    if ($unit.length && $activeTick && $activeTick.length) {
                        var $tickLabel = $activeTick.find('.installment-slider-tick-label');
                        if ($tickLabel.length) {
                            // Get the tick's position relative to the slider-term container
                            var tickOffset = $activeTick.position().left;
                            var tickWidth = $activeTick.outerWidth() || 0;
                            // Center the unit below the tick label
                            var unitLeft = tickOffset + (tickWidth / 2);
                            $unit.css({
                                'left': unitLeft + 'px'
                            });
                        }
                    } else if ($unit.length) {
                        // Fallback: position based on slider value percentage
                        $unit.css({
                            'left': left + '%'
                        });
                    }
                }

                ensureSliderDecorations(data);

                // Destroy existing slider if it exists
                if ($slider.data('touchSlider')) {
                    $slider.touchSlider('destroy');
                } else if ($slider.data('ui-slider')) {
                    $slider.slider('destroy');
                }

                var useTouch = shouldUseTouchSlider();
                // Get default index from config, fallback to 0 if not set
                var defaultIndex = (typeof data.defaultIndex !== 'undefined') ? data.defaultIndex : 0;
                var sliderOptions = {
                    range: 'min',
                    step: .0001,
                    orientation: 'horizontal',
                    animate: 'slow',
                    max: data.max,
                    value: defaultIndex,
                    create: function (event, ui) {
                        sliderUpdate(defaultIndex);
                        updateSliderDecorations(defaultIndex, data);
                    },
                    slide: function (event, ui) {
                        var v = Math.round(ui.value);
                        sliderUpdate(v);
                        updateSliderDecorations(v, data);
                    },
                    stop: function (event, ui) {
                        var v = Math.round(ui.value);
                        var method = useTouch ? 'touchSlider' : 'slider';
                        $slider[method]('value', v);
                        updateSliderDecorations(v, data);
                    }
                };

                if (useTouch) {
                    $slider.touchSlider(sliderOptions);
                    // Set initial value after creation
                    $slider.touchSlider('value', defaultIndex);
                } else {
                    $slider.slider(sliderOptions);
                    // Set initial value after creation
                    $slider.slider('value', defaultIndex);
                }

                function sliderUpdate(id = 0)
                {
                    var installmentData = JSON.parse($('.installment-slider-data').html())
                    var data = installmentData.data[id];
                    var currency = installmentData.currency
                    $('.term-html .installment_period').html(data.installment_period + ' x');
                    var installmentAmountHtml = data.installment_amount + currency
                    var installmentTotalHtml = (data.installment_period * data.installment_amount).toFixed(2) + currency
                    if (installmentData.convertedValues && installmentData.convertedValues[id]) {
                        installmentAmountHtml += ' / ' + installmentData.convertedValues[id].toFixed(2) + installmentData.convertedCurrency;
                        installmentTotalHtml += ' / ' + (data.installment_period * installmentData.convertedValues[id]).toFixed(2) + installmentData.convertedCurrency
                    }
                    $('.term-html .installment_amount').html(installmentAmountHtml);
                    $('.installment-slider-term .total')
                        .html(installmentTotalHtml);

                    // Update tooltip header amount (right side) to match currently selected installment amount; preserve /mesec
                    if ($('.installment-title-price-amount').length) {
                        var headerAmountHtml = data.installment_amount + currency;
                        if (installmentData.convertedValues && installmentData.convertedValues[id]) {
                            headerAmountHtml += ' / ' + installmentData.convertedValues[id].toFixed(2) + installmentData.convertedCurrency;
                        }
                        $('.installment-title-price-amount').html(headerAmountHtml);
                    }
                }
            }
        }

        function handleToolTipCheckout()
        {
            // Use document-level delegation for dynamically loaded content
            $(document).off('mouseenter.checkout-installment mouseleave.checkout-installment click.checkout-calculate click.checkout-close');

            $(document).on(
                'mouseenter.checkout-installment',
                '.checkout-index-index .installment-mouse',
                function () {
                    $('.installment-tooltip').removeClass('hidden');
                }
            ).on(
                'mouseleave.checkout-installment',
                '.checkout-index-index .installment-mouse',
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

            // Handle Calculate button click on checkout
            $(document).on(
                'click.checkout-calculate',
                '.checkout-index-index .installment-calculate-btn',
                function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var $btn = $(this);
                    var $tooltip = $('.installment-tooltip');
                    var $wrapper = $('.installment-wrapper');

                    if ($tooltip.hasClass('hidden')) {
                        $tooltip.removeClass('hidden').addClass('opened');
                        $btn.addClass('active');
                        $wrapper.addClass('tooltip-opened');
                    } else {
                        $tooltip.addClass('hidden').removeClass('opened');
                        $btn.removeClass('active');
                        $wrapper.removeClass('tooltip-opened');
                    }
                }
            );

            // Close tooltip when clicking close button on checkout
            $(document).on(
                'click.checkout-close',
                '.checkout-index-index .installment-tooltip-close',
                function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $('.installment-tooltip').addClass('hidden').removeClass('opened');
                    $('.installment-calculate-btn').removeClass('active');
                    $('.installment-wrapper').removeClass('tooltip-opened');
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

            // Handle Calculate button click
            $('.price-installment_price').on(
                'click',
                '.installment-calculate-btn',
                function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var $btn = $(this);
                    var $tooltip = $('.installment-tooltip');
                    var $wrapper = $('.installment-wrapper');

                    if ($tooltip.hasClass('hidden')) {
                        $tooltip.removeClass('hidden').addClass('opened');
                        $btn.addClass('active');
                        $wrapper.addClass('tooltip-opened');
                    } else {
                        $tooltip.addClass('hidden').removeClass('opened');
                        $btn.removeClass('active');
                        $wrapper.removeClass('tooltip-opened');
                    }
                }
            );

            // Close tooltip when clicking close button
            $('.price-installment_price').on(
                'click',
                '.installment-tooltip-close',
                function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $('.installment-tooltip').addClass('hidden').removeClass('opened');
                    $('.installment-calculate-btn').removeClass('active');
                    $('.installment-wrapper').removeClass('tooltip-opened');
                }
            );

            // Close tooltip when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.installment-wrapper').length) {
                    $('.installment-tooltip').addClass('hidden').removeClass('opened');
                    $('.installment-calculate-btn').removeClass('active');
                    $('.installment-wrapper').removeClass('tooltip-opened');
                }
            });
        }
    }
);
