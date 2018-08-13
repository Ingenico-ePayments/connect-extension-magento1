<?php

/**
 * Class Netresearch_Epayments_Model_Resource_Token_Collection
 */
class Netresearch_Epayments_Model_Resource_Token_Collection extends Mage_Sales_Model_Resource_Collection_Abstract
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
