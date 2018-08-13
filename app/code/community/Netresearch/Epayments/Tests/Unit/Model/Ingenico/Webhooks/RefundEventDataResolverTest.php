<?php

class Netresearch_Epayments_Tests_Unit_Model_Ingenico_Webhooks_RefundEventDataResolverTest
    extends PHPUnit_Framework_TestCase
{
    /** @var \Netresearch_Epayments_Model_Ingenico_Webhooks_RefundEventDataResolver */
    private $resolver;

    protected function setUp()
    {
        parent::setUp();
        $this->resolver = new Netresearch_Epayments_Model_Ingenico_Webhooks_RefundEventDataResolver();
    }

    /**
     * @dataProvider incorrectEventsDataProvider
     * @expectedException RuntimeException
     * @expectedExceptionMessage Event does not match resolver.
     *
     * @param \Ingenico\Connect\Sdk\Domain\Webhooks\WebhooksEvent $incorrectEvent
     */
    public function testGetResponseIfEventDoesNotMatchResolver(
        \Ingenico\Connect\Sdk\Domain\Webhooks\WebhooksEvent $incorrectEvent
    )
    {
        $this->resolver->getResponse($incorrectEvent);
    }

    public function testGetResponse()
    {
        $event = new \Ingenico\Connect\Sdk\Domain\Webhooks\WebhooksEvent();
        $event->refund = new \Ingenico\Connect\Sdk\Domain\Refund\RefundResponse();
        $event->refund->refundOutput = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\RefundOutput();
        $event->refund->refundOutput->references = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\PaymentReferences();
        $event->refund->refundOutput->references->merchantOrderId = 888;

        $response = $this->resolver->getResponse($event);

        $this->assertEquals($event->refund, $response);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Merchant order id value is missing in Event response.
     */
    public function testGetMerchantOrderIdIfOrderIdMissed()
    {
        $event = new \Ingenico\Connect\Sdk\Domain\Webhooks\WebhooksEvent();
        $event->refund = new \Ingenico\Connect\Sdk\Domain\Refund\RefundResponse();
        $event->refund->refundOutput = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\RefundOutput();
        $event->refund->refundOutput->references = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\PaymentReferences();
        $event->refund->refundOutput->references->merchantOrderId = 0;

        $this->resolver->getMerchantReference($event);
    }

    public function testGetMerchantOrderId()
    {
        $event = new \Ingenico\Connect\Sdk\Domain\Webhooks\WebhooksEvent();
        $event->refund = new \Ingenico\Connect\Sdk\Domain\Refund\RefundResponse();
        $event->refund->refundOutput = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\RefundOutput();
        $event->refund->refundOutput->references = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\PaymentReferences();
        $event->refund->refundOutput->references->merchantOrderId = "888";

        $merchantOrderId = $this->resolver->getMerchantReference($event);

        $this->assertThat($merchantOrderId, $this->isType(PHPUnit_Framework_Constraint_IsType::TYPE_INT));
        $this->assertEquals(888, $merchantOrderId);
    }

    /**
     * @return array
     */
    public function incorrectEventsDataProvider()
    {
        $event = new \Ingenico\Connect\Sdk\Domain\Webhooks\WebhooksEvent();
        $event->refund = new \Ingenico\Connect\Sdk\Domain\Refund\RefundResponse();
        $event2 = clone $event;
        $event->refund->refundOutput = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\PaymentOutput();
        return [
            [new \Ingenico\Connect\Sdk\Domain\Webhooks\WebhooksEvent()],
            [$event],
            [$event2]
        ];
    }
}
