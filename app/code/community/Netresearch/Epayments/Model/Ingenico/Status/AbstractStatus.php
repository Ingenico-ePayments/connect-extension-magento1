<?php

use Netresearch_Epayments_Model_Ingenico_StatusInterface as StatusInterface;

abstract class Netresearch_Epayments_Model_Ingenico_Status_AbstractStatus implements StatusInterface
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
     * @var \Ingenico\Connect\Sdk\Domain\Payment\Definitions\Payment
     */
    protected $ingenicoOrderStatus;

    /**
     * @var Netresearch_Epayments_Model_Order_EmailInterface
     */
    protected $orderEMailManager;

    /**
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        if (!isset($args['gcOrderStatus'])
            || !$args['gcOrderStatus'] instanceof \Ingenico\Connect\Sdk\Domain\Definitions\AbstractOrderStatus
        ) {
            throw new InvalidArgumentException('gcOrderStatus parameter is required.');
        }

        $this->statusResponseManager = Mage::getModel('netresearch_epayments/statusResponseManager');
        $this->ingenicoOrderStatus = $args['gcOrderStatus'];
        $this->status = $this->ingenicoOrderStatus->status;
        $this->orderEMailManager = Mage::getModel('netresearch_epayments/order_emailManager');

        $statusOutput = $this->ingenicoOrderStatus->statusOutput;
        $this->statusCode = $statusOutput->statusCode;

    }

    /**
     * @param Mage_Sales_Model_Order $order
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
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return mixed
     */
    protected abstract function _apply(Mage_Sales_Model_Order $order);

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
}
