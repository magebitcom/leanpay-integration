<?php
/**
 * @author Magebit <info@magebit.com>
 * @copyright Copyright (c) Magebit, Ltd. (https://magebit.com)
 * @license https://magebit.com/code-license
 */

declare(strict_types=1);

namespace Leanpay\Payment\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ColorTheme implements OptionSourceInterface
{
    public const DEFAULT = 'default';
    public const ORANGE = 'orange';
    public const LIGHT = 'light';
    public const DARK = 'dark';

    /**
     * Return available color theme options
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => self::DEFAULT, 'label' => __('Default')],
            ['value' => self::ORANGE, 'label' => __('Orange')],
            ['value' => self::LIGHT, 'label' => __('Light')],
            ['value' => self::DARK, 'label' => __('Dark')],
        ];
    }
}
