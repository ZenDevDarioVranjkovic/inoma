<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */
namespace Ubertheme\UbMegaMenu\Model\Item\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class IsActive
 */
class IsActive implements OptionSourceInterface
{
    /**
     * @var \Ubertheme\UbMegaMenu\Model\Item
     */
    protected $item;

    /**
     * Constructor
     *
     * @param \Ubertheme\UbMegaMenu\Model\Item $item
     */
    public function __construct(\Ubertheme\UbMegaMenu\Model\Item $item)
    {
        $this->item = $item;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->item->getAvailableStatuses();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
