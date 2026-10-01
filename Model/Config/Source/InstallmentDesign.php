<?php
/**
 * @author Magebit <info@magebit.com>
 * @copyright Copyright (c) Magebit, Ltd. (https://magebit.com)
 * @license https://magebit.com/code-license
 */

declare(strict_types=1);

namespace Leanpay\Payment\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class InstallmentDesign implements OptionSourceInterface
{
    public const DEFAULT = 'default';
    public const NARROW = 'narrow';

    /**
     * Return available installment widget design options
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => self::DEFAULT, 'label' => __('Default')],
            ['value' => self::NARROW, 'label' => __('Narrow')],
        ];
    }
}
