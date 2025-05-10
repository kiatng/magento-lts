<?php
/**
 * Kiatng AiAgent Extension
 *
 * @category   Kiatng
 * @package    Kiatng_AiAgent
 * @author     Kiatng
 */
abstract class Kiatng_AiAgent_Model_Adapter_Abstract extends Varien_Object
{
    /**
     * Send a message to the AI and get a response
     *
     * @param string $message User message
     * @param array $chatHistory Previous messages in the conversation
     * @param array $context Additional context (RAG/CAG data)
     * @return string AI response
     */
    abstract public function sendMessage($message, $chatHistory = array(), $context = array());
    
    /**
     * Generate content for CMS or product descriptions
     *
     * @param string $prompt Content generation prompt
     * @param array $parameters Additional parameters for content generation
     * @return string Generated content
     */
    abstract public function generateContent($prompt, $parameters = array());
    
    /**
     * Search the web for information (admin only)
     *
     * @param string $query Search query
     * @return array Search results
     */
    abstract public function searchWeb($query);
    
    /**
     * Get system prompt for the AI based on context
     *
     * @param string $context Context (frontend/admin)
     * @return string System prompt
     */
    protected function getSystemPrompt($context = 'frontend')
    {
        if ($context == 'admin') {
            return "You are an AI assistant for OpenMage store administrators. "
                . "You can help with generating product descriptions, CMS content, "
                . "and answering questions about OpenMage functionality. "
                . "Be concise, accurate, and helpful.";
        } else {
            return "You are an AI assistant for an OpenMage store. "
                . "You can help customers with questions about products, orders, "
                . "and general information about the store. "
                . "Be friendly, concise, and helpful.";
        }
    }
    
    /**
     * Format chat history for the AI provider
     *
     * @param array $chatHistory
     * @return array Formatted chat history
     */
    protected function formatChatHistory($chatHistory)
    {
        $formattedHistory = array();
        
        foreach ($chatHistory as $message) {
            $formattedHistory[] = array(
                'role' => $message['is_from_user'] ? 'user' : 'assistant',
                'content' => $message['message']
            );
        }
        
        return $formattedHistory;
    }
}
