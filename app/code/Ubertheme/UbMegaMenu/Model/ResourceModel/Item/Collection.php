<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */
namespace Ubertheme\UbMegaMenu\Model\ResourceModel\Item;

/**
 * UB Mega Menu Item Collection
 *
 * Class Collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
    * @var string
    */
    protected $_idFieldName = 'item_id';

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    protected $storeManager;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->storeManager = $storeManager;
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Ubertheme\UbMegaMenu\Model\Item',
            'Ubertheme\UbMegaMenu\Model\ResourceModel\Item'
        );
        $this->_map['fields']['item_id'] = 'main_table.item_id';
    }

    /**
     * @return array
     */
    public function toOptionIdArray()
    {
        $res = [];
        $existingIds = [];
        foreach ($this as $item) {
            $id = $item->getData('item_id');

            $data['value'] = $id;
            $data['label'] = $item->getData('title');

            if (in_array($id, $existingIds)) {
                $data['value'] .= '|' . $item->getData('item_id');
            } else {
                $existingIds[] = $id;
            }

            $res[] = $data;
        }

        return $res;
    }

    /**
     * Get SQL for get record count.
     * Extra GROUP BY strip added.
     *
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectCountSql()
    {
        /** @var \Magento\Framework\ObjectManagerInterface $om */
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $groupId = $om->get('Magento\Backend\Model\Session')->getMenuGroupId();
        if ($groupId) {
            $this->addFieldToFilter('group_id', $groupId);
        }

        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(\Zend_Db_Select::GROUP);

        return $countSelect;
    }

    /**
     * Redeclare before load method for adding event
     *
     * @return $this
     */
    protected function _beforeLoad()
    {
        /** @var \Magento\Framework\ObjectManagerInterface $om */
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $groupId = $om->get('Magento\Backend\Model\Session')->getMenuGroupId();
        if ($groupId) {
            $this->addFieldToFilter('group_id', $groupId);
        }

        return parent::_beforeLoad();
    }

    /**
     * Join menu group relation table if there is menu group filter
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        /** @var \Magento\Framework\ObjectManagerInterface $om */
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $groupId = $om->get('Magento\Backend\Model\Session')->getMenuGroupId();
        if ($groupId) {
            $this->addFieldToFilter('group_id', $groupId);
        }

        parent::_renderFiltersBefore();
    }
}
