<?php

$oldModuleName = 'Netresearch_Epayments';

if (Mage::helper('core')->isModuleEnabled($oldModuleName)) {
    Mage::throwException(sprintf('Please, remove %s module before installing new Ingenico_Connect module.', $oldModuleName));
}

/* @var $installer Mage_Sales_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$customerTableName = $installer->getTable('ingenico_connect/token');
$oldCustomerTableName = 'nr_epayments_customer_token';

$webhooksTableName = $installer->getTable('ingenico_connect/event');
$oldWebhooksTableName = 'epayments_webhook_event';

// Rename customer token table if module was installed previously
if ($installer->getConnection()->isTableExists($oldCustomerTableName)) {
    $installer->getConnection()->renameTable($oldCustomerTableName, $customerTableName);
} else {
    /**
     * Create table 'ingenico_connect_customer_token'
     */
    $table = $installer->getConnection()
        ->newTable($customerTableName)
        ->addColumn(
            'entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'Entity Id'
        )
        ->addColumn(
            'customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned' => true,
            'nullable' => true,
        ), 'Customer Id'
        )
        ->addColumn(
            'token_string', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
            'nullable' => false,
        ), 'Token String'
        )
        ->addColumn(
            'created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
            'nullable' => false,
        ), 'Created At'
        )
        ->addColumn(
            'updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
            'nullable' => false,
        ), 'Updated At'
        )
        ->addIndex(
            $installer->getIdxName('ingenico_connect/token', array('customer_id')),
            array('customer_id')
        )
        ->addForeignKey(
            $installer->getFkName('ingenico_connect/token', 'customer_id', 'customer/entity', 'entity_id'),
            'customer_id', $installer->getTable('customer/entity'), 'entity_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
        )
        ->setComment('Customer Token Entity');
    $installer->getConnection()->createTable($table);

    $attribute = array(
        'type'            => 'varchar',
        'backend_type'    => 'text',
        'frontend_input'  => 'text',
        'is_user_defined' => false,
        'label'           => 'Order update WR file status',
        'visible'         => false,
        'required'        => false,
        'user_defined'    => false,
        'searchable'      => false,
        'filterable'      => false,
        'comparable'      => false,
        'default'         => null,
    );
    $installer->addAttribute('order', 'order_update_wr_status', $attribute);

    $attribute = array(
        'type'            => 'varchar',
        'backend_type'    => 'text',
        'frontend_input'  => 'text',
        'is_user_defined' => false,
        'label'           => 'Order update WR file first time',
        'visible'         => false,
        'required'        => false,
        'user_defined'    => false,
        'searchable'      => false,
        'filterable'      => false,
        'comparable'      => false,
        'default'         => null,
    );
    $installer->addAttribute('order', 'order_update_wr_first_time', $attribute);

    $attribute = array(
        'type'            => 'text',
        'backend_type'    => 'text',
        'frontend_input'  => 'text',
        'is_user_defined' => false,
        'label'           => 'Order update WR history',
        'visible'         => false,
        'required'        => false,
        'user_defined'    => false,
        'searchable'      => false,
        'filterable'      => false,
        'comparable'      => false,
        'default'         => null,
    );
    $installer->addAttribute('order', 'order_update_wr_history', $attribute);

    $attribute = array(
        'type'            => 'varchar',
        'backend_type'    => 'text',
        'frontend_input'  => 'text',
        'is_user_defined' => false,
        'label'           => 'Order update api last attempt time',
        'visible'         => false,
        'required'        => false,
        'user_defined'    => false,
        'searchable'      => false,
        'filterable'      => false,
        'comparable'      => false,
        'default'         => null,
    );
    $installer->addAttribute('order', 'order_update_api_last_attempt_time', $attribute);

    $attribute = array(
        'type'            => 'text',
        'backend_type'    => 'text',
        'frontend_input'  => 'text',
        'is_user_defined' => false,
        'label'           => 'Order update api history',
        'visible'         => false,
        'required'        => false,
        'user_defined'    => false,
        'searchable'      => false,
        'filterable'      => false,
        'comparable'      => false,
        'default'         => null,
    );
    $installer->addAttribute('order', 'order_update_api_history', $attribute);
}

// Rename webhook event table if module was installed previously
if ($installer->getConnection()->isTableExists($oldWebhooksTableName)) {
    $installer->getConnection()->renameTable($oldWebhooksTableName, $webhooksTableName);
} else {
    $table = $installer->getConnection()
        ->newTable($webhooksTableName)
        ->addColumn(
            Ingenico_Connect_Model_Event::ID, Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity'  => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
        ), 'Id'
        )
        ->addColumn(
            Ingenico_Connect_Model_Event::EVENT_ID, Varien_Db_Ddl_Table::TYPE_TEXT, 100, array(
            'nullable'  => false,
        ), 'Webhook event id'
        )
        ->addColumn(
            Ingenico_Connect_Model_Event::ORDER_INCREMENT_ID,
            Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
            'nullable'  => false,
        ), 'merchant reference / order increment id'
        )
        ->addColumn(
            Ingenico_Connect_Model_Event::PAYLOAD, Varien_Db_Ddl_Table::TYPE_TEXT, null, array(),
            'Original event data payload'
        )
        ->addColumn(
            Ingenico_Connect_Model_Event::STATUS, Varien_Db_Ddl_Table::TYPE_INTEGER, 1, array(
            'unsigned' => true,
            'default' => 0
        ), 'Processing status of the webhook event'
        )
        ->addColumn(
            Ingenico_Connect_Model_Event::CREATED_TIMESTAMP,
            Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
            null,
            array(),
            'Creation date of event on platform'
        )
        ->addIndex(
            $installer->getIdxName($webhooksTableName, array('event_id', 'order_increment_id')),
            array('event_id', 'order_increment_id')
        );
    $installer->getConnection()->createTable($table);
}

$installer->endSetup();
