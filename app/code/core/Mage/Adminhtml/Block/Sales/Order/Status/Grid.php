<?php

/**
 * @copyright  For copyright and license information, read the COPYING.txt file.
 * @link       /COPYING.txt
 * @license    Open Software License (OSL 3.0)
 * @package    Mage_Adminhtml
 */

/**
 * Adminhtml sales order's statuses grid
 *
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_Sales_Order_Status_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('sales_order_status_grid');
        //$this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        $this->setDefaultSort('state');
        $this->setDefaultDir('DESC');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('sales/order_status_collection');
        $collection->joinStates();
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('label', [
            'header' => Mage::helper('sales')->__('Status'),
            'index' => 'label',
        ]);

        $this->addColumn('status', [
            'header' => Mage::helper('sales')->__('Status Code'),
            'type'  => 'text',
            'index' => 'status',
            'filter_index' => 'main_table.status',
            'width'     => '200px',
        ]);

        $this->addColumn('is_default', [
            'header'    => Mage::helper('sales')->__('Default Status'),
            'index'     => 'is_default',
            'width'     => '100px',
            'type'      => 'options',
            'options'   => [0 => $this->__('No'), 1 => $this->__('Yes')],
            'sortable'  => false,
        ]);

        $this->addColumn('state', [
            'header' => Mage::helper('sales')->__('State Code [State Title]'),
            'type'  => 'text',
            'index' => 'state',
            'width'     => '250px',
            'frame_callback' => [$this, 'decorateState'],
        ]);

        $this->addColumn('unassign', [
            'header'    => Mage::helper('sales')->__('Action'),
            'index'     => 'unassign',
            'width'     => '100px',
            'type'      => 'text',
            'frame_callback' => [$this, 'decorateAction'],
            'sortable'  => false,
            'filter'    => false,
        ]);

        return parent::_prepareColumns();
    }

    /**
     * Decorate status column values
     *
     * @return string
     */
    public function decorateState($value, $row, $column, $isExport)
    {
        if ($value) {
            $cell = $value . ' [' . Mage::getSingleton('sales/order_config')->getStateLabel($value) . ']';
        } else {
            $cell = $value;
        }
        return $cell;
    }

    public function decorateAction($value, $row, $column, $isExport)
    {
        $cell = '';
        $state = $row->getState();
        if (!empty($state)) {
            $url = $this->getUrl(
                '*/*/unassign',
                ['status' => $row->getStatus(), 'state' => $row->getState()],
            );
            $label = Mage::helper('sales')->__('Unassign');
            $cell = '<a href="' . $url . '">' . $label . '</a>';
        }
        return $cell;
    }

    /**
     * No pegination for this grid
     */
    protected function _preparePage()
    {
        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/sales_order_status/edit', ['status' => $row->getStatus()]);
    }
}
