<?php

class Ingenico_Connect_Model_OrderUpdate_Processor
{
    /**
     * Update order statuses
     */
    public function process($scopeId)
    {
        // get target orders
        /** @var Ingenico_Connect_Model_Resource_IngenicoPendingOrder_Collection $orderCollection */
        $orderCollection = Mage::getResourceModel('ingenico_connect/ingenicoPendingOrder_collection');
        $orderCollection->addRealTimeUpdateOrdersFilter($scopeId);

        // update order statuses
        $total = $orderCollection->count();
        if ($total > 0) {
            Mage::log('', Zend_Log::INFO, 'order_update.log');
            Mage::log("selected $total orders", Zend_Log::INFO, 'order_update.log');

            /** @var Ingenico_Connect_Model_OrderUpdate_Order $order */
            $order = Mage::getModel('ingenico_connect/orderUpdate_order');
            try {
                foreach ($orderCollection as $orderPayment) {
                    $order->process($orderPayment);
                }
            } catch (Exception $e) {
                Mage::log($e->getMessage(), Zend_Log::ERR, 'order_update.log');
            }

            Mage::log("all orders were processed", Zend_Log::INFO, 'order_update.log');
        }
    }
}
