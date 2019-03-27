<?php

class Netresearch_Epayments_Model_OrderUpdate_HistoryManager
{
    const TYPE_API = 'api';
    const TYPE_WR  = 'wr';

    /**
     * Add attempt data to api history
     *
     * @param Mage_Sales_Model_Order $order
     * @param array $data
     * @param string $historyType
     */
    public function addHistory(
        Mage_Sales_Model_Order $order,
        array $data,
        $historyType
    ) { 
        /** @var string $dbHistory */
        $dbHistory = $this->getHistory($order, $historyType);

        // build data
        $columnData = @unserialize($dbHistory);
        if (is_array($columnData)) {
            $history = $columnData;
        }

        $history[] = $data;

        // save data
        $this->setHistory($order, $historyType, $history);
    }

    /**
     * Get history from db
     *
     * @param Mage_Sales_Model_Order $order
     * @param $type
     * @return bool|string
     */
    private function getHistory(
        Mage_Sales_Model_Order $order,
        $type
    ) { 
        $data = false;
        switch ($type) {
            case self::TYPE_API:
                $data = $order->getOrderUpdateApiHistory();
                break;
            case self::TYPE_WR:
                $data = $order->getOrderUpdateWrHistory();
                break;
        }

        return $data;
    }

    /**
     * Set history db value
     *
     * @param Mage_Sales_Model_Order $order
     * @param $type
     * @param array $data
     */
    private function setHistory(
        Mage_Sales_Model_Order $order,
        $type,
        array $data
    ) { 
        $data = serialize($data);
        switch ($type) {
            case self::TYPE_API:
                $order->setOrderUpdateApiHistory($data);
                break;
            case self::TYPE_WR:
                $order->setOrderUpdateWrHistory($data);
                break;
        }
    }
}
