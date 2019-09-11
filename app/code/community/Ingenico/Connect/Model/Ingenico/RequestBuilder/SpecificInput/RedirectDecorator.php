<?php

use Ingenico\Connect\Sdk\DataObject;
use Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_RequestBuilder as RequestBuilder;
use Ingenico_Connect_Model_Ingenico_RequestBuilder_DecoratorInterface as DecoratorInterface;
use Ingenico_Connect_Model_Method_HostedCheckout as HostedCheckout;

/**
 * Class Ingenico_Connect_Model_Ingenico_RequestBuilder_SpecificInput_RedirectDecorator
 */
class Ingenico_Connect_Model_Ingenico_RequestBuilder_SpecificInput_RedirectDecorator implements DecoratorInterface
{
    /**
     * @inheritdoc
     */
    public function decorate(DataObject $request, Mage_Sales_Model_Order $order)
    {
        $input = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\RedirectPaymentMethodSpecificInput();
        $input->paymentProductId = $order->getPayment()->getAdditionalInformation(HostedCheckout::PRODUCT_ID_KEY);

        if ($order->getPayment()->getAdditionalInformation(HostedCheckout::CLIENT_PAYLOAD_KEY)) {
            $input->returnUrl = Mage::getUrl(RequestBuilder::REDIRECT_PAYMENT_RETURN_URL);
        } else {
            $input->returnUrl = Mage::getUrl(RequestBuilder::HOSTED_CHECKOUT_RETURN_URL);
        }

        $tokenize = $order->getPayment()->getAdditionalInformation(
            HostedCheckout::PRODUCT_TOKENIZE_KEY
        );
        $input->tokenize = $tokenize;

        // Retrieve capture mode from config
        $captureMode = Mage::getStoreConfig(
            Ingenico_Connect_Model_Config::CONFIG_INGENICO_CAPTURES_MODE,
            Mage::app()->getStore()->getId()
        );
        $input->requiresApproval = ($captureMode === Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE);

        $request->redirectPaymentMethodSpecificInput = $input;

        return $request;
    }
}
