<?php
/**
 * Ingenico_Connect
 *
 * See LICENSE.txt for license details.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to
 * newer versions in the future.
 *
 * @category  Epayments
 * @package   Ingenico_Connect

 */

use Ingenico\Connect\Sdk\DataObject;
use Ingenico_Connect_Model_Method_HostedCheckout as HostedCheckout;
use Ingenico_Connect_Model_Ingenico_RequestBuilder_DecoratorInterface as DecoratorInterface;

/**
 * Class Ingenico_Connect_Model_Ingenico_RequestBuilder_SpecificInput_DirectDebitDecorator
 */
class Ingenico_Connect_Model_Ingenico_RequestBuilder_SpecificInput_DirectDebitDecorator implements
    DecoratorInterface
{
    /**
     * @inheritdoc
     */
    public function decorate(DataObject $request, Mage_Sales_Model_Order $order)
    {
        $input = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\NonSepaDirectDebitPaymentMethodSpecificInput();
        $input->paymentProductId = $order->getPayment()->getAdditionalInformation(HostedCheckout::PRODUCT_ID_KEY);
        $input->directDebitText = $order->getIncrementId();

        $tokenize = $order->getPayment()->getAdditionalInformation(
            HostedCheckout::PRODUCT_TOKENIZE_KEY
        );
        $input->tokenize = $tokenize;

        $request->directDebitPaymentMethodSpecificInput = $input;

        return $request;
    }
}
