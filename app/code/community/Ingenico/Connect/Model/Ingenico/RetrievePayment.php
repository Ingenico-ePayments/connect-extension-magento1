<?php

use \Ingenico\Connect\Sdk\Domain\Definitions\AbstractOrderStatus;
use \Ingenico\Connect\Sdk\Merchant;
use \Ingenico\Connect\Sdk\Domain\Payment\Definitions\Payment as IngenicoPayment;
use \Ingenico\Connect\Sdk\Domain\Refund\Definitions\RefundResult as IngenicoRefund;
use Ingenico_Connect_Model_Ingenico_ActionInterface as ActionInterface;
use Ingenico_Connect_Model_Method_HostedCheckout as HostedCheckout;

/**
 * Class Ingenico_Connect_Model_Ingenico_RetrievePayment
 *
 * @link https://developer.globalcollect.com/documentation/api/server/#__merchantId__payments__paymentId__get
 */
class Ingenico_Connect_Model_Ingenico_RetrievePayment implements ActionInterface
{
    /**
     * @var Ingenico_Connect_Model_StatusResponseManager
     */
    protected $statusResponseManager;

    /**
     * @var Ingenico_Connect_Model_Ingenico_Api_ClientInterface $ingenicoClient
     */
    protected $ingenicoClient;

    /**
     * @var Ingenico_Connect_Model_ConfigInterface $ePaymentsConfig
     */
    protected $ePaymentsConfig;

    /**
     * @var Ingenico_Connect_Model_Ingenico_Status_ResolverInterface $statusResolver
     */
    protected $statusResolver;

    /**
     * @var Ingenico_Connect_Helper_Data
     */
    protected $helper;

    /**
     * Ingenico_Connect_Model_Ingenico_RetrievePayment constructor.
     */
    public function __construct()
    {
        $this->statusResponseManager = Mage::getModel('ingenico_connect/statusResponseManager');
        $this->ePaymentsConfig = Mage::getModel('ingenico_connect/config');
        $this->ingenicoClient = Mage::getModel('ingenico_connect/ingenico_client');
        $this->statusResolver = Mage::getModel('ingenico_connect/ingenico_status_resolver');
        $this->helper = Mage::helper('ingenico_connect');
    }

    /**
     * Will retrieve updates for all transactions/objects related to the order (payment, capture, refund)
     *
     * @param Mage_Sales_Model_Order $order
     * @return bool true: order was updated because payment status has changed since the last time
     *              false: status has not changed
     * @throws Exception
     * @throws Mage_Core_Exception
     * @throws InvalidArgumentException
     */
    public function process(Mage_Sales_Model_Order $order)
    {
        $orderWasUpdated = false;
        $isIngenicoOrder = $this->helper->isIngenicoOrder($order);

        if (!$isIngenicoOrder) {
            throw new InvalidArgumentException('This order was not placed via Ingenico ePayments');
        }

        /** @var Mage_Sales_Model_Order_Payment $payment */
        $payment = $order->getPayment();

        $ingenicoPaymentId = $payment->getAdditionalInformation(HostedCheckout::PAYMENT_ID_KEY);
        if (!$ingenicoPaymentId) {
            try {
                return $this->updateHostedCheckoutStatus($order);
            } catch (Exception $exception) {
                Mage::throwException(
                    $this->helper->__('Order is not linked with Ingenico ePayments orders.')
                );
            }
        }

        $orderTransactions = Mage::getModel('sales/order_payment_transaction')->getCollection();
        $orderTransactions->addPaymentIdFilter($payment);
        /** @var Mage_Sales_Model_Order_Payment_Transaction $transaction */
        foreach ($orderTransactions as $transaction) {
            $responseObject = $this->statusResponseManager->get($payment, $transaction->getTxnId());
            /** @var \Ingenico\Connect\Sdk\Merchant $merchant */
            $merchant = $this->ingenicoClient
                ->getIngenicoClient($order->getStoreId())
                ->merchant($this->ePaymentsConfig->getMerchantId($order->getStoreId()));
            $update = $this->pullUpdateFor(
                $merchant,
                $responseObject
            );
            if ($update->status !== $responseObject->status) {
                $this->statusResolver->resolve($order, $update);

                // get updated transaction object
                $transaction = $payment->getTransaction($transaction->getTxnId());
                $order->addRelatedObject($transaction);
                $order->addRelatedObject($payment);
                $orderWasUpdated = true;
            } else {
                $this->statusResponseManager->set($payment, $update->id, $update);
                $payment->getTransaction($update->id)
                        ->save();
            }
        }

        if ($orderWasUpdated) {
            $order->save();
        }

        return $orderWasUpdated;
    }

    /**
     * @param Merchant $merchant
     * @param AbstractOrderStatus $data
     * @return AbstractOrderStatus|IngenicoPayment|IngenicoRefund
     */
    protected function pullUpdateFor(Merchant $merchant, AbstractOrderStatus $data)
    {
        $response = null;
        if ($data instanceof \Ingenico\Connect\Sdk\Domain\Refund\Definitions\RefundResult) {
            $response = $merchant->refunds()->get($data->id);
        } elseif ($data instanceof \Ingenico\Connect\Sdk\Domain\Payment\Definitions\Payment) {
            $response = $merchant->payments()->get($data->id);
        } elseif ($data instanceof \Ingenico\Connect\Sdk\Domain\Capture\Definitions\Capture) {
            $response = $merchant->captures()->get($data->id);
        }

        return $response;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return bool
     */
    protected function updateHostedCheckoutStatus(Mage_Sales_Model_Order $order)
    {
        $hostedCheckoutId = $order->getPayment()->getAdditionalInformation(HostedCheckout::HOSTED_CHECKOUT_ID_KEY);
        /** @var Ingenico_Connect_Model_Ingenico_GetHostedCheckoutStatus $status */
        $status = Mage::getModel('ingenico_connect/ingenico_getHostedCheckoutStatus');
        $order = $status->process($hostedCheckoutId);
        $ingenicoPaymentId = $order->getPayment()->getAdditionalInformation(HostedCheckout::PAYMENT_ID_KEY);

        return $ingenicoPaymentId !== null;
    }
}
