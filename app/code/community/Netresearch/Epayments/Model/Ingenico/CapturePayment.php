<?php

use \Ingenico\Connect\Sdk\Domain\Payment\ApprovePaymentRequest;
use \Ingenico\Connect\Sdk\Domain\Payment\CapturePaymentRequest;
use \Ingenico\Connect\Sdk\Domain\Capture\CaptureResponse;
use \Ingenico\Connect\Sdk\Domain\Payment\Definitions\OrderApprovePayment;
use \Ingenico\Connect\Sdk\Domain\Payment\Definitions\OrderReferencesApprovePayment;
use \Ingenico\Connect\Sdk\Domain\Payment\Definitions\Payment;
use \Ingenico\Connect\Sdk\Domain\Payment\PaymentApprovalResponse;
use Netresearch_Epayments_Model_Method_HostedCheckout as HostedCheckout;
/**
 * Class Netresearch_Epayments_Model_Ingenico_CapturePayment
 *
 * @link https://developer.globalcollect.com/documentation/api/server/#__merchantId__payments__paymentId__approve_post
 */
class Netresearch_Epayments_Model_Ingenico_CapturePayment
    extends Netresearch_Epayments_Model_Ingenico_AbstractAction
{
    /**
     * @var Netresearch_Epayments_Model_Ingenico_Api_ClientInterface
     */
    protected $ingenicoClient;

    /**
     * @var Netresearch_Epayments_Model_ConfigInterface
     */
    protected $ePaymentsConfig;

    /**
     * Netresearch_Epayments_Model_Ingenico_CreateHostedCheckout constructor.
     */
    public function __construct()
    {
        $this->ingenicoClient  = Mage::getSingleton('netresearch_epayments/ingenico_client');
        $this->ePaymentsConfig = Mage::getSingleton('netresearch_epayments/config');

        parent::__construct();
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param float $amount
     * @throws Exception
     */
    public function process(Mage_Sales_Model_Order $order, $amount)
    {
        $payment = $order->getPayment();
        $transactionId = $payment->getAdditionalInformation(HostedCheckout::PAYMENT_ID_KEY);
        $authResponseObject = $this->statusResponseManager->get($payment, $transactionId);

        $ingenicoPaymentId = $authResponseObject->id;
        $status = $authResponseObject->status;

        if ($status == Netresearch_Epayments_Model_Ingenico_StatusInterface::PENDING_CAPTURE) {
            /** @var CaptureResponse $response */
            $response = $this->capturePayment($ingenicoPaymentId, $payment, $amount);
        } else if ($status == Netresearch_Epayments_Model_Ingenico_StatusInterface::PENDING_APPROVAL) {
            /** @var PaymentApprovalResponse $response */
            $response = $this->approvePayment($ingenicoPaymentId, $payment, $amount);
        } else {
            Mage::throwException("Unknown or invalid payment status $status");
        }
        if ($response->status === Netresearch_Epayments_Model_Ingenico_StatusInterface::CAPTURE_REQUESTED) {
            $payment->setIsTransactionPending(true); // set order status to 'Payment Review'
        }
        $this->postProcess($payment, $response);
    }

    /**
     * Approve payments made via global collect api
     *
     * @param string $ingenicoPaymentId
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param float $amount
     * @return Payment
     */
    protected function approvePayment($ingenicoPaymentId, $payment, $amount)
    {
        $request = new ApprovePaymentRequest();

        $orderReferencesApprovePayment = new OrderReferencesApprovePayment();
        $orderReferencesApprovePayment->merchantReference = $payment->getOrder()->getIncrementId();

        $orderApprovePayment = new OrderApprovePayment();
        $orderApprovePayment->references = $orderReferencesApprovePayment;

        $request->order = $orderApprovePayment;
        $request->amount = Mage::helper('netresearch_epayments')->formatIngenicoAmount($amount);

        $scopeId = $payment->getOrder()->getStoreId();
        $response = $this->ingenicoClient
            ->getIngenicoClient($scopeId)
            ->merchant($this->ePaymentsConfig->getMerchantId($scopeId))
            ->payments()
            ->approve($ingenicoPaymentId, $request);

        return $response->payment;
    }

    /**
     * Capture payments via Ogone api. With no further settings the request will always capture the full amount and
     * finish the transaction
     *
     * @param string $ingenicoPaymentId
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param float $amount
     * @return CaptureResponse
     */
    protected function capturePayment($ingenicoPaymentId, $payment, $amount)
    {
        $request = new CapturePaymentRequest();
        $request->amount = Mage::helper('netresearch_epayments')->formatIngenicoAmount($amount);
        $storeId = $payment->getOrder()->getStoreId();

        $response = $this->ingenicoClient
            ->getIngenicoClient($storeId)
            ->merchant($this->ePaymentsConfig->getMerchantId($storeId))
            ->payments()
            ->capture($ingenicoPaymentId, $request);

        return $response;
    }
}
