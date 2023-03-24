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
     * Currency constructor.
     *
     * @param Adapter $translator
     * @param Context $context
     * @param CurrencyModel $currencyModel
     * @param array $data
     */
    public function __construct(
        Adapter $translator,
        Context $context,
        CurrencyModel $currencyModel,
        array $data = []
    ) {
        $this->translator = $translator;
        $this->currencyModel = $currencyModel;

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

        if (!array_key_exists('EUR', $rates)) {
            return $this->translator->translate(
                '<span style="color: red;"><b>Please set up Currency rate for EUR</b></span>'
            );
        }

        return sprintf('1 %1$s equals %3$s %2$f', $baseCode, number_format((float)$rates['EUR'], 4), 'EUR');
    }
}
