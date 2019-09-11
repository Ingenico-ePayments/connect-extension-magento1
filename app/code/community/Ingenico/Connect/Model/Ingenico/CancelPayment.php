<?php

use Ingenico_Connect_Model_Method_HostedCheckout as HostedCheckout;

/**
 * Class Ingenico_Connect_Model_Ingenico_CancelPayment
 *
 * @link https://developer.globalcollect.com/documentation/api/server/#__merchantId__payments__paymentId__cancel_post
 */
class Ingenico_Connect_Model_Ingenico_CancelPayment
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
     * @var Ingenico_Connect_Model_Ingenico_Status_ResolverInterface
     */
    protected $statusResolver;

    /**
     * Ingenico_Connect_Model_Ingenico_CreateHostedCheckout constructor.
     */
    public function __construct()
    {
        $this->ingenicoClient  = Mage::getSingleton('ingenico_connect/ingenico_client');
        $this->ePaymentsConfig = Mage::getSingleton('ingenico_connect/config');
        $this->statusResolver  = Mage::getSingleton('ingenico_connect/ingenico_status_resolver');

        parent::__construct();
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @throws Exception
     */
    public function process(Mage_Sales_Model_Order $order)
    {
        $payment = $order->getPayment();
        $transactionId = $payment->getAdditionalInformation(HostedCheckout::PAYMENT_ID_KEY);
        $authResponseObject = $this->statusResponseManager->get($payment, $transactionId);
        $ingenicoPaymentId = $authResponseObject->id;

        $response = $this->ingenicoClient
            ->getIngenicoClient($order->getStoreId())
            ->merchant($this->ePaymentsConfig->getMerchantId($order->getStoreId()))
            ->payments()
            ->cancel($ingenicoPaymentId);
        $this->statusResolver->resolve($order, $response->payment);

        $transaction = $payment->getTransaction($transactionId);
        $transaction->setIsClosed(true);
        $order->addRelatedObject($transaction);
        $this->postProcess($payment, $response->payment);
    }
}
