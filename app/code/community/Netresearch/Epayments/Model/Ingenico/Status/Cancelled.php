<?php

use Ingenico\Connect\Sdk\Domain\Definitions\AbstractOrderStatus;
use Netresearch_Epayments_Model_Ingenico_Status_HandlerInterface as HandlerInterface;

/**
 * Class Netresearch_Epayments_Model_Ingenico_Status_Cancelled
 */
class Netresearch_Epayments_Model_Ingenico_Status_Cancelled implements HandlerInterface
{
    /**
     * @param Mage_Sales_Model_Order $order
     * @param AbstractOrderStatus $ingenicoStatus
     */
    public function resolveStatus(Mage_Sales_Model_Order $order, AbstractOrderStatus $ingenicoStatus)
    {
        /** @var Mage_Sales_Model_Order_Invoice $invoice */
        foreach ($order->getInvoiceCollection() as $invoice) {
            if ($invoice->getTransactionId() === $ingenicoStatus->id) {
                $invoice->cancel();
                $invoice->setState(Mage_Sales_Model_Order_Invoice::STATE_CANCELED);
                $order->addRelatedObject($invoice);
            }
        }

        $order->registerCancellation();
        $order->sendOrderUpdateEmail();
    }

}
