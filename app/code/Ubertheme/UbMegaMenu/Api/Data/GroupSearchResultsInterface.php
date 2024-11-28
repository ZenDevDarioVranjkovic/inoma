<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */
namespace Ubertheme\UbMegaMenu\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for UB mega menu group search results.
 * @api
 */
interface GroupSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get group list.
     *
     * @return \Ubertheme\UbMegaMenu\Api\Data\GroupInterface[]
     */
    public function getItems();

    /**
     * Set group list.
     *
     * @param \Ubertheme\UbMegaMenu\Api\Data\GroupInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
