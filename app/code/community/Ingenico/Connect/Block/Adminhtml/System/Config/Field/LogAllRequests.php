<?php

class Ingenico_Connect_Block_Adminhtml_System_Config_Field_LogAllRequests
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Add style attribute to $element from field config
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $logFile = $this->getLogFile();
        if ($logFile && is_file($logFile)) {
            $downloadLink = $this->getLinkToDownloadLogFile();
            $element->setComment(
                Mage::helper('ingenico_connect')
                    ->__(
                        sprintf('Download %s', '<a href="' . $downloadLink . '">log file</a>')
                    )
            );
        }

        return parent::render($element);
    }

    /**
     * @return string
     */
    protected function getLogFile()
    {
        /** @var Ingenico_Connect_Model_ConfigInterface $config */
        $config = Mage::getSingleton('ingenico_connect/config');
        return Mage::getBaseDir('var') . DS . 'log' . DS . $config->getLogAllRequestsFile();
    }

    /**
     * @return string
     */
    protected function getLinkToDownloadLogFile()
    {
        return $this->getUrl('*/epayments/downloadLogFile');
    }
}
