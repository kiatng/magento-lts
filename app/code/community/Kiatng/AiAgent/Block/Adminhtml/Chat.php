<?php
/**
 * Kiatng AiAgent Extension
 *
 * @category   Kiatng
 * @package    Kiatng_AiAgent
 * @author     Kiatng
 */
class Kiatng_AiAgent_Block_Adminhtml_Chat extends Mage_Adminhtml_Block_Template
{
    /**
     * Check if chat is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return Mage::helper('aiagent')->isEnabled();
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
        $adminId = Mage::getSingleton('admin/session')->getUser()->getId();
        return $chat->getOrCreateChat(null, $adminId);
    }
    
    /**
     * Get AJAX URL for sending messages
     *
     * @return string
     */
    public function getSendMessageUrl()
    {
        return $this->getUrl('adminhtml/aiagent/sendMessage');
    }
    
    /**
     * Get AJAX URL for getting chat history
     *
     * @return string
     */
    public function getChatHistoryUrl()
    {
        return $this->getUrl('adminhtml/aiagent/getChatHistory');
    }
    
    /**
     * Get AJAX URL for generating content
     *
     * @return string
     */
    public function getGenerateContentUrl()
    {
        return $this->getUrl('adminhtml/aiagent/generateContent');
    }
}
