<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\AutoRelatedProductPlus\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magefan\AutoRelatedProduct\Api\RelatedCollectionInterfaceFactory;
use Magento\Customer\Model\ResourceModel\Group\Collection as GroupCollection;
use Magefan\AutoRelatedProduct\Api\RuleRepositoryInterface;

class AddCustomerGroupsIfMissed implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var RelatedCollectionInterfaceFactory
     */
    protected $ruleCollection;

    /**
     * @var GroupCollection
     */
    protected $groupCollection;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param RuleRepositoryInterface $ruleRepository
     * @param RelatedCollectionInterfaceFactory $ruleCollection
     * @param GroupCollection $groupCollection
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        RuleRepositoryInterface $ruleRepository,
        RelatedCollectionInterfaceFactory $ruleCollection,
        GroupCollection $groupCollection

    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->ruleRepository = $ruleRepository;
        $this->ruleCollection = $ruleCollection;
        $this->groupCollection = $groupCollection;
    }

    /**
     * @return AddSampleRuleProductsFromTheSameCategory|void
     */
    public function apply()
    {
        $connection = $this->moduleDataSetup->getConnection();
        $connection->startSetup();

        $customerGroupsIds = $this->groupCollection->getAllIds();
        $rules = $this->ruleCollection->create()
            ->addFieldToFilter('status', 1)
            ->addFieldToFilter('customer_group_ids', ['null' => true]);

        foreach ($rules as $rule) {
            $rule = $this->ruleRepository->get($rule->getId());
            $rule->setData('customer_group_ids', implode(',', $customerGroupsIds));
            $this->ruleRepository->save($rule);
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
