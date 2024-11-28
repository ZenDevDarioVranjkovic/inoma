<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */
namespace Ubertheme\UbMegaMenu\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for UB mega menu item search results.
 * @api
 */
interface ItemSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get group list.
     *
     * @return \Ubertheme\UbMegaMenu\Api\Data\ItemInterface[]
     */
    public function getItems();

    /**
     * Set group list.
     *
     * @param \Ubertheme\UbMegaMenu\Api\Data\ItemInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
