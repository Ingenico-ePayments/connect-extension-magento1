<?php
/*
 * This class was auto-generated from the API references found at
 * https://epayments-api.developer-ingenico.com/s2sapi/v1/
 */
namespace Ingenico\Connect\Sdk\Domain\Payout\Definitions;

use Ingenico\Connect\Sdk\Domain\Definitions\BankAccountBban;
use Ingenico\Connect\Sdk\Domain\Definitions\BankAccountIban;
use Ingenico\Connect\Sdk\Domain\Payout\Definitions\AbstractPayoutMethodSpecificInput;
use Ingenico\Connect\Sdk\Domain\Payout\Definitions\PayoutCustomer;
use UnexpectedValueException;

/**
 * @package Ingenico\Connect\Sdk\Domain\Payout\Definitions
 */
class BankTransferPayoutMethodSpecificInput extends AbstractPayoutMethodSpecificInput
{
    /**
     * @var BankAccountBban
     */
    public $bankAccountBban = null;

    /**
     * @var BankAccountIban
     */
    public $bankAccountIban = null;

    /**
     * @var PayoutCustomer
     */
    public $customer = null;

    /**
     * @var string
     */
    public $payoutDate = null;

    /**
     * @var string
     */
    public $payoutText = null;

    /**
     * @var string
     */
    public $swiftCode = null;

    /**
     * @param object $object
     * @return $this
     * @throws UnexpectedValueException
     */
    public function fromObject($object)
    {
        parent::fromObject($object);
        if (property_exists($object, 'bankAccountBban')) {
            if (!is_object($object->bankAccountBban)) {
                throw new UnexpectedValueException('value \'' . print_r($object->bankAccountBban, true) . '\' is not an object');
            }
            $value = new BankAccountBban();
            $this->bankAccountBban = $value->fromObject($object->bankAccountBban);
        }
        if (property_exists($object, 'bankAccountIban')) {
            if (!is_object($object->bankAccountIban)) {
                throw new UnexpectedValueException('value \'' . print_r($object->bankAccountIban, true) . '\' is not an object');
            }
            $value = new BankAccountIban();
            $this->bankAccountIban = $value->fromObject($object->bankAccountIban);
        }
        if (property_exists($object, 'customer')) {
            if (!is_object($object->customer)) {
                throw new UnexpectedValueException('value \'' . print_r($object->customer, true) . '\' is not an object');
            }
            $value = new PayoutCustomer();
            $this->customer = $value->fromObject($object->customer);
        }
        if (property_exists($object, 'payoutDate')) {
            $this->payoutDate = $object->payoutDate;
        }
        if (property_exists($object, 'payoutText')) {
            $this->payoutText = $object->payoutText;
        }
        if (property_exists($object, 'swiftCode')) {
            $this->swiftCode = $object->swiftCode;
        }
        return $this;
    }
}
