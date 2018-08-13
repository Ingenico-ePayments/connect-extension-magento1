<?php

use Netresearch_Epayments_Model_Ingenico_Status_AbstractStatus as AbstractStatus;
use \Ingenico\Connect\Sdk\Domain\Capture\Definitions\Capture;
use \Ingenico\Connect\Sdk\Domain\Payment\Definitions\Payment;
use Netresearch_Epayments_Model_Method_HostedCheckout as HostedCheckout;

class Netresearch_Epayments_Model_Ingenico_Status_CaptureRequested extends AbstractStatus
{
    /**
     * {@inheritDoc}
     */
    public function _apply(Mage_Sales_Model_Order $order)
    {
        $payment = $order->getPayment();

        if ($this->ingenicoOrderStatus instanceof Payment) {
            $amount = $this->ingenicoOrderStatus->paymentOutput->amountOfMoney->amount;
        } elseif ($this->ingenicoOrderStatus instanceof Capture) {
            $amount = $this->ingenicoOrderStatus->captureOutput->amountOfMoney->amount;
        }
        $amount /= 100;

        if (!$payment->getTransaction($payment->getAdditionalInformation(HostedCheckout::PAYMENT_ID_KEY))) {
            $captureRequestedStatus = Mage::getModel(
                'netresearch_epayments/ingenico_status_pendingCapture',
                array('gcOrderStatus' => $this->ingenicoOrderStatus)
            );
            $captureRequestedStatus->apply($order);
        }
        $payment->setIsTransactionPending(true);
        $payment->setIsTransactionClosed(false);
        $payment->registerCaptureNotification($amount);
        $this->orderEMailManager->process($order, $this->getStatus());
    }
}
