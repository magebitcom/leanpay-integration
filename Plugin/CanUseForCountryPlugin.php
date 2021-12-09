<?php
declare(strict_types=1);

namespace Leanpay\Payment\Plugin;

use Leanpay\Payment\Helper\Data;
use Leanpay\Payment\Model\Method\Leanpay;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Payment\Model\Checks\CanUseForCountry;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;

class CanUseForCountryPlugin
{
    /**
     * @var DirectoryHelper
     */
    protected $directoryHelper;

    /**
     * @var Data
     */
    protected $data;

    /**
     * @param DirectoryHelper $directoryHelper
     * @param Data $data
     */
    public function __construct(DirectoryHelper $directoryHelper, Data $data)
    {
        $this->directoryHelper = $directoryHelper;
        $this->data = $data;
    }

    /**
     * Check whether payment method is applicable to quote
     *
     * @param CanUseForCountry $subject
     * @param callable $proceed
     * @param MethodInterface $paymentMethod
     * @param Quote $quote
     *
     * @return bool
     */
    public function aroundIsApplicable(
        CanUseForCountry $subject,
        callable $proceed,
        MethodInterface $paymentMethod,
        Quote $quote
    ) {
        if ($paymentMethod->getCode() != Leanpay::CODE) {
            return $proceed($paymentMethod, $quote);
        }

        return
            $this->data->canUseForCountry($this->billingAddress($quote->getBillingAddress())) &&
            $paymentMethod->canUseForCountry($this->billingAddress($quote->getShippingAddress()));
    }

    /**
     * Get billing address
     *
     * @param Address $address
     * @return int|string
     */
    protected function billingAddress(Address $address)
    {
        return (!empty($address) && !empty($address->getCountry()))
            ? $address->getCountry()
            : $this->directoryHelper->getDefaultCountry();
    }
}
