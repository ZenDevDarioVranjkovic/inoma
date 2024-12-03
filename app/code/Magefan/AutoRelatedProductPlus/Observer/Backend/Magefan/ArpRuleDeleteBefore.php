<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\AutoRelatedProductPlus\Observer\Backend\Magefan;

use Magento\Framework\Event\ObserverInterface;

class ArpRuleDeleteBefore implements ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $rule = $observer->getRule();
        $condition = ['rule_id = ?' => (int)$rule->getId()];
        $tables = [
            'magefan_autorp_index',
            'magefan_autorp_rule_group'
        ];

        $resourceModel = $rule->getResource();

        foreach ($tables as $table) {
            $resourceModel->getConnection()->delete(
                $resourceModel->getTable($table),
                $condition
            );
        }
    }
}
