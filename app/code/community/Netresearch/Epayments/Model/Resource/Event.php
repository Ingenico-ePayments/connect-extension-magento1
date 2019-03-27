<?php
/**
 * Netresearch_Epayments
 *
 * See LICENSE.txt for license details.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to
 * newer versions in the future.
 *
 * @category  Epayments
 * @package   Netresearch_Epayments
 * @author    Andreas Müller <andreas.mueller@netresearch.de>
 * @license   https://opensource.org/licenses/MIT
 * @link      http://www.netresearch.de/
 */

/**
 * Class Netresearch_Epayments_Model_Resource_Event
 */
class Netresearch_Epayments_Model_Resource_Event extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
       $this->_init('netresearch_epayments/event', 'id');
    }
}
