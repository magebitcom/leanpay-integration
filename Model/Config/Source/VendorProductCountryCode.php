<?php
declare(strict_types=1);

namespace Leanpay\Payment\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class VendorProductCountryCode implements OptionSourceInterface
{

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'si',
                'label' => __('Slovenia')
            ],
            [
                'value' => 'hr',
                'label' => __('Croatia')
            ]
        ];
    }
}
