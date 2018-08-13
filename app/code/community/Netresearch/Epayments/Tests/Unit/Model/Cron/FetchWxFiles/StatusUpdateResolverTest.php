<?php

/**
 * Class Netresearch_Epayments_Tests_Unit_Model_Cron_FetchWxFiles_StatusUpdateResolverTest
 */
class Netresearch_Epayments_Tests_Unit_Model_Cron_FetchWxFiles_StatusUpdateResolverTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Ingenico\Connect\Sdk\Domain\Definitions\AbstractOrderStatus|\PHPUnit_Framework_MockObject_MockObject
     */
    private $ingenicoStatus;

    /**
     * @var Netresearch_Epayments_Model_Ingenico_StatusInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderStatus;

    /**
     * @var Netresearch_Epayments_Model_Ingenico_GlobalCollect_OrderStatusFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $statusFactory;

    /**
     * @var Mage_Sales_Model_Order|\PHPUnit_Framework_MockObject_MockObject
     */
    private $order;

    /**
     * @var Netresearch_Epayments_Model_Resource_Order_Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderCollection;

    /**
     * @var Netresearch_Epayments_Model_Cron_FetchWxFiles_Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    public function setUp()
    {
        $this->ingenicoStatus = $this->getMockBuilder(AbstractOrderStatus::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderStatus = $this->getMockBuilder(StatusInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(array('apply'))
            ->getMock();

        $this->statusFactory = $this->getMockBuilder(StatusFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(array('create'))
            ->getMock();
        $this->statusFactory->method('create')->willReturn($this->orderStatus);

        $this->order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->setMethods(array('getIncrementId', 'getEntityId'))
            ->getMock();

        $this->orderCollection = $this->getMockBuilder(Netresearch_Epayments_Model_Resource_Order_Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(array('addFieldToFilter', 'getItems', 'save'))
            ->getMock();
        $this->orderCollection->method('getItems')->willReturn(array($this->order));

        $this->logger = $this->getMockBuilder(Monolog::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testResolveBatch()
    {
        $orderIncrementId = '123456';
        $orderEntityId = '123';
        $this->order->method('getIncrementId')->willReturn($orderIncrementId);
        $this->order->method('getEntityId')->willReturn($orderEntityId);
        $statusList = [$orderIncrementId => $this->ingenicoStatus];

        $this->orderStatus->expects($this->once())->method('apply')->with($this->order);
        $this->orderCollection->expects($this->once())->method('save');

        $subject = new Netresearch_Epayments_Model_Cron_FetchWxFiles_StatusUpdateResolver(
            array(
                'statusFactory' => $this->statusFactory,
                'orderCollection' => $this->orderCollection,
                'logger' => $this->logger
            )
        );

        $result = $subject->resolveBatch($statusList);

        $this->assertEquals($result, [$orderEntityId => $orderIncrementId]);
    }
}
