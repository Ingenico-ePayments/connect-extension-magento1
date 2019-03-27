<?php

use Ingenico\Connect\Sdk\DataObject;
use Netresearch_Epayments_Model_Method_HostedCheckout as HostedCheckout;
use Netresearch_Epayments_Model_Ingenico_RequestBuilder_DecoratorInterface as DecoratorInterface;

/**
 * Class Netresearch_Epayments_Model_Ingenico_RequestBuilder_SpecificInput_BankTransferDecorator
 */
class Netresearch_Epayments_Model_Ingenico_RequestBuilder_SpecificInput_BankTransferDecorator
    implements DecoratorInterface
{
    /**
     * @inheritdoc
     */
    public function decorate(DataObject $request, Mage_Sales_Model_Order $order)
    {
        $input = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\BankTransferPaymentMethodSpecificInput();
        $input->paymentProductId = $order->getPayment()->getAdditionalInformation(HostedCheckout::PRODUCT_ID_KEY);

        $request->bankTransferPaymentMethodSpecificInput = $input;

        return $request;
    }
}
