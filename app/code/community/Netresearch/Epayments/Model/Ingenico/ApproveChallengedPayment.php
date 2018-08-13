<?php

use Netresearch_Epayments_Model_Method_HostedCheckout as HostedCheckout;

/**
 * Class Netresearch_Epayments_Model_Ingenico_ApproveChallengedPayment
 *
 * @link https://developer.globalcollect.com/documentation/api/server/#__merchantId__payments__paymentId__processchallenged_post
 */
class Netresearch_Epayments_Model_Ingenico_ApproveChallengedPayment
    extends Netresearch_Epayments_Model_Ingenico_AbstractAction
    implements Netresearch_Epayments_Model_Ingenico_ActionInterface
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
     * Netresearch_Epayments_Model_Ingenico_ApproveChallengedPayment constructor.
     */
    public function __construct()
    {
        $this->ingenicoClient  = Mage::getSingleton('netresearch_epayments/ingenico_client');
        $this->ePaymentsConfig = Mage::getSingleton('netresearch_epayments/config');

        parent::__construct();
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @throws Mage_Core_Exception
     */
    public function process(Mage_Sales_Model_Order $order)
    {
        $isIngenicoFraudOrder = Mage::helper('netresearch_epayments')->isIngenicoFraudOrder($order);

        if (!$isIngenicoFraudOrder) {
            throw new InvalidArgumentException(
                'This order was not placed via Ingenico ePayments or was not detected as fraud'
            );
        }

        $payment = $order->getPayment();
        $paymentId = $payment->getAdditionalInformation(HostedCheckout::PAYMENT_ID_KEY);

        $response = $this->ingenicoClient->getIngenicoClient($order->getStoreId())
                                         ->merchant($this->ePaymentsConfig->getMerchantId($order->getStoreId()))
                                         ->payments()
                                         ->processchallenged($paymentId);

        /**
         * Update order with the new status the payment would have moved to in case it wasn't marked as fraud payment
         */
        /** @var Netresearch_Epayments_Model_Ingenico_StatusFactory $statusFactory */

        $transaction = $payment->getTransaction($paymentId);
        $transaction->setIsClosed(false);
        $this->postProcess($payment, $response);
    }
}
