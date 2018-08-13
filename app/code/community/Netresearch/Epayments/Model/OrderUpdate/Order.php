<?php
use Netresearch_Epayments_Model_Method_HostedCheckout as HostedCheckout;

class Netresearch_Epayments_Model_OrderUpdate_Order
{
    const STATUS_WAIT     = 'wait';
    const STATUS_FINISHED = 'finished';

    /** @var Mage_Sales_Model_Order $order */
    private $order;

    /** @var Netresearch_Epayments_Model_OrderUpdate_Scheduler $scheduler */
    private $scheduler;

    /** @var int */
    private $scopeId;

    /** @var Netresearch_Epayments_Model_Ingenico_RetrievePayment  */
    private $retrievePaymentModel;

    public function __construct()
    {
        $this->order = Mage::getModel('sales/order');
        $this->scheduler = Mage::getModel('netresearch_epayments/orderUpdate_scheduler');
        $this->retrievePaymentModel = Mage::getModel('netresearch_epayments/ingenico_retrievePayment');
    }

    /**
     * Update order status
     *
     * @param Mage_Sales_Model_Order $order
     */
    public function process(Mage_Sales_Model_Order $order)
    {
        $orderId = $order->getEntityId();
        $this->scopeId = $order->getStoreId();
        $paymentId = @unserialize($order->getAdditionalInformation())[HostedCheckout::PAYMENT_ID_KEY];
        Mage::log("--- orderId $orderId, paymentId $paymentId", Zend_Log::INFO, 'order_update.log');

        // skip order if it's not time to pull
        if (!$this->scheduler->timeForAttempt($order)) {
            Mage::log("skipped, not a time yet", Zend_Log::INFO, 'order_update.log');

            return;
        }
        if (!$paymentId) {
            Mage::log("skipped, no paymentId", Zend_Log::INFO, 'order_update.log');

            return;
        }

        try {
            // call ingenico
            $orderUpdated = $this->retrievePaymentModel->process($order);
            if ($orderUpdated) {
                $message = "Ingenico status updated";
            } else {
                $message = "No update available";
            }
            Mage::log($message, Zend_Log::INFO, 'order_update.log');
        } catch (Exception $e) {
            Mage::log($e->getMessage(), Zend_Log::ERR, 'order_update.log');
        }

        // add last attempt time
        $order->setOrderUpdateApiLastAttemptTime(time());


        // check if time to WR
        if ($this->scheduler->timeForWr($order)) {
            $order->setOrderUpdateWrStatus(
                self::STATUS_WAIT
            );
        }

        $order->save();
    }
}
