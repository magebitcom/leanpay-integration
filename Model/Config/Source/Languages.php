<?php
/**
 * This file is part of the Leanpay_Payment package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Leanpay_Payment
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2019 Magebit, Ltd. (https://magebit.com/)
 * @license   GNU General Public License ("GPL") v3.0
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Leanpay\Payment\Model\Config\Source;


/**
 * Class Languages
 * @package Leanpay\Payment\Model\Config\Source
 */
class Languages implements ArrayInterface
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
                'value' => 'sl',
                'label' => __('Slovenian')
            ],
            [
                'value' => 'en',
                'label' => __('English')
            ],
        ];
    }
}