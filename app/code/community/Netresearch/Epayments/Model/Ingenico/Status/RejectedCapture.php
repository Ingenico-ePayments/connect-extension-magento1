<?php

use Netresearch_Epayments_Model_Ingenico_Status_AbstractStatus as AbstractStatus;

class Netresearch_Epayments_Model_Ingenico_Status_RejectedCapture extends AbstractStatus
{
    /**
     * {@inheritDoc}
     */
    public function _apply(Mage_Sales_Model_Order $order)
    {
        /** @var Mage_Sales_Model_Order_Invoice $invoice */
        $invoice = $this->getInvoiceForTransactionId($this->ingenicoOrderStatus->id, $order);
        if ($invoice) {
            $invoice->cancel();
        }
    }

    /**
     * Return invoice model for transaction
     *
     * @param string $transactionId
     * @param Mage_Sales_Model_Order $order
     * @return Mage_Sales_Model_Order_Invoice|false
     */
    protected function getInvoiceForTransactionId($transactionId, $order)
    {
        foreach ($order->getInvoiceCollection() as $invoice) {
            if ($invoice->getTransactionId() == $transactionId) {
                $invoice->load($invoice->getId());
                return $invoice;
            }
        }
        return false;
    }
}
