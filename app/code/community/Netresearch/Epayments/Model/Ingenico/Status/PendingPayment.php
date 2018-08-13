<?php

use Netresearch_Epayments_Model_Ingenico_StatusInterface as StatusInterface;
use Netresearch_Epayments_Model_Ingenico_Status_AbstractStatus as AbstractStatus;
/**
 * Class Netresearch_Epayments_Model_Ingenico_Status_PendingPayment
 */
class Netresearch_Epayments_Model_Ingenico_Status_PendingPayment extends AbstractStatus
{
    /**
     * {@inheritDoc}
     */
    public function _apply(Mage_Sales_Model_Order $order)
    {
        $order->getPayment()->setIsTransactionClosed(false);
        $order->getPayment()->addTransaction(
            Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH,
            $order,
            false,
            Mage::helper('netresearch_epayments')->getPaymentStatusInfo(StatusInterface::PENDING_PAYMENT)
        );

        $this->orderEMailManager->process(
            $order,
            $this->getStatus()
        );
    }
}
