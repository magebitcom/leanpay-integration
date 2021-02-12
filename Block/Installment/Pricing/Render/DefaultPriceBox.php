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
use Magento\Framework\View\Element\Template\Context;

/**
 * Class DefaultPriceBox
 *
 * @package Leanpay\Payment\Block\Installment\Pricing\Render
 */
class DefaultPriceBox extends FinalPriceBox
{
    /**
     * @var InstallmentHelper
     */
    private InstallmentHelper $installmentHelper;

    /**
     * @var Data
     */
    private Data $helper;

    /**
     * DefaultPriceBox constructor.
     *
     * @param Context $context
     * @param SaleableInterface $saleableItem
     * @param PriceInterface $price
     * @param RendererPool $rendererPool
     * @param Data $helper
     * @param InstallmentHelper $installmentHelper
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
        array $data = [],
        SalableResolverInterface $salableResolver = null,
        MinimalPriceCalculatorInterface $minimalPriceCalculator = null
    ) {
        $this->helper = $helper;
        $this->installmentHelper = $installmentHelper;
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
     * @return string
     */
    public function getLowestInstallmentPrice(): string
    {
        $amount = $this->getSaleableItem()
            ->getPriceInfo()
            ->getPrice(FinalPrice::PRICE_CODE)
            ->getAmount()
            ->getValue();

        return $this->installmentHelper->getLowestInstallmentPrice($amount);
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
}
