<?php

namespace Leanpay\Payment\Model\Config\Source;

use Leanpay\Payment\Helper\Data;
use Magento\Framework\Option\ArrayInterface;

class Modes implements ArrayInterface
{
    /**
     * Return array of options as value-label pairs
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => Data::LEANPAY_API_MODE_DEV,
                'label' => __('Demo')
            ],

            [
                'value' => Data::LEANPAY_API_MODE_LIVE,
                'label' => __('Production')
            ],
        ];
    }
}