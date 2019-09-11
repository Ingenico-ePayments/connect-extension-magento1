<?php

/**
 * Class Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_MerchantBuilder
 */
class Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_MerchantBuilder
{
    /**
     * @param Mage_Sales_Model_Order $order
     * @return \Ingenico\Connect\Sdk\Domain\Payment\Definitions\Merchant
     */
    public function create(Mage_Sales_Model_Order $order)
    {
        $ingenicoMerchant = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\Merchant();

        $websiteUrl = $this->getWebsiteUrl($order);
        if ($websiteUrl !== null) {
            $ingenicoMerchant->websiteUrl = $websiteUrl;
        }

        $contactWebsiteUrl = $this->getContactWebsiteUrl($order);
        if ($contactWebsiteUrl !== null) {
            $ingenicoMerchant->contactWebsiteUrl = $contactWebsiteUrl;
        }

        return $ingenicoMerchant;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     *
     * @return string|null
     */
    protected function getWebsiteUrl(Mage_Sales_Model_Order $order)
    {
        try {
            return Mage::app()->getStore($order->getStoreId())->getBaseUrl();
        } catch (Exception $exception) {
            Mage::logException($exception);
            return null;
        }
    }

    /**
     * @param Mage_Sales_Model_Order $order
     *
     * @return string|null
     */
    protected function getContactWebsiteUrl(Mage_Sales_Model_Order $order)
    {
        if (!Mage::helper('core')->isModuleEnabled('Mage_Contacts')) {
            return null;
        }
        if (!Mage::helper('contacts')->isEnabled()) {
            return null;
        }
        try {
            return Mage::app()->getStore($order->getStoreId())->getUrl('contacts');
        } catch (Exception $exception) {
            Mage::logException($exception);
            return null;
        }
    }
}
