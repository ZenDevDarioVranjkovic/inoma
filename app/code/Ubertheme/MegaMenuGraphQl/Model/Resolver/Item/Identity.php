<?php
/**
 * Copyright © 2016 Ubertheme. All rights reserved.
 */
declare(strict_types=1);

namespace Ubertheme\MegaMenuGraphQl\Model\Resolver\Item;

use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;

/**
 * Identity for resolved menu item
 */
class Identity implements IdentityInterface
{
    /** @var string */
    private $cacheTag = \Ubertheme\UbMegaMenu\Model\Item::CACHE_TAG;

    /**
     * Get menu item identities from resolved data
     *
     * @param array $resolvedData
     * @return string[]
     */
    public function getIdentities(array $resolvedData): array
    {
        $ids = [];
        $items = $resolvedData['items'] ?? [];
        foreach ($items as $item) {
            if (is_array($item) && !empty($item['id'])) {
                $ids[] = sprintf('%s_%s', $this->cacheTag, $item['id']);
            }
        }

        if (!empty($ids)) {
            array_unshift($ids, $this->cacheTag);
        }

        return $ids;
    }
}
