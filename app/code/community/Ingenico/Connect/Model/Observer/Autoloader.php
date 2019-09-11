<?php

/**
 * Class Ingenico_Connect_Model_Observer_Autoloader
 */
class Ingenico_Connect_Model_Observer_Autoloader extends Varien_Event_Observer
{
    /**
     * Register autoloader for Ingenico SDK
     *
     * @event controller_front_init_before
     * @param Varien_Event_Observer $event
     */
    public function controllerFrontInitBefore($event) 
    {
        Ingenico_Connect_Model_Autoloader::register();
    }
}
