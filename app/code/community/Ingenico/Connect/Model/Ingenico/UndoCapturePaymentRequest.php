<?php

use Ingenico_Connect_Model_Method_HostedCheckout as HostedCheckout;

class Ingenico_Connect_Model_Ingenico_UndoCapturePaymentRequest
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
     * Ingenico_Connect_Model_Ingenico_CreateHostedCheckout constructor.
     */
    public function __construct()
    {
        $this->ingenicoClient  = Mage::getSingleton('ingenico_connect/ingenico_client');
        $this->ePaymentsConfig = Mage::getSingleton('ingenico_connect/config');

        parent::__construct();
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @throws Exception
     */
    public function process(Mage_Sales_Model_Order $order)
    {
        $payment = $order->getPayment();
        $ingenicoPaymentId = $payment->getAdditionalInformation(HostedCheckout::PAYMENT_ID_KEY);

        $statusResponse = $this->ingenicoClient
            ->getIngenicoClient($order->getStoreId())
            ->merchant($this->ePaymentsConfig->getMerchantId($order->getStoreId()))
            ->payments()
            ->get($ingenicoPaymentId);

        if (in_array($statusResponse->status, $this->getValidStatuses(), true)) {
            $this->ingenicoClient
                ->getIngenicoClient($order->getStoreId())
                ->merchant($this->ePaymentsConfig->getMerchantId($order->getStoreId()))
                ->payments()
                ->cancelapproval($ingenicoPaymentId);

            $authTransaction = $payment->getTransaction($ingenicoPaymentId);
            $authTransaction->setIsClosed(false);
            $authTransaction->save();
        }
    }

    /**
     * @return array
     */
    protected function getValidStatuses()
    {
        return array(Ingenico_Connect_Model_Ingenico_StatusInterface::CAPTURE_REQUESTED);
    }
}
