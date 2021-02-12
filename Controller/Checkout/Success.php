<?php
declare(strict_types=1);

namespace Leanpay\Payment\Controller\Checkout;

use Leanpay\Payment\Controller\AbstractAction;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Result\Page;
use Magento\Sales\Model\Order;

/**
 * Class Success
 *
 * @package Leanpay\Payment\Controller\Checkout
 */
class Success extends AbstractAction
{
    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return ResponseInterface|Page
     */
    public function execute()
    {
        return $this->resultPageFactory->create();
    }
}
