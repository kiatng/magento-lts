<?php

/**
 * @copyright  For copyright and license information, read the COPYING.txt file.
 * @link       /COPYING.txt
 * @license    Open Software License (OSL 3.0)
 * @package    Mage_Core
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();
$table = $installer->getTable('core/translate');

$connection->dropIndex($table, $installer->getIdxName(
    'core/translate',
    ['store_id', 'locale', 'string'],
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE,
));

$connection->addColumn($table, 'crc_string', [
    'type'     => Varien_Db_Ddl_Table::TYPE_BIGINT,
    'nullable' => false,
    'default'  => crc32(Mage_Core_Model_Translate::DEFAULT_STRING),
    'comment'  => 'Translation String CRC32 Hash',
]);

$connection->addIndex($table, $installer->getIdxName(
    'core/translate',
    ['store_id', 'locale', 'crc_string', 'string'],
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE,
), ['store_id', 'locale', 'crc_string', 'string'], Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE);

$installer->endSetup();
