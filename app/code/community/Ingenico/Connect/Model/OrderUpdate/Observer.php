<?php

class Ingenico_Connect_Model_OrderUpdate_Observer
{
    /** @var Ingenico_Connect_Model_OrderUpdate_Processor $processor */
    private $processor;

    public function __construct()
    {
        Ingenico_Connect_Model_Autoloader::register();
        $this->processor = Mage::getModel('ingenico_connect/orderUpdate_processor');
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
