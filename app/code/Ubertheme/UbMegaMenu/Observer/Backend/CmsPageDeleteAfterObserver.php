<?php
/**
 * Copyright © 2018 Ubertheme.com All rights reserved.
 */
namespace Ubertheme\UbMegaMenu\Observer\Backend;

use Magento\Framework\Event\ObserverInterface;

class CmsPageDeleteAfterObserver implements ObserverInterface
{
    /**
     * Update related menu items after a CMS page deleted
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this|void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Ubertheme\Base\Helper\Data $baseHelper */
        $baseHelper = $om->get('Ubertheme\Base\Helper\Data');
        /** @var \Ubertheme\UbMegaMenu\Helper\Data $helperData */
        $helperData = $om->get('Ubertheme\UbMegaMenu\Helper\Data');

        //check has allowed
        $isAllowed = (bool)$baseHelper->getConfigValueByKey('auto_sync_cmspage_menu_item', ['ubmegamenu']);
        if (!$isAllowed) {
            return;
        }
        $pageId = $helperData->getRequest()->getParam('page_id');
        if ($pageId) {
            $helperData->deleteRelatedMenuItems(
                \Ubertheme\UbMegaMenu\Model\Item::LINK_TYPE_CMS,
                [
                    'cms_page_ids' => [$pageId]
                ],
                false
            );
        }

        return $this;
    }
}
