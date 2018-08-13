<?php

class Netresearch_Epayments_Tests_Unit_Model_Ingenico_WebhooksTest extends PHPUnit_Framework_TestCase
{
    /** @var Netresearch_Epayments_Model_Ingenico_Webhooks */
    private $webhooks;

    /** @var Netresearch_Epayments_Model_Ingenico_Webhooks_HelperAdapter|PHPUnit_Framework_MockObject_MockObject */
    private $webhooksHelperAdapter;

    /** @var Netresearch_Epayments_Model_Ingenico_StatusFactory|PHPUnit_Framework_MockObject_MockObject */
    private $statusFactory;

    /** @var Mage_Sales_Model_Order|PHPUnit_Framework_MockObject_MockObject */
    private $orderModel;

    protected function setUp()
    {
        parent::setUp();
        $this->webhooksHelperAdapter = $this->getMockBuilder(Netresearch_Epayments_Model_Ingenico_Webhooks_HelperAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->statusFactory = $this->getMockBuilder('Netresearch_Epayments_Model_Ingenico_StatusFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderModel = $this->getMockBuilder('Mage_Sales_Model_Order')
            ->disableOriginalConstructor()
            ->getMock();

        $this->webhooks = new Netresearch_Epayments_Model_Ingenico_Webhooks(
            array(
                'webhooksHelperAdapter' => $this->webhooksHelperAdapter,
                'statusFactory' => $this->statusFactory,
                'orderModel' => $this->orderModel
            )
        );
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage System can not load order mentioned in the Event.
     */
    public function testHandleIfOrderDoesNotExist()
    {
        $orderId = 123;

        $request = new Netresearch_Epayments_Model_Ingenico_Webhooks_RequestContext(
            ['headers' => ['header_key' =>'header_value'], 'body' => 'body_content']
        );

        $event = new \Ingenico\Connect\Sdk\Domain\Webhooks\WebhooksEvent();

        $eventDataResolver = $this->getMockBuilder(Netresearch_Epayments_Model_Ingenico_Webhooks_EventDataResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $eventDataResolver->expects($this->once())->method('getMerchantReference')
            ->with($event)->will($this->returnValue($orderId));

        $this->orderModel->expects($this->once())->method('unsetData')->with($this->isEmpty())->willReturnSelf();
        $this->orderModel->expects($this->once())
            ->method('load')->with($orderId)
            ->willReturnSelf();
        $this->orderModel->expects($this->any())->method('getId')->will($this->returnValue(0));

        $this->webhooksHelperAdapter->expects($this->once())
            ->method('unmarshal')
            ->with('body_content', ['header_key' =>'header_value'])
            ->will($this->returnValue($event));

        $this->webhooks->handle($request, $eventDataResolver);
    }

    public function testHandle()
    {
        $orderId = 126;

        $this->orderModel->expects($this->once())->method('unsetData')->with($this->isEmpty())->willReturnSelf();
        $this->orderModel->expects($this->once())->method('load')->with($orderId)->will($this->returnSelf());
        $this->orderModel->expects($this->any())->method('getId')->will($this->returnValue($orderId));

        $event = new \Ingenico\Connect\Sdk\Domain\Webhooks\WebhooksEvent();
        $eventResponse = $this->getMockBuilder(\Ingenico\Connect\Sdk\Domain\Definitions\AbstractOrderStatus::class)
            ->disableOriginalConstructor()
            ->getMock();

        $status = $this->getMockBuilder(Netresearch_Epayments_Model_Ingenico_StatusInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $status->expects($this->once())->method('apply')->with($this->orderModel);

        $this->webhooksHelperAdapter->expects($this->once())
            ->method('unmarshal')->with('body_content', ['header_key' =>'header_value'])
            ->will($this->returnValue($event));

        $this->statusFactory->expects($this->once())->method('create')->with($eventResponse)
            ->will($this->returnValue($status));

        $request = new Netresearch_Epayments_Model_Ingenico_Webhooks_RequestContext(
            ['headers' => ['header_key' =>'header_value'], 'body' => 'body_content']
        );

        $eventDataResolver = $this->getMockBuilder(Netresearch_Epayments_Model_Ingenico_Webhooks_EventDataResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $eventDataResolver->expects($this->once())->method('getResponse')
            ->with($event)->will($this->returnValue($eventResponse));
        $eventDataResolver->expects($this->once())->method('getMerchantReference')
            ->with($event)->will($this->returnValue($orderId));

        $this->webhooks->handle($request, $eventDataResolver);
    }
}
