<?php

class Netresearch_Epayments_Model_Resource_Order_Collection extends Mage_Sales_Model_Resource_Order_Collection
{
    /**
     * Add filter pending order status
     *
     * @return Netresearch_Epayments_Model_Resource_Order_Collection
     */
    public function addPendingStatusFilter()
    {
        $this->addFieldToFilter('status', Mage_Sales_Model_Order::STATE_PENDING_PAYMENT);

        return $this;
    }

    /**
     * Add filter by cteated_at field, less or equal than $days before
     *
     * @param $days
     *
     * @return Netresearch_Epayments_Model_Resource_Order_Collection
     */
    public function addCreatedAtFilter($days)
    {
        $date = new Zend_Date();
        $date->sub($days, Zend_Date::DAY);
        $this->addFieldToFilter('created_at', array('lt' => $date->toString('YYYY-MM-dd')));

        return $this;
    }
}
