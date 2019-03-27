<?php

class Netresearch_Epayments_Block_Adminhtml_System_Config_Field_Expanded
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
        $originalData = $element->getOriginalData();
        if (isset($originalData['style'])) {
            $element->setStyle($originalData['style']);
        }

        return parent::render($element);
    }
}
