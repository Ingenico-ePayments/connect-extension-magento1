<?php

use Netresearch_Epayments_Model_Method_HostedCheckout as HostedCheckout;

class Netresearch_Epayments_Model_Ingenico_UndoCapturePaymentRequest
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
        return array(Netresearch_Epayments_Model_Ingenico_StatusInterface::CAPTURE_REQUESTED);
    }
}
