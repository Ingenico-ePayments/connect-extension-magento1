<?php
require_once dirname($_SERVER['SCRIPT_FILENAME']) . '/abstract.php';

/**
 * Class Netresearch_Epayments_Shell_ProcessEvents
 */
class Netresearch_Epayments_Shell_ProcessEvents extends Mage_Shell_Abstract
{
    /**
     * @var Netresearch_Epayments_Model_Event_Processor
     */
    protected $processor;

    /**
     * Netresearch_Epayments_Shell_ProcessEvents constructor.
     */
    public function __construct()
    {
        parent::__construct();
        Netresearch_Epayments_Model_Autoloader::register();
        $this->processor = Mage::getSingleton('netresearch_epayments/event_processor');
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

$shell = new Netresearch_Epayments_Shell_ProcessEvents();
$shell->run();
