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

/**
 * Class Netresearch_Epayments_Model_Resource_Event_Collection
 */
class Netresearch_Epayments_Model_Resource_Event_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    public function _construct()
    {
        $this->_init(
            'netresearch_epayments/event',
            'netresearch_epayments/event'
        );
    }

    /**
     * @param string $orderIncrementId
     * @return $this
     */
    public function getListByOrderIncrementId($orderIncrementId)
    {
        $this->addFieldToFilter(Netresearch_Epayments_Model_Event::ORDER_INCREMENT_ID, $orderIncrementId);

        return $this;
    }

    /**
     * @param string $eventId
     * @return $this
     */
    public function getByEventId($eventId)
    {
        $this->addFieldToFilter(Netresearch_Epayments_Model_Event::EVENT_ID, $eventId);

        return $this;
    }
}
