<?php

use \Ingenico\Connect\Sdk\Domain\Definitions\AbstractOrderStatus;
use \Ingenico\Connect\Sdk\Domain\Payment\Definitions\Payment as IngenicoPayment;
use \Ingenico\Connect\Sdk\Domain\Refund\Definitions\RefundResult as IngenicoRefund;
use Netresearch_Epayments_Model_Ingenico_StatusInterface as StatusInterface;
use Netresearch_Epayments_Model_Ingenico_RefundHandlerInterface as RefundHandlerInterface;

/**
 * Class Netresearch_Epayments_Model_Ingenico_StatusFactory
 */
class Netresearch_Epayments_Model_Ingenico_StatusFactory
{
    /**
     * @var string[]
     */
    protected static $paymentStatusToClassPathMap = array(
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
     * @var string[]
     */
    protected static $refundStatusToClassPathMap = array(
        RefundHandlerInterface::REFUND_CREATED => 'netresearch_epayments/ingenico_status_refund_null',
        RefundHandlerInterface::REFUND_PENDING_APPROVAL => 'netresearch_epayments/ingenico_status_refund_pendingApproval',
        RefundHandlerInterface::REFUND_REJECTED => 'netresearch_epayments/ingenico_status_refund_cancelled',
        RefundHandlerInterface::REFUND_REFUND_REQUESTED => 'netresearch_epayments/ingenico_status_refund_refundRequested',
        RefundHandlerInterface::REFUND_CAPTURED => 'netresearch_epayments/ingenico_status_refund_refunded',
        RefundHandlerInterface::REFUND_REFUNDED => 'netresearch_epayments/ingenico_status_refund_refunded',
        RefundHandlerInterface::REFUND_CANCELLED => 'netresearch_epayments/ingenico_status_refund_cancelled'
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
