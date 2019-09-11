<?php

use Ingenico\Connect\Sdk\DataObject;
use Mage_Payment_Model_Method_Abstract as AbstractMethod;
use Ingenico_Connect_Model_Method_HostedCheckout as HostedCheckout;
use Ingenico_Connect_Model_Ingenico_RequestBuilder_DecoratorInterface as DecoratorInterface;

/**
 * Class Ingenico_Connect_Model_Ingenico_RequestBuilder_SpecificInput_CardDecorator
 */
class Ingenico_Connect_Model_Ingenico_RequestBuilder_SpecificInput_CardDecorator implements DecoratorInterface
{
    const TRANSACTION_CHANNEL = 'ECOMMERCE';
    const UNSCHEDULED_CARD_ON_FILE_SEQUENCE_INDICATOR_FIRST = 'first';
    const UNSCHEDULED_CARD_ON_FILE_SEQUENCE_INDICATOR_SUBSEQUENT = 'subsequent';
    const UNSCHEDULED_CARD_ON_FILE_REQUESTOR_CARDHOLDER_INITIATED = 'cardholderInitiated';

    /**
     * @var Ingenico_Connect_Model_Ingenico_RequestBuilder_SpecificInput_Card_ThreeDSecureBuilder
     */
    protected $threeDSecureBuilder;

    /**
     * Ingenico_Connect_Model_Ingenico_RequestBuilder_SpecificInput_CardDecorator constructor.
     */
    public function __construct()
    {
        $this->threeDSecureBuilder = Mage::getSingleton('ingenico_connect/ingenico_requestBuilder_specificInput_card_threeDSecureBuilder');
    }

    /**
     * @inheritdoc
     */
    public function decorate(DataObject $request, Mage_Sales_Model_Order $order)
    {
        $input = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\CardPaymentMethodSpecificInput();
        $input->threeDSecure = $this->threeDSecureBuilder->create($order);
        $input->transactionChannel = self::TRANSACTION_CHANNEL;
        $input->paymentProductId = $order->getPayment()->getAdditionalInformation(HostedCheckout::PRODUCT_ID_KEY);

        // Retrieve capture mode from config
        $captureMode = Mage::getStoreConfig(
            Ingenico_Connect_Model_Config::CONFIG_INGENICO_CAPTURES_MODE,
            Mage::app()->getStore()->getId()
        );
        $input->requiresApproval = (
            $captureMode === AbstractMethod::ACTION_AUTHORIZE
        );

        if (!$order->getCustomerIsGuest() && $order->getCustomerId()) {
            $tokenizeValue = $order->getPayment()->getAdditionalInformation(HostedCheckout::PRODUCT_TOKENIZE_KEY);
            $input->tokenize = filter_var($tokenizeValue, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        } else {
            $input->tokenize = false;
        }
        try {
            $input->unscheduledCardOnFileSequenceIndicator = $this->getUnscheduledCardOnFileSequenceIndicator($order);
        } catch (Exception $e) {
            //Do nothing
        }
        $input->unscheduledCardOnFileRequestor =
            $this->getUnscheduledCardOnFileRequestor($input->unscheduledCardOnFileSequenceIndicator);

        $request->cardPaymentMethodSpecificInput = $input;

        return $request;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return string|null
     * @throws Exception
     */
    private function getUnscheduledCardOnFileSequenceIndicator(Mage_Sales_Model_Order $order)
    {
        $payment = $order->getPayment();
        if ($payment === null) {
            Mage::throwException('No payment available for this order');
        }

        if (filter_var(
            $payment->getAdditionalInformation(HostedCheckout::PRODUCT_TOKENIZE_KEY),
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE
        )) {
            return self::UNSCHEDULED_CARD_ON_FILE_SEQUENCE_INDICATOR_FIRST;
        }

        if (filter_var(
            $payment->getAdditionalInformation(HostedCheckout::ACCOUNT_ON_FILE_KEY),
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE
        )) {
            return self::UNSCHEDULED_CARD_ON_FILE_SEQUENCE_INDICATOR_SUBSEQUENT;
        }
        return null;
    }

    /**
     * @param string|null $unscheduledCardOnFileSequenceIndicator
     * @return string|null
     */
    private function getUnscheduledCardOnFileRequestor($unscheduledCardOnFileSequenceIndicator)
    {
        if ($unscheduledCardOnFileSequenceIndicator === self::UNSCHEDULED_CARD_ON_FILE_SEQUENCE_INDICATOR_FIRST ||
            $unscheduledCardOnFileSequenceIndicator === self::UNSCHEDULED_CARD_ON_FILE_SEQUENCE_INDICATOR_SUBSEQUENT
        ) {
            return self::UNSCHEDULED_CARD_ON_FILE_REQUESTOR_CARDHOLDER_INITIATED;
        }
        return null;
    }
}
