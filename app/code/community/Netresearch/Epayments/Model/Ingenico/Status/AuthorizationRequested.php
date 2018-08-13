<?php

/**
 * Class Netresearch_Epayments_Model_Ingenico_Status_AuthorizationRequested
 */
class Netresearch_Epayments_Model_Ingenico_Status_AuthorizationRequested
    extends Netresearch_Epayments_Model_Ingenico_Status_PendingFraudApproval
{
    /**
     * The only difference between the AUTHORIZATION_REQUESTED and the PENDING_FRAUD_APPROVAL status currently is
     * that AUTHORIZATION_REQUESTED can not be reviewed. This difference is handled in
     * Netresearch_Epayments_Model_Method_HostedCheckout::canReviewPayment()
     */
}
