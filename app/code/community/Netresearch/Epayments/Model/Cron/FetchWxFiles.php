<?php

use Netresearch_Epayments_Model_Cron_FetchWxFiles_ProcessorInterface as ProcessorInterface;

class Netresearch_Epayments_Model_Cron_FetchWxFiles
{
    /** @var ProcessorInterface */
    private $processor;

    /**
     * FetchWxFiles constructor
     */
    public function __construct()
    {
        Netresearch_Epayments_Model_Autoloader::register();
        $this->processor = \Mage::getSingleton('netresearch_epayments/cron_fetchWxFiles_processor');
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
