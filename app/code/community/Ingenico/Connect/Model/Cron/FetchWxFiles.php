<?php

use Ingenico_Connect_Model_Cron_FetchWxFiles_ProcessorInterface as ProcessorInterface;

class Ingenico_Connect_Model_Cron_FetchWxFiles
{
    /** @var ProcessorInterface */
    private $processor;

    /**
     * FetchWxFiles constructor
     */
    public function __construct()
    {
        Ingenico_Connect_Model_Autoloader::register();
        $this->processor = \Mage::getSingleton('ingenico_connect/cron_fetchWxFiles_processor');
    }

    /**
     * Load and process WX file for every website
     *
     * @param string $date
     * @return $this
     */
    public function execute()
    {
        /** @var \Mage_Core_Model_Website $website */
        foreach (\Mage::app()->getWebsites(true) as $website) {
            $storeId = $website->getDefaultGroup()->getDefaultStoreId();
            $this->processor->process($storeId);
        }

        return $this;
    }
}
