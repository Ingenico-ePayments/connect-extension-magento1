<?php

use Netresearch_Epayments_Model_Method_HostedCheckout as HostedCheckout;

/**
 * Class Netresearch_Epayments_Model_Ingenico_CancelPayment
 *
 * @link https://developer.globalcollect.com/documentation/api/server/#__merchantId__payments__paymentId__cancel_post
 */
class Netresearch_Epayments_Model_Ingenico_CancelPayment
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
        $transaction = $payment->getTransaction($transactionId);
        $transaction->setIsClosed(true);
        $order->addRelatedObject($transaction);
        $this->postProcess($payment, $response->payment);
    }
}
