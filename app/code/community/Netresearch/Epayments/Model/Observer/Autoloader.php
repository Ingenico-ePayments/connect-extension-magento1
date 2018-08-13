<?php

/**
 * Class Netresearch_Epayments_Model_Observer_Autoloader
 */
class Netresearch_Epayments_Model_Observer_Autoloader extends Varien_Event_Observer
{
    /**
     * Register autoloader for Ingenico SDK
     *
     * @event controller_front_init_before
     * @param Varien_Event_Observer $event
     */
    public function controllerFrontInitBefore($event) {
        Netresearch_Epayments_Model_Autoloader::register();
    }
}
