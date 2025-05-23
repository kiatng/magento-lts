<?php

/**
 * @copyright  For copyright and license information, read the COPYING.txt file.
 * @link       /COPYING.txt
 * @license    Open Software License (OSL 3.0)
 * @package    Mage_Adminhtml
 */

/**
 * Convert profile edit tab
 *
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_System_Convert_Profile_Edit_Tab_History extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Mage_Adminhtml_Block_System_Convert_Profile_Edit_Tab_History constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('history_grid');
        $this->setDefaultSort('performed_at');
        $this->setDefaultDir('desc');
        $this->setUseAjax(true);
    }

    /**
     * @inheritDoc
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('dataflow/profile_history_collection')
            ->joinAdminUser()
            ->addFieldToFilter('profile_id', Mage::registry('current_convert_profile')->getId());
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('action_code', [
            'header'    => Mage::helper('adminhtml')->__('Profile Action'),
            'index'     => 'action_code',
            'filter'    => 'adminhtml/system_convert_profile_edit_filter_action',
            'renderer'  => 'adminhtml/system_convert_profile_edit_renderer_action',
        ]);

        $this->addColumn('performed_at', [
            'header'    => Mage::helper('adminhtml')->__('Performed At'),
            'type'      => 'datetime',
            'index'     => 'performed_at',
        ]);

        $this->addColumn('firstname', [
            'header'    => Mage::helper('adminhtml')->__('First Name'),
            'index'     => 'firstname',
        ]);

        $this->addColumn('lastname', [
            'header'    => Mage::helper('adminhtml')->__('Last Name'),
            'index'     => 'lastname',
        ]);

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/history', ['_current' => true]);
    }
}
