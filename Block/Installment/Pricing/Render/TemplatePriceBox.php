<?php

namespace Leanpay\Payment\Block\Installment\Pricing\Render;

use Leanpay\Payment\Helper\Data;
use Leanpay\Payment\Helper\InstallmentHelper;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template;

/**
 * Class TemplatePriceBox
 *
 * @package Leanpay\Payment\Block\Installment\Pricing\Render
 */
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
     * @return float
     */
    public function getAmount()
    {
        return $this->getData('amount');
    }

    /**
     * @return string
     */
    public function getCacheKey()
    {
        return parent::getCacheKey() . ($this->getData('amount'));
    }

    /**
     * @return string
     */
    public function getLowestInstallmentPrice(): string
    {
        return $this->installmentHelper->getLowestInstallmentPrice($this->getAmount());
    }

    /**
     * @return InstallmentHelper
     */
    public function getInstallmentHelper(): InstallmentHelper
    {
        return $this->installmentHelper;
    }

    /**
     * @return Data
     */
    public function getHelper(): Data
    {
        return $this->helper;
    }

    /**
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
     * @return string
     */
    public function getJsonConfig()
    {
        $list = $this->installmentHelper->getInstallmentList($this->getAmount());
        $list = array_values($list);
        $values = [];

        for ($index = 0; $index < count($list); $index++) {
            $values[] = $index;
        }

        $data = [
            'min' => array_key_first($list),
            'max' => array_key_last($list),
            'data' => $list,
            'value' => $values
        ];

        return (string) $this->serializer->serialize($data);
    }
}
