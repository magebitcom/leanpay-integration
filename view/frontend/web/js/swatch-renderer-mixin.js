define(['jquery'], function ($) {
    'use strict';

    return function (widget) {
        $.widget('mage.SwatchRenderer', widget, {

            /**
             * Update total price
             *
             * @private
             */
            _UpdatePrice: function () {
                var $widget = this,
                    $product = $widget.element.parents($widget.options.selectorProduct),
                    $productPrice = $product.find(this.options.selectorProductPrice),
                    result = $widget._getNewPrices(),
                    tierPriceHtml,
                    isShow,
                    installmentPrice = $('.price-installment_price .installment-wrapper'),
                    optionId = $widget._CalcProducts()[$widget._CalcProducts().length - 1];

                if (
                    typeof $widget.options.jsonConfig.optionPrices[optionId].instalment_html !== 'undefined'
                ) {
                    var newHtml = $widget.options.jsonConfig.optionPrices[optionId].instalment_html;
                    if (installmentPrice !== newHtml) {
                        $('.price-installment_price').html(newHtml);
                        $('.price-installment_price').trigger('contentUpdated');
                        $(document).trigger('installmentSlider');
                    }
                }

                $productPrice.trigger(
                    'updatePrice',
                    {
                        'prices': $widget._getPrices(result, $productPrice.priceBox('option').prices)
                    }
                );


                isShow = typeof result != 'undefined' && result.oldPrice.amount !== result.finalPrice.amount;

                $product.find(this.options.slyOldPriceSelector)[isShow ? 'show' : 'hide']();

                if (typeof result != 'undefined' && result.tierPrices && result.tierPrices.length) {
                    if (this.options.tierPriceTemplate) {
                        tierPriceHtml = mageTemplate(
                            this.options.tierPriceTemplate,
                            {
                                'tierPrices': result.tierPrices,
                                '$t': $t,
                                'currencyFormat': this.options.jsonConfig.currencyFormat,
                                'priceUtils': priceUtils
                            }
                        );
                        $(this.options.tierPriceBlockSelector).html(tierPriceHtml).show();
                    }
                } else {
                    $(this.options.tierPriceBlockSelector).hide();
                }

                $(this.options.normalPriceLabelSelector).hide();

                _.each($('.' + this.options.classes.attributeOptionsWrapper), function (attribute) {
                    if ($(attribute).find('.' + this.options.classes.optionClass + '.selected').length === 0) {
                        if ($(attribute).find('.' + this.options.classes.selectClass).length > 0) {
                            _.each($(attribute).find('.' + this.options.classes.selectClass), function (dropdown) {
                                if ($(dropdown).val() === '0') {
                                    $(this.options.normalPriceLabelSelector).show();
                                }
                            }.bind(this));
                        } else {
                            $(this.options.normalPriceLabelSelector).show();
                        }
                    }
                }.bind(this));
            },
        });
        return $.mage.SwatchRenderer;
    };
});
