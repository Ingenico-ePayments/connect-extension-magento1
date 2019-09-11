<?php

/**
 * Class Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_Order_Customer_Account_PaymentActivityBuilder
 */
class Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_Order_Customer_Account_PaymentActivityBuilder
{
    /**
     * @param Mage_Sales_Model_Order $order
     * @return \Ingenico\Connect\Sdk\Domain\Payment\Definitions\CustomerPaymentActivity
     * @throws Exception
     */
    public function create(Mage_Sales_Model_Order $order)
    {
        $ingenicoAccountPaymentActivity = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\CustomerPaymentActivity();

        $numberOfPaymentAttemptsLast24Hours = $this->getNumberOfPaymentAttemptsLast24Hours($order);
        if ($numberOfPaymentAttemptsLast24Hours !== null) {
            $ingenicoAccountPaymentActivity->numberOfPaymentAttemptsLast24Hours = $numberOfPaymentAttemptsLast24Hours;
        }

        $numberOfPaymentAttemptsLastYear = $this->getNumberOfPaymentAttemptsLastYear($order);
        if ($numberOfPaymentAttemptsLastYear !== null) {
            $ingenicoAccountPaymentActivity->numberOfPaymentAttemptsLastYear = $numberOfPaymentAttemptsLastYear;
        }

        $numberOfCompletedPurchasesLast6Months = $this->getNumberOfCompletedPurchasesLast6Months($order);
        if ($numberOfCompletedPurchasesLast6Months !== null) {
            $ingenicoAccountPaymentActivity->numberOfPurchasesLast6Months = $numberOfCompletedPurchasesLast6Months;
        }

        return $ingenicoAccountPaymentActivity;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return int|null
     */
    protected function getNumberOfPaymentAttemptsLast24Hours(Mage_Sales_Model_Order $order) {
        try {
            return $this->getNumberOfPaymentAttemptsSince($order, new DateTime('-24 hours'));
        } catch (Exception $exception) {
            Mage::logException($exception);
            return null;
        }
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return int|null
     */
    protected function getNumberOfPaymentAttemptsLastYear(Mage_Sales_Model_Order $order) {
        try {
            return $this->getNumberOfPaymentAttemptsSince($order, new DateTime('-1 year'));
        } catch (Exception $exception) {
            Mage::logException($exception);
            return null;
        }
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param DateTime $fromDate
     * @return int|null
     */
    protected function getNumberOfPaymentAttemptsSince(Mage_Sales_Model_Order $order, DateTime $fromDate)
    {
        if ($order->getCustomerIsGuest()) {
            return null;
        }
        if (!$order->getCustomerId()) {
            return null;
        }
        /** @var Mage_Sales_Model_Resource_Order_Payment_Collection $paymentCollection */
        $paymentCollection = Mage::getResourceModel('sales/order_payment_collection');
        $paymentCollection->join(
            'sales/order',
            '`sales/order`.entity_id = main_table.parent_id',
            []
        );
        $paymentCollection->addFieldToFilter('sales/order.customer_id', $order->getCustomerId());
        $paymentCollection->addFieldToFilter('sales/order.created_at', ['gteq' => $fromDate->format('Y-m-d H:i:s')]);
        $paymentCollection->addFieldToFilter('sales/order.entity_id', ['neq' => $order->getId()]);
        return $paymentCollection->getSize();
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return int|null
     */
    protected function getNumberOfCompletedPurchasesLast6Months(Mage_Sales_Model_Order $order) {
        if ($order->getCustomerIsGuest()) {
            return null;
        }

        $customerId = $order->getCustomerId();

        if (!$customerId) {
            return null;
        }

        try {
            return $this->countNumberOfCompletedPurchasesSince($customerId, new DateTime('-6 months'));
        } catch (Exception $exception) {
            Mage::logException($exception);
            return null;
        }
    }

    /**
     * @param $customerId
     * @return int
     */
    protected function countNumberOfCompletedPurchasesSince($customerId, DateTime $fromDate)
    {
        $orderCollection = Mage::getResourceModel('sales/order_collection');
        $orderCollection->addFieldToFilter('customer_id', $customerId);
        $orderCollection->addFieldToFilter('total_due', ['lt' => '0.01']);
        $orderCollection->addFieldToFilter('created_at', ['gteq' => $fromDate->format('Y-m-d H:i:s')]);
        return $orderCollection->getSize();
    }
}
