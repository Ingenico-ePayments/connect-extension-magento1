<?php

/**
 * Class Netresearch_Epayments_Model_Token
 *
 * @method Netresearch_Epayments_Model_Token setTokenString($token)
 * @method string getTokenString
 * @method Netresearch_Epayments_Model_Token setCustomerId($cid)
 * @method int getCustomerId
 */
class Netresearch_Epayments_Model_Token extends Mage_Core_Model_Abstract
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('netresearch_epayments/token');
    }

    /**
     * @return $this
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
        if ($this->isObjectNew()) {
            $this->setData('created_at', Varien_Date::now());
        }

        return $this;
    }
}
