<?php

use Ingenico\Connect\Sdk\DataObject;
use Ingenico\Connect\Sdk\Domain\Definitions\Address;
use Ingenico\Connect\Sdk\Domain\Payment\Definitions\AddressPersonal;

/**
 * Class Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_Order_AbstractAddressBuilder
 */
abstract class Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_Order_AbstractAddressBuilder
{
    const ADDITIONAL_INFO = 'additional_info';
    const HOUSE_NUMBER = 'house_number';
    const STREET = 'street';

    protected function populateAddress(DataObject $dataObject, Mage_Sales_Model_Order_Address $orderAddress)
    {
        if (!($dataObject instanceof Address || $dataObject instanceof AddressPersonal)) {
            return null;
        }

        $dataObject->city = $orderAddress->getCity();
        $dataObject->countryCode = $orderAddress->getCountryId();
        $dataObject->state = $orderAddress->getRegion();
        $dataObject->stateCode = $orderAddress->getRegionCode();
        $dataObject->zip = $orderAddress->getPostcode();
        $street = $orderAddress->getStreet();
        if ($street !== null) {
            $addressArray = $this->getHouseNumberFromAddress($street);
            $dataObject->street = $addressArray[self::STREET];
            $dataObject->houseNumber = $addressArray[self::HOUSE_NUMBER];
            $dataObject->additionalInfo = $addressArray[self::ADDITIONAL_INFO];
        }
    }

    private function getHouseNumberFromAddress($streetLines)
    {
        $address = trim(implode(' ', $streetLines));
        $address = str_replace(['nº', "\n"], ['', ' '], $address);

        // We reverse the address and the regex, so we start searching for the suffix, then the house number.
        // The remainder is considered to be the street name.
        $address = strrev($address);
        $hasMatch = preg_match('/^([a-zA-Z\-\s]*)\s*?(\d+)\s+(.*)$/', $address, $match);

        if (!$hasMatch) {
            return [
                self::STREET => strrev($address),
                self::HOUSE_NUMBER => '',
                self::ADDITIONAL_INFO => '',
            ];
        }

        return [
            self::STREET => strrev(trim($match[3], ' ,-')),
            self::HOUSE_NUMBER => strrev(trim($match[2])),
            self::ADDITIONAL_INFO => strrev(trim($match[1], ' ,-')),
        ];
    }
}
