<?php

namespace Leanpay\Payment\Block\Adminhtml\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Directory\Model\Currency as CurrencyModel;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Currency extends Field
{
    /**
     * @var CurrencyModel
     */
    protected $currencyModel;

    /**
     * Currency constructor.
     * @param Context $context
     * @param CurrencyModel $currencyModel
     * @param array $data

     */
    public function __construct(
        Context $context,
        CurrencyModel $currencyModel,
        array $data = []
    )
    {
        $this->currencyModel = $currencyModel;

        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        $baseCode = $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
        $allowedCurrencies = $this->currencyModel->getConfigAllowCurrencies();
        $rates = $this->currencyModel->getCurrencyRates($baseCode, array_values($allowedCurrencies));

        if (!array_key_exists('EUR', $rates)) {
            return _('<span style="color: red;"><b>Please set up Currency rate for EUR</b></span>');
        }

        return sprintf(__('1 %1$s equals %3$s %2$f'), $baseCode, number_format($rates['EUR'], 4), 'EUR');
    }
}