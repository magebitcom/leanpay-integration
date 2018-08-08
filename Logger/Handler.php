<?php

namespace Leanpay\Payment\Logger;

use Magento\Framework\Logger\Handler\Base;
use Zend\Log\Logger;

class Handler extends Base
{
    /**
     * @var int
     */
    protected $loggerType = Logger::DEBUG;

    /**
     * @var string
     */
    protected $fileName = '/var/log/leanpay.log';
}