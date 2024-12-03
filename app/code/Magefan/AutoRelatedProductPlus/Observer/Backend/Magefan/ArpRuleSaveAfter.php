<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\AutoRelatedProductPlus\Observer\Backend\Magefan;

use Magento\Framework\Event\ObserverInterface;

class ArpRuleSaveAfter implements ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $rule = $observer->getRule();
        $object = $rule->getResource();
        $oldIds = (array)$object->lookupIds($rule->getId(), 'magefan_autorp_rule_group', 'group_id');
        $newIds = explode(',', (string)$rule->getData('customer_group_ids'));
        $object->updateLinks($rule, $newIds, $oldIds, 'magefan_autorp_rule_group', 'group_id');
    }
}
