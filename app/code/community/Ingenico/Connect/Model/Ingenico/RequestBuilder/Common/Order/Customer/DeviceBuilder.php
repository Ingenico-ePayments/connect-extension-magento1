<?php

/**
 * Class Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_Order_Customer_DeviceBuilder
 */
class Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_Order_Customer_DeviceBuilder
{
    /**
     * @var Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_Order_Customer_Device_BrowserDataBuilder
     */
    protected $browserDataBuilder;

    /**
     * Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_Order_Customer_DeviceBuilder constructor.
     */
    public function __construct()
    {
        $this->browserDataBuilder = Mage::getModel('ingenico_connect/ingenico_requestBuilder_common_order_customer_device_browserDataBuilder');
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return \Ingenico\Connect\Sdk\Domain\Payment\Definitions\CustomerDevice
     */
    public function create(Mage_Sales_Model_Order $order)
    {
        $ingenicoDevice = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\CustomerDevice();

        $ingenicoDevice->browserData = $this->browserDataBuilder->create($order);

        $acceptHeader = $this->getAcceptHeader();
        if ($acceptHeader !== null) {
            $ingenicoDevice->acceptHeader = $acceptHeader;
        }

        return $ingenicoDevice;
    }

    /**
     * @return string|null
     */
    protected function getAcceptHeader() {
        try {
            $acceptHeader = Mage::app()->getRequest()->getHeader('Accept');
            if (!$acceptHeader) {
                return null;
            }
            return $acceptHeader;
        } catch (Exception $exception) {
            Mage::logException($exception);
            return null;
        }

    }
}
