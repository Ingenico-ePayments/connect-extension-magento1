<?php

use Netresearch_Epayments_Model_Ingenico_RequestBuilder_SpecificInput_AbstractMethodDecorator as
    AbstractMethodDecorator;

/**
 * Class Netresearch_Epayments_Model_Ingenico_RequestBuilder_SpecificInput_CashDecorator
 */
class Netresearch_Epayments_Model_Ingenico_RequestBuilder_SpecificInput_CashDecorator extends AbstractMethodDecorator
{
    /**
     * @inheritdoc
     */
    public function decorate($request, Mage_Sales_Model_Order $order)
    {
        $input = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\CashPaymentMethodSpecificInput();
        $input->paymentProductId = $this->getProductId($order);

        $request->cashPaymentMethodSpecificInput = $input;

        return $request;
    }
}
