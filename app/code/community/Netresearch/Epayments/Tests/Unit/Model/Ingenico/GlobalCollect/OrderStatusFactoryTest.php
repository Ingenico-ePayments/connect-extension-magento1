<?php

use Netresearch_Epayments_Model_Ingenico_StatusInterface as StatusInterface;
use Netresearch_Epayments_Model_Ingenico_GlobalCollect_Wx_DataRecord as DataRecord;
use Netresearch_Epayments_Model_Ingenico_GlobalCollect_ProductMapper as ProductMapper;
use Netresearch_Epayments_Model_Ingenico_GlobalCollect_OrderStatusFactory as OrderStatusFactory;
use Ingenico\Connect\Sdk\Domain\Payment\Definitions\Payment;
use Ingenico\Connect\Sdk\Domain\Refund\Definitions\RefundResult;

/**
 * Class Netresearch_Epayments_Tests_Unit_Model_Ingenico_GlobalCollect_OrderStatusFactoryTest
 */
class Netresearch_Epayments_Tests_Unit_Model_Ingenico_GlobalCollect_OrderStatusFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $status = StatusInterface::REDIRECTED;
        $merchantId = '1234';
        $orderId = '4321';
        $paymentAmount = '1001';
        $attemptId = '99999';
        $effortId = '55555';
        $dataRecord = DataRecord::fromArray(
            array(
                'MerchantID' => $merchantId,
                'OrderID' => $orderId,
                'AttemptID' => $attemptId,
                'EffortID' => $effortId,
                'PaymentData' => array(
                    'PaymentStatus' => 20, // resolves to status REDIRECTED
                    'PaymentGroupId' => ProductMapper::METHOD_PREPAID,
                    'OrderID' => $orderId,
                    'AmountDelivered' => $paymentAmount,
                ),
            )
        );
        $subject = new OrderStatusFactory();

        $result = $subject->create($status, $dataRecord);
        $this->assertInstanceOf(Payment::class, $result);
        $this->assertEquals(20, $result->statusOutput->statusCode);
        $this->assertEquals($paymentAmount, $result->paymentOutput->amountOfMoney->amount);
        $this->assertEquals("000000{$merchantId}{$orderId}{$effortId}{$attemptId}", $result->id);
    }

    public function testCreateRefund()
    {
        $status = StatusInterface::REFUND_REQUESTED;
        $merchantId = '1234';
        $orderId = '4321';
        $paymentAmount = '1001';
        $attemptId = '99999';
        $effortId = '55555';
        $dataRecord = DataRecord::fromArray(
            array(
                'MerchantID' => $merchantId,
                'OrderID' => $orderId,
                'AttemptID' => $attemptId,
                'EffortID' => $effortId,
                'PaymentData' => array(
                    'PaymentStatus' => 800, // resolves to status REFUND_REQUESTED
                    'PaymentGroupId' => ProductMapper::METHOD_BANK_REFUNDS,
                    'OrderID' => $orderId,
                    'AmountDelivered' => $paymentAmount,
                ),
            )
        );
        $subject = new OrderStatusFactory();

        $result = $subject->create($status, $dataRecord);
        $this->assertInstanceOf(RefundResult::class, $result);
        $this->assertEquals(800, $result->statusOutput->statusCode);
        $this->assertEquals($paymentAmount, $result->refundOutput->amountPaid);
        $this->assertEquals("000000{$merchantId}{$orderId}{$effortId}{$attemptId}", $result->id);
    }
}
