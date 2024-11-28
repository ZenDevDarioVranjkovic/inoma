<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */
namespace Ubertheme\UbMegaMenu\Setup;

use Ubertheme\UbMegaMenu\Model\GroupFactory;
use Ubertheme\UbMegaMenu\Model\ItemFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * Group factory
     *
     * @var GroupFactory
     */
    private $groupFactory;

    /**
     * Item factory
     *
     * @var ItemFactory
     */
    private $itemFactory;

    /**
     * Init
     *
     * @param GroupFactory $groupFactory
     */
    public function __construct(GroupFactory $groupFactory, ItemFactory $itemFactory)
    {
        $this->groupFactory = $groupFactory;
        $this->itemFactory = $itemFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /**
         * Insert a default menu group
         */
        $menuGroups = [
            [
                'title' => 'UB Top Menu',
                'identifier' => 'main-menu',
                'menu_position' => \Ubertheme\UbMegaMenu\Model\Group::POSITION_MAIN,
                'stores' => [0],
                'customer_groups' => [0],
                'is_active' => \Ubertheme\UbMegaMenu\Model\Group::STATUS_ENABLED,
                'menu_type' => \Ubertheme\UbMegaMenu\Model\Group::TYPE_HORIZONTAL,
                'animation_type' => 'none',
                'description' => 'UB Top Menu Default',
                'sort_order' => 0
            ]
        ];
        foreach ($menuGroups as $data) {
            //create and save menu group
            $group = $this->groupFactory->create()->setData($data)->save();

            /**
             * Insert default menu items
             */
            $menuItems = [
                [
                    'title' => 'Home',
                    'identifier' => 'home',
                    'path' => '',
                    'level' => 1,
                    'parent_id' => 0,
                    'group_id' => $group->getId(),
                    'show_title' => \Ubertheme\UbMegaMenu\Model\Item::SHOW_TITLE_YES,
                    'icon_image' => '',
                    'font_awesome' => 'fa fa-home',
                    'link_type' => \Ubertheme\UbMegaMenu\Model\Item::LINK_TYPE_CUSTOM,
                    'link' => '#',
                    'target' => '_self',
                    'category_id' => null,
                    'show_number_product' => \Ubertheme\UbMegaMenu\Model\Item::SHOW_NUMBER_PRODUCT_USE_GENERAL_CONFIG,
                    'cms_page' => null,
                    'is_group' => \Ubertheme\UbMegaMenu\Model\Item::IS_GROUP_NO,
                    'mega_cols' => 1,
                    'mega_width' => 0,
                    'mega_col_width' => 0,
                    'mega_col_x_width' => null,
                    'mega_sub_content_type' => \Ubertheme\UbMegaMenu\Model\Item::SUB_CONTENT_TYPE_CHILD_ITEMS,
                    'custom_content' => null,
                    'static_blocks' => null,
                    'addition_class' => null,
                    'description' => null,
                    'is_active' => \Ubertheme\UbMegaMenu\Model\Group::STATUS_ENABLED,
                    'sort_order' => 0
                ],
                [
                    'title' => 'Products',
                    'identifier' => 'products',
                    'path' => '',
                    'level' => 1,
                    'parent_id' => 0,
                    'group_id' => $group->getId(),
                    'show_title' => \Ubertheme\UbMegaMenu\Model\Item::SHOW_TITLE_YES,
                    'icon_image' => '',
                    'font_awesome' => 'fab fa-product-hunt',
                    'link_type' => \Ubertheme\UbMegaMenu\Model\Item::LINK_TYPE_CUSTOM,
                    'link' => '#',
                    'target' => '_self',
                    'category_id' => null,
                    'show_number_product' => \Ubertheme\UbMegaMenu\Model\Item::SHOW_NUMBER_PRODUCT_USE_GENERAL_CONFIG,
                    'cms_page' => null,
                    'is_group' => \Ubertheme\UbMegaMenu\Model\Item::IS_GROUP_NO,
                    'mega_cols' => 1,
                    'mega_width' => 0,
                    'mega_col_width' => 0,
                    'mega_col_x_width' => null,
                    'mega_sub_content_type' => \Ubertheme\UbMegaMenu\Model\Item::SUB_CONTENT_TYPE_CHILD_ITEMS,
                    'custom_content' => null,
                    'static_blocks' => null,
                    'addition_class' => null,
                    'description' => null,
                    'is_active' => \Ubertheme\UbMegaMenu\Model\Group::STATUS_ENABLED,
                    'sort_order' => 0
                ],
                [
                    'title' => 'Service',
                    'identifier' => 'service',
                    'path' => '',
                    'level' => 1,
                    'parent_id' => 0,
                    'group_id' => $group->getId(),
                    'show_title' => \Ubertheme\UbMegaMenu\Model\Item::SHOW_TITLE_YES,
                    'icon_image' => '',
                    'font_awesome' => 'fab fa-servicestack',
                    'link_type' => \Ubertheme\UbMegaMenu\Model\Item::LINK_TYPE_CUSTOM,
                    'link' => '#',
                    'target' => '_self',
                    'category_id' => null,
                    'show_number_product' => \Ubertheme\UbMegaMenu\Model\Item::SHOW_NUMBER_PRODUCT_USE_GENERAL_CONFIG,
                    'cms_page' => null,
                    'is_group' => \Ubertheme\UbMegaMenu\Model\Item::IS_GROUP_NO,
                    'mega_cols' => 1,
                    'mega_width' => 0,
                    'mega_col_width' => 0,
                    'mega_col_x_width' => null,
                    'mega_sub_content_type' => \Ubertheme\UbMegaMenu\Model\Item::SUB_CONTENT_TYPE_CHILD_ITEMS,
                    'custom_content' => null,
                    'static_blocks' => null,
                    'addition_class' => null,
                    'description' => null,
                    'is_active' => \Ubertheme\UbMegaMenu\Model\Group::STATUS_ENABLED,
                    'sort_order' => 0
                ]
            ];
            foreach ($menuItems as $itemData) {
                //create and save menu item
                $this->itemFactory->create()->setData($itemData)->save();
            }
        }

        $setup->endSetup();
    }
}
