<?php
/**
 * Class Netresearch_Epayments_Model_Ingenico_Status_AbstractStatusPool
 */
abstract class Netresearch_Epayments_Model_Ingenico_Status_AbstractStatusPool
{
    /**
     * @var string[]
     */
    protected static $statusHandlers = array();

    /**
     * @param $ingenicoStatus
     */
    abstract public function get($ingenicoStatus);
}
