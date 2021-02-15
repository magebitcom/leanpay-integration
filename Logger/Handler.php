<?php
declare(strict_types=1);

namespace Leanpay\Payment\Logger;

use Magento\Framework\Logger\Handler\Base;
use Magento\Framework\Logger\Monolog;

class Handler extends Base
{
    /**
     * @var int
     */
    protected $loggerType = Monolog::DEBUG;

    /**
     * @var string
     */
    protected $fileName = '/var/log/leanpay.log';
}
