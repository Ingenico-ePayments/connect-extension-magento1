<?php

class Netresearch_Epayments_Block_Adminhtml_System_Config_Field_Version
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Show extension version
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        /** @var \Mage_Core_Model_Config $config */
        $config = Mage::getConfig();
        $moduleConfig = $config->getModuleConfig('Netresearch_Epayments');
        $moduleVersion = (string) $moduleConfig->version;


        $element->setValue($moduleVersion);
        return parent::render($element);
    }
}
