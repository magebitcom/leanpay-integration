<?php

namespace Leanpay\Payment\Controller\Checkout;

use Leanpay\Payment\Controller\AbstractAction;
use Magento\Sales\Model\Order;

class Success extends AbstractAction
{
    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $order = $this->checkoutSession->getLastRealOrder();

        if ($order->getId() && $order->getStatus() == Order::STATE_NEW) {
            $order->setStatus(Order::STATE_PENDING_PAYMENT);
            $order->setState(Order::STATE_PENDING_PAYMENT);

            $this->orderRepository->save($order);

            return $this->resultPageFactory->create();
        }

        return $this->_redirect('/');
    }
}