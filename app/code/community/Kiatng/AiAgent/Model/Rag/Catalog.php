<?php
/**
 * Kiatng AiAgent Extension
 *
 * @category   Kiatng
 * @package    Kiatng_AiAgent
 * @author     Kiatng
 */
class Kiatng_AiAgent_Model_Rag_Catalog extends Varien_Object
{
    /**
     * Get relevant product information based on query
     *
     * @param string $query User query
     * @return array Relevant product information
     */
    public function getRelevantProductInfo($query)
    {
        $relevantInfo = array();
        
        $keywords = $this->extractKeywords($query);
        
        if (empty($keywords)) {
            return $relevantInfo;
        }
        
        $products = $this->searchProducts($keywords);
        
        if (empty($products)) {
            return $relevantInfo;
        }
        
        foreach ($products as $product) {
            $productInfo = $this->formatProductInfo($product);
            $relevantInfo[] = $productInfo;
        }
        
        return $relevantInfo;
    }
    
    /**
     * Extract keywords from query
     *
     * @param string $query User query
     * @return array Keywords
     */
    protected function extractKeywords($query)
    {
        $stopWords = array('a', 'an', 'the', 'and', 'or', 'but', 'is', 'are', 'in', 'on', 'at', 'to', 'for', 'with', 'about');
        $query = strtolower($query);
        $query = preg_replace('/[^\w\s]/', '', $query);
        $words = explode(' ', $query);
        $keywords = array();
        
        foreach ($words as $word) {
            $word = trim($word);
            if (!empty($word) && !in_array($word, $stopWords) && strlen($word) > 2) {
                $keywords[] = $word;
            }
        }
        
        return $keywords;
    }
    
    /**
     * Search for products based on keywords
     *
     * @param array $keywords Keywords
     * @return array Products
     */
    protected function searchProducts($keywords)
    {
        $products = array();
        
        try {
            $collection = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToSelect(array('name', 'description', 'short_description', 'price', 'sku'))
                ->setPageSize(5)
                ->setCurPage(1);
            
            $conditions = array();
            foreach ($keywords as $keyword) {
                $conditions[] = array('like' => '%' . $keyword . '%');
            }
            
            $collection->addAttributeToFilter(array(
                array('attribute' => 'name', 'conditions' => $conditions),
                array('attribute' => 'description', 'conditions' => $conditions),
                array('attribute' => 'short_description', 'conditions' => $conditions),
            ));
            
            $products = $collection->getItems();
        } catch (Exception $e) {
            Mage::logException($e);
        }
        
        return $products;
    }
    
    /**
     * Format product information
     *
     * @param Mage_Catalog_Model_Product $product Product
     * @return string Formatted product information
     */
    protected function formatProductInfo($product)
    {
        $info = "Product: " . $product->getName() . "\n";
        $info .= "SKU: " . $product->getSku() . "\n";
        $info .= "Price: " . Mage::helper('core')->currency($product->getPrice(), true, false) . "\n";
        
        if ($product->getShortDescription()) {
            $info .= "Description: " . strip_tags($product->getShortDescription()) . "\n";
        } elseif ($product->getDescription()) {
            $info .= "Description: " . strip_tags($product->getDescription()) . "\n";
        }
        
        return $info;
    }
    
    /**
     * Get information about a specific product
     *
     * @param int $productId Product ID
     * @return string Product information
     */
    public function getProductInfo($productId)
    {
        try {
            $product = Mage::getModel('catalog/product')->load($productId);
            
            if (!$product->getId()) {
                return '';
            }
            
            return $this->formatProductInfo($product);
        } catch (Exception $e) {
            Mage::logException($e);
            return '';
        }
    }
}
