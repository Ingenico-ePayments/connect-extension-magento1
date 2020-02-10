<?php

use Ingenico\Connect\Sdk\Domain\Payment\Definitions\AddressPersonal;
use Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_Order_AbstractAddressBuilder as AbstractAddressBuilder;

/**
 * Class Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_Order_Shipping_AddressBuilder
 */
class Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_Order_Shipping_AddressBuilder extends AbstractAddressBuilder
{
    /**
     * @var Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_Order_Shipping_Address_NameBuilder
     */
    private $nameBuilder;

    /**
     * Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_Order_Shipping_AddressBuilder constructor.
     */
    public function __construct()
    {
        $this->nameBuilder = Mage::getModel('ingenico_connect/ingenico_requestBuilder_common_order_shipping_address_nameBuilder');
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return AddressPersonal
     */
    public function create(Mage_Sales_Model_Order $order)
    {
        $addressPersonal = new AddressPersonal();

        $shippingAddress = $this->getShippingAddressFromOrder($order);
        if ($shippingAddress !== null) {
            $this->populateAddress($addressPersonal, $shippingAddress);
            $addressPersonal->name = $this->nameBuilder->create($shippingAddress);
        }

        return $addressPersonal;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return Mage_Sales_Model_Order_Address|null
     */
    public function getShippingAddressFromOrder(Mage_Sales_Model_Order $order)
    {
        $shippingAddress = $order->getShippingAddress();
        if ($shippingAddress === false) {
            return null;
        }
        return $shippingAddress;
    }
}
