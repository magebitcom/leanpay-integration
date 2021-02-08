<?php

namespace Leanpay\Payment\Logger;

use Magento\Framework\Logger\Handler\Base;
use Zend\Log\Logger;

/**
 * Class Handler
 *
 * @package Leanpay\Payment\Logger
 */
class InstallmentSync extends Base
{
    /**
     * @var int
     */
    protected $loggerType = Logger::DEBUG;

    /**
     * @var string
     */
    protected $fileName = '/var/log/installment.log';
}
