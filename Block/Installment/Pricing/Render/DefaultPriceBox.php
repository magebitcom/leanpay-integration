<?php

namespace Leanpay\Payment\Block\Installment\Pricing\Render;

use Leanpay\Payment\Api\Data\InstallmentProductInterface;
use Leanpay\Payment\Api\InstallmentProductRepositoryInterface;
use Leanpay\Payment\Helper\Data;
use Leanpay\Payment\Helper\InstallmentHelper;
use Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolverInterface;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Catalog\Pricing\Price\MinimalPriceCalculatorInterface;
use Magento\Catalog\Pricing\Render\FinalPriceBox;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\Render\RendererPool;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;

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
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteria;

    /**
     * @var InstallmentProductRepositoryInterface
     */
    private $productRepo;

    /**
     * DefaultPriceBox constructor.
     * @param StoreManagerInterface $storeManager
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
        StoreManagerInterface $storeManager,
        InstallmentProductRepositoryInterface  $productRepository,
        SearchCriteriaBuilderFactory $criteriaBuilderFactory,
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
        $this->productRepo = $productRepository;
        $this->searchCriteria = $criteriaBuilderFactory->create();
        $this->storeManager = $storeManager;
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
     * Retreves financila product, used in all view except checkout place order
     *
     * @return string
     */
    public function getFinancialProduct(): string
    {
        try {
            return $this->helper->getProductPromoCode($this->getSaleableItem());
        } catch (LocalizedException $exception) {
            return '';
        }
    }

    /**
     * Returns amount
     *
     * @return float
     */
    public function getAmount(): float
    {
        $amount = $this->getSaleableItem()
            ->getPriceInfo()
            ->getPrice(FinalPrice::PRICE_CODE)
            ->getAmount()
            ->getValue();

        if ($this->storeManager->getStore()->getCurrentCurrency()->getCode() === "HRK") {
           $amount = (float)$this->installmentHelper->getTransitionPriceHkrToEur((string)$amount);
        }

        return $amount;
    }

    /**
     * @param string $code
     * @return string|void
     */
    public function getInstallmentVendorName(string $code = ''){
        if (!$code){
            return '';
        }

        try{
            $search = $this->searchCriteria->addFilter(InstallmentProductInterface::GROUP_ID, $code)
                ->setPageSize(1)
                ->setCurrentPage(1)
                ->create();

            $items = $this->productRepo->getList($search)->getItems();

            if (empty($items)){
                return '';
            }

            foreach ($items as $item){
                return $item->getData(InstallmentProductInterface::GROUP_NAME);
            }

        }catch (LocalizedException $exception){
            return '';
        }
    }

    /**
     * Returns lowest installment price
     *
     * @return string
     */
    public function getLowestInstallmentPrice(string $group = ''): string
    {
        return $this->installmentHelper->getLowestInstallmentPrice($this->getAmount(), $group);
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
     * Get Key for caching block content
     *
     * @return string
     */
    public function getCacheKey()
    {
        return parent::getCacheKey() . ($this->getData('view_key'));
    }

}
