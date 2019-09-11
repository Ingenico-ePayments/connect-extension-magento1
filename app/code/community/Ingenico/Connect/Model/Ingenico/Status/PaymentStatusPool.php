<?php
use Ingenico_Connect_Model_Ingenico_Status_AbstractStatusPool as AbstractStatusPool;
use Ingenico_Connect_Model_Ingenico_StatusInterface as StatusInterface;

/**
 * Class Ingenico_Connect_Model_Ingenico_Status_PaymentStatusPool
 */
class Ingenico_Connect_Model_Ingenico_Status_PaymentStatusPool extends AbstractStatusPool
{
    /**
     * @var string[]
     */
    protected static $statusHandlers = array(
        StatusInterface::REDIRECTED => 'ingenico_connect/ingenico_status_redirected',
        StatusInterface::PENDING_PAYMENT => 'ingenico_connect/ingenico_status_pendingPayment',
        StatusInterface::ACCOUNT_VERIFIED => 'ingenico_connect/ingenico_status_null',
        StatusInterface::PENDING_FRAUD_APPROVAL => 'ingenico_connect/ingenico_status_pendingFraudApproval',
        StatusInterface::AUTHORIZATION_REQUESTED => 'ingenico_connect/ingenico_status_null',
        StatusInterface::PENDING_APPROVAL => 'ingenico_connect/ingenico_status_pendingApproval',
        StatusInterface::PENDING_CAPTURE => 'ingenico_connect/ingenico_status_pendingCapture',
        StatusInterface::CAPTURE_REQUESTED => 'ingenico_connect/ingenico_status_captureRequested',
        StatusInterface::CAPTURED => 'ingenico_connect/ingenico_status_captured',
        StatusInterface::PAID => 'ingenico_connect/ingenico_status_paid',
        StatusInterface::REVERSED => 'ingenico_connect/ingenico_status_null',
        StatusInterface::CHARGEBACK => 'ingenico_connect/ingenico_status_null',
        StatusInterface::REJECTED => 'ingenico_connect/ingenico_status_rejected',
        StatusInterface::REJECTED_CAPTURE => 'ingenico_connect/ingenico_status_rejectedCapture',
        StatusInterface::CANCELLED => 'ingenico_connect/ingenico_status_cancelled'
    );

    /**
     * @param string $ingenicoStatus
     * @return Ingenico_Connect_Model_Ingenico_Status_HandlerInterface
     * @throws Mage_Core_Exception
     */
    public function get($ingenicoStatus)
    {
        if (!isset(self::$statusHandlers[$ingenicoStatus])) {
            throw new RuntimeException(__('Handler not found'));
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
