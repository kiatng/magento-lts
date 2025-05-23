<?php

/**
 * @copyright  For copyright and license information, read the COPYING.txt file.
 * @link       /COPYING.txt
 * @license    Open Software License (OSL 3.0)
 * @package    Mage_CatalogInventory
 */

$installer = $this;
/** @var Mage_Eav_Model_Entity_Setup $installer */

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS `{$this->getTable('cataloginventory_stock')}`;
CREATE TABLE `{$this->getTable('cataloginventory_stock')}` (
  `stock_id` smallint(4) unsigned NOT NULL auto_increment,
  `stock_name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`stock_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Catalog inventory Stocks list';

insert into `{$this->getTable('cataloginventory_stock')}`(`stock_id`,`stock_name`) values (1, 'Default');

-- DROP TABLE IF EXISTS `{$this->getTable('cataloginventory_stock_item')}`;
CREATE TABLE `{$this->getTable('cataloginventory_stock_item')}` (
    `item_id` int(10) unsigned NOT NULL auto_increment,
    `product_id` int(10) unsigned NOT NULL default '0',
    `stock_id` smallint(4) unsigned NOT NULL default '0',
    `qty` decimal(12,4) NOT NULL default '0.0000',
    `min_qty` decimal(12,4) NOT NULL default '0.0000',
    `use_config_min_qty` tinyint(1) unsigned NOT NULL default '1',
    `is_qty_decimal` tinyint(1) unsigned NOT NULL default '0',
    `backorders` tinyint(3) unsigned NOT NULL default '0',
    `use_config_backorders` tinyint(1) unsigned NOT NULL default '1',
    `min_sale_qty` decimal(12,4) NOT NULL default '1.0000',
    `use_config_min_sale_qty` tinyint(1) unsigned NOT NULL default '1',
    `max_sale_qty` decimal(12,4) NOT NULL default '0.0000',
    `use_config_max_sale_qty` tinyint(1) unsigned NOT NULL default '1',
    `is_in_stock` tinyint(1) unsigned NOT NULL default '0',
    PRIMARY KEY  (`item_id`),
    UNIQUE KEY `IDX_STOCK_PRODUCT` (`product_id`,`stock_id`),
    KEY `FK_CATALOGINVENTORY_STOCK_ITEM_PRODUCT` (`product_id`),
    KEY `FK_CATALOGINVENTORY_STOCK_ITEM_STOCK` (`stock_id`),
    CONSTRAINT `FK_CATALOGINVENTORY_STOCK_ITEM_PRODUCT` FOREIGN KEY (`product_id`) REFERENCES `{$this->getTable('catalog_product_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `FK_CATALOGINVENTORY_STOCK_ITEM_STOCK` FOREIGN KEY (`stock_id`) REFERENCES `{$this->getTable('cataloginventory_stock')}` (`stock_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Inventory Stock Item Data';

    ");

$installer->endSetup();
