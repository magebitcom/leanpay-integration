<?php
declare(strict_types=1);

namespace Leanpay\Payment\Plugin;

use Leanpay\Payment\Helper\Data;
use Leanpay\Payment\Model\Method\Leanpay;
use Magento\Payment\Model\Checks\TotalMinMax;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Model\Quote;

class TotalMinMaxPlugin
{
    protected $leanpayHelper;

    public function __construct(
        Data $leanpayHelper
    ) {
        $this->leanpayHelper = $leanpayHelper;
    }

    /**
     * Adds extra check to isApplicable
     *
     * @param TotalMinMax $subject
     * @param callable $proceed
     * @param MethodInterface $paymentMethod
     * @param Quote $quote
     * @return bool
     * @throws \Exception
     */
    public function aroundIsApplicable(
        TotalMinMax $subject,
        callable $proceed,
        MethodInterface $paymentMethod,
        Quote $quote
    ) {
        if ($paymentMethod->getCode() != Leanpay::CODE) {
            return $proceed($paymentMethod, $quote);
        }

        $total = $quote->getStore()->getBaseCurrency()->convert(
            $total = $quote->getBaseGrandTotal(), $this->leanpayHelper->getCurrencyType()
        );
        $minTotal = $paymentMethod->getConfigData(TotalMinMax::MIN_ORDER_TOTAL);
        $maxTotal = $paymentMethod->getConfigData(TotalMinMax::MAX_ORDER_TOTAL);

        if (!empty($minTotal) && $total < $minTotal || !empty($maxTotal) && $total > $maxTotal) {
            return false;
        }

        return true;
    }
}
