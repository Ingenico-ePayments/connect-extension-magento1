<?php

use Ingenico_Connect_Model_Method_HostedCheckout as HostedCheckout;
use \Ingenico\Connect\Sdk\Domain\Payment\CreatePaymentRequest;
use Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_RequestBuilder as CommonRequestBuilder;

/**
 * Class Ingenico_Connect_Model_Ingenico_RequestBuilder_CreatePayment_CreatePaymentRequestBuilder
 */
class Ingenico_Connect_Model_Ingenico_RequestBuilder_CreatePayment_CreatePaymentRequestBuilder
{
    /**
     * @var CommonRequestBuilder
     */
    private $requestBuilder;

    /**
     * Ingenico_Connect_Model_Ingenico_RequestBuilder_CreatePayment_CreatePaymentRequestBuilder constructor.
     */
    public function __construct()
    {
        $this->requestBuilder = Mage::getSingleton(
            'ingenico_connect/ingenico_requestBuilder_common_requestBuilder'
        );
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return CreatePaymentRequest
     */
    public function create(Mage_Sales_Model_Order $order)
    {
        $request = new CreatePaymentRequest();
        $request = $this->requestBuilder->create($request, $order);

        $payload = $order->getPayment()->getAdditionalInformation(HostedCheckout::CLIENT_PAYLOAD_KEY);
        $request->encryptedCustomerInput = $payload;

        return $request;
    }
}
