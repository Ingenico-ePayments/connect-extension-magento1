<?php

use Netresearch_Epayments_Model_Method_HostedCheckout as HostedCheckout;
use \Ingenico\Connect\Sdk\Domain\Payment\CreatePaymentRequest;
use Netresearch_Epayments_Model_Ingenico_RequestBuilder_Common_RequestBuilder as CommonRequestBuilder;

/**
 * Class Netresearch_Epayments_Model_Ingenico_RequestBuilder_CreatePayment_CreatePaymentRequestBuilder
 */
class Netresearch_Epayments_Model_Ingenico_RequestBuilder_CreatePayment_CreatePaymentRequestBuilder
{
    /**
     * @var CommonRequestBuilder
     */
    private $requestBuilder;

    /**
     * Netresearch_Epayments_Model_Ingenico_RequestBuilder_CreatePayment_CreatePaymentRequestBuilder constructor.
     */
    public function __construct()
    {
        $this->requestBuilder = Mage::getSingleton(
            'netresearch_epayments/ingenico_requestBuilder_common_requestBuilder'
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
