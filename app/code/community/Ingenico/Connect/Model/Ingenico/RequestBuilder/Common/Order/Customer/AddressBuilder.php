<?php

use Ingenico\Connect\Sdk\Domain\Payment\Definitions\AddressPersonal;
use Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_Order_AbstractAddressBuilder as AbstractAddressBuilder;

/**
 * Class Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_Order_Customer_AddressBuilder
 */
class Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_Order_Customer_AddressBuilder extends AbstractAddressBuilder
{
    public function create(Mage_Sales_Model_Order $order)
    {
        $addressPersonal = new AddressPersonal();
        $billingAddress = $order->getBillingAddress();
        $this->populateAddress($addressPersonal, $billingAddress);

        return $addressPersonal;
    }
}
