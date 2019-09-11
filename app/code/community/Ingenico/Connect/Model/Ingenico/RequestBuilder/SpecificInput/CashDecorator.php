<?php

use Ingenico\Connect\Sdk\DataObject;
use Ingenico_Connect_Model_Method_HostedCheckout as HostedCheckout;
use Ingenico_Connect_Model_Ingenico_RequestBuilder_DecoratorInterface as DecoratorInterface;

/**
 * Class Ingenico_Connect_Model_Ingenico_RequestBuilder_SpecificInput_CashDecorator
 */
class Ingenico_Connect_Model_Ingenico_RequestBuilder_SpecificInput_CashDecorator implements DecoratorInterface
{
    /**
     * @inheritdoc
     */
    public function decorate(DataObject $request, Mage_Sales_Model_Order $order)
    {
        $input = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\CashPaymentMethodSpecificInput();
        $input->paymentProductId = $order->getPayment()->getAdditionalInformation(HostedCheckout::PRODUCT_ID_KEY);

        $request->cashPaymentMethodSpecificInput = $input;

        return $request;
    }
}
