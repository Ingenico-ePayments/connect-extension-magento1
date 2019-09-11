<?php
/**
 * Class Ingenico_Connect_Model_Ingenico_Status_AbstractStatusPool
 */
abstract class Ingenico_Connect_Model_Ingenico_Status_AbstractStatusPool
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
