<?php
/**
 * Kiatng AiAgent Extension
 *
 * @category   Kiatng
 * @package    Kiatng_AiAgent
 * @author     Devin AI
 * @license    GNU General Public License v3.0 (GPL-3.0)
 */
class Kiatng_AiAgent_Model_Chat_Message extends Mage_Core_Model_Abstract
{
    /**
     * Initialize model
     */
    protected function _construct()
    {
        $this->_init('aiagent/chat_message');
    }
}
