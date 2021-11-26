<?php
declare(strict_types=1);

namespace Leanpay\Payment\Logger;

use Magento\Framework\Logger\Handler\Base;
use Magento\Framework\Logger\Monolog;

/**
 * Class Handler
 */
class InstallmentSync extends Base
{
    /**
     * @var int
     */
    protected $loggerType = Monolog::DEBUG;

    /**
     * @var string
     */
    protected $fileName = '/var/log/installment.log';
}
