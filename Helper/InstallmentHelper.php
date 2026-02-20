<?php

declare(strict_types=1);

namespace Leanpay\Payment\Helper;

use Leanpay\Payment\Api\Data\InstallmentInterface;
use Leanpay\Payment\Model\Config\Source\ViewBlockConfig;
use Leanpay\Payment\Model\ResourceModel\Installment;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

use function GuzzleHttp\Psr7\str;

class InstallmentHelper extends AbstractHelper
{
    /**
     * Leanpay Installment Color
     */
    public const LEANPAY_INSTALLMENT_COLOR = 'payment/leanpay_installment/color';

    /**
     * Leanpay Installment Backgroundcolor
     */
    public const LEANPAY_INSTALLMENT_BACKGROUND_COLOR = 'payment/leanpay_installment/background_color';

    /**
     * Leanpay Installment homepage
     */
    public const LEANPAY_INSTALLMENT_FONT_HOMEPAGE = 'payment/leanpay_installment/font_size_homepage';

    /**
     * Leanpay Installment product page
     */
    public const LEANPAY_INSTALLMENT_FONT_PRODUCT_PAGE = 'payment/leanpay_installment/font_size_product_page';

    /**
     * Leanpay Installment font category page
     */
    public const LEANPAY_INSTALLMENT_FONT_CATEGORY_PAGE = 'payment/leanpay_installment/font_size_catalog_page';

    /**
     * Leanpay Installment more info
     */
    public const LEANPAY_INSTALLMENT_MORE_INFO = 'payment/leanpay_installment/more_info';

    /**
     * Leanpay Installment check yout limits URL
     */
    public const LEANPAY_INSTALLMENT_CHECK_YOUR_LIMIT = 'payment/leanpay_installment/check_your_limit';

    /**
     * Leanpay Installment allowed views
     */
    public const LEANPAY_INSTALLMENT_ALLOWED_VIEWS = 'payment/leanpay/installment_allowed_views';

    /**
     * Leanpay Installment group
     */
    public const LEANPAY_INSTALLMENT_GROUP = 'payment/leanpay_installment/group';

    /**
     * Leanpay Installment allowed views
     */
    public const LEANPAY_INSTALLMENT_USE_DARK_LOGO_PATH = 'payment/leanpay_installment/use_dark_logo';

    /**
     * Leanpay Installment enable product rounding
     */
    public const LEANPAY_INSTALLMENT_ENABLE_PRODUCT_ROUNDING = 'payment/leanpay_installment/enable_product_rounding';

    /**
     * Leanpay MIN order allowed price
     */
    public const LEANPAY_INSTALLMENT_MIN = 'payment/leanpay/min_order_total';

    /**
     * Leanpay MAX order allowed price
     */
    public const LEANPAY_INSTALLMENT_MAX = 'payment/leanpay/max_order_total';


    /**
     * Installment view options
     */
    public const LEANPAY_INSTALLMENT_VIEW_OPTION_HOMEPAGE = 'HOMEPAGE';
    /**
     *
     */
    public const LEANPAY_INSTALLMENT_VIEW_OPTION_PRODUCT_PAGE = 'PRODUCT_PAGE';
    /**
     *
     */
    public const LEANPAY_INSTALLMENT_VIEW_OPTION_CATEGORY_PAGE = 'CATEGORY_PAGE';

    /**
     * Leanpay Installment allowed currencies
     */
    public const LEANPAY_INSTALLMENT_CRON_CURRENCIES = 'payment/leanpay_installment/cron_currencies';

    /**
     * Fixed conversion rate during euro transition period HR
     */
    public const TRANSITION_CONVERSION_RATE = [
        'HRK' => '7.53450',
        'RON' => '6.4100',
        'EUR' => '1'
    ];

    public const ALLOWED_CURRENCIES = [
        'HRK'
    ];

    /**
     * @var ViewBlockConfig
     */
    private $blockConfig;

    /**
     * @var Installment
     */
    private $resourceModel;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    protected $dataHelper;

    /**
     * InstallmentHelper constructor.
     *
     * @param Data $dataHelper
     * @param SerializerInterface $serializer
     * @param StoreManagerInterface $storeManager
     * @param Context $context
     * @param ViewBlockConfig $blockConfig
     * @param Installment $resourceModel
     */
    public function __construct(
        Data $dataHelper,
        SerializerInterface $serializer,
        StoreManagerInterface $storeManager,
        Context $context,
        ViewBlockConfig $blockConfig,
        Installment $resourceModel
    ) {
        $this->dataHelper = $dataHelper;
        $this->serializer = $serializer;
        $this->storeManager = $storeManager;
        $this->resourceModel = $resourceModel;
        $this->blockConfig = $blockConfig;
        parent::__construct($context);
    }

    /**
     * Get installment group
     *
     * @return string
     */
    public function getGroup(): string
    {
        return (string) $this->scopeConfig->getValue(self::LEANPAY_INSTALLMENT_GROUP, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get installment color
     *
     * @return string
     */
    public function getInstallmentColor()
    {
        return (string) $this->scopeConfig->getValue(self::LEANPAY_INSTALLMENT_COLOR);
    }

    /**
     * Get homepage font size
     *
     * @return string
     */
    public function getHomepageFontSize()
    {
        return (string) $this->scopeConfig->getValue(self::LEANPAY_INSTALLMENT_FONT_HOMEPAGE);
    }

    /**
     * Get catalog font size
     *
     * @return string
     */
    public function getCatalogFontSize()
    {
        return (string) $this->scopeConfig->getValue(self::LEANPAY_INSTALLMENT_FONT_CATEGORY_PAGE);
    }

    /**
     * Get product font size
     *
     * @return string
     */
    public function getProductFontSize()
    {
        return (string) $this->scopeConfig->getValue(self::LEANPAY_INSTALLMENT_FONT_PRODUCT_PAGE);
    }

    /**
     * Get more info url
     *
     * @return string
     */
    public function getMoreInfoURL()
    {
        return (string) $this->scopeConfig->getValue(self::LEANPAY_INSTALLMENT_MORE_INFO);
    }

    /**
     * Get check your limit url
     *
     * @return string
     */
    public function getCheckYourLimitURL()
    {
        return (string) $this->scopeConfig->getValue(self::LEANPAY_INSTALLMENT_CHECK_YOUR_LIMIT);
    }

    /**
     * Get allowed views
     *
     * @return string
     */
    public function getAllowedViews()
    {
        return (string) $this->scopeConfig->getValue(self::LEANPAY_INSTALLMENT_ALLOWED_VIEWS);
    }

    /**
     * Get background color
     *
     * @return string
     */
    public function getBackgroundColor()
    {
        return (string) $this->scopeConfig->getValue(self::LEANPAY_INSTALLMENT_BACKGROUND_COLOR);
    }

    /**
     * Check if can show installment
     *
     * @param string $view
     * @return bool
     */
    public function canShowInstallment(string $view): bool
    {
        $result = false;

        $installmentCurrencies = $this->resourceModel->getInstallmentCurrencies();
        $currentStoreCurrency = $this->storeManager->getStore()->getCurrentCurrency()->getCode();
        if ($this->scopeConfig->getValue(Data::LEANPAY_CONFIG_CURRENCY) === $currentStoreCurrency &&
            !in_array($currentStoreCurrency, array_keys($installmentCurrencies))
        ) {
            return $result;
        }

        $config = $this->getAllowedViews();

        if (strlen($config) > 0 && strpos($config, 'DISABLED') !== false) {
            return $result;
        }

        if (strlen($config) > 0 && strpos($config, $view) !== false) {
            $result = true;
        }

        return $result;
    }

    /**
     * Round price up to nearest value divisible by 5
     *
     * @param float $price
     * @return float
     */
    private function roundUpToNearestFive(float $price): float
    {
        return ceil($price / 5) * 5;
    }

    /**
     * Apply product rounding if enabled and for Romanian plugin
     *
     * @param float $price
     * @return float
     */
    private function applyProductRounding(float $price): float
    {
        $scopeId = $this->storeManager->getStore()->getId();
        $isRoundingEnabled = (bool) $this->scopeConfig->getValue(
            self::LEANPAY_INSTALLMENT_ENABLE_PRODUCT_ROUNDING,
            ScopeInterface::SCOPE_STORE,
            $scopeId
        );

        if ($isRoundingEnabled && $this->dataHelper->getApiType() === Data::API_ENDPOINT_ROMANIA) {
            return $this->roundUpToNearestFive($price);
        }

        return $price;
    }

    /**
     * Get lowest installment price
     *
     * @param float $price
     * @return string
     */
    public function getLowestInstallmentPrice(float $price, $group = '')
    {
        $scopeId = $this->storeManager->getStore()->getId();

        $min = $this->scopeConfig->getValue(
            self::LEANPAY_INSTALLMENT_MIN,
            ScopeInterface::SCOPE_STORE,
            $scopeId
        );
        $max = $this->scopeConfig->getValue(
            self::LEANPAY_INSTALLMENT_MAX,
            ScopeInterface::SCOPE_STORE,
            $scopeId
        );

        if (!$price) {
            return '';
        }

        if ($price > $max || $price < $min) {
            return '';
        }

        if (!$group) {
            $group = $this->getGroup();
        }

        $roundedPrice = $this->applyProductRounding($price);
        
        return $this->resourceModel->getLowestInstallment($roundedPrice, $group, $this->dataHelper->getApiType());
    }

    /**
     * Get installment list
     *
     * @param float $price
     * @return array
     */
    public function getInstallmentList(float $price, $group = '')
    {
        $roundedPrice = $this->applyProductRounding($price);

        if ($group) {
            return $this->resourceModel->getInstallmentList($roundedPrice, $group);
        }

        return $this->resourceModel->getInstallmentList($roundedPrice, $this->getGroup());
    }

    /**
     * Get tooltip data
     *
     * @param float $price
     * @param bool $useTerm
     * @return string
     */
    public function getToolTipData(float $price, $useTerm, $group = '')
    {
        if (!$price) {
            return '';
        }

        $roundedPrice = $this->applyProductRounding($price);

        if ($group) {
            return $this->resourceModel->getToolTipData($roundedPrice, $group, $useTerm);
        }

        return $this->resourceModel->getToolTipData($roundedPrice, $this->getGroup(), $useTerm);
    }

    /**
     * Check if theme logo is dark
     *
     * @return mixed
     */
    public function isDarkThemeLogo()
    {
        return $this->scopeConfig->getValue(self::LEANPAY_INSTALLMENT_USE_DARK_LOGO_PATH);
    }

    /**
     * Get font size
     *
     * @param string $view
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

        return (string) $result;
    }

    /**
     * Down payment is calculated manually and rules are as follow:
     *
     * 0 EUR for purchases up to 999.99
     * EUR100 EUR for purchases from 1000 EUR to 1999.99
     * EUR150 EUR for purchases from 2000 EUR to 2999.99
     * EUR200 EUR for purchases from 3000 EUR
     *
     * @param float $price
     * @return mixed
     */
    public function getDownPaymentRule(float $price): int
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

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrency(): string
    {
        return (string) $this->scopeConfig->getValue(
            Data::LEANPAY_CONFIG_CURRENCY,
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrencyCode(): string
    {
        if ($this->dataHelper->getApiType() === Data::API_ENDPOINT_CROATIA) {
            return 'EUR';
        } elseif ($this->dataHelper->getApiType() === Data::API_ENDPOINT_ROMANIA) {
            return 'RON';
        } else {
            return 'EUR';
        }
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function allowDownPayment(): bool
    {
        return false;
    }

    /**
     * @param string $price
     * @param string $currencyCode
     * @return string|int
     */
    public function getTransitionPrice(string $price, string $currencyCode): string|int
    {
        if (!in_array($currencyCode, self::ALLOWED_CURRENCIES)) {
            return 1;
        }

        $convertedPrice = $price * self::TRANSITION_CONVERSION_RATE[$currencyCode];

        return (string) round($convertedPrice, 2);
    }

    /**
     * Returns Json config
     *
     * @return string
     */
    public function getJsonConfig($amount, $group = '')
    {
        $list = $this->getInstallmentList($amount, $group);
        $list = array_values($list);
        $values = [];
        $listLength = count($list);
        for ($index = 0; $index < $listLength; $index++) {
            $values[] = $index;
        }

        $data = [
            'min' => array_key_first($list),
            'max' => array_key_last($list),
            'data' => $list,
            'value' => $values,
            'currency' => $this->getCurrencyCode(),
        ];

        if ($this->dataHelper->getApiType() === Data::API_ENDPOINT_CROATIA) {
            $convertedValues = [];
            foreach ($list as $value) {
                $convertedValues[] = $value[InstallmentInterface::INSTALLMENT_AMOUNT] * self::TRANSITION_CONVERSION_RATE['HRK'];;
            }
            $data['convertedCurrency'] = 'HRK';
            $data['convertedValues'] = $convertedValues;
        }

        return (string) $this->serializer->serialize($data);
    }

    /**
     * @param float $amount
     * @param false $useTerm
     * @param string $group
     * @return Phrase
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getTooltipPriceBlock(float $amount, $useTerm = false, $group = ''): \Magento\Framework\Phrase
    {
        $data = $this->getToolTipData($amount, $useTerm, $group);
        $term = $data[InstallmentInterface::INSTALLMENT_PERIOD];
        $amount = $data[InstallmentInterface::INSTALLMENT_AMOUNT];

        if ($this->dataHelper->getApiType() === Data::API_ENDPOINT_CROATIA) {
            return __(
                '%1 x %2%3 / %4%5',
                $term,
                $amount,
                'EUR',
                $this->getTransitionPrice($amount, 'HRK'),
                'HRK'
            );
        }

        return __('%1 x %2%3', $term, $amount, $this->getCurrencyCode());
    }

    /**
     * @param float $amount
     * @param false $useTerm
     * @return bool
     */
    public function shouldRenderTooltipPriceBlock(float $amount, $useTerm = false): bool
    {
        $data = $this->getToolTipData($amount, $useTerm);

        return isset(
            $data[InstallmentInterface::INSTALLMENT_AMOUNT],
            $data[InstallmentInterface::INSTALLMENT_PERIOD]
        );
    }

    /**
     * @param float $amount
     * @param int $preCalculatedValue
     * @return Phrase
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCategoryPriceBlock(float $amount, $preCalculatedValue = 0): \Magento\Framework\Phrase
    {
        if ($preCalculatedValue) {
            $price = $preCalculatedValue;
        } else {
            $price = $this->getLowestInstallmentPrice($amount);
        }

        if ($this->dataHelper->getApiType() === Data::API_ENDPOINT_CROATIA) {
            return __(
                'od %1 %2 / %3 %4 mjesečno',
                $price,
                'EUR',
                $this->getTransitionPrice($price, 'HRK'),
                'HRK'
            );
        }

        return __('ali od %1 %2 / mesec', $price, $this->getCurrencyCode());
    }

    /**
     * @param float $amount
     * @param int $preCalculatedValue
     * @return Phrase
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getProductPriceBlock(float $amount, $preCalculatedValue = 0): \Magento\Framework\Phrase
    {
        if ($preCalculatedValue) {
            $price = $preCalculatedValue;
        } else {
            $price = $this->getLowestInstallmentPrice($amount);
        }

        if ($this->dataHelper->getApiType() === Data::API_ENDPOINT_CROATIA) {
            return
                __(
                    '%1 %2 / %3 %4',
                    $price,
                    'EUR',
                    $this->getTransitionPrice($price, 'HRK'),
                    'HRK'
                );
        }

        return __('%1 %2', $price, $this->getCurrencyCode());
    }
}
