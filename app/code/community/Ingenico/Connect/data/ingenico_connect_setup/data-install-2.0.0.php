<?php

/* @var $installer Mage_Sales_Model_Resource_Setup */
$installer = $this;

$installer->run("
    UPDATE {$this->getTable('core_email_template')}
    SET `orig_template_code` = REPLACE(`orig_template_code`, 'netresearch_epayments_', 'ingenico_connect_')
    WHERE `orig_template_code` LIKE 'netresearch_epayments_%'
");

$installer->run("
    UPDATE {$this->getTable('core_translate')}
    SET `string` = REPLACE(`string`, 'Netresearch_Epayments::', 'Ingenico_Connect::')
    WHERE `string` LIKE 'Netresearch_Epayments::%'
");
