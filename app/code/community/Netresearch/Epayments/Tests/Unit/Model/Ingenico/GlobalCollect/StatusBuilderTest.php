<?php

use Netresearch_Epayments_Model_Ingenico_GlobalCollect_Wx_DataRecord as DataRecord;
use Netresearch_Epayments_Model_Ingenico_GlobalCollect_OrderStatusFactory as OrderStatusFactory;
use Netresearch_Epayments_Model_Ingenico_StatusInterface as StatusInterface;
use Netresearch_Epayments_Model_Ingenico_GlobalCollect_ProductMapper as ProductMapper;
use Netresearch_Epayments_Model_Ingenico_GlobalCollect_StatusBuilder as StatusBuilder;

/**
 * Class Netresearch_Epayments_Tests_Unit_Model_Ingenico_GlobalCollect_StatusBuilderTest
 */
class Netresearch_Epayments_Tests_Unit_Model_Ingenico_GlobalCollect_StatusBuilderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var OrderStatusFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderStatusFactory;

    public function setUp()
    {
        $this->orderStatusFactory = $this->getMockBuilder(OrderStatusFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(array('create'))
            ->getMock();
    }

    public function testCreateApplicableRecord()
    {
        $dataRecord = DataRecord::fromArray(
            array(
                'PaymentData' => array(
                    'PaymentStatus' => 20, // should resolve to status REDIRECTED
                    'PaymentGroupId' => ProductMapper::METHOD_PREPAID,
                )
            )
        );
        $subject = new StatusBuilder(array('orderStatusFactory' => $this->orderStatusFactory));

        $this->orderStatusFactory
            ->expects($this->once())
            ->method('create')
            ->with(StatusInterface::REDIRECTED, $dataRecord)
            ->willReturn(true);

        $result = $subject->create($dataRecord);
        $this->assertTrue($result);
    }

    public function testCreateInapplicableRecord()
    {
        $dataRecord = DataRecord::fromArray(
            array(
                'PaymentData' => array(
                    'PaymentStatus' => -1,
                    'PaymentGroupId' => ProductMapper::METHOD_CARDS,
                )
            )
        );
        $subject = new StatusBuilder(array('orderStatusFactory' => $this->orderStatusFactory));

        $result = $subject->create($dataRecord);
        $this->assertFalse($result);
    }

    /**
     * @expectedException Error
     * @expectedExceptionMessage Class 'Mage' not found
     */
    public function testCreateAmbiguousRecord()
    {
        $dataRecord = DataRecord::fromArray(
            array(
                'PaymentData' => array(
                    'PaymentStatus' => 800,
                    'PaymentGroupId' => ProductMapper::METHOD_CARDS,
                )
            )
        );
        $subject = new StatusBuilder(array('orderStatusFactory' => $this->orderStatusFactory));
        $subject->create($dataRecord);
    }
}
