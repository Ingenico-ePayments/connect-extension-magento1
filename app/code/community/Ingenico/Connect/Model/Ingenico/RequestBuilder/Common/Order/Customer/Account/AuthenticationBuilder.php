<?php

/**
 * Class Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_Order_Customer_Account_AuthenticationBuilder
 */
class Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_Order_Customer_Account_AuthenticationBuilder
{
    const GUEST = 'guest';
    const MERCHANT_CREDENTIALS = 'merchant-credentials';


    /**
     * @param Mage_Sales_Model_Order $order
     *
     * @return \Ingenico\Connect\Sdk\Domain\Payment\Definitions\CustomerAccountAuthentication
     */
    public function create(Mage_Sales_Model_Order $order)
    {
        $ingenicoAccountAuthentication = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\CustomerAccountAuthentication();

        $ingenicoAccountAuthentication->method = $this->getMethod($order);

        $lastLoginAt = $this->getUtcTimestamp($order);
        if ($lastLoginAt !== null) {
            $ingenicoAccountAuthentication->utcTimestamp = $lastLoginAt;
        }

        return $ingenicoAccountAuthentication;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return string
     */
    protected function getMethod(Mage_Sales_Model_Order $order) {
        return $order->getCustomerIsGuest() ? self::GUEST : self::MERCHANT_CREDENTIALS;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return string|null
     */
    protected function getUtcTimestamp(Mage_Sales_Model_Order $order) {
        if ($order->getCustomerIsGuest()) {
            return null;
        }

        try {
            return $this->getLastLoginAt($order->getCustomerId());
        } catch (Exception $exception) {
            Mage::logException($exception);
            return null;
        }
    }

    /**
     * @param int $customerId
     * @return string|null
     * @throws Exception
     */
    protected function getLastLoginAt($customerId)
    {
        if (!Mage::helper('log')->isLogEnabled()) {
            return null;
        }

        /** @var Mage_Log_Model_Customer $logCustomer */
        $customerLog = Mage::getModel('log/customer')->loadByCustomer($customerId);

        $lastLoginAt = $customerLog->getLoginAt();
        if (!$lastLoginAt) {
            return null;
        }

        $lastLoginAtDate = new DateTime($lastLoginAt);

        return $lastLoginAtDate->format('YmdHi');
    }
}
