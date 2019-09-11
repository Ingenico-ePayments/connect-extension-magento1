<?php

/**
 * Class Ingenico_Connect_Model_Ingenico_RequestBuilder_SpecificInput_Card_ThreeDSecureBuilder
 */
class Ingenico_Connect_Model_Ingenico_RequestBuilder_SpecificInput_Card_ThreeDSecureBuilder
{
    const AUTHENTICATION_FLOW_BROWSER = 'browser';

    /**
     * @var Ingenico_Connect_Model_Ingenico_RequestBuilder_SpecificInput_Card_ThreeDSecure_RedirectionDataBuilder
     */
    protected $redirectionDataBuilder;

    /**
     * Ingenico_Connect_Model_Ingenico_RequestBuilder_SpecificInput_Card_ThreeDSecureBuilder constructor.
     */
    public function __construct()
    {
        $this->redirectionDataBuilder = Mage::getModel('ingenico_connect/ingenico_requestBuilder_specificInput_card_threeDSecure_redirectionDataBuilder');
    }

    /**
     * @param Mage_Sales_Model_Order $order
     *
     * @return \Ingenico\Connect\Sdk\Domain\Payment\Definitions\ThreeDSecure
     */
    public function create(Mage_Sales_Model_Order $order)
    {
        $ingenicoThreeDSecure = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\ThreeDSecure();
        $ingenicoThreeDSecure->redirectionData = $this->redirectionDataBuilder->create($order);
        $ingenicoThreeDSecure->authenticationFlow = $this->getAuthenticationFlow();

        return $ingenicoThreeDSecure;
    }

    /**
     * @return string
     */
    protected function getAuthenticationFlow()
    {
        return self::AUTHENTICATION_FLOW_BROWSER;
    }
}
