<?php
declare(strict_types=1);

namespace Leanpay\Payment\Controller\Checkout;

use Exception;
use Leanpay\Payment\Controller\AbstractAction;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Sales\Model\Order;

class Failure extends AbstractAction
{
    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return Redirect
     */
    public function execute()
    {
        try {
            $order = $this->checkoutSession->getLastRealOrder();

            if ($order->getState() == Order::STATE_NEW) {
                $order->cancel();
                $this->orderRepository->save($order);

                $this->checkoutSession->restoreQuote();
            }
            $this->checkoutSession->setErrorMessage('Your order closed, because not finished leanpay!');
            return $this->resultRedirectFactory->create()->setPath($this->helper->getMagentoCheckoutUrl());
        } catch (Exception $exception) {
            return $this->resultRedirectFactory->create()->setPath('/');
        }
    }
}
