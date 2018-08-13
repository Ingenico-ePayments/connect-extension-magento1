<?php

use Netresearch_Epayments_Model_Ingenico_GlobalCollect_Wx_PaymentData as PaymentData;

class Netresearch_Epayments_Model_Ingenico_GlobalCollect_Wx_DataRecord
{
    private $MerchantID;
    private $OrderID;
    private $EffortID;
    private $AttemptID;
    /**
     * @var PaymentData
     */
    private $PaymentData;

    /**
     * @return mixed
     */
    public function getMerchantID()
    {
        return $this->MerchantID;
    }

    /**
     * @return mixed
     */
    public function getOrderID()
    {
        return $this->OrderID;
    }

    /**
     * @return mixed
     */
    public function getEffortID()
    {
        return $this->EffortID;
    }

    /**
     * @return mixed
     */
    public function getAttemptID()
    {
        return $this->AttemptID;
    }

    /**
     * @return PaymentData
     */
    public function getPaymentData()
    {
        return $this->PaymentData;
    }

    /**
     * @return string the generated connect payment id for the GC identifiers
     *
     */
    public function getConnectPaymentReference()
    {
        return str_pad($this->getMerchantID(), 10, '0', STR_PAD_LEFT)
               . $this->getOrderID()
               . str_pad($this->getEffortID(), 5, '0', STR_PAD_LEFT)
               . str_pad($this->getAttemptID(), 5, '0', STR_PAD_LEFT);
    }

    /**
     * @param array $data [attribute => value]
     * @return Netresearch_Epayments_Model_Ingenico_GlobalCollect_Wx_DataRecord
     */
    public static function fromArray(array $data)
    {
        $instance = new self();
        foreach ($data as $key => $value) {
            if ($key === 'PaymentData') {
                $instance->PaymentData = PaymentData::fromArray($value);
            } elseif (property_exists($instance, $key)) {
                $instance->$key = $value;
            }
        }

        return $instance;
    }

    /**
     * @param \DOMElement $element
     * @return Netresearch_Epayments_Model_Ingenico_GlobalCollect_Wx_DataRecord
     */
    public static function fromDomElement(\DOMElement $element)
    {
        $instance = new self();
        /** @var \DOMNode $item */
        foreach ($element->getElementsByTagName('*') as $item) {
            if ($item->localName === 'PaymentData') {
                $instance->PaymentData = PaymentData::fromDomNode($item);
            } elseif (preg_match("/.*DataRecord\[\d+\]\/[^\/.]*$/", $item->getNodePath())) {
                if (property_exists($instance, $item->localName)) {
                    $property = $item->localName;
                    $instance->$property = $item->nodeValue;
                }
            }
        }
        return $instance;
    }
}
