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
     * Leanpay MIN order allowed price
     */
    public const LEANPAY_INSTALLMENT_MIN = 'payment/leanpay/min_order_total';

    /**
     * Leanpay MAX order allowed price
     */
    public const LEANPAY_INSTALLMENT_MAX = 'payment/leanpay/max_order_total';

    /**
     * Leanpay Installment amount threshold
     */
    public const LEANPAY_INSTALLMENT_AMOUNT_THRESHOLD = 'payment/leanpay_installment/advanced/amount_threshold';

    /**
     * Leanpay Installment default installment count for amounts <= threshold
     */
    public const LEANPAY_INSTALLMENT_DEFAULT_COUNT = 'payment/leanpay_installment/advanced/default_installment_count';

    /**
     * Leanpay Installment under threshold text
     */
    public const LEANPAY_INSTALLMENT_UNDER_THRESHOLD_TEXT = 'payment/leanpay_installment/advanced/under_threshold_text';

    /**
     * Leanpay Installment PLP background color
     */
    public const LEANPAY_INSTALLMENT_PLP_BACKGROUND_COLOR = 'payment/leanpay_installment/advanced/plp_background_color';

    /**
     * Leanpay Installment PDP text color
     */
    public const LEANPAY_INSTALLMENT_PDP_TEXT_COLOR = 'payment/leanpay_installment/advanced/pdp_text_color';

    /**
     * Leanpay Installment tooltip quick information text (PDP)
     */
    public const LEANPAY_INSTALLMENT_QUICK_INFORMATION = 'payment/leanpay_installment/advanced/quick_information';


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
     * Get amount threshold
     *
     * @return float|null
     */
    public function getAmountThreshold()
    {
        $threshold = $this->scopeConfig->getValue(
            self::LEANPAY_INSTALLMENT_AMOUNT_THRESHOLD,
            ScopeInterface::SCOPE_STORE
        );

        if ($threshold === null || $threshold === '') {
            return 300.0; // Default threshold
        }

        return (float) $threshold;
    }

    /**
     * Get default installment count for amounts <= threshold
     *
     * @return int
     */
    public function getDefaultInstallmentCount(): int
    {
        $count = $this->scopeConfig->getValue(
            self::LEANPAY_INSTALLMENT_DEFAULT_COUNT,
            ScopeInterface::SCOPE_STORE
        );

        if ($count === null || $count === '') {
            return 3; // Default count
        }

        return (int) $count;
    }

    /**
     * Check if amount is within threshold (<= threshold)
     *
     * @param float $amount
     * @return bool
     */
    public function isWithinThreshold(float $amount): bool
    {
        $threshold = $this->getAmountThreshold();
        return $amount <= $threshold;
    }

    /**
     * Check if amount meets threshold requirement
     *
     * @param float $amount
     * @return bool
     */
    public function meetsAmountThreshold(float $amount): bool
    {
        $threshold = $this->getAmountThreshold();

        if ($threshold === null) {
            return true; // No threshold set, show for all amounts
        }

        return $amount >= $threshold;
    }

    /**
     * Get under threshold text
     *
     * @return string
     */
    public function getUnderThresholdText(): string
    {
        $text = (string) $this->scopeConfig->getValue(
            self::LEANPAY_INSTALLMENT_UNDER_THRESHOLD_TEXT,
            ScopeInterface::SCOPE_STORE
        );

        return (string) preg_replace('/\bEUR\b/u', '€', $text);
    }

    /**
     * Get PLP background color
     *
     * @return string
     */
    public function getPlpBackgroundColor(): string
    {
        return (string) $this->scopeConfig->getValue(
            self::LEANPAY_INSTALLMENT_PLP_BACKGROUND_COLOR,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get PDP text color
     *
     * @return string
     */
    public function getPdpTextColor(): string
    {
        return (string) $this->scopeConfig->getValue(
            self::LEANPAY_INSTALLMENT_PDP_TEXT_COLOR,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get tooltip quick information (PDP)
     */
    public function getQuickInformation(): string
    {
        $value = (string) $this->scopeConfig->getValue(
            self::LEANPAY_INSTALLMENT_QUICK_INFORMATION,
            ScopeInterface::SCOPE_STORE
        );

        return $value;
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
     * Get lowest installment price
     *
     * @param float $price
     * @param string $group
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
        // If amount is within threshold, return price for default installment count
        if ($this->isWithinThreshold($price)) {
            $defaultCount = $this->getDefaultInstallmentCount();
            $installmentList = $this->resourceModel->getInstallmentList($price, $group);

            // Search for installment matching the default count
            foreach ($installmentList as $installmentData) {
                if (isset($installmentData[InstallmentInterface::INSTALLMENT_PERIOD]) &&
                    isset($installmentData[InstallmentInterface::INSTALLMENT_AMOUNT]) &&
                    (int)$installmentData[InstallmentInterface::INSTALLMENT_PERIOD] === $defaultCount) {
                    return (string) $installmentData[InstallmentInterface::INSTALLMENT_AMOUNT];
                }
            }

            // Fallback: if exact match not found, return lowest installment
            return $this->resourceModel->getLowestInstallment($price, $group, $this->dataHelper->getApiType());
        }

        // For amounts above threshold, return lowest installment price
        return $this->resourceModel->getLowestInstallment($price, $group, $this->dataHelper->getApiType());
    }

    /**
     * Get installment list
     *
     * @param float $price
     * @param string $group
     * @return array
     */
    public function getInstallmentList(float $price, $group = '')
    {
        if (!$price) {
            return [];
        }

        // Determine which group to use
        $targetGroup = $group ?: $this->getGroup();

        // Get full installment list
        $installmentList = $this->resourceModel->getInstallmentList($price, $targetGroup);

        // If amount is within threshold, return only the default installment
        if ($this->isWithinThreshold($price)) {
            $defaultCount = $this->getDefaultInstallmentCount();

            // Search for installment matching the default count
            foreach ($installmentList as $period => $installmentData) {
                if (isset($installmentData[InstallmentInterface::INSTALLMENT_PERIOD]) &&
                    (int)$installmentData[InstallmentInterface::INSTALLMENT_PERIOD] === $defaultCount) {
                    // Return array with single entry matching the default count
                    return [$period => $installmentData];
                }
            }

            // Fallback: if exact match not found, return first available installment
            if (!empty($installmentList)) {
                $firstKey = array_key_first($installmentList);
                return [$firstKey => $installmentList[$firstKey]];
            }

            // Final fallback: return empty array
            return [];
        }

        // For amounts above threshold, return full list
        return $installmentList;
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

        if ($group) {
            return $this->resourceModel->getToolTipData($price, $group, $useTerm);
        }

        return $this->resourceModel->getToolTipData($price, $this->getGroup(), $useTerm);
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
            return '€';
        } elseif ($this->dataHelper->getApiType() === Data::API_ENDPOINT_ROMANIA) {
            return 'RON';
        } else {
            return '€';
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
        // Check if amount meets threshold
        if (!$this->meetsAmountThreshold($amount)) {
            $underThresholdText = $this->getUnderThresholdText();
            if ($underThresholdText) {
                return __($underThresholdText, $amount);
            }
            // Fallback to just showing the amount if no custom text is configured
            return __((string) $amount);
        }

        // Amount meets threshold, show installment price
        if ($preCalculatedValue) {
            $price = $preCalculatedValue;
        } else {
            $price = $this->getLowestInstallmentPrice($amount);
        }

        if ($this->dataHelper->getApiType() === Data::API_ENDPOINT_CROATIA) {
            return __(
                'od %1 %2 / %3 %4 mjesečno',
                $price,
                '€',
                $this->getTransitionPrice($price, 'HRK'),
                'HRK'
            );
        }

        return __('ali od %1 %2 / mesec', $price, $this->getCurrencyCode());
    }

    /**
     * Get installment period for a given price
     *
     * @param float $amount
     * @param float|string $installmentPrice
     * @param string $group
     * @return int
     */
    public function getInstallmentPeriodForPrice(float $amount, $installmentPrice, $group = ''): int
    {
        $installmentList = $this->getInstallmentList($amount, $group);
        $installmentPriceFloat = (float) $installmentPrice;

        // Search for installment matching the price
        foreach ($installmentList as $item) {
            if (isset($item[InstallmentInterface::INSTALLMENT_PERIOD]) &&
                isset($item[InstallmentInterface::INSTALLMENT_AMOUNT])) {
                $itemAmount = (float) $item[InstallmentInterface::INSTALLMENT_AMOUNT];

                // Compare with small tolerance for floating point precision
                if (abs($itemAmount - $installmentPriceFloat) < 0.01) {
                    return (int) $item[InstallmentInterface::INSTALLMENT_PERIOD];
                }
            }
        }

        // If within threshold and no match found, use default count
        if ($this->isWithinThreshold($amount)) {
            return $this->getDefaultInstallmentCount();
        }

        return 0;
    }

    /**
     * Get maximum installment period from list
     *
     * @param float $amount
     * @param string $group
     * @return int
     */
    public function getMaxInstallmentPeriod(float $amount, $group = ''): int
    {
        $installmentList = $this->getInstallmentList($amount, $group);
        $maxPeriod = 0;

        foreach ($installmentList as $item) {
            if (isset($item[InstallmentInterface::INSTALLMENT_PERIOD])) {
                $period = (int) $item[InstallmentInterface::INSTALLMENT_PERIOD];
                $maxPeriod = max($maxPeriod, $period);
            }
        }

        return $maxPeriod;
    }

    /**
     * Get product price only (without "from" and /mesec) for amounts above threshold
     *
     * @param float $amount
     * @param int $preCalculatedValue
     * @return Phrase
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getProductPriceOnly(float $amount, $preCalculatedValue = 0): \Magento\Framework\Phrase
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
                    '€',
                    $this->getTransitionPrice($price, 'HRK'),
                    'HRK'
                );
        }

        return __('%1 %2', $price, $this->getCurrencyCode());
    }

    /**
     * Get product price block with /mesec format for amounts above threshold
     *
     * @param float $amount
     * @param int $preCalculatedValue
     * @return Phrase
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getProductPriceBlockWithMonth(float $amount, $preCalculatedValue = 0): \Magento\Framework\Phrase
    {
        if ($preCalculatedValue) {
            $price = $preCalculatedValue;
        } else {
            $price = $this->getLowestInstallmentPrice($amount);
        }

        if ($this->dataHelper->getApiType() === Data::API_ENDPOINT_CROATIA) {
            return
                __(
                    'from %1 %2 / %3 %4 /mesec',
                    $price,
                    '€',
                    $this->getTransitionPrice($price, 'HRK'),
                    'HRK'
                );
        }

        return __('from %1 %2 /mesec', $price, $this->getCurrencyCode());
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
                    '€',
                    $this->getTransitionPrice($price, 'HRK'),
                    'HRK'
                );
        }

        return __('%1 %2', $price, $this->getCurrencyCode());
    }
}
