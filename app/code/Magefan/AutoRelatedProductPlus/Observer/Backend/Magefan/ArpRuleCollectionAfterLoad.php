<?php

namespace Magefan\AutoRelatedProductPlus\Observer\Backend\Magefan;

use Magento\Framework\Event\ObserverInterface;

class ArpRuleCollectionAfterLoad implements ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $collection = $observer->getCollection();
        $items = $collection->getColumnValues('id');

        if (count($items)) {
            $connection = $collection->getConnection();
            $tableName = $collection->getTable('magefan_autorp_rule_group');
            $select = $connection->select()
                ->from(['cps' => $tableName])
                ->where('cps.rule_id IN (?)', $items);

            $result = [];

            foreach ($connection->fetchAll($select) as $item) {
                if (!isset($result[$item['rule_id']])) {
                    $result[$item['rule_id']] = [];
                }
                $result[$item['rule_id']][] = $item['group_id'];
            }

            if ($result) {
                foreach ($collection as $item) {
                    $ruleId = $item->getData('id');
                    if (!isset($result[$ruleId])) {
                        continue;
                    }
                    if ($result[$ruleId] == 0) {
                        $stores = $collection->_storeManager->getStores(false, true);
                        $storeId = current($stores)->getId();
                    } else {
                        $storeId = $result[$item->getData('id')];
                    }

                    $item->setData('_first_store_id', $storeId);
                    $item->setData('group_ids', $result[$ruleId]);
                    $item->setData('category_ids', $item->getData('category_ids'));
                }
            }
        }
    }
}
