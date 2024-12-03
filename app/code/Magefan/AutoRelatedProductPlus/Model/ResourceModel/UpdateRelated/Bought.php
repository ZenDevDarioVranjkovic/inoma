<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\AutoRelatedProductPlus\Model\ResourceModel\UpdateRelated;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\ResourceConnection;

class Bought
{
    /**
     * @var CollectionFactory
     */
    protected $productCollection;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @param CollectionFactory $productCollection
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        CollectionFactory $productCollection,
        ResourceConnection $resourceConnection
    ) {
        $this->productCollection = $productCollection;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @return void
     * @throws \Zend_Db_Statement_Exception
     */
    public function execute()
    {
        $productList = $this->productCollection->create();

        foreach ($productList as $product) {
            if ($productId = (int)$product->getId()) {

                $sales_order_item = $this->resourceConnection->getTableName('sales_order_item');
                $sales_order = $this->resourceConnection->getTableName('sales_order');
                $twoYearsAgo = date('Y-m-d H:i:s', strtotime('-2 years'));

                $selectProductsIds =
                    "SELECT product_id FROM {$sales_order_item}
                        WHERE product_id != {$productId} AND order_id IN (
                            SELECT order_id FROM {$sales_order_item} oi
                                LEFT JOIN {$sales_order} o ON oi.order_id = o.entity_id
                                WHERE oi.product_id = {$productId}
                                AND o.status IN ('complete', 'processing', 'pending')
                                AND o.created_at >= '{$twoYearsAgo}')";

                $relatedProductsIds = $this->resourceConnection->getConnection()->query($selectProductsIds)->fetchAll();

                if (empty($relatedProductsIds)) {
                    continue;
                }

                $relatedIds = [];

                foreach ($relatedProductsIds as $item) {
                    $relatedIds[$item['product_id']] = $item['product_id'];
                }

                $this->resourceConnection->getConnection()->insertOnDuplicate(
                    $this->resourceConnection->getTableName('magefan_autorp_index_also_bought'),
                    ['product_id' => $product->getId(), 'related_products_ids' => implode(',', $relatedIds)],
                    ['related_products_ids']
                );
            }
        }
    }
}
