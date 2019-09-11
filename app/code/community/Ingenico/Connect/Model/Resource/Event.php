<?php
/**
 * Ingenico_Connect
 *
 * See LICENSE.txt for license details.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to
 * newer versions in the future.
 *
 * @category  Epayments
 * @package   Ingenico_Connect

 */

/**
 * Class Ingenico_Connect_Model_Resource_Event
 */
class Ingenico_Connect_Model_Resource_Event extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
       $this->_init('ingenico_connect/event', 'id');
    }
}
