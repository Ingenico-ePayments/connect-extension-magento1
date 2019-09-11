<?php

/**
 * Class Ingenico_Connect_Model_Resource_Token_Collection
 */
class Ingenico_Connect_Model_Resource_Token_Collection extends Mage_Sales_Model_Resource_Collection_Abstract
{
    /**
     * Filter for a customer id
     *
     * @param $customerId
     * @return $this
     */
    public function addCustomerIdFilter($customerId)
    {
        $this->addFieldToFilter('customer_id', $customerId);

        return $this;
    }
}
