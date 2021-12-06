<?php
declare(strict_types=1);

namespace Leanpay\Payment\Controller\Checkout;

use Exception;
use Leanpay\Payment\Controller\AbstractAction;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;

class Api extends AbstractAction
{
    public const ORDER_STATUS_SUCCESS = 'SUCCESS';
    public const ORDER_STATUS_CANCEL = [
        'FAILED', 'CANCELED', 'EXPIRED'
    ];

    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return void
     * @throws Exception
     */
    public function execute()
    {
        $responseBody = $this->driver->fileGetContents('php://input');
        $result = $this->resultJsonFactory->create()->setData([
            'success' => false
        ]);

        if (empty($responseBody)) {
            return $result;
        }

        $leanpayData = json_decode($responseBody, true);

        $md5Secret = $this->helper->generateMD5Secret(
            $leanpayData['leanPayTransactionId'],
            $leanpayData['vendorTransactionId'],
            $leanpayData['amount'],
            $leanpayData['status']
        );

        if ($leanpayData['md5Signature'] != $md5Secret) {
            $this->logger->addError('Response: ' . $responseBody);
            $this->logger->addError('My md5: ' . $md5Secret);
            return $result;
        }

        $order = $this->findOrder($leanpayData['vendorTransactionId']);

        try {
            switch (true) {
                case $leanpayData['status'] == self::ORDER_STATUS_SUCCESS:
                    $this->orderSuccess($order);
                    break;

                case in_array($leanpayData['status'], self::ORDER_STATUS_CANCEL):
                    $this->orderCancel($order);
                    break;
            }
        } catch (Exception $exception) {
            $this->logger->addError(
                'There was error while trying to parse Leanpay API data: ' . $exception->getMessage()
            );
        }

        return $result->setData([
            'success' => true,
        ]);
    }

    /**
     * Set order to cancel
     *
     * @param Order $order
     */
    protected function orderCancel(Order $order)
    {
        $order->cancel();

        $this->orderRepository->save($order);
    }

    /**
     * Process order placement
     *
     * @param Order $order
     * @throws LocalizedException
     *
     * @return void
     */
    protected function orderSuccess(Order $order)
    {
        if (!$order->canInvoice()) {
            $this->logger->error("Can't Create invoice, order id: " . $order->getId());

            return;
        }

        $invoice = $this->invoiceService->prepareInvoice($order);

        if (!$invoice->getTotalQty()) {
            $this->logger->error("You can't create an invoice without products order id: " . $order->getId());

            return;
        }

        // Register as invoice item
        $invoice->setRequestedCaptureCase(Invoice::CAPTURE_OFFLINE);
        $invoice->register();

        // Save the invoice to the order
        $transaction = $this->transaction
            ->addObject($invoice)
            ->addObject($invoice->getOrder());

        $transaction->save();

        $this->invoiceSender->send($invoice);

        $order->addStatusHistoryComment(__('Notified customer about invoice #%1.', $invoice->getId()))
            ->setIsCustomerNotified(true);

        $order->setStatus(Order::STATE_PROCESSING);
        $order->setState(Order::STATE_PROCESSING);

        $this->orderRepository->save($order);
    }
}
