<?php
/**
 * Kiatng AiAgent Extension
 *
 * @category   Kiatng
 * @package    Kiatng_AiAgent
 * @author     Kiatng
 */
class Kiatng_AiAgent_Model_Adapter_Openai extends Kiatng_AiAgent_Model_Adapter_Abstract
{
    /**
     * API endpoint for OpenAI
     */
    const API_ENDPOINT = 'https://api.openai.com/v1/chat/completions';
    
    /**
     * Send a message to OpenAI and get a response
     *
     * @param string $message User message
     * @param array $chatHistory Previous messages in the conversation
     * @param array $context Additional context (RAG/CAG data)
     * @return string AI response
     */
    public function sendMessage($message, $chatHistory = array(), $context = array())
    {
        $apiKey = $this->getApiKey();
        $model = $this->getModel();
        
        if (empty($apiKey)) {
            Mage::throwException(Mage::helper('aiagent')->__('OpenAI API key is not configured'));
        }
        
        $isAdmin = Mage::app()->getStore()->isAdmin();
        $systemPrompt = $this->getSystemPrompt($isAdmin ? 'admin' : 'frontend');
        
        if (!empty($context)) {
            $systemPrompt .= "\n\nHere is some relevant information that might help you answer:\n";
            $systemPrompt .= implode("\n", $context);
        }
        
        $messages = array(
            array('role' => 'system', 'content' => $systemPrompt)
        );
        
        if (!empty($chatHistory)) {
            $formattedHistory = $this->formatChatHistory($chatHistory);
            $messages = array_merge($messages, $formattedHistory);
        }
        
        $messages[] = array('role' => 'user', 'content' => $message);
        
        $data = array(
            'model' => $model,
            'messages' => $messages,
            'temperature' => 0.7,
            'max_tokens' => 1000,
        );
        
        try {
            $response = $this->makeApiRequest(self::API_ENDPOINT, $data);
            
            if (isset($response['choices'][0]['message']['content'])) {
                return $response['choices'][0]['message']['content'];
            } else {
                Mage::throwException(Mage::helper('aiagent')->__('Invalid response from OpenAI API'));
            }
        } catch (Exception $e) {
            Mage::logException($e);
            return Mage::helper('aiagent')->__('Sorry, I encountered an error while processing your request. Please try again later.');
        }
    }
    
    /**
     * Generate content for CMS or product descriptions
     *
     * @param string $prompt Content generation prompt
     * @param array $parameters Additional parameters for content generation
     * @return string Generated content
     */
    public function generateContent($prompt, $parameters = array())
    {
        $apiKey = $this->getApiKey();
        $model = $this->getModel();
        
        if (empty($apiKey)) {
            Mage::throwException(Mage::helper('aiagent')->__('OpenAI API key is not configured'));
        }
        
        $systemPrompt = "You are a content generation assistant for an OpenMage store. "
            . "Generate high-quality, SEO-friendly content based on the provided prompt. "
            . "Format the content in HTML when appropriate.";
        
        $messages = array(
            array('role' => 'system', 'content' => $systemPrompt),
            array('role' => 'user', 'content' => $prompt)
        );
        
        $data = array(
            'model' => $model,
            'messages' => $messages,
            'temperature' => isset($parameters['temperature']) ? $parameters['temperature'] : 0.7,
            'max_tokens' => isset($parameters['max_tokens']) ? $parameters['max_tokens'] : 2000,
        );
        
        try {
            $response = $this->makeApiRequest(self::API_ENDPOINT, $data);
            
            if (isset($response['choices'][0]['message']['content'])) {
                return $response['choices'][0]['message']['content'];
            } else {
                Mage::throwException(Mage::helper('aiagent')->__('Invalid response from OpenAI API'));
            }
        } catch (Exception $e) {
            Mage::logException($e);
            return Mage::helper('aiagent')->__('Sorry, I encountered an error while generating content. Please try again later.');
        }
    }
    
    /**
     * Search the web for information (admin only)
     *
     * @param string $query Search query
     * @return array Search results
     */
    public function searchWeb($query)
    {
        $apiKey = $this->getApiKey();
        $model = $this->getModel();
        
        if (empty($apiKey)) {
            Mage::throwException(Mage::helper('aiagent')->__('OpenAI API key is not configured'));
        }
        
        $systemPrompt = "You are a web search assistant. Based on the user's query, "
            . "provide a summary of what information you would search for online. "
            . "Format your response as a JSON array with 'title' and 'snippet' fields for each result.";
        
        $messages = array(
            array('role' => 'system', 'content' => $systemPrompt),
            array('role' => 'user', 'content' => "Search for: " . $query)
        );
        
        $data = array(
            'model' => $model,
            'messages' => $messages,
            'temperature' => 0.3,
            'max_tokens' => 1000,
        );
        
        try {
            $response = $this->makeApiRequest(self::API_ENDPOINT, $data);
            
            if (isset($response['choices'][0]['message']['content'])) {
                $content = $response['choices'][0]['message']['content'];
                if (preg_match('/\[.*\]/s', $content, $matches)) {
                    $jsonStr = $matches[0];
                    $results = json_decode($jsonStr, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        return $results;
                    }
                }
                
                return array(
                    array(
                        'title' => 'Search Results',
                        'snippet' => $content
                    )
                );
            } else {
                Mage::throwException(Mage::helper('aiagent')->__('Invalid response from OpenAI API'));
            }
        } catch (Exception $e) {
            Mage::logException($e);
            return array(
                array(
                    'title' => 'Error',
                    'snippet' => Mage::helper('aiagent')->__('Sorry, I encountered an error while searching. Please try again later.')
                )
            );
        }
    }
    
    /**
     * Make API request to OpenAI
     *
     * @param string $endpoint API endpoint
     * @param array $data Request data
     * @return array Response data
     */
    protected function makeApiRequest($endpoint, $data)
    {
        $apiKey = $this->getApiKey();
        
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            Mage::throwException(Mage::helper('aiagent')->__('cURL error: %s', curl_error($ch)));
        }
        
        curl_close($ch);
        
        if ($httpCode >= 400) {
            Mage::throwException(Mage::helper('aiagent')->__('API error: HTTP code %s, Response: %s', $httpCode, $response));
        }
        
        $responseData = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            Mage::throwException(Mage::helper('aiagent')->__('Invalid JSON response from API'));
        }
        
        return $responseData;
    }
}
