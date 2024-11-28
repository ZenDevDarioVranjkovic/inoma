<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */
namespace Ubertheme\UbMegaMenu\Observer\Backend;

use Magento\Framework\Event\ObserverInterface;

class CatalogCategoryDeleteAfterObserver implements ObserverInterface
{
    /**
     * @var \Ubertheme\UbMegaMenu\Helper\Data
     */
    protected $_helper;

    /**
     * @param \Ubertheme\UbMegaMenu\Helper\Data $helper
     */
    public function __construct(
        \Ubertheme\UbMegaMenu\Helper\Data $helper
    ) {
        $this->_helper = $helper;
    }

    /**
     * Delete related menu item after a category deleted
     *
     * @param   \Magento\Framework\Event\Observer $observer
     * @return  $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Catalog\Model\Category $category */
        $category = $observer->getEvent()->getCategory();
        $this->_helper->deleteRelatedMenuItems(
            \Ubertheme\UbMegaMenu\Model\Item::LINK_TYPE_CATEGORY,
            [
                'category_ids' => [$category->getId()]
            ],
            false
        );

        return $this;
    }
}
