<?php

use Ingenico\Connect\Sdk\Domain\Definitions\CompanyInformation;
use Ingenico\Connect\Sdk\Domain\Payment\Definitions\ContactDetails;
use Ingenico\Connect\Sdk\Domain\Payment\Definitions\Customer;
use Ingenico\Connect\Sdk\Domain\Payment\Definitions\PersonalInformation;
use Ingenico\Connect\Sdk\Domain\Payment\Definitions\PersonalName;

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
     * @var Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_Order_Customer_AddressBuilder
     */
    private $addressBuilder;

    /**
     * @var Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_Order_Customer_CompanyInformationBuilder
     */
    protected $companyInformationBuilder;

    /**
     * Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_Order_CustomerBuilder constructor.
     */
    public function __construct()
    {
        $this->accountBuilder = Mage::getModel('ingenico_connect/ingenico_requestBuilder_common_order_customer_accountBuilder');
        $this->addressBuilder = Mage::getModel('ingenico_connect/ingenico_requestBuilder_common_order_customer_addressBuilder');
        $this->deviceBuilder = Mage::getModel('ingenico_connect/ingenico_requestBuilder_common_order_customer_deviceBuilder');
        $this->companyInformationBuilder = Mage::getModel('ingenico_connect/ingenico_requestBuilder_common_order_customer_companyInformationBuilder');
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return Customer
     * @throws Exception
     */
    public function create(Mage_Sales_Model_Order $order)
    {
        $ingenicoCustomer = new Customer();

        $ingenicoCustomer->personalInformation = $this->getPersonalInformation($order);
        // create dummy customer id
        $ingenicoCustomer->merchantCustomerId = $order->getCustomerId() ?: mt_rand(100000, 999999);

        $billing = $order->getBillingAddress();
        if (!empty($billing)) {

            $companyInformation = new CompanyInformation();
            $companyInformation->name = $billing->getCompany();
            $ingenicoCustomer->companyInformation = $companyInformation;

            $ingenicoCustomer->contactDetails = $this->getContactDetails($order, $billing);
        }

        $ingenicoCustomer->account = $this->accountBuilder->create($order);

        $accountType = $this->getAccountType($order);
        if ($accountType !== null) {
            $ingenicoCustomer->accountType = $accountType;
        }

        $ingenicoCustomer->device = $this->deviceBuilder->create($order);
        $ingenicoCustomer->billingAddress = $this->addressBuilder->create($order);
        $ingenicoCustomer->companyInformation = $this->companyInformationBuilder->create($order);

        return $ingenicoCustomer;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return PersonalInformation
     * @throws Exception
     */
    protected function getPersonalInformation(Mage_Sales_Model_Order $order)
    {
        $personalInformation = new PersonalInformation();

        $personalName = new PersonalName();
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
     * @throws Exception
     */
    protected function getDateOfBirth($order)
    {
        $dateOfBirth = '';
        if ($order->getCustomerDob()) {
            $dateOfBirthObject = new DateTime($order->getCustomerDob());
            $dateOfBirth = $dateOfBirthObject->format('Ymd');
        }

        return $dateOfBirth;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param Mage_Sales_Model_Order_Address $billing
     * @return ContactDetails
     */
    protected function getContactDetails(
        Mage_Sales_Model_Order $order,
        Mage_Sales_Model_Order_Address $billing
    ) {
        $contactDetails = new ContactDetails();
        $contactDetails->emailAddress = $order->getCustomerEmail();
        $contactDetails->emailMessageType = Ingenico_Connect_Helper_Data::EMAIL_MESSAGE_TYPE;
        $contactDetails->phoneNumber = $billing->getTelephone();
        $contactDetails->faxNumber = $billing->getFax();

        return $contactDetails;
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
    protected function getAccountType(Mage_Sales_Model_Order $order)
    {
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
