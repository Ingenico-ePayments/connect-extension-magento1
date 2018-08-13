<?php

use Ingenico\Connect\Sdk\Domain\Errors\Definitions\APIError;
use Ingenico\Connect\Sdk\Domain\Payment\Definitions\PaymentStatusOutput;
use Ingenico\Connect\Sdk\Domain\Payment\PaymentResponse;

class Netresearch_Epayments_Tests_Unit_Model_StatusResponseManagerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Netresearch_Epayments_Model_StatusResponseManager
     */
    protected $statusResponseManager;

    /**
     * @var \Mage_Sales_Model_Order_Payment_Transaction|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $testTransaction;

    /**
     * @var \Mage_Sales_Model_Order_Payment
     */
    protected $testPayment;

    /**
     * @var PaymentResponse
     */
    protected $testOrderStatus;

    /**
     * @var Mage_Sales_Model_Order|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $testOrder;

    /**
     * @var string
     */
    protected $testTransactionId = '13579';

    public function setUp()
    {
        $this->testOrder = $this->getMockBuilder(Mage_Sales_Model_Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->testTransaction = $this->getMockBuilder(\Mage_Sales_Model_Order_Payment_Transaction::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->testTransaction->method('getId')->willReturn(1);

        $this->testPayment = $this->getMockBuilder(\Mage_Sales_Model_Order_Payment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->testPayment->method('getTransaction')->willReturn($this->testTransaction);
        $this->testPayment->method('getOrder')->willReturn($this->testOrder);

        $this->statusResponseManager = new \Netresearch_Epayments_Model_StatusResponseManager();

        $this->testOrderStatus = $this->buildTestOrderStatus();
    }

    public function testGet()
    {
        $this->testTransaction
            ->expects($this->at(0))
            ->method('getAdditionalInformation')
            ->with('gc_response_class')
            ->willReturn(get_class($this->testOrderStatus));
        $this->testTransaction
            ->expects($this->at(1))
            ->method('getAdditionalInformation')
            ->with('gc_response_object')
            ->willReturn($this->testOrderStatus->toJson());

        $result = $this->statusResponseManager->get(
            $this->testPayment,
            $this->testTransactionId
        );

        $this->assertEquals($this->testOrderStatus, $result);
    }

    /**
     * @throws \Mage_Core_Exception
     */
    public function testSet()
    {
        $expectedRawDetails = array(
            'status' => 'CAPTURE_REQUESTED',
            'errors' => 'EXTERNAL_AQUIRER_ERROR, ANOTHER_ERROR',
            'statusCategory' => 'PENDING_CONNECT_OR_3RD_PARTY',
            'statusCode' => 92,
        );

        $this->testTransaction
            ->expects($this->exactly(3))
            ->method('setAdditionalInformation');
        $this->testTransaction
            ->expects($this->at(0))
            ->method('setAdditionalInformation')
            ->with('gc_response_class', get_class($this->testOrderStatus));
        $this->testTransaction
            ->expects($this->at(1))
            ->method('setAdditionalInformation')
            ->with('gc_response_object', $this->testOrderStatus->toJson());
        $this->testTransaction
            ->expects($this->at(2))
            ->method('setAdditionalInformation')
            ->with('raw_details_info', $expectedRawDetails);

        $this->statusResponseManager->set(
            $this->testPayment,
            $this->testTransactionId,
            $this->testOrderStatus
        );
    }

    /**
     * @throws Mage_Core_Exception
     */
    public function testSetUnknownStatus()
    {
        /**
         * The call to Mage is supposed to throw an exception anyways so we use this workaround.
         */
        $this->setExpectedException(Error::class, 'Class \'Mage\' not found');
        $this->testOrderStatus->status = null;
        $this->statusResponseManager->set($this->testPayment, $this->testTransactionId, $this->testOrderStatus);
    }

    /**
     * @throws Mage_Core_Exception
     */
    public function testSetUnknownStatus2()
    {
        $this->setExpectedException(Error::class, 'Class \'Mage\' not found');
        $this->testOrderStatus->statusOutput = null;
        $this->statusResponseManager->set($this->testPayment, $this->testTransactionId, $this->testOrderStatus);
    }

    /**
     * @return PaymentResponse
     */
    protected function buildTestOrderStatus()
    {
        /** @var APIError $apiError */
        $apiError = new APIError();
        $apiError->id = 'EXTERNAL_AQUIRER_ERROR';
        $apiError->category = 'IO_ERROR';
        $apiError->message = '10008 : ';
        $apiError->httpStatusCode = 402;

        $apiErrorAlt = new APIError();
        $apiErrorAlt->id = 'ANOTHER_ERROR';
        $apiErrorAlt->category = 'IO_ERROR';
        $apiErrorAlt->message = 'problem';
        $apiErrorAlt->httpStatusCode = 100;

        $statusOutput = new PaymentStatusOutput();
        $statusOutput->errors = array($apiError, $apiErrorAlt);
        $statusOutput->statusCategory = 'PENDING_CONNECT_OR_3RD_PARTY';
        $statusOutput->statusCode = 92;

        $status = new PaymentResponse();
        $status->status = 'CAPTURE_REQUESTED';
        $status->statusOutput = $statusOutput;

        return $status;
    }
}
