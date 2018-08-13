<?php
/*
 * This class was auto-generated from the API references found at
 * https://epayments-api.developer-ingenico.com/s2sapi/v1/
 */
namespace Ingenico\Connect\Sdk\Domain\Services;

use Ingenico\Connect\Sdk\Domain\Services\Definitions\BankDetails;
use UnexpectedValueException;

/**
 * @package Ingenico\Connect\Sdk\Domain\Services
 */
class BankDetailsRequest extends BankDetails
{
    /**
     * @param object $object
     * @return $this
     * @throws UnexpectedValueException
     */
    public function fromObject($object)
    {
        parent::fromObject($object);
        return $this;
    }
}
