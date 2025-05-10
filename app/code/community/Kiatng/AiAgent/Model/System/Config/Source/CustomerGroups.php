<?php
/**
 * Kiatng AiAgent Extension
 *
 * @category   Kiatng
 * @package    Kiatng_AiAgent
 * @author     Kiatng
 */
class Kiatng_AiAgent_Model_System_Config_Source_CustomerGroups
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $customerGroups = Mage::getResourceModel('customer/group_collection')
            ->loadData()
            ->toOptionArray();
        
        return $customerGroups;
    }
}
