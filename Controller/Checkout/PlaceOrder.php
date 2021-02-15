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

/**
 * Class PlaceOrder
 *
 * @package Leanpay\Payment\Controller\Checkout
 */
class PlaceOrder extends AbstractAction
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * PlaceOrder constructor.
     *
     * @param Context $context
     * @param Session $checkoutSession
     * @param Data $helper
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param OrderRepository $orderRepository
     * @param PageFactory $resultPageFactory
     * @param PaymentLogger $logger
     * @param PaymentRepository $payment
     * @param InvoiceService $invoiceService
     * @param Transaction $transaction
     * @param InvoiceSender $invoiceSender
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        Data $helper,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        OrderRepository $orderRepository,
        PageFactory $resultPageFactory,
        PaymentLogger $logger,
        PaymentRepository $payment,
        InvoiceService $invoiceService,
        Transaction $transaction,
        InvoiceSender $invoiceSender,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct(
            $context,
            $checkoutSession,
            $helper,
            $searchCriteriaBuilder,
            $filterBuilder,
            $orderRepository,
            $resultPageFactory,
            $logger,
            $payment,
            $invoiceService,
            $transaction,
            $invoiceSender
        );
        $this->resultJsonFactory = $resultJsonFactory;
    }

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
