<?php
declare(strict_types=1);

namespace Leanpay\Payment\Helper;

use Exception;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{

    public CONST LEANPAY_BASE_URL = 'https://app.leanpay.si/';
    public CONST LEANPAY_BASE_URL_HR = 'https://app.leanpay.hr/';
    public CONST LEANPAY_BASE_URL_DEV = 'https://lapp.leanpay.si/';
    public CONST LEANPAY_BASE_URL_DEV_HR = 'https://lapp.leanpay.hr/';

    public CONST LEANPAY_CONFIG_CURRENCY = 'payment/leanpay/leanpay_currency';
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


    /**
     * Leanpay installment URL
     *
     * https://docs.leanpay.com/api-integracija/API/custom/installment-plans-credit-calculation
     */
    public const LEANPAY_INSTALLMENT_URL_DEV = [
        'EUR' => 'https://lapp.leanpay.si/vendor/installment-plans',
        'HKR' => 'https://lapp.leanpay.hr/vendor/installment-plans'
    ];


    /**
     * Leanpay installment URL
     *
     * https://docs.leanpay.com/api-integracija/API/custom/installment-plans-credit-calculation
     */
    public const LEANPAY_INSTALLMENT_URL = [
        'EUR'=> 'https://app.leanpay.si/vendor/installment-plans',
        'HKR' => 'https://app.leanpay.hr/vendor/installment-plans'
    ] ;

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
     * Mode types
     */
    public const LEANPAY_API_MODE_DEV = 'DEV_MODE';
    public const LEANPAY_API_MODE_LIVE = 'LIVE_MODE';

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
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        Context $context,
        EncryptorInterface $encryptor,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->encryptor = $encryptor;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
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
        $currencyType = $this->getCurrencyType();

        if ($currencyType === 'EUR') {
            if ($this->getEnvironmentMode() == self::LEANPAY_API_MODE_LIVE) {
                return (string) self::LEANPAY_BASE_URL;
            }

            return (string) self::LEANPAY_BASE_URL_DEV;
        }

        if ($this->getEnvironmentMode() == self::LEANPAY_API_MODE_LIVE) {
            return (string) self::LEANPAY_BASE_URL_HR;
        }

        return (string) self::LEANPAY_BASE_URL_DEV_HR;
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
        return (string)$this->encryptor->decrypt(
            $this->scopeConfig->getValue(
                self::LEANPAY_API_CONFIG_API_KEY_PATH,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->getStoreId()
            )
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
        return (string)$this->encryptor->decrypt(
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
}
