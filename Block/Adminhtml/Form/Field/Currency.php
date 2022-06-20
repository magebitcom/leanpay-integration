<?php
declare(strict_types=1);

namespace Leanpay\Payment\Block\Adminhtml\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Directory\Model\Currency as CurrencyModel;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Translate\Adapter;
use Leanpay\Payment\Helper\Data as PaymentData;

class Currency extends Field
{
    /**
     * @var CurrencyModel
     */
    protected $currencyModel;

    /**
     * @var Adapter
     */
    protected $translator;

    /**
     * @var PaymentData
     */
    protected $helper;

    /**
     * Currency constructor.
     *
     * @param Adapter $translator
     * @param Context $context
     * @param CurrencyModel $currencyModel
     * @param PaymentData $helper
     * @param array $data
     */
    public function __construct(
        Adapter $translator,
        Context $context,
        CurrencyModel $currencyModel,
        PaymentData $helper,
        array $data = []
    ) {
        $this->translator = $translator;
        $this->currencyModel = $currencyModel;
        $this->helper = $helper;

        parent::__construct($context, $data);
    }

    /**
     * Configurations get EUR rate.
     *
     * @param AbstractElement $element
     * @return string
     *
     * @throws NoSuchEntityException|LocalizedException
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        $baseCode = $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
        $allowedCurrencies = $this->currencyModel->getConfigAllowCurrencies();
        $rates = $this->currencyModel->getCurrencyRates($baseCode, array_values($allowedCurrencies));
        $currency = $this->helper->getCurrencyType();

        if (!array_key_exists($currency, $rates)) {
            return $this->translator->translate(
                '<span style="color: red; font-size: 14px;"><b>Please set up Currency rate for HRK</b></span>'
            );
        }

        return sprintf(__('<div style="font-size:14px;">1 %1$s equals %3$s %2$f</div>'), $baseCode, number_format((float)$rates[$currency], 4), $currency);
    }
}
