<?php
/**
 * Kiatng AiAgent Extension
 *
 * @category   Kiatng
 * @package    Kiatng_AiAgent
 * @author     Kiatng
 */
class Kiatng_AiAgent_AjaxController extends Mage_Core_Controller_Front_Action
{
    /**
     * Initialize action
     */
    public function preDispatch()
    {
        parent::preDispatch();
        
        if (!Mage::helper('aiagent')->isEnabledForCurrentCustomer()) {
            $this->getResponse()
                ->setHeader('HTTP/1.1', '403 Forbidden')
                ->setHeader('Status', '403 Forbidden')
                ->setBody(json_encode(array(
                    'error' => true,
                    'message' => Mage::helper('aiagent')->__('AI Agent is not enabled for your account.')
                )))
                ->sendResponse();
            exit;
        }
    }
    
    /**
     * Send a message to the AI agent
     */
    public function sendMessageAction()
    {
        try {
            if (!$this->getRequest()->isAjax()) {
                $this->_redirect('/');
                return;
            }
            
            $message = $this->getRequest()->getParam('message');
            
            if (empty($message)) {
                $this->_sendJsonResponse(array(
                    'error' => true,
                    'message' => Mage::helper('aiagent')->__('Message cannot be empty.')
                ));
                return;
            }
            
            $chat = Mage::getModel('aiagent/chat');
            
            if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                $customerId = Mage::getSingleton('customer/session')->getCustomerId();
                $chat = $chat->getOrCreateChat($customerId);
            } else {
                $sessionId = Mage::getSingleton('core/session')->getSessionId();
                $chat = $chat->getOrCreateChat(null, null, $sessionId);
            }
            
            $response = $chat->processMessage($message);
            
            $this->_sendJsonResponse(array(
                'error' => false,
                'response' => $response
            ));
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_sendJsonResponse(array(
                'error' => true,
                'message' => Mage::helper('aiagent')->__('An error occurred while processing your request. Please try again later.')
            ));
        }
    }
    
    /**
     * Get chat history
     */
    public function getChatHistoryAction()
    {
        try {
            if (!$this->getRequest()->isAjax()) {
                $this->_redirect('/');
                return;
            }
            
            $chat = Mage::getModel('aiagent/chat');
            
            if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                $customerId = Mage::getSingleton('customer/session')->getCustomerId();
                $chat = $chat->getOrCreateChat($customerId);
            } else {
                $sessionId = Mage::getSingleton('core/session')->getSessionId();
                $chat = $chat->getOrCreateChat(null, null, $sessionId);
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
            
            $this->_sendJsonResponse(array(
                'error' => false,
                'messages' => $messages
            ));
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_sendJsonResponse(array(
                'error' => true,
                'message' => Mage::helper('aiagent')->__('An error occurred while retrieving chat history. Please try again later.')
            ));
        }
    }
    
    /**
     * Send JSON response
     *
     * @param array $data Response data
     */
    protected function _sendJsonResponse($data)
    {
        $this->getResponse()
            ->setHeader('Content-Type', 'application/json')
            ->setBody(json_encode($data));
    }
}
