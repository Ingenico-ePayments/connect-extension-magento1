<?php

use \Ingenico\Connect\Sdk\Domain\Capture\Definitions\Capture;
use Ingenico\Connect\Sdk\Domain\Definitions\AbstractOrderStatus;
use \Ingenico\Connect\Sdk\Domain\Payment\Definitions\Payment;
use Netresearch_Epayments_Model_Method_HostedCheckout as HostedCheckout;
use Netresearch_Epayments_Model_Ingenico_Status_HandlerInterface as HandlerInterface;
use Netresearch_Epayments_Model_Order_EmailInterface as OrderEmailMananger;

class Netresearch_Epayments_Model_Ingenico_Status_CaptureRequested implements HandlerInterface
{
    /**
     * @var OrderEmailMananger
     */
    protected $orderEMailManager;

    /**
     * Netresearch_Epayments_Model_Ingenico_Status_CaptureRequested constructor.
     */
    public function __construct()
    {
        $this->orderEMailManager = Mage::getModel('netresearch_epayments/order_emailManager');
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
                Mage::helper('netresearch_epayments')->__('Unknown order status.')
            );
        }

        $amount /= 100;

        if (!$payment->getTransaction($payment->getAdditionalInformation(HostedCheckout::PAYMENT_ID_KEY))) {
            /** @var Netresearch_Epayments_Model_Ingenico_Status_PendingCapture $captureRequestedStatus */
            $captureRequestedStatus = Mage::getModel(
                'netresearch_epayments/ingenico_status_pendingCapture'
            );
            $captureRequestedStatus->resolveStatus($order, $ingenicoStatus);
        }

        $payment->setIsTransactionPending(true);
        $payment->setIsTransactionClosed(false);
        $payment->registerCaptureNotification($amount);
        $this->orderEMailManager->process($order, $ingenicoStatus->status);
    }

}
