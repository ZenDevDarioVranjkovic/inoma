<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\AutoRelatedProductPlus\Observer\Backend\Magefan;

use Magento\Framework\Event\ObserverInterface;
use Magefan\AutoRelatedProduct\Model\Config\Source\RelatedTemplate;

class ArpRuleAfterLoad implements ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $rule = $observer->getRule();

        if ($rule->getId()) {
            $groupIds = $rule->getResource()->lookupIds($rule->getId(), 'magefan_autorp_rule_group', 'group_id');
            $rule->setData('customer_group_ids', $groupIds);
        }

        $rule->setData('category_ids', explode(',', (string)$rule->getData('category_ids')));
        $rule->setData('apply_same_as_condition', ($rule->getData('same_as_conditions_serialized') ? true : false));

        if ($rule->getTemplate() && !in_array($rule->getTemplate(), RelatedTemplate::DEFAULT_TEMPLATES)) {
            $rule->setCustomTemplate($rule->getTemplate());
            $rule->setTemplate(RelatedTemplate::CUSTOM);
        }
    }
}
