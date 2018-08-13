<?php

use Ingenico\Connect\Sdk\Domain\Sessions\SessionRequest;

/**
 * Class Netresearch_Epayments_Model_Ingenico_CreateSession_SessionRequestFactory
 */
class Netresearch_Epayments_Model_Ingenico_CreateSession_SessionRequestFactory
{
    /**
     * Create class instance with specified parameters
     *
     * @param array $tokens
     * @return SessionRequest
     */
    public function create(array $tokens = array())
    {
        /** @var SessionRequest $request */
        $request = Mage::getModel(SessionRequest::class);
        if (!empty($tokens)) {
            $request->tokens = $tokens;
        }

        return $request;
    }
}
