<?php

use Netresearch_Epayments_Model_Method_HostedCheckout as HostedCheckout;
use Netresearch_Epayments_Model_Ingenico_RequestBuilder_AbstractRequestBuilder as AbstractRequestBuilder;
use \Ingenico\Connect\Sdk\Domain\Payment\CreatePaymentRequest;

/**
 * Class Netresearch_Epayments_Model_Ingenico_RequestBuilder_CreatePayment_CreatePaymentRequestBuilder
 */
class Netresearch_Epayments_Model_Ingenico_RequestBuilder_CreatePayment_CreatePaymentRequestBuilder extends
    AbstractRequestBuilder
{
    /**
     * Netresearch_Epayments_Model_Ingenico_RequestBuilder_CreatePayment_CreatePaymentRequestBuilder constructor.
     */
    public function __construct()
    {
        $this->requestObject = new CreatePaymentRequest();

        parent::__construct();
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return CreatePaymentRequest
     */
    public function create(Mage_Sales_Model_Order $order)
    {
        $request = parent::create($order);
        $payload = $order->getPayment()->getAdditionalInformation(HostedCheckout::CLIENT_PAYLOAD_KEY);
        $request->encryptedCustomerInput = $payload;

        return $request;
    }
}
