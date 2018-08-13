<?php

use Netresearch_Epayments_Model_Ingenico_RequestBuilder_SpecificInput_RequestDecoratorFactory as
    RequestDecoratorFactory;
use \Ingenico\Connect\Sdk\Domain\Hostedcheckout\CreateHostedCheckoutRequest;
use \Ingenico\Connect\Sdk\Domain\Payment\CreatePaymentRequest;

/**
 * Abstract builder for Ingenico requests like CreateHostedCheckoutRequest or CreatePaymentRequest.
 * Use the decorator pattern to add specificInput objects to the request.
 *
 * Class Netresearch_Epayments_Model_Ingenico_RequestBuilder_AbstractRequestBuilder
 */
abstract class Netresearch_Epayments_Model_Ingenico_RequestBuilder_AbstractRequestBuilder
{
    const HOSTED_CHECKOUT_RETURN_URL = 'epayments/hostedCheckout/return';

    /**
     * @var CreateHostedCheckoutRequest|CreatePaymentRequest
     */
    protected $requestObject;

    /**
     * @var RequestDecoratorFactory The request decorator is used to add the correct *MethodSpecificInput
     *                              property to the request object
     */
    protected $requestDecoratorFactory;

    /**
     * @var Netresearch_Epayments_Model_Ingenico_RequestBuilder_Common_OrderBuilder
     */
    protected $orderBuilder;

    /**
     * @var Netresearch_Epayments_Model_Ingenico_RequestBuilder_Common_FraudFieldsBuilder
     */
    protected $fraudFieldsBuilder;

    /**
     * Netresearch_Epayments_Model_Ingenico_RequestBuilder_AbstractRequestBuilder constructor.
     */
    public function __construct()
    {
        $this->orderBuilder = Mage::getModel('netresearch_epayments/ingenico_requestBuilder_common_orderBuilder');
        $this->fraudFieldsBuilder = Mage::getModel(
            'netresearch_epayments/ingenico_requestBuilder_common_fraudFieldsBuilder'
        );
        $this->requestDecoratorFactory = Mage::getModel(
            'netresearch_epayments/ingenico_requestBuilder_specificInput_requestDecoratorFactory'
        );
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return CreateHostedCheckoutRequest|CreatePaymentRequest
     */
    public function create(Mage_Sales_Model_Order $order)
    {
        $ingenicoRequest = $this->requestObject;

        $ingenicoRequest->fraudFields = $this->fraudFieldsBuilder->create();
        $ingenicoRequest->order = $this->orderBuilder->create($order);

        $requestDecorator = $this->requestDecoratorFactory->create($order);
        $ingenicoRequest = $requestDecorator->decorate($ingenicoRequest, $order);

        return $ingenicoRequest;
    }
}
