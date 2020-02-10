<?php

use Ingenico\Connect\Sdk\Domain\Hostedcheckout\CreateHostedCheckoutRequest;
use Ingenico\Connect\Sdk\Domain\Payment\CreatePaymentRequest;
use Ingenico_Connect_Model_Config as Config;
use Ingenico_Connect_Model_Ingenico_RequestBuilder_DecoratorPool as DecoratorPool;
use Ingenico_Connect_Model_Method_HostedCheckout as HostedCheckout;

/**
 * Builder for Ingenico requests like CreateHostedCheckoutRequest and CreatePaymentRequest.
 * Uses the decorator pool pattern to add specificInput objects to the request.
 *
 * Class Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_RequestBuilder
 */
class Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_RequestBuilder
{
    const HOSTED_CHECKOUT_RETURN_URL = 'epayments/hostedCheckout/return';
    const REDIRECT_PAYMENT_RETURN_URL = 'epayments/redirectPayment/return';

    /**
     * @var Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_OrderBuilder
     */
    private $orderBuilder;

    /**
     * @var Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_MerchantBuilder
     */
    private $merchantBuilder;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var DecoratorPool
     */
    private $decoratorPool;

    /**
     * Ingenico_Connect_Model_Ingenico_RequestBuilder_AbstractRequestBuilder constructor.
     */
    public function __construct()
    {
        $this->orderBuilder = Mage::getSingleton('ingenico_connect/ingenico_requestBuilder_common_orderBuilder');
        $this->merchantBuilder = Mage::getSingleton('ingenico_connect/ingenico_requestBuilder_common_merchantBuilder');
        $this->config = Mage::getSingleton('ingenico_connect/config');
        $this->decoratorPool = Mage::getSingleton('ingenico_connect/ingenico_requestBuilder_decoratorPool');
    }

    /**
     * @param \Ingenico\Connect\Sdk\DataObject|CreateHostedCheckoutRequest|CreatePaymentRequest $ingenicoRequest
     * @param Mage_Sales_Model_Order $order
     * @return CreateHostedCheckoutRequest|CreatePaymentRequest
     * @throws Exception
     */
    public function create($ingenicoRequest, Mage_Sales_Model_Order $order)
    {
        $ingenicoRequest->order = $this->orderBuilder->create($order);
        $ingenicoRequest->merchant = $this->merchantBuilder->create($order);

        if ($this->config->getCheckoutType($order->getStoreId()) === Config::CONFIG_INGENICO_CHECKOUT_TYPE_REDIRECT) {
            /**
             * Apply all decorators if checkout uses full Hosted Checkout redirect.
             */
            $ingenicoRequest = $this->decoratorPool->decorate($ingenicoRequest, $order);
        } else {
            /**
             * Apply one specific decorator if only one is needed.
             */
            try {
                $paymentMethod = $order->getPayment()->getAdditionalInformation(HostedCheckout::PRODUCT_PAYMENT_METHOD_KEY);
                $methodDecorator = $this->decoratorPool->get($paymentMethod);
                $ingenicoRequest = $methodDecorator->decorate($ingenicoRequest, $order);
            } catch (\Exception $exception) {
                // might occur if no decorator is available
            }
        }

        return $ingenicoRequest;
    }
}
