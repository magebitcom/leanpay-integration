<?php
declare(strict_types=1);

namespace Leanpay\Payment\Model\Method;

use Leanpay\Payment\Helper\Data as PaymentData;
use Magento\Checkout\Model\Session;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
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
use Magento\Sales\Model\Order\Payment;
use Magento\Store\Model\StoreManagerInterface;
use Leanpay\Payment\Model\Request;
use Magento\Sales\Model\Order;

class Leanpay extends AbstractMethod
{
    public const CODE = 'leanpay';

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
     * @var PaymentData
     */
    protected $helper;

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
     * @param PaymentData $helper
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
        PaymentData $helper,
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
    ) {
        parent::__construct(
            $context,
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

        $this->helper = $helper;
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
     * @throws NoSuchEntityException|LocalizedException
     */
    public function isAvailable(CartInterface $quote = null)
    {
        $isActive = parent::isAvailable($quote);

        if (!$isActive) {
            return false;
        }

        $currency = $this->helper->getCurrencyType();
        $baseCode = $this->storeManager->getStore()->getCurrentCurrency()->getCode();
        $allowedCurrencies = $this->currencyModel->getConfigAllowCurrencies();
        $rates = $this->currencyModel->getCurrencyRates($baseCode, array_values($allowedCurrencies));
        if ($currency != $baseCode && !array_key_exists($currency, $rates)) {
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
        /** @var Payment $paymentInfo */
        $paymentInfo = $this->getInfoInstance();
        $order = $paymentInfo->getOrder();
        $address = $order->getBillingAddress();

        $currency = $this->helper->getCurrencyType();

        $amount = $order->getStore()->getBaseCurrency()->convert($order->getBaseGrandTotal(), $currency);

        $orderItems = $order->getAllVisibleItems();

        $additionData = [
            'vendorTransactionId' => $order->getQuoteId(),
            'amount' => $amount,
            'vendorFirstName' => $order->getCustomerFirstname() ?: $order->getBillingAddress()->getFirstname(),
            'vendorLastName' => $order->getCustomerLastname() ?: $order->getBillingAddress()->getLastname(),
            'vendorAddress' => current($address->getStreet()),
            'vendorZip' => $address->getPostcode(),
            'vendorCity' => $address->getCity(),
            'vendorPhoneNumber' => $address->getTelephone(),
            'language' => $this->helper->getLeanpayLanguage()
        ];

        if ($orderItems) {
            foreach ($orderItems as $item) {
                $price = $item->getRowTotal() / $item->getQtyOrdered();

                if ($price) {
                    $additionData['cartItems'][] = [
                        'name' => $item->getName(),
                        'sku' => $item->getSku(),
                        'price' => $order->getStore()->getBaseCurrency()->convert($price, $currency),
                        'qty' => $item->getQtyOrdered()
                    ];
                }
            }
        }

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
