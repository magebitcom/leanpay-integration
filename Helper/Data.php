<?php
declare(strict_types=1);

namespace Leanpay\Payment\Helper;

use Exception;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Encryption\EncryptorInterface;

class Data extends AbstractHelper
{
    /**
     *  Post Token URL
     *
     * http://static.leanpay.com/api-docs/docs.html#integration-steps-request-token-post
     */
    public const LEANPAY_TOKEN_URL_DEV = 'https://lapp.leanpay.si/vendor/token';

    /**
     * Post checkout URL
     *
     * http://static.leanpay.com/api-docs/docs.html#integration-steps-checkout-page-post
     */
    public const LEANPAY_CHECKOUT_URL_DEV = 'https://lapp.leanpay.si/vendor/checkout';

    /**
     * Leanpay installment URL
     *
     * https://docs.leanpay.com/api-integracija/API/custom/installment-plans-credit-calculation
     */
    public const LEANPAY_INSTALLMENT_URL_DEV = 'https://lapp.leanpay.si/vendor/installment-plans';

    /**
     *  Post Token URL
     *
     * http://static.leanpay.com/api-docs/docs.html#integration-steps-request-token-post
     */
    public const LEANPAY_TOKEN_URL = 'https://app.leanpay.si/vendor/token';

    /**
     * Post checkout URL
     *
     * http://static.leanpay.com/api-docs/docs.html#integration-steps-checkout-page-post
     */
    public const LEANPAY_CHECKOUT_URL = 'https://app.leanpay.si/vendor/checkout';

    /**
     * Leanpay installment URL
     *
     * https://docs.leanpay.com/api-integracija/API/custom/installment-plans-credit-calculation
     */
    public const LEANPAY_INSTALLMENT_URL = 'https://app.leanpay.si/vendor/installment-plans';

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

    /**
     * Multiple financin producs names
     */
    public const REGULAR_PRODUCT = 'regular_product';
    public const ZERO_APR = 'zero_apr';
    public const ZERO_PERCENT = 'zero_percent';
    public const HR_VENDOR_PRODUCT_ONE = 'hr_one';
    public const HR_VENDOR_PRODUCT_TWO = 'hr_two';
    /**
     * Multiple financing products codes
     * @var array
     */
    protected $vendorProductCodes = [
        self::REGULAR_PRODUCT => 'ad2e37e4-b626-429a-9bce-49480532a947',
        self::ZERO_APR => '166ede78-6556-47f5-bcdc-25ab57a7d6a1',
        self::ZERO_PERCENT => '6ebfd301-e22c-4382-a2bf-cb1d50d20aa2',
        self::HR_VENDOR_PRODUCT_ONE => 'e8884a49-c537-4f51-8599-167702732ad9',
        self::HR_VENDOR_PRODUCT_TWO => '17ac4e4a-5b1f-4dba-aa05-00395ea8fc0b'
    ];
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
     * Data constructor.
     *
     * @param Context $context
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        Context                                   $context,
        EncryptorInterface                        $encryptor,
        DateTime                                  $dateTime,
        \Magento\Framework\App\ResourceConnection $connection,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
    )
    {
        $this->connection = $connection;
        $this->dateTime = $dateTime;
        $this->encryptor = $encryptor;
        $this->catalogCategoryFactory = $categoryCollectionFactory;
        parent::__construct($context);
    }

    /**
     * Check if method is active
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return (bool)$this->scopeConfig->getValue(self::LEANPAY_IS_ACTIVE_PATH);
    }

    /**
     * Get Leanpay api key
     *
     * @return string
     */
    public function getLeanpayApiKey(): string
    {
        return (string)$this->encryptor->decrypt(
            $this->scopeConfig->getValue(self::LEANPAY_API_CONFIG_API_KEY_PATH)
        );
    }

    /**
     * Get leanpay instructions
     *
     * @return string
     */
    public function getInstructions(): string
    {
        return (string)$this->scopeConfig->getValue(
            self::LEANPAY_CONFIG_INSTRUCTIONS_PATH,
            ScopeInterface::SCOPE_STORES
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
        if ($this->getEnvironmentMode() == self::LEANPAY_API_MODE_LIVE) {
            return self::LEANPAY_CHECKOUT_URL;
        }

        return self::LEANPAY_CHECKOUT_URL_DEV;
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
        if ($this->getEnvironmentMode() == self::LEANPAY_API_MODE_LIVE) {
            return self::LEANPAY_TOKEN_URL;
        }

        return self::LEANPAY_TOKEN_URL_DEV;
    }

    /**
     * Get Leanpay Language
     *
     * @return string
     */
    public function getLeanpayLanguage(): string
    {
        return (string)$this->scopeConfig->getValue(self::LEANPAY_CONFIG_LANG_PATH);
    }

    /**
     * Get secret word from DB
     *
     * @return string
     */
    private function getSecretWord(): string
    {
        return (string)$this->encryptor->decrypt(
            $this->scopeConfig->getValue(self::LEANPAY_API_CONFIG_SECRET_WORD_PATH)
        );
    }

    /**
     * Get environment mode
     *
     * @return string
     */
    private function getEnvironmentMode(): string
    {
        return (string)$this->scopeConfig->getValue(self::LEANPAY_CONFIG_MODE_PATH);
    }

    /**
     * Get Magento Checkout URL
     *
     * @return string
     */
    public function getMagentoCheckoutUrl(): string
    {
        return (string)$this->scopeConfig->getValue(self::LEANPAY_MAGENTO_CHECKOUT_URL);
    }

    /**
     * Get installment URL
     *
     * @return string
     */
    public function getInstallmentURL(): string
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
     * Get Leanpay Promos Name
     *
     * @return string
     */
    public function getLeanpayPromosMFPName(): string
    {
        return (string)$this->scopeConfig->getValue(self::LEANPAY_PROMOS_MFP_PRODUCT_NAME);
    }

    /**
     * Get Leanpay Promos Start Date
     *
     * @return string
     */
    public function getLeanpayPromosMFPStartDate(): string
    {
        return (string)$this->scopeConfig->getValue(self::LEANPAY_PROMOS_MFP_START_DATE);
    }

    /**
     * Get Leanpay Promos End Date
     *
     * @return string
     */
    public function getLeanpayPromosMFPEndDate(): string
    {
        return (string)$this->scopeConfig->getValue(self::LEANPAY_PROMOS_MFP_END_DATE);
    }

    /**
     * Get Leanpay Promos Cart Size
     *
     * @return string
     */
    public function getLeanpayPromosMFPCartSize(): string
    {
        return (string)$this->scopeConfig->getValue(self::LEANPAY_PROMOS_MFP_CART_SIZE);
    }

    /**
     * Get Leanpay Promos vendor code by selected product name
     *
     * @return string
     */
    public function getLeanpayPromosVendorCode(): string
    {
        $name = $this->getLeanpayPromosMFPName();
        return array_key_exists($name, $this->vendorProductCodes)
            ? $this->vendorProductCodes[$name]
            : '';
    }

    /**
     * Need to be able to validate and undestand correct rule
     *
     * @return bool
     */
    public function getPromoCode($handler): string
    {
        if (!$handler instanceof OrderInterface && !$handler instanceof CartInterface) {
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
        if ($handler instanceof OrderInterface || $handler instanceof CartInterface) {
            $cache = [];
            foreach ($handler->getItems() as $item) {
                $product = $item->getProduct();
                $productValue = $product->getData('leanpay_product_financing_product_value');
                $vendorCode = $product->getData('leanpay_product_vendor_code');
                $incluse = $product->getData('leanpay_product_exclusive_inclusive') == 'inclusive' ? 1 : 0;
                $priority = $product->getData('leanpay_product_priority');
                $cache[$item->getProductId()] = $incluse;

                $end = strtotime($product->getData('leanpay_product_end_date') ?? '');
                $start = strtotime($product->getData('leanpay_product_start_date') ?? '');
                $isTime = strtotime($product->getData('leanpay_product_time_based') ?? '');

                if ($productValue > $product->getFinalPrice()){
                    return $result;
                }

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
            }
        }

        if (!empty($validOption)) {
            foreach ($validOption as $option) {
                if ($option['inclusive']) {
                    $result = $option['code'];
                    break;
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
        if ($handler instanceof OrderInterface || $handler instanceof CartInterface) {
            foreach ($handler->getItems() as $item) {
                $ids[] = $item->getProductId();
            }

            if (!empty($ids)) {
                // To improve scaling of the program calculating all required cateogries to compare
                $ids = implode(',', array_unique($ids));
                $table = $this->connection->getTableName('catalog_category_product');
                $string = sprintf('select category_id from %s where product_id in (%s) group by category_id', $table, $ids);
                $categoriesToCompare = $this->connection->getConnection()->fetchCol($string);

                if (!empty($categoriesToCompare)){
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
                   $categories = $collection->addIdFilter($categoriesToCompare)->addAttributeToSelect($requiredAttributes)->getItems();
                    if (!empty($categories)) {
                        foreach ($categories as $category) {
                            $categoryStart = strtotime($category->getData('leanpay_category_start_date') ?? '');
                            $categoryEnd = strtotime($category->getData('leanpay_category_end_date') ?? '');
                            $categoryIsTime = strtotime($category->getData('leanpay_category_time_based') ?? '');
                            $categoryPriority = (bool)$category->getData('leanpay_category_priority');
                            $categoryIsExclusive = $category->getData('leanpay_category_exclusive_inclusive') == 'inclusive' ? true : false;
                            $categoryVendorProduct = $category->getData('leanpay_category_vendor_code');
                            $categoryProductValue = $category->getData('leanpay_category_financing_product_value');
                            $currentTime = strtotime($this->dateTime->gmtDate() ?? '');

                            if ($categoryIsTime){
                                if ($categoryStart < $currentTime && $categoryEnd > $currentTime) {
                                    if (!$result) {
                                        $result = $categoryVendorProduct;
                                    } else {
                                        if ($categoryIsExclusive) {
                                            $result = $categoryVendorProduct;
                                        }
                                    }
                                }
                            } else {
                                if (!$result) {
                                    $result = $categoryVendorProduct;
                                } else {
                                    if ($categoryIsExclusive) {
                                        $result = $categoryVendorProduct;
                                    }
                                }
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
            $requiredSize = $this->getLeanpayPromosMFPCartSize() ?? 0;
            $requiredDateStart = strtotime($this->getLeanpayPromosMFPStartDate());
            $requiredDateEnd = strtotime($this->getLeanpayPromosMFPEndDate());
            $currentTime = strtotime($this->dateTime->gmtDate());

            // Invalid due to date
            if ($currentTime < $requiredDateStart && $currentTime > $requiredDateEnd) {
                return false;
            }

            $amount = 0.00;

            if ($handler instanceof OrderInterface || $handler instanceof CartInterface) {
                $amount = (float)$handler->getStore()->getBaseCurrency()->convert(
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
}
