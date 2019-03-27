<?php

use Ingenico\Connect\Sdk\Domain\Definitions\AbstractOrderStatus;
use Netresearch_Epayments_Model_Ingenico_RefundHandlerInterface as RefundHandlerInterface;

/**
 * Class Netresearch_Epayments_Model_Ingenico_Status_Refund_RefundRequested
 */
class Netresearch_Epayments_Model_Ingenico_Status_Refund_RefundRequested implements RefundHandlerInterface
{
    /**
     * @var Netresearch_Epayments_Model_Order_Creditmemo_ServiceInterface
     */
    protected $creditmemoService;

    /**
     * Netresearch_Epayments_Model_Ingenico_Status_Refund_RefundRequested constructor.
     */
    public function __construct()
    {
        /** @var Netresearch_Epayments_Model_Order_Creditmemo_ServiceInterface creditmemoService */
        $this->creditmemoService = Mage::getSingleton('netresearch_epayments/order_creditmemo_service');
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
    }
}
