<?php
/**
 * Netresearch_Epayments
 *
 * See LICENSE.txt for license details.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to
 * newer versions in the future.
 *
 * @category  Epayments
 * @package   Netresearch_Epayments
 * @author    Andreas Müller <andreas.mueller@netresearch.de>
 * @license   https://opensource.org/licenses/MIT
 * @link      http://www.netresearch.de/
 */

class Netresearch_Epayments_Model_Cron_ProcessEvents
{
    /**
     * @var Netresearch_Epayments_Model_Event_Processor
     */
    protected $processor;

    /**
     * Netresearch_Epayments_Model_Cron_ProcessEvents constructor.
     */
    public function __construct()
    {
        $this->processor = Mage::getSingleton('netresearch_epayments/event_processor');
    }

    /**
     * @throws Exception
     */
    public function execute()
    {
        $this->processor->processBatch();
    }
}
