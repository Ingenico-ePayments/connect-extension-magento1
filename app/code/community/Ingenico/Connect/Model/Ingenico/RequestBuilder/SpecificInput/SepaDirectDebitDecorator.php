<?php

use Ingenico\Connect\Sdk\DataObject;
use Ingenico\Connect\Sdk\Domain\Mandates\Definitions\CreateMandateWithReturnUrl;
use Ingenico\Connect\Sdk\Domain\Payment\Definitions\SepaDirectDebitPaymentMethodSpecificInput;
use Ingenico\Connect\Sdk\Domain\Payment\Definitions\SepaDirectDebitPaymentProduct771SpecificInput;
use Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_RequestBuilder as RequestBuilder;
use Ingenico_Connect_Model_Method_HostedCheckout as HostedCheckout;
use Ingenico_Connect_Model_Ingenico_RequestBuilder_DecoratorInterface as DecoratorInterface;

/**
 * Class Ingenico_Connect_Model_Ingenico_RequestBuilder_SpecificInput_SepaDirectDebitDecorator
 */
class Ingenico_Connect_Model_Ingenico_RequestBuilder_SpecificInput_SepaDirectDebitDecorator
    implements DecoratorInterface
{
    const SIGNATURE_TYPE_UNSIGNED = 'UNSIGNED';
    const SIGNATURE_TYPE_SMS = 'SMS';

    const RECURRENCE_TYPE_UNIQUE = 'UNIQUE';
    const RECURRENCE_TYPE_RECURRING = 'RECURRING';

    /**
     * @inheritdoc
     */
    public function decorate(DataObject $request, Mage_Sales_Model_Order $order)
    {
        $input = new SepaDirectDebitPaymentMethodSpecificInput();
        /**
         * In case of full redirect (payment method selection on hostedCheckout, we do not have a product id, but need
         * to transmit the SDD mandate data to support that payment method properly on the hosted checkout
         */
        $paymentProduct = $order->getPayment()->getAdditionalInformation(HostedCheckout::PRODUCT_ID_KEY);
        if ($paymentProduct === '771' || $paymentProduct === null) {
            $mandate = new CreateMandateWithReturnUrl();

            $mandate->signatureType = self::SIGNATURE_TYPE_SMS;
            $mandate->recurrenceType = self::RECURRENCE_TYPE_UNIQUE;

            if ($mandate->signatureType === self::SIGNATURE_TYPE_SMS) {
                if ($order->getPayment()->getAdditionalInformation(HostedCheckout::CLIENT_PAYLOAD_KEY)) {
                    $mandate->returnUrl = Mage::getUrl(RequestBuilder::REDIRECT_PAYMENT_RETURN_URL);
                } else {
                    $mandate->returnUrl = Mage::getUrl(RequestBuilder::HOSTED_CHECKOUT_RETURN_URL);
                }
            }

            if ($order->getCustomerId()) {
                $mandate->customerReference = $order->getCustomerId();
            } else {
                $mandate->customerReference = 'guest_' . $order->getBillingAddressId();
            }

            $specificInput = new SepaDirectDebitPaymentProduct771SpecificInput();
            $specificInput->mandate = $mandate;

            $input->paymentProduct771SpecificInput = $specificInput;
        }

        $request->sepaDirectDebitPaymentMethodSpecificInput = $input;

        return $request;
    }
}
