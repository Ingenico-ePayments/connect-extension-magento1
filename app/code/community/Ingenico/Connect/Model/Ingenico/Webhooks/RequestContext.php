<?php

class Ingenico_Connect_Model_Ingenico_Webhooks_RequestContext
{
    /**
     * @var array
     */
    private $headers;

    /**
     * @var string
     */
    private $body;

    /**
     * Ingenico_Connect_Model_Ingenico_WebhooksRequestContext constructor.
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        /**
         * @todo: Check if exists in headers
         * X-GCS-Signature
         * X-GCS-KeyId
         */

        $this->headers = $args['headers'];
        $this->body    = $args['body'];
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }
}
