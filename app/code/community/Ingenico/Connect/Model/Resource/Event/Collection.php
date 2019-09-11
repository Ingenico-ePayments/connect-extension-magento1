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

/**
 * Class Ingenico_Connect_Model_Resource_Event_Collection
 */
class Ingenico_Connect_Model_Resource_Event_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    public function _construct()
    {
        $this->_init(
            'ingenico_connect/event',
            'ingenico_connect/event'
        );
    }

    /**
     * @param string $orderIncrementId
     * @return $this
     */
    public function getListByOrderIncrementId($orderIncrementId)
    {
        $this->addFieldToFilter(Ingenico_Connect_Model_Event::ORDER_INCREMENT_ID, $orderIncrementId);

        return $this;
    }

    /**
     * @param string $eventId
     * @return $this
     */
    public function getByEventId($eventId)
    {
        $this->addFieldToFilter(Ingenico_Connect_Model_Event::EVENT_ID, $eventId);

        return $this;
    }
}
