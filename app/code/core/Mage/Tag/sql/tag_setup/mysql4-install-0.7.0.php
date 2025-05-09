<?php

/**
 * @copyright  For copyright and license information, read the COPYING.txt file.
 * @link       /COPYING.txt
 * @license    Open Software License (OSL 3.0)
 * @package    Mage_Tag
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('tag')};
CREATE TABLE {$this->getTable('tag')} (
  `tag_id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `status` smallint(6) NOT NULL default '0',
  PRIMARY KEY (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS {$this->getTable('tag_relation')};
CREATE TABLE {$this->getTable('tag_relation')} (
  `tag_relation_id` int(11) unsigned NOT NULL auto_increment,
  `tag_id` int(11) unsigned NOT NULL default '0',
  `customer_id` int(10) unsigned NOT NULL default '0',
  `product_id` int(11) unsigned NOT NULL default '0',
  `store_id` smallint(6) unsigned NOT NULL default '1',
  `active` tinyint (1) unsigned NOT NULL default '1',
  `created_at` datetime default NULL,
  PRIMARY KEY (`tag_relation_id`),
  KEY `FK_TAG_RELATION_TAG` (`tag_id`),
  KEY `FK_TAG_RELATION_CUSTOMER` (`customer_id`),
  KEY `FK_TAG_RELATION_PRODUCT` (`product_id`),
  KEY `FK_TAG_RELATION_STORE` (`store_id`),
  CONSTRAINT `FK_TAG_RELATION_PRODUCT` FOREIGN KEY (`product_id`) REFERENCES {$this->getTable('catalog_product_entity')} (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tag_relation_ibfk_1` FOREIGN KEY (`tag_id`) REFERENCES {$this->getTable('tag')} (`tag_id`) ON DELETE CASCADE,
  CONSTRAINT `tag_relation_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES {$this->getTable('customer_entity')} (`entity_id`) ON DELETE CASCADE,
  CONSTRAINT `tag_relation_ibfk_4` FOREIGN KEY (`store_id`) REFERENCES {$this->getTable('core_store')} (`store_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS {$this->getTable('tag_summary')};
CREATE TABLE {$this->getTable('tag_summary')} (
   `tag_id` int(11) unsigned NOT NULL default '0',
   `store_id` smallint(5) unsigned NOT NULL default '0',
   `customers` int(11) unsigned NOT NULL default '0',
   `products` int(11) unsigned NOT NULL default '0',
   `uses` int(11) unsigned NOT NULL default '0',
   `historical_uses` int(11) unsigned NOT NULL default '0',
   `popularity` int(11) unsigned NOT NULL default '0',
   PRIMARY KEY  (`tag_id`,`store_id`),
   CONSTRAINT `TAG_SUMMARY_TAG` FOREIGN KEY (`tag_id`) REFERENCES {$this->getTable('tag')} (`tag_id`) ON DELETE CASCADE
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup();
