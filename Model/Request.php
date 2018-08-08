<?php

namespace Leanpay\Payment\Model;

use Exception;
use Leanpay\Payment\Api\RequestInterface;
use Leanpay\Payment\Helper\Data;
use Magento\Framework\HTTP\Client\Curl;
use Psr\Log\LoggerInterface;

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
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * API constructor.
     * @param Curl $curl
     * @param Data $helper
     * @param LoggerInterface $logger
     */
    public function __construct(Curl $curl, Data $helper, LoggerInterface $logger)
    {
        $this->curl = $curl;
        $this->helper = $helper;
        $this->logger = $logger;
    }

    /**
     * @param array $additionalData
     * @return array
     * @throws Exception
     */
    public function getLeanpayToken($additionalData = []): array
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
            'vendorCity' => '',
            'language' => 'en'
        ];

        $this->curl->setTimeout(30);
        $this->curl->addHeader('content-type', 'application/json');
        $this->curl->setOption(CURLOPT_HEADERFUNCTION, [$this, 'parseHeaders']);

        $postData = array_merge($postData, $additionalData);
        $jsonData = json_encode($postData);

        try {
            $this->curl->post($this->helper->getTokenUrl(), $jsonData);
            $response = json_decode($this->curl->getBody(), true);

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
     * @param $ch
     * @param $data
     * @return int
     */
    public function parseHeaders($ch, $data)
    {
        return strlen($data);
    }
}