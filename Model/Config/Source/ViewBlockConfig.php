<?php
declare(strict_types=1);

namespace Leanpay\Payment\Model\Config\Source;

use Leanpay\Payment\Helper\InstallmentHelper;
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
                'value' => 'DISABLED',
                'label' => __('Disabled in all views')
            ],
            [
                'value' => InstallmentHelper::LEANPAY_INSTALLMENT_VIEW_OPTION_HOMEPAGE,
                'label' => __('Homepage')
            ],
            [
                'value' => InstallmentHelper::LEANPAY_INSTALLMENT_VIEW_OPTION_PRODUCT_PAGE,
                'label' => __('Product')
            ],
            [
                'value' => InstallmentHelper::LEANPAY_INSTALLMENT_VIEW_OPTION_CATEGORY_PAGE,
                'label' => __('Category')
            ],
        ];
    }
}
