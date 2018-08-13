<?php

use Netresearch_Epayments_Model_Ingenico_Status_Refund_AbstractStatus as AbstractStatus;

class Netresearch_Epayments_Model_Ingenico_Status_Refund_RefundRequested extends AbstractStatus
{
    /**
     * {@inheritDoc}
     */
    public function _apply(Mage_Sales_Model_Order $order)
    {
        $payment = $order->getPayment();
        /** @var Mage_Sales_Model_Order_Creditmemo $creditmemo */
        $creditmemo = $this->getCreditmemo($payment);

        if ($creditmemo->getId()) {
            $this->applyCreditmemo($creditmemo);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function applyCreditmemo(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $creditmemo->setState(Mage_Sales_Model_Order_Creditmemo::STATE_OPEN);
    }
}
