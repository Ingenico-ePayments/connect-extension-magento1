<?php

use Ingenico_Connect_Model_Config as EpaymentsConfig;
use Ingenico_Connect_Model_Ingenico_StatusInterface as StatusInterface;

/**
 * Class Ingenico_Connect_Model_Method_HostedCheckout
 */
class Ingenico_Connect_Model_Method_HostedCheckout extends Mage_Payment_Model_Method_Abstract
{
    const TRANSACTION_INFO_KEY = 'gc_response_object';
    const TRANSACTION_CLASS_KEY = 'gc_response_class';

    const PAYMENT_ID_KEY = 'gc_payment_id';
    const PAYMENT_STATUS_KEY = 'gc_payment_status';
    const PAYMENT_STATUS_CODE_KEY = 'gc_payment_status_code';
    const PAYMENT_SHOW_DATA_KEY = 'gc_payment_show_data';

    const PRODUCT_ID_KEY = 'gc_payment_product_id';
    const PRODUCT_LABEL_KEY = 'gc_payment_product_label';
    const PRODUCT_PAYMENT_METHOD_KEY = 'gc_payment_product_method';
    const PRODUCT_TOKENIZE_KEY = 'gc_payment_product_tokenize';
    const ACCOUNT_ON_FILE_KEY = 'gc_payment_account_on_file';
    const CLIENT_PAYLOAD_KEY = 'gc_payment_client_payload';

    const REDIRECT_URL_KEY = 'gc_redirect_url';
    const HOSTED_CHECKOUT_ID_KEY = 'gc_hosted_checkout_id';
    const RETURNMAC_KEY = 'gc_returnmac';
    const IDEMPOTENCE_KEY = 'gc_idempotence_key';

    /**
     * @var string
     */
    protected $_code = 'hosted_checkout';

    /**
     * @var string
     */
    protected $_formBlockType = 'ingenico_connect/form_checkoutMethods';

    /**
     * @var string
     */
    protected $_infoBlockType = 'ingenico_connect/info_hostedCheckout';

    /**
     * @var bool
     */
    protected $_canVoid = true;

    /**
     * @var bool
     */
    protected $_canRefund = true;

    /**
     * @var bool
     */
    protected $_canRefundInvoicePartial = true;

    /**
     * @var bool
     */
    protected $_canCapture = true;

    /**
     * @var bool
     */
    protected $_canCapturePartial = false;

    /**
     * @var bool
     */
    protected $_canUseInternal = false;

    /**
     * @var bool
     */
    protected $_canUseForMultishipping = false;

    /**
     * @var bool
     */
    protected $_isInitializeNeeded = true;

    /**
     * @var Ingenico_Connect_Model_Ingenico_Api_ClientInterface
     */
    protected $ingenicoClient;

    /**
     * @var EpaymentsConfig
     */
    protected $ingenicoConfig;

    /**
     * @var Ingenico_Connect_Model_Ingenico_CreateHostedCheckout
     */
    protected $ingenicoCreateHostedCheckout;

    /**
     * @var Ingenico_Connect_Model_Ingenico_CreatePayment
     */
    protected $ingenicoCreatePayment;

    /**
     * @var Ingenico_Connect_Model_StatusResponseManager
     */
    protected $statusResponseManager;

    /**
     * @var Ingenico_Connect_Model_Ingenico_Status_ResolverInterface
     */
    protected $statusResolver;

    /**
     * Ingenico_Connect_Model_Method_HostedCheckout constructor.
     */
    public function __construct()
    {
        $this->ingenicoClient = Mage::getSingleton('ingenico_connect/ingenico_client');
        $this->ingenicoConfig = Mage::getSingleton('ingenico_connect/config');
        $this->ingenicoCreateHostedCheckout = Mage::getSingleton('ingenico_connect/ingenico_createHostedCheckout');
        $this->ingenicoCreatePayment = Mage::getSingleton('ingenico_connect/ingenico_createPayment');
        $this->statusResponseManager = Mage::getModel('ingenico_connect/statusResponseManager');
        $this->statusResolver = Mage::getModel('ingenico_connect/ingenico_status_resolver');

        parent::__construct();
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->ingenicoConfig->getTitle();
    }

    /**
     * @param Mage_Sales_Model_Quote|null $quote
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        $storeId = $quote ? $quote->getStoreId() : null;
        $isVerified = $this->ingenicoConfig->isAccountVerified($storeId);

        return parent::isAvailable($quote) &&
               Mage::getStoreConfig('ingenico_epayments/settings/active', $storeId) &&
               $isVerified;
    }

    /**
     * Store received data from checkout on the payment object.
     *
     * @param array|Varien_Object $data
     * @return $this|Mage_Payment_Model_Info
     * @throws Mage_Core_Exception
     */
    public function assignData($data)
    {
        parent::assignData($data);

        if (is_array($data)) {
            $data = new Varien_Object($data);
        }

        /** @var Mage_Sales_Model_Quote_Payment $info */
        $info = $this->getInfoInstance();

        if ($this->ingenicoConfig->isFullRedirect($info->getQuote()->getStoreId())) {
            return $this;
        }

        $productId = $data->getData(self::PRODUCT_ID_KEY);
        $productLabel = $data->getData(self::PRODUCT_LABEL_KEY);
        $paymentMethod = $data->getData(self::PRODUCT_PAYMENT_METHOD_KEY);
        $tokenize = $data->getData(self::PRODUCT_TOKENIZE_KEY);
        if ($tokenize === null) {
            $tokenize = true;
        }
        $accountOnFile = $data->getData(self::ACCOUNT_ON_FILE_KEY);

        if ($productId === null || $productLabel === null || $paymentMethod === null) {
            Mage::throwException(Mage::helper('ingenico_connect')->__('Please select a payment method.'));
        }

        $info->setAdditionalInformation(self::PRODUCT_ID_KEY, $productId);
        $info->setAdditionalInformation(self::PRODUCT_LABEL_KEY, $productLabel);
        $info->setAdditionalInformation(self::PRODUCT_PAYMENT_METHOD_KEY, $paymentMethod);
        $info->setAdditionalInformation(self::PRODUCT_TOKENIZE_KEY, $tokenize);
        $info->setAdditionalInformation(self::ACCOUNT_ON_FILE_KEY, $accountOnFile);
        /** Store and unset encrypted client payload */
        if ($payload = $data->getData(self::CLIENT_PAYLOAD_KEY)) {
            $info->setAdditionalInformation(self::CLIENT_PAYLOAD_KEY, $payload);
            $data->unsetData(self::CLIENT_PAYLOAD_KEY);
        }

        return $this;
    }

    /**
     * @param string $paymentAction
     * @param object $stateObject
     * @return $this
     * @throws Mage_Core_Exception
     */
    public function initialize($paymentAction, $stateObject)
    {
        parent::initialize($paymentAction, $stateObject);

        /** @var Mage_Sales_Model_Order_Payment $info */
        $info = $this->getInfoInstance();
        $order = $info->getOrder();
        $this->ingenicoCreateHostedCheckout->create($order);

        $stateObject->setState(Mage_Sales_Model_Order::STATE_NEW);
        $stateObject->setStatus('pending');
        $stateObject->setIsNotified(false);

        return $this;
    }

    /**
     * @param Varien_Object|Mage_Sales_Model_Order_Payment|Mage_Sales_Model_Order_Creditmemo $payment
     * @param float $amount
     * @return $this
     * @throws Exception
     */
    public function capture(Varien_Object $payment, $amount)
    {
        parent::capture($payment, $amount);

        if (!$payment->getAuthorizationTransaction()) {
            // createPayment
            $this->ingenicoCreatePayment->create($payment->getOrder());
            $payment->setAdditionalInformation(self::CLIENT_PAYLOAD_KEY, null);

            return $this;
        }

        /** @var Ingenico_Connect_Model_Ingenico_CapturePayment $capturePayment */
        $capturePayment = Mage::getSingleton('ingenico_connect/ingenico_capturePayment');
        $capturePayment->process($payment->getOrder(), $amount);

        return $this;
    }

    /**
     * @param Varien_Object|Mage_Sales_Model_Order_Payment $payment
     * @param float $amount
     * @return $this
     * @throws Exception
     */
    public function refund(Varien_Object $payment, $amount)
    {
        parent::refund($payment, $amount);

        /** @var Ingenico_Connect_Model_Ingenico_CreateRefund $ingenicoCreateRefund */
        $ingenicoCreateRefund = Mage::getSingleton('ingenico_connect/ingenico_createRefund');
        $ingenicoCreateRefund->process($payment->getOrder(), $amount);

        return $this;
    }

    /**
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return Mage_Payment_Model_Method_Abstract
     */
    public function processCreditmemo($creditmemo, $payment)
    {
        /** @var \Ingenico\Connect\Sdk\Domain\Refund\RefundResponse $refundResponse */
        $statusResponse = $payment->getRefundResponse();
        $order = $creditmemo->getOrder();
        $this->statusResolver->resolve($order, $statusResponse);

        return parent::processCreditmemo($creditmemo, $payment);
    }

    /**
     * @param Varien_Object|Mage_Sales_Model_Order_Payment|Mage_Sales_Model_Order_Creditmemo $payment
     * @return $this
     * @throws Exception
     */
    public function cancel(Varien_Object $payment)
    {
        parent::cancel($payment);

        /** @var Ingenico_Connect_Model_Ingenico_CancelPayment $cancelPayment */
        $cancelPayment = Mage::getSingleton('ingenico_connect/ingenico_cancelPayment');
        $cancelPayment->process($payment->getOrder());

        return $this;
    }

    /**
     * @param Varien_Object|Mage_Sales_Model_Order_Payment|Mage_Sales_Model_Order_Creditmemo $payment
     * @return $this
     * @throws Exception
     */
    public function void(Varien_Object $payment)
    {
        parent::void($payment);

        if ($invoice = Mage::registry('current_invoice')) {
            // @TODO determine if this is correctly handled
            /** @var Ingenico_Connect_Model_Ingenico_UndoCapturePaymentRequest $undoCaptureRequest */
            $undoCaptureRequest = Mage::getSingleton(
                'ingenico_connect/ingenico_undoCapturePaymentRequest'
            );
            $undoCaptureRequest->process($payment->getOrder());
        } else {
            /** @var Ingenico_Connect_Model_Ingenico_CancelPayment $cancelPaymentRequest */
            $cancelPaymentRequest = Mage::getSingleton('ingenico_connect/ingenico_cancelPayment');
            $cancelPaymentRequest->process($payment->getOrder());
        }

        return $this;
    }

    /**
     * This method is never directly called by the Magento core. Instead, it is called via an observer.
     *
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     * @return $this
     * @throws Mage_Core_Exception
     */
    public function cancelCreditmemo(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        /** @var Ingenico_Connect_Model_Ingenico_CancelRefund $cancelRefundRequest */
        $cancelRefundRequest = Mage::getSingleton('ingenico_connect/ingenico_cancelRefund');
        $cancelRefundRequest->process($creditmemo);

        return $this;
    }

    /**
     * Return Order place redirect url
     *
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        $info = $this->getInfoInstance();

        return $info->getAdditionalInformation(self::REDIRECT_URL_KEY);
    }

    /**
     * @inheritdoc
     * @return bool
     */
    public function canRefund()
    {
        $result = parent::canRefund();
        /** @var Mage_Sales_Model_Order_Payment|Mage_Payment_Model_Info $payment */
        $payment = $this->getInfoInstance();
        /** @var Mage_Sales_Model_Order_Creditmemo $creditmemo */
        $creditmemo = Mage::registry('current_creditmemo');
        if ($creditmemo && $transactionId = $creditmemo->getTransactionId()) {
            /** @var \Ingenico\Connect\Sdk\Domain\Refund\Definitions\RefundResult $refundResponse */
            $refundResponse = $this->statusResponseManager->get($payment, $transactionId);
            $result = $refundResponse->status == StatusInterface::PENDING_APPROVAL;
        } elseif ($paymentResponse = $this->statusResponseManager->get(
            $payment,
            $payment->getAdditionalInformation(self::PAYMENT_ID_KEY)
        )) {
            $result = $paymentResponse->statusOutput->isRefundable;
            // @FIXME: This is a workaround for the Ogone endpoint until the ingenico api is fixed!
            if (!$result &&
                $creditmemo &&
                $creditmemo->getInvoice() &&
                $invoiceId = $creditmemo->getInvoice()->getTransactionId()
            ) {
                $paymentResponse = $this->statusResponseManager->get($payment, $invoiceId);
                $result = in_array($paymentResponse->statusOutput->statusCode, array(9, 95), true);
            }
        }

        return $result;
    }

    /**
     * @param Varien_Object $document
     * @return bool
     */
    public function canVoid(Varien_Object $document)
    {
        $result = parent::canVoid($document);

        // $document can be any of CreditMemo, Invoice, Payment
        if ($document instanceof Mage_Sales_Model_Order_Creditmemo) {
            $transactionId = $document->getTransactionId();
        } else {
            $transactionId = $this->getInfoInstance()->getAdditionalInformation(self::PAYMENT_ID_KEY);
        }

        $actionResponseObject = $this->statusResponseManager->get($this->getInfoInstance(), $transactionId);
        if ($actionResponseObject) {
            $result = $actionResponseObject->statusOutput->isCancellable;
            $document->setParentTransactionId($actionResponseObject->id);
        }

        return $result;
    }

    /**
     * @inheritdoc
     * @return bool
     */
    public function canCapture()
    {
        $result = parent::canCapture();
        /** @var Mage_Sales_Model_Order_Payment|Mage_Payment_Model_Info $payment */
        $payment = $this->getInfoInstance();
        $paymentResponse = $this->statusResponseManager->get(
            $payment,
            $payment->getAdditionalInformation(self::PAYMENT_ID_KEY)
        );
        if ($paymentResponse) {
            $result = $paymentResponse->statusOutput->isAuthorized;
        }

        return $result;
    }

    /**
     * @inheritdoc
     * @return bool
     */
    public function canCapturePartial()
    {
        $result = parent::canCapturePartial();

        $payment = $this->getInfoInstance();
        $paymentResponse = $this->statusResponseManager->get(
            $payment,
            $payment->getAdditionalInformation(self::PAYMENT_ID_KEY)
        );
        if ($paymentResponse) {
            $result = $paymentResponse->status === StatusInterface::PENDING_CAPTURE;
        }

        return $result;
    }

    /**
     * @param Mage_Payment_Model_Info $payment
     * @return bool
     */
    public function canReviewPayment(Mage_Payment_Model_Info $payment)
    {

        $transactionId = $payment->getAdditionalInformation(self::PAYMENT_ID_KEY);
        $responseObject = $this->statusResponseManager->get($payment, $transactionId);

        return $responseObject->status === StatusInterface::PENDING_FRAUD_APPROVAL;
    }

    /**
     * @param Mage_Payment_Model_Info|Mage_Sales_Model_Order_Payment $payment
     * @return bool
     * @throws Mage_Core_Exception
     */
    public function acceptPayment(Mage_Payment_Model_Info $payment)
    {
        parent::acceptPayment($payment);

        /** @var Ingenico_Connect_Model_Ingenico_ApproveChallengedPayment $action */
        $action = Mage::getModel('ingenico_connect/ingenico_approveChallengedPayment');
        $action->process($payment->getOrder());

        return true;
    }

    /**
     * @param Mage_Payment_Model_Info|Mage_Sales_Model_Order_Payment $payment
     * @return bool
     * @throws Mage_Core_Exception
     */
    public function denyPayment(Mage_Payment_Model_Info $payment)
    {
        parent::denyPayment($payment);

        /** @var Ingenico_Connect_Model_Ingenico_CancelPayment $action */
        $action = Mage::getModel('ingenico_connect/ingenico_cancelPayment');
        try {
            $action->process($payment->getOrder());
        } catch (\Exception $e) {
            Mage::throwException(Mage::helper('payment')->__('The deny payment action is unavailable.'));
        }

        return true;
    }

    /**
     * @return bool
     */
    public function isInitializeNeeded()
    {
        $shouldInitialize = true;
        /** @var Mage_Sales_Model_Quote_Payment $info */
        $info = $this->getInfoInstance();
        $isInline = $this->ingenicoConfig->getCheckoutType($info->getOrder()->getStoreId()) ===
                    Ingenico_Connect_Model_Config::CONFIG_INGENICO_CHECKOUT_TYPE_INLINE;
        if ($isInline) {
            $payment = $info->getOrder()->getPayment();
            $shouldInitialize = !$payment->getAdditionalInformation(self::CLIENT_PAYLOAD_KEY);
        }

        return $shouldInitialize;
    }

    /**
     * @return mixed|string
     */
    public function getConfigPaymentAction()
    {
        $info = $this->getInfoInstance();

        return $this->ingenicoConfig->getCaptureMode($info->getOrder()->getStoreId());
    }

    /**
     * @param Varien_Object|Mage_Sales_Model_Order_Payment $payment
     * @param float $amount
     * @return $this|Mage_Payment_Model_Abstract
     * @throws Mage_Payment_Model_Info_Exception
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        $this->ingenicoCreatePayment->create($payment->getOrder());
        $payment->setAdditonalInformation(self::CLIENT_PAYLOAD_KEY, null);

        return $this;
    }
}
