<?php

use \Ingenico\Connect\Sdk\Domain\Refund\Definitions\RefundResult;
use Netresearch_Epayments_Model_Ingenico_RefundStatusInterface as RefundStatusInterface;

abstract class Netresearch_Epayments_Model_Ingenico_Status_Refund_AbstractStatus implements RefundStatusInterface
{
    /**
     * @var Netresearch_Epayments_Model_StatusResponseManager
     */
    protected $statusResponseManager;

    /**
     * @var string
     */
    protected $status;

    /**
     * @var string
     */
    protected $statusCode;

    /**
     * @var \Ingenico\Connect\Sdk\Domain\Refund\Definitions\RefundResult
     */
    protected $ingenicoOrderStatus;

    /**
     * @var Mage_Sales_Model_Order_Creditmemo
     */
    protected $creditmemo;

    /**
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        if (!isset($args['gcOrderStatus'])
            || !$args['gcOrderStatus'] instanceof RefundResult
        ) {
            throw new InvalidArgumentException('gcOrderStatus parameter is required.');
        }

        $this->statusResponseManager = Mage::getModel('netresearch_epayments/statusResponseManager');
        $this->ingenicoOrderStatus = $args['gcOrderStatus'];
        $this->status              = $this->ingenicoOrderStatus->status;
        $this->statusCode          = $this->ingenicoOrderStatus->statusOutput->statusCode;#
    }

    /**
     * {@inheritDoc}
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * {@inheritDoc}
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @throws Exception
     * @throws Mage_Core_Exception
     */
    public function apply(Mage_Sales_Model_Order $order)
    {
        $payment = $order->getPayment();
        $payment->setTransactionId($this->ingenicoOrderStatus->id);

        if (!$this->statusResponseManager->get($payment, $this->ingenicoOrderStatus->id)) {
            $this->statusResponseManager->set(
                $payment,
                $this->ingenicoOrderStatus->id,
                $this->ingenicoOrderStatus
            );
        }

        $this->_apply($order);

        $this->statusResponseManager->set(
            $payment,
            $this->ingenicoOrderStatus->id,
            $this->ingenicoOrderStatus
        );

        // Save everything involved in status application
        $creditmemo = $this->getCreditmemo($payment);

        if ($creditmemo->getInvoice()) {
            $order->addRelatedObject($creditmemo->getInvoice());
        }
        $order->addRelatedObject($creditmemo);
        $order->addRelatedObject($payment->getTransaction($payment->getTransactionId()));
        $order->addRelatedObject($payment->getTransaction($creditmemo->getTransactionId()));
        $order->setDataChanges(true);

        $order->save();
    }

    /**
     * @param Mage_Sales_Model_Order $order
     */
    protected abstract function _apply(Mage_Sales_Model_Order $order);

    /**
     * Gets the creditmemo that is currently being created or loads the creditmemo via the transaction id from ING
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return Mage_Sales_Model_Order_Creditmemo
     */
    protected function getCreditmemo(Mage_Sales_Model_Order_Payment $payment)
    {
        if (!$this->creditmemo) {
            if ($payment->getCreditMemo()) {
                $this->creditmemo = $payment->getCreditMemo();
            } else {
                $this->creditmemo = $payment->getOrder()
                                        ->getCreditmemosCollection()
                                        ->addFieldToFilter(
                                            'transaction_id',
                                            $this->ingenicoOrderStatus->id
                                        )
                                        ->getFirstItem();
            }
        }

        return $this->creditmemo;
    }
}
