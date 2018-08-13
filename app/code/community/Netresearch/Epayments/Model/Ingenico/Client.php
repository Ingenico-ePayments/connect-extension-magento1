<?php

use \Ingenico\Connect\Sdk\CommunicatorConfiguration;
use \Ingenico\Connect\Sdk\Client;
use \Ingenico\Connect\Sdk\DefaultConnection;
use \Ingenico\Connect\Sdk\Domain\Payment\CreatePaymentRequest;
use \Ingenico\Connect\Sdk\Domain\Payment\CreatePaymentResponse;
use \Ingenico\Connect\Sdk\Domain\Sessions\SessionRequest;
use Netresearch_Epayments_Model_Ingenico_Api_ClientInterface as ClientInterface;
use Netresearch_Epayments_Model_Ingenico_Client_Communicator as Communicator;
use Netresearch_Epayments_Model_Ingenico_Client_CommunicatorLogger as CommunicatorLogger;

/**
 * Class Netresearch_Epayments_Model_Ingenico_Client
 */
class Netresearch_Epayments_Model_Ingenico_Client implements ClientInterface
{
    /**
     * @var Netresearch_Epayments_Model_ConfigInterface
     */
    protected $ePaymentsConfig;

    /**
     * @var Client[]
     */
    protected $ingenicoClient = array();

    /**
     * Netresearch_Epayments_Model_Ingenico_Client constructor.
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        $ePaymentsConfig = isset($args['ePaymentsConfig'])
                && $args['ePaymentsConfig'] instanceof Netresearch_Epayments_Model_ConfigInterface
            ? $args['ePaymentsConfig']
            : null;

        if (null === $ePaymentsConfig) {
            $ePaymentsConfig = Mage::getSingleton('netresearch_epayments/config');
        }
        $this->ePaymentsConfig = $ePaymentsConfig;
    }

    /**
     * @param $scopeId int
     */
    protected function initialize($scopeId)
    {
        if (!isset($this->ingenicoClient[$scopeId])) {
            $communicatorConfig = new CommunicatorConfiguration(
                $this->ePaymentsConfig->getApiKey($scopeId),
                $this->ePaymentsConfig->getApiSecret($scopeId),
                $this->ePaymentsConfig->getApiEndpoint($scopeId),
                $this->ePaymentsConfig->getIntegrator()
            );
            $secondaryCommunicatorConfig = new CommunicatorConfiguration(
                $this->ePaymentsConfig->getApiKey($scopeId),
                $this->ePaymentsConfig->getApiSecret($scopeId),
                $this->ePaymentsConfig->getSecondaryApiEndpoint($scopeId),
                $this->ePaymentsConfig->getIntegrator()
            );
            $communicator = new Communicator(
                new DefaultConnection(), $communicatorConfig
            );
            $communicator->setSecondaryCommunicatorConfiguration($secondaryCommunicatorConfig);
            $this->ingenicoClient[$scopeId] = new Client($communicator);

            if ($this->ePaymentsConfig->getLogAllRequests($scopeId)) {
                /** @var \Ingenico\Connect\Sdk\CommunicatorLogger|CommunicatorLogger $communicatorLogger */
                $communicatorLogger = Mage::getModel('netresearch_epayments/ingenico_client_communicatorLogger');
                $this->ingenicoClient[$scopeId]->enableLogging($communicatorLogger);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getIngenicoClient($scopeId = null)
    {
        if ($scopeId === null) {
            $scopeId = Mage::app()->getStore()->getId();
        }
        $this->initialize($scopeId);
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
}
