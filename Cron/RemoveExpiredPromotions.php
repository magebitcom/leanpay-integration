<?php
declare(strict_types=1);

namespace Leanpay\Payment\Cron;

use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Psr\Log\LoggerInterface;

class RemoveExpiredPromotions
{
    /**
     * Leanpay Product Attributes
     */
    const LEANPAY_PRODUCT_PRIORITY = 'leanpay_product_priority';
    const LEANPAY_PRODUCT_TIME_BASED = 'leanpay_product_time_based';
    const LEANPAY_PRODUCT_START_DATE = 'leanpay_product_start_date';
    const LEANPAY_PRODUCT_END_DATE = 'leanpay_product_end_date';
    const LEANPAY_PRODUCT_VENDOR_CODE = 'leanpay_product_vendor_code';
    const LEANPAY_PRODUCT_EXCLUSIVE_INCLUSIVE = 'leanpay_product_exclusive_inclusive';
    private StoreRepositoryInterface $storeRepository;
    private ProductRepositoryInterface $productRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    private LoggerInterface $logger;

    public function __construct(StoreRepositoryInterface $storeRepository,
                                ProductRepositoryInterface $productRepository,
                                SearchCriteriaBuilder $searchCriteriaBuilder,
                                LoggerInterface $logger)
    {
        $this->storeRepository = $storeRepository;
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger = $logger;
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        $allStores = $this->storeRepository->getList();
        $storeIds = [];
        foreach ($allStores as $store) {
            $storeIds[] = $store->getId();
        }

        try {
            foreach ($storeIds as $storeId) {
                $searchCriteria = $this->searchCriteriaBuilder
                    ->addFilter('store_id', $storeId)
                    ->addFilter(self::LEANPAY_PRODUCT_END_DATE, date('Y-m-d'), 'lt')
                    ->create();
                $products = $this->productRepository->getList($searchCriteria)->getItems();
                foreach ($products as $product) {
                    $this->resetLeanpayProductAttributes($product, $storeId);
                }
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * @param $product
     * @param $storeId
     * @return void
     */
    private function resetLeanpayProductAttributes($product, $storeId): void
    {
        $product->setStoreId($storeId);
        $product->setData(self::LEANPAY_PRODUCT_PRIORITY, null);
        $product->setData(self::LEANPAY_PRODUCT_TIME_BASED, null);
        $product->setData(self::LEANPAY_PRODUCT_START_DATE, null);
        $product->setData(self::LEANPAY_PRODUCT_END_DATE, null);
        $product->setData(self::LEANPAY_PRODUCT_VENDOR_CODE, null);
        $product->setData(self::LEANPAY_PRODUCT_EXCLUSIVE_INCLUSIVE, null);
        $product->save();
    }
}
