<?php

use Netresearch_Epayments_Model_Ingenico_Status_AbstractStatus as AbstractStatus;

class Netresearch_Epayments_Model_Ingenico_Status_Paid extends AbstractStatus
{
    /**
     * @var Netresearch_Epayments_Helper_Data
     */
    protected $_helper;

    /**
     * Netresearch_Epayments_Model_Ingenico_Status_Paid constructor.
     * @param array $args
     */
    public function __construct(array $args)
    {
        parent::__construct($args);
        if (!isset($args['helper']) || !$args['helper'] instanceof Netresearch_Epayments_Helper_Data) {
            $args['helper'] = Mage::helper('netresearch_epayments');
        }
        $this->_helper = $args['helper'];
    }

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
        if ($currentStatus !== self::CAPTURED) {
            /** @var Netresearch_Epayments_Model_Ingenico_Status_Captured $capturedStatus */
            $capturedStatus = Mage::getModel(
                'netresearch_epayments/ingenico_status_captured',
                array('gcOrderStatus' => $this->ingenicoOrderStatus)
            );
            $capturedStatus->apply($order);
        }
        $order->addStatusHistoryComment($this->_helper->getPaymentStatusInfo(self::PAID));

        $this->orderEMailManager->process($order, $this->getStatus());
    }
}
