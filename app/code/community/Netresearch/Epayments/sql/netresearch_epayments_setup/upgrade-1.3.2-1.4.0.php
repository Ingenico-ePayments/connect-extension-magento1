<?php
/* @var Mage_Customer_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();

$tableName = $installer->getTable('epayments_webhook_event');

if ($installer->getConnection()->isTableExists($tableName) === false) {
    $table = $installer->getConnection()
        ->newTable($installer->getTable('epayments_webhook_event'))
        ->addColumn(
            Netresearch_Epayments_Model_Event::ID, Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
            ), 'Id'
        )
        ->addColumn(
            Netresearch_Epayments_Model_Event::EVENT_ID, Varien_Db_Ddl_Table::TYPE_TEXT, 100, array(
                'nullable'  => false,
            ), 'Webhook event id'
        )
        ->addColumn(
            Netresearch_Epayments_Model_Event::ORDER_INCREMENT_ID,
            Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
                'nullable'  => false,
            ), 'merchant reference / order increment id'
        )
        ->addColumn(
            Netresearch_Epayments_Model_Event::PAYLOAD, Varien_Db_Ddl_Table::TYPE_TEXT, null, array(),
            'Original event data payload'
        )
        ->addColumn(
            Netresearch_Epayments_Model_Event::STATUS, Varien_Db_Ddl_Table::TYPE_INTEGER, 1, array(
                'unsigned' => true,
                'default' => 0
            ), 'Processing status of the webhook event'
        )
        ->addColumn(
            Netresearch_Epayments_Model_Event::CREATED_TIMESTAMP,
            Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
            null,
            array(),
            'Creation date of event on platform'
        )
        ->addIndex(
            $installer->getIdxName($tableName, array('event_id', 'order_increment_id')),
            array('event_id', 'order_increment_id')
        );
    $installer->getConnection()->createTable($table);
}

$installer->endSetup();
