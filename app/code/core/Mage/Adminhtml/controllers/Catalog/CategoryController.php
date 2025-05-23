<?php

/**
 * @copyright  For copyright and license information, read the COPYING.txt file.
 * @link       /COPYING.txt
 * @license    Open Software License (OSL 3.0)
 * @package    Mage_Adminhtml
 */

/**
 * Catalog category controller
 *
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Catalog_CategoryController extends Mage_Adminhtml_Controller_Action
{
    /**
     * ACL resource
     * @see Mage_Adminhtml_Controller_Action::_isAllowed()
     */
    public const ADMIN_RESOURCE = 'catalog/categories';

    /**
     * Initialize requested category and put it into registry.
     * Root category can be returned, if inappropriate store/category is specified
     *
     * @param bool $getRootInstead
     * @return Mage_Catalog_Model_Category|false
     */
    protected function _initCategory($getRootInstead = false)
    {
        $this->_title($this->__('Catalog'))
             ->_title($this->__('Categories'))
             ->_title($this->__('Manage Categories'));

        $categoryId = (int) $this->getRequest()->getParam('id', false);
        $storeId    = (int) $this->getRequest()->getParam('store');
        $category = Mage::getModel('catalog/category');
        $category->setStoreId($storeId);

        if ($categoryId) {
            $category->load($categoryId);
            if ($storeId) {
                $rootId = Mage::app()->getStore($storeId)->getRootCategoryId();
                if (!in_array($rootId, $category->getPathIds())) {
                    // load root category instead wrong one
                    if ($getRootInstead) {
                        $category->load($rootId);
                    } else {
                        $this->_redirect('*/*/', ['_current' => true, 'id' => null]);
                        return false;
                    }
                }
            }
        }

        if ($activeTabId = (string) $this->getRequest()->getParam('active_tab_id')) {
            Mage::getSingleton('admin/session')->setActiveTabId($activeTabId);
        }

        Mage::register('category', $category);
        Mage::register('current_category', $category);
        Mage::getSingleton('cms/wysiwyg_config')->setStoreId($this->getRequest()->getParam('store'));
        return $category;
    }
    /**
     * Catalog categories index action
     */
    public function indexAction()
    {
        $this->_forward('edit');
    }

    /**
     * Add new category form
     */
    public function addAction()
    {
        Mage::getSingleton('admin/session')->unsActiveTabId();
        $this->_forward('edit');
    }

    /**
     * Edit category page
     */
    public function editAction()
    {
        $params['_current'] = true;
        $redirect = false;

        $storeId = (int) $this->getRequest()->getParam('store');
        $parentId = (int) $this->getRequest()->getParam('parent');
        $prevStoreId = Mage::getSingleton('admin/session')
            ->getLastViewedStore(true);

        if (!empty($prevStoreId) && !$this->getRequest()->getQuery('isAjax')) {
            $params['store'] = $prevStoreId;
            $redirect = true;
        }

        $categoryId = (int) $this->getRequest()->getParam('id');
        $prevCategoryId = Mage::getSingleton('admin/session')
            ->getLastEditedCategory(true);

        if ($prevCategoryId
            && !$this->getRequest()->getQuery('isAjax')
            && !$this->getRequest()->getParam('clear')
        ) {
            $this->getRequest()->setParam('id', $prevCategoryId);
        }

        if ($redirect) {
            $this->_redirect('*/*/edit', $params);
            return;
        }

        if ($storeId && !$categoryId && !$parentId) {
            $store = Mage::app()->getStore($storeId);
            $prevCategoryId = (int) $store->getRootCategoryId();
            $this->getRequest()->setParam('id', $prevCategoryId);
        }

        if (!($category = $this->_initCategory(true))) {
            return;
        }

        $this->_title($categoryId ? $category->getName() : $this->__('New Category'));

        /**
         * Check if we have data in session (if duering category save was exceprion)
         */
        $data = Mage::getSingleton('adminhtml/session')->getCategoryData(true);
        if (isset($data['general'])) {
            $category->addData($data['general']);
        }

        /**
         * Build response for ajax request
         */
        if ($this->getRequest()->getQuery('isAjax')) {
            // prepare breadcrumbs of selected category, if any
            $breadcrumbsPath = $category->getPath();
            if (empty($breadcrumbsPath)) {
                // but if no category, and it is deleted - prepare breadcrumbs from path, saved in session
                $breadcrumbsPath = Mage::getSingleton('admin/session')->getDeletedPath(true);
                if (!empty($breadcrumbsPath)) {
                    $breadcrumbsPath = explode('/', $breadcrumbsPath);
                    // no need to get parent breadcrumbs if deleting category level 1
                    if (count($breadcrumbsPath) <= 1) {
                        $breadcrumbsPath = '';
                    } else {
                        array_pop($breadcrumbsPath);
                        $breadcrumbsPath = implode('/', $breadcrumbsPath);
                    }
                }
            }

            Mage::getSingleton('admin/session')
                ->setLastViewedStore($this->getRequest()->getParam('store'));
            Mage::getSingleton('admin/session')
                ->setLastEditedCategory($category->getId());
            $this->loadLayout();

            $eventResponse = new Varien_Object([
                'content' => $this->getLayout()->getBlock('category.edit')->getFormHtml()
                    . $this->getLayout()->getBlock('category.tree')
                    ->getBreadcrumbsJavascript($breadcrumbsPath, 'editingCategoryBreadcrumbs'),
                'messages' => $this->getLayout()->getMessagesBlock()->getGroupedHtml(),
            ]);

            Mage::dispatchEvent('category_prepare_ajax_response', [
                'response' => $eventResponse,
                'controller' => $this,
            ]);

            $this->getResponse()->setBody(
                Mage::helper('core')->jsonEncode($eventResponse->getData()),
            );

            return;
        }

        $this->loadLayout();
        $this->_setActiveMenu('catalog/categories');
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true)
            ->setContainerCssClass('catalog-categories');

        $this->_addBreadcrumb(
            Mage::helper('catalog')->__('Manage Catalog Categories'),
            Mage::helper('catalog')->__('Manage Categories'),
        );

        $block = $this->getLayout()->getBlock('catalog.wysiwyg.js');
        if ($block) {
            $block->setStoreId($storeId);
        }

        $this->renderLayout();
    }

    /**
     * WYSIWYG editor action for ajax request
     *
     */
    public function wysiwygAction()
    {
        $elementId = $this->getRequest()->getParam('element_id', md5(microtime()));
        $storeId = $this->getRequest()->getParam('store_id', 0);
        $storeMediaUrl = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);

        $content = $this->getLayout()->createBlock('adminhtml/catalog_helper_form_wysiwyg_content', '', [
            'editor_element_id' => $elementId,
            'store_id'          => $storeId,
            'store_media_url'   => $storeMediaUrl,
        ]);

        $this->getResponse()->setBody($content->toHtml());
    }

    /**
     * Get tree node (Ajax version)
     */
    public function categoriesJsonAction()
    {
        if ($this->getRequest()->getParam('expand_all')) {
            Mage::getSingleton('admin/session')->setIsTreeWasExpanded(true);
        } else {
            Mage::getSingleton('admin/session')->setIsTreeWasExpanded(false);
        }
        if ($categoryId = (int) $this->getRequest()->getPost('id')) {
            $this->getRequest()->setParam('id', $categoryId);

            if (!$category = $this->_initCategory()) {
                return;
            }
            $this->getResponse()->setBody(
                $this->getLayout()->createBlock('adminhtml/catalog_category_tree')
                    ->getTreeJson($category),
            );
        }
    }

    /**
     * Category save
     */
    public function saveAction()
    {
        if (!$category = $this->_initCategory()) {
            return;
        }

        $storeId = $this->getRequest()->getParam('store');
        $refreshTree = 'false';
        if ($data = $this->getRequest()->getPost()) {
            if (isset($data['general']['path'])) {
                unset($data['general']['path']);
            }
            $category->addData($data['general']);
            if (!$category->getId()) {
                $parentId = $this->getRequest()->getParam('parent');
                if (!$parentId) {
                    if ($storeId) {
                        $parentId = Mage::app()->getStore($storeId)->getRootCategoryId();
                    } else {
                        $parentId = Mage_Catalog_Model_Category::TREE_ROOT_ID;
                    }
                }
                $parentCategory = Mage::getModel('catalog/category')->load($parentId);
                $category->setPath($parentCategory->getPath());
            }

            /**
             * Check "Use Default Value" checkboxes values
             */
            if ($useDefaults = $this->getRequest()->getPost('use_default')) {
                foreach ($useDefaults as $attributeCode) {
                    $category->setData($attributeCode, false);
                }
            }

            /**
             * Process "Use Config Settings" checkboxes
             */
            if ($useConfig = $this->getRequest()->getPost('use_config')) {
                foreach ($useConfig as $attributeCode) {
                    $category->setData($attributeCode, null);
                }
            }

            /**
             * Create Permanent Redirect for old URL key
             */
            if ($category->getId() && isset($data['general']['url_key_create_redirect'])) {
                // && $category->getOrigData('url_key') != $category->getData('url_key')
                $category->setData('save_rewrites_history', (bool) $data['general']['url_key_create_redirect']);
            }

            $category->setAttributeSetId($category->getDefaultAttributeSetId());

            if (isset($data['category_products']) &&
                !$category->getProductsReadonly()
            ) {
                $products = Mage::helper('core/string')->parseQueryStr($data['category_products']);
                $category->setPostedProducts($products);
            }

            Mage::dispatchEvent('catalog_category_prepare_save', [
                'category' => $category,
                'request' => $this->getRequest(),
            ]);

            /**
             * Proceed with $_POST['use_config']
             * set into category model for processing through validation
             */
            $category->setData('use_post_data_config', $this->getRequest()->getPost('use_config'));

            try {
                $validate = $category->validate();
                if ($validate !== true) {
                    foreach ($validate as $code => $error) {
                        if ($error === true) {
                            Mage::throwException(Mage::helper('catalog')->__('Attribute "%s" is required.', $category->getResource()->getAttribute($code)->getFrontend()->getLabel()));
                        } else {
                            Mage::throwException($error);
                        }
                    }
                }

                /**
                 * Unset $_POST['use_config'] before save
                 */
                $category->unsetData('use_post_data_config');

                $category->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('catalog')->__('The category has been saved.'));
                $refreshTree = 'true';
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage())
                    ->setCategoryData($data);
                $refreshTree = 'false';
            }
        }
        $url = $this->getUrl('*/*/edit', ['_current' => true, 'id' => $category->getId()]);
        $this->getResponse()->setBody(
            '<script type="text/javascript">parent.updateContent("' . $url . '", {}, ' . $refreshTree . ');</script>',
        );
    }

    /**
     * Move category action
     */
    public function moveAction()
    {
        $category = $this->_initCategory();
        if (!$category) {
            $this->getResponse()->setBody(Mage::helper('catalog')->__('Category move error'));
            return;
        }
        /**
         * New parent category identifier
         */
        $parentNodeId   = $this->getRequest()->getPost('pid', false);
        /**
         * Category id after which we have put our category
         */
        $prevNodeId     = $this->getRequest()->getPost('aid', false);
        $category->setData('save_rewrites_history', Mage::helper('catalog')->shouldSaveUrlRewritesHistory());
        try {
            $category->move($parentNodeId, $prevNodeId);
            $this->getResponse()->setBody('SUCCESS');
        } catch (Mage_Core_Exception $e) {
            $this->getResponse()->setBody($e->getMessage());
        } catch (Exception $e) {
            $this->getResponse()->setBody(Mage::helper('catalog')->__('Category move error %s', $e));
            Mage::logException($e);
        }
    }

    /**
     * Delete category action
     */
    public function deleteAction()
    {
        if ($id = (int) $this->getRequest()->getParam('id')) {
            try {
                $category = Mage::getModel('catalog/category')->load($id);
                Mage::dispatchEvent('catalog_controller_category_delete', ['category' => $category]);

                Mage::getSingleton('admin/session')->setDeletedPath($category->getPath());

                $category->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('catalog')->__('The category has been deleted.'));
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->getResponse()->setRedirect($this->getUrl('*/*/edit', ['_current' => true]));
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('catalog')->__('An error occurred while trying to delete the category.'));
                $this->getResponse()->setRedirect($this->getUrl('*/*/edit', ['_current' => true]));
                return;
            }
        }
        $this->getResponse()->setRedirect($this->getUrl('*/*/', ['_current' => true, 'id' => null]));
    }

    /**
     * Grid Action
     * Display list of products related to current category
     */
    public function gridAction()
    {
        if (!$category = $this->_initCategory(true)) {
            return;
        }
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('adminhtml/catalog_category_tab_product', 'category.product.grid')
                ->toHtml(),
        );
    }

    /**
     * Tree Action
     * Retrieve category tree
     */
    public function treeAction()
    {
        $storeId = (int) $this->getRequest()->getParam('store');
        $categoryId = (int) $this->getRequest()->getParam('id');

        if ($storeId) {
            if (!$categoryId) {
                $store = Mage::app()->getStore($storeId);
                $rootId = $store->getRootCategoryId();
                $this->getRequest()->setParam('id', $rootId);
            }
        }

        $category = $this->_initCategory(true);

        /** @var Mage_Adminhtml_Block_Catalog_Category_Tree $block */
        $block = $this->getLayout()->createBlock('adminhtml/catalog_category_tree');
        $root  = $block->getRoot();
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode([
            'data' => $block->getTree(),
            'parameters' => [
                'text'        => $block->buildNodeName($root),
                'draggable'   => false,
                'allowDrop'   => ($root->getIsVisible()) ? true : false,
                'id'          => (int) $root->getId(),
                'expanded'    => (int) $block->getIsWasExpanded(),
                'store_id'    => (int) $block->getStore()->getId(),
                'category_id' => (int) $category->getId(),
                'root_visible' => (int) $root->getIsVisible(),
            ]]));
    }

    /**
    * Build response for refresh input element 'path' in form
    */
    public function refreshPathAction()
    {
        if ($id = (int) $this->getRequest()->getParam('id')) {
            $category = Mage::getModel('catalog/category')->load($id);
            $this->getResponse()->setBody(
                Mage::helper('core')->jsonEncode([
                    'id' => $id,
                    'path' => $category->getPath(),
                ]),
            );
        }
    }

    /**
     * Controller pre-dispatch method
     *
     * @return Mage_Adminhtml_Controller_Action
     */
    public function preDispatch()
    {
        $this->_setForcedFormKeyActions('delete');
        return parent::preDispatch();
    }
}
