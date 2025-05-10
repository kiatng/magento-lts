<?php
/**
 * Kiatng AiAgent Extension
 *
 * @category   Kiatng
 * @package    Kiatng_AiAgent
 * @author     Kiatng
 */
class Kiatng_AiAgent_Model_Adapter_Factory
{
    /**
     * Create AI adapter based on provider
     *
     * @param string $provider
     * @param string $model
     * @param string $apiKey
     * @return Kiatng_AiAgent_Model_Adapter_Abstract
     * @throws Exception
     */
    public function create($provider, $model, $apiKey)
    {
        switch ($provider) {
            case 'openai':
                return Mage::getModel('aiagent/adapter_openai')
                    ->setModel($model)
                    ->setApiKey($apiKey);
            case 'anthropic':
                return Mage::getModel('aiagent/adapter_anthropic')
                    ->setModel($model)
                    ->setApiKey($apiKey);
            case 'nvidia':
                return Mage::getModel('aiagent/adapter_nvidia')
                    ->setModel($model)
                    ->setApiKey($apiKey);
            default:
                throw new Exception(Mage::helper('aiagent')->__('Unsupported AI provider: %s', $provider));
        }
    }
}
