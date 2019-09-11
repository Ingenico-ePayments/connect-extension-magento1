<?php

use Ingenico_Connect_Model_Ingenico_RequestBuilder_Common_RequestBuilder as RequestBuilder;
use Ingenico_Connect_Model_Method_HostedCheckout as HostedCheckout;

/**
 * Class Ingenico_Connect_Model_Ingenico_RequestBuilder_SpecificInput_Card_ThreeDSecure_RedirectionDataBuilder
 */
class Ingenico_Connect_Model_Ingenico_RequestBuilder_SpecificInput_Card_ThreeDSecure_RedirectionDataBuilder
{
    /**
     * @param Mage_Sales_Model_Order $order
     * @return \Ingenico\Connect\Sdk\Domain\Payment\Definitions\RedirectionData
     */
    public function create(Mage_Sales_Model_Order $order)
    {
        $ingenicoRedirectionData = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\RedirectionData();

        $variant = $this->getVariant($order);
        if ($variant) {
            $ingenicoRedirectionData->variant = $variant;
        }

        $ingenicoRedirectionData->returnUrl = $this->getReturnUrl($order);

        return $ingenicoRedirectionData;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return string
     */
    protected function getVariant(Mage_Sales_Model_Order $order) {
        /** @var Ingenico_Connect_Model_Config $config */
        $config = Mage::getModel('ingenico_connect/config');

        return $config->getHostedCheckoutVariant($order->getStoreId());
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return string
     */
    protected function getReturnUrl(Mage_Sales_Model_Order $order) {
        if ($order->getPayment()->getAdditionalInformation(HostedCheckout::CLIENT_PAYLOAD_KEY)) {
            return Mage::getUrl(RequestBuilder::REDIRECT_PAYMENT_RETURN_URL);
        } else {
            return Mage::getUrl(RequestBuilder::HOSTED_CHECKOUT_RETURN_URL);
        }
    }
}
