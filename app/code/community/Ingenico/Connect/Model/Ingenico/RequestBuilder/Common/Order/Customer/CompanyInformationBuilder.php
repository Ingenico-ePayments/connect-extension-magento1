<?php

use Ingenico\Connect\Sdk\Domain\Definitions\CompanyInformation;

/**
 * Class Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_Order_Customer_CompanyInformationBuilder
 */
class Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_Order_Customer_CompanyInformationBuilder
{
    public function create(Mage_Sales_Model_Order $order)
    {
        $companyInformation = new CompanyInformation();
        $companyInformation->vatNumber = $order->getCustomerTaxvat();
        return $companyInformation;
    }
}
