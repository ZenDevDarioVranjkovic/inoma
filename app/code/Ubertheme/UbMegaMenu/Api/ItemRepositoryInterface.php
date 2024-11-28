<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */
namespace Ubertheme\UbMegaMenu\Api;

/**
 * UB Mega Menu Item CRUD interface.
 * @api
 */
interface ItemRepositoryInterface
{
    /**
     * Save item.
     *
     * @param \Ubertheme\UbMegaMenu\Api\Data\ItemInterface $item
     * @return \Ubertheme\UbMegaMenu\Api\Data\ItemInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(Data\ItemInterface $item);

    /**
     * Retrieve item.
     *
     * @param int $itemId
     * @return \Ubertheme\UbMegaMenu\Api\Data\ItemInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($itemId);

    /**
     * Retrieve items matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Ubertheme\UbMegaMenu\Api\Data\ItemSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete item.
     *
     * @param \Ubertheme\UbMegaMenu\Api\Data\ItemInterface $item
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Ubertheme\UbMegaMenu\Api\Data\ItemInterface $item);

    /**
     * Delete item by item id.
     *
     * @param int $itemId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($itemId);
}
