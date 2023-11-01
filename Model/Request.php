<?php
declare(strict_types=1);

namespace Leanpay\Payment\Model;

use Exception;
use Leanpay\Payment\Api\RequestInterface;
use Leanpay\Payment\Helper\Data;
use Leanpay\Payment\Logger\PaymentLogger;
use Magento\Framework\HTTP\Client\Curl;

class Request implements RequestInterface
{
    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var PaymentLogger
     */
    protected $logger;

    /**
     * API constructor.
     *
     * @param Curl $curl
     * @param Data $helper
     * @param PaymentLogger $logger
     */
    public function __construct(Curl $curl, Data $helper, PaymentLogger $logger)
    {
        $this->curl = $curl;
        $this->helper = $helper;
        $this->logger = $logger;
    }

    /**
     * Request leanpay token
     *
     * @param array $additionalData
     * @return array
     * @throws Exception
     */
    public function getLeanpayToken(array $additionalData = []): array
    {
        $postData = [
            'vendorApiKey' => $this->helper->getLeanpayApiKey(),
            'vendorTransactionId' => 0,
            'amount' => 0.0,
            'successUrl' => $this->helper->createUrl(Data::LEANPAY_SUCCESS_URL),
            'errorUrl' => $this->helper->createUrl(Data::LEANPAY_ERROR_URL),
            'vendorPhoneNumber' => '',
            'vendorFirstName' => '',
            'vendorLastName' => '',
            'vendorAddress' => '',
            'vendorZip' => '',
            'vendorCity' => ''
        ];

        $this->curl->setTimeout(30);
        $this->curl->addHeader('content-type', 'application/json');
        $this->curl->setOption(CURLOPT_HEADERFUNCTION, [$this, 'parseHeaders']);

        $postData = array_merge($postData, $additionalData);
        $jsonData = json_encode($postData);

        try {
            $this->curl->post($this->helper->getTokenUrl(), $jsonData);
            $response = json_decode($this->curl->getBody(), true);

            $response = array_filter($response, static function($var){return $var !== null;} );

            return array_merge($response, [
                'error' => false
            ]);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());

            return [
                'error' => true
            ];
        }
    }

    /**
     * Remove header validation
     *
     * @param resource $ch
     * @param string $data
     * @return int
     */
    public function parseHeaders($ch, $data): int
    {
        return strlen($data);
    }
}
