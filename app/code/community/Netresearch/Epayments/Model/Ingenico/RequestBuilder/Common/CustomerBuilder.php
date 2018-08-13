<?php

/**
 * Class Netresearch_Epayments_Model_Ingenico_RequestBuilder_Common_CustomerBuilder
 */
class Netresearch_Epayments_Model_Ingenico_RequestBuilder_Common_CustomerBuilder
{
    /**
     * @param Mage_Sales_Model_Order $order
     * @return \Ingenico\Connect\Sdk\Domain\Payment\Definitions\Customer
     */
    public function create(Mage_Sales_Model_Order $order)
    {
        $ingenicoCustomer = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\Customer();

        $ingenicoCustomer->personalInformation = $this->getPersonalInformation($order);
        // create dummy customer id
        $ingenicoCustomer->merchantCustomerId = $order->getCustomerId() ?: rand(100000, 999999);

        $billing = $order->getBillingAddress();
        if (!empty($billing)) {
            $ingenicoCustomer->vatNumber = $billing->getVatId();

            $companyInformation = new \Ingenico\Connect\Sdk\Domain\Definitions\CompanyInformation();
            $companyInformation->name = $billing->getCompany();
            $ingenicoCustomer->companyInformation = $companyInformation;

            $ingenicoCustomer->billingAddress = $this->getBillingAddress($billing);
            $ingenicoCustomer->contactDetails = $this->getContactDetails($order, $billing);
        }

        $shipping = $order->getShippingAddress();
        if (!empty($shipping)) {
            $ingenicoCustomer->shippingAddress = $this->getAddressPersonal($shipping, $billing);
        }

        return $ingenicoCustomer;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return \Ingenico\Connect\Sdk\Domain\Payment\Definitions\PersonalInformation
     */
    protected function getPersonalInformation(Mage_Sales_Model_Order $order)
    {
        $personalInformation = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\PersonalInformation();

        $personalName = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\PersonalName();
        $personalName->title = $order->getCustomerPrefix();
        $personalName->firstName = $order->getCustomerFirstname();
        $personalName->surnamePrefix = $order->getCustomerMiddlename();
        $personalName->surname = $order->getCustomerLastname();

        $personalInformation->name = $personalName;
        $personalInformation->gender = $order->getCustomerGender();
        $personalInformation->dateOfBirth = $order->getCustomerDob();

        return $personalInformation;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param Mage_Sales_Model_Order_Address $billing
     * @return \Ingenico\Connect\Sdk\Domain\Payment\Definitions\ContactDetails
     */
    protected function getContactDetails(
        Mage_Sales_Model_Order $order,
        Mage_Sales_Model_Order_Address $billing
    )
    {
        $contactDetails = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\ContactDetails();
        $contactDetails->emailAddress = $order->getCustomerEmail();
        $contactDetails->emailMessageType = Netresearch_Epayments_Helper_Data::EMAIL_MESSAGE_TYPE;
        $contactDetails->phoneNumber = $billing->getTelephone();
        $contactDetails->faxNumber = $billing->getFax();

        return $contactDetails;
    }

    /**
     * @param Mage_Sales_Model_Order_Address $billing
     * @return \Ingenico\Connect\Sdk\Domain\Definitions\Address
     */
    protected function getBillingAddress(Mage_Sales_Model_Order_Address $billing)
    {
        $billingAddress = new \Ingenico\Connect\Sdk\Domain\Definitions\Address();
        /** @var array $streetArray */
        $streetArray = $billing->getStreet();
        $billingAddress->street = array_shift($streetArray);
        if (!empty($streetArray)) {
            $billingAddress->additionalInfo = implode(', ', $streetArray);
        }
        $billingAddress->zip = $billing->getPostcode();
        $billingAddress->city = $billing->getCity();
        $billingAddress->state = $billing->getRegion();
        $billingAddress->countryCode = $billing->getCountry();

        return $billingAddress;
    }

    /**
     * @param Mage_Sales_Model_Order_Address $shipping
     * @param Mage_Sales_Model_Order_Address $billing
     * @return \Ingenico\Connect\Sdk\Domain\Payment\Definitions\AddressPersonal
     */
    protected function getAddressPersonal(
        Mage_Sales_Model_Order_Address $shipping,
        Mage_Sales_Model_Order_Address $billing
    )
    {
        $shippingName = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\PersonalName();
        $shippingName->title = $shipping->getPrefix();
        $shippingName->firstName = $shipping->getFirstname();
        $shippingName->surname = $shipping->getLastname();

        $shippingAddress = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\AddressPersonal();
        $shippingAddress->name = $shippingName;
        /** @var array $streetArray */
        $streetArray = $billing->getStreet();
        $shippingAddress->street = array_shift($streetArray);
        if (!empty($streetArray)) {
            $shippingAddress->additionalInfo = implode(', ', $streetArray);
        }
        $shippingAddress->zip = $shipping->getPostcode();
        $shippingAddress->city = $shipping->getCity();
        $shippingAddress->state = $shipping->getRegion();
        $shippingAddress->countryCode = $shipping->getCountry();

        return $shippingAddress;
    }
}
