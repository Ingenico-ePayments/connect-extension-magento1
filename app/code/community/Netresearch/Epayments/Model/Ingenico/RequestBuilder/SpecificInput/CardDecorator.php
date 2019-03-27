<?php

use Ingenico\Connect\Sdk\DataObject;
use Mage_Payment_Model_Method_Abstract as AbstractMethod;
use Netresearch_Epayments_Model_Ingenico_RequestBuilder_Common_RequestBuilder as RequestBuilder;
use Netresearch_Epayments_Model_Method_HostedCheckout as HostedCheckout;
use Netresearch_Epayments_Model_Ingenico_RequestBuilder_DecoratorInterface as DecoratorInterface;

/**
 * Class Netresearch_Epayments_Model_Ingenico_RequestBuilder_SpecificInput_CardDecorator
 */
class Netresearch_Epayments_Model_Ingenico_RequestBuilder_SpecificInput_CardDecorator implements DecoratorInterface
{
    /**
     * @inheritdoc
     */
    public function decorate(DataObject $request, Mage_Sales_Model_Order $order)
    {
        $input = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\CardPaymentMethodSpecificInput();
        $input->paymentProductId = $order->getPayment()->getAdditionalInformation(HostedCheckout::PRODUCT_ID_KEY);
        if ($order->getPayment()->getAdditionalInformation(HostedCheckout::CLIENT_PAYLOAD_KEY)) {
            $input->returnUrl = Mage::getUrl(RequestBuilder::REDIRECT_PAYMENT_RETURN_URL);
        } else {
            $input->returnUrl = Mage::getUrl(RequestBuilder::HOSTED_CHECKOUT_RETURN_URL);
        }

        // Retrieve capture mode from config
        $captureMode = Mage::getStoreConfig(
            Netresearch_Epayments_Model_Config::CONFIG_INGENICO_CAPTURES_MODE,
            Mage::app()->getStore()->getId()
        );
        $input->requiresApproval = (
            $captureMode === AbstractMethod::ACTION_AUTHORIZE
        );

        $tokenize = $order->getPayment()->getAdditionalInformation(
            HostedCheckout::PRODUCT_TOKENIZE_KEY
        );
        $input->tokenize = $tokenize;
        $input->transactionChannel = 'ECOMMERCE';

        // Skip auth for recurring payments
        if ($input->isRecurring && $input->recurringPaymentSequenceIndicator === 'recurring') {
            $input->skipAuthentication = true;
        } else {
            $input->skipAuthentication = false;
        }

        $request->cardPaymentMethodSpecificInput = $input;

        return $request;
    }
}
