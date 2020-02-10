<?php

use Ingenico\Connect\Sdk\Domain\Definitions\AbstractOrderStatus;
use Ingenico_Connect_Model_Ingenico_Status_HandlerInterface as HandlerInterface;
use Ingenico_Connect_Model_Order_EmailInterface as OrderEmailMananger;

/**
 * Class Ingenico_Connect_Model_Ingenico_Status_Redirected
 */
class Ingenico_Connect_Model_Ingenico_Status_Redirected implements HandlerInterface
{
    /**
     * @var OrderEmailMananger
     */
    protected $orderEMailManager;

    /**
     * Ingenico_Connect_Model_Ingenico_Status_Redirected constructor.
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
        $this->orderEMailManager->process($order, $ingenicoStatus->status);

        /**
         * For inline payments with redirect actions a transaction is created. If the transaction is not kept open,
         * a later online capture is impossible
         */
        $order->getPayment()->setIsTransactionClosed(false);

        /**
         * Mark the transaction as pending, otherwise the invoice will be marked as "paid"
         * and the order will be set to "processing"
         */
        $order->getPayment()->setIsTransactionPending(true);

        $order->setData(Ingenico_Connect_Model_Observer::KEY_FLAG_STATE_SHOULD_BE_PENDING, true);
    }
}
