<?php
/**
 * Kiatng AiAgent Extension
 *
 * @category   Kiatng
 * @package    Kiatng_AiAgent
 * @author     Devin AI
 * @license    GNU General Public License v3.0 (GPL-3.0)
 */

$installer = $this;
$installer->startSetup();

$installer->run("
    ALTER TABLE {$installer->getTable('aiagent/chat_message')} 
    MODIFY COLUMN `message` TEXT NOT NULL COMMENT 'Message Content';
");

$connection = $installer->getConnection();
$tableName = $installer->getTable('aiagent/chat_message');
$indexName = 'IDX_AIAGENT_CHAT_MESSAGE_CHAT_ID';

$indexesList = $connection->getIndexList($tableName);

if (!array_key_exists($indexName, $indexesList)) {
    $installer->run("
        ALTER TABLE {$tableName}
        ADD INDEX `{$indexName}` (`chat_id`);
    ");
}

$installer->endSetup();
