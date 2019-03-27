<?php

use Netresearch_Epayments_Model_Ingenico_Status_AbstractStatusPool as AbstractStatusPool;
use Netresearch_Epayments_Model_Ingenico_RefundHandlerInterface as HandlerInterface;

/**
 * Class Netresearch_Epayments_Model_Ingenico_Status_RefundStatusPool
 */
class Netresearch_Epayments_Model_Ingenico_Status_RefundStatusPool extends AbstractStatusPool
{
    /**
     * @var string[]
     */
    protected static $statusHandlers = array(
        HandlerInterface::REFUND_CREATED => 'netresearch_epayments/ingenico_status_refund_null',
        HandlerInterface::REFUND_PENDING_APPROVAL => 'netresearch_epayments/ingenico_status_refund_pendingApproval',
        HandlerInterface::REFUND_REJECTED => 'netresearch_epayments/ingenico_status_refund_cancelled',
        HandlerInterface::REFUND_REFUND_REQUESTED => 'netresearch_epayments/ingenico_status_refund_refundRequested',
        HandlerInterface::REFUND_CAPTURED => 'netresearch_epayments/ingenico_status_refund_refunded',
        HandlerInterface::REFUND_REFUNDED => 'netresearch_epayments/ingenico_status_refund_refunded',
        HandlerInterface::REFUND_CANCELLED => 'netresearch_epayments/ingenico_status_refund_cancelled'
    );

    /**
     * @param string $ingenicoStatus
     * @return Netresearch_Epayments_Model_Ingenico_Status_HandlerInterface
     * @throws Exception
     */
    public function get($ingenicoStatus)
    {
        if (!isset(self::$statusHandlers[$ingenicoStatus])) {
            Mage::throwException(
                Mage::helper('netresearch_epayments')->__('Handler not found')
            );
        }

        /** @var Netresearch_Epayments_Model_Ingenico_Status_HandlerInterface $status */
        $status = Mage::getModel(self::$statusHandlers[$ingenicoStatus]);
        if (!$status) {
            Mage::throwException(
                Mage::helper('netresearch_epayments')->__('Handler does not exist')
            );
        }

        return $status;
    }
}
