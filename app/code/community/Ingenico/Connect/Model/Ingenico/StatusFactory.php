<?php

use \Ingenico\Connect\Sdk\Domain\Definitions\AbstractOrderStatus;
use \Ingenico\Connect\Sdk\Domain\Payment\Definitions\Payment as IngenicoPayment;
use \Ingenico\Connect\Sdk\Domain\Refund\Definitions\RefundResult as IngenicoRefund;
use Ingenico_Connect_Model_Ingenico_StatusInterface as StatusInterface;
use Ingenico_Connect_Model_Ingenico_RefundHandlerInterface as RefundHandlerInterface;

/**
 * Class Ingenico_Connect_Model_Ingenico_StatusFactory
 */
class Ingenico_Connect_Model_Ingenico_StatusFactory
{
    /**
     * @var string[]
     */
    protected static $paymentStatusToClassPathMap = array(
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
     * @var string[]
     */
    protected static $refundStatusToClassPathMap = array(
        RefundHandlerInterface::REFUND_CREATED => 'ingenico_connect/ingenico_status_refund_null',
        RefundHandlerInterface::REFUND_PENDING_APPROVAL => 'ingenico_connect/ingenico_status_refund_pendingApproval',
        RefundHandlerInterface::REFUND_REJECTED => 'ingenico_connect/ingenico_status_refund_cancelled',
        RefundHandlerInterface::REFUND_REFUND_REQUESTED => 'ingenico_connect/ingenico_status_refund_refundRequested',
        RefundHandlerInterface::REFUND_CAPTURED => 'ingenico_connect/ingenico_status_refund_refunded',
        RefundHandlerInterface::REFUND_REFUNDED => 'ingenico_connect/ingenico_status_refund_refunded',
        RefundHandlerInterface::REFUND_CANCELLED => 'ingenico_connect/ingenico_status_refund_cancelled'
    );

    /**
     * @param AbstractOrderStatus|IngenicoPayment|IngenicoRefund $ingenicoOrderStatus
     * @return StatusInterface|RefundHandlerInterface
     */
    public function create(AbstractOrderStatus $ingenicoOrderStatus)
    {
        $classPath = $this->resolveClassPath($ingenicoOrderStatus);

        /** @var StatusInterface|RefundHandlerInterface $status */
        $status = Mage::getModel($classPath, array('gcOrderStatus' => $ingenicoOrderStatus));

        return $status;
    }

    /**
     * @param AbstractOrderStatus $ingenicoOrderStatus
     * @return string
     * @throws RuntimeException
     */
    protected function resolveClassPath(AbstractOrderStatus $ingenicoOrderStatus)
    {
        $classPath = null;
        if ($ingenicoOrderStatus instanceof \Ingenico\Connect\Sdk\Domain\Payment\Definitions\Payment
            || $ingenicoOrderStatus instanceof \Ingenico\Connect\Sdk\Domain\Capture\Definitions\Capture
        ) {
            $classPath = isset(self::$paymentStatusToClassPathMap[$ingenicoOrderStatus->status])
                ? self::$paymentStatusToClassPathMap [$ingenicoOrderStatus->status]
                : null;
        } elseif ($ingenicoOrderStatus instanceof \Ingenico\Connect\Sdk\Domain\Refund\Definitions\RefundResult) {
            $classPath = isset(self::$refundStatusToClassPathMap[$ingenicoOrderStatus->status])
                ? self::$refundStatusToClassPathMap[$ingenicoOrderStatus->status]
                : null;
        }

        if (null === $classPath) {
            throw new RuntimeException('Given status is unknown.');
        }

        return $classPath;
    }
}
