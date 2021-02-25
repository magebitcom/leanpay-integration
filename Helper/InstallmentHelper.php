<?php
declare(strict_types=1);

namespace Leanpay\Payment\Helper;

use Leanpay\Payment\Model\Config\Source\ViewBlockConfig;
use Leanpay\Payment\Model\ResourceModel\Installment;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

/**
 * Class InstallmentHelper
 *
 * @package Leanpay\Payment\Helper
 */
class InstallmentHelper extends AbstractHelper
{
    /**
     * Leanpay Installment Color
     */
    const LEANPAY_INSTALLMENT_COLOR = 'payment/leanpay_installment/color';

    /**
     * Leanpay Installment Backgroundcolor
     */
    const LEANPAY_INSTALLMENT_BACKGROUND_COLOR = 'payment/leanpay_installment/background_color';

    /**
     * Leanpay Installment homepage
     */
    const LEANPAY_INSTALLMENT_FONT_HOMEPAGE = 'payment/leanpay_installment/font_size_homepage';

    /**
     * Leanpay Installment product page
     */
    const LEANPAY_INSTALLMENT_FONT_PRODUCT_PAGE = 'payment/leanpay_installment/font_size_product_page';

    /**
     * Leanpay Installment font category page
     */
    const LEANPAY_INSTALLMENT_FONT_CATEGORY_PAGE = 'payment/leanpay_installment/font_size_catalog_page';

    /**
     * Leanpay Installment more info
     */
    const LEANPAY_INSTALLMENT_MORE_INFO = 'payment/leanpay_installment/more_info';

    /**
     * Leanpay Installment check yout limits URL
     */
    const LEANPAY_INSTALLMENT_CHECK_YOUR_LIMIT = 'payment/leanpay_installment/check_your_limit';

    /**
     * Leanpay Installment allowed views
     */
    const LEANPAY_INSTALLMENT_ALLOWED_VIEWS = 'payment/leanpay/installment_allowed_views';

    /**
     * Leanpay Installment group
     */
    const LEANPAY_INSTALLMENT_GROUP = 'payment/leanpay_installment/group';

    /**
     * Leanpay Installment allowed views
     */
    const LEANPAY_INSTALLMENT_USE_DARK_LOGO_PATH = 'payment/leanpay_installment/use_dark_logo';

    /**
     * Installment view options
     */
    const LEANPAY_INSTALLMENT_VIEW_OPTION_HOMEPAGE = 'HOMEPAGE';
    /**
     *
     */
    const LEANPAY_INSTALLMENT_VIEW_OPTION_PRODUCT_PAGE = 'PRODUCT_PAGE';
    /**
     *
     */
    const LEANPAY_INSTALLMENT_VIEW_OPTION_CATEGORY_PAGE = 'CATEGORY_PAGE';

    /**
     * @var ViewBlockConfig
     */
    private $blockConfig;

    /**
     * @var Installment
     */
    private $resourceModel;

    /**
     * InstallmentHelper constructor.
     *
     * @param Context $context
     * @param ViewBlockConfig $blockConfig
     * @param Installment $resourceModel
     */
    public function __construct(
        Context $context,
        ViewBlockConfig $blockConfig,
        Installment $resourceModel
    ) {
        $this->resourceModel = $resourceModel;
        $this->blockConfig = $blockConfig;
        parent::__construct($context);
    }

    /**
     * @return string
     */
    public function getGroup(): string
    {
        return (string)$this->scopeConfig->getValue(self::LEANPAY_INSTALLMENT_GROUP);
    }

    /**
     * @return string
     */
    public function getInstallmentColor()
    {
        return (string)$this->scopeConfig->getValue(self::LEANPAY_INSTALLMENT_COLOR);
    }

    /**
     * @return string
     */
    public function getHomepageFontSize()
    {
        return (string)$this->scopeConfig->getValue(self::LEANPAY_INSTALLMENT_FONT_HOMEPAGE);
    }

    /**
     * @return string
     */
    public function getCatalogFontSize()
    {
        return (string)$this->scopeConfig->getValue(self::LEANPAY_INSTALLMENT_FONT_CATEGORY_PAGE);
    }

    /**
     * @return string
     */
    public function getProductFontSize()
    {
        return (string)$this->scopeConfig->getValue(self::LEANPAY_INSTALLMENT_FONT_PRODUCT_PAGE);
    }

    /**
     * @return string
     */
    public function getMoreInfoURL()
    {
        return (string)$this->scopeConfig->getValue(self::LEANPAY_INSTALLMENT_MORE_INFO);
    }

    /**
     * @return string
     */
    public function getCheckYourLimitURL()
    {
        return (string)$this->scopeConfig->getValue(self::LEANPAY_INSTALLMENT_CHECK_YOUR_LIMIT);
    }

    /**
     * @return string
     */
    public function getAllowedViews()
    {
        return (string)$this->scopeConfig->getValue(self::LEANPAY_INSTALLMENT_ALLOWED_VIEWS);
    }

    /**
     * @return string
     */
    public function getBackgroundColor()
    {
        return (string) $this->scopeConfig->getValue(self::LEANPAY_INSTALLMENT_BACKGROUND_COLOR);
    }

    /**
     * @param string $view
     * @return bool
     */
    public function canShowInstallment(string $view): bool
    {
        $result = false;

        $config = $this->getAllowedViews();

        if (strlen($config) > 0 && strpos($config, $view) !== false) {
            $result = true;
        }

        return $result;
    }

    /**
     * @param $price
     * @return string
     */
    public function getLowestInstallmentPrice($price)
    {
        if (!$price) {
            return '';
        }

        return $this->resourceModel->getLowestInstallment($price, $this->getGroup());
    }

    /**
     * @param $price
     * @return array
     */
    public function getInstallmentList($price)
    {
        return $this->resourceModel->getInstallmentList($price, $this->getGroup());
    }

    /**
     * @return mixed
     */
    public function isDarkThemeLogo()
    {
        return $this->scopeConfig->getValue(self::LEANPAY_INSTALLMENT_USE_DARK_LOGO_PATH);
    }

    /**
     * @param $view
     * @return string
     */
    public function getFontSize($view): string
    {
        $result = 20;

        switch ($view) {
            case self::LEANPAY_INSTALLMENT_VIEW_OPTION_HOMEPAGE:
                $result = $this->getHomepageFontSize();
                break;
            case self::LEANPAY_INSTALLMENT_VIEW_OPTION_PRODUCT_PAGE:
                $result = $this->getProductFontSize();
                break;
            case self::LEANPAY_INSTALLMENT_VIEW_OPTION_CATEGORY_PAGE:
                $result = $this->getCatalogFontSize();
                break;
        }

        return (string)$result;
    }

    /**
     * Down payment is calculated manually and rules are as follow:
     *
     * 0 EUR for purchases up to 999.99
     * EUR100 EUR for purchases from 1000 EUR to 1999.99
     * EUR150 EUR for purchases from 2000 EUR to 2999.99
     * EUR200 EUR for purchases from 3000 EUR
     * @param $price
     * @return mixed
     */
    public function getDownPaymentRule($price): int
    {
        switch ($price) {
            case ($price >= 1000 && $price <= 1999.99):
                $result = 100;
                break;
            case ($price >= 2000 && $price <= 2999.99):
                $result = 150;
                break;
            case ($price >= 3000):
                $result = 200;
                break;
            default:
                $result = 0;
        }

        return $result;
    }
}
