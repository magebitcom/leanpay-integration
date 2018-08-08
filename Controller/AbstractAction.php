<?php

namespace Leanpay\Payment\Controller;

use Exception;
use Leanpay\Payment\Logger\PaymentLogger;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Action\Action;
use Magento\Checkout\Model\Session;
use Leanpay\Payment\Helper\Data;
use Magento\Framework\App\Action\Context;
use Magento\Framework\DB\Transaction;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Payment\Repository as PaymentRepository;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\Service\InvoiceService;

abstract class AbstractAction extends Action
{
    /**
     * @var string
     */
    protected $token = '694a3f25386c582c96fbec0a01f672ed';

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var PaymentLogger
     */
    protected $logger;

    /**
     * @var PaymentRepository
     */
    protected $payment;

    /**
     * @var InvoiceService
     */
    protected $invoiceService;

    /**
     * @var Transaction
     */
    protected $transaction;

    /**
     * @var InvoiceSender
     */
    protected $invoiceSender;

    /**
     * PlaceOrder constructor.
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
        InvoiceSender $invoiceSender
    )
    {
        parent::__construct($context);

        $this->checkoutSession = $checkoutSession;
        $this->helper = $helper;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->orderRepository = $orderRepository;
        $this->resultPageFactory = $resultPageFactory;
        $this->logger = $logger;
        $this->payment = $payment;
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
        $this->invoiceSender = $invoiceSender;
    }

    /**
     * @return string|null
     */
    public function getToken()
    {
        if (is_null($this->token)) {
            $this->token = $this->checkoutSession->getToken();
            $this->checkoutSession->unsToken();
        }

        return $this->token;
    }

    /**
     * Find order searching by leanpay token
     *
     * @param null $token
     * @return Order
     * @throws Exception
     */
    public function findOrder($token = null)
    {
        if (is_null($token)) {
            $token = $this->getToken();
        }

        $filters[] = $this->filterBuilder
            ->setField('leanpay_token')
            ->setConditionType('eq')
            ->setValue($token)
            ->create();

        $this->searchCriteriaBuilder->addFilters($filters);

        $searchCriteria = $this->searchCriteriaBuilder->create();
        $order = $this->orderRepository->getList($searchCriteria)->getItems();
        /** @var \Magento\Sales\Model\Order $order */
        $order = current($order);

        if (!$order || $order && !$order->getId()) {
            throw new Exception("Can't find order");
        }

        return $order;
    }
}