<?php

use Ingenico\Connect\Sdk\Domain\Definitions\AbstractOrderStatus;
use Ingenico_Connect_Model_Ingenico_Status_HandlerInterface as HandlerInterface;
use Ingenico_Connect_Model_Order_EmailInterface as OrderEmailMananger;

/**
 * Class Ingenico_Connect_Model_Ingenico_Status_PendingApproval
 */
class Ingenico_Connect_Model_Ingenico_Status_PendingApproval implements HandlerInterface
{
    /**
     * @var OrderEmailMananger
     */
    protected $orderEMailManager;

    /**
     * @var Ingenico_Connect_Model_ConfigInterface
     */
    protected $moduleConfig;

    /**
     * Ingenico_Connect_Model_Ingenico_Status_PendingApproval constructor.
     */
    public function __construct()
    {
        $this->orderEMailManager = Mage::getModel('ingenico_connect/order_emailManager');
        $this->moduleConfig = Mage::getModel('ingenico_connect/config');
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param AbstractOrderStatus $ingenicoStatus
     */
    public function resolveStatus(Mage_Sales_Model_Order $order, AbstractOrderStatus $ingenicoStatus)
    {
        $payment = $order->getPayment();
        $amount = $ingenicoStatus->paymentOutput->amountOfMoney->amount;
        $amount /= 100;

        $payment->setIsTransactionClosed(false);
        if ($order->getEntityId() !== null &&
            $this->moduleConfig->getCheckoutType(
                $order->getStoreId()
            ) !== Ingenico_Connect_Model_Config::CONFIG_INGENICO_CHECKOUT_TYPE_INLINE) {
            $payment->registerAuthorizationNotification($amount);
        }

        $this->orderEMailManager->process($order, $ingenicoStatus->status);
    }

}
