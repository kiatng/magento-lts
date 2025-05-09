<?php

/**
 * @copyright  For copyright and license information, read the COPYING.txt file.
 * @link       /COPYING.txt
 * @license    Open Software License (OSL 3.0)
 * @package    Mage_Eav
 */

/**
 * Eav Form Element Resource Collection
 *
 * @package    Mage_Eav
 */
class Mage_Eav_Model_Resource_Form_Element_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Initialize collection model
     */
    protected function _construct()
    {
        $this->_init('eav/form_element');
    }

    /**
     * Add Form Type filter to collection
     *
     * @param Mage_Eav_Model_Form_Type|int $type
     * @return $this
     */
    public function addTypeFilter($type)
    {
        if ($type instanceof Mage_Eav_Model_Form_Type) {
            $type = $type->getId();
        }

        return $this->addFieldToFilter('type_id', $type);
    }

    /**
     * Add Form Fieldset filter to collection
     *
     * @param Mage_Eav_Model_Form_Fieldset|int $fieldset
     * @return $this
     */
    public function addFieldsetFilter($fieldset)
    {
        if ($fieldset instanceof Mage_Eav_Model_Form_Fieldset) {
            $fieldset = $fieldset->getId();
        }

        return $this->addFieldToFilter('fieldset_id', $fieldset);
    }

    /**
     * Add Attribute filter to collection
     *
     * @param Mage_Eav_Model_Entity_Attribute_Abstract|int $attribute
     *
     * @return $this
     */
    public function addAttributeFilter($attribute)
    {
        if ($attribute instanceof Mage_Eav_Model_Entity_Attribute_Abstract) {
            $attribute = $attribute->getId();
        }

        return $this->addFieldToFilter('attribute_id', $attribute);
    }

    /**
     * Set order by element sort order
     *
     * @return $this
     */
    public function setSortOrder()
    {
        $this->setOrder('sort_order', self::SORT_ORDER_ASC);

        return $this;
    }

    /**
     * Join attribute data
     *
     * @return $this
     */
    protected function _joinAttributeData()
    {
        $this->getSelect()->join(
            ['eav_attribute' => $this->getTable('eav/attribute')],
            'main_table.attribute_id = eav_attribute.attribute_id',
            ['attribute_code', 'entity_type_id'],
        );

        return $this;
    }

    /**
     * Load data (join attribute data)
     *
     * @inheritDoc
     */
    public function load($printQuery = false, $logQuery = false)
    {
        if (!$this->isLoaded()) {
            $this->_joinAttributeData();
        }
        return parent::load($printQuery, $logQuery);
    }
}
