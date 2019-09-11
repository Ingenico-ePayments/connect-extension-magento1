<?php

use Ingenico_Connect_Model_Method_HostedCheckout as HostedCheckout;
use Ingenico_Connect_Model_Ingenico_ActionInterface as ActionInterface;
use Ingenico\Connect\Sdk\Domain\Hostedcheckout\GetHostedCheckoutResponse;

/**
 * Class Ingenico_Connect_Model_Ingenico_HostedCheckoutPaymentStatus
 *
 * Uses to update Magento Order state/status after payment creation via HostedCheckout Payment method.
 *
 * @link https://developer.globalcollect.com/documentation/api/server/#__merchantId__hostedcheckouts__hostedCheckoutId__get
 */
class Ingenico_Connect_Model_Ingenico_GetHostedCheckoutStatus implements ActionInterface
{
    const PAYMENT_CREATED                    = 'PAYMENT_CREATED';
    const IN_PROGRESS                        = 'IN_PROGRESS';
    const RETURNMAC                          = 'RETURNMAC';
    const PAYMENT_STATUS_CATEGORY_SUCCESSFUL = 'SUCCESSFUL';
    const PAYMENT_STATUS_CATEGORY_UNKNOWN    = 'STATUS_UNKNOWN';
    const PAYMENT_STATUS_CATEGORY_REJECTED   = 'REJECTED';
    const PAYMENT_OUTPUT_SHOW_INSTRUCTIONS   = 'SHOW_INSTRUCTIONS';
    const PAYMENT_CANCELED_BY_CUSTOMER       = 'CANCELLED_BY_CONSUMER';

    /**
     * @var Ingenico_Connect_Model_Ingenico_MerchantReference
     */
    protected $merchantReference;

    /**
     * @var Ingenico_Connect_Model_TokenService
     */
    protected $tokenService;

    /**
     * @var Ingenico_Connect_Model_ConfigInterface
     */
    protected $ingenicoConfig;

    /**
     * @var Ingenico_Connect_Model_Ingenico_Api_ClientInterface
     */
    protected $ingenicoClient;

    /**
     * @var Ingenico_Connect_Model_Ingenico_Status_Resolver
     */
    protected $statusResolver;

    /**
     * @var Ingenico_Connect_Model_StatusResponseManager
     */
    protected $statusResponseManager;

    /**
     * @var Mage_Sales_Model_Order_Payment_Transaction
     */
    protected $paymentTransaction;

    /**
     * @var Ingenico_Connect_Helper_Data
     */
    protected $helper;


    /**
     * Ingenico_Connect_Model_Ingenico_Webhooks_PaymentEventDataResolver constructor.
     */
    public function __construct()
    {
        $this->merchantReference = Mage::getSingleton('ingenico_connect/ingenico_merchantReference');
        $this->tokenService = Mage::getSingleton('ingenico_connect/tokenService');
        $this->ingenicoConfig = Mage::getSingleton('ingenico_connect/config');
        $this->ingenicoClient = Mage::getSingleton('ingenico_connect/ingenico_client');
        $this->statusResolver = Mage::getSingleton('ingenico_connect/ingenico_status_resolver');
        $this->statusResponseManager = Mage::getSingleton('ingenico_connect/statusResponseManager');
        $this->paymentTransaction = Mage::getModel('sales/order_payment_transaction');
        $this->helper = Mage::helper('ingenico_connect');
    }

    /**
     * @param string $hostedCheckoutId
     * @throws Exception
     *
     * @return Mage_Sales_Model_Order
     */
    public function process($hostedCheckoutId)
    {
        $statusResponse = $this->getStatusResponse($hostedCheckoutId);
        $this->validateResponse($statusResponse);

        $incrementId = $this->merchantReference->extractOrderReference(
            $statusResponse->createdPaymentOutput->payment->paymentOutput->references->merchantReference
        );
        $order = $this->getOrderByIncrementId($incrementId);

        $this->checkPaymentStatusCategory($statusResponse, $order);

        if ($statusResponse->status === self::PAYMENT_CREATED) {
            $this->checkReturnMac($order);
            $token = $statusResponse->createdPaymentOutput->tokens;
            $this->processOrder($order, $statusResponse);
            $customerId = $order->getCustomerId();
            if ($customerId && $token !== null) {
                $this->processCustomerToken($customerId, $token);
            }

            // save the order before sendNewOrderEmail is called due to possible unexpected behaviour in CE1.8
            $order->save();

            $this->createTransactionIfNotExists($statusResponse, $order);

            try {
                $order->sendNewOrderEmail();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        } elseif ($statusResponse->status === self::PAYMENT_CANCELED_BY_CUSTOMER) {
            $order->registerCancellation('You canceled your payment');
            $order->save();
        } else {
            $order->save();
        }

        return $order;
    }

    /**
     * @param string $customerId
     * @param string $tokenString
     * @throws Exception
     */
    protected function processCustomerToken($customerId, $tokenString)
    {
        $this->tokenService->assignToken($customerId, $tokenString);
    }

    /**
     * @param string $hostedCheckoutId
     * @return GetHostedCheckoutResponse
     */
    protected function getStatusResponse($hostedCheckoutId)
    {
        /** @var GetHostedCheckoutResponse $statusResponse */
        $statusResponse = $this->ingenicoClient->getIngenicoClient()
            ->merchant($this->ingenicoConfig->getMerchantId())
            ->hostedcheckouts()
            ->get($hostedCheckoutId);

        return $statusResponse;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param GetHostedCheckoutResponse $statusResponse
     * @return Mage_Sales_Model_Order
     * @throws Mage_Core_Exception
     */
    protected function processOrder(
        Mage_Sales_Model_Order $order,
        GetHostedCheckoutResponse $statusResponse
    ) {
        $paymentId = $statusResponse->createdPaymentOutput->payment->id;
        $paymentStatus = $statusResponse->createdPaymentOutput->payment->status;
        $paymentStatusCode = $statusResponse->createdPaymentOutput->payment->statusOutput->statusCode;

        $payment = $order->getPayment();
        if (isset($statusResponse->createdPaymentOutput->displayedData)
            && $statusResponse->createdPaymentOutput->displayedData->displayedDataType
            === self::PAYMENT_OUTPUT_SHOW_INSTRUCTIONS
        ) {
            $payment->setAdditionalInformation(
                HostedCheckout::PAYMENT_SHOW_DATA_KEY,
                $statusResponse->createdPaymentOutput->displayedData->toJson()
            );
        }

        $this->statusResolver->resolve($order, $statusResponse->createdPaymentOutput->payment);

        $payment->setAdditionalInformation(HostedCheckout::PAYMENT_ID_KEY, $paymentId);
        $payment->setAdditionalInformation(HostedCheckout::PAYMENT_STATUS_KEY, $paymentStatus);
        $payment->setAdditionalInformation(HostedCheckout::PAYMENT_STATUS_CODE_KEY, $paymentStatusCode);

        return $order;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     *
     * @throws Exception
     */
    protected function checkReturnMac(Mage_Sales_Model_Order $order)
    {
        $orderReturnmac = $order->getPayment()->getAdditionalInformation(HostedCheckout::RETURNMAC_KEY);
        $returnmac = Mage::app()->getRequest()->get(self::RETURNMAC);

        if ($returnmac !== $orderReturnmac) {
            Mage::throwException($this->helper->__('RETURNMAC doesn\'t match.'));
        }
    }

    /**
     * Handles rejected or faulty orders by checking paymentStatusCategory, will escalate through exception
     *
     * @param GetHostedCheckoutResponse $statusResponse
     * @param Mage_Sales_Model_Order $order
     *
     * @throws Mage_Core_Exception if order is faulty or rejected by platform
     *
     */
    protected function checkPaymentStatusCategory(
        GetHostedCheckoutResponse $statusResponse,
        Mage_Sales_Model_Order $order
    ) {
        // handle faulty responses or rejected/cancelled orders
        $createdPaymentOutput = $statusResponse->createdPaymentOutput;
        if (!$createdPaymentOutput
            || $createdPaymentOutput->paymentStatusCategory === self::PAYMENT_STATUS_CATEGORY_REJECTED
        ) {
            if ($createdPaymentOutput) {
                $status = $createdPaymentOutput->payment->status;
            } else {
                $status = $statusResponse->status;
            }

            $info = $this->helper->getPaymentStatusInfo($status);
            if ($info) {
                $msg = $this->helper->__('Payment error:') . ' ' . $info;
            } else {
                $msg = $this->helper->__('Your payment was rejected or a technical error occured during processing.');
                $info = $msg;
            }

            $order->registerCancellation("<b>Payment error, status</b><br />{$statusResponse->status}: $info");
            $order->save();
            Mage::throwException($msg);
        }
    }

    /**
     * @param string $incrementId
     * @return Mage_Sales_Model_Order
     */
    protected function getOrderByIncrementId($incrementId)
    {
        /**
         * @var Mage_Sales_Model_Order $order
         */
        $order = Mage::getModel('sales/order');

        return $order->loadByIncrementId($incrementId);
    }

    /**
     * @param GetHostedCheckoutResponse $statusResponse
     * @throws Exception
     */
    protected function validateResponse($statusResponse)
    {
        if (!$statusResponse->createdPaymentOutput) {
            $msg = $this->helper->__('Your payment was rejected or a technical error occured during processing.');
            Mage::throwException($msg);
        }
    }

    /**
     * @param GetHostedCheckoutResponse $statusResponse
     * @param Mage_Sales_Model_Order $order
     * @throws Mage_Core_Exception
     */
    protected function createTransactionIfNotExists(
        GetHostedCheckoutResponse $statusResponse,
        Mage_Sales_Model_Order $order
    ) {
        $payment = $order->getPayment();
        $payId = $statusResponse->createdPaymentOutput->payment->id;

        /**
         * $payment->getTransaction($payId) is not reliable in this part of the request, therefore we manually
         * retrieve the transaction from the database.
         */
        $transCollection = $this->paymentTransaction->getCollection()
            ->addFieldToFilter('txn_id', array('eq' => $payId));
        $transCollection->setPageSize(1);
        $transaction = $transCollection->getItems();

        if (empty($transaction)) {
            $this->statusResponseManager->set($payment, $payId, $statusResponse->createdPaymentOutput->payment);
            $transaction = $payment->addTransaction(
                Mage_Sales_Model_Order_Payment_Transaction::TYPE_PAYMENT, $order, false
            );
            $transaction->save();
        }
    }
}
