<?php

class Netresearch_Epayments_Tests_Unit_Model_Ingenico_Webhooks_RequestContextTest extends PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $requestContext = new Netresearch_Epayments_Model_Ingenico_Webhooks_RequestContext(
            [
                'headers' => ['headerKey' => 'headerValue'],
                'body' => 'raw_body'
            ]
        );
        $this->assertEquals(['headerKey' => 'headerValue'], $requestContext->getHeaders());
        $this->assertEquals('raw_body', $requestContext->getBody());
    }
}
