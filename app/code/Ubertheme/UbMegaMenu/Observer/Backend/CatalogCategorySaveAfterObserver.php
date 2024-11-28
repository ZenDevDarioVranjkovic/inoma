<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */
namespace Ubertheme\UbMegaMenu\Observer\Backend;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Ubertheme\Base\Helper\Data as BaseHelper;
use Ubertheme\UbMegaMenu\Helper\Data as Helper;

class CatalogCategorySaveAfterObserver implements ObserverInterface
{
    /**
     * @var BaseHelper
     */
    protected $baseHelper;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var MessageManager
     */
    protected $messageManager;

    public function __construct(
        BaseHelper $baseHelper,
        Helper $helper,
        MessageManager $messageManager
    ) {
        $this->baseHelper = $baseHelper;
        $this->helper = $helper;
        $this->messageManager = $messageManager;
    }

    /**
     * Update related menu items after a category saved
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this|void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        //check has allowed
        $isAllowed = (bool)$this->baseHelper->getConfigValueByKey('auto_sync_category_menu_item', ['ubmegamenu']);
        if (!$isAllowed) {
            return;
        }

        /** @var \Magento\Catalog\Model\Category $category */
        $category = $observer->getEvent()->getCategory();

        //get the ID of the parent category
        $parentId = $category->getParentId();

        if ($parentId == \Magento\Catalog\Model\Category::TREE_ROOT_ID) {
            return;
        }

        //get all menu items which has relationship with the parent category
        $relatedMenuItems = $this->helper->getRelatedMenuItems(
            \Ubertheme\UbMegaMenu\Model\Item::LINK_TYPE_CATEGORY,
            ['category_ids' => [$parentId]],
            false
        );
        if ($relatedMenuItems) {
            foreach ($relatedMenuItems as $relatedMenuItem) {
                $item = $this->helper->getRelatedMenuItems(
                    \Ubertheme\UbMegaMenu\Model\Item::LINK_TYPE_CATEGORY,
                    [
                        'category_ids' => [$category->getId()],
                        'parent_id' => $relatedMenuItem->getId()
                    ],
                    true
                );
                if (!$item->getId()) {
                    //add new menu item with this category
                    $this->addMenuItem($relatedMenuItem, $category);
                }
            }
            //add message updated menu items
            $this->messageManager->addWarningMessage(__('Menu items associated with this Category have been updated.'));
        }

        return $this;
    }

    /**
     * @param $parentMenuItem
     * @param $category
     * @return mixed
     */
    public function addMenuItem($parentMenuItem, $category)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();

        //build menu item data
        $data = [];
        $data['show_title'] = \Ubertheme\UbMegaMenu\Model\Item::SHOW_TITLE_YES;
        $data['icon_image'] = '';
        $data['font_awesome'] = '';
        $data['target'] = '_self';
        $data['show_number_product'] = \Ubertheme\UbMegaMenu\Model\Item::SHOW_NUMBER_PRODUCT_USE_GENERAL_CONFIG;
        $data['cms_page'] = null;
        $data['is_group'] = \Ubertheme\UbMegaMenu\Model\Item::IS_GROUP_NO;
        $data['mega_cols'] = 1;
        $data['mega_width'] = 0;
        $data['mega_col_width'] = 0;
        $data['mega_col_x_width'] = null;
        $data['mega_sub_content_type'] = \Ubertheme\UbMegaMenu\Model\Item::SUB_CONTENT_TYPE_CHILD_ITEMS;
        $data['custom_content'] = null;
        $data['static_blocks'] = null;
        $data['addition_class'] = null;
        $data['description'] = null;
        $data['is_active'] = \Ubertheme\UbMegaMenu\Model\Group::STATUS_ENABLED;
        $data['sort_order'] = $category->getPosition();
        /* @var \Ubertheme\UbMegaMenu\Model\Item $parentMenuItem */
        $data['parent_id'] = $parentMenuItem->getId();
        $data['group_id'] = $parentMenuItem->getGroupId();
        $data['link_type'] = \Ubertheme\UbMegaMenu\Model\Item::LINK_TYPE_CATEGORY;
        $data['link'] = 'dynamically';
        $data['category_id'] = $category->getId();
        $data['title'] = $category->getName();
        $data['identifier'] = trim(
            preg_replace(
                '/[^a-z0-9]+/',
                '-',
                strtolower($data['title'])
            ),
            '-'
        );
        $data['is_show_category_thumb'] = 0;

        //create and save menu item
        $menuItem = $om->create('Ubertheme\UbMegaMenu\Model\Item')->setData($data)->save();

        return $menuItem;
    }
}
