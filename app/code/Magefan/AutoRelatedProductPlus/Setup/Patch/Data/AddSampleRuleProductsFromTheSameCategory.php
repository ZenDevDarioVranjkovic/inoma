<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\AutoRelatedProductPlus\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magefan\AutoRelatedProduct\Model\Config\Source\RelatedTemplate;

class AddSampleRuleProductsFromTheSameCategory implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(ModuleDataSetupInterface $moduleDataSetup)
    {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @return AddSampleRuleProductsFromTheSameCategory|void
     */
    public function apply()
    {
        $connection = $this->moduleDataSetup->getConnection();
        $connection->startSetup();

        $mayInstallSampleData = $connection->select()
            ->from([$this->moduleDataSetup->getTable('magefan_autorp_rule')]);

        if (!count($connection->fetchAll($mayInstallSampleData))) {
            $connection->insert(
                $this->moduleDataSetup->getTable('magefan_autorp_rule'),
                [
                    'id' => 1,
                    'status' => 1,
                    'name' => 'Products from The Same Category',
                    'priority' => 20,
                    'store_ids' => 0,
                    'block_position' => 'product_into_related',
                    'merge_type' => 'Merge',
                    'from_one_category_only' => 1,
                    'block_title' => 'Related Products',
                    'sort_by' => 1,
                    'number_of_products' => 6,
                    'template' => RelatedTemplate::DEFAULT
                ]
            );

            $connection->insert(
                $this->moduleDataSetup->getTable('magefan_autorp_rule_store'),
                [
                    'rule_id' => 1,
                    'store_id' => 0,
                ]
            );

            $ruleGroup = $this->moduleDataSetup->getTable('magefan_autorp_rule_group');
            $ruleTable = $this->moduleDataSetup->getTable('magefan_autorp_rule');

            $this->moduleDataSetup->run('INSERT INTO ' . $ruleGroup . ' SELECT id, 0 from ' . $ruleTable . ' ON DUPLICATE KEY UPDATE group_id=group_id');
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }
}
