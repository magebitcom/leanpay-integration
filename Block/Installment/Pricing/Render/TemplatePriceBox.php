<?php

namespace Leanpay\Payment\Block\Installment\Pricing\Render;

use Leanpay\Payment\Helper\Data;
use Leanpay\Payment\Helper\InstallmentHelper;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template;

class TemplatePriceBox extends Template
{
    /**
     * @var InstallmentHelper
     */
    private $installmentHelper;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var string
     */
    protected $_template = 'Leanpay_Payment::pricing/render/installment.phtml';

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * TemplatePriceBox constructor.
     *
     * @param Template\Context $context
     * @param InstallmentHelper $installmentHelper
     * @param Data $helper
     * @param SerializerInterface $serializer
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        InstallmentHelper  $installmentHelper,
        Data $helper,
        SerializerInterface $serializer,
        array $data = []
    ) {
        $this->installmentHelper = $installmentHelper;
        $this->helper = $helper;
        $this->serializer = $serializer;
        $data['view_key'] = InstallmentHelper::LEANPAY_INSTALLMENT_VIEW_OPTION_PRODUCT_PAGE;
        parent::__construct($context, $data);
    }

    /**
     * Returns amount
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->getData('amount');
    }

    /**
     * Returns cache key
     *
     * @return string
     */
    public function getCacheKey()
    {
        return parent::getCacheKey() . ($this->getData('amount'));
    }

    /**
     * Returns lowest installment price
     *
     * @return string
     */
    public function getLowestInstallmentPrice(): string
    {
        return $this->installmentHelper->getLowestInstallmentPrice($this->getAmount());
    }

    /**
     * Returns installment helper
     *
     * @return InstallmentHelper
     */
    public function getInstallmentHelper(): InstallmentHelper
    {
        return $this->installmentHelper;
    }

    /**
     * Returns price box helper
     *
     * @return Data
     */
    public function getHelper(): Data
    {
        return $this->helper;
    }

    /**
     * Returns logo
     *
     * @return string
     */
    public function getLogo()
    {
        if ($this->getData('is_checkout')) {
            return $this->getViewFileUrl('Leanpay_Payment::images/checkout.svg');
        }

        if (!$this->installmentHelper->isDarkThemeLogo()) {
            return $this->getViewFileUrl('Leanpay_Payment::images/leanpay.svg');
        }

        return $this->getViewFileUrl('Leanpay_Payment::images/dark-leanpay.svg');
    }

    /**
     * Returns Json config
     *
     * @return string
     */
    public function getJsonConfig()
    {
        $list = $this->installmentHelper->getInstallmentList($this->getAmount());
        $list = array_values($list);
        $values = [];
        $listLength = count($list);
        for ($index = 0; $index < $listLength; $index++) {
            $values[] = $index;
        }

        $data = [
            'min' => array_key_first($list),
            'max' => array_key_last($list),
            'data' => $list,
            'value' => $values,
            'currency' => $this->installmentHelper->getCurrencyCode()
        ];

        return (string) $this->serializer->serialize($data);
    }
}
