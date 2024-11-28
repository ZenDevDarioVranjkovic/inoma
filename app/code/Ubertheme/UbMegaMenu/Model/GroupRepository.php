<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */
namespace Ubertheme\UbMegaMenu\Model;

use Ubertheme\UbMegaMenu\Api\Data;
use Ubertheme\UbMegaMenu\Api\GroupRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;
use Ubertheme\UbMegaMenu\Model\ResourceModel\Group as ResourceGroup;
use Ubertheme\UbMegaMenu\Model\ResourceModel\Group\CollectionFactory as GroupCollectionFactory;

/**
 * Class GroupRepository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GroupRepository implements GroupRepositoryInterface
{
    /**
     * @var ResourceGroup
     */
    protected $resource;

    /**
     * @var GroupFactory
     */
    protected $groupFactory;

    /**
     * @var GroupCollectionFactory
     */
    protected $groupCollectionFactory;

    /**
     * @var Data\GroupSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var \Ubertheme\UbMegaMenu\Api\Data\GroupInterfaceFactory
     */
    protected $dataGroupFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ResourceGroup $resource
     * @param GroupFactory $groupFactory
     * @param Data\GroupInterfaceFactory $dataGroupFactory
     * @param GroupCollectionFactory $groupCollectionFactory
     * @param Data\GroupSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceGroup $resource,
        GroupFactory $groupFactory,
        Data\GroupInterfaceFactory $dataGroupFactory,
        GroupCollectionFactory $groupCollectionFactory,
        Data\GroupSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->groupFactory = $groupFactory;
        $this->groupCollectionFactory = $groupCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataGroupFactory = $dataGroupFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * Save Group data
     *
     * @param \Ubertheme\UbMegaMenu\Api\Data\GroupInterface $group
     * @return Group
     * @throws CouldNotSaveException
     */
    public function save(\Ubertheme\UbMegaMenu\Api\Data\GroupInterface $group)
    {
        try {
            $this->resource->save($group);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $group;
    }

    /**
     * Load Group data by given Group Identity
     *
     * @param string $groupId
     * @return Group
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($groupId)
    {
        $group = $this->groupFactory->create();
        $group->load($groupId);
        if (!$group->getId()) {
            throw new NoSuchEntityException(__('Menu Group with id "%1" does not exist.', $groupId));
        }

        return $group;
    }

    /**
     * Load menu group data by given menu key
     *
     * @param string $menuKey
     * @return Group
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByMenuKey($menuKey)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $collection = $this->groupCollectionFactory->create()
            ->addFieldToSelect(['group_id', 'title', 'identifier', 'is_active'])
            ->addFieldToFilter('identifier', ['eq' => $menuKey])
            ->addFieldToFilter('is_active', ['eq' => \Ubertheme\UbMegaMenu\Model\Group::STATUS_ENABLED])
            ->addStoreFilter($storeId, true)
            ->addOrder('group_id', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);

        /** @var \Ubertheme\UbMegaMenu\Model\Group $group */
        $group = $collection->getFirstItem();
        if (!$group->getId()) {
            throw new NoSuchEntityException(__('Menu Group with menu key "%1" does not exist.', $menuKey));
        }

        return $group;
    }

    /**
     * Load Group data collection by given search criteria
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return \Ubertheme\UbMegaMenu\Model\ResourceModel\Group\Collection
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $collection = $this->groupCollectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() === 'store_id') {
                    $collection->addStoreFilter($filter->getValue(), false);
                    continue;
                }
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
        $searchResults->setTotalCount($collection->getSize());
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        $groups = [];
        /** @var Group $groupModel */
        foreach ($collection as $groupModel) {
            $groupData = $this->dataGroupFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $groupData,
                $groupModel->getData(),
                'Ubertheme\UbMegaMenu\Api\Data\GroupInterface'
            );
            $groups[] = $this->dataObjectProcessor->buildOutputDataArray(
                $groupData,
                'Ubertheme\UbMegaMenu\Api\Data\GroupInterface'
            );
        }
        $searchResults->setItems($groups);

        return $searchResults;
    }

    /**
     * Delete Group
     *
     * @param \Ubertheme\UbMegaMenu\Api\Data\GroupInterface $group
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(\Ubertheme\UbMegaMenu\Api\Data\GroupInterface $group)
    {
        try {
            $this->resource->delete($group);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete Group by given Group Identity
     *
     * @param string $groupId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($groupId)
    {
        return $this->delete($this->getById($groupId));
    }
}
