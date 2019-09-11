<?php

use Ingenico_Connect_Model_Ingenico_Status_AbstractStatusPool as AbstractStatusPool;
use Ingenico_Connect_Model_Ingenico_RefundHandlerInterface as HandlerInterface;

/**
 * Class Ingenico_Connect_Model_Ingenico_Status_RefundStatusPool
 */
class Ingenico_Connect_Model_Ingenico_Status_RefundStatusPool extends AbstractStatusPool
{
    /**
     * @var string[]
     */
    protected static $statusHandlers = array(
        HandlerInterface::REFUND_CREATED => 'ingenico_connect/ingenico_status_refund_null',
        HandlerInterface::REFUND_PENDING_APPROVAL => 'ingenico_connect/ingenico_status_refund_pendingApproval',
        HandlerInterface::REFUND_REJECTED => 'ingenico_connect/ingenico_status_refund_cancelled',
        HandlerInterface::REFUND_REFUND_REQUESTED => 'ingenico_connect/ingenico_status_refund_refundRequested',
        HandlerInterface::REFUND_CAPTURED => 'ingenico_connect/ingenico_status_refund_refunded',
        HandlerInterface::REFUND_REFUNDED => 'ingenico_connect/ingenico_status_refund_refunded',
        HandlerInterface::REFUND_CANCELLED => 'ingenico_connect/ingenico_status_refund_cancelled'
    );

    /**
     * @param string $ingenicoStatus
     * @return Ingenico_Connect_Model_Ingenico_Status_HandlerInterface
     * @throws Exception
     */
    public function get($ingenicoStatus)
    {
        if (!isset(self::$statusHandlers[$ingenicoStatus])) {
            Mage::throwException(
                Mage::helper('ingenico_connect')->__('Handler not found')
            );
        }

        /** @var Ingenico_Connect_Model_Ingenico_Status_HandlerInterface $status */
        $status = Mage::getModel(self::$statusHandlers[$ingenicoStatus]);
        if (!$status) {
            Mage::throwException(
                Mage::helper('ingenico_connect')->__('Handler does not exist')
            );
        }

        return $status;
    }
}
