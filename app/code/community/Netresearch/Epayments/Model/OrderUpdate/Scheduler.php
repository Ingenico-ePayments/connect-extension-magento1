<?php

class Netresearch_Epayments_Model_OrderUpdate_Scheduler
{
    /** @var array */
    private $intervalSetMinute = array(
        30,
        105,
        120,
    );

    /**
     * Decide if it's time to pull payment from ingenico
     *
     * @param Mage_Sales_Model_Order $order
     * @return bool
     */
    public function timeForAttempt(Mage_Sales_Model_Order $order)
    {
        /** @var int $intervalLastAttempt */
        $intervalLastAttempt = $this->getInterval($order, $order->getPullPaymentLastAttemptTime());

        /** @var int $intervalCurrent */
        $intervalCurrent = $this->getInterval($order, time(), false);

        return $intervalCurrent > $intervalLastAttempt;
    }

    /**
     * Decide if it's time for WR
     *
     * @param Mage_Sales_Model_Order $order
     * @return bool
     */
    public function timeForWr(Mage_Sales_Model_Order $order)
    {
        return $this->getInterval($order, time()) == count($this->intervalSetMinute);
    }

    /**
     * Detect interval by timestamp
     *
     * @param Mage_Sales_Model_Order $order
     * @param $timestamp
     * @param bool $skipLogging
     * @return int
     */
    private function getInterval(
        Mage_Sales_Model_Order $order,
        $timestamp,
        $skipLogging = true
    ) {
        // build timestamp from date created
        $orderDateCreatedTimestamp = Varien_Date::toTimestamp($order->getCreatedAt());

        if (!$skipLogging) {
            Mage::log(
                "minutes passed from creation "
                . floor((time() - $orderDateCreatedTimestamp) / 60)
                . " (interval " . implode(", ", $this->intervalSetMinute) . ")",
                Zend_Log::INFO,
                'order_update.log'
            );
        }

        // detect interval
        $interval = 0;
        foreach ($this->intervalSetMinute as $point) {
            $currentPosition = floor(($timestamp - $orderDateCreatedTimestamp) / 60);
            if ($currentPosition >= $point) {
                $interval++;
            }
        }

        if (!$skipLogging) {
            Mage::log("interval is $interval", Zend_Log::INFO, 'order_update.log');
        }

        return $interval;
    }
}
