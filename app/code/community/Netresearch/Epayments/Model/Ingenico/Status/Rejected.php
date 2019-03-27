<?php

use Ingenico\Connect\Sdk\Domain\Definitions\AbstractOrderStatus;
use Netresearch_Epayments_Model_Ingenico_Status_HandlerInterface as HandlerInterface;

/**
 * Class Netresearch_Epayments_Model_Ingenico_Status_Rejected
 */
class Netresearch_Epayments_Model_Ingenico_Status_Rejected implements HandlerInterface
{
    /**
     * @param Mage_Sales_Model_Order $order
     * @param AbstractOrderStatus $ingenicoStatus
     */
    public function resolveStatus(Mage_Sales_Model_Order $order, AbstractOrderStatus $ingenicoStatus)
    {
        $order->registerCancellation(
            "Order was canceled with status {$ingenicoStatus->status}"
        );
    }
}
