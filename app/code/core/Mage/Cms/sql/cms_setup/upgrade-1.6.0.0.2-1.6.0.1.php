<?php
/**
 * OpenMage
 *
 * @category    Mage
 * @package     Mage_Cms
 * @copyright   Copyright (c) 2021 Ng Kiat Siong
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('cms/block'), 'cache_bypass', [
    'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
    'nullable'  => false,
    'default'   => '0',
    'comment' => 'Cache Bypass'
]);

$installer->endSetup();