<?php

class Netresearch_Epayments_Model_Ingenico_Client_CommunicatorLogger implements \Ingenico\Connect\Sdk\CommunicatorLogger
{
    /** @var Netresearch_Epayments_Model_Config */
    protected $epaymentsConfig;

    public function __construct()
    {
        $this->epaymentsConfig = Mage::getSingleton('netresearch_epayments/config');
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
