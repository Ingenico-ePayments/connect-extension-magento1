<?php

use Ingenico\Connect\Sdk\Domain\Capture\CaptureResponse;
use Ingenico\Connect\Sdk\Domain\Definitions\AbstractOrderStatus;
use Ingenico\Connect\Sdk\Domain\Payment\Definitions\Payment;
use Ingenico\Connect\Sdk\Domain\Payment\PaymentResponse;
use Ingenico\Connect\Sdk\Domain\Refund\Definitions\RefundResult;
use Ingenico\Connect\Sdk\Domain\Refund\RefundResponse;
use Ingenico_Connect_Model_Ingenico_Status_ResolverInterface as ResolverInterface;
use Ingenico_Connect_Model_Ingenico_Status_PaymentStatusPool as PaymentStatusPool;
use Ingenico_Connect_Model_Ingenico_Status_RefundStatusPool as RefundStatusPool;
use Ingenico_Connect_Model_Order_Creditmemo_Service as Service;

/**
 * Class Ingenico_Connect_Model_Ingenico_Status_Resolver
 */
class Ingenico_Connect_Model_Ingenico_Status_Resolver implements ResolverInterface
{
    /**
     * @var RefundStatusPool
     */
    protected $refundHandlerPool;

    /**
     * @var PaymentStatusPool
     */
    protected $paymentHandlerPool;

    /**
     * @var Ingenico_Connect_Model_StatusResponseManager
     */
    protected $statusResponseManager;

    /**
     * @var Service
     */
    private $creditMemoService;

    /**
     * Ingenico_Connect_Model_Ingenico_Status_Resolver constructor.
     */
    public function __construct()
    {
        $this->refundHandlerPool = Mage::getModel('ingenico_connect/ingenico_status_refundStatusPool');
        $this->paymentHandlerPool = Mage::getModel('ingenico_connect/ingenico_status_paymentStatusPool');
        $this->statusResponseManager = Mage::getModel('ingenico_connect/statusResponseManager');
        $this->creditMemoService = Mage::getModel('ingenico_connect/order_creditmemo_service');
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param PaymentResponse|RefundResponse|AbstractOrderStatus $ingenicoStatus
     * @throws Exception
     */
    public function resolve(Mage_Sales_Model_Order $order, AbstractOrderStatus $ingenicoStatus)
    {
        $payment = $order->getPayment();
        if ($payment === null) {
            Mage::throwException(
                Mage::helper('ingenico_connect')
                    ->__('No payment object on order #%id', array('id' => $order->getIncrementId()))
            );
        }

        $existingStatus = $this->statusResponseManager->get($payment, $ingenicoStatus->id);
        $newStatusChangeDateTime = $ingenicoStatus->statusOutput->statusCodeChangeDateTime;

        if ($existingStatus
            && $existingStatus->statusOutput->statusCodeChangeDateTime >= $newStatusChangeDateTime) {
            // the already existing status information is newer or equal to the status that should be applied
            return;
        }

        $this->preparePayment($payment, $ingenicoStatus);

        $statusHandler = $this->getStatusHandler($ingenicoStatus);
        $transport = new Varien_Object(
            array(
                'status_handler' => $statusHandler,
                'ingenico_status' => $ingenicoStatus
            )
        );
        Mage::dispatchEvent(
            'ingenico_status_resolve_before', array(
                'transport' => $transport
            )
        );
        $statusHandler = $transport->getData('status_handler');
        $statusHandler->resolveStatus($order, $ingenicoStatus);
        $order->addStatusHistoryComment(
            sprintf(
                'Successfully processed notification about status %s with statusCode %s',
                $ingenicoStatus->status,
                $ingenicoStatus->statusOutput->statusCode
            )
        );
        $this->updatePayment($payment, $ingenicoStatus);

        if ($ingenicoStatus instanceof RefundResult) {
            $this->persistCreditMemoUpdate($order);
        }
    }

    /**
     * @param AbstractOrderStatus $ingenicoStatus
     * @return Ingenico_Connect_Model_Ingenico_Status_HandlerInterface
     * @throws Mage_Core_Exception
     */
    protected function getStatusHandler(AbstractOrderStatus $ingenicoStatus)
    {
        $handler = false;
        if ($ingenicoStatus instanceof Payment || $ingenicoStatus instanceof CaptureResponse) {
            $handler = $this->paymentHandlerPool->get($ingenicoStatus->status);
        } elseif ($ingenicoStatus instanceof RefundResult) {
            $handler = $this->refundHandlerPool->get($ingenicoStatus->status);
        }

        if (!$handler) {
            Mage::throwException(
                Mage::helper('ingenico_connect')
                    ->__(
                        'Could not find status resolver for response %class and status %status',
                        array(
                            'class' => get_class($ingenicoStatus),
                            'status' => $ingenicoStatus->status
                        )
                    )
            );
        }

        return $handler;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @throws Exception
     */
    protected function persistCreditMemoUpdate(Mage_Sales_Model_Order $order)
    {
        $payment = $order->getPayment();

        // Save everything involved in status application
        $creditmemo = $this->creditMemoService->getCreditmemo($payment);

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
     * @param string $type
     * @param string $status
     * @return Ingenico_Connect_Model_Ingenico_Status_HandlerInterface
     * @throws Exception
     */
    public function getHandlerByType($type, $status)
    {
        switch ($type) {
            case self::TYPE_CAPTURE:
            case self::TYPE_PAYMENT:
                return $this->paymentHandlerPool->get($status);
            case self::TYPE_REFUND:
                return $this->refundHandlerPool->get($status);
            default:
                throw new \InvalidArgumentException('Unkown type provided.');
        }
    }

    /**
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param AbstractOrderStatus $ingenicoStatus
     */
    protected function preparePayment(Mage_Sales_Model_Order_Payment $payment, AbstractOrderStatus $ingenicoStatus)
    {
        $payment->setTransactionId($ingenicoStatus->id);

        if (!$this->statusResponseManager->get($payment, $ingenicoStatus->id)) {
            $this->updatePayment($payment, $ingenicoStatus);
        }
    }

    /**
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param AbstractOrderStatus $ingenicoStatus
     */
    protected function updatePayment(Mage_Sales_Model_Order_Payment $payment, AbstractOrderStatus $ingenicoStatus)
    {
        $this->statusResponseManager->set(
            $payment,
            $ingenicoStatus->id,
            $ingenicoStatus
        );
    }
}
