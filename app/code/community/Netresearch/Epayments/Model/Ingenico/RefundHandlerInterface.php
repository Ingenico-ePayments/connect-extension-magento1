<?php

/**
 * Interface Netresearch_Epayments_Model_Ingenico_RefundHandlerInterface
 */
interface Netresearch_Epayments_Model_Ingenico_RefundHandlerInterface
    extends Netresearch_Epayments_Model_Ingenico_Status_HandlerInterface
{
    const REFUND_CREATED          = 'CREATED';
    const REFUND_PENDING_APPROVAL = 'PENDING_APPROVAL';
    const REFUND_REJECTED         = 'REJECTED';
    const REFUND_REFUND_REQUESTED = 'REFUND_REQUESTED';
    const REFUND_CAPTURED         = 'CAPTURED';
    const REFUND_REFUNDED         = 'REFUNDED';
    const REFUND_CANCELLED        = 'CANCELLED';

    /**
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     * @return void
     */
    public function applyCreditmemo(Mage_Sales_Model_Order_Creditmemo $creditmemo);
}
