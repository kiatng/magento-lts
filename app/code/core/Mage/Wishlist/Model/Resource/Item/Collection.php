<?php

/**
 * @copyright  For copyright and license information, read the COPYING.txt file.
 * @link       /COPYING.txt
 * @license    Open Software License (OSL 3.0)
 * @package    Mage_Wishlist
 */

/**
 * Wishlist item collection
 *
 * @package    Mage_Wishlist
 *
 * @method Mage_Wishlist_Model_Item getItemById(int|string $value)
 * @method Mage_Wishlist_Model_Item[] getItems()
 */
class Mage_Wishlist_Model_Resource_Item_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Product Visibility Filter to product collection flag
     *
     * @var bool
     */
    protected $_productVisible = false;

    /**
     * Product Salable Filter to product collection flag
     *
     * @var bool
     */
    protected $_productSalable = false;

    /**
     * If product out of stock, its item will be removed after load
     *
     * @var bool
      */
    protected $_productInStock = false;

    /**
     * Product Ids array
     *
     * @var array
     */
    protected $_productIds = [];

    /**
     * Store Ids array
     *
     * @var array
     */
    protected $_storeIds = [];

    /**
     * Add days in wishlist filter of product collection
     *
     * @var bool
     */
    protected $_addDaysInWishlist = false;

    /**
     * Sum of items collection qty
     *
     * @var int|null
     */
    protected $_itemsQty;

    /**
     * Whether product name attribute value table is joined in select
     *
     * @var bool
     */
    protected $_isProductNameJoined = false;

    /**
     * Customer website ID
     *
     * @var int
     */
    protected $_websiteId = null;

    /**
     * Customer group ID
     *
     * @var int
     */
    protected $_customerGroupId = null;

    public function _construct()
    {
        $this->_init('wishlist/item');
        $this->addFilterToMap('store_id', 'main_table.store_id');
    }

    /**
     * After load processing
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();

        /**
         * Assign products
         */
        $this->_assignOptions();
        $this->_assignProducts();
        $this->resetItemsDataChanged();

        $this->getPageSize();

        return $this;
    }

    /**
     * Add options to items
     *
     * @return $this
     */
    protected function _assignOptions()
    {
        $itemIds = array_keys($this->_items);
        /** @var Mage_Wishlist_Model_Resource_Item_Option_Collection $optionCollection */
        $optionCollection = Mage::getModel('wishlist/item_option')->getCollection();
        $optionCollection->addItemFilter($itemIds);

        /** @var Mage_Wishlist_Model_Item $item */
        foreach ($this as $item) {
            $item->setOptions($optionCollection->getOptionsByItem($item));
        }
        $productIds = $optionCollection->getProductIds();
        $this->_productIds = array_merge($this->_productIds, $productIds);

        return $this;
    }

    /**
     * Add products to items and item options
     *
     * @return $this
     */
    protected function _assignProducts()
    {
        Varien_Profiler::start('WISHLIST:' . __METHOD__);
        $productIds = [];

        $isStoreAdmin = Mage::app()->getStore()->isAdmin();

        $storeIds = [];
        foreach ($this as $item) {
            $productIds[$item->getProductId()] = 1;
            if ($isStoreAdmin && !in_array($item->getStoreId(), $storeIds)) {
                $storeIds[] = $item->getStoreId();
            }
        }
        if (!$isStoreAdmin) {
            $storeIds = $this->_storeIds;
        }

        $this->_productIds = array_merge($this->_productIds, array_keys($productIds));
        $attributes = Mage::getSingleton('wishlist/config')->getProductAttributes();
        $productCollection = Mage::getModel('catalog/product')->getCollection();
        foreach ($storeIds as $id) {
            $productCollection->addWebsiteFilter(Mage::app()->getStore($id)->getWebsiteId());
        }

        if ($this->_productVisible) {
            Mage::getSingleton('catalog/product_visibility')->addVisibleInSiteFilterToCollection($productCollection);
        }

        $productCollection->addPriceData($this->_customerGroupId, $this->_websiteId)
            ->addTaxPercents()
            ->addIdFilter($this->_productIds)
            ->addAttributeToSelect($attributes)
            ->addOptionsToResult()
            ->addUrlRewrite();

        if ($this->_productSalable) {
            $productCollection = Mage::helper('adminhtml/sales')->applySalableProductTypesFilter($productCollection);
        }

        Mage::dispatchEvent('wishlist_item_collection_products_after_load', [
            'product_collection' => $productCollection,
        ]);

        $checkInStock = $this->_productInStock && !Mage::helper('cataloginventory')->isShowOutOfStock();

        foreach ($this as $item) {
            /** @var Mage_Catalog_Model_Product $product */
            $product = $productCollection->getItemById($item->getProductId());
            if ($product) {
                if ($checkInStock && !$product->isInStock()) {
                    $this->removeItemByKey($item->getId());
                } else {
                    $product->setCustomOptions([]);
                    $item->setProduct($product);
                    $item->setProductName($product->getName());
                    $item->setName($product->getName());
                    $item->setPrice($product->getPrice());
                }
            } else {
                $item->isDeleted(true);
            }
        }

        Varien_Profiler::stop('WISHLIST:' . __METHOD__);

        return $this;
    }

    /**
     * Add filter by wishlist object
     *
     * @return $this
     */
    public function addWishlistFilter(Mage_Wishlist_Model_Wishlist $wishlist)
    {
        $this->addFieldToFilter('wishlist_id', $wishlist->getId());
        return $this;
    }

    /**
     * Add filtration by customer id
     *
     * @param int $customerId
     * @return $this
     */
    public function addCustomerIdFilter($customerId)
    {
        $this->getSelect()
            ->join(
                ['wishlist' => $this->getTable('wishlist/wishlist')],
                'main_table.wishlist_id = wishlist.wishlist_id',
                [],
            )
            ->where('wishlist.customer_id = ?', $customerId);
        return $this;
    }

    /**
     * Add filter by shared stores
     *
     * @param array $storeIds
     *
     * @return $this
     */
    public function addStoreFilter($storeIds = [])
    {
        if (!is_array($storeIds)) {
            $storeIds = [$storeIds];
        }
        $this->_storeIds = $storeIds;
        $this->addFieldToFilter('store_id', ['in' => $this->_storeIds]);

        return $this;
    }

    /**
     * Add items store data to collection
     *
     * @return $this
     */
    public function addStoreData()
    {
        $storeTable = Mage::getSingleton('core/resource')->getTableName('core/store');
        $this->getSelect()->join(['store' => $storeTable], 'main_table.store_id=store.store_id', [
            'store_name' => 'name',
            'item_store_id' => 'store_id',
        ]);
        return $this;
    }

    /**
     * Add wishlist sort order
     *
     * @deprecated after 1.6.0.0-rc2
     * @see Varien_Data_Collection_Db::setOrder() is used instead
     *
     * @param string $attribute
     * @param string $dir
     * @return $this
     */
    public function addWishListSortOrder($attribute = 'added_at', $dir = 'desc')
    {
        $this->setOrder($attribute, $dir);
        return $this;
    }

    /**
     * Reset sort order
     *
     * @return $this
     */
    public function resetSortOrder()
    {
        $this->getSelect()->reset(Zend_Db_Select::ORDER);
        return $this;
    }

    /**
     * Set product Visibility Filter to product collection flag
     *
     * @param bool $flag
     * @return $this
     */
    public function setVisibilityFilter($flag = true)
    {
        $this->_productVisible = (bool) $flag;
        return $this;
    }

    /**
     * Set Salable Filter.
     * This filter apply Salable Product Types Filter to product collection.
     *
     * @param bool $flag
     * @return $this
     */
    public function setSalableFilter($flag = true)
    {
        $this->_productSalable = (bool) $flag;
        return $this;
    }

    /**
     * Set In Stock Filter.
     * This filter remove items with no salable product.
     *
     * @param bool $flag
     * @return $this
     */
    public function setInStockFilter($flag = true)
    {
        $this->_productInStock = (bool) $flag;
        return $this;
    }

    /**
     * Set add days in wishlist
     *
     * This method appears in 1.5.0.0 in deprecated state, because:
     * - we need it to make wishlist item collection interface as much as possible compatible with old
     *   wishlist product collection
     * - this method is useless because we can calculate days in php, and don't use MySQL for it
     *
     * @deprecated after 1.4.2.0
     * @return $this
     */
    public function addDaysInWishlist()
    {
        $this->_addDaysInWishlist = true;

        $adapter = $this->getConnection();
        $dateModel = Mage::getSingleton('core/date');
        /** @var Mage_Core_Model_Resource_Helper_Mysql4 $resHelper */
        $resHelper = Mage::getResourceHelper('core');

        $offsetFromDb = (int) $dateModel->getGmtOffset();
        $startDate = $adapter->getDateAddSql('added_at', $offsetFromDb, Varien_Db_Adapter_Interface::INTERVAL_SECOND);

        $nowDate = $dateModel->date();
        $dateDiff = $resHelper->getDateDiff($startDate, $adapter->formatDate($nowDate));

        $this->getSelect()->columns(['days_in_wishlist' => $dateDiff]);
        return $this;
    }

    /**
     * Adds filter on days in wishlist
     *
     * $constraints may contain 'from' and 'to' indexes with number of days to look for items
     *
     * @param array $constraints
     * @return $this
     */
    public function addDaysFilter($constraints)
    {
        if (!is_array($constraints)) {
            return $this;
        }

        $filter = [];

        $now = Mage::getSingleton('core/date')->date();
        $gmtOffset = (int) Mage::getSingleton('core/date')->getGmtOffset();
        if (isset($constraints['from'])) {
            $lastDay = new Zend_Date($now, Varien_Date::DATETIME_INTERNAL_FORMAT);
            $lastDay->subSecond($gmtOffset)
                ->subDay($constraints['from'] - 1);
            $filter['to'] = $lastDay;
        }

        if (isset($constraints['to'])) {
            $firstDay = new Zend_Date($now, Varien_Date::DATETIME_INTERNAL_FORMAT);
            $firstDay->subSecond($gmtOffset)
                ->subDay($constraints['to']);
            $filter['from'] = $firstDay;
        }

        if ($filter) {
            $filter['datetime'] = true;
            $this->addFieldToFilter('added_at', $filter);
        }

        return $this;
    }

    /**
     * Joins product name attribute value to use it in WHERE and ORDER clauses
     *
     * @return $this
     */
    protected function _joinProductNameTable()
    {
        if (!$this->_isProductNameJoined) {
            $entityTypeId = Mage::getResourceModel('catalog/config')
                    ->getEntityTypeId();
            $attribute = Mage::getModel('catalog/entity_attribute')
                ->loadByCode($entityTypeId, 'name');

            $storeId = Mage::app()->getStore()->getId();

            $this->getSelect()
                ->join(
                    ['product_name_table' => $attribute->getBackendTable()],
                    'product_name_table.entity_id=main_table.product_id' .
                        ' AND product_name_table.store_id=' . $storeId .
                        ' AND product_name_table.attribute_id=' . $attribute->getId() .
                        ' AND product_name_table.entity_type_id=' . $entityTypeId,
                    [],
                );

            $this->_isProductNameJoined = true;
        }
        return $this;
    }

    /**
     * Adds filter on product name
     *
     * @param string $productName
     * @return $this
     */
    public function addProductNameFilter($productName)
    {
        $this->_joinProductNameTable();
        $this->getSelect()
            ->where('INSTR(product_name_table.value, ?)', $productName);

        return $this;
    }

    /**
     * Sets ordering by product name
     *
     * @param string $dir
     * @return $this
     */
    public function setOrderByProductName($dir)
    {
        $this->_joinProductNameTable();
        $this->getSelect()->order('product_name_table.value ' . $dir);
        return $this;
    }

    /**
     * Get sum of items collection qty
     *
     * @return int
     */
    public function getItemsQty()
    {
        if (is_null($this->_itemsQty)) {
            $this->_itemsQty = 0;
            foreach ($this as $wishlistItem) {
                $qty = $wishlistItem->getQty();
                $this->_itemsQty += ($qty === 0) ? 1 : $qty;
            }
        }

        return (int) $this->_itemsQty;
    }

    /**
     * Setter for $_websiteId
     *
     * @param int $websiteId
     * @return $this
     */
    public function setWebsiteId($websiteId)
    {
        $this->_websiteId = $websiteId;
        return $this;
    }

    /**
     * Setter for $_customerGroupId
     *
     * @param int $customerGroupId
     * @return $this
     */
    public function setCustomerGroupId($customerGroupId)
    {
        $this->_customerGroupId = $customerGroupId;
        return $this;
    }
}
