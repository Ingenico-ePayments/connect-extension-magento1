<?php

/**
 * Class Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_Order_Customer_Device_BrowserDataBuilder
 */
class Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_Order_Customer_Device_BrowserDataBuilder
{
    /**
     * @param Mage_Sales_Model_Order $order
     * @return \Ingenico\Connect\Sdk\Domain\Payment\Definitions\BrowserData
     */
    public function create(Mage_Sales_Model_Order $order)
    {
        $ingenicoBrowserData = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\BrowserData();

        $ingenicoBrowserData->javaScriptEnabled = $this->getJavaScriptEnabled();

        return $ingenicoBrowserData;
    }

    /**
     * @return bool
     */
    protected function getJavaScriptEnabled()
    {
        return true;
    }
}
