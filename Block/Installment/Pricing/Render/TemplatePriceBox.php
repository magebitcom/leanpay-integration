<?php

namespace Leanpay\Payment\Block\Installment\Pricing\Render;

use Leanpay\Payment\Helper\Data;
use Leanpay\Payment\Helper\InstallmentHelper;
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
     * TemplatePriceBox constructor.
     *
     * @param Template\Context $context
     * @param InstallmentHelper $installmentHelper
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        InstallmentHelper  $installmentHelper,
        Data $helper,
        array $data = []
    ) {
        $this->installmentHelper = $installmentHelper;
        $this->helper = $helper;
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
    public function getLogo(): string
    {
        if (!$this->installmentHelper->isDarkThemeLogo()) {
            return $this->getViewFileUrl('Leanpay_Payment::images/leanpay.svg');
        }

        return $this->getViewFileUrl('Leanpay_Payment::images/dark-leanpay.svg');
    }
}
