<?php

/**
 * Class Ingenico_Connect_Model_Cron_FetchWxFiles_Logger
 */
class Ingenico_Connect_Model_Cron_FetchWxFiles_Logger
{
    private static $fileName = 'wx_update.log';

    /**
     * @param string $message
     * @param int $level
     */
    public function addRecord($message, $level = Zend_Log::DEBUG)
    {
        \Mage::log($message, $level, self::$fileName);
    }

    /**
     * @param string $message
     */
    public function addInfo($message)
    {
        $this->addRecord($message, Zend_Log::INFO);
    }

    /**
     * @param string $message
     */
    public function addError($message)
    {
        $this->addRecord($message, Zend_Log::ERR);
    }

    /**
     * @param string $message
     */
    public function addNotice($message)
    {
        $this->addRecord($message, Zend_Log::NOTICE);
    }
}