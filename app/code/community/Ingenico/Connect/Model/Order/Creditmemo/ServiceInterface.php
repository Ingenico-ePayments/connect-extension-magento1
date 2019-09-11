<?php

/**
 * Interface Ingenico_Connect_Model_Order_Creditmemo_ServiceInterface
 */
interface Ingenico_Connect_Model_Order_Creditmemo_ServiceInterface
{
    /**
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param string|int|null $transactionId
     * @return Mage_Sales_Model_Order_Creditmemo
     */
    public function getCreditmemo(Mage_Sales_Model_Order_Payment $payment, $transactionId = null);

    /**
     * @param string|int $transactionId
     * @return Mage_Sales_Model_Order_Creditmemo
     */
    public function getCreditMemoByTxnId($transactionId);
}
