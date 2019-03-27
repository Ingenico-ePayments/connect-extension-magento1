<?php

use Netresearch_Epayments_Model_Config as Config;
use Netresearch_Epayments_Model_Ingenico_RequestBuilder_DecoratorPool as DecoratorPool;
use Netresearch_Epayments_Model_Method_HostedCheckout as HostedCheckout;
use \Ingenico\Connect\Sdk\Domain\Hostedcheckout\CreateHostedCheckoutRequest;
use \Ingenico\Connect\Sdk\Domain\Payment\CreatePaymentRequest;

/**
 * Builder for Ingenico requests like CreateHostedCheckoutRequest and CreatePaymentRequest.
 * Uses the decorator pool pattern to add specificInput objects to the request.
 *
 * Class Netresearch_Epayments_Model_Ingenico_RequestBuilder_Common_RequestBuilder
 */
class Netresearch_Epayments_Model_Ingenico_RequestBuilder_Common_RequestBuilder
{
    const HOSTED_CHECKOUT_RETURN_URL = 'epayments/hostedCheckout/return';
    const REDIRECT_PAYMENT_RETURN_URL = 'epayments/redirectPayment/return';

    /**
     * @var Netresearch_Epayments_Model_Ingenico_RequestBuilder_Common_OrderBuilder
     */
    private $orderBuilder;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Netresearch_Epayments_Model_Ingenico_RequestBuilder_Common_FraudFieldsBuilder
     */
    private $fraudFieldsBuilder;

    /**
     * @var DecoratorPool
     */
    private $decoratorPool;

    /**
     * Netresearch_Epayments_Model_Ingenico_RequestBuilder_AbstractRequestBuilder constructor.
     */
    public function __construct()
    {
        $this->orderBuilder = Mage::getSingleton('netresearch_epayments/ingenico_requestBuilder_common_orderBuilder');
        $this->config = Mage::getSingleton('netresearch_epayments/config');
        $this->decoratorPool = Mage::getSingleton('netresearch_epayments/ingenico_requestBuilder_decoratorPool');
        $this->fraudFieldsBuilder = Mage::getSingleton(
            'netresearch_epayments/ingenico_requestBuilder_common_fraudFieldsBuilder'
        );
    }

    /**
     * @param \Ingenico\Connect\Sdk\DataObject|CreateHostedCheckoutRequest|CreatePaymentRequest $ingenicoRequest
     * @param Mage_Sales_Model_Order $order
     * @return CreateHostedCheckoutRequest|CreatePaymentRequest
     */
    public function create($ingenicoRequest, Mage_Sales_Model_Order $order)
    {
        $ingenicoRequest->fraudFields = $this->fraudFieldsBuilder->create();
        $ingenicoRequest->order = $this->orderBuilder->create($order);

        if ($this->config->getCheckoutType($order->getStoreId()) === Config::CONFIG_INGENICO_CHECKOUT_TYPE_REDIRECT) {
            /**
             * Apply all decorators if checkout uses full Hosted Checkout redirect.
             */
            $ingenicoRequest = $this->decoratorPool->decorate($ingenicoRequest, $order);
        } else {
            /**
             * Apply one specific decorator if only one is needed.
             */
            $paymentMethod = $order->getPayment()->getAdditionalInformation(HostedCheckout::PRODUCT_PAYMENT_METHOD_KEY);
            try {
                $methodDecorator = $this->decoratorPool->get($paymentMethod);
                $ingenicoRequest = $methodDecorator->decorate($ingenicoRequest, $order);
            } catch (\Exception $exception) {
                // might occur if no decorator is available
            }
        }

        return $ingenicoRequest;
    }
}
