<?php

use Netresearch_Epayments_Model_Ingenico_Status_AbstractStatus as AbstractStatus;

class Netresearch_Epayments_Model_Ingenico_Status_Captured extends AbstractStatus
{
    /**
     * {@inheritDoc}
     */
    public function _apply(Mage_Sales_Model_Order $order)
    {
        $payment = $order->getPayment();
        $currentStatus = '';
        $captureTransaction = $payment->getTransaction($this->ingenicoOrderStatus->id);
        if ($captureTransaction) {
            $currentCaptureStatus = new \Ingenico\Connect\Sdk\Domain\Capture\CaptureResponse();
            $currentCaptureStatus = $currentCaptureStatus->fromJson(
                $captureTransaction->getAdditionalInformation(
                    Netresearch_Epayments_Model_Method_HostedCheckout::TRANSACTION_INFO_KEY
                )
            );

            $currentStatus = $currentCaptureStatus->status;
        }
        if ($currentStatus !== self::CAPTURE_REQUESTED) {
            /** @var Netresearch_Epayments_Model_Ingenico_Status_CaptureRequested $captureRequestedStatus */
            $captureRequestedStatus = Mage::getModel(
                'netresearch_epayments/ingenico_status_captureRequested',
                array('gcOrderStatus' => $this->ingenicoOrderStatus)
            );
            $captureRequestedStatus->apply($order);
        }

        $payment->setNotificationResult(true);
        $payment->registerPaymentReviewAction(Mage_Sales_Model_Order_Payment::REVIEW_ACTION_ACCEPT, false);

        $this->orderEMailManager->process($order, $this->getStatus());
    }
}
