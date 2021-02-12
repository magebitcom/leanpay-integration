<?php
declare(strict_types=1);

namespace Leanpay\Payment\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Languages
 *
 * @package Leanpay\Payment\Model\Config\Source
 */
class Languages implements OptionSourceInterface
{
    /**
     * Return array of leanpay languages
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => 'sl',
                'label' => __('Slovenian')
            ],
            [
                'value' => 'en',
                'label' => __('English')
            ],
        ];
    }
}
