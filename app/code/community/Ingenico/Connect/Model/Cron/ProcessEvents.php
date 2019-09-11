<?php
/**
 * Ingenico_Connect
 *
 * See LICENSE.txt for license details.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to
 * newer versions in the future.
 *
 * @category  Epayments
 * @package   Ingenico_Connect
 */

class Ingenico_Connect_Model_Cron_ProcessEvents
{
    /**
     * @var Ingenico_Connect_Model_Event_Processor
     */
    protected $processor;

    /**
     * Ingenico_Connect_Model_Cron_ProcessEvents constructor.
     */
    public function __construct()
    {
        $this->processor = Mage::getSingleton('ingenico_connect/event_processor');
    }

    /**
     * @throws Exception
     */
    public function execute()
    {
        $this->processor->processBatch();
    }
}
