<?php

/**
 * Class Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_Order_CustomerBuilder
 */
class Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_Order_CustomerBuilder
{
    const GENDER_MALE = 1;
    const GENDER_FEMALE = 2;

    /**
     * @var Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_Order_Customer_AccountBuilder
     */
    protected $accountBuilder;

    /**
     * @var Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_Order_Customer_DeviceBuilder
     */
    protected $deviceBuilder;

    /**
     * Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_Order_CustomerBuilder constructor.
     */
    public function __construct()
    {
        $this->accountBuilder = Mage::getModel('ingenico_connect/ingenico_requestBuilder_common_order_customer_accountBuilder');
        $this->deviceBuilder = Mage::getModel('ingenico_connect/ingenico_requestBuilder_common_order_customer_deviceBuilder');
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return \Ingenico\Connect\Sdk\Domain\Payment\Definitions\Customer
     * @throws Exception
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
            $ingenicoCustomer->shippingAddress = $this->getAddressPersonal($shipping);
        }

        $ingenicoCustomer->account = $this->accountBuilder->create($order);

        $accountType = $this->getAccountType($order);
        if ($accountType !== null) {
            $ingenicoCustomer->accountType = $accountType;
        }

        $ingenicoCustomer->device = $this->deviceBuilder->create($order);

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
        $personalInformation->gender = $this->getCustomerGender($order);
        $personalInformation->dateOfBirth = $this->getDateOfBirth($order);

        return $personalInformation;
    }

    /**
     * Extracts the date of birth in the YYYYMMDD format required by the API
     *
     * @param $order
     * @return string dob in proper format
     */
    protected function getDateOfBirth($order)
    {
        $dateOfBirth = '';
        if ($order->getCustomerDob()) {
            $dateOfBirthObject = new \DateTime($order->getCustomerDob());
            $dateOfBirth = $dateOfBirthObject->format('Ymd');
        }

        return $dateOfBirth;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param Mage_Sales_Model_Order_Address $billing
     * @return \Ingenico\Connect\Sdk\Domain\Payment\Definitions\ContactDetails
     */
    protected function getContactDetails(
        Mage_Sales_Model_Order $order,
        Mage_Sales_Model_Order_Address $billing
    ) {
        $contactDetails = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\ContactDetails();
        $contactDetails->emailAddress = $order->getCustomerEmail();
        $contactDetails->emailMessageType = Ingenico_Connect_Helper_Data::EMAIL_MESSAGE_TYPE;
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
        Mage_Sales_Model_Order_Address $shipping
    ) {
        $shippingName = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\PersonalName();
        $shippingName->title = $shipping->getPrefix();
        $shippingName->firstName = $shipping->getFirstname();
        $shippingName->surname = $shipping->getLastname();

        $shippingAddress = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\AddressPersonal();
        $shippingAddress->name = $shippingName;
        /** @var array $streetArray */
        $streetArray = $shipping->getStreet();
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

    /**
     * @param Mage_Sales_Model_Order $order
     * @return string
     */
    protected function getCustomerGender($order)
    {
        switch ($order->getCustomerGender()) {
            case self::GENDER_MALE:
                return 'male';
            case self::GENDER_FEMALE:
                return 'female';
            default:
                return 'unknown';
        }
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return string
     */
    protected function getAccountType(Mage_Sales_Model_Order $order) {
        if ($order->getCustomerIsGuest()) {
            return 'none';
        }

        /** @var Mage_Sales_Model_Quote $quote */
        $quote = Mage::getModel('sales/quote')->load($order->getQuoteId());
        if (!$quote->getId()) {
            return null;
        }

        if ($quote->getCheckoutMethod(true) === 'register') {
            return 'created';
        }
        return 'existing';
    }
}
