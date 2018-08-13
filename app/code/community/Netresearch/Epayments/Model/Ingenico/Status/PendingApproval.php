<?php

use Netresearch_Epayments_Model_Ingenico_Status_AbstractStatus as AbstractStatus;

class Netresearch_Epayments_Model_Ingenico_Status_PendingApproval extends AbstractStatus
{
    /**
     * {@inheritDoc}
     */
    public function _apply(Mage_Sales_Model_Order $order)
    {
        $payment = $order->getPayment();
        $amount = $this->ingenicoOrderStatus->paymentOutput->amountOfMoney->amount;
        $amount /= 100;

        $payment->setIsTransactionClosed(false);
        $payment->registerAuthorizationNotification($amount);
        $this->orderEMailManager->process($order, $this->getStatus());
    }
}
