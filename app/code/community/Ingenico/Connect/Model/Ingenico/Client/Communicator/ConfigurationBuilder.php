<?php

use Ingenico\Connect\Sdk\CommunicatorConfiguration;
use Ingenico\Connect\Sdk\Domain\MetaData\ShoppingCartExtension;

class Ingenico_Connect_Model_Ingenico_Client_Communicator_ConfigurationBuilder
{
    /**
     * @var Ingenico_Connect_Model_ConfigInterface
     */
    private $config;

    public function __construct()
    {
        $this->config = Mage::getSingleton('ingenico_connect/config');
    }

    /**
     * @param int|null $scopeId
     * @param array $data
     * @return CommunicatorConfiguration
     */
    public function build($scopeId = null, $data = [])
    {
        $shoppingCartExtension = new ShoppingCartExtension(
            $this->config->getIntegrator(),
            $this->config->getShoppingCartExtensionName(),
            $this->config->getMagentoVersion(),
            $this->config->getVersion()
        );

        $apiKey = !empty($data['api_key']) ? $data['api_key'] : $this->config->getApiKey($scopeId);
        $apiSecret = !empty($data['api_secret']) ? $data['api_secret'] : $this->config->getApiSecret($scopeId);
        $apiEndpoint = !empty($data['api_endpoint']) ?
            $data['api_endpoint'] : $this->config->getApiEndpoint($scopeId);

        $communicatorConfig = new CommunicatorConfiguration(
            $apiKey,
            $apiSecret,
            $apiEndpoint,
            $this->config->getIntegrator()
        );

        $communicatorConfig->setShoppingCartExtension($shoppingCartExtension);
        return $communicatorConfig;
    }
}
