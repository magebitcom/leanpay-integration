<?php
declare(strict_types=1);

namespace Leanpay\Payment\Controller\Installment;

use Leanpay\Payment\Block\Installment\Pricing\Render\TemplatePriceBox;
use Leanpay\Payment\Helper\Data;
use Leanpay\Payment\Helper\InstallmentHelper;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory as ResultRedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\StoreManagerInterface;

class Index implements ActionInterface
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var TemplatePriceBox
     */
    private $template;

    /**
     * @var array
     */
    private $templateCache = [];

    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var ResultRedirectFactory
     */
    private $resultRedirectFactory;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var InstallmentHelper
     */
    private $installmentHelper;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * Index constructor.
     * @param InstallmentHelper $installmentHelper
     * @param StoreManagerInterface $storeManager
     * @param JsonFactory $jsonFactory
     * @param Data $helper
     * @param TemplatePriceBox $template
     * @param SerializerInterface $serializer
     * @param ResultRedirectFactory $resultRedirectFactory
     * @param RequestInterface $request
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        InstallmentHelper $installmentHelper,
        StoreManagerInterface $storeManager,
        JsonFactory $jsonFactory,
        Data $helper,
        TemplatePriceBox $template,
        SerializerInterface $serializer,
        ResultRedirectFactory $resultRedirectFactory,
        RequestInterface $request,
        ProductRepositoryInterface $productRepository
    ) {
        $this->installmentHelper = $installmentHelper;
        $this->storeManager = $storeManager;
        $this->jsonFactory = $jsonFactory;
        $this->helper = $helper;
        $this->template = $template;
        $this->serializer = $serializer;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->request = $request;
        $this->productRepository = $productRepository;
    }

    /**
     * Execute action based on request and return result
     *
     * @return ResponseInterface|Json|Redirect|ResultInterface
     */
    public function execute()
    {
        if (!$this->request->isAjax()) {
            return $this->resultRedirectFactory->create()->setPath('');
        }

        $amount = $this->request->getParam('amount');
        $isCheckout = (bool)$this->request->getParam('checkout');
        // There is no current product on AJAX, so the product page sends its own context (LMM-144)
        $this->template
            ->setData('product', $this->getProduct($this->request->getParam('product_id')))
            ->setData('variant', $this->getProduct($this->request->getParam('variant_id')));
        $enabled = $this->helper->isActive();
        $response = $this->jsonFactory->create();
        if ($amount && $enabled) {
            $response->setData(
                [
                    'installment_html' => $this->getHtmlFromCache($amount, $isCheckout),
                ]
            );
        }

        return $response;
    }

    /**
     * Retreives HTML from cache
     *
     * @param float $amount
     * @param bool $isCheckout
     * @return string
     */
    private function getHtmlFromCache($amount, $isCheckout)
    {
        if (!isset($this->templateCache[$amount])) {
            $this->templateCache[$amount] = $this->template
                ->setData('amount', $amount)
                ->setData('is_checkout', $isCheckout)
                ->toHtml();
        }

        return $this->templateCache[$amount];
    }

    /**
     * Load a product passed by the product page, the badge falls back to default terms without it
     *
     * @param mixed $productId
     * @return ProductInterface|null
     */
    private function getProduct($productId): ?ProductInterface
    {
        if (!(int)$productId) {
            return null;
        }

        try {
            return $this->productRepository->getById(
                (int)$productId,
                false,
                (int)$this->storeManager->getStore()->getId()
            );
        } catch (NoSuchEntityException $exception) {
            return null;
        }
    }
}
