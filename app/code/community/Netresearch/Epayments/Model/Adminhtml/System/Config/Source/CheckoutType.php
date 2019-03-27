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

use Netresearch_Epayments_Model_Config as Config;

/**
 * Class Netresearch_Epayments_Model_Adminhtml_System_Config_Source_CheckoutType
 */
class Netresearch_Epayments_Model_Adminhtml_System_Config_Source_CheckoutType
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => Config::CONFIG_INGENICO_CHECKOUT_TYPE_REDIRECT,
                'label' => Mage::helper('netresearch_epayments')->__('Payment products and input fields on Hosted Checkout')
            ),
            array(
                'value' => Config::CONFIG_INGENICO_CHECKOUT_TYPE_HOSTED_CHECKOUT,
                'label' => Mage::helper('netresearch_epayments')
                    ->__('Payment products in Magento checkout, input fields on Hosted Checkout')
            ),
            array(
                'value' => Config::CONFIG_INGENICO_CHECKOUT_TYPE_INLINE,
                'label' => Mage::helper('netresearch_epayments')->__('Payment products and input fields in Magento checkout (inline)')
            ),
        );
    }
}
