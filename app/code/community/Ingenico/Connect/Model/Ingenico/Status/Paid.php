<?php

use Ingenico\Connect\Sdk\Domain\Definitions\AbstractOrderStatus;
use Ingenico_Connect_Model_Ingenico_Status_HandlerInterface as HandlerInterface;
use Ingenico_Connect_Model_Ingenico_StatusInterface as StatusInterface;
use Ingenico_Connect_Model_Order_EmailInterface as OrderEmailMananger;

/**
 * Class Ingenico_Connect_Model_Ingenico_Status_Paid
 */
class Ingenico_Connect_Model_Ingenico_Status_Paid implements HandlerInterface
{
    /**
     * @var Ingenico_Connect_Helper_Data
     */
    protected $_helper;

    /**
     * @var OrderEmailMananger
     */
    protected $orderEMailManager;

    /**
     * Ingenico_Connect_Model_Ingenico_Status_Paid constructor.
     * @param array $args
     */
    public function __construct(array $args)
    {
        if (!isset($args['helper']) || !$args['helper'] instanceof Ingenico_Connect_Helper_Data) {
            $args['helper'] = Mage::helper('ingenico_connect');
        }

        $this->_helper = $args['helper'];
        $this->orderEMailManager = Mage::getModel('ingenico_connect/order_emailManager');
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
                    Ingenico_Connect_Model_Method_HostedCheckout::TRANSACTION_INFO_KEY
                )
            );

            $currentStatus = $currentCaptureStatus->status;
        }

        if ($currentStatus !== StatusInterface::CAPTURED) {
            /** @var Ingenico_Connect_Model_Ingenico_Status_Captured $capturedStatus */
            $capturedStatus = Mage::getModel(
                'ingenico_connect/ingenico_status_captured'
            );
            $capturedStatus->resolveStatus($order, $ingenicoStatus);
        }

        $order->addStatusHistoryComment($this->_helper->getPaymentStatusInfo(StatusInterface::PAID));

        $this->orderEMailManager->process($order, $ingenicoStatus->status);
    }
}
