<?php
/**
 * Kiatng AiAgent Extension
 *
 * @category   Kiatng
 * @package    Kiatng_AiAgent
 * @author     Kiatng
 */
class Kiatng_AiAgent_Block_Chat extends Mage_Core_Block_Template
{
    /**
     * Check if chat is enabled for current customer
     *
     * @return bool
     */
    public function isEnabled()
    {
        return Mage::helper('aiagent')->isEnabledForCurrentCustomer();
    }
    
    /**
     * Get chat history
     *
     * @return array
     */
    public function getChatHistory()
    {
        $chat = $this->getChat();
        
        if (!$chat || !$chat->getId()) {
            return array();
        }
        
        $messages = array();
        $chatMessages = $chat->getMessages();
        
        foreach ($chatMessages as $chatMessage) {
            $messages[] = array(
                'message' => $chatMessage->getMessage(),
                'is_from_user' => (bool)$chatMessage->getIsFromUser(),
                'created_at' => $chatMessage->getCreatedAt()
            );
        }
        
        return $messages;
    }
    
    /**
     * Get chat
     *
     * @return Kiatng_AiAgent_Model_Chat
     */
    public function getChat()
    {
        $chat = Mage::getModel('aiagent/chat');
        
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customerId = Mage::getSingleton('customer/session')->getCustomerId();
            return $chat->getOrCreateChat($customerId);
        } else {
            $sessionId = Mage::getSingleton('core/session')->getSessionId();
            return $chat->getOrCreateChat(null, null, $sessionId);
        }
    }
    
    /**
     * Get AJAX URL for sending messages
     *
     * @return string
     */
    public function getSendMessageUrl()
    {
        return $this->getUrl('aiagent/ajax/sendMessage');
    }
    
    /**
     * Get AJAX URL for getting chat history
     *
     * @return string
     */
    public function getChatHistoryUrl()
    {
        return $this->getUrl('aiagent/ajax/getChatHistory');
    }
}
