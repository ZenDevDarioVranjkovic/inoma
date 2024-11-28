<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */
namespace Ubertheme\UbMegaMenu\Model\Config\Source;

class Devices implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'desktop', 'label' => __('Desktop')],
            ['value' => 'tablet', 'label' => __('Tablet')],
            ['value' => 'mobile', 'label' => __('Mobile')]
        ];
    }
}
