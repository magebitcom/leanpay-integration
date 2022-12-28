<?php
declare(strict_types=1);

namespace Leanpay\Payment\Helper;

use Leanpay\Payment\Api\Data\InstallmentInterface;
use Leanpay\Payment\Model\Config\Source\ViewBlockConfig;
use Leanpay\Payment\Model\ResourceModel\Installment;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

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
     * Fixed conversion rate during euro transition period
     */
    public const TRANSITION_CONVERSION_RATE = 7.53450;

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
        return (string)$this->scopeConfig->getValue(self::LEANPAY_INSTALLMENT_GROUP, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get installment color
     *
     * @return string
     */
    public function getInstallmentColor()
    {
        return (string)$this->scopeConfig->getValue(self::LEANPAY_INSTALLMENT_COLOR);
    }

    /**
     * Get homepage font size
     *
     * @return string
     */
    public function getHomepageFontSize()
    {
        return (string)$this->scopeConfig->getValue(self::LEANPAY_INSTALLMENT_FONT_HOMEPAGE);
    }

    /**
     * Get catalog font size
     *
     * @return string
     */
    public function getCatalogFontSize()
    {
        return (string)$this->scopeConfig->getValue(self::LEANPAY_INSTALLMENT_FONT_CATEGORY_PAGE);
    }

    /**
     * Get product font size
     *
     * @return string
     */
    public function getProductFontSize()
    {
        return (string)$this->scopeConfig->getValue(self::LEANPAY_INSTALLMENT_FONT_PRODUCT_PAGE);
    }

    /**
     * Get more info url
     *
     * @return string
     */
    public function getMoreInfoURL()
    {
        return (string)$this->scopeConfig->getValue(self::LEANPAY_INSTALLMENT_MORE_INFO);
    }

    /**
     * Get check your limit url
     *
     * @return string
     */
    public function getCheckYourLimitURL()
    {
        return (string)$this->scopeConfig->getValue(self::LEANPAY_INSTALLMENT_CHECK_YOUR_LIMIT);
    }

    /**
     * Get allowed views
     *
     * @return string
     */
    public function getAllowedViews()
    {
        return (string)$this->scopeConfig->getValue(self::LEANPAY_INSTALLMENT_ALLOWED_VIEWS);
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

        $currentStoreCurrency = $this->storeManager->getStore()->getCurrentCurrency()->getCode();
        if ($currentStoreCurrency !== "HRK" && $currentStoreCurrency !== "EUR") {
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
     * @param string $price
     * @return string
     */
    public function getLowestInstallmentPrice($price)
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

        return $this->resourceModel->getLowestInstallment($price, $this->getGroup(), $this->dataHelper->getApiType());
    }

    /**
     * Get installment list
     *
     * @param float $price
     * @return array
     */
    public function getInstallmentList($price)
    {
        return $this->resourceModel->getInstallmentList($price, $this->getGroup());
    }

    /**
     * Get tooltip data
     *
     * @param float $price
     * @param bool $useTerm
     * @return string
     */
    public function getToolTipData($price, $useTerm)
    {
        if (!$price) {
            return '';
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

        return (string)$result;
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

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrency(): string
    {
        return $this->scopeConfig->getValue(Data::LEANPAY_CONFIG_CURRENCY,
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
            return "HRK";
        } else {
            return "€";
        }
    }

    /**
     * @param $text
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getTranslation($text): string
    {
        $translationArray = [
            'Hitro in enostavno obročno odplačevanje' => 'Brz i jednostavan izračun rata',
            'Že od' => 'Već od',
            'Vaš mesečni obrok' => 'mjesečno',
            'Izračun obrokov' => 'Klikni za izračun',
            'Želim čim nižji obrok' => 'Želim što niži iznos rate',
            'Odplačati želim čim prej' => 'Želim otplatiti što prije',
            'Želim si izbrati svoje obroke' => 'Želim sam odabarati broj rata',
            'Informativni znesek za plačilo' => '',
            'Leanpay omogoča hitro in enostavno obročno odplačevanje preko spleta. ' => 'Leanpay omogućuje brzo i jednostavno plaćanje na rate preko interneta. ',
            'Za obročno plačilo v košarici izberi Leanpay. ' => 'Za plaćanje na rate u košarici odaberite Leanpay kao vrstu plaćanja. ',
            'Informativni izračun ne vključuje stroškov ocene tveganja.' => 'Informativni izračun ne uključuje troškove procjene rizika.',
            'Preveri svoj limit' => 'Provjerite svoj limit',
            'Več informacij' => 'Više informacija'
        ];
        if ($this->dataHelper->getApiType() === Data::API_ENDPOINT_CROATIA && isset($translationArray[$text])) {
            return $translationArray[$text];
        } else {
            return $text;
        }
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function allowDownPayment(): bool
    {
        return $this->dataHelper->getApiType() === Data::API_ENDPOINT_SLOVENIA;
    }

    /**
     * @param string $price
     * @return string
     */
    public function getTransitionPrice (string $price): string
    {
        $convertedPrice = $price * self::TRANSITION_CONVERSION_RATE;
        return (string) round($convertedPrice,2);
    }

    /**
     * @param string $price
     * @return string
     */
    public function getTransitionPriceHkrToEur (string $price): string
    {
        $convertedPrice = $price / self::TRANSITION_CONVERSION_RATE;
        return (string) round($convertedPrice,2);
    }

    /**
     * Returns Json config
     *
     * @return string
     */
    public function getJsonConfig($amount)
    {
        $list = $this->getInstallmentList($amount);
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
            'currency' => 'EUR',
        ];
        if ($this->dataHelper->getApiType() === Data::API_ENDPOINT_CROATIA) {
            $convertedValues = [];
            foreach ($list as $value) {
                $convertedValues[] = $value[InstallmentInterface::INSTALLMENT_AMOUNT] * self::TRANSITION_CONVERSION_RATE;;
            }
            $data['convertedCurrency'] = 'HRK';
            $data['convertedValues'] = $convertedValues;
        }

        return (string) $this->serializer->serialize($data);
    }

    /**
     * @param $amount
     * @param false $useTerm
     * @return \Magento\Framework\Phrase
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getTooltipPriceBlock($amount, $useTerm = false): \Magento\Framework\Phrase
    {
        $data = $this->getToolTipData($amount, $useTerm);
        $term = $data[InstallmentInterface::INSTALLMENT_PERIOD];
        $amount = $data[InstallmentInterface::INSTALLMENT_AMOUNT];
        if ($this->dataHelper->getApiType() === Data::API_ENDPOINT_CROATIA) {
            return __(
                '%1 x %2%3 / %4%5',
                $term,
                $amount,
                'EUR',
                $this->getTransitionPrice($amount),
                $this->getCurrencyCode()
            );
        }
        return __('%1 x %2%3', $term, $amount, $this->getCurrencyCode());
    }

    /**
     * @param $amount
     * @param false $useTerm
     * @return bool
     */
    public function shouldRenderTooltipPriceBlock($amount,$useTerm = false): bool
    {
        $data = $this->getToolTipData($amount, $useTerm);
        return isset(
            $data[InstallmentInterface::INSTALLMENT_AMOUNT],
            $data[InstallmentInterface::INSTALLMENT_PERIOD]
        );
    }

    /**
     * @param $amount
     * @return \Magento\Framework\Phrase
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCategoryPriceBlock($amount): \Magento\Framework\Phrase
    {
        $price = $this->getLowestInstallmentPrice($amount);
        if ($this->dataHelper->getApiType() === Data::API_ENDPOINT_CROATIA) {
            return __(
                'od %1 %2 / %3 %4 mjesečno',
                $price,
                'EUR',
                $this->getTransitionPrice($price),
                $this->getCurrencyCode()
            );
        }
        return __('ali od %1 %2 / mesec', $price, $this->getCurrencyCode());
    }

    /**
     * @param $amount
     * @return \Magento\Framework\Phrase
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductPriceBlock($amount): \Magento\Framework\Phrase
    {
        $price = $this->getLowestInstallmentPrice($amount);
        if ($this->dataHelper->getApiType() === Data::API_ENDPOINT_CROATIA) {
            return
                __(
                    '%1 %2 / %3 %4',
                    $price,
                    'EUR',
                    $this->getTransitionPrice($price),
                    $this->getCurrencyCode()
                );
        }
        return __('%1 %2', $price, $this->getCurrencyCode());
    }
}
