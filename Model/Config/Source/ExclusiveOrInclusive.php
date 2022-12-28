<?php

namespace Leanpay\Payment\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class ExclusiveOrInclusive extends AbstractSource
{
    
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                [
                    'label' => __('Please Select'),
                    'value' => null
                ],
                [
                    'label' => __('Exclusive'),
                    'value' => 'exclusive'
                ],
                [
                    'label' => __('Inclusive'),
                    'value' => 'inclusive'
                ]
            ];
        }
        return $this->_options;
    }
}
