<?php

use Ingenico\Connect\Sdk\Domain\Definitions\AbstractOrderStatus;
use Ingenico_Connect_Model_Ingenico_RefundHandlerInterface as RefundHandlerInterface;

/**
 * Class Ingenico_Connect_Model_Ingenico_Status_Refund_PendingApproval
 */
class Ingenico_Connect_Model_Ingenico_Status_Refund_PendingApproval implements RefundHandlerInterface
{
    /**
     * @var Ingenico_Connect_Model_Order_Creditmemo_ServiceInterface
     */
    protected $creditmemoService;

    /**
     * Ingenico_Connect_Model_Ingenico_Status_Refund_PendingApproval constructor.
     */
    public function __construct()
    {
        $this->creditmemoService = Mage::getSingleton('ingenico_connect/order_creditmemo_service');
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param AbstractOrderStatus $ingenicoStatus
     */
    public function resolveStatus(Mage_Sales_Model_Order $order, AbstractOrderStatus $ingenicoStatus)
    {
        $payment = $order->getPayment();
        /** @var Mage_Sales_Model_Order_Creditmemo $creditmemo */
        $creditmemo = $this->creditmemoService->getCreditmemo($payment, $ingenicoStatus->id);
        if ($creditmemo->getId()) {
            $payment->setCreditmemo($creditmemo);
            $this->applyCreditmemo($creditmemo);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function applyCreditmemo(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $creditmemo->setState(Mage_Sales_Model_Order_Creditmemo::STATE_OPEN);
        $payment = $creditmemo->getOrder()->getPayment();
        $transaction = $payment->getTransaction($creditmemo->getTransactionId());
        if ($transaction !== false && $transaction->getId()) {
            $transaction->setIsClosed(false);
        }
    }
}
