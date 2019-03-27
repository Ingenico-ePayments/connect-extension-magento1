<?php

use Ingenico\Connect\Sdk\Domain\Definitions\AbstractOrderStatus;
use Netresearch_Epayments_Model_Ingenico_Status_HandlerInterface as HandlerInterface;
use Netresearch_Epayments_Model_Ingenico_StatusInterface as StatusInterface;
use Netresearch_Epayments_Model_Order_EmailInterface as OrderEmailMananger;
/**
 * Class Netresearch_Epayments_Model_Ingenico_Status_PendingPayment
 */
class Netresearch_Epayments_Model_Ingenico_Status_PendingPayment implements HandlerInterface
{
    /**
     * @var OrderEmailMananger
     */
    protected $orderEMailManager;

    /**
     * Netresearch_Epayments_Model_Ingenico_Status_PendingPayment constructor.
     */
    public function __construct()
    {
        $this->orderEMailManager = Mage::getModel('netresearch_epayments/order_emailManager');
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param AbstractOrderStatus $ingenicoStatus
     */
    public function resolveStatus(Mage_Sales_Model_Order $order, AbstractOrderStatus $ingenicoStatus)
    {
        $order->getPayment()->setIsTransactionClosed(false);
        $order->getPayment()->addTransaction(
            Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH,
            $order,
            false,
            Mage::helper('netresearch_epayments')->getPaymentStatusInfo(StatusInterface::PENDING_PAYMENT)
        );

        $this->orderEMailManager->process(
            $order,
            $ingenicoStatus->status
        );
    }
}
