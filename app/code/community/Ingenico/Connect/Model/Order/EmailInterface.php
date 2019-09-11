<?php

/**
 * Interface Ingenico_Connect_Model_Order_EmailInterface
 */
interface Ingenico_Connect_Model_Order_EmailInterface
{
    /**
     * @param \Mage_Sales_Model_Order $order
     * @param $ingenicoPaymentStatus
     *
     * @return void
     */
    public function process(Mage_Sales_Model_Order $order, $ingenicoPaymentStatus);
}
