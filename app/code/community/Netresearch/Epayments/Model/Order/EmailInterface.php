<?php

/**
 * Interface Netresearch_Epayments_Model_Order_EmailInterface
 */
interface Netresearch_Epayments_Model_Order_EmailInterface
{
    /**
     * @param \Mage_Sales_Model_Order $order
     * @param $ingenicoPaymentStatus
     *
     * @return void
     */
    public function process(Mage_Sales_Model_Order $order, $ingenicoPaymentStatus);
}
