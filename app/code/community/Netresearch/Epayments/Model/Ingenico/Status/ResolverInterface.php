<?php

use Ingenico\Connect\Sdk\Domain\Definitions\AbstractOrderStatus;

/**
 * Interface Netresearch_Epayments_Model_Ingenico_Status_ResolverInterface
 */
interface Netresearch_Epayments_Model_Ingenico_Status_ResolverInterface
{
    const TYPE_CAPTURE = 'capture';
    const TYPE_PAYMENT = 'payment';
    const TYPE_REFUND = 'refund';

    /**
     * @param Mage_Sales_Model_Order $order
     * @param AbstractOrderStatus $ingenicoStatus
     */
    public function resolve(Mage_Sales_Model_Order $order, AbstractOrderStatus $ingenicoStatus);

    /**
     * @param string $type
     * @param string $status
     * @return Netresearch_Epayments_Model_Ingenico_Status_HandlerInterface
     */
    public function getHandlerByType($type, $status);
}
