<?php
declare(strict_types=1);

namespace Leanpay\Payment\Pricing\Price;

use Magento\Framework\Pricing\Price\AbstractPrice;

class Installment extends AbstractPrice
{
    public const PRICE_CODE = 'installment_price';

    /**
     * Function get to get value
     *
     * @return void
     */
    public function getValue()
    {
    }
}
