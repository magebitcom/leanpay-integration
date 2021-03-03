<?php
declare(strict_types=1);

namespace Leanpay\Payment\Controller\Installment;

use Leanpay\Payment\Block\Installment\Pricing\Render\TemplatePriceBox;
use Leanpay\Payment\Helper\Data;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Serialize\SerializerInterface;

class Index extends Action
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
     * Index constructor.
     *
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param Data $helper
     * @param TemplatePriceBox $template
     * @param SerializerInterface $serializer
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        Data $helper,
        TemplatePriceBox $template,
        SerializerInterface $serializer
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->helper = $helper;
        $this->template = $template;
        $this->serializer = $serializer;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        if (!$this->getRequest()->isAjax()) {
            return $this->_redirect('');
        }

        $amount = $this->getRequest()->getParam('amount');
        $isCheckout = (bool)$this->getRequest()->getParam('checkout');
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
