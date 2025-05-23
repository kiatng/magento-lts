<?php

/**
 * @copyright  For copyright and license information, read the COPYING.txt file.
 * @link       /COPYING.txt
 * @license    Open Software License (OSL 3.0)
 * @package    Mage_Eav
 */

/** @var Mage_Eav_Model_Entity_Setup $installer */
$installer = $this;
$installer->startSetup();

$installer->run("

CREATE TABLE `{$installer->getTable('eav/form_type')}` (
  `type_id` smallint(5) unsigned NOT NULL auto_increment,
  `code` char(64) NOT NULL,
  `label` varchar(255) NOT NULL,
  `is_system` tinyint(1) unsigned NOT NULL default '0',
  `theme` varchar(64) NOT NULL default '',
  `store_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`type_id`),
  UNIQUE KEY `UNQ_FORM_TYPE_CODE` (`code`, `theme`, `store_id`),
  KEY `IDX_STORE` (`store_id`),
  CONSTRAINT `FK_EAV_FORM_TYPE_STORE` FOREIGN KEY (`store_id`) REFERENCES `{$installer->getTable('core/store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `{$installer->getTable('eav/form_type_entity')}` (
  `type_id` smallint(5) unsigned NOT NULL,
  `entity_type_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY  (`type_id`,`entity_type_id`),
  KEY `IDX_EAV_ENTITY_TYPE` (`entity_type_id`),
  CONSTRAINT `FK_EAV_FORM_TYPE_ENTITY_ENTITY_TYPE` FOREIGN KEY (`entity_type_id`) REFERENCES `{$installer->getTable('eav/entity_type')}` (`entity_type_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_EAV_FORM_TYPE_ENTITY_FORM_TYPE` FOREIGN KEY (`type_id`) REFERENCES `{$installer->getTable('eav/form_type')}` (`type_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `{$installer->getTable('eav/form_fieldset')}` (
  `fieldset_id` smallint(5) unsigned NOT NULL auto_increment,
  `type_id` smallint(5) unsigned NOT NULL,
  `code` char(64) NOT NULL,
  `sort_order` int(11) NOT NULL default '0',
  PRIMARY KEY  (`fieldset_id`),
  UNIQUE KEY `UNQ_FORM_FIELDSET_CODE` (`type_id`,`code`),
  KEY `IDX_FORM_TYPE` (`type_id`),
  CONSTRAINT `FK_EAV_FORM_FIELDSET_FORM_TYPE` FOREIGN KEY (`type_id`) REFERENCES `{$installer->getTable('eav/form_type')}` (`type_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `{$installer->getTable('eav/form_fieldset_label')}` (
  `fieldset_id` smallint(5) unsigned NOT NULL,
  `store_id` smallint(5) unsigned NOT NULL,
  `label` varchar(255) NOT NULL,
  PRIMARY KEY (`fieldset_id`, `store_id`),
  KEY `IDX_FORM_FIELDSET` (`fieldset_id`),
  KEY `IDX_STORE` (`store_id`),
  CONSTRAINT `FK_EAV_FORM_FIELDSET_LABEL_FORM_FIELDSET` FOREIGN KEY (`fieldset_id`) REFERENCES `{$installer->getTable('eav/form_fieldset')}` (`fieldset_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_EAV_FORM_FIELDSET_LABEL_STORE` FOREIGN KEY (`store_id`) REFERENCES `{$installer->getTable('core/store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `{$installer->getTable('eav_form_element')}` (
  `element_id` int(10) unsigned NOT NULL auto_increment,
  `type_id` smallint(5) unsigned NOT NULL,
  `fieldset_id` smallint(5) unsigned default NULL,
  `attribute_id` smallint(5) unsigned NOT NULL,
  `sort_order` int(11) NOT NULL default '0',
  PRIMARY KEY  (`element_id`),
  KEY `IDX_FORM_TYPE` (`type_id`),
  UNIQUE KEY `UNQ_FORM_ATTRIBUTE` (`type_id`,`attribute_id`),
  KEY `IDX_FORM_FIELDSET` (`fieldset_id`),
  KEY `IDX_FORM_ATTRIBUTE` (`attribute_id`),
  CONSTRAINT `FK_EAV_FORM_ELEMENT_FORM_TYPE` FOREIGN KEY (`type_id`) REFERENCES `{$installer->getTable('eav/form_type')}` (`type_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_EAV_FORM_ELEMENT_FORM_FIELDSET` FOREIGN KEY (`fieldset_id`) REFERENCES `{$installer->getTable('eav/form_fieldset')}` (`fieldset_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_EAV_FORM_ELEMENT_ATTRIBUTE` FOREIGN KEY (`attribute_id`) REFERENCES `{$installer->getTable('eav/attribute')}` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup();
