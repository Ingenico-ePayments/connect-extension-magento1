<?php

use Netresearch_Epayments_Model_Ingenico_RequestBuilder_SpecificInput_AbstractMethodDecorator as
    AbstractMethodDecorator;
use Netresearch_Epayments_Model_Ingenico_RequestBuilder_AbstractRequestBuilder as AbstractRequestBuilder;
use Netresearch_Epayments_Model_Method_HostedCheckout as HostedCheckout;
/**
 * Class Netresearch_Epayments_Model_Ingenico_RequestBuilder_SpecificInput_RedirectDecorator
 */
class Netresearch_Epayments_Model_Ingenico_RequestBuilder_SpecificInput_RedirectDecorator extends
    AbstractMethodDecorator
{
    /**
     * @inheritdoc
     */
    public function decorate($request, Mage_Sales_Model_Order $order)
    {
        $input = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\RedirectPaymentMethodSpecificInput();
        $input->paymentProductId = $this->getProductId($order);
        $input->returnUrl = Mage::getUrl(AbstractRequestBuilder::HOSTED_CHECKOUT_RETURN_URL);

        $tokenize = $order->getPayment()->getAdditionalInformation(
            HostedCheckout::PRODUCT_TOKENIZE_KEY
        );
        $input->tokenize = $tokenize;

        // Retrieve capture mode from config
        $captureMode = Mage::getStoreConfig(
            Netresearch_Epayments_Model_Config::CONFIG_INGENICO_CAPTURES_MODE,
            Mage::app()->getStore()->getId()
        );
        $input->requiresApproval = (
            $captureMode === Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE
        );

        $request->redirectPaymentMethodSpecificInput = $input;

        return $request;
    }
}
