<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\AutoRelatedProductPlus\Ui\Component\DataProvider\Product;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Registry;
use Magento\Store\Api\StoreRepositoryInterface;

/**
 * Class ProductDataProvider
 * @package Magefan\AutoRelatedProductPlus\Ui\DataProvider\Product
 */
class ProductDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    const PRODUCTS_KEY = 'mfautorp_conditions_applied';

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param Registry $registry
     * @param StoreRepositoryInterface $storeRepository
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        Registry $registry,
        StoreRepositoryInterface $storeRepository,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->storeRepository = $storeRepository;
        $this->collection = $collectionFactory->create();
        $this->registry = $registry;
    }

    /**
     * @param array $productIds
     */
    public function updateCollection($productIds)
    {
        /** @var \Magento\Catalog\Ui\DataProvider\Product\ProductCollection $collection */
        $collection = parent::getCollection();
        $collection->addIdFilter($productIds);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        $appliedProducts = $this->registry->registry(self::PRODUCTS_KEY);
        $appliedProducts = $this->prepareData($appliedProducts);
        $collection = $this->getCollection();
        $collection->joinField('qty', 'cataloginventory_stock_item', 'qty', 'product_id=entity_id', '{{table}}.stock_id=1', 'left');
        $collection->addFieldToFilter('entity_id', ['in' => array_keys($appliedProducts)]);

        if (!$collection->isLoaded()) {
            if ($appliedProducts) {
                $this->updateCollection(array_keys($appliedProducts));
            }
            $collection->load();
        }
        $items = $collection->toArray();
        if ($appliedProducts) {
            foreach ($items as &$item) {
                if (isset($appliedProducts[$item['entity_id']])) {
                    $stores = $appliedProducts[$item['entity_id']];
                    if (in_array('0', $stores)) {
                        $item['stores'] = ['0'];
                    } else {
                        $item['stores'] = $stores;
                    }
                }
            }
        }

        return [
            'totalRecords' => $collection->getSize(),
            'items' => array_values($items),
        ];
    }


    /**
     * @param $ids
     * @return array
     */
    protected function prepareData($ids)
    {
        $resultIds = [];

        if ($ids) {
            $stores = $this->storeRepository->getList();
            /** @var $productCollection Collection */
            $productCollection = $this->getCollection();
            foreach ($stores as $store) {
                $storeId = $store->getId();
                if (!empty($storeId)) {
                    $productCollection->setStoreId($storeId)->addIdFilter($ids);
                    foreach ($ids as $id) {
                        $resultIds[$id][] = $store->getId();
                    }
                }
            }

        }

        return $resultIds;
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        return [
            'mfautorp_product_listing_data_source' => [
                'arguments' => [
                    'data' => [
                        'js_config' => [
                            'rule_id' => $this->getRequest()->getParam('rule_id')
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @return \Magento\Framework\App\RequestInterface
     */
    private function getRequest()
    {
        return $this->data['config']['request'];
    }
}
