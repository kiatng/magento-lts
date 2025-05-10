<?php
/**
 * Kiatng AiAgent Extension
 *
 * @category   Kiatng
 * @package    Kiatng_AiAgent
 * @author     Kiatng
 */
class Kiatng_AiAgent_Model_System_Config_Source_Provider
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'openai', 'label' => Mage::helper('aiagent')->__('OpenAI')),
            array('value' => 'anthropic', 'label' => Mage::helper('aiagent')->__('Anthropic')),
            array('value' => 'nvidia', 'label' => Mage::helper('aiagent')->__('Nvidia')),
        );
    }
}
