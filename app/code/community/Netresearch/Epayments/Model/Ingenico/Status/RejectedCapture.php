<?php

use Ingenico\Connect\Sdk\Domain\Definitions\AbstractOrderStatus;
use Netresearch_Epayments_Model_Ingenico_Status_HandlerInterface as HandlerInterface;

/**
 * Class Netresearch_Epayments_Model_Ingenico_Status_RejectedCapture
 */
class Netresearch_Epayments_Model_Ingenico_Status_RejectedCapture implements HandlerInterface
{
    /**
     * @param Mage_Sales_Model_Order $order
     * @param AbstractOrderStatus $ingenicoStatus
     */
    public function resolveStatus(Mage_Sales_Model_Order $order, AbstractOrderStatus $ingenicoStatus)
    {
        $invoice = $this->getInvoiceForTransactionId($ingenicoStatus->id, $order);
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
    protected function getInvoiceForTransactionId($transactionId, Mage_Sales_Model_Order $order)
    {
        foreach ($order->getInvoiceCollection() as $invoice) {
            if ($invoice->getTransactionId() === $transactionId) {
                $invoice->load($invoice->getId());

                return $invoice;
            }
        }

        return false;
    }
}
