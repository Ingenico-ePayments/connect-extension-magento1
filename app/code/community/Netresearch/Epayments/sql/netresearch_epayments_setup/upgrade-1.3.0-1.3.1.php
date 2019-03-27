<?php
/* @var $installer Mage_Customer_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$encryptor = Mage::getModel('core/encryption');


$items = array(
    'ingenico_epayments/settings/api_secret',
    'ingenico_epayments/webhooks/secret_key',
    'ingenico_epayments/webhooks/secret_key_secondary'
);

foreach ($items as $item => $path) {
    $collection = Mage::getModel('core/config_data')->getCollection()
                      ->addFieldToFilter('path', $path)
                      ->load();

    if (!empty($collection->getItems())) {
        foreach ($collection as $apiSecret) {
            $value = $encryptor->encrypt($apiSecret->getValue());
            $scope = $apiSecret->getScope();
            $scopeId = $apiSecret->getScopeId();

            Mage::getConfig()->saveConfig($path, $value, $scope, $scopeId);
        }
    }
}

$installer->endSetup();


