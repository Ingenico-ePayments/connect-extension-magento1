<?php

use Netresearch_Epayments_Model_Ingenico_Status_Refund_AbstractStatus as AbstractStatus;

class Netresearch_Epayments_Model_Ingenico_Status_Refund_Null extends AbstractStatus
{
    /**
     * @param Mage_Sales_Model_Order $order
     * @throws Mage_Core_Exception
     */
    public function _apply(Mage_Sales_Model_Order $order)
    {
        Mage::throwException('Status is not implemented');
    }

    /**
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     * @throws Mage_Core_Exception
     */
    public function applyCreditmemo(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        Mage::throwException('Status is not implemented');
    }
}
