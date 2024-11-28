<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */
namespace Ubertheme\UbMegaMenu\Model\Group\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class IsActive
 */
class IsActive implements OptionSourceInterface
{
    /**
     * @var \Ubertheme\UbMegaMenu\Model\Group
     */
    protected $group;

    /**
     * Constructor
     *
     * @param \Ubertheme\UbMegaMenu\Model\Group $group
     */
    public function __construct(\Ubertheme\UbMegaMenu\Model\Group $group)
    {
        $this->group = $group;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->group->getAvailableStatuses();
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
