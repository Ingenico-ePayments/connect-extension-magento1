<?php

use Ingenico\Connect\Sdk\DataObject;
use Ingenico\Connect\Sdk\Domain\Hostedcheckout\CreateHostedCheckoutRequest;
use Ingenico\Connect\Sdk\Domain\Payment\CreatePaymentRequest;

/**
 * Interface Netresearch_Epayments_Model_Ingenico_RequestBuilder_DecoratorInterface
 */
interface Netresearch_Epayments_Model_Ingenico_RequestBuilder_DecoratorInterface
{
    /**
     * @param CreateHostedCheckoutRequest|CreatePaymentRequest|DataObject $request
     * @param Mage_Sales_Model_Order $order
     * @return CreateHostedCheckoutRequest|CreatePaymentRequest - updated Request
     */
    public function decorate(DataObject $request, Mage_Sales_Model_Order $order);
}
