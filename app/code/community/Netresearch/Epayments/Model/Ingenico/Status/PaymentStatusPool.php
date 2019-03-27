<?php
use Netresearch_Epayments_Model_Ingenico_Status_AbstractStatusPool as AbstractStatusPool;
use Netresearch_Epayments_Model_Ingenico_StatusInterface as StatusInterface;

/**
 * Class Netresearch_Epayments_Model_Ingenico_Status_PaymentStatusPool
 */
class Netresearch_Epayments_Model_Ingenico_Status_PaymentStatusPool extends AbstractStatusPool
{
    /**
     * @var string[]
     */
    protected static $statusHandlers = array(
        StatusInterface::REDIRECTED => 'netresearch_epayments/ingenico_status_redirected',
        StatusInterface::PENDING_PAYMENT => 'netresearch_epayments/ingenico_status_pendingPayment',
        StatusInterface::ACCOUNT_VERIFIED => 'netresearch_epayments/ingenico_status_null',
        StatusInterface::PENDING_FRAUD_APPROVAL => 'netresearch_epayments/ingenico_status_pendingFraudApproval',
        StatusInterface::AUTHORIZATION_REQUESTED => 'netresearch_epayments/ingenico_status_null',
        StatusInterface::PENDING_APPROVAL => 'netresearch_epayments/ingenico_status_pendingApproval',
        StatusInterface::PENDING_CAPTURE => 'netresearch_epayments/ingenico_status_pendingCapture',
        StatusInterface::CAPTURE_REQUESTED => 'netresearch_epayments/ingenico_status_captureRequested',
        StatusInterface::CAPTURED => 'netresearch_epayments/ingenico_status_captured',
        StatusInterface::PAID => 'netresearch_epayments/ingenico_status_paid',
        StatusInterface::REVERSED => 'netresearch_epayments/ingenico_status_null',
        StatusInterface::CHARGEBACK => 'netresearch_epayments/ingenico_status_null',
        StatusInterface::REJECTED => 'netresearch_epayments/ingenico_status_rejected',
        StatusInterface::REJECTED_CAPTURE => 'netresearch_epayments/ingenico_status_rejectedCapture',
        StatusInterface::CANCELLED => 'netresearch_epayments/ingenico_status_cancelled'
    );

    /**
     * @param string $ingenicoStatus
     * @return Netresearch_Epayments_Model_Ingenico_Status_HandlerInterface
     * @throws Mage_Core_Exception
     */
    public function get($ingenicoStatus)
    {
        if (!isset(self::$statusHandlers[$ingenicoStatus])) {
            throw new RuntimeException(__('Handler not found'));
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
