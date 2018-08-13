<?php

use Netresearch_Epayments_Model_Ingenico_StatusInterface as StatusInterface;
use Netresearch_Epayments_Model_Ingenico_GlobalCollect_ProductMapper as ProductMapper;

class Netresearch_Epayments_Model_Ingenico_GlobalCollect_StatusMapper
{
    private static $statusMap = array(
        ProductMapper::METHOD_CARDS => array(
            StatusInterface::REDIRECTED => array(20, 25, 30, 50, 650),
            StatusInterface::ACCOUNT_VERIFIED => array(300, 350),
            StatusInterface::PENDING_COMPLETION => array(60, 200),
            StatusInterface::PENDING_FRAUD_APPROVAL => array(525),
            StatusInterface::PENDING_APPROVAL => array(600),
            StatusInterface::PENDING_CAPTURE => array(680),
            StatusInterface::AUTHORIZATION_REQUESTED => array(625),
            StatusInterface::CAPTURE_REQUESTED => array(800, 900, 925, 975),
            StatusInterface::REFUND_REQUESTED => array(800, 900, 925, 975),
            StatusInterface::PAID => array(1000, 1030, 1050),
            StatusInterface::CHARGEBACK => array(1500),
            StatusInterface::REFUNDED => array(1800),
            StatusInterface::REJECTED => array(100, 130, 150, 160, 170, 172, 175, 180, 220, 230, 280, 310, 320, 330),
            StatusInterface::REJECTED_CAPTURE => array(190, 1100, 1120, 1150, 1850),
            StatusInterface::CANCELLED => array(400, 99999),
        ),
        ProductMapper::METHOD_DIRECT_DEBITS => array(
            StatusInterface::REDIRECTED => array(20, 25, 30),
            StatusInterface::PENDING_FRAUD_APPROVAL => array(525),
            StatusInterface::PENDING_APPROVAL => array(600),
            StatusInterface::REFUND_REQUESTED => array(800, 900, 975, 1020),
            StatusInterface::PAID => array(1000, 1010, 1050),
            StatusInterface::REVERSED => array(1510, 1520),
            StatusInterface::REFUNDED => array(1800),
            StatusInterface::REJECTED => array(100, 160),
            StatusInterface::REJECTED_CAPTURE => array(1100, 1210),
            StatusInterface::CANCELLED => array(400, 99999),
        ),
        ProductMapper::METHOD_EWALLET => array(
            StatusInterface::REDIRECTED => array(20, 25, 30, 50, 150, 650),
            StatusInterface::PENDING_PAYMENT => array(1020),
            StatusInterface::ACCOUNT_VERIFIED => array(300),
            StatusInterface::PENDING_COMPLETION => array(70),
            StatusInterface::PENDING_FRAUD_APPROVAL => array(525),
            StatusInterface::PENDING_APPROVAL => array(600),
            StatusInterface::PENDING_CAPTURE => array(680),
            StatusInterface::CAPTURED => array(800, 900, 975),
            StatusInterface::PAID => array(1000, 1050),
            StatusInterface::REVERSED => array(1520),
            StatusInterface::REFUNDED => array(1800),
            StatusInterface::REJECTED => array(100, 120, 125, 130, 160),
            StatusInterface::REJECTED_CAPTURE => array(1100),
            StatusInterface::CANCELLED => array(400, 99999),
        ),
        ProductMapper::METHOD_REALTIME_BT_EINVOICE => array(
            StatusInterface::REDIRECTED => array(20, 25, 30, 50, 150, 650),
            StatusInterface::PENDING_PAYMENT => array(1020),
            StatusInterface::PENDING_APPROVAL => array(600),
            StatusInterface::CAPTURED => array(800, 900, 975),
            StatusInterface::PAID => array(1000, 1050),
            StatusInterface::REVERSED => array(1520),
            StatusInterface::REFUNDED => array(1800),
            StatusInterface::REJECTED => array(100, 120, 125, 130, 140),
            StatusInterface::REJECTED_CAPTURE => array(1100),
            StatusInterface::CANCELLED => array(400, 99999),
        ),
        ProductMapper::METHOD_PREPAID => array(
            StatusInterface::REDIRECTED => array(20, 25, 30, 50, 150, 650),
            StatusInterface::PENDING_PAYMENT => array(1020),
            StatusInterface::CAPTURED => array(800, 900, 975),
            StatusInterface::PAID => array(1000, 1050),
            StatusInterface::REVERSED => array(1520),
            StatusInterface::REFUNDED => array(1800),
            StatusInterface::REJECTED => array(100, 130),
            StatusInterface::REJECTED_CAPTURE => array(1100, 1120),
            StatusInterface::CANCELLED => array(400, 99999),
        ),
        ProductMapper::METHOD_MOBILE => array(
            StatusInterface::REDIRECTED => array(20, 25, 30, 50, 150, 650),
            StatusInterface::PENDING_PAYMENT => array(1020),
            StatusInterface::CAPTURED => array(800, 900),
            StatusInterface::PAID => array(1000, 1050),
            StatusInterface::REVERSED => array(1520),
            StatusInterface::REFUNDED => array(1800),
            StatusInterface::REJECTED => array(100, 125),
            StatusInterface::REJECTED_CAPTURE => array(1100),
            StatusInterface::CANCELLED => array(400, 99999),
        ),
        ProductMapper::METHOD_CASH => array(
            StatusInterface::REDIRECTED => array(20, 25, 30, 50),
            StatusInterface::PENDING_PAYMENT => array(55, 65, 1020),
            StatusInterface::CAPTURED => array(800, 900),
            StatusInterface::PAID => array(1000, 1050),
            StatusInterface::REVERSED => array(1520),
            StatusInterface::REFUNDED => array(1800),
            StatusInterface::REJECTED => array(100),
            StatusInterface::REJECTED_CAPTURE => array(1100),
            StatusInterface::CANCELLED => array(400, 99999),
        ),
        ProductMapper::METHOD_BANKTRANSFER => array(
            StatusInterface::REDIRECTED => array(20, 25, 30),
            StatusInterface::PENDING_PAYMENT => array(800, 900, 1020),
            StatusInterface::PENDING_APPROVAL => array(600),
            StatusInterface::PAID => array(1000, 1050),
            StatusInterface::REVERSED => array(1520),
            StatusInterface::REFUNDED => array(1800),
            StatusInterface::REJECTED => array(100),
            StatusInterface::REJECTED_CAPTURE => array(1100),
            StatusInterface::CANCELLED => array(400, 99999),
        ),
        ProductMapper::METHOD_CHECKS_INVOICE => array(
            StatusInterface::PENDING_PAYMENT => array(800, 900, 950, 1020),
            StatusInterface::PENDING_CAPTURE => array(680),
            StatusInterface::CAPTURED => array(1000, 1050),
            StatusInterface::REVERSED => array(1250, 1520),
            StatusInterface::REFUNDED => array(1800),
            StatusInterface::REJECTED => array(100),
            StatusInterface::REJECTED_CAPTURE => array(1100),
            StatusInterface::CANCELLED => array(400, 99999),
        ),
        ProductMapper::METHOD_PAYOUTS => array(
            StatusInterface::PENDING_APPROVAL => array(600),
            StatusInterface::PAYOUT_REQUESTED => array(800, 900, 975),
            StatusInterface::ACCOUNT_CREDITED => array(2000, 2030),
            StatusInterface::REFUNDED => array(1800),
            StatusInterface::REJECTED => array(100),
            StatusInterface::REJECTED_CREDIT => array(2100, 2110, 2120, 2130),
            StatusInterface::CANCELLED => array(400, 99999),
        ),
        ProductMapper::METHOD_BANK_REFUNDS => array(
            StatusInterface::PENDING_APPROVAL => array(600),
            StatusInterface::REFUND_REQUESTED => array(800, 900, 1810),
            StatusInterface::REFUNDED => array(1800),
            StatusInterface::REJECTED => array(100),
            StatusInterface::REJECTED_CAPTURE => array(1100),
            StatusInterface::CANCELLED => array(400, 99999),
        ),
    );

    /**
     * @param $gcStatusCode
     * @param $gcPaymentGroupId
     * @return array
     * @throws \Mage_Core_Exception
     */
    public static function getConnectStatus($gcStatusCode, $gcPaymentGroupId)
    {
        if (!array_key_exists($gcPaymentGroupId, self::$statusMap)) {
            \Mage::throwException("Payment group id not supported: {$gcPaymentGroupId}");
        }
        $groupStatuses = self::$statusMap[$gcPaymentGroupId];
        $availableStatuses = array_filter(
            $groupStatuses,
            function ($element) use ($gcStatusCode) {
                return in_array($gcStatusCode, $element) !== false;
            }
        );
        return array_keys($availableStatuses);
    }
}