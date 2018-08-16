<?php

namespace Leanpay\Payment\Model\Method;

use Magento\Checkout\Model\Session;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Registry;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Model\Method\Logger;
use Magento\Directory\Model\Currency as CurrencyModel;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Leanpay\Payment\Model\Request;
use Magento\Sales\Model\Order;

class Leanpay extends AbstractMethod
{
    const CODE = 'leanpay';

    /**
     * Payment system code
     *
     * @var string
     */
    protected $_code = self::CODE;

    /**
     * @var bool
     */
    protected $_canOrder = true;

    /**
     * @var bool
     */
    protected $_canAuthorize = true;

    /**
     * @var bool
     */
    protected $_canCapture = true;

    /**
     * @var bool
     */
    protected $_canCapturePartial = true;

    /**
     * @var bool
     */
    protected $_canRefundInvoicePartial = true;

    /**
     * @var bool
     */
    protected $_canVoid = true;

    /**
     * @var bool
     */
    protected $_canUseInternal = false;

    /**
     * @var bool
     */
    protected $_canFetchTransactionInfo = true;

    /**
     * @var bool
     */
    protected $_canReviewPayment = true;

    /**
     * @var bool
     */
    protected $_isInitializeNeeded = true;

    /**
     * @var CurrencyModel
     */
    protected $currencyModel;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * Leanpay constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param Data $paymentData
     * @param ScopeConfigInterface $scopeConfig
     * @param Logger $logger
     * @param CurrencyModel $currencyModel
     * @param StoreManagerInterface $storeManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param Session $checkoutSession
     * @param Request $request
     * @param OrderRepositoryInterface $orderRepository
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @param DirectoryHelper|null $directory
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        Data $paymentData,
        ScopeConfigInterface $scopeConfig,
        Logger $logger,
        CurrencyModel $currencyModel,
        StoreManagerInterface $storeManager,
        PriceCurrencyInterface $priceCurrency,
        Session $checkoutSession,
        Request $request,
        OrderRepositoryInterface $orderRepository,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = [],
        DirectoryHelper $directory = null
    )
    {
        parent::__construct($context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data,
            $directory
        );

        $this->currencyModel = $currencyModel;
        $this->storeManager = $storeManager;
        $this->priceCurrency = $priceCurrency;
        $this->request = $request;
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Check payment method enable. if true, then check has euro currency
     *
     * @param CartInterface|null $quote
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isAvailable(CartInterface $quote = null)
    {
        $isActive = parent::isAvailable($quote);

        if (!$isActive) {
            return false;
        }

        $baseCode = $this->storeManager->getStore()->getCurrentCurrency()->getCode();
        $allowedCurrencies = $this->currencyModel->getConfigAllowCurrencies();
        $rates = $this->currencyModel->getCurrencyRates($baseCode, array_values($allowedCurrencies));

        if (!array_key_exists('EUR', $rates)) {
            return false;
        }

        return true;
    }

    /**
     * After place order get tokken from leanpay
     *
     * @param string $paymentAction
     * @param object $stateObject
     * @return AbstractMethod
     * @throws LocalizedException
     */
    public function initialize($paymentAction, $stateObject)
    {
        /** @var \Magento\Sales\Model\Order\Payment $paymentInfo */
        $paymentInfo = $this->getInfoInstance();
        $order = $paymentInfo->getOrder();
        $address = $order->getBillingAddress();
        $amount = $order->getStore()->getBaseCurrency()->convert($order->getBaseGrandTotal(), 'EUR');

        $additionData = [
            'vendorTransactionId' => $order->getIncrementId(),
            'amount' => $amount,
            'vendorFirstName' =>  $order->getCustomerFirstname(),
            'vendorLastName' => $order->getCustomerLastname(),
            'vendorAddress' => current($address->getStreet()),
            'vendorZip' => $address->getPostcode(),
            'vendorCity' => $address->getCity(),
            'vendorPhoneNumber' => $address->getTelephone(),
            'language' => 'sl'
        ];

        $leanpayTokenData = $this->request->getLeanpayToken($additionData);

        $state = Order::STATE_CANCELED;

        if (!$leanpayTokenData['error']) {
            $state = Order::STATE_NEW;
            $this->checkoutSession->setToken($leanpayTokenData['token']);
        }

        $stateObject->setState($state);
        $stateObject->setStatus($state);

        return $this;
    }
}