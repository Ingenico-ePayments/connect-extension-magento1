<?php

interface Netresearch_Epayments_Model_Ingenico_StatusInterface
{
    const REDIRECTED = 'REDIRECTED';
    const PENDING_PAYMENT = 'PENDING_PAYMENT';
    const PENDING_COMPLETION = 'PENDING_COMPLETION';
    const ACCOUNT_VERIFIED = 'ACCOUNT_VERIFIED';
    const PENDING_FRAUD_APPROVAL = 'PENDING_FRAUD_APPROVAL';
    const AUTHORIZATION_REQUESTED = 'AUTHORIZATION_REQUESTED';
    const PENDING_APPROVAL = 'PENDING_APPROVAL';
    const PENDING_CAPTURE = 'PENDING_CAPTURE';
    const CAPTURE_REQUESTED = 'CAPTURE_REQUESTED';
    const CAPTURED = 'CAPTURED';
    const PAID = 'PAID';
    const REVERSED = 'REVERSED';
    const CHARGEBACK = 'CHARGEBACKED';
    const REJECTED = 'REJECTED';
    const REJECTED_CAPTURE = 'REJECTED_CAPTURE';
    const CANCELLED = 'CANCELLED';
    const REFUND_REQUESTED = 'REFUND_REQUESTED';
    const REFUNDED = 'REFUNDED';

    // below are statuses for payout/bank refunds
    const PAYOUT_REQUESTED = 'PAYOUT_REQUESTED';
    const ACCOUNT_CREDITED = 'ACCOUNT_CREDITED';
    const REJECTED_CREDIT = 'REJECTED_CREDIT';

    /**
     * @return string
     */
    public function getStatus();

    /**
     * @return string
     */
    public function getStatusCode();

    /**
     * @param Mage_Sales_Model_Order $order
     * @return void
     */
    public function apply(Mage_Sales_Model_Order $order);
}
