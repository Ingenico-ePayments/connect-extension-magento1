<?php

use Ingenico\Connect\Sdk\Domain\Definitions\AbstractOrderStatus;
use Netresearch_Epayments_Model_Ingenico_Status_HandlerInterface as HandlerInterface;
use Netresearch_Epayments_Model_Ingenico_StatusInterface as StatusInterface;
use Netresearch_Epayments_Model_Order_EmailInterface as OrderEmailMananger;

/**
 * Class Netresearch_Epayments_Model_Ingenico_Status_Paid
 */
class Netresearch_Epayments_Model_Ingenico_Status_Paid implements HandlerInterface
{
    /**
     * @var Netresearch_Epayments_Helper_Data
     */
    protected $_helper;

    /**
     * @var OrderEmailMananger
     */
    protected $orderEMailManager;

    /**
     * Netresearch_Epayments_Model_Ingenico_Status_Paid constructor.
     * @param array $args
     */
    public function __construct(array $args)
    {
        if (!isset($args['helper']) || !$args['helper'] instanceof Netresearch_Epayments_Helper_Data) {
            $args['helper'] = Mage::helper('netresearch_epayments');
        }

        $this->_helper = $args['helper'];
        $this->orderEMailManager = Mage::getModel('netresearch_epayments/order_emailManager');
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param AbstractOrderStatus $ingenicoStatus
     */
    public function resolveStatus(Mage_Sales_Model_Order $order, AbstractOrderStatus $ingenicoStatus)
    {
        $payment = $order->getPayment();
        $currentStatus = '';
        $captureTransaction = $payment->getTransaction($ingenicoStatus->id);
        if ($captureTransaction) {
            $currentCaptureStatus = new \Ingenico\Connect\Sdk\Domain\Capture\CaptureResponse();
            $currentCaptureStatus = $currentCaptureStatus->fromJson(
                $captureTransaction->getAdditionalInformation(
                    Netresearch_Epayments_Model_Method_HostedCheckout::TRANSACTION_INFO_KEY
                )
            );

            $currentStatus = $currentCaptureStatus->status;
        }

        if ($currentStatus !== StatusInterface::CAPTURED) {
            /** @var Netresearch_Epayments_Model_Ingenico_Status_Captured $capturedStatus */
            $capturedStatus = Mage::getModel(
                'netresearch_epayments/ingenico_status_captured'
            );
            $capturedStatus->resolveStatus($order, $ingenicoStatus);
        }

        $order->addStatusHistoryComment($this->_helper->getPaymentStatusInfo(StatusInterface::PAID));

        $this->orderEMailManager->process($order, $ingenicoStatus->status);
    }
}
