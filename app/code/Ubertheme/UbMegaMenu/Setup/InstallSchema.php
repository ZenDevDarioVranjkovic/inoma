<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */
namespace Ubertheme\UbMegaMenu\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface {

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'ubmegamenu_group'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ubmegamenu_group')
        )->addColumn(
            'group_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Menu Group ID'
        )->addColumn(
            'title',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Menu Group Title'
        )->addColumn(
            'identifier',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            100,
            ['nullable' => true, 'default' => null],
            'Menu Group Key'
        )->addColumn(
            'animation_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            100,
            ['nullable' => true, 'default' => null],
            'Animation effect you want to display sub-menu items'
        )->addColumn(
            'mobile_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            100,
            ['nullable' => true],
            'Type you want to display sub-menu items in Mobile'
        )->addColumn(
            'description',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '2M',
            [],
            'Menu Group Description'
        )->addColumn(
            'creation_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Creation Time'
        )->addColumn(
            'update_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
            'Modification Time'
        )->addColumn(
            'is_active',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '1'],
            'Is Active'
        )->addColumn(
            'sort_order',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '0'],
            'Sort Order'
        )->addIndex(
            $installer->getIdxName('ubmegamenu_group', ['identifier']),
            ['identifier']
        )->setComment(
            'UB Mega Menu Group Table'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'ubmegamenu_group_store'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ubmegamenu_group_store')
        )->addColumn(
            'group_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'primary' => true],
            'Menu Group ID'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Store ID'
        )->addIndex(
            $installer->getIdxName('ubmegamenu_group_store', ['store_id']),
            ['store_id']
        )->addForeignKey(
            $installer->getFkName('ubmegamenu_group_store',
                'group_id', $installer->getTable('ubmegamenu_group'),
                'group_id'),
            'group_id',
            $installer->getTable('ubmegamenu_group'),
            'group_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('ubmegamenu_group_store',
                'store_id',
                $installer->getTable('store'),
                'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'UB Mega Menu Group To Store Linkage Table'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'ubmegamenu_item'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ubmegamenu_item')
        )->addColumn(
            'item_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Menu Item ID'
        )->addColumn(
            'parent_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '0'],
            'Menu Item Parent ID'
        )->addColumn(
            'group_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false],
            'Menu Group ID'
        )->addColumn(
            'title',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Menu Item Title'
        )->addColumn(
            'identifier',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Menu Item Identifier'
        )->addColumn(
            'path',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Menu Item Tree Path'
        )->addColumn(
            'level',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'default' => '1'],
            'Menu Item Level'
        )->addColumn(
            'show_title',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '1'],
            'Show/Hide Menu Item Title'
        )->addColumn(
            'icon_image',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Icon Image of Menu Item'
        )->addColumn(
            'font_awesome',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            50,
            ['nullable' => true],
            'Font Awesome Icon Class Name'
        )->addColumn(
            'link_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            50,
            ['nullable' => false, 'default' => 'custom_link'],
            'Link type of Menu Item Link(custom_link, category_page, cms_page)'
        )->addColumn(
            'link',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            500,
            ['nullable' => false, 'default' => null],
            'Menu Item Link'
        )->addColumn(
            'link_target',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            50,
            ['nullable' => false, 'default' => '_self'],
            'Menu Item Link Target Value (_blank,_self,_parent,_top)'
        )->addColumn(
            'category_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Category ID'
        )->addColumn(
            'show_number_product',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '0'],
            'Show number products of category'
        )->addColumn(
            'cms_page',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'ID of CMS page'
        )->addColumn(
            'is_group',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '0'],
            'Is Menu Item Group'
        )->addColumn(
            'mega_cols',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Number columns of sub-menu'
        )->addColumn(
            'mega_width',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Width of sub-menu wrapper (px)'
        )->addColumn(
            'mega_col_width',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Width of a column in sub-menu'
        )->addColumn(
            'mega_col_x_width',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            500,
            ['nullable' => true],
            'Width of column x in sub-menu'
        )->addColumn(
            'mega_sub_content_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            50,
            ['nullable' => false],
            'Type of sub content(Child menu items, Static blocks, Custom Content)'
        )->addColumn(
            'custom_content',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '2M',
            ['nullable' => true],
            'Custom Content (Block short code/Html/Text)'
        )->addColumn(
            'static_blocks',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            500,
            ['nullable' => true],
            'List IDs of CMS Static Blocks'
        )->addColumn(
            'addition_class',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            50,
            ['nullable' => true],
            'Addition Class CSS for menu item'
        )->addColumn(
            'description',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            500,
            ['nullable' => true],
            'Menu Item Description'
        )->addColumn(
            'creation_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Menu Item Creation Time'
        )->addColumn(
            'update_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
            'Menu Item Modification Time'
        )->addColumn(
            'is_active',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '1'],
            'Is Menu Item Active'
        )->addColumn(
            'sort_order',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '0'],
            'Menu Item Sort Order'
        )->addIndex(
            $installer->getIdxName('ubmegamenu_group', ['group_id']),
            ['group_id']
        )->addForeignKey(
            $installer->getFkName('ubmegamenu_item',
                'group_id',
                $installer->getTable('ubmegamenu_group'),
                'group_id'),
            'group_id',
            $installer->getTable('ubmegamenu_group'),
            'group_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'UB Mega Menu Items Table'
        );
        $installer->getConnection()->createTable($table);

        $installer->getConnection()->addIndex(
            $installer->getTable('ubmegamenu_group'),
            $setup->getIdxName(
                $installer->getTable('ubmegamenu_group'),
                ['identifier'],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['identifier'],
            AdapterInterface::INDEX_TYPE_INDEX
        );
        $installer->getConnection()->addIndex(
            $installer->getTable('ubmegamenu_item'),
            $setup->getIdxName(
                $installer->getTable('ubmegamenu_item'),
                ['group_id', 'parent_id'],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['group_id', 'parent_id'],
            AdapterInterface::INDEX_TYPE_INDEX
        );

        $installer->endSetup();
    }
}
