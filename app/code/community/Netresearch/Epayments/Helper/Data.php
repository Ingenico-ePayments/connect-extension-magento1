<?php

class Netresearch_Epayments_Helper_Data extends Mage_Core_Helper_Abstract
{
    const EMAIL_MESSAGE_TYPE = 'html';

    /**
     * @param float $amount
     * @return int
     */
    public function formatIngenicoAmount($amount)
    {
        return (int)number_format($amount * 100, 0, '.', '');
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return bool
     */
    public function isIngenicoOrder(Mage_Sales_Model_Order $order)
    {
        $paymentMethod = $order->getPayment()->getMethodInstance();
        return $paymentMethod instanceof Netresearch_Epayments_Model_Method_HostedCheckout;
    }

    /**
     * Check if order was marked as fraud by Ingenico
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return bool
     */
    public function isIngenicoFraudOrder(Mage_Sales_Model_Order $order)
    {
        if ($this->isIngenicoOrder($order)
            && $order->getStatus() == Mage_Sales_Model_Order::STATUS_FRAUD
        ) {
            return true;
        }

        return false;
    }

    /**
     * Get payment status definition from store config
     *
     * @param string $status
     * @return string|null
     */
    public function getPaymentStatusInfo($status)
    {
        $info = Mage::getStoreConfig('ingenico_epayments/payment_statuses/' . $status);
        if ($info) {
            return $this->escapeHtml($info);
        }
        return null;
    }
}
