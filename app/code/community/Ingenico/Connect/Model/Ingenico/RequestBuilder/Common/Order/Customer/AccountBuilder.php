<?php

/**
 * Class Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_Order_Customer_AccountBuilder
 */
class Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_Order_Customer_AccountBuilder
{
    /**
     * @var Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_Order_Customer_Account_AuthenticationBuilder
     */
    protected $authenticationBuilder;

    /**
     * @var Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_Order_Customer_Account_PaymentActivityBuilder
     */
    protected $paymentActivityBuilder;

    /**
     * Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_Order_Customer_AccountBuilder constructor.
     */
    public function __construct()
    {
        $this->authenticationBuilder = Mage::getModel('ingenico_connect/ingenico_requestBuilder_common_order_customer_account_authenticationBuilder');
        $this->paymentActivityBuilder = Mage::getModel('ingenico_connect/ingenico_requestBuilder_common_order_customer_account_paymentActivityBuilder');
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return \Ingenico\Connect\Sdk\Domain\Payment\Definitions\CustomerAccount
     * @throws Exception
     */
    public function create(Mage_Sales_Model_Order $order)
    {
        $ingenicoAccount = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\CustomerAccount();

        $ingenicoAccount->authentication = $this->authenticationBuilder->create($order);
        $ingenicoAccount->paymentActivity = $this->paymentActivityBuilder->create($order);

        $customer = $this->getCustomer($order);
        if ($customer !== null) {
            $changeDate = $this->getChangeDate($customer);
            if ($changeDate !== null) {
                $ingenicoAccount->changeDate = $changeDate;
            }
            $createDate = $this->getCreateDate($customer);
            if ($createDate !== null) {
                $ingenicoAccount->createDate = $createDate;
            }
            $ingenicoAccount->hadSuspiciousActivity = $this->getHadSuspiciousActivity($customer);
        }

        return $ingenicoAccount;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return Mage_Customer_Model_Customer|null
     */
    protected function getCustomer(Mage_Sales_Model_Order $order)
    {
        if ($order->getCustomerIsGuest()) {
            return null;
        }

        /** @var Mage_Customer_Model_Customer $customer */
        $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
        if (!$customer->getId()) {
            return null;
        }

        return $customer;
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     * @return string|null
     */
    protected function getChangeDate(Mage_Customer_Model_Customer $customer)
    {
        try {
            $customerUpdatedAt = new DateTime($customer->getData('updated_at'));
            $latestCustomerAddressUpdatedAt = $this->getLatestCustomerAddressUpdatedAt($customer->getId());

            return $latestCustomerAddressUpdatedAt > $customerUpdatedAt ?
                $latestCustomerAddressUpdatedAt->format('Ymd') :
                $customerUpdatedAt->format('Ymd');
        } catch (Exception $exception) {
            Mage::logException($exception);
            return null;
        }
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     * @return string|null
     */
    protected function getCreateDate(Mage_Customer_Model_Customer $customer)
    {
        try {
            $createDate = new DateTime($customer->getData('created_at'));
            return $createDate->format('Ymd');
        } catch (Exception $exception) {
            Mage::logException($exception);
            return null;
        }
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     * @return bool
     */
    protected function getHadSuspiciousActivity(Mage_Customer_Model_Customer $customer)
    {
        return $this->customerHadFraudOrders($customer->getId());
    }

    /**
     * Check if a customer had orders with FRAUD status
     *
     * @param int $customerId
     * @return bool
     */
    protected function customerHadFraudOrders($customerId)
    {
        $orderCollection = Mage::getResourceModel('sales/order_collection');
        $orderCollection->addFieldToFilter('customer_id', $customerId);
        $orderCollection->addFieldToFilter('status', Mage_Sales_Model_Order::STATUS_FRAUD);
        return $orderCollection->getSize() > 0;
    }

    /**
     * @param int $customerId
     * @return DateTime
     * @throws Exception
     */
    protected function getLatestCustomerAddressUpdatedAt($customerId)
    {
        $addressCollection = Mage::getResourceModel('customer/address_collection');
        $addressCollection->addFieldToFilter('parent_id', $customerId);
        $addressCollection->addAttributeToSort('updated_at', 'DESC');
        $addressCollection->setPageSize(1);
        return new DateTime($addressCollection->getFirstItem()->getData('updated_at'));
    }
}
