<?php
/**
 * Kiatng AiAgent Extension
 *
 * @category   Kiatng
 * @package    Kiatng_AiAgent
 * @author     Kiatng
 */
class Kiatng_AiAgent_Model_System_Config_Source_AnthropicModel
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'claude-3-opus', 'label' => Mage::helper('aiagent')->__('Claude 3 Opus')),
            array('value' => 'claude-3-sonnet', 'label' => Mage::helper('aiagent')->__('Claude 3 Sonnet')),
            array('value' => 'claude-3-haiku', 'label' => Mage::helper('aiagent')->__('Claude 3 Haiku')),
        );
    }
}
