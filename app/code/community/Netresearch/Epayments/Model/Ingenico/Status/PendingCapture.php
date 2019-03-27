<?php

use Ingenico\Connect\Sdk\Domain\Definitions\AbstractOrderStatus;
use Netresearch_Epayments_Model_Ingenico_Status_HandlerInterface as HandlerInterface;
use Netresearch_Epayments_Model_Order_EmailInterface as OrderEmailMananger;

/**
 * Class Netresearch_Epayments_Model_Ingenico_Status_PendingCapture
 */
class Netresearch_Epayments_Model_Ingenico_Status_PendingCapture implements HandlerInterface
{
    /**
     * @var OrderEmailMananger
     */
    protected $orderEMailManager;

    /**
     * @var Netresearch_Epayments_Model_ConfigInterface
     */
    protected $moduleConfig;

    /**
     * Netresearch_Epayments_Model_Ingenico_Status_PendingCapture constructor.
     */
    public function __construct()
    {
        $this->orderEMailManager = Mage::getModel('netresearch_epayments/order_emailManager');
        $this->moduleConfig = Mage::getModel('netresearch_epayments/config');
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param AbstractOrderStatus $ingenicoStatus
     */
    public function resolveStatus(Mage_Sales_Model_Order $order, AbstractOrderStatus $ingenicoStatus)
    {
        // Same behaviour as PendingApproval

        $payment = $order->getPayment();

        $amount = $ingenicoStatus->paymentOutput->amountOfMoney->amount;
        $amount /= 100;

        $payment->setIsTransactionClosed(false);
        if ($order->getEntityId() !== null &&
            $this->moduleConfig->getCheckoutType(
                $order->getStoreId()
            ) !== Netresearch_Epayments_Model_Config::CONFIG_INGENICO_CHECKOUT_TYPE_INLINE) {
            $payment->registerAuthorizationNotification($amount);
        }

        $this->orderEMailManager->process($order, $ingenicoStatus->status);
    }

}
