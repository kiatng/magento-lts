<?php
/**
 * Kiatng AiAgent Extension
 *
 * @category   Kiatng
 * @package    Kiatng_AiAgent
 * @author     Kiatng
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
