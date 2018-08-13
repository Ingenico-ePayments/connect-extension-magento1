<?php

/* @var $installer Mage_Customer_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

Mage::app()->setCurrentStore(Mage::getModel('core/store')->load(Mage_Core_Model_App::ADMIN_STORE_ID));

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

$installer->endSetup();
