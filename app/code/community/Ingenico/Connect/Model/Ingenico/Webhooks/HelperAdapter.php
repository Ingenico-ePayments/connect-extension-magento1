<?php

class Ingenico_Connect_Model_Ingenico_Webhooks_HelperAdapter
{
    /** @var Ingenico_Connect_Model_ConfigInterface */
    private $config;

    /**
     * Ingenico_Connect_Model_Ingenico_Webhooks_HelperAdapter constructor.
     */
    public function __construct()
    {
        $this->config = Mage::getSingleton('ingenico_connect/config');
    }

    /**
     * @param string $body
     * @param array $requestHeaders
     * @return \Ingenico\Connect\Sdk\Domain\Webhooks\WebhooksEvent
     * @throws \Ingenico\Connect\Sdk\Webhooks\SignatureValidationException
     * @throws \Ingenico\Connect\Sdk\Webhooks\ApiVersionMismatchException
     */
    public function unmarshal($body, array $requestHeaders)
    {
        $secretKeys = array();
        if ($this->config->getWebhooksKeyId() && $this->config->getWebhooksSecretKey()) {
            $keyId = $this->config->getWebhooksKeyId();
            $key = $this->config->getWebhooksSecretKey();
            $secretKeys[$keyId] = $key;
        }

        if ($this->config->getSecondaryWebhooksKeyId() && $this->config->getSecondaryWebhooksSecretKey()) {
            $secondaryKeyId = $this->config->getSecondaryWebhooksKeyId();
            $secondaryKey = $this->config->getSecondaryWebhooksSecretKey();
            $secretKeys[$secondaryKeyId] = $secondaryKey;
        }

        $secretKeyStore = new \Ingenico\Connect\Sdk\Webhooks\InMemorySecretKeyStore($secretKeys);
        $helper         = new \Ingenico\Connect\Sdk\Webhooks\WebhooksHelper($secretKeyStore);

        return $helper->unmarshal($body, $requestHeaders);
    }
}
