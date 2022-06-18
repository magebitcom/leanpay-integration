<?php
declare(strict_types=1);

namespace Leanpay\Payment\Model\Config\Source;

use Leanpay\Payment\Helper\Data;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class VendorProductCode implements OptionSourceInterface
{

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => '',
                'label' => ''
            ],
            [
                'value' => Data::REGULAR_PRODUCT, //ad2e37e4-b626-429a-9bce-49480532a947
                'label' => __('Regular product up to 48 months')
            ],
            [
                'value' => Data::ZERO_APR, //166ede78-6556-47f5-bcdc-25ab57a7d6a1
                'label' => __('0% appreciation up to 24 months')
            ],
            [
                'value' => Data::ZERO_PERCENT, //6ebfd301-e22c-4382-a2bf-cb1d50d20aa2
                'label' => __('0% interest')
            ],
            [
                'value' => Data::HR_VENDOR_PRODUCT_ONE,
                'label' => __('0% appreciation up to 24 months (HR)')
            ],
            [
                'value' => Data::HR_VENDOR_PRODUCT_TWO,
                'label' => __('0% interest (HR)' )
            ],
        ];
    }
}
