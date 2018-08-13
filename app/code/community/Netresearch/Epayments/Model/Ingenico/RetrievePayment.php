<?php

use \Ingenico\Connect\Sdk\Domain\Definitions\AbstractOrderStatus;
use \Ingenico\Connect\Sdk\Merchant;
use \Ingenico\Connect\Sdk\Domain\Payment\Definitions\Payment as IngenicoPayment;
use \Ingenico\Connect\Sdk\Domain\Refund\Definitions\RefundResult as IngenicoRefund;
use Netresearch_Epayments_Model_Ingenico_ActionInterface as ActionInterface;

/**
 * Class Netresearch_Epayments_Model_Ingenico_RetrievePayment
 *
 * @link https://developer.globalcollect.com/documentation/api/server/#__merchantId__payments__paymentId__get
 */
class Netresearch_Epayments_Model_Ingenico_RetrievePayment implements ActionInterface
{
    /**
     * @var Netresearch_Epayments_Model_StatusResponseManager
     */
    protected $statusResponseManager;

    /**
     * @var Netresearch_Epayments_Model_Ingenico_Api_ClientInterface $ingenicoClient
     */
    protected $ingenicoClient;

    /**
     * @var Netresearch_Epayments_Model_ConfigInterface $ePaymentsConfig
     */
    protected $ePaymentsConfig;

    /**
     * @var Netresearch_Epayments_Model_Ingenico_StatusFactory $statusFactory
     */
    protected $statusFactory;

    /**
     * Netresearch_Epayments_Model_Ingenico_RetrievePayment constructor.
     */
    public function __construct()
    {
        $this->statusResponseManager = Mage::getModel('netresearch_epayments/statusResponseManager');
        $this->ePaymentsConfig = Mage::getModel('netresearch_epayments/config');
        $this->ingenicoClient = Mage::getModel('netresearch_epayments/ingenico_client');
        $this->statusFactory = Mage::getModel('netresearch_epayments/ingenico_statusFactory');
    }


    /**
     * Will retrieve updates for all transactions/objects related to the order (payment, capture, refund)
     *
     * @param Mage_Sales_Model_Order $order
     * @return bool true: order was updated because payment status has changed since the last time
     *              false: status has not changed
     * @throws Exception
     * @throws Mage_Core_Exception
     */
    public function process(Mage_Sales_Model_Order $order)
    {
        $orderWasUpdated = false;
        $isIngenicoOrder = Mage::helper('netresearch_epayments')->isIngenicoOrder($order);

        if (!$isIngenicoOrder) {
            throw new InvalidArgumentException('This order was not placed via Ingenico ePayments');
        }

        /** @var Mage_Sales_Model_Order_Payment $payment */
        $payment = $order->getPayment();

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
            if ($update->status != $responseObject->status) {
                $status = $this->statusFactory->create($update);
                $status->apply($order);
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
    protected function pullUpdateFor(
        Merchant $merchant,
        AbstractOrderStatus $data
    )
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
}
