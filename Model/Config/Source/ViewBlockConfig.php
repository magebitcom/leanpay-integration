<?php

namespace Leanpay\Payment\Model\Config\Source;

use Leanpay\Payment\Helper\Data;
use Magento\Framework\Data\OptionSourceInterface;

class ViewBlockConfig implements OptionSourceInterface
{
    /**
     * Return array of leanpay environment
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => Data::LEANPAY_INSTALLMENT_VIEW_OPTION_HOMEPAGE,
                'label' => __('Homepage')
            ],
            [
                'value' => Data::LEANPAY_INSTALLMENT_VIEW_OPTION_PRODUCT_PAGE,
                'label' => __('Product')
            ],
            [
                'value' => Data::LEANPAY_INSTALLMENT_VIEW_OPTION_CATEGORY_PAGE,
                'label' => __('Category')
            ],
        ];
    }
}
