<?php

use Netresearch_Epayments_Model_Method_HostedCheckout as HostedCheckout;

/**
 * Class Netresearch_Epayments_Model_Observer
 */
class Netresearch_Epayments_Model_Observer
{
    /**
     * @var Netresearch_Epayments_Model_StatusResponseManager
     */
    protected $statusResponseManager;

    /**
     * Netresearch_Epayments_Model_Observer constructor.
     */
    public function __construct()
    {
        $this->statusResponseManager = Mage::getModel('netresearch_epayments/statusResponseManager');
    }

    /**
     * Add button for refreshing order status from API to order detail view
     *
     * @param Varien_Event_Observer $observer
     */
    public function addUpdateOrderButton(Varien_Event_Observer $observer)
    {
        $block = $observer->getBlock();
        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->load($block->getOrderId());
        $orderState = $order->getState();

        if (!empty($orderState)) {
            $paymentId = $order->getPayment()->getAdditionalInformation(HostedCheckout::PAYMENT_ID_KEY);

            $isViewBlock = $block instanceof Mage_Adminhtml_Block_Sales_Order_View;
            $isIngenicoOrder = Mage::helper('netresearch_epayments')->isIngenicoOrder($order);
            if ($isViewBlock && $isIngenicoOrder) {
                $message = Mage::helper('netresearch_epayments')->__('Are you sure you want to do this?');
                $clickUrl = $block->getUrl('*/orderProcessing/refreshOrderStatus');
                $block->addButton(
                    'refresh_payment_status',
                    array(
                        'label' => Mage::helper('netresearch_epayments')->__('Refresh Payment Status'),
                        'onclick' => empty($paymentId) ? '' : "confirmSetLocation('{$message}', '{$clickUrl}')",
                        'class' => sprintf(
                            'go%s',
                            empty($paymentId) ? ' disabled' : ''
                        )
                    )
                );
            }
        }
    }

    /**
     * Handler of sales_order_payment_cancel_invoice event
     *
     * @param Varien_Event_Observer $observer
     * @throws Exception
     */
    public function undoCapturePaymentRequest(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order_Payment $payment */
        $payment = $observer->getEvent()->getPayment();

        /** @var Netresearch_Epayments_Model_Ingenico_UndoCapturePaymentRequest $undoCaptureRequest */
        $undoCaptureRequest = Mage::getSingleton(
            'netresearch_epayments/ingenico_undoCapturePaymentRequest'
        );
        $undoCaptureRequest->process($payment->getOrder());
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return bool
     */
    protected function isValidToRefreshStatus(Mage_Sales_Model_Order $order)
    {
        return !$order->isCanceled()
                && $order->getState() !== Mage_Sales_Model_Order::STATE_COMPLETE
                && $order->getState() !== Mage_Sales_Model_Order::STATE_CLOSED;
    }

    /**
     * Hook into Magento creditmemo cancel logic
     *
     * @param Varien_Event_Observer $observer
     * @throws Mage_Core_Exception
     */
    public function cancelCreditmemo(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order_Payment $payment */
        $payment = $observer->getEvent()->getPayment();
        /** @var Netresearch_Epayments_Model_Method_HostedCheckout $paymentMethod */
        $paymentMethod = $payment->getMethodInstance();
        if (!$paymentMethod instanceof Netresearch_Epayments_Model_Method_HostedCheckout) {
            return;
        }
        /** @var Mage_Sales_Model_Order_Creditmemo $creditmemo */
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $paymentResponse = $this->statusResponseManager->get($payment, $creditmemo->getTransactionId());
        if ($paymentResponse->statusOutput->isCancellable && !$payment->getIsRefundCancellationInProgress()) {
            $paymentMethod->cancelCreditmemo($creditmemo);
        }
    }

    /**
     * Overwrite refund button on creditmemo detail view for creditmemo approval usage
     *
     * @param Varien_Event_Observer $observer
     * @event adminhtml_widget_container_html_before
     * @throws Mage_Core_Exception
     */
    public function addRefundUrlToButton(Varien_Event_Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();
        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_Creditmemo_View) {
            $methodInstance = $block->getCreditmemo()->getOrder()->getPayment()->getMethodInstance();
            $isHostedCheckout = $methodInstance instanceof Netresearch_Epayments_Model_Method_HostedCheckout;
            $isRefundable = $block->getCreditmemo()->canRefund();
            if ($isHostedCheckout && $isRefundable) {
                $refundUrl = $block->getUrl(
                    '*/refundProcessing/approveCreditMemo',
                    array('creditmemo_id' => $block->getCreditmemo()->getId())
                );
                $block->updateButton(
                    'refund',
                    'onclick',
                    'setLocation(\'' . $refundUrl . '\')'
                );
                $block->updateButton(
                    'refund',
                    'label',
                    Mage::helper('netresearch_epayments')->__('Approve Refund')
                );
            }
        }
    }

    /**
     * Remove credit memo cancel button on creditmemo detail view if the refund is not actually cancelable by API
     *
     * @param Varien_Event_Observer $observer
     * @event adminhtml_widget_container_html_before
     * @throws Mage_Core_Exception
     */
    public function removeCancelCreditmemoButton(Varien_Event_Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();
        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_Creditmemo_View) {
            $methodInstance = $block->getCreditmemo()->getOrder()->getPayment()->getMethodInstance();
            if (!$methodInstance instanceof Netresearch_Epayments_Model_Method_HostedCheckout) {
                return;
            }
            $refundResponse = $this->statusResponseManager->get(
                $methodInstance->getInfoInstance(),
                $block->getCreditmemo()->getTransactionId()
            );

            if (!$refundResponse->statusOutput->isCancellable) {
                $block->removeButton('cancel');
            }
        }
    }
}
