<?php
/**
 * @author Dmytro Zavalkin <dmytro.zavalkin@aoe.com>
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

// create table aoe_asynccache
$table = $installer->getConnection()->newTable($installer->getTable('aoeasynccache/asynccache'));

$table->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'identity' => true,
    'primary'  => true,
    'unsigned' => true,
    'nullable' => false,
));
$table->addColumn('tstamp', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
    'nullable' => false,
    'default'  => Varien_Db_Ddl_Table::TIMESTAMP_INIT
));
$table->addColumn('mode', Varien_Db_Ddl_Table::TYPE_TEXT, 250, array(
    'nullable' => false,
    'default'  => ''
));
$table->addColumn('tags', Varien_Db_Ddl_Table::TYPE_TEXT, 250, array(
    'nullable' => false,
    'default'  => ''
));
$table->addColumn('marker', Varien_Db_Ddl_Table::TYPE_TEXT, 250, array(
    'nullable' => true,
    'default'  => null
));

$installer->getConnection()->createTable($table);

// migrate rows from old table to the new one

$installer->getConnection()->query(<<<SQL
    INSERT INTO {$installer->getTable('aoeasynccache/asynccache')} (`mode`, `tags`)
    SELECT `mode`, `tags` FROM {$installer->getTable('asynccache')}
SQL
);

// drop old table asynccache
$installer->getConnection()->dropTable($installer->getTable('asynccache'));

// add unique index
$installer->getConnection()->addIndex(
    $installer->getTable('aoeasynccache/asynccache'),
    $installer->getIdxName('aoeasynccache/asynccache', array('mode', 'tags')),
    array('mode', 'tags'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);

// add index on tstamp field
$installer->getConnection()->addIndex(
    $installer->getTable('aoeasynccache/asynccache'),
    $installer->getIdxName('aoeasynccache/asynccache', 'tstamp'),
    'tstamp',
    Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
);

// add index on marker field
$installer->getConnection()->addIndex(
    $installer->getTable('aoeasynccache/asynccache'),
    $installer->getIdxName('aoeasynccache/asynccache', 'marker'),
    'marker',
    Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
);

$installer->endSetup();
