<?php

/**
 * Class Ingenico_Connect_Model_Ingenico_Status_AuthorizationRequested
 */
class Ingenico_Connect_Model_Ingenico_Status_AuthorizationRequested
    extends Ingenico_Connect_Model_Ingenico_Status_PendingFraudApproval
{
    /**
     * The only difference between the AUTHORIZATION_REQUESTED and the PENDING_FRAUD_APPROVAL status currently is
     * that AUTHORIZATION_REQUESTED can not be reviewed. This difference is handled in
     * Ingenico_Connect_Model_Method_HostedCheckout::canReviewPayment()
     */
}
