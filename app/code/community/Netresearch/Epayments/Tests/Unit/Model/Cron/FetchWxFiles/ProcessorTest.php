<?php

/**
 * Class Netresearch_Epayments_Tests_Unit_Model_Cron_FetchWxFiles_ProcessorTest
 */
class Netresearch_Epayments_Tests_Unit_Model_Cron_FetchWxFiles_ProcessorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Netresearch_Epayments_Model_WxTransfer_Client|PHPUnit_Framework_MockObject_MockObject
     */
    private $wxClient;

    /**
     * @var Netresearch_Epayments_Model_Cron_FetchWxFiles_Logger|PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var Netresearch_Epayments_Model_Cron_FetchWxFiles_StatusUpdateResolver|PHPUnit_Framework_MockObject_MockObject
     */
    private $statusUpdateResolver;

    /**
     * @var Netresearch_Epayments_Model_Ingenico_GlobalCollect_StatusBuilder|PHPUnit_Framework_MockObject_MockObject
     */
    private $statusBuilder;

    /**
     * @var Netresearch_Epayments_Model_Ingenico_GlobalCollect_Wx_DataRecord|PHPUnit_Framework_MockObject_MockObject
     */
    private $sampleDataRecord;

    public function setUp()
    {
        parent::setUp();
        $this->wxClient = $this->getMockBuilder(Netresearch_Epayments_Model_WxTransfer_Client::class)
            ->disableOriginalConstructor()
            ->setMethods(array('loadDailyWx'))
            ->getMock();
        $this->logger = $this->getMockBuilder(
            Netresearch_Epayments_Model_Cron_FetchWxFiles_Logger::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->statusUpdateResolver = $this->getMockBuilder(
            Netresearch_Epayments_Model_Cron_FetchWxFiles_StatusUpdateResolver::class
        )
            ->disableOriginalConstructor()
            ->setMethods(array('resolveBatch'))
            ->getMock();
        $this->sampleDataRecord = $this->getMockBuilder(
            Netresearch_Epayments_Model_Ingenico_GlobalCollect_Wx_DataRecord::class
        )
            ->getMock();
        $this->statusBuilder = $this->getMockBuilder(
            Netresearch_Epayments_Model_Ingenico_GlobalCollect_StatusBuilder::class
        )
            ->disableOriginalConstructor()
            ->setMethods(array('create'))
            ->getMock();
        $this->statusBuilder->method('create')->willReturn($this->sampleDataRecord);
    }

    public function testProcess()
    {
        $validRecordKey = '000000060';
        $validRecordCategory = 'A';

        $wxFile = "<?xml version='1.0' encoding='utf-8'?>
<tns:report xmlns:tns='https://wx.v2_6.globalcollect.com'>
<tns:AccountID>227</tns:AccountID>
<tns:Filename>000000022720180414.010236</tns:Filename>
<tns:FilenameExtension>wxt</tns:FilenameExtension>
<tns:DateProduction>2018-04-14</tns:DateProduction>
<tns:SerialNumber/>
<tns:PeriodFrom>2018-04-14</tns:PeriodFrom>
<tns:PeriodTo>2018-04-14</tns:PeriodTo>
<tns:CutoffTime>09:00:00</tns:CutoffTime>
<tns:Version>01.02.36</tns:Version>
<tns:DataRecord>
<tns:MerchantID>227</tns:MerchantID>
<tns:OrderID>1000002778</tns:OrderID>
<tns:EffortID>1</tns:EffortID>
<tns:AttemptID>1</tns:AttemptID>
<tns:PaymentData>
<tns:Recordcategory>X</tns:Recordcategory>
<tns:Recordtype>AC</tns:Recordtype>
<tns:PaymentReference>0</tns:PaymentReference>
<tns:AdditionalReference>000000059</tns:AdditionalReference>
<tns:CurrencyLocal>USD</tns:CurrencyLocal>
<tns:AmountLocal>2300</tns:AmountLocal>
<tns:PaymentCountry>DE</tns:PaymentCountry>
<tns:PaymentStatus>99999</tns:PaymentStatus>
<tns:TransactionDateTime>2018-04-13T14:31:46</tns:TransactionDateTime>
<tns:CardNumber>************1111</tns:CardNumber>
<tns:ExpiryDate>0525</tns:ExpiryDate>
<tns:PaymentGroupId>10</tns:PaymentGroupId>
<tns:PaymentMethodId>1</tns:PaymentMethodId>
<tns:PaymentProductId>1</tns:PaymentProductId>
<tns:IIN>411111</tns:IIN>
<tns:PaymentProcessorID>222</tns:PaymentProcessorID>
<tns:MID>99840100101</tns:MID>
<tns:AuthorizationCodePayment>654321</tns:AuthorizationCodePayment>
<tns:FraudResult>A</tns:FraudResult>
<tns:FraudCode>150</tns:FraudCode>
<tns:FraudStatus>ACCEPT</tns:FraudStatus>
</tns:PaymentData>
<tns:ThirdPartyData/>
<tns:MerchantData/>
<tns:CustomerData>
<tns:CustomerId>4</tns:CustomerId>
</tns:CustomerData>
<tns:AirlineData>
<tns:AirlineLegs/>
</tns:AirlineData>
</tns:DataRecord>
<tns:DataRecord>
<tns:MerchantID>227</tns:MerchantID>
<tns:OrderID>1000002779</tns:OrderID>
<tns:EffortID>1</tns:EffortID>
<tns:AttemptID>1</tns:AttemptID>
<tns:PaymentData>
<tns:Recordcategory>$validRecordCategory</tns:Recordcategory>
<tns:Recordtype>AC</tns:Recordtype>
<tns:PaymentReference>022700008719</tns:PaymentReference>
<tns:AdditionalReference>$validRecordKey</tns:AdditionalReference>
<tns:CurrencyLocal>USD</tns:CurrencyLocal>
<tns:AmountLocal>16100</tns:AmountLocal>
<tns:PaymentCountry>AT</tns:PaymentCountry>
<tns:PaymentStatus>99999</tns:PaymentStatus>
<tns:TransactionDateTime>2018-04-13T15:28:00</tns:TransactionDateTime>
<tns:PaymentGroupId>80</tns:PaymentGroupId>
<tns:PaymentMethodId>15</tns:PaymentMethodId>
<tns:PaymentProductId>1501</tns:PaymentProductId>
</tns:PaymentData>
<tns:ThirdPartyData/>
<tns:MerchantData/>
<tns:CustomerData>
<tns:CustomerId>4</tns:CustomerId>
</tns:CustomerData>
<tns:AirlineData>
<tns:AirlineLegs/>
</tns:AirlineData>
</tns:DataRecord>
<tns:DataRecord>
<tns:MerchantID>227</tns:MerchantID>
<tns:OrderID>1000002777</tns:OrderID>
<tns:EffortID>1</tns:EffortID>
<tns:AttemptID>1</tns:AttemptID>
<tns:PaymentData>
<tns:Recordcategory>X</tns:Recordcategory>
<tns:Recordtype>AC</tns:Recordtype>
<tns:PaymentReference>0</tns:PaymentReference>
<tns:AdditionalReference>000000058</tns:AdditionalReference>
<tns:CurrencyLocal>USD</tns:CurrencyLocal>
<tns:AmountLocal>17900</tns:AmountLocal>
<tns:PaymentCountry>US</tns:PaymentCountry>
<tns:PaymentStatus>99999</tns:PaymentStatus>
<tns:TransactionDateTime>2018-04-13T14:29:20</tns:TransactionDateTime>
<tns:CardNumber>************5487</tns:CardNumber>
<tns:ExpiryDate>1020</tns:ExpiryDate>
<tns:PaymentGroupId>10</tns:PaymentGroupId>
<tns:PaymentMethodId>1</tns:PaymentMethodId>
<tns:PaymentProductId>1</tns:PaymentProductId>
<tns:IIN>498833</tns:IIN>
<tns:IssuerCountry>IE</tns:IssuerCountry>
<tns:PaymentProcessorID>222</tns:PaymentProcessorID>
<tns:MID>99840100101</tns:MID>
<tns:AuthorizationCodePayment>654321</tns:AuthorizationCodePayment>
<tns:FraudResult>C</tns:FraudResult>
<tns:FraudCode>330</tns:FraudCode>
<tns:FraudStatus>CHALLENGE</tns:FraudStatus>
</tns:PaymentData>
<tns:ThirdPartyData/>
<tns:MerchantData/>
<tns:CustomerData>
<tns:CustomerId>4</tns:CustomerId>
</tns:CustomerData>
<tns:AirlineData>
<tns:AirlineLegs/>
</tns:AirlineData>
</tns:DataRecord>
<tns:DataRecord>
<tns:MerchantID>227</tns:MerchantID>
<tns:OrderID>1000002780</tns:OrderID>
<tns:EffortID>1</tns:EffortID>
<tns:AttemptID>1</tns:AttemptID>
<tns:PaymentData>
<tns:Recordcategory>X</tns:Recordcategory>
<tns:Recordtype>AC</tns:Recordtype>
<tns:PaymentReference>0</tns:PaymentReference>
<tns:AdditionalReference>000000061</tns:AdditionalReference>
<tns:CurrencyLocal>USD</tns:CurrencyLocal>
<tns:AmountLocal>10100</tns:AmountLocal>
<tns:PaymentCountry>DE</tns:PaymentCountry>
<tns:PaymentStatus>99999</tns:PaymentStatus>
<tns:TransactionDateTime>2018-04-13T16:43:35</tns:TransactionDateTime>
<tns:CardNumber>************1111</tns:CardNumber>
<tns:ExpiryDate>0120</tns:ExpiryDate>
<tns:PaymentGroupId>10</tns:PaymentGroupId>
<tns:PaymentMethodId>1</tns:PaymentMethodId>
<tns:PaymentProductId>1</tns:PaymentProductId>
<tns:IIN>411111</tns:IIN>
<tns:PaymentProcessorID>222</tns:PaymentProcessorID>
<tns:MID>99840100101</tns:MID>
<tns:AuthorizationCodePayment>654321</tns:AuthorizationCodePayment>
<tns:FraudResult>A</tns:FraudResult>
<tns:FraudCode>150</tns:FraudCode>
<tns:FraudStatus>ACCEPT</tns:FraudStatus>
</tns:PaymentData>
<tns:ThirdPartyData/>
<tns:MerchantData/>
<tns:CustomerData>
<tns:CustomerId>4</tns:CustomerId>
</tns:CustomerData>
<tns:AirlineData>
<tns:AirlineLegs/>
</tns:AirlineData>
</tns:DataRecord>
<tns:DataRecord>
<tns:MerchantID>227</tns:MerchantID>
<tns:OrderID>1000002776</tns:OrderID>
<tns:EffortID>1</tns:EffortID>
<tns:AttemptID>1</tns:AttemptID>
<tns:PaymentData>
<tns:Recordcategory>X</tns:Recordcategory>
<tns:Recordtype>CD</tns:Recordtype>
<tns:PaymentReference>022700008709</tns:PaymentReference>
<tns:AdditionalReference>000000057</tns:AdditionalReference>
<tns:CurrencyLocal>USD</tns:CurrencyLocal>
<tns:AmountLocal>33200</tns:AmountLocal>
<tns:PaymentCountry>DE</tns:PaymentCountry>
<tns:PaymentStatus>130</tns:PaymentStatus>
<tns:TransactionDateTime>2018-04-13T14:26:19</tns:TransactionDateTime>
<tns:ErrorCodes>400702</tns:ErrorCodes>
<tns:ErrorMessages>FAILED_AT_BANK</tns:ErrorMessages>
<tns:PaymentGroupId>60</tns:PaymentGroupId>
<tns:PaymentMethodId>8</tns:PaymentMethodId>
<tns:PaymentProductId>840</tns:PaymentProductId>
</tns:PaymentData>
<tns:ThirdPartyData/>
<tns:MerchantData/>
<tns:CustomerData>
<tns:CustomerId>4</tns:CustomerId>
</tns:CustomerData>
<tns:AirlineData>
<tns:AirlineLegs/>
</tns:AirlineData>
</tns:DataRecord>
<tns:DataRecord>
<tns:MerchantID>227</tns:MerchantID>
<tns:OrderID>1000002775</tns:OrderID>
<tns:EffortID>1</tns:EffortID>
<tns:AttemptID>1</tns:AttemptID>
<tns:PaymentData>
<tns:Recordcategory>X</tns:Recordcategory>
<tns:Recordtype>CD</tns:Recordtype>
<tns:PaymentReference>0</tns:PaymentReference>
<tns:AdditionalReference>000000054</tns:AdditionalReference>
<tns:CurrencyLocal>USD</tns:CurrencyLocal>
<tns:AmountLocal>33200</tns:AmountLocal>
<tns:PaymentStatus>100</tns:PaymentStatus>
<tns:TransactionDateTime>2018-04-13T14:23:28</tns:TransactionDateTime>
<tns:ErrorCodes>430327</tns:ErrorCodes>
<tns:ErrorMessages>INVALID_AMOUNT</tns:ErrorMessages>
<tns:PaymentGroupId>10</tns:PaymentGroupId>
<tns:PaymentMethodId>1</tns:PaymentMethodId>
<tns:PaymentProductId>1</tns:PaymentProductId>
</tns:PaymentData>
<tns:ThirdPartyData/>
<tns:MerchantData/>
<tns:CustomerData>
<tns:CustomerId>4</tns:CustomerId>
</tns:CustomerData>
<tns:AirlineData>
<tns:AirlineLegs/>
</tns:AirlineData>
</tns:DataRecord>
<tns:Totals>
<tns:NumberOfRecords>6</tns:NumberOfRecords>
<tns:NumberOfSentInvoices>0</tns:NumberOfSentInvoices>
<tns:NumberOfRejectedInvoices>0</tns:NumberOfRejectedInvoices>
<tns:NumberOfInvoicePayments>0</tns:NumberOfInvoicePayments>
<tns:NumberOfConvertedInvoicePayments>0</tns:NumberOfConvertedInvoicePayments>
<tns:NumberOfCorrectionsOnPayments>0</tns:NumberOfCorrectionsOnPayments>
<tns:NumberOfReversals>0</tns:NumberOfReversals>
<tns:NumberOfCorrectionsOnReversals>0</tns:NumberOfCorrectionsOnReversals>
<tns:NumberOfRejectedCardPayments>0</tns:NumberOfRejectedCardPayments>
<tns:NumberOfCardRefunds>0</tns:NumberOfCardRefunds>
<tns:NumberOfCorrectedCardRefunds>0</tns:NumberOfCorrectedCardRefunds>
<tns:NumberOfCollectedCardOnline>0</tns:NumberOfCollectedCardOnline>
<tns:NumberOfCollectedCardOffline>0</tns:NumberOfCollectedCardOffline>
<tns:NumberOfCollectedCardRefunds>0</tns:NumberOfCollectedCardRefunds>
<tns:NumberOfCollectedCardChargebacks>0</tns:NumberOfCollectedCardChargebacks>
<tns:NumberOfrejectedCardOnline>0</tns:NumberOfrejectedCardOnline>
<tns:NumberOfrejectedCardOffline>0</tns:NumberOfrejectedCardOffline>
<tns:NumberOfDirectDebitOrdersRejectedGlobalCollect>0</tns:NumberOfDirectDebitOrdersRejectedGlobalCollect>
<tns:NumberOfDirectDebitOrdersRejectedByBank>0</tns:NumberOfDirectDebitOrdersRejectedByBank>
<tns:NumberOfCollectedDirectDebitPayments>0</tns:NumberOfCollectedDirectDebitPayments>
<tns:NumberOfreversedDirectDebitPayments>0</tns:NumberOfreversedDirectDebitPayments>
<tns:NumberOfWithdrawnChargebacks>0</tns:NumberOfWithdrawnChargebacks>
<tns:NumberOfCardPaymentsSentOnlineForSettlement>0</tns:NumberOfCardPaymentsSentOnlineForSettlement>
<tns:NumberOfCardRefundsSentOnlineForSettlement>0</tns:NumberOfCardRefundsSentOnlineForSettlement>
<tns:NumberOfOnlineCapturedNonCardPayments>0</tns:NumberOfOnlineCapturedNonCardPayments>
<tns:NumberOfSuccessfulCreatedReversals>0</tns:NumberOfSuccessfulCreatedReversals>
<tns:NumberOfTimedOutTransactions>0</tns:NumberOfTimedOutTransactions>
<tns:NumberOfSuccessfulCreatedRefunds>0</tns:NumberOfSuccessfulCreatedRefunds>
<tns:NumberOfdeclinedTransactions>2</tns:NumberOfdeclinedTransactions>
<tns:NumberOfFailedCapturesByWebcollect>0</tns:NumberOfFailedCapturesByWebcollect>
<tns:NumberOfCanceledTransactions>4</tns:NumberOfCanceledTransactions>
</tns:Totals>
<tns:NumberOfRecords>6</tns:NumberOfRecords>
</tns:report>";
        $xml = new DOMDocument('1.0', 'utf-8');
        $xml->loadXML($wxFile, LIBXML_PARSEHUGE);

        $this->wxClient->expects($this->once())->method('loadDailyWx')->willReturn($xml);

        $this->statusBuilder
            ->expects($this->once())
            ->method('create');

        $this->statusUpdateResolver
            ->expects($this->once())
            ->method('resolveBatch')
            ->with(array($validRecordKey => $this->sampleDataRecord));

        $subject = new Netresearch_Epayments_Model_Cron_FetchWxFiles_Processor(
            array(
                'wxClient' => $this->wxClient,
                'logger' => $this->logger,
                'statusBuilder' => $this->statusBuilder,
                'statusUpdateResolver' => $this->statusUpdateResolver,
            )
        );
        $subject->process(0, '');
    }
}
