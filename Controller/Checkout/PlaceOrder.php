<?php

namespace Leanpay\Payment\Controller\Checkout;

use Leanpay\Payment\Controller\AbstractAction;
use Magento\Framework\App\ResponseInterface;

class PlaceOrder extends AbstractAction
{
    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $token = $this->checkoutSession->getToken();

        if (!$token) {
            return $this->_redirect('/');
        }

        return $this->_redirect($this->helper->getCheckoutUrl().'?token='.$token);
    }
}