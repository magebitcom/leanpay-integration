<?php

namespace Leanpay\Payment\Block\Installment\Pricing\Render;

use Leanpay\Payment\Helper\Data;
use Leanpay\Payment\Helper\InstallmentHelper;
use Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolverInterface;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Catalog\Pricing\Price\MinimalPriceCalculatorInterface;
use Magento\Catalog\Pricing\Render\FinalPriceBox;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\Render\RendererPool;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template\Context;
use Leanpay\Payment\Api\Data\InstallmentInterface;

class DefaultPriceBox extends FinalPriceBox
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
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * DefaultPriceBox constructor.
     *
     * @param Context $context
     * @param SaleableInterface $saleableItem
     * @param PriceInterface $price
     * @param RendererPool $rendererPool
     * @param Data $helper
     * @param InstallmentHelper $installmentHelper
     * @param SerializerInterface $serializer
     * @param array $data
     * @param SalableResolverInterface|null $salableResolver
     * @param MinimalPriceCalculatorInterface|null $minimalPriceCalculator
     */
    public function __construct(
        Context $context,
        SaleableInterface $saleableItem,
        PriceInterface $price,
        RendererPool $rendererPool,
        Data $helper,
        InstallmentHelper $installmentHelper,
        SerializerInterface $serializer,
        array $data = [],
        SalableResolverInterface $salableResolver = null,
        MinimalPriceCalculatorInterface $minimalPriceCalculator = null
    ) {
        $this->helper = $helper;
        $this->installmentHelper = $installmentHelper;
        $this->serializer = $serializer;
        parent::__construct(
            $context,
            $saleableItem,
            $price,
            $rendererPool,
            $data,
            $salableResolver,
            $minimalPriceCalculator
        );
    }

    /**
     * Returns amount
     *
     * @return float
     */
    public function getAmount(): float
    {
        return $this->getSaleableItem()
            ->getPriceInfo()
            ->getPrice(FinalPrice::PRICE_CODE)
            ->getAmount()
            ->getValue();
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
    public function getLogo(): string
    {
        if (!$this->installmentHelper->isDarkThemeLogo()) {
            return $this->getViewFileUrl('Leanpay_Payment::images/leanpay.svg');
        }

        return $this->getViewFileUrl('Leanpay_Payment::images/dark-leanpay.svg');
    }

    /**
     * Returns JSON config
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
            'currency' => $this->installmentHelper->getCurrencyCode(),
        ];

        if ($this->installmentHelper->getCurrency() === 'HRK') {
            $convertedValues = [];
            foreach ($list as $value) {
                $convertedValues[] = $this->installmentHelper->getTransitionPrice($value[InstallmentInterface::INSTALLMENT_AMOUNT]);
            }
            $data['convertedCurrency'] = 'EUR';
            $data['convertedValues'] = $convertedValues;
        }

        return (string) $this->serializer->serialize($data);
    }

    /**
     * Get Key for caching block content
     *
     * @return string
     */
    public function getCacheKey()
    {
        return parent::getCacheKey() . ($this->getData('view_key'));
    }

    public function getCategoryPriceBlock(): string
    {
        $price = $this->getLowestInstallmentPrice();
        if ($this->installmentHelper->getCurrency() === 'HRK') {
            return $this->_escaper->escapeHtml(__(
                'od %1 %2 / %3 %4 mjesečno',
                $price,
                $this->installmentHelper->getCurrencyCode(),
                $this->installmentHelper->getTransitionPrice($price),
                'EUR'
            ));
        }
        return $this->_escaper->escapeHtml(
            __('ali od %1 %2 / mesec', $price, $this->installmentHelper->getCurrencyCode())
        );
    }

    public function getProductPriceBlock(): string
    {
        $price = $this->getLowestInstallmentPrice();
        if ($this->installmentHelper->getCurrency() === 'HRK') {
           return $this->_escaper->escapeHtml(
               __('%1 %2 / %3 %4',
                   $price,
                   $this->installmentHelper->getCurrencyCode(),
                   $this->installmentHelper->getTransitionPrice($price),
                   'EUR'
               )
           );
        }
        return $this->_escaper->escapeHtml(
            __('%1 %2', $price, $this->installmentHelper->getCurrencyCode())
        );
    }

    public function getTooltipPriceBlock($useTerm = false): string
    {
        $data = $this->installmentHelper->getToolTipData($this->getAmount(), $useTerm);
        $term = $data[InstallmentInterface::INSTALLMENT_PERIOD];
        $amount = $data[InstallmentInterface::INSTALLMENT_AMOUNT];
        if ($this->installmentHelper->getCurrency() === 'HRK') {
            return $this->_escaper->escapeHtml(
                __('%1 x %2%3 / %4%5',
                    $term,
                    $amount,
                    $this->installmentHelper->getCurrencyCode(),
                    $this->installmentHelper->getTransitionPrice($amount),
                    'EUR'
                )
            );
        }
        return $this->_escaper->escapeHtml(__('%1 x %2%3', $term, $amount, $this->installmentHelper->getCurrencyCode()));
    }

    public function shouldRenderTooltipPriceBlock($useTerm = false): bool
    {
        $data = $this->installmentHelper->getToolTipData($this->getAmount(), $useTerm);
        return isset(
            $data[InstallmentInterface::INSTALLMENT_AMOUNT],
            $data[InstallmentInterface::INSTALLMENT_PERIOD]
        );
    }

}
