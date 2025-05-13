<?php
/**
 * Kiatng AiAgent Extension
 *
 * @category   Kiatng
 * @package    Kiatng_AiAgent
 * @author     Devin AI
 * @license    GNU General Public License v3.0 (GPL-3.0)
 */
class Kiatng_AiAgent_Model_Chat extends Mage_Core_Model_Abstract
{
    /**
     * Initialize model
     */
    protected function _construct()
    {
        $this->_init('aiagent/chat');
    }
    
    /**
     * Get or create a chat session
     *
     * @param int|null $customerId Customer ID
     * @param int|null $adminId Admin user ID
     * @param string|null $sessionId Session ID for guests
     * @return Kiatng_AiAgent_Model_Chat
     */
    public function getOrCreateChat($customerId = null, $adminId = null, $sessionId = null)
    {
        $chat = null;
        
        if ($customerId) {
            $chat = $this->getCollection()
                ->addFieldToFilter('customer_id', $customerId)
                ->addFieldToFilter('is_active', 1)
                ->setOrder('updated_at', 'DESC')
                ->getFirstItem();
        } elseif ($adminId) {
            $chat = $this->getCollection()
                ->addFieldToFilter('admin_id', $adminId)
                ->addFieldToFilter('is_active', 1)
                ->setOrder('updated_at', 'DESC')
                ->getFirstItem();
        } elseif ($sessionId) {
            $chat = $this->getCollection()
                ->addFieldToFilter('session_id', $sessionId)
                ->addFieldToFilter('is_active', 1)
                ->setOrder('updated_at', 'DESC')
                ->getFirstItem();
        }
        
        if (!$chat || !$chat->getId()) {
            $chat = $this->setData([
                'customer_id' => $customerId,
                'admin_id' => $adminId,
                'session_id' => $sessionId,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ])->save();
        }
        
        return $chat;
    }
    
    /**
     * Add a message to the chat
     *
     * @param string $message Message content
     * @param bool $isFromUser Whether the message is from the user
     * @return Kiatng_AiAgent_Model_Chat_Message
     */
    public function addMessage($message, $isFromUser = true)
    {
        $chatMessage = Mage::getModel('aiagent/chat_message');
        $chatMessage->setData([
            'chat_id' => $this->getId(),
            'message' => $message,
            'is_from_user' => $isFromUser ? 1 : 0,
            'created_at' => now(),
        ])->save();
        
        $this->setUpdatedAt(now())->save();
        
        return $chatMessage;
    }
    
    /**
     * Get chat messages
     *
     * @param int $limit Maximum number of messages to retrieve
     * @return array Chat messages
     */
    public function getMessages($limit = 10)
    {
        $collection = Mage::getModel('aiagent/chat_message')->getCollection()
            ->addFieldToFilter('chat_id', $this->getId())
            ->setOrder('created_at', 'ASC');
        
        if ($limit) {
            $collection->setPageSize($limit);
        }
        
        return $collection->getItems();
    }
    
    /**
     * Process a user message and get AI response
     *
     * @param string $message User message
     * @return string AI response
     */
    public function processMessage($message)
    {
        $this->addMessage($message, true);
        
        $chatHistory = [];
        $messages = $this->getMessages(10);
        
        foreach ($messages as $chatMessage) {
            $chatHistory[] = [
                'message' => $chatMessage->getMessage(),
                'is_from_user' => $chatMessage->getIsFromUser(),
            ];
        }
        
        $context = $this->getContextForMessage($message);
        
        $helper = Mage::helper('aiagent');
        $adapter = $helper->getAiAdapter();
        $response = $adapter->sendMessage($message, $chatHistory, $context);
        
        $this->addMessage($response, false);
        
        return $response;
    }
    
    /**
     * Get context for message
     *
     * @param string $message User message
     * @return array Context information
     */
    protected function getContextForMessage($message)
    {
        $context = [];
        $helper = Mage::helper('aiagent');
        
        if ($helper->isRagEnabled()) {
            $ragModel = Mage::getModel('aiagent/rag_catalog');
            $productInfo = $ragModel->getRelevantProductInfo($message);
            
            if (!empty($productInfo)) {
                $context = array_merge($context, $productInfo);
            }
        }
        
        if ($helper->isCagEnabled()) {
            $cagModel = Mage::getModel('aiagent/cag_cms');
            $cmsInfo = $cagModel->getRelevantCmsInfo($message);
            
            if (!empty($cmsInfo)) {
                $context = array_merge($context, $cmsInfo);
            }
        }
        
        if (Mage::app()->getStore()->isAdmin() && $helper->isWebSearchEnabledForAdmin()) {
            $adapter = $helper->getAiAdapter();
            $searchResults = $adapter->searchWeb($message);
            
            if (!empty($searchResults)) {
                foreach ($searchResults as $result) {
                    $context[] = $result['title'] . ': ' . $result['snippet'];
                }
            }
        }
        
        return $context;
    }
}
