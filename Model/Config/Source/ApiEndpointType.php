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


class ApiEndpointType implements OptionSourceInterface
{
    /**
     * Return array of leanpay API endpoint types
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => 'SLO',
                'label' => __('SLO')
            ],
            [
                'value' => 'CRO',
                'label' => __('CRO')
            ],
            [
                'value' => 'RON',
                'label' => __('RON')
            ],
        ];
    }
}
