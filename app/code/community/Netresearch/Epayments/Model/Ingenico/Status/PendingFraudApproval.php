<?php

use Netresearch_Epayments_Model_Ingenico_Status_AbstractStatus as AbstractStatus;

class Netresearch_Epayments_Model_Ingenico_Status_PendingFraudApproval extends AbstractStatus
{
    /**
     * Netresearch_Epayments_Model_Ingenico_Status_PendingFraudApproval constructor.
     *
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        parent::__construct($args);
        /** @var Netresearch_Epayments_Model_Order_FraudManager $orderEmailManager */
        $orderEmailManager = Mage::getModel('netresearch_epayments/order_fraudManager');
        $this->orderEMailManager = $orderEmailManager;
    }


    /**
     * {@inheritDoc}
     */
    public function _apply(Mage_Sales_Model_Order $order)
    {
        /** @var Mage_Sales_Model_Order_Payment $payment */
        $payment = $order->getPayment();
        $payment->setIsFraudDetected(true);
        $payment->setIsTransactionPending(true);
        $amount = $this->ingenicoOrderStatus->paymentOutput->amountOfMoney->amount;
        $amount /= 100;
        $payment->registerAuthorizationNotification($amount);

        $this->orderEMailManager->process($order, $this->getStatus());
    }
}
