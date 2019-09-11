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

use Ingenico_Connect_Model_Config as Config;

/**
 * Class Ingenico_Connect_Model_Adminhtml_System_Config_Source_CheckoutType
 */
class Ingenico_Connect_Model_Adminhtml_System_Config_Source_CheckoutType
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => Config::CONFIG_INGENICO_CHECKOUT_TYPE_REDIRECT,
                'label' => Mage::helper('ingenico_connect')->__('Payment products and input fields on Hosted Checkout')
            ),
            array(
                'value' => Config::CONFIG_INGENICO_CHECKOUT_TYPE_HOSTED_CHECKOUT,
                'label' => Mage::helper('ingenico_connect')
                    ->__('Payment products in Magento checkout, input fields on Hosted Checkout')
            ),
            array(
                'value' => Config::CONFIG_INGENICO_CHECKOUT_TYPE_INLINE,
                'label' => Mage::helper('ingenico_connect')->__('Payment products and input fields in Magento checkout (inline)')
            ),
        );
    }
}
