<?php
/**
 * Kiatng AiAgent Extension
 *
 * @category   Kiatng
 * @package    Kiatng_AiAgent
 * @author     Kiatng
 */
class Kiatng_AiAgent_Block_Adminhtml_Customer_Edit_Tab_Chat
    extends Mage_Adminhtml_Block_Widget_Grid
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Initialize grid
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('customer_chat_grid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }
    
    /**
     * Prepare collection
     *
     * @return Kiatng_AiAgent_Block_Adminhtml_Customer_Edit_Tab_Chat
     */
    protected function _prepareCollection()
    {
        $customerId = $this->getRequest()->getParam('id');
        
        if ($customerId) {
            $collection = Mage::getModel('aiagent/chat')->getCollection()
                ->addFieldToFilter('customer_id', $customerId);
            
            $this->setCollection($collection);
        }
        
        return parent::_prepareCollection();
    }
    
    /**
     * Prepare columns
     *
     * @return Kiatng_AiAgent_Block_Adminhtml_Customer_Edit_Tab_Chat
     */
    protected function _prepareColumns()
    {
        $this->addColumn('chat_id', array(
            'header' => Mage::helper('aiagent')->__('Chat ID'),
            'index' => 'chat_id',
            'type' => 'number',
            'width' => '50px',
        ));
        
        $this->addColumn('created_at', array(
            'header' => Mage::helper('aiagent')->__('Created At'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '150px',
        ));
        
        $this->addColumn('updated_at', array(
            'header' => Mage::helper('aiagent')->__('Last Message'),
            'index' => 'updated_at',
            'type' => 'datetime',
            'width' => '150px',
        ));
        
        $this->addColumn('is_active', array(
            'header' => Mage::helper('aiagent')->__('Active'),
            'index' => 'is_active',
            'type' => 'options',
            'options' => array(
                0 => Mage::helper('aiagent')->__('No'),
                1 => Mage::helper('aiagent')->__('Yes'),
            ),
            'width' => '80px',
        ));
        
        $this->addColumn('action', array(
            'header' => Mage::helper('aiagent')->__('Action'),
            'width' => '100px',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('aiagent')->__('View Messages'),
                    'url' => array('base' => '*/*/viewChatMessages'),
                    'field' => 'chat_id'
                )
            ),
            'filter' => false,
            'sortable' => false,
            'is_system' => true,
        ));
        
        return parent::_prepareColumns();
    }
    
    /**
     * Get row URL
     *
     * @param Varien_Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/viewChatMessages', array('chat_id' => $row->getId()));
    }
    
    /**
     * Get grid URL
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/chatGrid', array('_current' => true));
    }
    
    /**
     * Get tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('aiagent')->__('AI Chat History');
    }
    
    /**
     * Get tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('aiagent')->__('AI Chat History');
    }
    
    /**
     * Check if tab can be shown
     *
     * @return bool
     */
    public function canShowTab()
    {
        return (bool)$this->getRequest()->getParam('id');
    }
    
    /**
     * Check if tab is hidden
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
}
