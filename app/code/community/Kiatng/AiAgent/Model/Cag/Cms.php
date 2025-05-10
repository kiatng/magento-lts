<?php
/**
 * Kiatng AiAgent Extension
 *
 * @category   Kiatng
 * @package    Kiatng_AiAgent
 * @author     Kiatng
 */
class Kiatng_AiAgent_Model_Cag_Cms extends Varien_Object
{
    /**
     * Get relevant CMS information based on query
     *
     * @param string $query User query
     * @return array Relevant CMS information
     */
    public function getRelevantCmsInfo($query)
    {
        $relevantInfo = array();
        
        $keywords = $this->extractKeywords($query);
        
        if (empty($keywords)) {
            return $relevantInfo;
        }
        
        $cmsPages = $this->searchCmsPages($keywords);
        
        if (!empty($cmsPages)) {
            foreach ($cmsPages as $page) {
                $pageInfo = $this->formatCmsPageInfo($page);
                $relevantInfo[] = $pageInfo;
            }
        }
        
        $cmsBlocks = $this->searchCmsBlocks($keywords);
        
        if (!empty($cmsBlocks)) {
            foreach ($cmsBlocks as $block) {
                $blockInfo = $this->formatCmsBlockInfo($block);
                $relevantInfo[] = $blockInfo;
            }
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
     * Search for CMS pages based on keywords
     *
     * @param array $keywords Keywords
     * @return array CMS pages
     */
    protected function searchCmsPages($keywords)
    {
        $pages = array();
        
        try {
            $collection = Mage::getModel('cms/page')->getCollection()
                ->addFieldToSelect(array('title', 'content', 'identifier'))
                ->setPageSize(3)
                ->setCurPage(1);
            
            $conditions = array();
            foreach ($keywords as $keyword) {
                $conditions[] = array('like' => '%' . $keyword . '%');
            }
            
            $collection->addFieldToFilter(array(
                array('field' => 'title', 'condition' => $conditions),
                array('field' => 'content', 'condition' => $conditions),
            ));
            
            $pages = $collection->getItems();
        } catch (Exception $e) {
            Mage::logException($e);
        }
        
        return $pages;
    }
    
    /**
     * Search for CMS blocks based on keywords
     *
     * @param array $keywords Keywords
     * @return array CMS blocks
     */
    protected function searchCmsBlocks($keywords)
    {
        $blocks = array();
        
        try {
            $collection = Mage::getModel('cms/block')->getCollection()
                ->addFieldToSelect(array('title', 'content', 'identifier'))
                ->setPageSize(3)
                ->setCurPage(1);
            
            $conditions = array();
            foreach ($keywords as $keyword) {
                $conditions[] = array('like' => '%' . $keyword . '%');
            }
            
            $collection->addFieldToFilter(array(
                array('field' => 'title', 'condition' => $conditions),
                array('field' => 'content', 'condition' => $conditions),
            ));
            
            $blocks = $collection->getItems();
        } catch (Exception $e) {
            Mage::logException($e);
        }
        
        return $blocks;
    }
    
    /**
     * Format CMS page information
     *
     * @param Mage_Cms_Model_Page $page CMS page
     * @return string Formatted CMS page information
     */
    protected function formatCmsPageInfo($page)
    {
        $info = "CMS Page: " . $page->getTitle() . "\n";
        $info .= "URL Key: " . $page->getIdentifier() . "\n";
        
        if ($page->getContent()) {
            $content = strip_tags($page->getContent());
            $content = preg_replace('/\s+/', ' ', $content);
            $content = trim($content);
            
            if (strlen($content) > 500) {
                $content = substr($content, 0, 500) . '...';
            }
            
            $info .= "Content: " . $content . "\n";
        }
        
        return $info;
    }
    
    /**
     * Format CMS block information
     *
     * @param Mage_Cms_Model_Block $block CMS block
     * @return string Formatted CMS block information
     */
    protected function formatCmsBlockInfo($block)
    {
        $info = "CMS Block: " . $block->getTitle() . "\n";
        $info .= "Identifier: " . $block->getIdentifier() . "\n";
        
        if ($block->getContent()) {
            $content = strip_tags($block->getContent());
            $content = preg_replace('/\s+/', ' ', $content);
            $content = trim($content);
            
            if (strlen($content) > 500) {
                $content = substr($content, 0, 500) . '...';
            }
            
            $info .= "Content: " . $content . "\n";
        }
        
        return $info;
    }
    
    /**
     * Generate HTML content for CMS
     *
     * @param string $prompt Content generation prompt
     * @param array $parameters Additional parameters
     * @return string Generated HTML content
     */
    public function generateCmsContent($prompt, $parameters = array())
    {
        $helper = Mage::helper('aiagent');
        $adapter = $helper->getAiAdapter();
        
        $systemPrompt = "Generate HTML content for a CMS page or block. "
            . "The content should be well-formatted, SEO-friendly, and include appropriate HTML tags. "
            . "Do not include <html>, <head>, or <body> tags.";
        
        $fullPrompt = $systemPrompt . "\n\n" . $prompt;
        
        return $adapter->generateContent($fullPrompt, $parameters);
    }
}
