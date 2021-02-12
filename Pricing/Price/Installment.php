<?php
declare(strict_types=1);

namespace Leanpay\Payment\Pricing\Price;

use Magento\Framework\Pricing\Price\AbstractPrice;

/**
 * Class Installment
 *
 * @package Leanpay\Payment\Pricing\Price
 */
class Installment extends AbstractPrice
{
    const PRICE_CODE = 'installment_price';

    /**
     * @return void
     */
    public function getValue()
    {
    }
}
