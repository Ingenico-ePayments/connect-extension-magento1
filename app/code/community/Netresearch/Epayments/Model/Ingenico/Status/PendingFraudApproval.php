<?php

use Ingenico\Connect\Sdk\Domain\Definitions\AbstractOrderStatus;
use Netresearch_Epayments_Model_Ingenico_Status_HandlerInterface as HandlerInterface;

/**
 * Class Netresearch_Epayments_Model_Ingenico_Status_PendingFraudApproval
 */
class Netresearch_Epayments_Model_Ingenico_Status_PendingFraudApproval implements HandlerInterface
{
    /**
     * @var Netresearch_Epayments_Model_Order_FraudManager
     */
    protected $orderEMailManager;

    /**
     * Netresearch_Epayments_Model_Ingenico_Status_PendingFraudApproval constructor.
     */
    public function __construct()
    {
        $this->orderEMailManager = Mage::getModel('netresearch_epayments/order_fraudManager');
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param AbstractOrderStatus $ingenicoStatus
     */
    public function resolveStatus(Mage_Sales_Model_Order $order, AbstractOrderStatus $ingenicoStatus)
    {
        /** @var Mage_Sales_Model_Order_Payment $payment */
        $payment = $order->getPayment();
        $payment->setIsFraudDetected(true);
        $payment->setIsTransactionPending(true);
        $amount = $ingenicoStatus->paymentOutput->amountOfMoney->amount;
        $amount /= 100;
        $payment->registerAuthorizationNotification($amount);

        $this->orderEMailManager->process($order, $ingenicoStatus->status);
    }

}
