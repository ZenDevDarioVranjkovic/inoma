<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */
namespace Ubertheme\UbMegaMenu\Setup;

use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class Uninstall implements UninstallInterface {

    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        //uninstall code, drop related tables
        $installer->getConnection()->dropTable($installer->getTable('ubmegamenu_item'));
        $installer->getConnection()->dropTable($installer->getTable('ubmegamenu_group_store'));
        $installer->getConnection()->dropTable($installer->getTable('ubmegamenu_group'));

        $installer->endSetup();
    }
}
