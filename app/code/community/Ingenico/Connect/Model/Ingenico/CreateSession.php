<?php

use Ingenico_Connect_Model_ConfigInterface as ConfigInterface;
use Ingenico_Connect_Model_Ingenico_ActionInterface as ActionInterface;
use Ingenico_Connect_Model_Ingenico_Api_ClientInterface as Client;
use Ingenico_Connect_Model_Ingenico_CreateSession_SessionRequestFactory as SessionRequestFactory;

/**
 * @link https://epayments-api.developer-ingenico.com/s2sapi/v1/en_US/php/sessions/create.html
 */
class Ingenico_Connect_Model_Ingenico_CreateSession implements ActionInterface
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
     * Ingenico_Connect_Model_Ingenico_CreateSession constructor.
     */
    public function __construct()
    {
        $this->client = Mage::getModel('ingenico_connect/ingenico_client');
        $this->config = Mage::getModel('ingenico_connect/config');
        $this->requestFactory = Mage::getModel('ingenico_connect/ingenico_createSession_sessionRequestFactory');
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
            /** @var Ingenico_Connect_Model_Token[] $tokens */
            $tokens = Mage::getModel('ingenico_connect/tokenService')->find($customer->getId());
            $tokens = array_map(
                function ($token) {
                    /** @var Ingenico_Connect_Model_Token $token */
                    return $token->getTokenString();
                },
                $tokens
            );
        }

        $request = $this->requestFactory->create($tokens);

        return $this->client->createSession($request);
    }
}
