<?php

use \Ingenico\Connect\Sdk\Domain\Sessions\SessionRequest;
use \Ingenico\Connect\Sdk\Domain\Sessions\SessionResponse;
use \Ingenico\Connect\Sdk\Domain\Payment\CreatePaymentRequest;
use \Ingenico\Connect\Sdk\Domain\Payment\CreatePaymentResponse;

/**
 * Interface Ingenico_Connect_Model_Ingenico_Api_ClientInterface
 */
interface Ingenico_Connect_Model_Ingenico_Api_ClientInterface
{
    /**
     * @param $scopeId int|null
     * @return \Ingenico\Connect\Sdk\Client
     */
    public function getIngenicoClient($scopeId = null);

    /**
     * @param CreatePaymentRequest $request
     * @param int|null $scopeId
     * @return CreatePaymentResponse
     */
    public function createPayment(CreatePaymentRequest $request, $scopeId = null);

    /**
     * @param SessionRequest $request
     * @param int|null $scopeId
     * @return SessionResponse
     */
    public function createSession(SessionRequest $request, $scopeId = null);
}
