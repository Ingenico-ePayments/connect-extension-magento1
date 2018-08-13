<?php

/**
 * Class Netresearch_Epayments_Model_Resource_Token
 */
class Netresearch_Epayments_Model_Resource_Token extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize connection and define main table and primary key
     */
    protected function _construct()
    {
        $this->_init('netresearch_epayments/token', 'entity_id');
    }
}
