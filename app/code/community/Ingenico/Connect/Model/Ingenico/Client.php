<?php

use \Ingenico\Connect\Sdk\Client;
use \Ingenico\Connect\Sdk\DefaultConnection;
use \Ingenico\Connect\Sdk\Domain\Payment\CreatePaymentRequest;
use \Ingenico\Connect\Sdk\Domain\Payment\CreatePaymentResponse;
use \Ingenico\Connect\Sdk\Domain\Sessions\SessionRequest;
use Ingenico_Connect_Model_Ingenico_Api_ClientInterface as ClientInterface;
use Ingenico_Connect_Model_Ingenico_Client_Communicator as Communicator;
use Ingenico_Connect_Model_Ingenico_Client_CommunicatorLogger as CommunicatorLogger;
use Ingenico_Connect_Model_Ingenico_Client_Communicator_ConfigurationBuilder as ConfigurationBuilder;

/**
 * Class Ingenico_Connect_Model_Ingenico_Client
 */
class Ingenico_Connect_Model_Ingenico_Client implements ClientInterface
{
    /**
     * @var Ingenico_Connect_Model_ConfigInterface
     */
    protected $ePaymentsConfig;

    /**
     * @var Client[]
     */
    protected $ingenicoClient = array();

    /**
     * @var ConfigurationBuilder
     */
    private $communicatorConfigBuilder;

    /**
     * Ingenico_Connect_Model_Ingenico_Client constructor.
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        $ePaymentsConfig = isset($args['ePaymentsConfig'])
                           && $args['ePaymentsConfig'] instanceof Ingenico_Connect_Model_ConfigInterface
            ? $args['ePaymentsConfig']
            : null;

        if (null === $ePaymentsConfig) {
            $ePaymentsConfig = Mage::getSingleton('ingenico_connect/config');
        }

        $this->ePaymentsConfig = $ePaymentsConfig;
        $this->communicatorConfigBuilder = Mage::getSingleton('ingenico_connect/ingenico_client_communicator_configurationBuilder');
    }

    /**
     * @param $scopeId int
     */
    protected function initialize($scopeId)
    {
        if (!isset($this->ingenicoClient[$scopeId])) {
            $communicatorConfig = $this->communicatorConfigBuilder->build($scopeId);
            $secondaryCommunicatorConfig = $this->communicatorConfigBuilder->build(
                $scopeId,
                array(
                    'api_endpoint' => $this->ePaymentsConfig->getSecondaryApiEndpoint(),
                )
            );
            $client = $this->buildClient($scopeId, $communicatorConfig, $secondaryCommunicatorConfig);
            $this->ingenicoClient[$scopeId] = $client;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getIngenicoClient($scopeId = null, $data = array())
    {
        if ($scopeId === null) {
            $scopeId = Mage::app()->getStore()->getId();
        }

        $this->initialize($scopeId, $data);
        return $this->ingenicoClient[$scopeId];
    }

    /**
     * @param CreatePaymentRequest $request
     * @param int|null $scopeId
     * @return CreatePaymentResponse
     */
    public function createPayment(CreatePaymentRequest $request, $scopeId = null)
    {
        $response = $this
            ->getIngenicoClient($scopeId)
            ->merchant($this->ePaymentsConfig->getMerchantId($scopeId))
            ->payments()
            ->create($request);

        return $response;
    }

    /**
     * @param SessionRequest $request
     * @param int|null $scopeId
     * @return \Ingenico\Connect\Sdk\Domain\Sessions\SessionResponse
     */
    public function createSession(SessionRequest $request, $scopeId = null)
    {
        $response = $this
            ->getIngenicoClient($scopeId)
            ->merchant($this->ePaymentsConfig->getMerchantId($scopeId))
            ->sessions()
            ->create($request);

        return $response;
    }

    /**
     * @param null $scopeId
     * @param array $data
     * @return \Ingenico\Connect\Sdk\Domain\Services\TestConnection
     */
    public function ingenicoTestAccount($scopeId = null, $data = array())
    {
        $client = $this->buildClient($scopeId, $this->communicatorConfigBuilder->build($scopeId, $data));
        $response = $client
            ->merchant($data['merchant_id'])
            ->services()
            ->testconnection();

        return $response;
    }

    /**
     * @param $scopeId
     * @param $config
     * @param $secondaryConfig
     * @return Client
     */
    protected function buildClient($scopeId, $config, $secondaryConfig = null)
    {
        $communicator = new Communicator(
            new DefaultConnection(), $config
        );
        if ($secondaryConfig !== null) {
            $communicator->setSecondaryCommunicatorConfiguration($secondaryConfig);
        }

        $client = new Client($communicator);

        if ($this->ePaymentsConfig->getLogAllRequests($scopeId)) {
            /** @var \Ingenico\Connect\Sdk\CommunicatorLogger|CommunicatorLogger $communicatorLogger */
            $communicatorLogger = Mage::getModel('ingenico_connect/ingenico_client_communicatorLogger');
            $client->enableLogging($communicatorLogger);
        }

        return $client;
    }
}
