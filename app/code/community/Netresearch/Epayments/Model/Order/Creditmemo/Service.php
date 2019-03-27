<?php

use Netresearch_Epayments_Model_Order_Creditmemo_ServiceInterface as ServiceInterface;

/**
 * Class Netresearch_Epayments_Model_Order_Creditmemo_Service
 */
class Netresearch_Epayments_Model_Order_Creditmemo_Service implements ServiceInterface
{
    /**
     * @var Mage_Sales_Model_Order_Creditmemo[]
     */
    protected $creditmemos = array();

    /**
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param int|null $transactionId
     * @return Mage_Sales_Model_Order_Creditmemo
     * @throws Mage_Core_Exception
     */
    public function getCreditmemo(Mage_Sales_Model_Order_Payment $payment, $transactionId = null)
    {
        if ($payment->getCreditmemo() !== null) {
            return $payment->getCreditmemo();
        }

        if ($transactionId !== null) {
            return $this->getCreditMemoByTxnId($transactionId);
        }

        Mage::throwException(Mage::helper('netresearch_epayments')->__('No creditmemo found'));
    }

    /**
     * @param $transactionId
     * @return mixed
     * @throws Mage_Core_Exception
     */
    public function getCreditMemoByTxnId($transactionId)
    {
        if (!array_key_exists($transactionId, $this->creditmemos)) {
            $creditmemos = Mage::getResourceModel('sales/order_creditmemo_collection')
                               ->addFilter('transaction_id', $transactionId)
                               ->getItems();
            $creditmemo = array_shift($creditmemos);
            if ($creditmemo === null) {
                Mage::throwException(
                    Mage::helper('netresearch_epayments')->__(
                        'No creditmemo for transaction id %transactionId found.',
                        array('transactionId' => $transactionId)
                    )
                );
            }

            $this->creditmemos[$transactionId] = $creditmemo;
        }

        return $this->creditmemos[$transactionId];
    }
}
