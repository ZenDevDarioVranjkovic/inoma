<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */
namespace Ubertheme\UbMegaMenu\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * UB Mega Menu Group CRUD interface.
 * @api
 */
interface GroupRepositoryInterface
{
    /**
     * Save group.
     *
     * @param \Ubertheme\UbMegaMenu\Api\Data\GroupInterface $group
     * @return \Ubertheme\UbMegaMenu\Api\Data\GroupInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Ubertheme\UbMegaMenu\Api\Data\GroupInterface $group);

    /**
     * Retrieve menu group by menu id.
     *
     * @param int $groupId
     * @return \Ubertheme\UbMegaMenu\Api\Data\GroupInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($groupId);

    /**
     * Retrieve menu group by menu key.
     *
     * @param string $menuKey
     * @return \Ubertheme\UbMegaMenu\Api\Data\GroupInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByMenuKey($menuKey);

    /**
     * Retrieve groups matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Ubertheme\UbMegaMenu\Api\Data\GroupSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete group.
     *
     * @param \Ubertheme\UbMegaMenu\Api\Data\GroupInterface $group
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Ubertheme\UbMegaMenu\Api\Data\GroupInterface $group);

    /**
     * Delete group by ID.
     *
     * @param int $groupId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($groupId);
}
