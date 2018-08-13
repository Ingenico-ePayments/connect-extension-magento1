<?php

class Netresearch_Epayments_Model_Ingenico_GlobalCollect_Wx_PaymentData
{
    private $OrderId;
    private $EffordId;
    private $AttemptId;
    private $Recordcategory;
    private $Recordtype;
    private $PaymentReference;
    private $InvoiceNumber;
    private $AdditionalReference;
    private $MerchantOrderID;
    private $CurrencyDelivered;
    private $AmountDelivered;
    private $CurrencyLocal;
    private $AmountLocal;
    private $PaymentCountry;
    private $PaymentStatus;
    private $TransactionDateTime;
    private $RejectionReasonId;
    private $RejectionReasonDescription;
    private $RejectedByIndicator;
    private $DateCollect;
    private $ErrorCodes;
    private $ErrorMessages;
    private $CardNumber;
    private $ExpiryDate;
    private $IssueNumber;
    private $SourceID;
    private $AuthorizationCode;
    private $PaymentGroupId;
    private $PaymentMethodId;
    private $PaymentProductId;
    private $IIN;
    private $IssuerCountry;
    private $PaymentProcessorID;
    private $MID;
    private $ReferenceOriginalPayment;
    private $NumberOfInstallments;
    private $AccountNumberDebtor;
    private $CreditCardCompany;
    private $UncleanIndicator;
    private $PaymentCurrency;
    private $PaymentAmount;
    private $CreditedCurrency;
    private $CreditedAmount;
    private $DebitedCurrency;
    private $DebitedAmount;
    private $CurrencyDue;
    private $AmountDue;
    private $DateDue;
    private $AuthorizationIndicator;
    private $AuthorizationCodePayment;
    private $OverOrUnderCurrencyLocal;
    private $OverOrUnderAmountLocal;
    private $DeductedCurrency;
    private $DeductedAmount;
    private $DeductedReasonID;
    private $DeductionReasonDescription;
    private $OriginalPaymentCurrency;
    private $OriginalPaymentAmount;
    private $OriginalPaymentID;
    private $AquirerReferenceNumber;
    private $OriginalOrderNumber;
    private $OriginalCustomerID;
    private $OriginalDateCollect;
    private $ChargeBackType;
    private $CaseID;
    private $ChargebackNumber;
    private $InstallmentNumber;
    private $InstallmentType;
    private $InstallmentPlan;
    private $PlatformIdNo;
    private $ProfileToken;
    private $FraudResult;
    private $FraudCode;
    private $FraudStatus;

    /**
     * @param $data
     * @return $this
     */
    public static function fromArray($data = array())
    {
        $instance = new self();
        foreach ($data as $key => $value) {
            if (property_exists($instance, $key)) {
                $instance->$key = $value;
            }
        }
        return $instance;
    }

    /**
     * @param \DOMElement $element
     * @return $this
     */
    public static function fromDomNode(\DOMElement $element)
    {
        $instance = new self();
        /** @var \DOMNode $item */
        foreach ($element->getElementsByTagName('*') as $item) {
            if (property_exists($instance, $item->localName)) {
                $property = $item->localName;
                $instance->$property = $item->nodeValue;
            }
        }
        return $instance;
    }

    /**
     * @return mixed
     */
    public function getAquirerReferenceNumber()
    {
        return $this->AquirerReferenceNumber;
    }

    /**
     * @return mixed
     */
    public function getOrderId()
    {
        return $this->OrderId;
    }

    /**
     * @return mixed
     */
    public function getEffordId()
    {
        return $this->EffordId;
    }

    /**
     * @return mixed
     */
    public function getAttemptId()
    {
        return $this->AttemptId;
    }

    /**
     * @return mixed
     */
    public function getInvoiceNumber()
    {
        return $this->InvoiceNumber;
    }

    /**
     * @return mixed
     */
    public function getRejectionReasonId()
    {
        return $this->RejectionReasonId;
    }

    /**
     * @return mixed
     */
    public function getRejectionReasonDescription()
    {
        return $this->RejectionReasonDescription;
    }

    /**
     * @return mixed
     */
    public function getRejectedByIndicator()
    {
        return $this->RejectedByIndicator;
    }

    /**
     * @return mixed
     */
    public function getDateCollect()
    {
        return $this->DateCollect;
    }

    /**
     * @return mixed
     */
    public function getErrorCodes()
    {
        return $this->ErrorCodes;
    }

    /**
     * @return mixed
     */
    public function getErrorMessages()
    {
        return $this->ErrorMessages;
    }

    /**
     * @return mixed
     */
    public function getCardNumber()
    {
        return $this->CardNumber;
    }

    /**
     * @return mixed
     */
    public function getExpiryDate()
    {
        return $this->ExpiryDate;
    }

    /**
     * @return mixed
     */
    public function getIssueNumber()
    {
        return $this->IssueNumber;
    }

    /**
     * @return mixed
     */
    public function getSourceID()
    {
        return $this->SourceID;
    }

    /**
     * @return mixed
     */
    public function getAuthorizationCode()
    {
        return $this->AuthorizationCode;
    }

    /**
     * @return mixed
     */
    public function getIIN()
    {
        return $this->IIN;
    }

    /**
     * @return mixed
     */
    public function getIssuerCountry()
    {
        return $this->IssuerCountry;
    }

    /**
     * @return mixed
     */
    public function getPaymentProcessorID()
    {
        return $this->PaymentProcessorID;
    }

    /**
     * @return mixed
     */
    public function getMID()
    {
        return $this->MID;
    }

    /**
     * @return mixed
     */
    public function getReferenceOriginalPayment()
    {
        return $this->ReferenceOriginalPayment;
    }

    /**
     * @return mixed
     */
    public function getNumberOfInstallments()
    {
        return $this->NumberOfInstallments;
    }

    /**
     * @return mixed
     */
    public function getAccountNumberDebtor()
    {
        return $this->AccountNumberDebtor;
    }

    /**
     * @return mixed
     */
    public function getCreditCardCompany()
    {
        return $this->CreditCardCompany;
    }

    /**
     * @return mixed
     */
    public function getDebitedCurrency()
    {
        return $this->DebitedCurrency;
    }

    /**
     * @return mixed
     */
    public function getDebitedAmount()
    {
        return $this->DebitedAmount;
    }

    /**
     * @return mixed
     */
    public function getAuthorizationIndicator()
    {
        return $this->AuthorizationIndicator;
    }

    /**
     * @return mixed
     */
    public function getAuthorizationCodePayment()
    {
        return $this->AuthorizationCodePayment;
    }

    /**
     * @return mixed
     */
    public function getDeductedCurrency()
    {
        return $this->DeductedCurrency;
    }

    /**
     * @return mixed
     */
    public function getDeductedAmount()
    {
        return $this->DeductedAmount;
    }

    /**
     * @return mixed
     */
    public function getDeductedReasonID()
    {
        return $this->DeductedReasonID;
    }

    /**
     * @return mixed
     */
    public function getDeductionReasonDescription()
    {
        return $this->DeductionReasonDescription;
    }

    /**
     * @return mixed
     */
    public function getOriginalPaymentCurrency()
    {
        return $this->OriginalPaymentCurrency;
    }

    /**
     * @return mixed
     */
    public function getOriginalPaymentAmount()
    {
        return $this->OriginalPaymentAmount;
    }

    /**
     * @return mixed
     */
    public function getOriginalPaymentID()
    {
        return $this->OriginalPaymentID;
    }

    /**
     * @return mixed
     */
    public function getOriginalOrderNumber()
    {
        return $this->OriginalOrderNumber;
    }

    /**
     * @return mixed
     */
    public function getOriginalCustomerID()
    {
        return $this->OriginalCustomerID;
    }

    /**
     * @return mixed
     */
    public function getOriginalDateCollect()
    {
        return $this->OriginalDateCollect;
    }

    /**
     * @return mixed
     */
    public function getChargeBackType()
    {
        return $this->ChargeBackType;
    }

    /**
     * @return mixed
     */
    public function getCaseID()
    {
        return $this->CaseID;
    }

    /**
     * @return mixed
     */
    public function getChargebackNumber()
    {
        return $this->ChargebackNumber;
    }

    /**
     * @return mixed
     */
    public function getInstallmentNumber()
    {
        return $this->InstallmentNumber;
    }

    /**
     * @return mixed
     */
    public function getInstallmentType()
    {
        return $this->InstallmentType;
    }

    /**
     * @return mixed
     */
    public function getInstallmentPlan()
    {
        return $this->InstallmentPlan;
    }

    /**
     * @return mixed
     */
    public function getProfileToken()
    {
        return $this->ProfileToken;
    }

    /**
     * @return mixed
     */
    public function getFraudResult()
    {
        return $this->FraudResult;
    }

    /**
     * @return mixed
     */
    public function getFraudCode()
    {
        return $this->FraudCode;
    }

    /**
     * @return mixed
     */
    public function getFraudStatus()
    {
        return $this->FraudStatus;
    }

    /**
     * @return mixed
     */
    public function getRecordcategory()
    {
        return $this->Recordcategory;
    }

    /**
     * @return mixed
     */
    public function getRecordtype()
    {
        return $this->Recordtype;
    }

    /**
     * @return mixed
     */
    public function getPaymentReference()
    {
        return $this->PaymentReference;
    }

    /**
     * @return mixed
     */
    public function getAdditionalReference()
    {
        return $this->AdditionalReference;
    }

    /**
     * @return mixed
     */
    public function getMerchantOrderID()
    {
        return $this->MerchantOrderID;
    }

    /**
     * @return mixed
     */
    public function getCurrencyDelivered()
    {
        return $this->CurrencyDelivered;
    }

    /**
     * @return mixed
     */
    public function getAmountDelivered()
    {
        return $this->AmountDelivered;
    }

    /**
     * @return mixed
     */
    public function getCurrencyLocal()
    {
        return $this->CurrencyLocal;
    }

    /**
     * @return mixed
     */
    public function getAmountLocal()
    {
        return $this->AmountLocal;
    }

    /**
     * @return mixed
     */
    public function getPaymentCountry()
    {
        return $this->PaymentCountry;
    }

    /**
     * @return mixed
     */
    public function getPaymentStatus()
    {
        return $this->PaymentStatus;
    }

    /**
     * @return mixed
     */
    public function getTransactionDateTime()
    {
        return $this->TransactionDateTime;
    }

    /**
     * @return mixed
     */
    public function getPaymentGroupId()
    {
        return $this->PaymentGroupId;
    }

    /**
     * @return mixed
     */
    public function getPaymentMethodId()
    {
        return $this->PaymentMethodId;
    }

    /**
     * @return mixed
     */
    public function getPaymentProductId()
    {
        return $this->PaymentProductId;
    }

    /**
     * @return mixed
     */
    public function getUncleanIndicator()
    {
        return $this->UncleanIndicator;
    }

    /**
     * @return mixed
     */
    public function getPaymentCurrency()
    {
        return $this->PaymentCurrency;
    }

    /**
     * @return mixed
     */
    public function getPaymentAmount()
    {
        return $this->PaymentAmount;
    }

    /**
     * @return mixed
     */
    public function getCreditedCurrency()
    {
        return $this->CreditedCurrency;
    }

    /**
     * @return mixed
     */
    public function getCreditedAmount()
    {
        return $this->CreditedAmount;
    }

    /**
     * @return mixed
     */
    public function getCurrencyDue()
    {
        return $this->CurrencyDue;
    }

    /**
     * @return mixed
     */
    public function getAmountDue()
    {
        return $this->AmountDue;
    }

    /**
     * @return mixed
     */
    public function getDateDue()
    {
        return $this->DateDue;
    }

    /**
     * @return mixed
     */
    public function getOverOrUnderCurrencyLocal()
    {
        return $this->OverOrUnderCurrencyLocal;
    }

    /**
     * @return mixed
     */
    public function getOverOrUnderAmountLocal()
    {
        return $this->OverOrUnderAmountLocal;
    }

    /**
     * @return mixed
     */
    public function getPlatformIdNo()
    {
        return $this->PlatformIdNo;
    }
}