<?php

/**
 * Ingenico_Connect
 *
 * See LICENSE.txt for license details.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to
 * newer versions in the future.
 *
 * @category  Epayments
 * @package   Ingenico_Connect
 *
 *
 * Class Ingenico_Connect_Model_Ingenico_ApproveRefund
 * @see https://epayments-api.developer-ingenico.com/s2sapi/v1/en_US/php/refunds/cancel.html
 */
class Ingenico_Connect_Model_Ingenico_ApproveRefund
    extends Ingenico_Connect_Model_Ingenico_AbstractAction
    implements Ingenico_Connect_Model_Ingenico_ActionInterface
{
    /**
     * @var string[]
     */
    protected $allowedStates = array(
        Ingenico_Connect_Model_Ingenico_StatusInterface::PENDING_APPROVAL,
    );

    /**
     * @var  Ingenico_Connect_Model_Ingenico_Api_ClientInterface
     */
    protected $ingenicoClient;

    /**
     * @var Ingenico_Connect_Model_ConfigInterface
     */
    protected $ePaymentsConfig;

    /**
     * @var Ingenico_Connect_Model_Ingenico_RetrievePayment
     */
    protected $retrievePayment;

    /**
     * @var Ingenico_Connect_Model_Ingenico_Status_ResolverInterface
     */
    protected $statusResolver;

    /**
     * @var \Ingenico\Connect\Sdk\Domain\Refund\ApproveRefundRequest
     */
    protected $approveRefundRequest;

    /**
     * Ingenico_Connect_Model_Ingenico_ApproveRefund constructor.
     */
    public function __construct()
    {
        $this->ingenicoClient = Mage::getSingleton('ingenico_connect/ingenico_client');
        $this->ePaymentsConfig = Mage::getSingleton('ingenico_connect/config');
        $this->retrievePayment = Mage::getSingleton('ingenico_connect/ingenico_retrievePayment');
        $this->statusResolver = Mage::getModel('ingenico_connect/ingenico_status_resolver');
        $this->approveRefundRequest = new \Ingenico\Connect\Sdk\Domain\Refund\ApproveRefundRequest();

        parent::__construct();
    }

    /**
     * Approve the creditmemo at the Ingenico API
     *
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     * @throws Mage_Core_Exception
     */
    public function process(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();
        $refundId = $creditmemo->getTransactionId();
        $payment = $order->getPayment();

        $refundResponse = $this->statusResponseManager->get($payment, $refundId);

        $isAllowedStatus = in_array(
            $refundResponse->status,
            $this->allowedStates
        );
        if (!$isAllowedStatus) {
            Mage::throwException("Cannot approve refund with status $refundResponse->status");
        }

        // Approve refund via Ingenico API
        $this->approveRefund($creditmemo);

        // Retrieve current status from api because
        // approveRefund only returns a HTTP status code
        $this->retrievePayment->process($order);
        $refundResponse = $this->statusResponseManager->get($payment, $refundId);

        $this->statusResolver->resolve($order, $refundResponse);
    }

    /**
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     */
    protected function approveRefund(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $amount = Mage::helper('ingenico_connect')->formatIngenicoAmount($creditmemo->getGrandTotal());
        $this->approveRefundRequest->amount = $amount;
        $this->ingenicoClient->getIngenicoClient($creditmemo->getStoreId())
        ->merchant($this->ePaymentsConfig->getMerchantId($creditmemo->getStoreId()))
            ->refunds()->approve($creditmemo->getTransactionId(), $this->approveRefundRequest);
    }
}
