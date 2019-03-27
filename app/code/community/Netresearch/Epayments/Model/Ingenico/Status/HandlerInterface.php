<?php

use Ingenico\Connect\Sdk\Domain\Definitions\AbstractOrderStatus;

/**
 * Interface Netresearch_Epayments_Model_Ingenico_Status_HandlerInterface
 */
interface Netresearch_Epayments_Model_Ingenico_Status_HandlerInterface
{
    /**
     * @param Mage_Sales_Model_Order $order
     * @param AbstractOrderStatus $ingenicoStatus
     * @return mixed
     */
    public function resolveStatus(Mage_Sales_Model_Order $order, AbstractOrderStatus $ingenicoStatus);
}
