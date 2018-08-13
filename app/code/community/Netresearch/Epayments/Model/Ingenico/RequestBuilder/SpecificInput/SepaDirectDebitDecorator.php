<?php

use Netresearch_Epayments_Model_Ingenico_RequestBuilder_SpecificInput_AbstractMethodDecorator as
    AbstractMethodDecorator;
use Netresearch_Epayments_Model_Method_HostedCheckout as HostedCheckout;
/**
 * Class Netresearch_Epayments_Model_Ingenico_RequestBuilder_SpecificInput_SepaDirectDebitDecorator
 */
class Netresearch_Epayments_Model_Ingenico_RequestBuilder_SpecificInput_SepaDirectDebitDecorator extends
    AbstractMethodDecorator
{
    /**
     * @inheritdoc
     */
    public function decorate($request, Mage_Sales_Model_Order $order)
    {
        $input = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\SepaDirectDebitPaymentMethodSpecificInput();
        $input->paymentProductId = $this->getProductId($order);

        $tokenize = $order->getPayment()->getAdditionalInformation(
            HostedCheckout::PRODUCT_TOKENIZE_KEY
        );
        $input->tokenize = $tokenize;

        $request->sepaDirectDebitPaymentMethodSpecificInput = $input;

        return $request;
    }
}
