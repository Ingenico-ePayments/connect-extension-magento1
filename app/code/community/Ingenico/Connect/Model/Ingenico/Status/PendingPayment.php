<?php

use Ingenico\Connect\Sdk\Domain\Definitions\AbstractOrderStatus;
use Ingenico_Connect_Model_Ingenico_Status_HandlerInterface as HandlerInterface;
use Ingenico_Connect_Model_Ingenico_StatusInterface as StatusInterface;
use Ingenico_Connect_Model_Order_EmailInterface as OrderEmailMananger;
/**
 * Class Ingenico_Connect_Model_Ingenico_Status_PendingPayment
 */
class Ingenico_Connect_Model_Ingenico_Status_PendingPayment implements HandlerInterface
{
    /**
     * @var OrderEmailMananger
     */
    protected $orderEMailManager;

    /**
     * Ingenico_Connect_Model_Ingenico_Status_PendingPayment constructor.
     */
    public function __construct()
    {
        $this->orderEMailManager = Mage::getModel('ingenico_connect/order_emailManager');
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
            Mage::helper('ingenico_connect')->getPaymentStatusInfo(StatusInterface::PENDING_PAYMENT)
        );

        $this->orderEMailManager->process(
            $order,
            $ingenicoStatus->status
        );
    }
}
