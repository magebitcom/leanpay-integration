<?php
declare(strict_types=1);

namespace Leanpay\Payment\Model\Method;

class LeanpayInstallment extends Leanpay
{
    public const CODE = 'leanpay_installment';

    /**
     * Payment system code
     *
     * @var string
     */
    protected $_code = self::CODE;
}
