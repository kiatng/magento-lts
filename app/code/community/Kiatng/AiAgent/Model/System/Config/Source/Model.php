<?php
/**
 * Kiatng AiAgent Extension
 *
 * @category   Kiatng
 * @package    Kiatng_AiAgent
 * @author     Kiatng
 */
class Kiatng_AiAgent_Model_System_Config_Source_Model
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'gpt-4', 'label' => Mage::helper('aiagent')->__('GPT-4')),
            array('value' => 'gpt-4-turbo', 'label' => Mage::helper('aiagent')->__('GPT-4 Turbo')),
            array('value' => 'gpt-3.5-turbo', 'label' => Mage::helper('aiagent')->__('GPT-3.5 Turbo')),
        );
    }
}
