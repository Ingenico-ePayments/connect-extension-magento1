<?php

use Ingenico\Connect\Sdk\Domain\Capture\CaptureResponse;
use Ingenico\Connect\Sdk\Domain\Payment\ApprovePaymentRequest;
use Ingenico\Connect\Sdk\Domain\Payment\CapturePaymentRequest;
use Ingenico\Connect\Sdk\Domain\Payment\Definitions\OrderApprovePayment;
use Ingenico\Connect\Sdk\Domain\Payment\Definitions\OrderReferencesApprovePayment;
use Ingenico\Connect\Sdk\Domain\Payment\Definitions\Payment;
use Ingenico_Connect_Model_Method_HostedCheckout as HostedCheckout;

/**
 * Class Ingenico_Connect_Model_Ingenico_CapturePayment
 *
 * @link https://developer.globalcollect.com/documentation/api/server/#__merchantId__payments__paymentId__approve_post
 */
class Ingenico_Connect_Model_Ingenico_CapturePayment
    extends Ingenico_Connect_Model_Ingenico_AbstractAction
{
    /**
     * @var Ingenico_Connect_Model_Ingenico_Api_ClientInterface
     */
    protected $ingenicoClient;

    /**
     * @var Ingenico_Connect_Model_ConfigInterface
     */
    protected $ePaymentsConfig;

    /**
     * @var Ingenico_Connect_Model_Ingenico_MerchantReference
     */
    protected $merchantReference;

    /**
     * @var Ingenico_Connect_Model_Ingenico_GlobalCollect_OrderstatusHelper
     */
    protected $orderStatusHelper;

    /**
     * Ingenico_Connect_Model_Ingenico_CreateHostedCheckout constructor.
     */
    public function __construct()
    {
        $this->ingenicoClient = Mage::getSingleton('ingenico_connect/ingenico_client');
        $this->ePaymentsConfig = Mage::getSingleton('ingenico_connect/config');
        $this->merchantReference = Mage::getSingleton('ingenico_connect/ingenico_merchantReference');
        $this->orderStatusHelper = Mage::getSingleton('ingenico_connect/ingenico_globalCollect_orderStatusHelper');

        parent::__construct();
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param float $amount
     * @throws Exception
     */
    public function process(Mage_Sales_Model_Order $order, $amount)
    {
        /** @var Mage_Sales_Model_Order_Payment $payment */
        $payment = $order->getPayment();
        $transactionId = $payment->getAdditionalInformation(HostedCheckout::PAYMENT_ID_KEY);
        $authResponseObject = $this->statusResponseManager->get($payment, $transactionId);

        $ingenicoPaymentId = $authResponseObject->id;
        $status = $authResponseObject->status;

        if ($status === Ingenico_Connect_Model_Ingenico_StatusInterface::PENDING_CAPTURE) {
            /** @var CaptureResponse $response */
            $response = $this->capturePayment($ingenicoPaymentId, $payment, $amount);
        } else {
            if ($status === Ingenico_Connect_Model_Ingenico_StatusInterface::PENDING_APPROVAL) {
                /** @var Payment $response */
                $response = $this->approvePayment($ingenicoPaymentId, $payment, $amount);
                if (!$this->orderStatusHelper->shouldOrderSkipPaymentReview($response)) {
                    $payment->setIsTransactionClosed(false);
                    $payment->setIsTransactionPending(true);
                } else {
                    foreach ($order->getInvoiceCollection() as $invoice) {
                        if ($invoice->getTransactionId() === $response->id) {
                            $invoice->setState(Mage_Sales_Model_Order_Invoice::STATE_OPEN);
                        }
                    }
                }
            } else {
                Mage::throwException("Unknown or invalid payment status $status");
            }
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
        $orderReferencesApprovePayment->merchantReference =
            $this->merchantReference->generateMerchantReference($payment->getOrder());

        $orderApprovePayment = new OrderApprovePayment();
        $orderApprovePayment->references = $orderReferencesApprovePayment;

        $request->order = $orderApprovePayment;
        $request->amount = Mage::helper('ingenico_connect')->formatIngenicoAmount($amount);

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
        $request->amount = Mage::helper('ingenico_connect')->formatIngenicoAmount($amount);
        $storeId = $payment->getOrder()->getStoreId();

        $response = $this->ingenicoClient
            ->getIngenicoClient($storeId)
            ->merchant($this->ePaymentsConfig->getMerchantId($storeId))
            ->payments()
            ->capture($ingenicoPaymentId, $request);

        return $response;
    }
}
