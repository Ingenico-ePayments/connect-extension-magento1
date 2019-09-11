<?php
require_once dirname($_SERVER['SCRIPT_FILENAME']) . '/abstract.php';

/**
 * Class Ingenico_Connect_Shell_ProcessEvents
 */
class Ingenico_Connect_Shell_ProcessEvents extends Mage_Shell_Abstract
{
    /**
     * @var Ingenico_Connect_Model_Event_Processor
     */
    protected $processor;

    /**
     * Ingenico_Connect_Shell_ProcessEvents constructor.
     */
    public function __construct()
    {
        parent::__construct();
        Ingenico_Connect_Model_Autoloader::register();
        $this->processor = Mage::getSingleton('ingenico_connect/event_processor');
    }



    public function run()
    {
        $amount = $this->getArg('amount') ?: $this->getArg('a');
        if ($amount === false) {
            $amount = 20;
        }

        $this->processor->processBatch($amount);
    }

    /**
     * @return string
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f ingenico_processevents.php -- [options]

  --amount | -a         Amount of events that should get processed - default: 20
  -h                    Short alias for help
  help                  This help
USAGE;
    }
}

$shell = new Ingenico_Connect_Shell_ProcessEvents();
$shell->run();
