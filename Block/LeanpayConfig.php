<?php
declare(strict_types=1);

namespace Leanpay\Payment\Block;

use Leanpay\Payment\Helper\Data;
use Leanpay\Payment\Helper\InstallmentHelper;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class LeanpayConfig extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Leanpay_Payment::config.phtml';

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var InstallmentHelper
     */
    private $installmentHelper;

    /**
     * LeanpayConfig constructor.
     *
     * @param Context $context
     * @param Data $helper
     * @param InstallmentHelper $installmentHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $helper,
        InstallmentHelper  $installmentHelper,
        array $data = []
    ) {
        $this->installmentHelper = $installmentHelper;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Get leanpay configurations and store javascript config
     *
     * @return string
     */
    public function getConfig(): string
    {
        return json_encode([
            'allowspecific' => $this->helper->getConfigData('allowspecific'),
            'countries' => $this->getAllowedBillingCountries(),
            'instructions' => nl2br($this->helper->getInstructions()),
            'logo' => $this->getLogo(),
            'url' => $this->getUrl('/leanpay/installment/index')
        ]);
    }

    /**
     * Get billing country is allowed for the payment method
     *
     * @return array
     */
    public function getAllowedBillingCountries(): array
    {
        if ($this->helper->getConfigData('billing_allowspecific') == 1) {
            return explode(',', $this->helper->getConfigData('billing_specificcountry'));
        }

        return [];
    }

    /**
     * Returns logo
     *
     * @return string
     */
    public function getLogo(): string
    {
        if (!$this->installmentHelper->isDarkThemeLogo()) {
            return $this->getViewFileUrl('Leanpay_Payment::images/leanpay.svg');
        }

        return $this->getViewFileUrl('Leanpay_Payment::images/dark-leanpay.svg');
    }
}
