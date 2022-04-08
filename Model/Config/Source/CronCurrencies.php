<?php


namespace leanpay\payment\Model\Config\Source;


use Leanpay\Payment\Helper\Data;
use Magento\Framework\Data\OptionSourceInterface;

class CronCurrencies implements OptionSourceInterface
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
                'value' => 'EUR',
                'label' => 'EUR'
            ],

            [
                'value' => 'HKR',
                'label' => 'HKR'
            ],
        ];
    }
}
