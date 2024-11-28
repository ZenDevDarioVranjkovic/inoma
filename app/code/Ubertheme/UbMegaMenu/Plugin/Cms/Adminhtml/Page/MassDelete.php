<?php
/**
 * Copyright © 2018 Ubertheme.com All rights reserved.
 */
namespace Ubertheme\UbMegaMenu\Plugin\Cms\Adminhtml\Page;

class MassDelete extends \Magento\Cms\Controller\Adminhtml\Page\MassDelete
{
    /**
     * @param \Magento\Cms\Controller\Adminhtml\Page\MassDelete $subject
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeExecute(\Magento\Cms\Controller\Adminhtml\Page\MassDelete $subject)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Ubertheme\Base\Helper\Data $baseHelper */
        $baseHelper = $om->get('Ubertheme\Base\Helper\Data');
        /** @var \Ubertheme\UbMegaMenu\Helper\Data $helperData */
        $helperData = $om->get('Ubertheme\UbMegaMenu\Helper\Data');

        //check has allowed
        $isAllowed = (bool)$baseHelper->getConfigValueByKey('auto_sync_cmspage_menu_item', ['ubmegamenu']);
        if (!$isAllowed) {
            return [];
        }

        $collection = $subject->filter->getCollection($subject->collectionFactory->create());
        foreach ($collection as $item) {
            $helperData->deleteRelatedMenuItems(
                \Ubertheme\UbMegaMenu\Model\Item::LINK_TYPE_CMS,
                [
                    'cms_page_ids' => [$item->getId()]
                ],
                false
            );
        }

        return [];
    }
}
