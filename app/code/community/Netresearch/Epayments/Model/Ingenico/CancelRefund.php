<?php

/**
 * Class Netresearch_Epayments_Model_Ingenico_CancelRefund
 */
class Netresearch_Epayments_Model_Ingenico_CancelRefund
    extends Netresearch_Epayments_Model_Ingenico_AbstractAction
    implements Netresearch_Epayments_Model_Ingenico_ActionInterface
{
    /**
     * @var string[]
     */
    protected $allowedStates = array(
        Netresearch_Epayments_Model_Ingenico_RefundHandlerInterface::REFUND_PENDING_APPROVAL,
        Netresearch_Epayments_Model_Ingenico_RefundHandlerInterface::REFUND_REFUND_REQUESTED
    );

    /**
     * @var  Netresearch_Epayments_Model_Ingenico_Api_ClientInterface
     */
    protected $ingenicoClient;

    /**
     * @var Netresearch_Epayments_Model_ConfigInterface
     */
    protected $ePaymentsConfig;

    /**
     * @var Netresearch_Epayments_Model_Ingenico_RetrievePayment
     */
    protected $retrievePayment;

    /**
     * @var Netresearch_Epayments_Model_Ingenico_Status_ResolverInterface
     */
    protected $statusResolver;

    /**
     * Netresearch_Epayments_Model_Ingenico_CancelRefund constructor.
     */
    public function __construct()
    {
        $this->ingenicoClient = Mage::getSingleton('netresearch_epayments/ingenico_client');
        $this->ePaymentsConfig = Mage::getSingleton('netresearch_epayments/config');
        $this->retrievePayment = Mage::getSingleton('netresearch_epayments/ingenico_retrievePayment');
        $this->statusResolver = Mage::getModel('netresearch_epayments/ingenico_status_resolver');

        parent::__construct();
    }

    /**
     * Cancel the creditmemo at the Ingenico API
     * and within Magento itself.
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
            Mage::throwException("Cannot cancel refund with status $refundResponse->status");
        }

        // Cancel refund via Ingenico API
        $this->cancelRefund($creditmemo);

        // Retrieve current status from api because
        // cancelRefund only returns a HTTP status code
        $this->retrievePayment->process($order);

        $refundResponse = $this->statusResponseManager->get($payment, $refundId);

        $this->statusResolver->resolve($order, $refundResponse);

        $creditmemo->cancel();
    }

    /**
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     */
    protected function cancelRefund(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $this->ingenicoClient->getIngenicoClient($creditmemo->getStoreId())
            ->merchant($this->ePaymentsConfig->getMerchantId($creditmemo->getStoreId()))
            ->refunds()
            ->cancel($creditmemo->getTransactionId());
    }
}
