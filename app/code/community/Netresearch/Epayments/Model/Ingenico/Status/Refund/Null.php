<?php

use Ingenico\Connect\Sdk\Domain\Definitions\AbstractOrderStatus;
use Netresearch_Epayments_Model_Ingenico_RefundHandlerInterface as RefundHandlerInterface;

/**
 * Class Netresearch_Epayments_Model_Ingenico_Status_Refund_Null
 */
class Netresearch_Epayments_Model_Ingenico_Status_Refund_Null implements RefundHandlerInterface
{
    /**
     * @param Mage_Sales_Model_Order $order
     * @param AbstractOrderStatus $ingenicoStatus
     * @throws Mage_Core_Exception
     */
    public function resolveStatus(Mage_Sales_Model_Order $order, AbstractOrderStatus $ingenicoStatus)
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
