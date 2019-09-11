<?php

class Ingenico_Connect_Model_Ingenico_Client_CommunicatorLogger implements \Ingenico\Connect\Sdk\CommunicatorLogger
{
    /** @var Ingenico_Connect_Model_Config */
    protected $epaymentsConfig;

    public function __construct()
    {
        $this->epaymentsConfig = Mage::getSingleton('ingenico_connect/config');
    }

    /** @inheritdoc */
    public function log($message)
    {
        Mage::log(
            $message,
            Zend_Log::INFO,
            $this->epaymentsConfig->getLogAllRequestsFile()
        );
    }

    /** @inheritdoc */
    public function logException(
        $message,
        Exception $exception
    ) { 
        Mage::log(
            'Exception occured: ' . $message,
            Zend_Log::INFO,
            $this->epaymentsConfig->getLogAllRequestsFile()
        );
        Mage::logException($exception);
    }
}
