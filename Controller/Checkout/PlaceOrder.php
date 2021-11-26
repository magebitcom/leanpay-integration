<?php
declare(strict_types=1);

namespace Leanpay\Payment\Controller\Checkout;

use Leanpay\Payment\Controller\AbstractAction;
use Leanpay\Payment\Helper\Data;
use Leanpay\Payment\Logger\PaymentLogger;
use Magento\Checkout\Model\Session;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\DB\Transaction;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Webapi\Exception;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Payment\Repository as PaymentRepository;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\Service\InvoiceService;

class PlaceOrder extends AbstractAction
{

    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return ResponseInterface|Json
     */
    public function execute()
    {
        $token = $this->checkoutSession->getToken();

        $result = $this->resultJsonFactory->create();

        if (!$token) {
            $result->setHttpResponseCode(Exception::HTTP_BAD_REQUEST);
            return $result->setData([
                'error' => true,
                'message' => __('An error occurred on the server. Please try to place the order again.')
            ]);
        }

        $data = [
            'action' => $this->helper->getCheckoutUrl(),
            'data' => [
                'token' => $token
            ],
        ];

        return $result->setData($data);
    }
}
