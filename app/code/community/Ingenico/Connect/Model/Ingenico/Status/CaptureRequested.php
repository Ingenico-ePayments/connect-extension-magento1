<?php

use \Ingenico\Connect\Sdk\Domain\Capture\Definitions\Capture;
use Ingenico\Connect\Sdk\Domain\Definitions\AbstractOrderStatus;
use \Ingenico\Connect\Sdk\Domain\Payment\Definitions\Payment;
use Ingenico_Connect_Model_Method_HostedCheckout as HostedCheckout;
use Ingenico_Connect_Model_Ingenico_Status_HandlerInterface as HandlerInterface;
use Ingenico_Connect_Model_Order_EmailInterface as OrderEmailMananger;

class Ingenico_Connect_Model_Ingenico_Status_CaptureRequested implements HandlerInterface
{
    /**
     * @var OrderEmailMananger
     */
    protected $orderEMailManager;

    /**
     * Ingenico_Connect_Model_Ingenico_Status_CaptureRequested constructor.
     */
    public function __construct()
    {
        $this->orderEMailManager = Mage::getModel('ingenico_connect/order_emailManager');
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param AbstractOrderStatus $ingenicoStatus
     */
    public function resolveStatus(Mage_Sales_Model_Order $order, AbstractOrderStatus $ingenicoStatus)
    {
        $payment = $order->getPayment();

        if ($ingenicoStatus instanceof Payment) {
            $amount = $ingenicoStatus->paymentOutput->amountOfMoney->amount;
        } elseif ($ingenicoStatus instanceof Capture) {
            $amount = $ingenicoStatus->captureOutput->amountOfMoney->amount;
        } else {
            Mage::throwException(
                Mage::helper('ingenico_connect')->__('Unknown order status.')
            );
        }

        $amount /= 100;

        if (!$payment->getTransaction($payment->getAdditionalInformation(HostedCheckout::PAYMENT_ID_KEY))) {
            /** @var Ingenico_Connect_Model_Ingenico_Status_PendingCapture $captureRequestedStatus */
            $captureRequestedStatus = Mage::getModel(
                'ingenico_connect/ingenico_status_pendingCapture'
            );
            $captureRequestedStatus->resolveStatus($order, $ingenicoStatus);
        }

        $payment->setIsTransactionPending(true);
        $payment->setIsTransactionClosed(false);
        $payment->registerCaptureNotification($amount);
        $this->orderEMailManager->process($order, $ingenicoStatus->status);
    }

}
