<?php

use Ingenico\Connect\Sdk\Domain\Payment\Definitions\Shipping;

/**
 * Class Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_Order_ShippingBuilder
 */
class Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_Order_ShippingBuilder
{
    const ANOTHER_VERIFIED_ADDRESS_ON_FILE_WITH_MERCHANT = 'another-verified-address-on-file-with-merchant';
    const DIFFERENT_THAN_BILLING = 'different-than-billing';
    const DIGITAL_GOODS = 'digital-goods';
    const SAME_AS_BILLING = 'same-as-billing';

    /**
     * @var Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_Order_Shipping_AddressBuilder
     */
    private $addressBuilder;

    public function __construct()
    {
        $this->addressBuilder = Mage::getModel('ingenico_connect/ingenico_requestBuilder_common_order_shipping_addressBuilder');
    }

    /**
     * @throws Exception
     */
    public function create(Mage_Sales_Model_Order $order)
    {
        $shipping = new Shipping();
        $shipping->address = $this->addressBuilder->create($order);

        $shippingAddress = $order->getShippingAddress();
        $shipping->addressIndicator = $this->getAddressIndicator($order);

        if ($billingAddress = $order->getBillingAddress()) {
            $shipping->emailAddress = $this->getEmailAddress($billingAddress);
        }

        if ($shippingAddress !== null && !$order->getCustomerIsGuest()) {
            $shipping->firstUsageDate = $this->getFirstUsageDate($shippingAddress);
            $shipping->isFirstUsage = $this->getIsAddressFirstUsage($shippingAddress);
        }

        $shipping->trackingNumber = $this->getShipmentTrackingNumber($order);

        return $shipping;
    }

    /**
     * @throws Exception
     */
    private function getFirstUsageDate(Mage_Sales_Model_Order_Address $shippingAddress)
    {
        $addressCollection = $this->getShippingAddressLastUsagesByOrder($shippingAddress);
        $oldestUsage = $addressCollection->getFirstItem();
        return (new DateTime($oldestUsage['created_at']))->format('Ymd');
    }

    private function getIsAddressFirstUsage(Mage_Sales_Model_Order_Address $shippingAddress)
    {
        $addressCollection = $this->getShippingAddressLastUsagesByOrder($shippingAddress);
        return !($addressCollection->getSize() > 1);
    }

    private function getAddressIndicator(Mage_Sales_Model_Order $order)
    {
        if ($order->getIsVirtual()) {
            return self::DIGITAL_GOODS;
        }

        if ($this->isShippingAddressEqualToBillingAddress($order->getBillingAddress(), $order->getShippingAddress())) {
            return self::SAME_AS_BILLING;
        }

        if (!$order->getCustomerIsGuest() &&
            $this->isShippingAddressOnFileWithTheRegisteredCustomer($order->getShippingAddress())
        ) {
            return self::ANOTHER_VERIFIED_ADDRESS_ON_FILE_WITH_MERCHANT;
        }

        return self::DIFFERENT_THAN_BILLING;
    }

    private function getEmailAddress(Mage_Sales_Model_Order_Address $address)
    {
        return $address->getEmail();
    }

    private function getShipmentTrackingNumber(Mage_Sales_Model_Order $order)
    {
        $trackingNumbers = $order->getTrackingNumbers();

        if ($trackingNumbers === []) {
            return null;
        }

        return $trackingNumbers[0];
    }

    private function isShippingAddressOnFileWithTheRegisteredCustomer(Mage_Sales_Model_Order_Address $shippingAddress)
    {
        return $shippingAddress->getCustomerAddressId() !== null;
    }

    private function getShippingAddressLastUsagesByOrder(Mage_Sales_Model_Order_Address $shippingAddress)
    {
        $addressCollection = Mage::getResourceModel('sales/order_address_collection');
        $addressCollection
            ->join(
                'customer/address_entity',
                'main_table.customer_address_id = `customer/address_entity`.entity_id',
                ['customer_address_entity_id' => 'customer/address_entity.entity_id']
            )
            ->join(
                'sales/order',
                'main_table.parent_id = `sales/order`.entity_id',
                ['created_at' => 'sales/order.created_at']
            )
            ->addFieldToFilter('customer/address_entity.entity_id', $shippingAddress->getCustomerAddressId())
            ->addFieldToFilter('main_table.address_type', Mage_Customer_Model_Address_Abstract::TYPE_SHIPPING)
            ->addOrder('`sales/order`.created_at', Zend_Db_Select::SQL_ASC);
        return $addressCollection;
    }

    private function isShippingAddressEqualToBillingAddress(
        Mage_Sales_Model_Order_Address $shippingAddress,
        Mage_Sales_Model_Order_Address $billingAddress
    ) {
        $shippingAddress = [
            'firstName'     => $shippingAddress->getFirstname(),
            'lastName'      => $shippingAddress->getLastname(),
            'company'       => $shippingAddress->getCompany(),
            'streetAddress' => $shippingAddress->getStreet(),
            'city'          => $shippingAddress->getCity(),
            'region'        => $shippingAddress->getRegion(),
            'postalCode'    => $shippingAddress->getPostcode(),
            'country'       => $shippingAddress->getCountryId(),
            'phoneNumber'   => $shippingAddress->getTelephone(),
        ];

        $billingAddress = [
            'firstName'     => $billingAddress->getFirstname(),
            'lastName'      => $billingAddress->getLastname(),
            'company'       => $billingAddress->getCompany(),
            'streetAddress' => $billingAddress->getStreet(),
            'city'          => $billingAddress->getCity(),
            'region'        => $billingAddress->getRegion(),
            'postalCode'    => $billingAddress->getPostcode(),
            'country'       => $billingAddress->getCountryId(),
            'phoneNumber'   => $billingAddress->getTelephone(),
        ];

        return $shippingAddress === $billingAddress;
    }
}
