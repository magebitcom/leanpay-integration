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
     * @return string
     */
    public function getConfig(): string
    {
        return json_encode([
            'instructions' => nl2br($this->helper->getInstructions()),
            'logo' => $this->getViewFileUrl('Leanpay_Payment::images/leanpay.svg')
        ]);
    }
}