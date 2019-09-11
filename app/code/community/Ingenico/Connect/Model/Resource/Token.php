<?php

/**
 * Class Ingenico_Connect_Model_Resource_Token
 */
class Ingenico_Connect_Model_Resource_Token extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize connection and define main table and primary key
     */
    protected function _construct()
    {
        $this->_init('ingenico_connect/token', 'entity_id');
    }
}
