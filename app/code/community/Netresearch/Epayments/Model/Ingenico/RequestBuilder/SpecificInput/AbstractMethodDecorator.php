<?php

use Netresearch_Epayments_Model_Method_HostedCheckout as HostedCheckout;
use \Ingenico\Connect\Sdk\Domain\Hostedcheckout\CreateHostedCheckoutRequest;
use \Ingenico\Connect\Sdk\Domain\Payment\CreatePaymentRequest;

/**
 * Class Netresearch_Epayments_Model_Ingenico_RequestBuilder_SpecificInput_AbstractMethodDecorator
 */
abstract class Netresearch_Epayments_Model_Ingenico_RequestBuilder_SpecificInput_AbstractMethodDecorator
{
    /**
     * @param CreateHostedCheckoutRequest|CreatePaymentRequest $request
     * @param Mage_Sales_Model_Order $order
     * @return CreateHostedCheckoutRequest|CreatePaymentRequest
     */
    abstract public function decorate($request, Mage_Sales_Model_Order $order);

    /**
     * @param Mage_Sales_Model_Order $order
     * @return string|null
     */
    protected function getProductId(Mage_Sales_Model_Order $order)
    {
        return $order->getPayment()->getAdditionalInformation(HostedCheckout::PRODUCT_ID_KEY);
    }
}
