<?php

use \Ingenico\Connect\Sdk\Domain\Definitions\AbstractOrderStatus;
use Netresearch_Epayments_Model_Ingenico_RefundHandlerInterface as RefundHandlerInterface;

/**
 * Class Netresearch_Epayments_Model_Ingenico_Status_Refund_Cancelled
 */
class Netresearch_Epayments_Model_Ingenico_Status_Refund_Cancelled implements RefundHandlerInterface
{
    /**
     * @var Netresearch_Epayments_Model_Order_Creditmemo_ServiceInterface
     */
    protected $creditmemoService;

    /**
     * @var string[]
     */
    protected static $totals = array(
        'total_refunded' => 'grand_total',
        'base_total_refunded' => 'base_grand_total',
        'subtotal',
        'base_subtotal',
        'base_tax_refunded' => 'base_tax_amount',
        'tax_refunded' => 'tax_amount',
        'base_hidden_tax_refunded' => 'base_hidden_tax_amount',
        'hidden_tax_refunded' => 'hidden_tax_amount',
        'base_shipping_refunded' => 'base_shipping_amount',
        'shipping_refunded' => 'shipping_amount',
        'base_shipping_tax_refunded' => 'base_shipping_tax_amount',
        'shipping_tax_refunded' => 'shipping_tax_amount',
        'base_adjustment_negative',
        'adjustment_negative',
        'base_adjustment_positive',
        'adjustment_positive',
        'discount_refunded' => 'discount_amount',
        'base_discount_refunded' => 'base_discount_amount',
        'base_total_online_refunded' => 'base_grand_total',
        'total_online_refunded' => 'grand_total'
    );

    /**
     * Netresearch_Epayments_Model_Ingenico_Status_Refund_Cancelled constructor.
     */
    public function __construct()
    {
        $this->creditmemoService = Mage::getSingleton('netresearch_epayments/order_creditmemo_service');
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param AbstractOrderStatus $ingenicoStatus
     */
    public function resolveStatus(Mage_Sales_Model_Order $order, AbstractOrderStatus $ingenicoStatus)
    {
        $payment = $order->getPayment();
        /** @var Mage_Sales_Model_Order_Creditmemo $creditmemo */
        $creditmemo = $this->creditmemoService->getCreditmemo($payment, $ingenicoStatus->id);

        if ($creditmemo->getId()) {
            $payment->setCreditmemo($creditmemo);
            $this->applyCreditmemo($creditmemo);
            $payment->setIsRefundCancellationInProgress(true);
            $payment->cancelCreditmemo($creditmemo);
            $this->closeRefundTransaction(
                $payment,
                $creditmemo
            );
            $this->resetInvoice($creditmemo);
            $this->resetItems(
                $order,
                $creditmemo
            );
            $this->resetOrder(
                $order,
                $creditmemo
            );
        }
    }

    /**
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     */
    public function applyCreditmemo(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $creditmemo->setState(Mage_Sales_Model_Order_Creditmemo::STATE_CANCELED);
    }

    /**
     * Closes the refund transaction for the given creditmemo
     *
     * @param $payment
     * @param $creditmemo
     */
    protected function closeRefundTransaction($payment, $creditmemo)
    {
        $refundTransaction = $payment
            ->lookupTransaction(
                $creditmemo->getTransactionId(),
                Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND
            );
        if ($refundTransaction) {
            $refundTransaction->setIsClosed(true)->save();
        }
    }

    /**
     * Reset invoice amounts
     *
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     */
    protected function resetInvoice($creditmemo)
    {
        /** @var Mage_Sales_Model_Order_Invoice $invoice */
        $invoice = Mage::getModel('sales/order_invoice')->load($creditmemo->getInvoiceId());
        $invoice->setIsUsedForRefund(0)
                ->setBaseTotalRefunded($invoice->getBaseTotalRefunded() - $creditmemo->getBaseGrandTotal());
        $creditmemo->setInvoice($invoice);
    }

    /**
     * Reset ordered items amounts
     *
     * @param Mage_Sales_Model_Order $order
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     */
    protected function resetItems($order, $creditmemo)
    {
        /** @var Mage_Sales_Model_Order_Creditmemo_Item $creditmemoItem */
        foreach ($creditmemo->getAllItems() as $creditmemoItem) {
            // Working directly on the orderItem from the creditmemo
            // does not transfer changes into the order object.
            $orderItem = $order->getItemById($creditmemoItem->getOrderItem()->getId());
            $orderItem->setAmountRefunded(
                $orderItem->getAmountRefunded() - $creditmemoItem->getRowTotalInclTax()
            );
            $orderItem->setBaseAmountRefunded(
                $orderItem->getBaseAmountRefunded() - $creditmemoItem->getBaseRowTotalInclTax()
            );
            $orderItem->setQtyRefunded($orderItem->getQtyRefunded() - $creditmemoItem->getQty());
        }
    }

    /**
     * Reset order object amounts and state
     *
     * @param Mage_Sales_Model_Order $order
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     */
    protected function resetOrder($order, $creditmemo)
    {
        $this->resetOrderTotals(
            $order,
            $creditmemo
        );

        if ($order->canShip() || $order->canInvoice()) {
            $state = Mage_Sales_Model_Order::STATE_PROCESSING;
            $order->setState(
                $state,
                true,
                Mage::helper('netresearch_epayments')
                    ->__('Refund %s refused/cancelled.', $this->ingenicoOrderStatus->id)
            );
        }

        $order->addRelatedObject($creditmemo);
        $order->addRelatedObject($creditmemo->getInvoice());
    }

    /**
     * Reset order totals according to what has been set during creditmemo creation
     * @see \Mage_Sales_Model_Order_Creditmemo::refund for all totals
     *
     * @param Mage_Sales_Model_Order $order
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     */
    protected function resetOrderTotals(
        Mage_Sales_Model_Order $order,
        Mage_Sales_Model_Order_Creditmemo $creditmemo
    ) {
        foreach ($this::$totals as $orderTotal => $creditmemoTotal) {
            if (is_numeric($orderTotal)) {
                $orderTotal = $creditmemoTotal . '_refunded';
            }

            $value = $order->getData($orderTotal) - $creditmemo->getData($creditmemoTotal);
            $order->setData(
                $orderTotal,
                $value
            );
        }
    }
}
