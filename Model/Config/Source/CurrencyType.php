<?php
declare(strict_types=1);

/**
 * Leanpay_Payment
 *
 * @category     Leanpay
 * @package      Leanpay_Payment
 * @author       Kristaps Zaķis
 * @copyright    Copyright (c) 2022 Leanpay, Ltd.(http://www.magebit.com/)
 */

namespace Leanpay\Payment\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;


class CurrencyType implements OptionSourceInterface
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
                'value' => 'EUR',
                'label' => __('EUR')
            ],
            [
                'value' => 'HRK',
                'label' => __('HRK')
            ],
            [
                'value' => 'RON',
                'label' => __('RON')
            ],
        ];
    }
}
