<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */
namespace Ubertheme\UbMegaMenu\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        //upgrade to 1.0.1
        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            //update ubmegamenu_item table
            $tableName = $setup->getTable('ubmegamenu_item');
            //check if the table already exists
            if ($setup->getConnection()->isTableExists($tableName) == true) {
                //declare some new columns
                $columns = [
                    'visible_option' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                        'nullable' => false,
                        'default' => 0,
                        'comment' => 'Options: 0 - Use general config / 1 - Customize',
                    ],
                    'visible_in' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'size' => 100,
                        'nullable' => false,
                        'comment' => 'Visible in: desktop, tablet, mobile',
                    ]
                ];
                //add columns
                $connection = $setup->getConnection();
                foreach ($columns as $name => $definition) {
                    $connection->addColumn($tableName, $name, $definition);
                }
            }
        }

        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            $tableName = $setup->getTable('ubmegamenu_item');
            $connection = $setup->getConnection();
            if ($connection->isTableExists($tableName) == true) {
                $query = "UPDATE {$tableName} SET `icon_image` = REPLACE(`icon_image`, '/ubmegamenu/images', '')";
                $connection->query($query);
            }
        }

        if (version_compare($context->getVersion(), '1.0.4') < 0) {
            //update ubmegamenu_item table
            $tableName = $setup->getTable('ubmegamenu_item');
            //check if the table already exists
            if ($setup->getConnection()->isTableExists($tableName) == true) {
                //declare some new columns
                $columns = [
                    'identifier' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'size' => 255,
                        'nullable' => true,
                        'comment' => 'Menu item identifier',
                    ],
                    'path' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'size' => 255,
                        'nullable' => true,
                        'comment' => 'Menu item tree path',
                    ],
                    'level' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'nullable' => false,
                        'default' => 1,
                        'comment' => 'Menu item tree level',
                    ]
                ];
                //add columns
                $connection = $setup->getConnection();
                foreach ($columns as $name => $definition) {
                    $connection->addColumn($tableName, $name, $definition);
                }

                //update values for new fields for all current menu items
                $menuItems = $this->getAllMenuItems($connection, $tableName);
                if ($menuItems) {
                    foreach ($menuItems as $menuItem) {
                        //get recursive parent menu items ids
                        $parentIds = [];
                        $this->getAllParentIds($connection, $tableName, $menuItem['item_id'], $parentIds);
                        $parentIds = array_reverse($parentIds);
                        //get level
                        $level = sizeof($parentIds);
                        $level = ($level) ? ++$level : 1;
                        //get path
                        $path = ($level > 1) ? implode('/', $parentIds)
                            . "/{$menuItem['item_id']}" : "{$menuItem['item_id']}";
                        //get identifier
                        $identifier = trim(
                            preg_replace('/[^a-z0-9]+/', '-', strtolower($menuItem['title'])),
                            '-'
                        );

                        //update new values
                        $query = "UPDATE {$tableName} SET `identifier` = '{$identifier}', `path` = '{$path}',"
                            ." `level` = {$level} WHERE item_id = {$menuItem['item_id']}";
                        $connection->query($query);
                    }

                    //update link for menu items
                    $query = "UPDATE {$tableName} SET `link` = 'dynamically' WHERE"
                        ." `link_type` = 'category-page' AND `category_id` > 0";
                    $connection->query($query);
                }
            }
        }

        if (version_compare($context->getVersion(), '1.1.1') < 0) {
            //update ubmegamenu_item table
            $tableName = $setup->getTable('ubmegamenu_item');
            //check if the table already exists
            if ($setup->getConnection()->isTableExists($tableName) == true) {
                //declare some new columns
                $columns = [
                    'seo_title' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'size' => 255,
                        'nullable' => true,
                        'comment' => 'Menu item SEO title',
                    ]
                ];
                //add columns
                $connection = $setup->getConnection();
                foreach ($columns as $name => $definition) {
                    $connection->addColumn($tableName, $name, $definition);
                }
            }
        }

        if (version_compare($context->getVersion(), '1.1.2') < 0) {
            //update ubmegamenu_item table
            $tableName = $setup->getTable('ubmegamenu_item');
            //check if the table already exists
            if ($setup->getConnection()->isTableExists($tableName) == true) {
                //add new column to ubmegamenu_item table
                $connection = $setup->getConnection();
                $connection->addColumn(
                    $tableName,
                    'mega_base_width_type',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                        'nullable' => false,
                        'default' => 1,
                        'comment' => 'Base column\'s width type: 1 - pixel(px), 2 - percent(%)'
                    ]
                );
            }
        }

        if (version_compare($context->getVersion(), '1.1.4') < 0) {
            //update ubmegamenu_item table
            $tableName = $setup->getTable('ubmegamenu_item');
            //check if the table already exists
            if ($setup->getConnection()->isTableExists($tableName) == true) {
                //add new column to ubmegamenu_item table
                $connection = $setup->getConnection();
                $connection->addColumn(
                    $tableName,
                    'is_show_category_thumb',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                        'nullable' => false,
                        'default' => 0,
                        'comment' => 'Is show category thumbnail'
                    ]
                );
            }
        }

        if (version_compare($context->getVersion(), '1.1.7') < 0) {
            /**
             * Create table 'ubmegamenu_group_customer_group'
             */
            $tableName = 'ubmegamenu_group_customer_group';
            $table = $setup->getConnection()->newTable(
                $setup->getTable($tableName)
            )->addColumn(
                'group_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'primary' => true],
                'Menu Group ID'
            )->addColumn(
                'customer_group_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Customer Group ID'
            )->addIndex(
                $setup->getIdxName('ubmegamenu_group_customer_group', ['customer_group_id']),
                ['customer_group_id']
            )->addForeignKey(
                $setup->getFkName($tableName,
                    'group_id',
                    $setup->getTable('ubmegamenu_group'),
                    'group_id'),
                'group_id',
                $setup->getTable('ubmegamenu_group'),
                'group_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment(
                'UB Mega Menu Group To Customer Group Linkage Table'
            );
            $setup->getConnection()->createTable($table);
        }

        if (version_compare($context->getVersion(), '1.1.9') < 0) {
            $tableName = $setup->getTable('ubmegamenu_group');
            $setup->getConnection()->addColumn($tableName, 'mobile_type', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'size' => 100,
                'nullable' => true,
                'comment' => 'Type you want to display sub-menu items in Mobile',
            ]);
        }

        if (version_compare($context->getVersion(), '1.2.4') < 0) {
            $tableName = $setup->getTable('ubmegamenu_group');
            $setup->getConnection()->addColumn($tableName, 'menu_type', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'size' => 100,
                'nullable' => true,
                'comment' => 'Type you want to display sub-menu items',
            ]);
            $setup->getConnection()->addColumn($tableName, 'menu_position', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'size' => 100,
                'nullable' => true,
                'comment' => 'The position you want to display the menu',
            ]);
        }

        $setup->endSetup();
    }

    public function getAllMenuItems($connection, $tableName)
    {
        /* @var  \Magento\Framework\DB\Adapter\AdapterInterface $connection */
        $query = "SELECT item_id, parent_id, title FROM {$tableName}";
        return $connection->fetchAll($query);
    }

    public function getAllParentIds($connection, $tableName, $menuItemId, &$parentIds)
    {
        /* @var  \Magento\Framework\DB\Adapter\AdapterInterface $connection */
        $query = "SELECT parent_id FROM {$tableName} WHERE item_id = {$menuItemId}";
        $parentId = $connection->fetchOne($query);
        if ($parentId) {
            $parentIds[] = $parentId;
            $this->getAllParentIds($connection, $tableName, $parentId, $parentIds);
        }
        return true;
    }
}
