<?php

class Ingenico_Connect_Model_Resource_IngenicoPendingOrder_Collection
    extends Mage_Sales_Model_Resource_Order_Collection
{
    /**
     * Add filter orders for real time api call update
     *
     * @return $this
     */
    public function addRealTimeUpdateOrdersFilter($scopeId)
    {
        $this->buildFilter($scopeId);
        $this->addFieldToFilter("order_update_wr_status", array('null' => true));

        return $this;
    }

    /**
     * Add filter orders for wr file processing
     *
     * @return $this
     */
    public function addWrFileUpdateOrdersFilter($scopeId)
    {
        $this->buildFilter($scopeId);
        $this->addFieldToFilter("order_update_wr_status", Ingenico_Connect_Model_OrderUpdate_Order::STATUS_WAIT);

        return $this;
    }

    /**
     * Build filter skeleton
     */
    private function buildFilter($scopeId)
    {
        $this->addFieldToFilter('status', Mage_Sales_Model_Order::STATE_PENDING_PAYMENT);
        $this->addFieldToFilter('method', 'hosted_checkout');
        $this->addFieldToFilter('store_id', $scopeId);

        $onCondition = "main_table.entity_id = sales_flat_order_payment.parent_id";
        $this->getSelect()->join('sales_flat_order_payment', $onCondition, array('method', 'additional_information'))->order("main_table.entity_id", 'ASC');
    }
}
