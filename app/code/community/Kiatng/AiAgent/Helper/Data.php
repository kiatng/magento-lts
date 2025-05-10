<?php
/**
 * Kiatng AiAgent Extension
 *
 * @category   Kiatng
 * @package    Kiatng_AiAgent
 * @author     Kiatng
 */
class Kiatng_AiAgent_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_ENABLED = 'aiagent/general/enabled';
    const XML_PATH_CUSTOMER_GROUPS = 'aiagent/general/customer_groups';
    const XML_PATH_PROVIDER = 'aiagent/api/provider';
    const XML_PATH_MODEL = 'aiagent/api/model';
    const XML_PATH_ANTHROPIC_MODEL = 'aiagent/api/anthropic_model';
    const XML_PATH_NVIDIA_MODEL = 'aiagent/api/nvidia_model';
    const XML_PATH_API_KEY = 'aiagent/api/key';
    const XML_PATH_ENABLE_RAG = 'aiagent/features/enable_rag';
    const XML_PATH_ENABLE_CAG = 'aiagent/features/enable_cag';
    const XML_PATH_ENABLE_WEB_SEARCH_ADMIN = 'aiagent/features/enable_web_search_admin';

    /**
     * Check if module is enabled
     *
     * @param mixed $store
     * @return bool
     */
    public function isEnabled($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ENABLED, $store);
    }

    /**
     * Check if module is enabled for current customer
     *
     * @return bool
     */
    public function isEnabledForCurrentCustomer()
    {
        if (!$this->isEnabled()) {
            return false;
        }

        if (Mage::app()->getStore()->isAdmin()) {
            return true;
        }

        $allowedGroups = $this->getAllowedCustomerGroups();
        $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();

        return in_array($customerGroupId, $allowedGroups);
    }

    /**
     * Get allowed customer groups
     *
     * @param mixed $store
     * @return array
     */
    public function getAllowedCustomerGroups($store = null)
    {
        $groups = Mage::getStoreConfig(self::XML_PATH_CUSTOMER_GROUPS, $store);
        return explode(',', $groups);
    }

    /**
     * Get API provider
     *
     * @param mixed $store
     * @return string
     */
    public function getApiProvider($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_PROVIDER, $store);
    }

    /**
     * Get API model
     *
     * @param mixed $store
     * @return string
     */
    public function getApiModel($store = null)
    {
        $provider = $this->getApiProvider($store);
        
        switch ($provider) {
            case 'openai':
                return Mage::getStoreConfig(self::XML_PATH_MODEL, $store);
            case 'anthropic':
                return Mage::getStoreConfig(self::XML_PATH_ANTHROPIC_MODEL, $store);
            case 'nvidia':
                return Mage::getStoreConfig(self::XML_PATH_NVIDIA_MODEL, $store);
            default:
                return Mage::getStoreConfig(self::XML_PATH_MODEL, $store);
        }
    }

    /**
     * Get API key
     *
     * @param mixed $store
     * @return string
     */
    public function getApiKey($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_API_KEY, $store);
    }

    /**
     * Check if RAG is enabled
     *
     * @param mixed $store
     * @return bool
     */
    public function isRagEnabled($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ENABLE_RAG, $store);
    }

    /**
     * Check if CAG is enabled
     *
     * @param mixed $store
     * @return bool
     */
    public function isCagEnabled($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ENABLE_CAG, $store);
    }

    /**
     * Check if web search is enabled for admin
     *
     * @param mixed $store
     * @return bool
     */
    public function isWebSearchEnabledForAdmin($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ENABLE_WEB_SEARCH_ADMIN, $store);
    }

    /**
     * Get AI adapter based on configuration
     *
     * @return Kiatng_AiAgent_Model_Adapter_Abstract
     */
    public function getAiAdapter()
    {
        $provider = $this->getApiProvider();
        $model = $this->getApiModel();
        $apiKey = $this->getApiKey();
        
        return Mage::getModel('aiagent/adapter_factory')->create($provider, $model, $apiKey);
    }
}
