<?php

namespace Leanpay\Payment\Helper;

use Exception;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    /**
     *  Post Token URL
     *
     * http://static.leanpay.com/api-docs/docs.html#integration-steps-request-token-post
     */
    const LEANPAY_TOKEN_URL_DEV = 'https://lapp.leanpay.si/vendor/token';

    /**
     * Post checkout URL
     *
     * http://static.leanpay.com/api-docs/docs.html#integration-steps-checkout-page-post
     */
    const LEANPAY_CHECKOUT_URL_DEV = 'https://lapp.leanpay.si/vendor/checkout';

    /**
     *  Post Token URL
     *
     * http://static.leanpay.com/api-docs/docs.html#integration-steps-request-token-post
     */
    const LEANPAY_TOKEN_URL = 'https://app.leanpay.si/vendor/token';

    /**
     * Post checkout URL
     *
     * http://static.leanpay.com/api-docs/docs.html#integration-steps-checkout-page-post
     */
    const LEANPAY_CHECKOUT_URL = 'https://app.leanpay.si/vendor/checkout';

    /**
     * Leanpay Magento 1 api key path in Database
     */
    const LEANPAY_API_CONFIG_API_KEY_PATH = 'payment/leanpay/api_key';

    /**
     * Leanpay Magento 1 secret word path in Database
     */
    const LEANPAY_API_CONFIG_SECRET_WORD_PATH = 'payment/leanpay/api_secret';

    /**
     * Leanpay Magento 1 description path in Database
     */
    const LEANPAY_CONFIG_INSTRUCTIONS_PATH = 'payment/leanpay/instructions';

    /**
     * Leanpay Magento 1 secret word path in Database
     */
    const LEANPAY_CONFIG_MODE_PATH = 'payment/leanpay/mode';

    /**
     * Successful redirect url
     */
    const LEANPAY_SUCCESS_URL = 'leanpay/checkout/success';

    /**
     * Error redirect url
     */
    const LEANPAY_ERROR_URL = 'leanpay/checkout/failure';

    /**
     * Mode types
     */
    const LEANPAY_API_MODE_DEV = 'DEV_MODE';
    const LEANPAY_API_MODE_LIVE = 'LIVE_MODE';

    /**
     * Leanpay responses from api
     */
    protected $responseMap = [
        'PENDING' => Order::STATE_PENDING_PAYMENT,
        'SUCCESS' => Order::STATE_PROCESSING,
        'CANCELED' => Order::STATE_CANCELED,
        'EXPIRED' => Order::STATE_CANCELED
    ];

    /**
     * Get Leanpay api key
     *
     * @return string
     */
    public function getLeanpayApiKey(): string
    {
        return (string) $this->scopeConfig->getValue(self::LEANPAY_API_CONFIG_API_KEY_PATH);
    }

    /**
     * @return string
     */
    public function getInstructions(): string
    {
        return (string) $this->scopeConfig->getValue(self::LEANPAY_CONFIG_INSTRUCTIONS_PATH, ScopeInterface::SCOPE_STORES);
    }

    /**
     * Convert leanpay api status to magento order status
     *
     * @param $status
     *
     * @return string
     * @throws Exception
     */
    public function convertLeanpayStatusToMagento($status): string
    {
        if (!isset($this->responseMap[$status])) {
            throw new Exception("Can't Find {$status} status");
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
     * @param $leanPayTransactionId
     * @param $vendorTransactionId
     * @param $amount
     * @param $responseStatus
     *
     * @return string
     */
    public function generateMD5Secret($leanPayTransactionId, $vendorTransactionId, $amount, $responseStatus): string
    {
        $string = is_null($leanPayTransactionId) ? 'null' : $leanPayTransactionId;
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
     * Get secret word from DB
     *
     * @return string
     */
    private function getSecretWord(): string
    {
        return (string) $this->scopeConfig->getValue(self::LEANPAY_API_CONFIG_SECRET_WORD_PATH);
    }

    /**
     * Get environment mode
     *
     * @return string
     */
    private function getEnvironmentMode(): string
    {
        return (string) $this->scopeConfig->getValue(self::LEANPAY_CONFIG_MODE_PATH);
    }

    /**
     * @param $route
     * @param array $params
     *
     * @return string
     */
    public function createUrl($route, $params = []): string
    {
        return $this->_getUrl($route, $params);
    }
}