<?php

namespace Leanpay\Payment\Block;

use Leanpay\Payment\Helper\Data;
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
     * LeanpayConfig constructor.
     *
     * @param Context $context
     * @param Data $helper
     * @param array $data
     */
    public function __construct(Context $context, Data $helper, array $data = [])
    {
        parent::__construct($context, $data);

        $this->helper = $helper;
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
            'logo' => $this->getViewFileUrl('Leanpay_Payment::images/leanpay.svg')
        ]);
    }
    
    /**
     * get billing country is allowed for the payment method
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
}