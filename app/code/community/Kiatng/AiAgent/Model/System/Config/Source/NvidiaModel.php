<?php
/**
 * Kiatng AiAgent Extension
 *
 * @category   Kiatng
 * @package    Kiatng_AiAgent
 * @author     Kiatng
 */
class Kiatng_AiAgent_Model_System_Config_Source_NvidiaModel
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'llama3-70b', 'label' => Mage::helper('aiagent')->__('Llama 3 70B')),
            array('value' => 'llama3-8b', 'label' => Mage::helper('aiagent')->__('Llama 3 8B')),
            array('value' => 'mixtral-8x7b', 'label' => Mage::helper('aiagent')->__('Mixtral 8x7B')),
        );
    }
}
