<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\AutoRelatedProductPlus\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;

/**
 * Class RelatedProducts
 * @package Magefan\AutoRelatedProductPlus\Model
 */
class RelatedProducts
{
    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var
     */
    protected $connection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
    {
        if (!$this->connection) {
            $this->connection = $this->resourceConnection->getConnection();
        }
        return $this->connection;
    }

    /**
     * @param int $productId
     * @param string $tableName
     * @return array
     */
    public function getRelatedIds(int $productId, string $tableName): array
    {
        $query = $this->getConnection()->select()
            ->from($this->resourceConnection->getTableName($tableName), 'related_products_ids')
            ->where('product_id = ?', $productId);

        $relatedProductsIds = $this->getConnection()->fetchOne($query);

        return ($relatedProductsIds)
            ? explode(',', $relatedProductsIds)
            : [];
    }
}
