<?php

use Netresearch_Epayments_Model_ConfigInterface as ConfigInterface;
use Netresearch_Epayments_Model_Ingenico_ActionInterface as ActionInterface;
use Netresearch_Epayments_Model_Ingenico_Api_ClientInterface as Client;
use Netresearch_Epayments_Model_Ingenico_CreateSession_SessionRequestFactory as SessionRequestFactory;

/**
 * @link https://epayments-api.developer-ingenico.com/s2sapi/v1/en_US/php/sessions/create.html
 */
class Netresearch_Epayments_Model_Ingenico_CreateSession implements ActionInterface
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var SessionRequestFactory
     */
    protected $requestFactory;

    /**
     * @var \Mage_Checkout_Model_Session
     */
    protected $checkoutSession;

    /**
     * Netresearch_Epayments_Model_Ingenico_CreateSession constructor.
     */
    public function __construct()
    {
        $this->client = Mage::getModel('netresearch_epayments/ingenico_client');
        $this->config = Mage::getModel('netresearch_epayments/config');
        $this->requestFactory = Mage::getModel('netresearch_epayments/ingenico_createSession_sessionRequestFactory');
        $this->checkoutSession = Mage::getModel('checkout/session');
    }

    /**
     *
     * Create a new customer session for the client SDK.
     *
     * @param Mage_Customer_Model_Customer|null $customer
     * @return \Ingenico\Connect\Sdk\Domain\Sessions\SessionResponse
     */
    public function create($customer = null)
    {
        $tokens = array();
        if ($customer) {
            /** @var Netresearch_Epayments_Model_Token[] $tokens */
            $tokens = Mage::getModel('netresearch_epayments/tokenService')->find($customer->getId());
            $tokens = array_map(
                function ($token) {
                    /** @var Netresearch_Epayments_Model_Token $token */
                    return $token->getTokenString();
                },
                $tokens
            );
        }

        $request = $this->requestFactory->create($tokens);

        return $this->client->createSession($request);
    }
}
