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

$installer->run("
    ALTER TABLE {$installer->getTable('aiagent/chat_message')}
    ADD INDEX `IDX_AIAGENT_CHAT_MESSAGE_CHAT_ID` (`chat_id`);
");

$installer->endSetup();
