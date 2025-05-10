<?php
/**
 * Kiatng AiAgent Extension
 *
 * @category   Kiatng
 * @package    Kiatng_AiAgent
 * @author     Kiatng
 */
class Kiatng_AiAgent_Adminhtml_AiagentController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Initialize action
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('customer/aiagent')
            ->_addBreadcrumb(
                Mage::helper('aiagent')->__('AI Agent'),
                Mage::helper('aiagent')->__('AI Agent')
            );
        
        return $this;
    }
    
    /**
     * Chat history grid
     */
    public function chatHistoryAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('aiagent/adminhtml_customer_edit_tab_chat'))
            ->renderLayout();
    }
    
    /**
     * Send a message to the AI agent
     */
    public function sendMessageAction()
    {
        try {
            if (!$this->getRequest()->isAjax()) {
                $this->_redirect('*/*/');
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
            $adminId = Mage::getSingleton('admin/session')->getUser()->getId();
            $chat = $chat->getOrCreateChat(null, $adminId);
            
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
                $this->_redirect('*/*/');
                return;
            }
            
            $chat = Mage::getModel('aiagent/chat');
            $adminId = Mage::getSingleton('admin/session')->getUser()->getId();
            $chat = $chat->getOrCreateChat(null, $adminId);
            
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
     * Generate content
     */
    public function generateContentAction()
    {
        try {
            if (!$this->getRequest()->isAjax()) {
                $this->_redirect('*/*/');
                return;
            }
            
            $prompt = $this->getRequest()->getParam('prompt');
            $type = $this->getRequest()->getParam('type', 'cms');
            
            if (empty($prompt)) {
                $this->_sendJsonResponse(array(
                    'error' => true,
                    'message' => Mage::helper('aiagent')->__('Prompt cannot be empty.')
                ));
                return;
            }
            
            if ($type == 'cms') {
                $cagModel = Mage::getModel('aiagent/cag_cms');
                $content = $cagModel->generateCmsContent($prompt);
            } else {
                $helper = Mage::helper('aiagent');
                $adapter = $helper->getAiAdapter();
                $content = $adapter->generateContent($prompt);
            }
            
            $this->_sendJsonResponse(array(
                'error' => false,
                'content' => $content
            ));
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_sendJsonResponse(array(
                'error' => true,
                'message' => Mage::helper('aiagent')->__('An error occurred while generating content. Please try again later.')
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
    
    /**
     * Check ACL permissions
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('customer/aiagent');
    }
}
