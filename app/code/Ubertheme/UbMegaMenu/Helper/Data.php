<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */
namespace Ubertheme\UbMegaMenu\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Store\Model\Store;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Cms\Model\PageFactory;
use Magento\Cms\Model\BlockFactory;
use Ubertheme\Base\Helper\Data as BaseHelper;
use Ubertheme\UbMegaMenu\Model\GroupFactory;
use Ubertheme\UbMegaMenu\Model\ItemFactory;

/**
 * Data Helper
 */
class Data extends AbstractHelper
{
    /**
     * @var BaseHelper
     */
    protected $baseHelper;

    /**
     *
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     *
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     *
     * @var BlockFactory
     */
    protected $blockFactory;

    /**
     *
     * @var GroupFactory
     */
    protected $groupFactory;

    /**
     *
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * @var MessageManagerInterface
     */
    protected $messageManager;

    /**
     * Data constructor.
     * @param Context $context
     * @param BaseHelper $baseHelper
     * @param CategoryFactory $categoryFactory
     * @param PageFactory $pageFactory
     * @param BlockFactory $blockFactory
     * @param GroupFactory $groupFactory
     * @param ItemFactory $itemFactory
     * @param MessageManagerInterface $messageManager
     */
    public function __construct(
        Context $context,
        BaseHelper $baseHelper,
        CategoryFactory $categoryFactory,
        PageFactory $pageFactory,
        BlockFactory $blockFactory,
        GroupFactory $groupFactory,
        ItemFactory $itemFactory,
        MessageManagerInterface $messageManager
    ) {
        $this->baseHelper = $baseHelper;
        $this->categoryFactory = $categoryFactory;
        $this->pageFactory = $pageFactory;
        $this->blockFactory = $blockFactory;
        $this->groupFactory = $groupFactory;
        $this->itemFactory = $itemFactory;
        $this->messageManager = $messageManager;

        parent::__construct($context);
    }

    /**
     * @param null $storeId
     * @param bool $isFilter
     * @param bool $countProduct
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCategoryOptions($storeId = null, $isFilter = false, $countProduct = false)
    {
        $store = $this->getStore($storeId);
        $parent_id = $store->getRootCategoryId();
        if ($store->getId() == Store::DEFAULT_STORE_ID) {
            $defaultStoreItems = $this->categoryFactory->create()->getCollection()
                ->addFieldToFilter('parent_id', ['in' => [$parent_id]]);
            $parent_id = $defaultStoreItems->getFirstItem()->getId();
        }

        //get categories
        $categories = $this->getCategories($store->getId(), $parent_id);

        //build tree options
        $options = $this->buildTree(
            $parent_id, $categories,
            99,
            'name',
            'entity_id',
            'parent_id',
            $isFilter,
            $countProduct
        );

        return $options;
    }

    /**
     * @param array $storeIds
     * @param bool $isFilter
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCMSPageOptions($storeIds = [], $isFilter = false)
    {
        if (!$storeIds) {
            $storeIds[] = $this->getStore()->getId();
        }

        if (!in_array(Store::DEFAULT_STORE_ID, $storeIds)) {
            $storeIds[] = Store::DEFAULT_STORE_ID;
        }

        $collection = $this->pageFactory->create()->getCollection()
            ->addFieldToSelect(['page_id', 'identifier', 'title'])
            ->addFieldToFilter('store_id', ['in' => $storeIds])
            ->addOrder('title', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);

        foreach ($collection->getItems() as $item) {
            $options[$item->getId()] = $item->getTitle();
        }

        return $options;
    }

    /**
     * @param array $storeIds
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStaticBlockOptions($storeIds = [])
    {
        $options = [];

        if (!$storeIds) {
            $storeIds[] = $this->getStore()->getId();
        }

        if (!in_array(Store::DEFAULT_STORE_ID, $storeIds)) {
            $storeIds[] = Store::DEFAULT_STORE_ID;
        }

        $collection = $this->blockFactory->create()->getCollection()
            ->addFieldToSelect(['block_id', 'identifier', 'title'])
            ->addFieldToFilter('store_id', ['in' => $storeIds])
            ->addOrder('title', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);

        foreach ($collection->getItems() as $item) {
            $options[$item->getId()] = $item->getTitle();
        }

        return $options;
    }

    /**
     * @param int $storeId
     * @param int $parentId
     * @return mixed
     */
    public function getCategories($storeId = 0, $parentId = 0)
    {
        $collection = $this->categoryFactory->create()->getCollection()
            ->addFieldToSelect(['entity_id', 'parent_id', 'name', 'level'])
            ->setStoreId($storeId)
            ->addIsActiveFilter();

        if ($parentId) {
            $collection->addFieldToFilter('path', ['like' => '%' . $parentId . '/%']);
        }

        $collection->getSelect()->order('position ASC');

        return $collection->load();
    }

    public function getMenuGroup($menuId = 0, $menuKey = null, $customerGroupId = 0)
    {
        $storeId = $this->baseHelper->getStoreManager()->getStore()->getId();
        $collection = $this->groupFactory->create()->getCollection();
        $fieldsToSelect = ['group_id', 'title', 'identifier', 'animation_type', 'is_active', 'menu_type',
            'mobile_type', 'menu_position'];
        $collection->addFieldToSelect($fieldsToSelect);
        $collection->addFieldToFilter('is_active', ['eq' => \Ubertheme\UbMegaMenu\Model\Group::STATUS_ENABLED]);
        $collection->addStoreFilter($storeId, true);
        $collection->addCustomerGroupFilter($customerGroupId);
        $collection->addOrder('group_id', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);
        if ($menuId) {
            $collection->addFieldToFilter('group_id', ['eq' => $menuId]);
        }
        if ($menuKey) {
            $collection->addFieldToFilter('identifier', ['eq' => $menuKey]);
        }

        return $collection->getFirstItem();
    }

    /**
     * @param $menuGroupId
     * @param array $configs
     * @return \Magento\Framework\DataObject[]|null
     */
    public function getMenuItems($menuGroupId, $configs = array())
    {
        $items = null;
        if ($menuGroupId) {
            $collection = $this->itemFactory->create()->getCollection()
                ->addFieldToFilter('group_id', ['eq' => $menuGroupId])
                ->addFieldToFilter('is_active', ['eq' => \Ubertheme\UbMegaMenu\Model\Item::STATUS_ENABLED])
                ->addFieldToFilter('level', ['lteq' => $configs['end_level']])
                ->addOrder('level', \Magento\Framework\Data\Collection::SORT_ORDER_ASC)
                ->addOrder('sort_order', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);
            $items = $collection->getItems();
        }

        return $items;
    }

    /**
     * Build tree items function
     *
     * @param int $rootId
     * @param $models
     * @param int $maxLevel
     * @param string $labelField
     * @param string $keyField
     * @param string $parentField
     * @param bool|false $isFilter
     * @param bool|false $countProduct
     * @return array
     */
    public function buildTree(
        $rootId = 0,
        $models = [],
        $maxLevel = 99,
        $labelField = "name",
        $keyField = "entity_id",
        $parentField = "parent_id",
        $isFilter = false,
        $countProduct = false
    ) {
        //grouping
        $children = [];
        foreach ($models as $model) {
            $pt = $model->getData($parentField);
            $list = (isset($children[$pt]) && $children[$pt]) ? $children[$pt] : [];
            array_push($list, $model);
            $children[$pt] = $list;
        }

        //build tree
        $lists = $this->_toTree(
            $rootId,
            '',
            [],
            $children,
            $maxLevel,
            0,
            $labelField,
            $keyField,
            $parentField,
            $countProduct
        );


        if ($isFilter) {
            $outputs = ['0' => __('All')];
        }

        foreach ($lists as $id => $list) {
            $lists[$id]->$labelField = $lists[$id]->$labelField;
            $outputs[$lists[$id]->getData($keyField)] = $lists[$id]->$labelField;
        }

        return $outputs;
    }

    /**
     * Generate tree items
     *
     * @param $id
     * @param $indent
     * @param $list
     * @param $children
     * @param int $maxLevel
     * @param int $level
     * @param $label
     * @param $key
     * @param $parent
     * @param bool|false $countProduct
     * @return mixed
     */
    protected function _toTree(
        $id,
        $indent,
        $list,
        &$children,
        $maxLevel = 99,
        $level = 0,
        $label = 'name',
        $key = 'entity_id',
        $parent = 'parent_id',
        $countProduct = false
    ) {
        if (isset($children[$id]) && $level <= $maxLevel) {

            foreach ($children[$id] as $v) {
                $id = $v->getData($key);

                $pre = '';
                $spacer = '--- ';
                if ($v->getData($parent) == 0) {
                    $txt = $v->getData($label);
                } else {
                    $txt = $pre . $v->getData($label);
                }

                $list[$id] = $v;
                $list[$id]->$label = "{$indent}{$txt}";

                if ($countProduct) {
                    $list[$id]->$label .= " (" . $v->getProductCount() . ")";
                }

                //$list[$id]->children = count($children[$id]);
                $list = $this->_toTree(
                    $id,
                    $indent . $spacer,
                    $list,
                    $children,
                    $maxLevel,
                    $level + 1,
                    $label,
                    $key,
                    $parent,
                    $countProduct
                );
            }
        }

        return $list;
    }

    /**
     * @param null $storeId
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getStore($storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = (int)$this->getRequest()->getParam('store', Store::DEFAULT_STORE_ID);
        }

        return $this->baseHelper->getStoreManager()->getStore($storeId);
    }

    /**
     * @return mixed
     */
    public function getRequest()
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $context = $om->get('\Magento\Backend\App\Action\Context');

        return $context->getRequest();
    }

    /**
     * @param string $type
     * @param $param
     * @param bool $getSingle
     */
    public function deleteRelatedMenuItems($type, $param = [], $getSingle = false)
    {
        //check exists of menu item with CMS page and delete items
        $collection = $this->getRelatedMenuItems($type, $param, $getSingle);
        if ($collection) {
            // delete related menu items
            if ($type == \Ubertheme\UbMegaMenu\Model\Item::LINK_TYPE_CMS) {
                foreach ($collection as $item) {
                    $item->delete();
                }
                $this->messageManager->addWarningMessage(
                    __('Menu items associated with this CMS Page have been deleted.')
                );
            } elseif($type == \Ubertheme\UbMegaMenu\Model\Item::LINK_TYPE_CATEGORY) {
                foreach ($collection as $item) {
                    $item->delete();
                }
                $this->messageManager->addWarningMessage(
                    __('Menu items associated with this Category have been deleted.')
                );
            }
        }
    }

    /**
     * @param $type
     * @param array $param
     * @param bool $getSingle
     * @return \Magento\Framework\DataObject|\Magento\Framework\DataObject[]|null
     */
    public function getRelatedMenuItems($type, $param = [], $getSingle = true)
    {
        $rs = null;

        //reset menu group filter
        \Magento\Framework\App\ObjectManager::getInstance()
            ->get('Magento\Backend\Model\Session')->setMenuGroupId(null);

        $collection = $this->itemFactory->create()->getCollection();
        if ($type == \Ubertheme\UbMegaMenu\Model\Item::LINK_TYPE_CMS) {
            $collection->addFieldToSelect(['item_id']);
            $collection->addFieldToFilter('link_type', ['eq' => $type]);
            if (isset($param['cms_page_ids'])) {
                $collection->addFieldToFilter('cms_page', ['in' => $param['cms_page_ids']]);
            }
        } elseif($type == \Ubertheme\UbMegaMenu\Model\Item::LINK_TYPE_CATEGORY) {
            $collection->addFieldToSelect(['group_id', 'item_id', 'parent_id']);
            $collection->addFieldToFilter('link_type', ['eq' => $type]);
            if (isset($param['parent_id'])) {
                $collection->addFieldToFilter('parent_id', ['eq' => $param['parent_id']]);
            }
            if (isset($param['category_ids'])) {
                $collection->addFieldToFilter('category_id', ['in' => $param['category_ids']]);
            }
        }
        $collection->load();
        if ($collection) {
            $rs = ($getSingle) ? $collection->getFirstItem() : $collection->getItems();
        }

        return $rs;
    }

}
