<?php

use Netresearch_Epayments_Model_Ingenico_RequestBuilder_SpecificInput_AbstractMethodDecorator as
    AbstractMethodDecorator;
use Netresearch_Epayments_Model_Method_HostedCheckout as HostedCheckout;
/**
 * This factory uses path mappings from the configuration to determine which PaymentMethodSpecificInput decorator
 * class belongs to which payment method group.
 * The neccessary information is stored in $order->getPayment()->getAdditionalinformation().
 *
 * Class Netresearch_Epayments_Model_Ingenico_RequestBuilder_SpecificInput_RequestDecoratorFactory
 */
class Netresearch_Epayments_Model_Ingenico_RequestBuilder_SpecificInput_RequestDecoratorFactory
{
    /**
     * @var array   Contains a 'paymentProduct => decoratorPath' map
     */
    protected $decoratorMap;

    /**
     * Netresearch_Epayments_Model_Ingenico_RequestBuilder_SpecificInput_RequestDecoratorFactory constructor.
     */
    public function __construct()
    {
        $this->decoratorMap = (array)Mage::getConfig()->getNode('hosted_checkout/decorator_mappings');
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return AbstractMethodDecorator
     */
    public function create(Mage_Sales_Model_Order $order)
    {
        /** @var AbstractMethodDecorator $requestDecorator */
        $requestDecorator = Mage::getModel($this->getDecoratorPath($order));

        return $requestDecorator;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return string
     * @throws RuntimeException
     */
    protected function getDecoratorPath(Mage_Sales_Model_Order $order)
    {
        $currentPaymentMethod = $order->getPayment()->getAdditionalInformation(
            HostedCheckout::PRODUCT_PAYMENT_METHOD_KEY
        );
        if (isset($this->decoratorMap[$currentPaymentMethod])) {
            return $this->decoratorMap[$currentPaymentMethod];
        }
        throw new RuntimeException("There is no SpecificInput decorator for payment method $currentPaymentMethod.");
    }
}
