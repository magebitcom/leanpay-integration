<?php

namespace Leanpay\Payment\Block\Installment\Pricing\Render;

use Leanpay\Payment\Api\Data\InstallmentInterface;
use Leanpay\Payment\Api\Data\InstallmentProductInterface;
use Leanpay\Payment\Api\InstallmentProductRepositoryInterface;
use Leanpay\Payment\Helper\Data;
use Leanpay\Payment\Helper\InstallmentHelper;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Checkout\Model\Cart;

class TemplatePriceBox extends Template
{
    /**
     * @var InstallmentHelper
     */
    private $installmentHelper;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var string
     */
    protected $_template = 'Leanpay_Payment::pricing/render/installment.phtml';

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var InstallmentProductRepositoryInterface
     */
    private $productRepo;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteria;
    private \Magento\Framework\Registry $registry;
    private Cart $cart;

    /**
     * TemplatePriceBox constructor.
     *
     * @param Template\Context $context
     * @param InstallmentHelper $installmentHelper
     * @param Data $helper
     * @param SerializerInterface $serializer
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        InstallmentHelper  $installmentHelper,
        Data $helper,
        SerializerInterface $serializer,
        InstallmentProductRepositoryInterface $productRepository,
        SearchCriteriaBuilderFactory $criteriaBuilderFactory,
        \Magento\Framework\Registry $registry,
        Cart $cart,
        array $data = []
    ) {
        $this->productRepo = $productRepository;
        $this->searchCriteria = $criteriaBuilderFactory->create();
        $this->installmentHelper = $installmentHelper;
        $this->helper = $helper;
        $this->serializer = $serializer;
        $data['view_key'] = InstallmentHelper::LEANPAY_INSTALLMENT_VIEW_OPTION_PRODUCT_PAGE;
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->cart = $cart;
    }

    /**
     * Returns amount
     *
     * @return float
     */
    public function getAmount()
    {
        $amount = $this->getData('amount');

        if ($this->getData('is_checkout')) {
            return $this->cart->getQuote()->getGrandTotal();
        }

        return $amount;
    }

    /**
     * Returns cache key
     *
     * @return string
     */
    public function getCacheKey()
    {
        return parent::getCacheKey() . ($this->getAmount());
    }

    /**
     * Returns lowest installment price
     *
     * @return string
     */
    public function getLowestInstallmentPrice(string $group = ''): string
    {
        return $this->installmentHelper->getLowestInstallmentPrice($this->getAmount(), $group);
    }

    /**
     * Retreves financila product, used in all view except checkout place order
     *
     * @return string
     */
    public function getFinancialProduct(): string
    {
        try {
            return $this->helper->getProductPromoCode($this->registry->registry('current_product'));
        } catch (LocalizedException $exception) {
            return '';
        }
    }

    /**
     * @param string $code
     * @return string|void
     */
    public function getInstallmentVendorName(string $code = '')
    {
        if (!$code) {
            return '';
        }

        try {
            $search = $this->searchCriteria->addFilter(InstallmentProductInterface::GROUP_ID, $code)
                ->setPageSize(1)
                ->setCurrentPage(1)
                ->create();

            $items = $this->productRepo->getList($search)->getItems();

            if (empty($items)) {
                return '';
            }

            foreach ($items as $item) {
                return $item->getData(InstallmentProductInterface::GROUP_NAME);
            }
        } catch (LocalizedException $exception) {
            return '';
        }
    }

    /**
     * Returns installment helper
     *
     * @return InstallmentHelper
     */
    public function getInstallmentHelper(): InstallmentHelper
    {
        return $this->installmentHelper;
    }

    /**
     * Returns price box helper
     *
     * @return Data
     */
    public function getHelper(): Data
    {
        return $this->helper;
    }

    /**
     * Returns logo
     *
     * @return string
     */
    public function getLogo()
    {
        if ($this->getData('is_checkout')) {
            return $this->getViewFileUrl('Leanpay_Payment::images/checkout.svg');
        }

        if (!$this->installmentHelper->isDarkThemeLogo()) {
            return $this->getViewFileUrl('Leanpay_Payment::images/leanpay.svg');
        }

        return $this->getViewFileUrl('Leanpay_Payment::images/dark-leanpay.svg');
    }
}
