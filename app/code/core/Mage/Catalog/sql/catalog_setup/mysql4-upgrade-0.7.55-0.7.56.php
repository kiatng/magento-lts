<?php

/**
 * @copyright  For copyright and license information, read the COPYING.txt file.
 * @link       /COPYING.txt
 * @license    Open Software License (OSL 3.0)
 * @package    Mage_Catalog
 */

/** @var Mage_Catalog_Model_Resource_Setup  $installer */
$installer = $this;
$installer->startSetup();

$categoryIndexTable = $installer->getTable('catalog/category_product_index');

$installer->getConnection()->addColumn(
    $categoryIndexTable,
    'store_id',
    'smallint(5) unsigned NOT NULL default \'0\'',
);

$installer->getConnection()->addColumn(
    $categoryIndexTable,
    'visibility',
    'tinyint(3) unsigned NOT NULL',
);

/**
 * Clear relation with root category
 */
$installer->getConnection()->delete($categoryIndexTable, 'category_id=' . Mage_Catalog_Model_Category::TREE_ROOT_ID);
$installer->getConnection()->addKey(
    $categoryIndexTable,
    'FK_CATALOG_CATEGORY_PRODUCT_INDEX_CATEGORY_ENTITY',
    ['category_id'],
);
$installer->getConnection()->dropKey($categoryIndexTable, 'IDX_CATEGORY_POSITION');
$installer->getConnection()->dropKey($categoryIndexTable, 'UNQ_CATEGORY_PRODUCT');

$storesData = $installer->getConnection()->fetchAll("
    SELECT
        s.store_id, s.website_id, c.path AS root_path
    FROM
        {$installer->getTable('core/store')} AS s,
        {$installer->getTable('core/store_group')} AS sg,
        {$installer->getTable('catalog/category')} AS c
    WHERE
        sg.group_id=s.group_id
        AND c.entity_id=sg.root_category_id
");

foreach ($storesData as $storeData) {
    $storeId   = $storeData['store_id'];
    $websiteId = $storeData['website_id'];
    $path      = $storeData['root_path'];

    $query = "INSERT INTO {$categoryIndexTable}
       (`category_id`, `product_id`, `position`, `is_parent`, `store_id`, `visibility`)
    SELECT
       ci.category_id,
       ci.product_id,
       ci.position,
       ci.is_parent,
       {$storeId},
       ep.visibility
    FROM
       $categoryIndexTable AS ci
       INNER JOIN {$installer->getTable('catalog/product_website')} AS pw
           ON pw.product_id=ci.product_id AND pw.website_id={$websiteId}
       INNER JOIN {$installer->getTable('catalog/category')} AS c
           ON c.entity_id=ci.category_id AND c.path LIKE '{$path}%'
       INNER JOIN {$installer->getTable('catalog/product_enabled_index')} AS ep
           ON ep.product_id=ci.product_id AND ep.store_id={$storeId}
    WHERE
        ci.store_id=0";

    $installer->run($query);
}

$installer->getConnection()->delete($categoryIndexTable, 'store_id=0');

$installer->getConnection()->addKey(
    $categoryIndexTable,
    'UNQ_CATEGORY_PRODUCT',
    ['store_id', 'category_id', 'product_id'],
);

$installer->getConnection()->addKey(
    $categoryIndexTable,
    'IDX_JOIN',
    ['product_id', 'store_id', 'category_id', 'visibility'],
);

$installer->getConnection()->addKey(
    $categoryIndexTable,
    'IDX_BASE',
    ['store_id', 'category_id', 'visibility', 'is_parent', 'position'],
);

$installer->getConnection()->addConstraint(
    'FK_CATEGORY_PRODUCT_INDEX_STORE',
    $categoryIndexTable,
    'store_id',
    $installer->getTable('core/store'),
    'store_id',
);

$installer->endSetup();
