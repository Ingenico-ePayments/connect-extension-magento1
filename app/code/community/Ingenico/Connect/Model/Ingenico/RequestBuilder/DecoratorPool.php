<?php

use Ingenico_Connect_Model_Ingenico_RequestBuilder_DecoratorInterface as DecoratorInterface;
use Ingenico\Connect\Sdk\DataObject;

/**
 * This Pool can apply payment method decorators configured via di.xml or return an individual decorator based on
 * an Ingenico payment method name.
 *
 * Class DecoratorPool
 */
class Ingenico_Connect_Model_Ingenico_RequestBuilder_DecoratorPool implements DecoratorInterface
{
    /**
     * @var string[]
     */
    private $decoratorPaths;

    /**
     * @var Ingenico_Connect_Helper_Data
     */
    private $helper;

    /**
     * Ingenico_Connect_Model_Ingenico_RequestBuilder_DecoratorPool constructor.
     */
    public function __construct()
    {
        $this->decoratorPaths = (array)Mage::getConfig()->getNode('hosted_checkout/decorator_mappings');
        $this->helper = Mage::helper('ingenico_connect');
    }

    /**
     * @param string $paymentMethodId
     * @return DecoratorInterface
     * @throws Mage_Core_Exception
     */
    public function get($paymentMethodId)
    {
        if (isset($this->decoratorPaths[$paymentMethodId])) {
            /** @var DecoratorInterface $requestDecorator */
            $requestDecorator = Mage::getModel($this->decoratorPaths[$paymentMethodId]);

            return $requestDecorator;
        }

        Mage::throwException(
            $this->helper->__(
                "There is no PaymentProductSpecificInput decorator for payment method id '%1'.",
                $paymentMethodId
            )
        );
    }

    /**
     * @inheritdoc
     */
    public function decorate(DataObject $request, Mage_Sales_Model_Order $order)
    {
        foreach ($this->decoratorPaths as $decoratorPath) {
            try {
                /** @var DecoratorInterface $decorator */
                $decorator = Mage::getModel($decoratorPath);
                $request = $decorator->decorate($request, $order);
            } catch (\Exception $e) {
                // to prevent execution failure of decorators down the line, we catch all exceptions and ignore them
            }
        }

        return $request;
    }
}
