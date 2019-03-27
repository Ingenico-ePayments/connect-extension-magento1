<?php

use Netresearch_Epayments_Model_Method_HostedCheckout as HostedCheckout;
use Netresearch_Epayments_Model_Ingenico_AbstractAction as AbstractAction;
use Netresearch_Epayments_Model_Ingenico_ActionInterface as ActionInterface;

/**
 * Class Netresearch_Epayments_Model_Ingenico_ApproveChallengedPayment
 *
 * @link https://developer.globalcollect.com/documentation/api/server/#__merchantId__payments__paymentId__processchallenged_post
 */
class Netresearch_Epayments_Model_Ingenico_ApproveChallengedPayment extends AbstractAction implements ActionInterface
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
     * @var Netresearch_Epayments_Model_Ingenico_Status_Resolver
     */
    protected $statusResolver;

    /**
     * Netresearch_Epayments_Model_Ingenico_ApproveChallengedPayment constructor.
     */
    public function __construct()
    {
        $this->ingenicoClient  = Mage::getSingleton('netresearch_epayments/ingenico_client');
        $this->ePaymentsConfig = Mage::getSingleton('netresearch_epayments/config');
        $this->statusResolver = Mage::getSingleton('netresearch_epayments/ingenico_status_resolver');

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

        $this->statusResolver->resolve($order, $response);
        /** update invoice set state open */
        /** @var Mage_Sales_Model_Order_Invoice $invoice */
        $invoice = $this->getInvoiceForTransactionId($paymentId, $order);
        if ($invoice) {
            $invoice->setState(Mage_Sales_Model_Order_Invoice::STATE_OPEN)->save();
        }

        /**
         * Update order with the new status the payment would have moved to in case it wasn't marked as fraud payment
         */
        $transaction = $payment->getTransaction($paymentId);
        $transaction->setIsClosed(false);
        $this->postProcess($payment, $response);
    }

    /**
     * Return invoice model for transaction
     *
     * @param string $transactionId
     * @param Mage_Sales_Model_Order $order
     * @return Mage_Sales_Model_Order_Invoice|false
     */
    protected function getInvoiceForTransactionId($transactionId, Mage_Sales_Model_Order $order)
    {
        foreach ($order->getInvoiceCollection() as $invoice) {
            if ($invoice->getTransactionId() === $transactionId) {
                $invoice->load($invoice->getId());

                return $invoice;
            }
        }

        return false;
    }
}
