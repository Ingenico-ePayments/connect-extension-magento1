<?php

class Netresearch_Epayments_Model_OrderUpdate_Observer
{
    /** @var Netresearch_Epayments_Model_OrderUpdate_Processor $processor */
    private $processor;

    public function __construct()
    {
        Netresearch_Epayments_Model_Autoloader::register();
        $this->processor = Mage::getModel('netresearch_epayments/orderUpdate_processor');
    }

    /**
     * Process pending ingenico orders
     */
    public function processPendingOrder()
    {
        foreach (Mage::app()->getStores(true) as $store) {
            $this->processor->process($store->getId());
        }
    }
}
