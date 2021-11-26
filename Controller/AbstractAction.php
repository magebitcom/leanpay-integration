<?php
declare(strict_types=1);

namespace Leanpay\Payment\Controller;

use Leanpay\Payment\Helper\Data;
use Leanpay\Payment\Logger\PaymentLogger;
use Magento\Checkout\Model\Session;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\DB\Transaction;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Payment\Repository as PaymentRepository;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Framework\Controller\Result\RedirectFactory as ResultRedirectFactory;

/**
 * Class AbstractAction
 *
 * @package Leanpay\Payment\Controller
 */
abstract class AbstractAction implements ActionInterface
{
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
     * @var DriverInterface
     */
    protected $driver;

    /**
     * @var ResultRedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * AbstractAction constructor.
     * @param JsonFactory $resultJsonFactory
     * @param ResultRedirectFactory $resultRedirectFactory
     * @param DriverInterface $driver
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
        JsonFactory $resultJsonFactory,
        ResultRedirectFactory $resultRedirectFactory,
        DriverInterface $driver,
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
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->driver = $driver;
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
     * Find order searching by leanpay token
     *
     * @param int $id
     * @return Order
     * @throws NotFoundException
     */
    public function findOrder(int $id)
    {
        $filters[] = $this->filterBuilder
            ->setField('increment_id')
            ->setConditionType('eq')
            ->setValue($id)
            ->create();

        $this->searchCriteriaBuilder->addFilters($filters);

        $searchCriteria = $this->searchCriteriaBuilder->create();
        $order = $this->orderRepository->getList($searchCriteria)->getItems();
        /** @var Order $order */
        $order = current($order);

        if (!$order || $order && !$order->getId()) {
            throw new NotFoundException(__('Can\'t find order'));
        }

        return $order;
    }
}
