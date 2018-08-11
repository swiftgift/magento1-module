<?php
Mage::app()->setCurrentStore(Mage::getModel('core/store')->load(Mage_Core_Model_App::ADMIN_STORE_ID));
$installer = new Mage_Sales_Model_Mysql4_Setup;
$installer->startSetup();
$installer->addAttribute('order', 'swift_gift_used', array(
    'type'          => 'boolean',
    'backend_type'  => 'boolean',
    'frontend_input' => null,
    'is_user_defined' => false,
    'label'         => 'Is swift gift order',
    'visible'       => false,
    'required'      => false,
    'user_defined'  => false,
    'searchable'    => false,
    'filterable'    => false,
    'comparable'    => false,
    'default'       => '0'
));
$installer->addAttribute('quote', 'swift_gift_used', array(
    'type'          => 'boolean',
    'backend_type'  => 'boolean',
    'frontend_input' => null,
    'is_user_defined' => false,
    'label'         => 'Is swift gift order',
    'visible'       => false,
    'required'      => false,
    'user_defined'  => false,
    'searchable'    => false,
    'filterable'    => false,
    'comparable'    => false,
    'default'       => '0'
));
$installer->addAttribute('quote', 'swift_gift_name', array(
    'type'          => 'varchar',
    'backend_type'  => 'varchar',
    'frontend_input' => null,
    'is_user_defined' => false,
    'visible'       => false,
    'required'      => false,
    'user_defined'  => false,
    'searchable'    => false,
    'filterable'    => false,
    'comparable'    => false,
    'default'       => null
));
$installer->addAttribute('quote', 'swift_gift_country_code', array(
    'type'          => 'varchar',
    'backend_type'  => 'varchar',
    'frontend_input' => null,
    'is_user_defined' => false,
    'visible'       => false,
    'required'      => false,
    'user_defined'  => false,
    'searchable'    => false,
    'filterable'    => false,
    'comparable'    => false,
    'default'       => null
));
$installer->addAttribute('quote', 'swift_gift_message', array(
    'type'          => 'text',
    'backend_type'  => 'text',
    'frontend_input' => null,
    'is_user_defined' => false,
    'visible'       => false,
    'required'      => false,
    'user_defined'  => false,
    'searchable'    => false,
    'filterable'    => false,
    'comparable'    => false,
    'default'       => null
));

$installer->endSetup();

$installer = $this;

$installer->startSetup();
$installer->run("
create table `{$installer->getTable('sggift/gift')}` (
       `entity_id` int not null auto_increment,
       `order_id` int(10) unsigned not null unique,
       `status` varchar(50) not null,
       `status_change_time` datetime not null,
       `code` varchar(255) not null,
       `share_url` varchar(255) not null,
       primary key (`entity_id`),
       foreign key (order_id) references `{$installer->getTable('sales/order')}` (entity_id)
) engine=InnoDB default charset=utf8 auto_increment=1;
");
$installer->endSetup();
