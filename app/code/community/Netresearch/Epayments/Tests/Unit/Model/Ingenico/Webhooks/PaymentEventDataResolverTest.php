<?php

class Netresearch_Epayments_Tests_Unit_Model_Ingenico_Webhooks_PaymentEventDataResolverTest
    extends PHPUnit_Framework_TestCase
{
    /** @var \Netresearch_Epayments_Model_Ingenico_Webhooks_PaymentEventDataResolver */
    private $resolver;

    protected function setUp()
    {
        parent::setUp();
        $this->resolver = new Netresearch_Epayments_Model_Ingenico_Webhooks_PaymentEventDataResolver();
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
    ) {
        $this->resolver->getResponse($incorrectEvent);
    }

    public function testGetResponse()
    {
        $event = new \Ingenico\Connect\Sdk\Domain\Webhooks\WebhooksEvent();
        $event->payment = new \Ingenico\Connect\Sdk\Domain\Payment\PaymentResponse();
        $event->payment->paymentOutput = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\PaymentOutput();
        $event->payment->paymentOutput->references = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\PaymentReferences();
        $event->payment->paymentOutput->references->merchantOrderId = 888;

        $response = $this->resolver->getResponse($event);

        $this->assertEquals($event->payment, $response);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Merchant order id value is missing in Event response.
     */
    public function testGetMerchantOrderIdIfOrderIdMissed()
    {
        $event = new \Ingenico\Connect\Sdk\Domain\Webhooks\WebhooksEvent();
        $event->payment = new \Ingenico\Connect\Sdk\Domain\Payment\PaymentResponse();
        $event->payment->paymentOutput = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\PaymentOutput();
        $event->payment->paymentOutput->references = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\PaymentReferences();
        $event->payment->paymentOutput->references->merchantOrderId = 0;

        $this->resolver->getMerchantReference($event);
    }

    public function testGetMerchantOrderId()
    {
        $event = new \Ingenico\Connect\Sdk\Domain\Webhooks\WebhooksEvent();
        $event->payment = new \Ingenico\Connect\Sdk\Domain\Payment\PaymentResponse();
        $event->payment->paymentOutput = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\PaymentOutput();
        $event->payment->paymentOutput->references = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\PaymentReferences();
        $event->payment->paymentOutput->references->merchantOrderId = "888";

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
        $event->payment = new \Ingenico\Connect\Sdk\Domain\Payment\PaymentResponse();
        $event2 = clone $event;
        $event->payment->paymentOutput = new \Ingenico\Connect\Sdk\Domain\Payment\Definitions\RefundOutput();
        return array(
            array(new \Ingenico\Connect\Sdk\Domain\Webhooks\WebhooksEvent()),
            array($event),
            array($event2)
        );
    }
}
