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
 * Class Netresearch_Epayments_Model_Event
 */
class Netresearch_Epayments_Model_Event extends Mage_Core_Model_Abstract
{
    const ID = 'id';
    const EVENT_ID = 'event_id';
    const ORDER_INCREMENT_ID = 'order_increment_id';
    const CREATED_TIMESTAMP = 'created_at';
    const PAYLOAD = 'payload';
    const STATUS = 'status';

    const STATUS_NEW = 0;
    const STATUS_PROCESSING = 1;
    const STATUS_SUCCESS = 2;
    const STATUS_FAILED = 3;

    protected function _construct()
    {
        $this->_init('netresearch_epayments/event');
    }

    /**
     * @return string
     */
    public function getEventId()
    {
        return (string) $this->getData(self::EVENT_ID);
    }

    /**
     * @param string $eventId
     */
    public function setEventId($eventId)
    {
        $this->setData(self::EVENT_ID, $eventId);
    }

    /**
     * @return string
     */
    public function getOrderIncrementId()
    {
        return (string) $this->getData(self::ORDER_INCREMENT_ID);
    }

    /**
     * @param string $orderIncrementId
     */
    public function setOrderIncrementId($orderIncrementId)
    {
        $this->setData(self::ORDER_INCREMENT_ID, $orderIncrementId);
    }

    /**
     * @return string|null
     */
    public function getPayload()
    {
        return $this->getData(self::PAYLOAD);
    }

    /**
     * @param string $payload
     */
    public function setPayload($payload)
    {
        $this->setData(self::PAYLOAD, $payload);
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return (int) $this->getData(self::STATUS);
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->setData(self::STATUS, $status);
    }

    /**
     * @return string
     */
    public function getCreatedTimeStamp()
    {
        return $this->getData(self::CREATED_TIMESTAMP);
    }

    /**
     * @param $timestamp
     */
    public function setCreatedTimeStamp($timestamp)
    {
        $this->setData(self::CREATED_TIMESTAMP, $timestamp);
    }
}
