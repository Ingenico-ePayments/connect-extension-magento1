<?php

use Netresearch_Epayments_Model_Ingenico_AbstractAction as AbstractAction;
use Netresearch_Epayments_Model_Ingenico_ActionInterface as ActionInterface;
use Netresearch_Epayments_Model_Method_HostedCheckout as HostedCheckout;

/**
 * Class Netresearch_Epayments_Model_Ingenico_CreateRefund
 *
 * @link https://developer.globalcollect.com/documentation/api/server/#__merchantId__payments__paymentId__refund_post
 */
class Netresearch_Epayments_Model_Ingenico_CreateRefund extends AbstractAction implements ActionInterface
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
     * @var Netresearch_Epayments_Model_Ingenico_Status_ResolverInterface
     */
    protected $statusResolver;

    /**
     * @var Netresearch_Epayments_Model_Ingenico_MerchantReference
     */
    protected $merchantReference;

    /**
     * Netresearch_Epayments_Model_Ingenico_CreateHostedCheckout constructor.
     */
    public function __construct()
    {
        $this->ingenicoClient = Mage::getSingleton('netresearch_epayments/ingenico_client');
        $this->ePaymentsConfig = Mage::getSingleton('netresearch_epayments/config');
        $this->statusResolver = Mage::getSingleton('netresearch_epayments/ingenico_status_resolver');
        $this->merchantReference = Mage::getSingleton('netresearch_epayments/ingenico_merchantReference');

        parent::__construct();
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param float $amount
     * @throws Exception
     */
    public function process(Mage_Sales_Model_Order $order, $amount)
    {
        $payment = $order->getPayment();
        /** @var Mage_Sales_Model_Order $order */
        $order = $payment->getOrder();

        $request = new \Ingenico\Connect\Sdk\Domain\Refund\RefundRequest();
        $refundReferences = new \Ingenico\Connect\Sdk\Domain\Refund\Definitions\RefundReferences();
        $refundReferences->merchantReference = $this->merchantReference->generateMerchantReference($order);
        $request->refundReferences = $refundReferences;

        $request->amountOfMoney = $this->_getAmountOfMoney($amount, $order->getBaseCurrencyCode());

        $refundCustomer = new \Ingenico\Connect\Sdk\Domain\Refund\Definitions\RefundCustomer();
        $addressPersonal = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\AddressPersonal();

        $billing = $order->getBillingAddress();
        if (!empty($billing)) {
            $addressPersonal->countryCode = $billing->getCountry();
        }

        $personalName = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\PersonalName();
        $personalName->surname = $order->getCustomerLastname();
        $addressPersonal->name = $personalName;

        $refundCustomer->address = $addressPersonal;

        $contactDetailsBase = new \Ingenico\Connect\Sdk\Domain\Definitions\ContactDetailsBase();
        $contactDetailsBase->emailAddress = $order->getCustomerEmail();
        $contactDetailsBase->emailMessageType = Netresearch_Epayments_Helper_Data::EMAIL_MESSAGE_TYPE;
        $refundCustomer->contactDetails = $contactDetailsBase;

        $request->customer = $refundCustomer;
        $request->refundDate = Mage::getSingleton('core/date')->date('Ymd');
        $callContext = new \Ingenico\Connect\Sdk\CallContext();

        $response = $this->ingenicoClient
            ->getIngenicoClient($order->getStoreId())
            ->merchant($this->ePaymentsConfig->getMerchantId($order->getStoreId()))
            ->payments()
            ->refund(
                $payment->getAdditionalInformation(HostedCheckout::PAYMENT_ID_KEY),
                $request,
                $callContext
            );

        $payment->setRefundResponse($response);

        /**
         * @var Netresearch_Epayments_Model_Ingenico_RefundHandlerInterface $handler
         */
        $handler = $this->statusResolver
            ->getHandlerByType(
                Netresearch_Epayments_Model_Ingenico_Status_ResolverInterface::TYPE_REFUND,
                $response->status
            );
        $handler->applyCreditmemo($payment->getCreditmemo());

        $payment->setLastTransId($response->id);

        $this->postProcess($payment, $response);
    }

    /**
     * @param float $amount
     * @param string $currencyCode
     * @return \Ingenico\Connect\Sdk\Domain\Definitions\AmountOfMoney
     */
    protected function _getAmountOfMoney($amount, $currencyCode)
    {
        $amountOfMoney = new \Ingenico\Connect\Sdk\Domain\Definitions\AmountOfMoney();
        $amountOfMoney->amount = Mage::helper('netresearch_epayments')->formatIngenicoAmount($amount);
        $amountOfMoney->currencyCode = $currencyCode;

        return $amountOfMoney;
    }
}
