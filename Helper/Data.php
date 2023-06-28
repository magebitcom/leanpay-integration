<?php

declare(strict_types=1);

namespace Leanpay\Payment\Helper;

use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Encryption\EncryptorInterface;
use Leanpay\Payment\Model\InstallmentProductRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;

class Data extends AbstractHelper
{

    public const LEANPAY_BASE_URL = 'https://app.leanpay.si/';
    public const LEANPAY_BASE_URL_HR = 'https://app.leanpay.hr/';
    public const LEANPAY_BASE_URL_DEV = 'https://lapp.leanpay.si/';
    public const LEANPAY_BASE_URL_DEV_HR = 'https://lapp.leanpay.hr/';
    public const LEANPAY_BASE_URL_RO = 'https://app.leanpay.ro/';
    public const LEANPAY_BASE_URL_DEV_RO = 'https://stage-app.leanpay.ro/';


    public const LEANPAY_CONFIG_CURRENCY = 'payment/leanpay/leanpay_currency';
    public const LEANPAY_CONFIG_API_ENDPOINT_TYPE = 'payment/leanpay/api_endpoint_type';

    public const API_ENDPOINT_SLOVENIA = 'SLO';
    public const API_ENDPOINT_ROMANIA = 'RON';
    public const API_ENDPOINT_CROATIA = 'CRO';
    public const API_ALLOWED_METHODS = [
        Data::API_ENDPOINT_CROATIA,
        Data::API_ENDPOINT_ROMANIA,
        Data::API_ENDPOINT_SLOVENIA
    ];
    /**
     *  Post Token URL
     *
     * http://static.leanpay.com/api-docs/docs.html#integration-steps-request-token-post
     */
    public const LEANPAY_TOKEN_URL = 'vendor/token';

    /**
     * Post checkout URL
     *
     * http://static.leanpay.com/api-docs/docs.html#integration-steps-checkout-page-post
     */
    public const LEANPAY_CHECKOUT_URL = 'vendor/checkout';

    public const LEANPAY_ALLOWED_BASE_URL = [
        self::API_ENDPOINT_SLOVENIA => self::LEANPAY_BASE_URL,
        self::API_ENDPOINT_CROATIA => self::LEANPAY_BASE_URL_HR,
        self::API_ENDPOINT_ROMANIA => self::LEANPAY_BASE_URL_RO
    ];
    public const LEANPAY_ALLOWED_BASE_URL_DEV = [
        self::API_ENDPOINT_SLOVENIA => self::LEANPAY_BASE_URL_DEV,
        self::API_ENDPOINT_CROATIA => self::LEANPAY_BASE_URL_DEV_HR,
        self::API_ENDPOINT_ROMANIA => self::LEANPAY_BASE_URL_DEV_RO
    ];


    /**
     * Leanpay installment URL
     *
     * https://docs.leanpay.com/api-integracija/API/custom/installment-plans-credit-calculation
     */
    public const LEANPAY_INSTALLMENT_URL_DEV = [
        'SLO' => 'https://lapp.leanpay.si/vendor/installment-plans',
        'CRO' => 'https://lapp.leanpay.hr/vendor/installment-plans',
        'RON' => 'https://stage-app.leanpay.ro/vendor/installment-plans',
    ];


    /**
     * Leanpay installment URL
     *
     * https://docs.leanpay.com/api-integracija/API/custom/installment-plans-credit-calculation
     */
    public const LEANPAY_INSTALLMENT_URL = [
        'SLO' => 'https://app.leanpay.si/vendor/installment-plans',
        'HRK' => 'https://app.leanpay.hr/vendor/installment-plans',
        'RON' => 'https://app.leanpay.ro/vendor/installment-plans'
    ];

    /**
     * Leanpay Magento 2 base url in config
     */
    public const LEANPAY_CONFIG_BASE_URL = 'payment/leanpay/leanpay_base_url';

    /**
     * Leanpay Magento 2 base_dev url in config
     */
    public const LEANPAY_CONFIG_BASE_URL_DEV = 'payment/leanpay/leanpay_base_url_dev';

    /**
     * Leanpay Magento 2 api key path in Database
     */
    public const LEANPAY_API_CONFIG_API_KEY_PATH = 'payment/leanpay/api_key';

    /**
     * Leanpay Magento 2 secret word path in Database
     */
    public const LEANPAY_API_CONFIG_SECRET_WORD_PATH = 'payment/leanpay/api_secret';

    /**
     * Leanpay Magento 1 description path in Database
     */
    public const LEANPAY_CONFIG_INSTRUCTIONS_PATH = 'payment/leanpay/instructions';

    /**
     * Leanpay Magento 1 secret word path in Database
     */
    public const LEANPAY_CONFIG_MODE_PATH = 'payment/leanpay/mode';

    /**
     * Leanpay Language configuration path
     */
    public const LEANPAY_CONFIG_LANG_PATH = 'payment/leanpay/language';

    /**
     * Leanpay is active configuration path
     */
    public const LEANPAY_IS_ACTIVE_PATH = 'payment/leanpay/active';

    /**
     * Successful redirect url
     */
    public const LEANPAY_SUCCESS_URL = 'leanpay/checkout/success';

    /**
     * Error redirect url
     */
    public const LEANPAY_ERROR_URL = 'leanpay/checkout/failure';

    /**
     * Magento Checkout url for redirect after failure
     */
    public const LEANPAY_MAGENTO_CHECKOUT_URL = 'payment/leanpay/checkout_url';

    /**
     * Magento Checkout url for redirect after failure
     */
    public const LEANPAY_MAGENTO_CHECKOUT_URL_A = 'payment/leanpay/checkout_url';

    /**
     * Mode types
     */
    public const LEANPAY_API_MODE_DEV = 'DEV_MODE';
    public const LEANPAY_API_MODE_LIVE = 'LIVE_MODE';

    /**
     * Multiple financing
     */
    public const LEANPAY_PROMOS_MFP_PRODUCT_NAME = 'leanpay_promos/mfp/product_name';
    public const LEANPAY_PROMOS_MFP_START_DATE = 'leanpay_promos/mfp/start_date';
    public const LEANPAY_PROMOS_MFP_END_DATE = 'leanpay_promos/mfp/end_date';
    public const LEANPAY_PROMOS_MFP_CART_SIZE = 'leanpay_promos/mfp/cart_size';
    public const LEANPAY_PROMOS_MFP_COUNTRY = 'leanpay_promos/mfp/product_country';


    /**
     * Leanpay responses from api
     *
     * @var array
     */
    protected $responseMap = [
        'PENDING' => Order::STATE_PENDING_PAYMENT,
        'SUCCESS' => Order::STATE_PROCESSING,
        'CANCELED' => Order::STATE_CANCELED,
        'EXPIRED' => Order::STATE_CANCELED
    ];

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $connection;

    private $catalogCategoryFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    private InstallmentProductRepository $installmentProductRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        Context $context,
        EncryptorInterface $encryptor,
        DateTime $dateTime,
        \Magento\Framework\App\ResourceConnection $connection,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        InstallmentProductRepository $installmentProductRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->connection = $connection;
        $this->dateTime = $dateTime;
        $this->encryptor = $encryptor;
        $this->catalogCategoryFactory = $categoryCollectionFactory;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->installmentProductRepository = $installmentProductRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        parent::__construct($context);
    }

    public function getStoreId(): int
    {
        return (int) $this->storeManager->getStore()->getId();
    }

    public function getCurrencyType(): string
    {
        $currencyType = $this->scopeConfig->getValue(
            self::LEANPAY_CONFIG_CURRENCY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );

        if (!$currencyType) {
            $currencyType = 'EUR';
        }

        return (string) $currencyType;
    }

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        $currencyType = $this->getApiType();

        if ($this->getEnvironmentMode() == self::LEANPAY_API_MODE_LIVE) {
            return (string) self::LEANPAY_ALLOWED_BASE_URL[$currencyType];
        }

        return (string) self::LEANPAY_ALLOWED_BASE_URL_DEV[$currencyType];
    }

    /**
     * Check if method is active
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return (bool) $this->scopeConfig->getValue(self::LEANPAY_IS_ACTIVE_PATH);
    }

    /**
     * Get Leanpay api key
     *
     * @return string
     */
    public function getLeanpayApiKey(): string
    {
        return (string) $this->encryptor->decrypt(
            $this->scopeConfig->getValue(
                self::LEANPAY_API_CONFIG_API_KEY_PATH,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->getStoreId()
            )
        );
    }

    /**
     * Get Leanpay api type
     *
     * @return string
     */
    public function getApiType(): string
    {
        return (string) $this->scopeConfig->getValue(
            self::LEANPAY_CONFIG_API_ENDPOINT_TYPE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * Get leanpay instructions
     *
     * @return string
     */
    public function getInstructions(): string
    {
        return (string) $this->scopeConfig->getValue(
            self::LEANPAY_CONFIG_INSTRUCTIONS_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * Convert leanpay api status to magento order status
     *
     * @param string $status
     *
     * @return string
     * @throws NotFoundException
     */
    public function convertLeanpayStatusToMagento($status): string
    {
        if (!isset($this->responseMap[$status])) {
            throw new NotFoundException(__('Can\'t Find {$status} status'));
        }

        return $this->responseMap[$status];
    }

    /**
     * Get leanpay server
     *
     * @return string
     */
    public function getCheckoutUrl(): string
    {
        return $this->getBaseUrl() . self::LEANPAY_CHECKOUT_URL;
    }

    /**
     * Generate token
     *
     * @param string $leanPayTransactionId
     * @param string $vendorTransactionId
     * @param float $amount
     * @param string $responseStatus
     *
     * @return string
     */
    public function generateMD5Secret($leanPayTransactionId, $vendorTransactionId, $amount, $responseStatus): string
    {
        $string = $leanPayTransactionId === null ? 'null' : $leanPayTransactionId;
        $string .= $vendorTransactionId;
        $string .= md5($this->getSecretWord());
        $string .= number_format($amount, 2, '.', '');
        $string .= $responseStatus;

        return md5($string);
    }

    /**
     * Get token URL
     *
     * @return string
     */
    public function getTokenUrl(): string
    {
        return $this->getBaseUrl() . self::LEANPAY_TOKEN_URL;
    }

    /**
     * Get Leanpay Language
     *
     * @return string
     */
    public function getLeanpayLanguage(): string
    {
        return (string) $this->scopeConfig->getValue(
            self::LEANPAY_CONFIG_LANG_PATH,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get secret word from DB
     *
     * @return string
     */
    private function getSecretWord(): string
    {
        return (string) $this->encryptor->decrypt(
            $this->scopeConfig->getValue(
                self::LEANPAY_API_CONFIG_SECRET_WORD_PATH,
                ScopeInterface::SCOPE_STORE,
                $this->getStoreId()
            )
        );
    }

    /**
     * Get environment mode
     *
     * @return string
     */
    private function getEnvironmentMode(): string
    {
        return (string) $this->scopeConfig->getValue(
            self::LEANPAY_CONFIG_MODE_PATH,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * @return string
     */
    public function getCountryCode(): string
    {
        return (string) $this->scopeConfig->getValue(
            self::LEANPAY_PROMOS_MFP_COUNTRY,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * Get Magento Checkout URL
     *
     * @return string
     */
    public function getMagentoCheckoutUrl(): string
    {
        return (string) $this->scopeConfig->getValue(
            self::LEANPAY_MAGENTO_CHECKOUT_URL,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * Get installment URL
     *
     * @return string
     */
    public function getInstallmentURL(): array
    {
        if ($this->getEnvironmentMode() == self::LEANPAY_API_MODE_LIVE) {
            return self::LEANPAY_INSTALLMENT_URL;
        }

        return self::LEANPAY_INSTALLMENT_URL_DEV;
    }

    /**
     * Get store url
     *
     * @param string $route
     * @param array $params
     *
     * @return string
     */
    public function createUrl($route, $params = []): string
    {
        return $this->_getUrl($route, $params);
    }

    /**
     * Get leanpay config data
     *
     * @param string $value
     *
     * @return mixed
     */
    public function getConfigData(string $value)
    {
        return $this->scopeConfig->getValue("payment/leanpay/{$value}");
    }

    /**
     * Check can I use
     *
     * @param string $country
     *
     * @return bool
     */
    public function canUseForCountry(string $country): bool
    {
        if ($this->getConfigData('billing_allowspecific') == 1) {
            $availableCountries = explode(',', $this->getConfigData('billing_specificcountry'));

            if (!in_array($country, $availableCountries)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Gets all unique api keys
     *
     * @return array
     */
    public function getAllLeanpayApiKeys(): array
    {
        $apiKeys = [];
        $stores = $this->storeManager->getStores();
        foreach ($stores as $store) {
            $apiKey = (string) $this->encryptor->decrypt(
                $this->scopeConfig->getValue(
                    self::LEANPAY_API_CONFIG_API_KEY_PATH,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $store->getId()
                )
            );
            if (!in_array($apiKey, $apiKeys)) {
                $endpointType = $this->scopeConfig->getValue(
                    self::LEANPAY_CONFIG_API_ENDPOINT_TYPE,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $store->getId()
                );
                $apiKeys[$endpointType] = ['key' => $apiKey, 'store_id' => $store->getId()];
            }
        }
        return $apiKeys;
    }

    /**
     * Get Leanpay Promos Name
     *
     * @return string
     */
    public function getLeanpayPromosMFPName(int $storeId = 0): string
    {
        if ($storeId) {
            return (string) $this->scopeConfig->getValue(
                self::LEANPAY_PROMOS_MFP_PRODUCT_NAME,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }

        return (string) $this->scopeConfig->getValue(self::LEANPAY_PROMOS_MFP_PRODUCT_NAME);
    }

    /**
     * Get Leanpay Promos Start Date
     *
     * @return string
     */
    public function getLeanpayPromosMFPStartDate(): string
    {
        return (string) $this->scopeConfig->getValue(self::LEANPAY_PROMOS_MFP_START_DATE);
    }

    /**
     * Get Leanpay Promos End Date
     *
     * @return string
     */
    public function getLeanpayPromosMFPEndDate(): string
    {
        return (string) $this->scopeConfig->getValue(self::LEANPAY_PROMOS_MFP_END_DATE);
    }

    /**
     * Get Leanpay Promos Cart Size
     *
     * @return string
     */
    public function getLeanpayPromosMFPCartSize(int $storeId = 0): string
    {
        if ($storeId) {
            return (string) $this->scopeConfig->getValue(
                self::LEANPAY_PROMOS_MFP_CART_SIZE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }

        return (string) $this->scopeConfig->getValue(self::LEANPAY_PROMOS_MFP_CART_SIZE);
    }

    /**
     * Get Leanpay Promos vendor code by selected product name
     *
     * @return string
     */
    public function getLeanpayPromosVendorCode(): string
    {
        $storeId = (int) $this->storeManager->getStore()->getId();

        return $this->getLeanpayPromosMFPName($storeId);
    }

    public function getProductPromoCode(ProductInterface $product = null)
    {
        if (!$product) {
            return '';
        }
        $data = [];
        $data[] = $product;

        return $this->getPromoCode($data);
    }


    /**
     * Need to be able to validate and undestand correct rule
     *
     * @return bool
     */
    public function getPromoCode($handler): string
    {
        if ((!$handler instanceof OrderInterface && !$handler instanceof CartInterface && !$handler instanceof Quote) xor is_array($handler)) {
            return '';
        }

        if ($productMatch = $this->validateProduct($handler)) {
            return $productMatch;
        }

        if ($categoryMatch = $this->validateCategory($handler)) {
            return $categoryMatch;
        }

        if ($this->validateGlobal($handler)) {
            return $this->getLeanpayPromosVendorCode();
        }

        return '';
    }

    /**
     * @param $handler
     * @return string
     */
    private function validateProduct($handler)
    {
        $validOption = [];
        $result = '';
        $productCount = 0;

        if (($handler instanceof OrderInterface || $handler instanceof CartInterface) xor is_array($handler)) {
            if (is_array($handler)) {
                $items = $handler;
            } else {
                $items = $handler->getItems();
            }

            $cache = [];
            foreach ($items as $item) {
                if (is_array($handler)) {
                    $product = $item;
                } else {
                    if ($item->getProductType() == 'simple') {
                        if ($item->getParentItem()) {
                            continue;
                        }
                        $product = $item->getProduct();
                    } else {
                        $product = $item->getProduct();
                    }
                }
                $productCount++;

                $vendorCode = $product->getData('leanpay_product_vendor_code');
                if (!$vendorCode) {
                    continue;
                }

                # Check if current product promo code is assigned to the current store api type
                if ($productInstalmments = $this->getProductInstallmentByVendorCode($vendorCode)) {
                    foreach ($productInstalmments as $productInstalmment) {
                        if ($productInstalmment->getData('country') != $this->getApiType()){
                            continue 2;
                        }
                    }
                }

                $incluse = $product->getData('leanpay_product_exclusive_inclusive') == 'inclusive' ? 1 : 0;
                $priority = $product->getData('leanpay_product_priority');
                $cache[$item->getProductId()] = $incluse;

                $end = strtotime($product->getData('leanpay_product_end_date') ?? '');
                $start = strtotime($product->getData('leanpay_product_start_date') ?? '');
                $isTime = $product->getData('leanpay_product_time_based');

                if ($isTime) {
                    $currentTime = strtotime($this->dateTime->gmtDate());
                    if ($start < $currentTime && $currentTime < $end) {
                        $validOption[$product->getId()] = [
                            'priority' => $priority,
                            'inclusive' => $incluse,
                            'code' => $vendorCode
                        ];
                    }
                } else {
                    $validOption[$product->getId()] = [
                        'priority' => $priority,
                        'inclusive' => $incluse,
                        'code' => $vendorCode
                    ];
                }

                if (sizeof($validOption) > 0) {
                    $allSame = true;
                    foreach ($validOption as $option) {
                        if (!reset($validOption)['code'] == $option['code']) {
                            $allSame = false;
                            break;
                        }
                    }
                }
            }
        }

        if (!empty($validOption)) {
            usort($validOption, function ($a, $b) {
                return ($a['priority'] > $b['priority']) ? -1 : 1;
            });

            // checks if there are two items or more and rule have exclusive only allowed
            if ($productCount > 1 && !$incluse) {
                if (!$allSame) {
                    $validOption = [];
                }
            }

            //check if two items with same priority colide
            if (sizeof($validOption) > 1 &&
                $validOption[0]['priority'] == $validOption[1]['priority'] &&
                $validOption[0]['inclusive'] == $validOption[1]['inclusive'] &&
                $validOption[0]['code'] != $validOption[1]['code']
            ) {
                $validOption = [];
            }

            foreach ($validOption as $option) {
                if ($option['inclusive']) {
                    $result = $option['code'];
                    break;
                } else {
                    $result = $option['code'];
                }
            }
        }

        return $result;
    }

    /**
     * @param $handler
     * @return string
     */
    private function validateCategory($handler)
    {
        $result = '';
        $validOption = [];
        $ids = [];
        if (($handler instanceof OrderInterface || $handler instanceof CartInterface) xor is_array($handler)) {
            if (is_array($handler)) {
                $items = $handler;
            } else {
                $items = $handler->getItems();
            }

            foreach ($items as $item) {
                $ids[] = $item->getProductId() ?? $item->getId();
            }

            if (!empty($ids)) {
                // To improve scaling of the program calculating all required cateogries to compare
                $ids = implode(',', array_unique($ids));
                $table = $this->connection->getTableName('catalog_category_product');
                $string = sprintf(
                    'select category_id from %s where product_id in (%s) group by category_id',
                    $table,
                    $ids
                );
                $categoriesToCompare = $this->connection->getConnection()->fetchCol($string);

                if (!empty($categoriesToCompare)) {
                    $collection = $this->catalogCategoryFactory->create();
                    $requiredAttributes = [
                        'leanpay_category_financing_product_value',
                        'leanpay_category_priority',
                        'leanpay_category_time_based',
                        'leanpay_category_start_date',
                        'leanpay_category_end_date',
                        'leanpay_category_vendor_code',
                        'leanpay_category_exclusive_inclusive'
                    ];
                    $categories = $collection
                        ->addIdFilter($categoriesToCompare)
                        ->addAttributeToSelect($requiredAttributes)
                        ->setStoreId($this->getStoreId())
                        ->getItems();
                    if(empty($categories)) {
                        $categories = $collection->addIdFilter($categoriesToCompare)
                            ->addAttributeToSelect($requiredAttributes)
                            ->addAttributeToFilter('leanpay_category_vendor_code', ['neq' => 'NULL'])
                            ->getItems();
                    }
                    if (!empty($categories)) {
                        foreach ($categories as $category) {
                            if (empty($category->getData('leanpay_category_vendor_code'))) {
                                continue;
                            }
                            $categoryStart = strtotime($category->getData('leanpay_category_start_date') ?? '');
                            $categoryEnd = strtotime($category->getData('leanpay_category_end_date') ?? '');
                            $categoryIsTime = $category->getData('leanpay_category_time_based');
                            $categoryPriority = $category->getData('leanpay_category_priority');
                            $categoryIsExclusive = $category->getData(
                                'leanpay_category_exclusive_inclusive'
                            ) == 'inclusive' ? true : false;
                            $categoryVendorProduct = $category->getData('leanpay_category_vendor_code');
                            $currentTime = strtotime($this->dateTime->gmtDate() ?? '');
                            if ($categoryIsTime) {
                                if ($categoryStart < $currentTime && $categoryEnd > $currentTime) {
                                    $validOption[] = [
                                        'priority' => $categoryPriority,
                                        'inclusive' => $categoryIsExclusive,
                                        'code' => $categoryVendorProduct
                                    ];
                                }
                            } else {
                                $validOption[] = [
                                    'priority' => $categoryPriority,
                                    'inclusive' => $categoryIsExclusive,
                                    'code' => $categoryVendorProduct
                                ];
                            }
                        }
                        if (!empty($validOption) && is_array($validOption)) {
                            usort($validOption, function ($a, $b) {
                                return ($a['priority'] > $b['priority']) ? -1 : 1;
                            });

                            foreach ($validOption as $item) {
                                $result = $item['code'];
                                break;
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param $handler
     * @return bool
     */
    private function validateGlobal($handler)
    {
        try {
            $storeId = (int) $this->storeManager->getStore()->getId();
            $requiredSize = $this->getLeanpayPromosMFPCartSize($storeId) ?? 0;
            $requiredDateStart = strtotime($this->getLeanpayPromosMFPStartDate());
            $requiredDateEnd = strtotime($this->getLeanpayPromosMFPEndDate());
            $currentTime = strtotime($this->dateTime->gmtDate());

            // Invalid due to date
            if ($currentTime < $requiredDateStart || $currentTime > $requiredDateEnd) {
                return false;
            }

            $amount = 0.00;

            if ($handler instanceof OrderInterface || $handler instanceof CartInterface) {
                $amount = (float) $handler->getStore()->getBaseCurrency()->convert(
                    $handler->getBaseGrandTotal(),
                    'EUR'
                );
            }

            if ($amount < $requiredSize) {
                return false;
            }

            return true;
        } catch (Exception $exception) {
            return false;
        }
    }
    function getProductInstallmentByVendorCode(string $vendorCode) {
        $searchCriteriaBuilder = $this->searchCriteriaBuilder;
        $searchCriteria = $searchCriteriaBuilder->addFilter(
            'group_id',
            $vendorCode,
            'eq'
        )->create();

        $result = $this->installmentProductRepository->getList($searchCriteria)->getItems();

        if (empty($result)){
            return null;
        }

        return $result;
    }
}
