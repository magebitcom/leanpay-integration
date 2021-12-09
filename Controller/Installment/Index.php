<?php
declare(strict_types=1);

namespace Leanpay\Payment\Controller\Installment;

use Leanpay\Payment\Block\Installment\Pricing\Render\TemplatePriceBox;
use Leanpay\Payment\Helper\Data;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory as ResultRedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Serialize\SerializerInterface;

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
     * Index constructor.
     *
     * @param JsonFactory $jsonFactory
     * @param Data $helper
     * @param TemplatePriceBox $template
     * @param SerializerInterface $serializer
     * @param ResultRedirectFactory $resultRedirectFactory
     * @param RequestInterface $request
     */
    public function __construct(
        JsonFactory $jsonFactory,
        Data $helper,
        TemplatePriceBox $template,
        SerializerInterface $serializer,
        ResultRedirectFactory $resultRedirectFactory,
        RequestInterface $request
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->helper = $helper;
        $this->template = $template;
        $this->serializer = $serializer;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->request = $request;
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
}
