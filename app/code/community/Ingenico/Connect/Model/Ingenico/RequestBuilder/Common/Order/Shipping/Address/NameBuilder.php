<?php

use Ingenico\Connect\Sdk\Domain\Payment\Definitions\PersonalName;

/**
 * Class Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_Order_Shipping_Address_NameBuilder
 */
class Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_Order_Shipping_Address_NameBuilder
{
    public function create(Mage_Sales_Model_Order_Address $address)
    {
        $personalName = new PersonalName();
        $personalName->firstName = $address->getFirstname();
        $personalName->surname = $address->getLastname();
        $personalName->surnamePrefix = $address->getMiddlename();
        $personalName->title = $address->getPrefix();
        return $personalName;
    }
}
