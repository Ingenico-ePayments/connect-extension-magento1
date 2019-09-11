<?php

require_once dirname($_SERVER['SCRIPT_FILENAME']) . '/abstract.php';

class Ingenico_Connect_Shell_WxImport extends Mage_Shell_Abstract
{
    /** @var Ingenico_Connect_Model_Cron_FetchWxFiles_ProcessorInterface */
    private $processor;

    /**
     * Ingenico_Connect_Shell_WxImport constructor.
     */
    public function __construct()
    {
        parent::__construct();
        Ingenico_Connect_Model_Autoloader::register();
        $this->processor = \Mage::getSingleton('ingenico_connect/cron_fetchWxFiles_processor');
    }

    public function run()
    {
        $date = $this->getArg('date') ?: $this->getArg('d');
        if ($date === false) {
            $date = 'yesterday';
        }

        foreach (\Mage::app()->getWebsites(true) as $website) {
            $storeId = $website->getDefaultGroup()->getDefaultStoreId();
            $this->processor->process($storeId, $date);
        }
    }

    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f ingenico_wximport.php -- [options]

  --date | -d [DATE]    phpstrtotime compatible date - default: 'yesterday'
  -h                    Short alias for help
  help                  This help
USAGE;
    }
}
$shell = new Ingenico_Connect_Shell_WxImport();
$shell->run();
