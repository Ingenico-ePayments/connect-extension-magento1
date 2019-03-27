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
 * @author    Paul Siedler <paul.siedler@netresearch.de>
 * @license   https://opensource.org/licenses/MIT
 * @link      http://www.netresearch.de/
 */

class Netresearch_Epayments_Model_Adminhtml_System_Config_Source_CaptureMode
{
    /**
     * @var string
     */
    protected $_direct = Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE;
    /**
     * @var string
     */
    protected $_authorize = Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE;

    /**
     * @return array[]
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => $this->_authorize,
                'label' => Mage::helper('netresearch_epayments')->__('Delayed Settlement')
            ),
            array(
                'value' => $this->_direct,
                'label' => Mage::helper('netresearch_epayments')->__('Direct Capture')
            ),
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return string[]
     */
    public function toArray()
    {
        return array(
            $this->_authorize => Mage::helper('epayments')->__('Delayed Settlement'),
            $this->_direct => Mage::helper('epayments')->__('Direct Capture'),
        );
    }
}
