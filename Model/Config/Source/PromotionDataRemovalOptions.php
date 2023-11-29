<?php
declare(strict_types=1);

namespace Leanpay\Payment\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * PromotionDataRemovalOptions
 */
class PromotionDataRemovalOptions implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'daily', 'label' => __('Daily')],
        ];
    }
}
