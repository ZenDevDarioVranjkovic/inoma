<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */
namespace Ubertheme\UbMegaMenu\Model\Config\Source;

class OffcanvasBreakpoint implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'all', 'label' => __('All Devices')],
            ['value' => 'mobile', 'label' => __('Mobile')]
        ];
    }
}
